#!/bin/bash
docker run --rm --volume=D:\Code\dockerpilot\docs:/srv/jekyll -it -p 4000:4000 -e JEKYLL_ENV=dev jekyll/jekyll:pages jekyll s --force_polling --config _config.dev.yml