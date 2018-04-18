vcl 4.0;

backend default {
    .host = "127.0.0.1";
    .port = "80";
    .connect_timeout = 600s;
    .first_byte_timeout = 600s;
    .between_bytes_timeout = 600s;
    .max_connections = 800;
}

import std;

acl purge {
    "10.0.0.0/16";
    "localhost";
    "127.0.0.1";
}

sub vcl_hit {
    if (req.method == "PURGE") {
        return(synth(200,"OK"));
    }
}

sub vcl_miss {
    if (req.method == "PURGE") {
        return(synth(404,"Not cached"));
    }
}

sub vcl_pipe {
    if (req.http.X-Application ~ "(?i)magento") {
        if (req.http.X-Version !~ "^2") {
            unset bereq.http.X-Turpentine-Secret-Handshake;
            set bereq.http.Connection = "close";
        }
    }
}

sub vcl_recv {
    if (req.restarts == 0) {
        if (!req.http.x-forwarded-for) {
            set req.http.X-Forwarded-For = client.ip;
        }
    }
    if (req.http.X-Application ~ "(?i)varnishpass") {
        return (pipe);
    }

    if (req.method == "URLPURGE") {
        if (!client.ip ~ purge) {
            return(synth(405, "This IP is not allowed to send PURGE requests."));
        }
        return (purge);
    }

    if (req.method == "PURGE") {
        if (!client.ip ~ purge) {
            return(synth(405, "This IP is not allowed to send PURGE requests."));
        }
        ban("req.http.host ~ " + req.http.host);
        return (purge);
    }

    if (req.method == "BAN") {
        if (!client.ip ~ purge) {
            return(synth(405, "This IP is not allowed to send PURGE requests."));
        }
        ban("req.http.host == " + req.http.host + "&& req.url == " + req.url);
        return(synth(200, "Ban added"));
    }

    if (req.http.Accept-Encoding) {
        if (req.url ~ "\.(gif|jpg|jpeg|swf|flv|mp3|mp4|pdf|ico|png|gz|tgz|bz2)(\?.*|)$") {
            unset req.http.Accept-Encoding;
        } elsif (req.http.Accept-Encoding ~ "gzip") {
            set req.http.Accept-Encoding = "gzip";
        } elsif (req.http.Accept-Encoding ~ "deflate") {
            set req.http.Accept-Encoding = "deflate";
        } else {
            unset req.http.Accept-Encoding;
        }
    }

    if (req.url ~ "\.(gif|jpg|jpeg|swf|css|js|flv|mp3|mp4|pdf|ico|png)(\?.*|)$") {
        unset req.http.cookie;
        set req.url = regsub(req.url, "\?.*$", "");
    }

    if (req.url ~ "\?(utm_(campaign|medium|source|term)|adParams|client|cx|eid|fbid|feed|ref(id|src)?|v(er|iew))=") {
        set req.url = regsub(req.url, "\?.*$", "");
    }

    if (req.http.cookie) {
        if (req.http.cookie ~ "(wordpress_|wp-settings-)") {
            return(pass);
        } else {
            unset req.http.cookie;
        }
    }
}

sub vcl_backend_response {
    if ( beresp.status == 500 ||
        beresp.status == 502 ||
        beresp.status == 503 ||
        beresp.status == 504 ||
        beresp.status == 404 ||
        beresp.status == 403 ){
        set beresp.uncacheable = true;
    } else {
	    set beresp.http.X-Cacheable = "YES";
    }

    if (bereq.url ~ "wp-(login|admin)" || bereq.url ~ "preview=true" || bereq.url ~ "xmlrpc.php") {
      set beresp.uncacheable = true;
      return (deliver);
    }
    if (beresp.http.set-cookie ~ "(wordpress_|wp-settings-)") {
          set beresp.uncacheable = true;
          return (deliver);
    }
    if ( (!(bereq.url ~ "(wp-(login|admin)|login)")) || (bereq.method == "GET") ) {
      unset beresp.http.set-cookie;
          set beresp.ttl = 4h;
    }

    if (bereq.url ~ "\.(gif|jpg|jpeg|swf|js|flv|mp3|mp4|pdf|ico|png)(\?.*|)$") {
      # set beresp.ttl = 1d;
      # set beresp.uncacheable = true;
      # return (deliver);
      unset beresp.http.set-cookie;
      set beresp.ttl = 365d;
      unset beresp.http.Cache-Control;
      set beresp.http.Cache-Control = "public, max-age=604800";
    }
}

sub vcl_deliver {
    if (req.http.X-Application !~ "(?i)magento" && req.http.X-Version !~ "^2") {
        unset resp.http.X-Varnish;
    }
    unset resp.http.Via;
    unset resp.http.X-Powered-By;
    if (obj.hits > 0) {
        set resp.http.X-Cache = "HIT";
    } else {
        set resp.http.X-Cache = "MISS";
    }

    set resp.http.X-Powered-By = "Sitepilot (sitepilot.io)";
}

sub vcl_hash {
    if (req.http.X-Forwarded-Proto) {
        hash_data(req.http.X-Forwarded-Proto);
    }
}

sub vcl_synth {
    set resp.http.Content-Type = "text/html; charset=utf-8";
    set resp.http.Retry-After = "5";
    return (deliver);
}