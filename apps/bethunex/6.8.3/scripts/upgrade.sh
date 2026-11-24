#!/bin/bash
# 升级/回滚钩子：先停容器 → 记录旧镜像 → 备份数据（应用目录外）→ 校验备份 → 提示回滚
set -uo pipefail
ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
DATA="$ROOT/data"
PRESERVE=${ZCLOUD_PRESERVE_DIR:-/opt/zcloud-preserved}
TS=$(date +%Y%m%d%H%M%S)
BAK="$PRESERVE/upgrade-$TS"
NEW_IMAGE="docker.io/bethunex/bethunex-core:6.8.3"

# 容器名：compose 用 ${CONTAINER_NAME} 渲染（由 1Panel 注入，实际名字可能是安装名而非 zcloud）。
# 商店升级时面板会先删容器，此处按 compose 项目名反查；都没有时兜底旧安装名 zcloud。
# 来源：1panel-appstore-skills references/appstore-format.md（compose 主服务用 ${CONTAINER_NAME}）。
CNAME="${CONTAINER_NAME:-}"
if [ -z "$CNAME" ]; then
  _proj=$(basename "$ROOT")
  CNAME=$(docker ps -a --filter "label=com.docker.compose.project=$_proj" \
            --format '{{.Names}}' 2>/dev/null | head -1)
fi
[ -n "$CNAME" ] || CNAME="zcloud"

# 商店形态：包内不带镜像，升级前从镜像仓库拉；先拉再停容器，失败时旧实例仍在。
# 无条件 pull：同名 tag 重建后本地旧镜像不会自动更新。
if [ ! -d "$ROOT/images" ] || [ -z "$(ls -A "$ROOT/images" 2>/dev/null)" ]; then
  echo "[upgrade] 从仓库拉取/更新新镜像 $NEW_IMAGE"
  docker pull "$NEW_IMAGE" \
    || { echo "[upgrade][ERROR] 拉取新镜像失败: $NEW_IMAGE" >&2; exit 1; }
fi

# 0) 版本闸门：6.8.3 不提供从 6.8.2 升级的迁移路径（Eureka → Consul、Prometheus →
#    VictoriaMetrics、服务清单 39 → 42），旧数据直接换镜像会起不来。跨版本请走
#    「卸载（数据自动保留到 /opt/zcloud-preserved）→ 安装 6.8.3 → 回灌数据」。
OLD_IMG=$(docker inspect "$CNAME" --format '{{.Image}}' 2>/dev/null || true)
if [ -z "$OLD_IMG" ]; then
  # 商店升级路径：1Panel 会先移除旧容器再调用本脚本，此时无法从容器取旧版本，
  # 改用数据卷里的版本标记（entrypoint 每次启动写入）判断是否同系列。
  MARKER="$DATA/shared/dbaas/zcloud-config/installed-version"
  OLD_VER=""
  [ -s "$MARKER" ] && OLD_VER=$(cat "$MARKER" 2>/dev/null | tr -d '[:space:]')
  if [ -z "$OLD_VER" ]; then
    if [ "${BLOCK_UNKNOWN_OLD:-}" = "1" ]; then
      echo "[upgrade][ERROR] 容器已移除且数据卷无版本标记，拒绝升级（BLOCK_UNKNOWN_OLD=1）。" >&2
      exit 1
    fi
    echo "[upgrade][WARN] 容器已由面板移除且数据卷无版本标记（商店升级路径），按同系列升级继续"
    OLD_VER="6.8.3"
  fi
  case "$OLD_VER" in
    6.8.3*) ;;
    *)
      echo "[upgrade][ERROR] 数据版本 ${OLD_VER} 不能直接升级到 6.8.3（不兼容 6.8.2 数据）。" >&2
      echo "                请走「卸载（数据保留）→ 安装 → 回灌」。" >&2
      exit 1
      ;;
  esac
  echo "[upgrade] 旧版本（数据标记）: $OLD_VER"
fi
if [ -n "$OLD_IMG" ]; then
  OLD_VER=$(docker run --rm --entrypoint /bin/sh "$OLD_IMG" -c \
      'grep -o "\"_version\": *\"[^\"]*\"" /opt/zcloud/etc/services.json | head -1' 2>/dev/null \
      | sed -E 's/.*"([0-9][^"]*)".*/\1/' || true)
  case "$OLD_VER" in
    6.8.3*) ;;
    "")
      echo "[upgrade][ERROR] 旧镜像 $OLD_IMG 里读不到版本（镜像已被删/不含 services.json）。" >&2
      echo "                若确认是同系列升级，可设 ALLOW_UNKNOWN_OLD=1 跳过闸门（将没有回滚镜像）。" >&2
      [ "${ALLOW_UNKNOWN_OLD:-}" = "1" ] || exit 1
      ;;
    *)
      echo "[upgrade][ERROR] 当前安装版本 ${OLD_VER} 不能直接升级到 6.8.3。" >&2
      echo "                6.8.3 更换了注册中心（Eureka→Consul）、监控后端（Prometheus→VictoriaMetrics）" >&2
      echo "                与服务清单（39→42），不兼容 6.8.2 的数据。请按以下步骤迁移：" >&2
      echo "                1) 面板「卸载」本应用（脚本会把 data 移到 /opt/zcloud-preserved/zcloud-<时间戳>）" >&2
      echo "                2) 安装 6.8.3（全新初始化，约 10 分钟）" >&2
      echo "                3) 按 /opt/zcloud-preserved/LAST_BACKUP 记录回灌业务数据（非整库覆盖）" >&2
      exit 1
      ;;
  esac
fi

# 1) 停容器（正在写的 InnoDB 不能直接 tar）
if docker ps --format '{{.Names}}' | grep -qx "$CNAME"; then
  echo "[upgrade] 停止容器 $CNAME"
  docker stop -t 300 "$CNAME" >/dev/null \
    || { echo "[upgrade][ERROR] 容器停止失败" >&2; exit 1; }
fi

# 2) 记录旧镜像并保留可回滚 tag（新镜像覆盖同名 tag 后旧镜像会变 <none>）
mkdir -p "$BAK"
OLD_ID=$(docker inspect "$CNAME" --format '{{.Image}}' 2>/dev/null || true)
if [ -n "$OLD_ID" ]; then
  docker tag "$OLD_ID" "zcloud-core:rollback-$TS" 2>/dev/null || true
fi
{
  echo "upgraded_at=$TS"
  echo "old_image_id=$OLD_ID"
  echo "old_image_tag=zcloud-core:rollback-$TS"
  echo "new_image=$NEW_IMAGE"
  echo "new_image_id=$(docker image inspect "$NEW_IMAGE" --format '{{.Id}}' 2>/dev/null)"
} > "$BAK/upgrade.json"

# 3) 备份数据：排除 socket/pid 与可再生日志
echo "[upgrade] 备份 data → $BAK/data.tar.gz"
tar -C "$ROOT" -czf "$BAK/data.tar.gz" \
    --exclude='data/**/*.sock' --exclude='data/**/*.pid' \
    --exclude='data/shared/dbaas/zcloud-log' \
    data || { echo "[upgrade][ERROR] 备份失败（容器已停止，可重新执行）" >&2; exit 1; }
tar -tzf "$BAK/data.tar.gz" >/dev/null 2>&1 \
  || { echo "[upgrade][ERROR] 备份不可读，停止升级" >&2; exit 1; }

cat > "$BAK/ROLLBACK.md" <<EOF
回滚步骤（升级后发现问题时）：
1. 停止容器：docker stop -t 300 $CNAME
2. 恢复镜像：docker tag zcloud-core:rollback-$TS $NEW_IMAGE
3. 恢复数据：rm -rf "$DATA" && tar -C "$ROOT" -xzf "$BAK/data.tar.gz"
4. 启动：docker compose up -d（数据卷保留）
5. 若 DB 版本已前进，需按 Flyway 版本回退说明处理（不自动降级 schema）
EOF

echo "[upgrade] 完成：备份 $BAK/data.tar.gz，回滚说明 $BAK/ROLLBACK.md"
echo "[upgrade] 旧镜像已保留为 zcloud-core:rollback-$TS"
