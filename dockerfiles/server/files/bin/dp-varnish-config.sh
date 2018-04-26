#!/bin/bash
echo "Preparing Varnish config, please wait..."
sleep 3

vclFileBackend="/etc/varnish/all_backend.vcl"
vclFileReceive="/etc/varnish/all_receive.vcl"
if [ -f $vclFileBackend ]; then rm $vclFileBackend; fi
if [ -f $vclFileReceive ]; then rm $vclFileReceive; fi
touch $vclFileBackend
touch $vclFileReceive

for f in /etc/nginx/vhosts.d/*.conf
do
    filename=$(basename -- "$f")
    app="${filename%.*}"
    ip=$(getent hosts "$app" | cut -d' ' -f1 | sort -u | tail -1)

    if [ ! -z "$ip" ]; then
        echo "backend $app { .host = \"$ip\"; .port = \"81\"; }" >> $vclFileBackend
        echo "if (req.http.x-backend ~ \"(?i)^(www.)?$app$\") { set req.backend_hint = $app; }" >> $vclFileReceive
    fi
done