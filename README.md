# Dockerpilot
Dockerpilot is a Docker based development/production server for web applications. Easily create, run, backup and manage web applications with simple commands and without the knowledge of Docker or webserver configuration.

## Example commands
#### dp server:(start|stop)
Dockerpilot will start and configure a Nginx Proxy container which will redirect traffic to the right application based on the application domain name.

#### dp app:(create|start|stop|delete)
Manage Dockerpilot applications with a single command from anywhere on the system.

#### dp app:(cmd|login)
Login or run a single command in an application you've created.

#### dp app:(backup|restore)
Backup and restore your applications.

#### dp wp:(install|update)
Install and update WordPress in your application.

## Requirements
In order to use Dockerpilot you need to install the following software on your local machine or server:
* [Docker](https://www.docker.com/)
* [Docker Compose](https://docs.docker.com/compose/)
* [PHP >= 7.0](http://php.net)
* [Git](https://git-scm.com)
* [Composer](https://getcomposer.org)

## Documentation
Full documentation - including installation docs - are available [here](https://sitepilot.github.io/dockerpilot/).