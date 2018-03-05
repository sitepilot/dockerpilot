---
title: "Local Installation"
permalink: /docs/local-installation/
excerpt: "How to install Dockerpilot on your local machine."
toc_label: "Content"
toc: true
---

Clone the repository to your computer to run Dockerpilot on your local machine:
1. Install [required software](/docs/installation-guide/) on your local machine.
1. Clone the repository: `git clone -b master git@github.com:sitepilot/dockerpilot.git dockerpilot`
1. Navigate into the Dockerpilot folder: `cd dockerpilot`
1. Run `source/setup.sh` on Linux or `source/setup.bat` on Windows.
1. Open a new Terminal or Command Prompt and type `dp` to see a list of commands.

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