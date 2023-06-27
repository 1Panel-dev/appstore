Apache Doris 是一个基于 MPP 架构的高性能、实时的分析型数据库，以极速易用的特点被人们所熟知，仅需亚秒级响应时间即可返回海量数据下的查询结果，不仅可以支持高并发的点查询场景，也能支持高吞吐的复杂分析场景。基于此，Apache Doris 能够较好的满足报表分析、即席查询、统一数仓构建、数据湖联邦查询加速等使用场景，用户可以在此之上构建用户行为分析、AB 实验平台、日志检索分析、用户画像分析、订单分析等应用。
Apache Doris is a high-performance, real-time analytical database based on MPP architecture, known for its extreme speed and ease of use. It only requires a sub-second response time to return query results under massive data and can support not only high-concurrent point query scenarios but also high-throughput complex analysis scenarios. All this makes Apache Doris an ideal tool for scenarios including report analysis, ad-hoc query, unified data warehouse, and data lake query acceleration. On Apache Doris, users can build various applications, such as user behavior analysis, AB test platform, log retrieval analysis, user portrait analysis, and order analysis.

前期环境准备
需在宿主机执行如下命令
`sysctl -w vm.max_map_count=2000000`


查看 FE 运行状态
你可以通过下面的命令来检查 Doris 是否启动成功
`curl http://127.0.0.1:8030/api/bootstrap`
这里 IP 和 端口分别是 FE 的 IP 和 http_port（默认8030），如果是你在 FE 节点执行，直接运行上面的命令即可。
如果返回结果中带有 "msg":"success" 字样，则说明启动成功。
你也可以通过 Doris FE 提供的Web UI 来检查，在浏览器里输入地址
http:// fe_ip:8030


使用mysql客户端连接
初始账号： root
初始密码： 留空