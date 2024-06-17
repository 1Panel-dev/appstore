This is a API testing tool. 🚀

## Features

* Supported protocols: HTTP, gRPC, tRPC
* Multiple test report formats: Markdown, HTML, PDF, Stdout
* Mock Server in simple configuration, and Open API support
* Support converting to [JMeter](https://jmeter.apache.org/) files
* Response Body fields equation check or [eval](https://expr.medv.io/)
* Validate the response body with [JSON schema](https://json-schema.org/)
* Pre and post handle with the API request
* Run in server mode, and provide the [gRPC](pkg/server/server.proto) and HTTP endpoint
* [VS Code extension](https://github.com/LinuxSuRen/vscode-api-testing) support
* Multiple storage backends supported(Local, ORM Database, S3, Git, Etcd, etc.)
* [HTTP API record](https://github.com/LinuxSuRen/atest-ext-collector)
* Install in multiple use cases(CLI, Container, Native-Service, Operator, Helm, etc.)
* Monitoring integration with Prometheus, SkyWalking
