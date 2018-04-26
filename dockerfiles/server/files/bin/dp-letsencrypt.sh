#!/bin/bash
# Bash script to create/add Let's Encrypt SSL to ServerPilot app
# by Rudy Affandi (2016)
# Edited Aug 14, 2016

# Todo
# 1. Generate certificate
# /usr/local/bin/certbot-auto certonly --webroot -w /srv/users/$username/apps/appname/public -d appdomain.tld
# 2. Generate appname.ssl.conf file
# 3. Restart nginx
# service nginx-sp restart
# 4. Confirm that it's done and show how to do auto-renew via CRON

# Settings
certbotfolder=/usr/local/bin/certbot-auto
appfolder=/srv/users/$username/apps
conffolder=/etc/nginx/vhosts.d
acmeconfigfolder=/etc/nginx/letsencrypt.d
acmeconfigfile="$acmeconfigfolder/letsencrypt-acme-challenge.conf"

# Make sure this script is run as root
if [ "$EUID" -ne 0 ]
then
    echo ""
	echo "Please run this script as root."
	exit
fi

echo ""
echo ""
echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-="
echo ""
echo "  Let's Encrypt SSL Certificate Generator"
echo "  For Dockerpilot-managed server instances"
echo ""
echo "  Written by Nick Jansen (2018)"
echo "  https://github.com/sitepilot/"
echo ""
echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-="
echo ""
echo ""
echo "Please enter your app name:"
read appname
echo ""
echo "Please enter the System User name for the app:"
read username
echo ""
echo "Please enter all the domain names and sub-domain names"
echo "you would like to use, separated by space"
read domains

# Assign domain names to array
APPDOMAINS=()
for domain in $domains; do
   APPDOMAINS+=("$domain")
done

# Assign domain list to array
APPDOMAINLIST=()
for domain in $domains; do
   APPDOMAINLIST+=("-d $domain")
done

# Generate certificate
echo ""
echo ""
echo "Generating SSL certificate for $appname"
echo ""

letsencrypt certonly --webroot -w /srv/users/$username/apps/$appname/public ${APPDOMAINLIST[@]}

certFile="/etc/letsencrypt/live/${APPDOMAINS[0]}/fullchain.pem"
if [ -f $certFile ]; then
    # Generate nginx configuration file
    configfile=$conffolder/$appname.ssl.conf
    echo ""
    echo ""
    echo "Creating configuration file for $appname in the $conffolder"
    touch $configfile
    echo "server {" | tee $configfile
    echo "   listen 443 ssl http2;" | tee -a $configfile
    echo "   listen [::]:443 ssl http2;" | tee -a $configfile
    echo "   server_name " | tee -a $configfile
       for domain in $domains; do
          echo -n $domain" " | tee -a $configfile
       done
    echo ";" | tee -a $configfile
    echo "" | tee -a $configfile
    echo "   ssl on;" | tee -a $configfile
    echo "" | tee -a $configfile
    echo "   # letsencrypt certificates" | tee -a $configfile
    echo "   ssl_certificate      /etc/letsencrypt/live/${APPDOMAINS[0]}/fullchain.pem;" | tee -a $configfile
    echo "   ssl_certificate_key  /etc/letsencrypt/live/${APPDOMAINS[0]}/privkey.pem;" | tee -a $configfile
    echo "" | tee -a $configfile
    echo "    #SSL Optimization" | tee -a $configfile
    echo "    ssl_session_timeout 1d;" | tee -a $configfile
    echo "    ssl_session_cache shared:SSL:20m;" | tee -a $configfile
    echo "    ssl_session_tickets off;" | tee -a $configfile
    echo "" | tee -a $configfile
    echo "    # modern configuration" | tee -a $configfile
    echo "    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;" | tee -a $configfile
    echo "    ssl_prefer_server_ciphers on;" | tee -a $configfile
    echo "" | tee -a $configfile
    echo "    ssl_ciphers 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!3DES:!MD5:!PSK';" | tee -a $configfile
    echo "" | tee -a $configfile
    echo "    # OCSP stapling" | tee -a $configfile
    echo "    ssl_stapling on;" | tee -a $configfile
    echo "    ssl_stapling_verify on;" | tee -a $configfile
    echo "" | tee -a $configfile
    echo "    # verify chain of trust of OCSP response" | tee -a $configfile
    echo "    ssl_trusted_certificate /etc/letsencrypt/live/${APPDOMAINS[0]}/chain.pem;" | tee -a $configfile
    echo "    #root directory and logfiles" | tee -a $configfile
    echo "    root /srv/users/$username/apps/$appname/public;" | tee -a $configfile
    echo "" | tee -a $configfile
    echo "    access_log /srv/users/$username/log/$appname/${appname}_nginx.access.log main;" | tee -a $configfile
    echo "    error_log /srv/users/$username/log/$appname/${appname}_nginx.error.log;" | tee -a $configfile
    echo "" | tee -a $configfile
    echo "    #proxyset" | tee -a $configfile
    echo "    proxy_set_header Host \$host;" | tee -a $configfile
    echo "    proxy_set_header X-Real-IP \$remote_addr;" | tee -a $configfile
    echo "    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;" | tee -a $configfile
    echo "    proxy_set_header X-Forwarded-SSL on;" | tee -a $configfile
    echo "    proxy_set_header X-Forwarded-Proto \$scheme;" | tee -a $configfile
    echo "" | tee -a $configfile
    echo "    #includes" | tee -a $configfile
    echo "    include /etc/nginx-sp/vhosts.d/$appname.d/*.conf;" | tee -a $configfile
    echo "    include $acmeconfigfolder/*.conf;" | tee -a $configfile
    echo "}" | tee -a $configfile

    # Wrapping it up
    echo ""
    echo ""
    echo "We're almost done here. Reloading nginx..."
    dp-reload
    echo ""
    echo ""
    echo ""
    echo ""
    echo "Your Let's Encrypt SSL certificate has been installed. Please update your .htaccess to force HTTPS on your app"
    echo ""
    echo "To enable auto-renewal, add the following to your crontab:"

    echo "0 */12 * * * letsencrypt renew && service nginx-sp reload"

    echo ""
    echo ""
    echo "Cheers!"
else
    echo "Could not create certificate, please check DNS settings."
    exit 1
fi;