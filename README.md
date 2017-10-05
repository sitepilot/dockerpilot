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

## Create a new application

1. `php sp app:create`
2. Choose an application name and a template.
3. Modify the .env file to your needs.

## Start an application

1. `php sp app:start`
2. Choose the application you would like te start.
3. Edit the hosts file on your computer and add the domains you've defined in the application .env file (under APP_DOMAINS).
3. Navigate to the application domain in your browser (on the host machine).
