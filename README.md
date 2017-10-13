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

## Configure UFW (Ubuntu 14.04 and 16.04)
Ubuntu ships with a very nice and simple frontend for iptables called ufw (uncomplicated firewall). Ufw makes it possible to setup a firewall without having to fully understand iptables itself. When you however are using Docker and you want to combine Docker with the ufw service. Things do get complicated.

The docker service talks directly to iptables for networking, basically bypassing everything that’s getting setup in the ufw utility and therefore ignoring the firewall. Additional configuration is required to prevent this behavior. The official Docker documentation however, seems to be incomplete.

1. Edit ufw config `sudo nano /etc/default/ufw`
2. Set `DEFAULT_FORWARD_POLICY="ACCEPT"`
3. Reload ufw `sudo ufw reload`
4. Allow port 2375 `sudo ufw allow 2375/tcp`
5. Edit `sudo nano /etc/default/docker`
6. Uncomment DOCKER_OPTS and add --iptables=false `DOCKER_OPTS="--dns 8.8.8.8 --dns 8.8.4.4 —iptables=false"`
7. Edit / create `/etc/docker/daemon.json`
8. Add `{ "iptables": false }`
9. Run `service docker restart`
10. Edit `sudo nano /etc/ufw/before.rules`
11. Add the following filter:
```
*nat
:POSTROUTING ACCEPT [0:0]
-A POSTROUTING ! -o docker0 -s 172.17.0.0/16 -j MASQUERADE
COMMIT
```
12. Reboot `sudo reboot now`
13. Allow ports `sudo ufw allow http` (allow http, https and port 2222 for sftp)

Source: https://svenv.nl/unixandlinux/dockerufw/

## Install PHP 7.1 on host machine (Ubuntu)
1. `sudo apt-get install -y python-software-properties`
2. `sudo add-apt-repository -y ppa:ondrej/php`
3. `sudo apt-get update -y`
4. `apt-cache pkgnames | grep php7.1`
5. Install the packages you need (e.g. `sudo apt-get install php7.1 php7.1-curl php7.1-xml php7.1-mbstring php7.1-zip`)
