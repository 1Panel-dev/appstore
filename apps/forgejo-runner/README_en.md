## Introduction

Forgejo Runner is a daemon process used to connect to a Forgejo instance and run continuous integration (CI) jobs. This project is part of the Forgejo ecosystem and aims to provide Forgejo users with automation workflow capabilities similar to GitHub Actions. Its core value lies in offering native CI/CD functionality for self-hosted Forgejo instances. The target users are development teams and organizations that require automated build, test, and deployment processes, especially in scenarios where self-hosted solutions are needed.

## Features

- **Continuous Integration Job Execution**: Connects to a Forgejo instance as a daemon process, automatically pulls and executes CI/CD tasks defined in workflows, enabling developers to automate build, test, and deployment processes.
- **Multi-Architecture Support**: Officially supports running on Linux kernel-based operating systems, covering `amd64` and `arm64` architectures. Provides binary files and container images. Active development is underway for support of other architectures (such as s390x, powerpc64le, riscv64) and Windows.
- **Deep Integration with Forgejo**: As a native Actions runner for Forgejo, it seamlessly integrates into Forgejo's workflow system, allowing users to configure and manage automated tasks via the Forgejo interface.

## Docker Daemon Running Mode Risk Warning

### Docker outside of Docker (DooD)

In this mode, the Runner service container (i.e., the container created by this application) and the job container (i.e., the container that actually runs workflows) share the Docker environment with the host machine. This may lead to the following risks:
  - Any containers executed in Docker will be accessible to the job, service, and step containers. For example, if Forgejo or Forgejo Runner are run as containers on the same host, then job containers will be able to execute actions like docker kill, docker inspect, or docker exec on those containers.

  > ```yaml
  > jobs:
  >   docker-access-demo:
  >     runs-on: docker
  >       container:
  >         image: node:lts
  >       services:
  >         postgres:
  >           image: postgres:latest
  >           env:
  >             POSTGRES_PASSWORD: example
  >       steps:
  >        - run: docker run -d --name ubuntu ubuntu:latest
  > ```
  >   In the example above, the node, postgres, and ubuntu containers can all access other containers on the host

  - All storage on the host can be compromised by an Action performing volume mounts to newly created containers. For example, an action step such as docker run -v /:/host-mount ubuntu would make the entire host’s storage available to the container, including any on-disk secrets.
  - Artifacts that workflows create, such as new images created by docker build or containers created by docker run, will be left in the host’s docker daemon. Any future workflow execution on the same runner will be able to inspect these artifacts and could extract confidential information from them.
  - Resource Constraints configured on Forgejo Runner won’t have any impact on containers created from workflow actions.
  - Specific network configuration via Forgejo Runner’s container.network setting will not be applied to any containers created from workflows. Workflows would be able to define settings like --network=host to access the host operating system’s native network. This creates new security risks by exposing Local Network Resources to these containers.

### Docker in Docker (DinD)

Job containers in this mode (i.e., the containers that actually run the workflow) are isolated from the host's Docker environment, providing a certain level of security. However, it is still important to note:
  - If a Forgejo Runner is configured with runner.capacity greater than 1 to allow multiple concurrent tasks, or multiple Forgejo Runners are configured using the same DIND container.docker_host, then job containers will be able to view and interact with each other through the docker CLI. This could allow one to compromise the secrets of another running job container or interact with its files while it is running.
  - The DIND container will have its own storage, in its own /var/lib/docker directory within the container. This means that…
    - Images available on the host won’t be available on the DIND container until pulled within that container,
    - Extra storage may be used holding duplicate copies of images relative to the host,
    - The /var/lib/docker directory will be wiped if the DIND container is recreated (for example, for software upgrades to the docker:dind image) unless it is volume-mapped to a persistent volume, potentially impacting the performance of future workflow runs if image caching is relied upon.
    - If /var/lib/docker is volume-mapped to a persistent volume, it will need periodic maintenance such as docker system prune to ensure that the images and volumes do not grow indefinitely with out-of-date and irrelevant contents.
  - Artifacts that workflows create, such as new images created by docker build or containers created by docker run, will be left in the DIND container. Any future workflow execution on the same runner will be able to inspect these artifacts and could extract confidential information from them.
  - Resource Constraints configured on Forgejo Runner won’t have any impact on containers created from workflow actions. However, resources will be constrained to those configured on the created DIND container.

## Configuration and Usage Instructions

- **Forgejo Instance URL**: (e.g., `https://git.example.com`)
- **Registration Token**: Obtained from the "Create new runner" button. This token is used exclusively for the Runner instance and should differ from a Personal Access Token (PAT).
- **Runner Name**: It is recommended to set a descriptive, unique name for each Runner (e.g., ForgejoRunner-DataCenter-Purpose-Number).
- **Runner Labels**: Used to tag Runner instances. Workflows can select a Runner based on labels to execute tasks. The same label can be assigned to multiple Runner instances.
- Workflows run by default in a container based on `node:lts`. If other environments are required, they can be configured in the workflow.
  ```yaml
  jobs:
    test:
      runs-on: docker
      container:
        image: pytorch/pytorch:2.10.0-cuda13.0-cudnn9-runtime
        options: 
          -v /path/to/folder:/path/to/folder
      steps:
        - run: ls -al /path/to/folder
  ```
- Workflows will run in a container based on `node:lts` by default. If the Docker client is required, it needs to be installed manually
  ```yaml
  on:
    workflow_dispatch:
  jobs:
    test:
      runs-on: docker
      steps:
        - run: |
            curl -fsSL https://get.docker.com | bash
            docker -v
            docker ps
  ```