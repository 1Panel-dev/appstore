#!/bin/bash
# 1Panel 卸载钩子：应用目录随后会被删除，数据必须先搬出去
set -uo pipefail
ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
PRESERVE=${ZCLOUD_PRESERVE_DIR:-/opt/zcloud-preserved}
TS=$(date +%Y%m%d%H%M%S)
DEST="$PRESERVE/zcloud-$TS"

# 同秒二次卸载会撞名，追加后缀避免与上一次备份混在一起
[ -e "$DEST" ] && DEST="$DEST-$RANDOM"

# 只有"确实装过"才值得保留：init.sh 的闸门失败时也会留下空 data 树，
# 把空目录当数据备份会让运维误以为有可回灌内容。
if [ -d "$ROOT/data" ] && { [ -s "$ROOT/data/mysql/ibdata1" ] || [ -d "$ROOT/data/shared/dbaas" ]; }; then
  mkdir -p "$PRESERVE" || {
    echo "[uninstall][ERROR] 无法创建保留目录 $PRESERVE，卸载中止（避免丢数据）" >&2
    exit 1
  }
  # 同分区 mv 是秒级；跨分区退化为复制，复制失败则中止卸载
  if mv "$ROOT/data" "$DEST" 2>/dev/null; then
    :
  else
    mkdir -p "$DEST"
    cp -a "$ROOT/data/." "$DEST/" || {
      echo "[uninstall][ERROR] 数据复制失败，卸载中止（原数据仍在 $ROOT/data）" >&2
      exit 1
    }
    rm -rf "$ROOT/data"
  fi
  echo "$DEST" > "$PRESERVE/LAST_BACKUP"
  echo "[uninstall] 数据已保留: $DEST"
  echo "[uninstall] 恢复方式: mv $DEST $ROOT/data 后重新安装"
  # 卸载会累积备份（每次 5G+）：把现存份数与占用打出来，避免下次安装被磁盘闸门拦住
  echo "[uninstall] 现有保留备份（可在确认无用后删除旧的）："
  du -sh "$PRESERVE"/zcloud-* 2>/dev/null | tail -5
else
  echo "[uninstall] 无有效 data（目录不存在或为空树），跳过保留"
fi
rm -rf "$ROOT/tmp" 2>/dev/null || true
