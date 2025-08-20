## Introduction

**Bytebase** is a database CI/CD solution for developers and DBAs. It is the only database CI/CD project included in the CNCF Landscape and Platform Engineering.

## The Bytebase Suite Consists of the Following Tools:

- **Bytebase Console**: A web-based GUI for developers and DBAs to manage the database development lifecycle.
- **Bytebase API**: Provides gRPC and RESTful APIs to operate various aspects of Bytebase.
- **Bytebase CLI**: A CLI tool to help developers integrate database changes into existing CI/CD workflows.
- **Bytebase GitHub App and SQL Review GitHub Action**: A GitHub App and GitHub Action to detect SQL anti-patterns and enforce consistent SQL style guidelines during pull requests.
- **Terraform Bytebase Provider**: A Terraform provider that enables teams to manage Bytebase resources via Terraform. A typical setup includes teams using Terraform to configure database instances from cloud providers and then preparing these instances for application use with the Bytebase provider.
