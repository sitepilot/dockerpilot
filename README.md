# Dockerpilot
Dockerpilot is a Docker based development/production server for web applications and uses Docker Swarm to deploy those applications to the cloud. Easily create, run, backup and manage your apps with simple commands.

## Example commands
#### dp server:(start|stop)
Dockerpilot will start and configure a HaProxy container which will redirect traffic to the right application based on the application domain name.

#### dp mysql:(start|stop)
Dockerpilot will start a MySQL server on the manager node.

#### dp mail:(start|stop)
Dockerpilot will start a mail relay server so that your apps can send email through, for example, Mailgun.

#### dp adminer:(start|stop)
Dockerpilot will start a simple database management app (Adminer).

#### dp portainer:(start|stop)
Dockerpilot will start a simple container management app (Portainer).

#### dp app:(create|start|stop|delete)
Manage Dockerpilot applications with a single command from anywhere on the system.

## Requirements
In order to use Dockerpilot you need to install the following software on your local machine or server:
* [Docker](https://www.docker.com/)
* [PHP >= 7.0](http://php.net)
* [Git](https://git-scm.com)
* [Composer](https://getcomposer.org)
* [Ansible (production servers only)](https://www.ansible.com/)

## Documentation
Full documentation - including installation docs - are available [here](https://sitepilot.github.io/dockerpilot/).