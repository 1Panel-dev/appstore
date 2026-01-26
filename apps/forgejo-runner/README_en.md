## Introduction

Forgejo Runner is a daemon process used to connect to a Forgejo instance and run continuous integration (CI) jobs. This project is part of the Forgejo ecosystem and aims to provide Forgejo users with automation workflow capabilities similar to GitHub Actions. Its core value lies in offering native CI/CD functionality for self-hosted Forgejo instances. The target users are development teams and organizations that require automated build, test, and deployment processes, especially in scenarios where self-hosted solutions are needed.

## Features

- **Continuous Integration Job Execution**: Connects to a Forgejo instance as a daemon process, automatically pulls and executes CI/CD tasks defined in workflows, enabling developers to automate build, test, and deployment processes.
- **Multi-Architecture Support**: Officially supports running on Linux kernel-based operating systems, covering `amd64` and `arm64` architectures. Provides binary files and container images. Active development is underway for support of other architectures (such as s390x, powerpc64le, riscv64) and Windows.
- **Deep Integration with Forgejo**: As a native Actions runner for Forgejo, it seamlessly integrates into Forgejo's workflow system, allowing users to configure and manage automated tasks via the Forgejo interface.

## Configuration and Usage Instructions

- **Choose a Version**: Two versions are currently available: the regular version (referred to as the DooD version) and the DinD version.
  - The DooD version has higher privileges and shares the Docker environment with the host machine. When workflows require container usage, the DooD version can manage containers within the Runner on the host machine.
  - The DinD version offers stronger security, with a Docker environment completely isolated from the host machine, minimizing privilege escalation risks.
  - If certain actions encounter insufficient permissions under the DinD version, first try enabling privileged mode. If errors persist, consider switching to the DooD version.
- **Forgejo Instance URL**: (e.g., `https://git.example.com`)
- **Registration Token**: Obtained from the "Create new runner" button. This token is used exclusively for the Runner instance and should differ from a Personal Access Token (PAT).
- **Runner Name**: A unique identifier to distinguish different Runner instances. Duplicate names are not allowed within the same namespace (repository or organization).
- **Runner Labels**: Used to tag Runner instances. Workflows can select a Runner based on labels to execute tasks. The same label can be assigned to multiple Runner instances.
