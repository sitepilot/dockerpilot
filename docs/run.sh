#!/bin/bash
docker run --rm --volume=$PWD:/srv/jekyll -it -p 4000:4000 -e JEKYLL_ENV=dev jekyll/jekyll:pages jekyll s --force_polling --config _config.yml,_config.dev.yml