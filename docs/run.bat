docker run --rm --volume=%~dp0:/srv/jekyll -it -p 4000:4000 -e JEKYLL_ENV=dev jekyll/jekyll:pages jekyll s --force_polling --config _config.yml,_config.dev.yml