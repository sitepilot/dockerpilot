Description
==================
This Image serves the purpose of testing your Application with Apache 2.4.7 and Php-Fpm-7 before upgrading your Apache and Php for optimal performance. This image reduces the load of Apache by using Php-Fpm instead of mod-php.Using mod_php each Apache worker has the entire PHP interpreter loaded into it. Because Apache needs one worker process per incoming request, you can quickly end up with hundreds of Apache workers in use, each with their own PHP interpreter loaded, consuming huge amounts of memory.To address this issue Php is configured as a CGI process.Configuration has been crafted  keeping in view CGI application vulnerability.This image outputs the logs to stdout and runs apache as site user.The image is immune to *httpoxy vulnerability*.

*The Image size is 87.82 MB and uses alpine3.5 as base image*

To Start the Container
-------------------------------
```docker run --name apache smtripat/alpine-apache-php-fpm:latest```

To make the conatiner listen on host port 80
```docker run -p 80:80 -d --name apache smtripat/alpine-apache-php-fpm:latest```

Docker Compose
------------------------
```
apache: 
   image: smtripat/alpine-apache-php-fpm:latest
```
Hosting A Web Application
------------------------------------
*Docroot is the path where the code directory is mounted.*
```
docker run --name apache -v /path/to/docroot:/var/www/localhost/htdocs smtripat/alpine-apache-php-fpm:latest
```
*Mapping the Port 80 of the container to your local machine or host machine port 8080(or any other port).*
```
docker run -p 8080:80 --name apache -v /path/to/docroot:/var/www/localhost/htdocs smtripat/alpine-apache-php-fpm:latest
```
*Using Docker-Compose*
```
apache:
  image: smtripat/alpine-apache-php-fpm:latest
  volumes:
    - /path/to/docroot:/var/www/localhost/htdocs
```

To get Shell Access inside the container
------------------------------------
To get access as site user
```docker exec -it <container-name> su site````

To get access as root user
```docker exec -it <container-name> /bin/ash```

> Please free feel free to raise issues.










