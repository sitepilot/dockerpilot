# Serverpilot Docker Development/Production Server

This is a Docker based development and production environment for web applications.

## Requirements

* [Docker](https://www.docker.com/)
* [docker-compose](https://docs.docker.com/compose/)
* PHP >= 7.0 on your host computer.

## Setup

1. `git clone https://github.com/sitepilot/serverpilot.git`
2. `cd serverpilot`
3. `php sp` to see a list of commands.

## Start server (nginx-proxy with letsencrypt)

1. Run `php sp server:start`.

This will start a nginx proxy server with Letsencrypt support. The proxy will redirect trafic to the right application container based on the domainname (defined in the .env file of each application).

## Start mailcatcher

1. Run `php sp mailcatcher:start`.

This command will start Mailcatcher which is listening at address serverpilot-mailcatcher:1025 for smtp connections. Navigate to <docker-ip>:1080 for the webinterface.

## Create a new application

1. `php sp app:create`
2. Choose an application name and a template.
3. Modify the generated .env file (in apps/your-app) to your needs.

## Start an application

1. `php sp app:start`
2. Choose the application you would like te start.
3. Edit the hosts file on your computer and add the domains you've defined in the application .env file (under APP_DOMAINS).
3. Navigate to the application domain in your browser (on the host machine).
