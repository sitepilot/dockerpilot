#! /bin/bash
MYSQL=/usr/bin/mysql
BACKUP_FILE="/var/www/restore/mysql/$APP_NAME.gz"

if [ -f $BACKUP_FILE ]; then
    echo "Restoring database backup ($BACKUP_FILE)..."
    gunzip < $BACKUP_FILE | $MYSQL -h $APP_DB_HOST -u $APP_NAME  -p$APP_DB_PASS $APP_NAME
    echo "Done!"
else
    echo "Can't find backup file ($BACKUP_FILE)!"
    exit 1
fi