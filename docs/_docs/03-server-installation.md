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

### Install Docker
1. First, install required software packages: 
   ```
   sudo apt-get install -y software-properties-common apt-transport-https curl git
   ```
1. Add the GPG key for the official Docker repository to the system:
   ```
   curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -`.
   ```
1. Add the Docker repository to APT sources: 
   ```
   sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
   ```
1. Next, update the package database with the Docker packages from the newly added repo: 
   ```
   sudo apt-get update
   ```
1. Install Docker: 
   ```
   sudo apt-get install -y docker-ce`
   ```
1. Finally, avoid typing sudo whenever you have to run the docker command: 
   ```
   sudo usermod -aG docker ${USER}`.
   ```
   
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