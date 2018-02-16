# Serverpilot Docker Server

Serverpilot is a Docker based development/production server for web applications.

## Requirements
* [Docker](https://www.docker.com/)
* [docker-compose](https://docs.docker.com/compose/)
* [PHP >= 7.0](http://php.net)
* [Git](https://git-scm.com)
* [Composer](https://getcomposer.org)

### Local Setup
1. `cd ~ && git clone git@github.com:sitepilot/serverpilot.git`
2. `cd serverpilot`
3. `composer install`
4. Run `php sp` to see a list of commands.

## Server Setup (Ubuntu 16.04)

### Initial production server setup
1. Add Serverpilot user `adduser serverpilot`.
2. Give user admin privileges `usermod -aG sudo serverpilot`.
3. Login as user `su - serverpilot`.
4. Create ssh key `ssh-keygen`.
5. Add your public key to `.ssh/authorized_keys` to enable SSH login without password.
6. Change permissions `chmod 600 ~/.ssh/authorized_keys`.

### Install PHP 7.1
1. `sudo apt-get install -y python-software-properties`
2. `sudo add-apt-repository -y ppa:ondrej/php`
3. `sudo apt-get update -y`
4. `apt-cache pkgnames | grep php7.1`
5. Install the packages you need (e.g. `sudo apt-get install php7.1 php7.1-cli php7.1-curl php7.1-xml php7.1-mbstring php7.1-zip`)
6. Remove preinstalled Apache (otherwise Serverpilot can't start): `sudo apt-get autoremove && sudo apt-get remove apache2*`.

### Install zip
1. `sudo apt-get install -y zip`

### Install Docker
[Tutorial on DigitalOcean](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-on-ubuntu-16-04)

### Install Docker Compose
[Tutorial on DigitalOcean](https://www.digitalocean.com/community/tutorials/how-to-install-docker-compose-on-ubuntu-16-04)

### Install Composer
[Tutorial on DigitalOcean](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-16-04)

## Commands

### Start server (nginx-proxy with letsencrypt)

1. Run `php sp server:start`.

This will start a nginx proxy server with Letsencrypt support. The proxy will redirect traffic to the right container based on the domain (defined in the .env file of each application).

### Create a new application

1. `php sp app:create`
2. Choose an application name and a stack.
3. Modify the generated .env file (in apps/your-app) to your needs.

### Start an application

1. `php sp app:start`
2. Choose the application you would like to start.
3. Edit the hosts file on your computer and add the domains you've defined in your application .env file (under APP_DOMAINS).
3. Navigate to the application domain in your browser.

### Start mailcatcher

1. Run `php sp mailcatcher:start`.

This command will start Mailcatcher which is listening at address sp-mailcatcher:1025 for smtp connections. Navigate to <docker-ip>:1080 for the webinterface.

