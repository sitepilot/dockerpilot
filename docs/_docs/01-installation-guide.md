---
title: "Installation Guide"
permalink: /docs/installation-guide/
excerpt: "How to install and setup Dockerpilot."
toc_label: "Content"
toc: true
---
Dockerpilot is a Docker based development and production server for web applications. 

## Requirements
You need to install the following software on your machine/server to use Dockerpilot.
* [Docker](https://www.docker.com/)
* [Docker Compose](https://docs.docker.com/compose/)
* [PHP >= 7.0](http://php.net)
* [Git](https://git-scm.com)

You can find the installation steps of the required software underneath.

### MacOS
1. [Install Docker & Docker Compose](https://docs.docker.com/docker-for-mac/install/)
1. MacOS High Sierra comes with build in PHP 7.1 so no need to install PHP. Run `php -v` from a terminal to verify it works.
1. Check if git is installed with `git --version`. If you don’t have it installed already, it will prompt you to install it.

Proceed to [local installation](/docs/local-installation/) if you've installed all required software. 

### Windows
#### Install Docker & Git
1. [Install Docker & Docker Compose](https://docs.docker.com/docker-for-windows/install/), run `docker --version` and `docker-compose --version` from a command prompt to verify it is installed.
1. [Install Git](https://git-scm.com/download/win), run `git --version` from a command prompt to verify it is installed.

#### Install PHP7.1
1. Download the latest PHP7.1 (non-thread safe version) zip file from [here](https://windows.php.net/download/).
1. Extract the contents of the zip file into C:\PHP7.1.
1. Copy C:\PHP7.1\php.ini-development to C:\PHP7.1\php.ini.
1. Open the newly copied C:\PHP7.1\php.ini in a text editor.
1. Scroll down to “Directory in which the loadable extensions (modules) reside.” and uncomment: `extension_dir = “ext”`.
1. Scroll down to the DLL extensions section and uncomment the extensions you want to use.
1. Add C:\PHP7.1 to system path environment variable or run `if exist C:\PHP7.1 setx PATH C:\PHP7.1` in a command prompt.

Proceed to [local installation](/docs/local-installation/) if you've installed all required software. 

### Ubuntu 16.04 (Server)
You can find a server installation guide [here](/docs/server-installation/). You can also use this guide for installation of Dockerpilot on Ubuntu Desktop 16.04.