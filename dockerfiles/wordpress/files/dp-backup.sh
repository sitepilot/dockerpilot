#! /bin/bash
MYSQLDUMP=/usr/bin/mysqldump
BACKUP_DIR="/var/www/restore/mysql"
BACKUP_FILE="$BACKUP_DIR/$APP_NAME.gz"

echo "Backup database ($BACKUP_FILE)..."
mkdir -p $BACKUP_DIR
$MYSQLDUMP --force --opt -h$APP_DB_HOST --user=$APP_NAME -p$APP_DB_PASS --databases $APP_NAME | gzip > $BACKUP_FILE
echo "Done!"