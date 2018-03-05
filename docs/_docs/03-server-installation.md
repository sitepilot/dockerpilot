---
title: "Server Installation"
permalink: /docs/server-installation/
excerpt: "How to install Dockerpilot on a server."
toc_label: "Content"
toc: true
---
Server installation is only tested and supported on Ubuntu 16.04.
    
### Initial setup
You have to run Dockerpilot as a non-root user to prevent file permission errors and security issues. To add a new user to the system run the following commands:
1. Add Dockerpilot user `adduser [username]`.
1. Give user admin privileges `usermod -aG sudo [username]`.
1. Login as the new user `su - [username]`.

### Install PHP 7.1
1. `sudo apt-get install -y python-software-properties`
1. `sudo add-apt-repository -y ppa:ondrej/php`
1. `sudo apt-get update -y`
1. `apt-cache pkgnames | grep php7.1`
1. Install the packages you need (e.g. `sudo apt-get install php7.1 php7.1-cli php7.1-curl php7.1-xml php7.1-mbstring php7.1-zip`)
1. Remove preinstalled Apache (otherwise Dockerpilot can't start): `sudo apt-get autoremove && sudo apt-get remove apache2*`.

### Install required packages
1. [Install Docker on Ubuntu 16.04](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-on-ubuntu-16-04)
1. [Install Docker Compose on Ubuntu 16.04](https://www.digitalocean.com/community/tutorials/how-to-install-docker-compose-on-ubuntu-16-04)
1. [Install Composer on Ubuntu 16.04](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-16-04)

### Install Dockerpilot
1. Clone the latest version of Dockerpilot to the server:
```
cd ~/ && git clone -b master https://github.com/sitepilot/dockerpilot.git dockerpilot && chmod +x dockerpilot/source/setup.sh && dockerpilot/source/setup.sh
```
1. Navigate into the Dockerpilot folder: `cd ~/dockerpilot`.
1. Edit `config.php`, example: `nano config.php`.
1. Change *SERVER_USER* to the username you are using to login to the server.
1. Change *MYSQL_ROOT_PASSWORD* to a secure password ([generate a secure password here](https://randomkeygen.com/)).
1. Start a new terminal or login again and type `dp` to see a list of commands.