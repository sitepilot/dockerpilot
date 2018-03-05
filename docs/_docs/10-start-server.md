---
title: "Start server"
permalink: /docs/commands/start-server/
excerpt: "Start Dockerpilot server."
toc_label: "Content"
toc: true
---
You have to start the Dockerpilot server containers before you can access your applications. This command will start a database (MySQL) container along with a Nginx Proxy container which will redirect traffic to the right application based on the application domain name.
```shell
dp server:start
```

## Options

| Option | Description |
| --- | --- |
| --build | Rebuilds the Dockerpilot containers. |

```shell
dp server:start --option
```