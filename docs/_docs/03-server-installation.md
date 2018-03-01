---
title: "Server Installation"
permalink: /docs/server-installation/
excerpt: "How to install Dockerpilot on a server."
toc_label: "Content"
toc: true
---
Server installation is only tested on Ubuntu 16.04.
    
### Initial setup
1. Add Dockerpilot user `adduser dockerpilot`.
1. Give user admin privileges `usermod -aG sudo dockerpilot`.
1. Login as user `su - dockerpilot`.
1. Create ssh key `ssh-keygen`.
1. Add your public key to `.ssh/authorized_keys` to enable SSH login without password.
1. Change permissions `chmod 600 ~/.ssh/authorized_keys`.

### Install PHP 7.1
1. `sudo apt-get install -y python-software-properties`
1. `sudo add-apt-repository -y ppa:ondrej/php`
1. `sudo apt-get update -y`
1. `apt-cache pkgnames | grep php7.1`
1. Install the packages you need (e.g. `sudo apt-get install php7.1 php7.1-cli php7.1-curl php7.1-xml php7.1-mbstring php7.1-zip`)
1. Remove preinstalled Apache (otherwise Dockerpilot can't start): `sudo apt-get autoremove && sudo apt-get remove apache2*`.

### Install required packages
1. `sudo apt-get install -y zip`
1. [Install Docker on Ubuntu 16.04](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-on-ubuntu-16-04)
1. [Install Docker Compose on Ubuntu 16.04](https://www.digitalocean.com/community/tutorials/how-to-install-docker-compose-on-ubuntu-16-04)
1. [Install Composer on Ubuntu 16.04](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-16-04)

### Install Dockerpilot
1. `cd ~ && git clone git@github.com:sitepilot/dockerpilot.git`
1. `cd dp`
1. `composer install`
1. Run `php dp` to see a list of commands.