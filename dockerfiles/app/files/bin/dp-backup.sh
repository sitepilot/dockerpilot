#! /bin/bash
MYSQLDUMP=/usr/bin/mysqldump
BACKUP_DIR="/srv/users/$APP_USER/backup/$APP_NAME"
BACKUP_FILE="$BACKUP_DIR/$APP_DB_NAME.gz"

echo "Backup database ($BACKUP_FILE)..."
mkdir -p $BACKUP_DIR
$MYSQLDUMP --force --opt -h$APP_DB_HOST --user=$APP_DB_USER -p$APP_DB_PASS --databases $APP_DB_NAME | gzip > $BACKUP_FILE
echo "Done!"