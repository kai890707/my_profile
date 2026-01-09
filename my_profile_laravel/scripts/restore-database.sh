#!/bin/bash
# ==============================================
# Database Restore Script
# ==============================================

set -e

BACKUP_FILE=$1
CONTAINER_NAME="my_profile_laravel_db_prod"
DB_NAME=${DB_DATABASE:-my_profile_laravel}
DB_USER=${DB_USERNAME:-root}
DB_PASS=${DB_PASSWORD:-123456}

if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file.sql.gz>"
    echo ""
    echo "Available backups:"
    ls -lh /opt/backups/mysql/*.sql.gz 2>/dev/null || echo "No backups found"
    exit 1
fi

if [ ! -f "$BACKUP_FILE" ]; then
    echo "Error: Backup file not found: $BACKUP_FILE"
    exit 1
fi

echo "⚠️  WARNING: This will restore the database from backup"
echo "Database: $DB_NAME"
echo "Backup file: $BACKUP_FILE"
echo ""
read -p "Are you sure you want to continue? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Restore cancelled"
    exit 0
fi

echo "Starting database restore..."

# Restore backup
gunzip < $BACKUP_FILE | docker exec -i $CONTAINER_NAME mysql \
    -u$DB_USER \
    -p$DB_PASS \
    $DB_NAME

if [ $? -eq 0 ]; then
    echo "✅ Database restored successfully"
    exit 0
else
    echo "❌ Database restore failed"
    exit 1
fi
