#!/bin/bash
service nginx reload

/usr/bin/dp-varnish-config

TIME=$(date +%s)
varnishadm -S /etc/varnish/secret vcl.load varnish_$TIME /etc/varnish/default.vcl
varnishadm -S /etc/varnish/secret vcl.use varnish_$TIME