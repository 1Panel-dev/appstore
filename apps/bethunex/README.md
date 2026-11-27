## 产品介绍

Bethune X 是面向数据库全生命周期的云管理平台，提供实例纳管、监控告警、巡检与智能问答能力，支持 MySQL、Oracle、PostgreSQL、Redis、MongoDB、openGauss、达梦、OceanBase 等主流数据库。

本应用为社区版单容器形态：平台服务、MySQL、proxy、AI 助手与 Milvus 等 42 个进程运行在同一个容器内，适合单机快速部署与验证。

## 主要功能

- 数据库实例纳管与统一视图
- 监控指标采集、仪表盘与告警通知
- 自动化巡检与健康报告
- AI 助手（数据库问答与运维建议）

官方网站：https://bethunex.enmotech.com/

## 安装要求

- 磁盘 ≥25G，内存 ≥16G（安装前请在「高级设置 → 资源限制」里配置，内存不足会阻断安装）
- 首次启动约 10–12 分钟，请耐心等待

## 访问与默认口令

- 平台地址：`http://<宿主 IP>:8080/enmoLogin`
- 普通用户：`sysuser` / `A3bDf#7Wz9`
- 管理用户：`sysadmin` / `N7tK8Wz#eR4`
- MySQL：`<宿主 IP>:3306`，root 口令固定 `dbaas@123`
- ⚠️ 以上均为默认口令，安装后请立即修改
