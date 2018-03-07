version: '2'

options:

syncs:
  dp-app-{{$env['APP_NAME']}}-sync:
    src: './app'
    sync_userid: '1000'
    sync_excludes: ['*/node_modules', '*/.git', '*.scss']