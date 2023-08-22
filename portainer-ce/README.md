## About Portainer

Portainer Community Edition (CE) is our foundation. With over half a million regular users, CE is a powerful, open source toolset that allows you to easily build and manage containers in Docker, Docker Swarm, Kubernetes and Azure ACI.

Portainer hides the complexity of managing containers behind an easy-to-use UI. By removing the need to use the CLI, write YAML or understand manifests, Portainer makes deploying apps and troubleshooting problems so easy that anyone can do it.

## Portainer architecture

- **Overview of Portainer architecture**

Portainer consists of two elements: the Portainer Server and the Portainer Agent. Both run as lightweight containers on your existing containerized infrastructure. The Portainer Agent should be deployed to each node in your cluster and configured to report back to the Portainer Server container.

A single Portainer Server will accept connections from any number of Portainer Agents, providing the ability to manage multiple clusters from one centralized interface. To do this, the Portainer Server container requires data persistence. The Portainer Agents are stateless, with data being shipped back to the Portainer Server container.

![The Portainer architecture](https://2914113074-files.gitbook.io/~/files/v0/b/gitbook-x-prod.appspot.com/o/spaces%2FiZWHJxqQsgWYd9sI88sO%2Fuploads%2FZDidVsHkkHwy97bVrRdM%2Fportainer-architecture-detailed.png?alt=media&token=a31751c5-f5d2-47ca-be2e-0d7f20f182ef)

- We don't currently support running multiple instances of the Portainer Server container to manage the same clusters. We recommend running the Portainer Server on a specific management node, with Portainer Agents deployed across the remaining nodes.

- **Agent vs Edge Agent**

In standard deployments, the central Portainer Server instance and any environments it manages are assumed to be on the same network, that is, Portainer Server and the Portainer Agents are able to seamlessly communicate with one another. However, in configurations where the remote environments are on a completely separate network to Portainer Server, say, across the internet, historically we would have been unable to centrally manage these devices.

With the new Edge Agent, we altered the architecture. Rather than the Portainer Server needing seamless access to the remote environment, only the remote environments need to be able to access the Portainer Server. This communication is performed over an encrypted TLS tunnel. This is important in Internet-connected configurations where there is no desire to expose the Portainer Agent to the internet.

- **Security and compliance**

Portainer runs exclusively on your servers, within your network, behind your own firewalls. As a result, we do not currently hold any SOC or PCI/DSS compliance because we do not host any of your infrastructure. You can even run Portainer completely disconnected (air-gapped) without any impact on functionality.

While we do (optionally) collect anonymous usage analytics from Portainer installations, we remain compliant with GDPR. Data collection can be disabled when you install the product, or at any time after that. If your installation is air-gapped, collection will silently fail without any adverse effects.

## Quick Start
- [Official Website](https://www.portainer.io/)
- [Document](https://docs.portainer.io/)
- [Dockerhub](https://hub.docker.com/r/portainer/portainer-ce/tags)