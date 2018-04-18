version: '3.5'
services:

  db:
    image: sitepilot/mysql:5.7
    networks:
      - dockerpilot
    secrets:
      - db-root-pass
      - db-user-pass
      - db-user-name
    environment:
      - MYSQL_DATABASE={{ $mysql["defaultDatabase"] }}
      - MYSQL_ROOT_PASSWORD_FILE=/run/secrets/db-root-pass
      - MYSQL_USER_FILE=/run/secrets/db-user-name
      - MYSQL_PASSWORD_FILE=/run/secrets/db-user-pass
    volumes:
      - {{ $mysql["storagePath"] }}:/var/lib/mysql:cached
      - {{ $mysql["backupPath"] }}:/backup/mysql
    deploy:
      placement:
        constraints: [node.role == manager]

networks:
  dockerpilot:
    external: true

secrets:
  db-root-pass:
    name: {{ $mysql["rootPassSecret"] }}
    external: true
  db-user-name:
    name: {{ $mysql["userNameSecret"] }}
    external: true
  db-user-pass:
    name: {{ $mysql["userPassSecret"] }}
    external: true