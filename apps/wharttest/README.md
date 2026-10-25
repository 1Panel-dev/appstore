# WHartTest

WHartTest 是一款 AI 驱动的测试自动化平台：上传需求文档即可自动生成功能 / 接口 / UI 测试用例，支持自动执行、失败归因与 AI 修复建议。内置 LangChain/LangGraph 编排、RAG 知识库与 MCP 工具调用。

- 官网：https://wharttest.mgdaas.com/
- 文档：https://docs.wharttest.mgdaas.com/
- GitHub：https://github.com/MGdaasLab/WHartTest

## 说明

- 默认管理员账号：`admin`，密码为安装时填写的「管理员密码」
- 首次启动需要初始化数据库，请等待 1-2 分钟后访问
- 本编排不含 weixin-plugin-host 与 playwright-mcp（按需参照上游 docker-compose 自行添加）
- Skills 模块权限较高，建议仅在内网或可信环境使用
