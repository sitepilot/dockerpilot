# Dockerpilot Server

Dockerpilot is a Docker based development/production server for web applications.

## Requirements
You need to install the following tools on your machine/server to use Dockerpilot.
* [Docker](https://www.docker.com/)
* [Docker Compose](https://docs.docker.com/compose/)
* [PHP >= 7.0](http://php.net)
* [Git](https://git-scm.com)
* [Composer](https://getcomposer.org)

## Local Installation
1. `cd ~ && git clone git@github.com:sitepilot/dockerpilot.git`
2. `cd dp`
3. `composer install`
4. Run `php dp` to see a list of commands.

### Update hosts file
You have to update your hosts file for each application you create.

Example:
```
127.0.0.1 myapp.local
```

In order to use the build in tools (MailCatcher and Adminer) you need to add the following lines to your hosts file:
```
127.0.0.1 adminer.local
127.0.0.1 mailcatcher.local
```

Hosts file location:
```
Linux: /etc/hosts
MacOS: /etc/hosts
Windows: C:\Windows\System32\drivers\etc\hosts
```

## Server Installation (Ubuntu 16.04)

### Initial setup
1. Add Dockerpilot user `adduser dp`.
2. Give user admin privileges `usermod -aG sudo dp`.
3. Login as user `su - dp`.
4. Create ssh key `ssh-keygen`.
5. Add your public key to `.ssh/authorized_keys` to enable SSH login without password.
6. Change permissions `chmod 600 ~/.ssh/authorized_keys`.

### Install PHP 7.1
1. `sudo apt-get install -y python-software-properties`
2. `sudo add-apt-repository -y ppa:ondrej/php`
3. `sudo apt-get update -y`
4. `apt-cache pkgnames | grep php7.1`
5. Install the packages you need (e.g. `sudo apt-get install php7.1 php7.1-cli php7.1-curl php7.1-xml php7.1-mbstring php7.1-zip`)
6. Remove preinstalled Apache (otherwise Dockerpilot can't start): `sudo apt-get autoremove && sudo apt-get remove apache2*`.

### Install zip
1. `sudo apt-get install -y zip`

### Install Docker
[Tutorial on DigitalOcean](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-on-ubuntu-16-04)

### Install Docker Compose
[Tutorial on DigitalOcean](https://www.digitalocean.com/community/tutorials/how-to-install-docker-compose-on-ubuntu-16-04)

### Install Composer
[Tutorial on DigitalOcean](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-16-04)

### Install Dockerpilot
1. `cd ~ && git clone git@github.com:sitepilot/dockerpilot.git`
2. `cd dp`
3. `composer install`
4. Run `php dp` to see a list of commands.

## Run from everywhere 
To run Dockerpilot (with `dp`) from anywhere on the system you have to update your environment. 

#### MacOS / Linux:
1. Open Terminal.
2. Navigate to `/path/to/dockerpilot`.
3. Run `chmod +x setup.sh && ./setup.sh`.
4. Open a new terminal and type `dp` to verify that it works.

#### Windows
1. Run `setup.bat`.
2. Open a new command prompt and type `dp` to verify that it works.

## Commands

To get a list of all Dockerpilot commands run `php dp`.

### Start server (nginx-proxy with letsencrypt)

1. Run `php dp server:start`.

This will start a nginx proxy server with Letsencrypt support. The proxy will redirect traffic to the right container based on the domain (defined in the .env file of each application).

### Create a new application

1. `php dp app:create`
2. Choose an application name and a stack.
3. Modify the generated .env file (in apps/your-app) to your needs.

### Start an application

1. `php dp app:start`
2. Choose the application you would like to start.
3. Edit the hosts file on your computer and add the domains you've defined in your application .env file (under APP_DOMAINS).
3. Navigate to the application domain in your browser.

### Start mailcatcher

1. Run `php dp mailcatcher:start`.

This command will start Mailcatcher which is listening at address dp-mailcatcher:1025 for smtp connections. Navigate to mailcatcher.local for the webinterface.