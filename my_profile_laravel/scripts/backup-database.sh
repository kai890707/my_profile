#!/bin/bash
# ==============================================
# Database Backup Script
# ==============================================

set -e

# Configuration
BACKUP_DIR="/opt/backups/mysql"
DATE=$(date +%Y%m%d_%H%M%S)
CONTAINER_NAME="my_profile_laravel_db_prod"
DB_NAME=${DB_DATABASE:-my_profile_laravel}
DB_USER=${DB_USERNAME:-root}
DB_PASS=${DB_PASSWORD:-123456}
RETENTION_DAYS=7

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup filename
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_${DATE}.sql.gz"

echo "Starting database backup..."
echo "Database: $DB_NAME"
echo "Backup file: $BACKUP_FILE"

# Create backup
docker exec $CONTAINER_NAME mysqldump \
    -u$DB_USER \
    -p$DB_PASS \
    $DB_NAME \
    --single-transaction \
    --quick \
    --lock-tables=false \
    | gzip > $BACKUP_FILE

# Check if backup was successful
if [ -f "$BACKUP_FILE" ]; then
    SIZE=$(du -h $BACKUP_FILE | cut -f1)
    echo "✅ Backup completed successfully"
    echo "Backup size: $SIZE"

    # Remove old backups
    echo "Removing backups older than $RETENTION_DAYS days..."
    find $BACKUP_DIR -name "${DB_NAME}_*.sql.gz" -mtime +$RETENTION_DAYS -delete

    # List recent backups
    echo "Recent backups:"
    ls -lh $BACKUP_DIR | tail -5

    exit 0
else
    echo "❌ Backup failed"
    exit 1
fi
