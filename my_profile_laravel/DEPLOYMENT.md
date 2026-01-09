# Deployment Guide

**Project**: YAMU 業務員推廣系統 - Laravel Backend API
**Version**: 1.0.0
**Last Updated**: 2026-01-09

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Environment Setup](#environment-setup)
4. [Local Deployment](#local-deployment)
5. [Staging Deployment](#staging-deployment)
6. [Production Deployment](#production-deployment)
7. [Blue-Green Deployment](#blue-green-deployment)
8. [Database Management](#database-management)
9. [Monitoring & Logging](#monitoring--logging)
10. [Troubleshooting](#troubleshooting)

---

## Overview

This guide covers the deployment process for the Laravel API backend, including:

- **Development**: Local Docker environment
- **Staging**: Pre-production testing environment
- **Production**: Blue-Green deployment with zero downtime

### Architecture

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Nginx     │───▶│  Laravel    │───▶│   MySQL     │
│  (Proxy)    │    │   (App)     │    │ (Database)  │
└─────────────┘    └─────────────┘    └─────────────┘
                          │
                          ▼
                   ┌─────────────┐
                   │    Redis    │
                   │  (Cache)    │
                   └─────────────┘
```

---

## Prerequisites

### Required Software

- **Docker**: v24.0+ and Docker Compose v2.20+
- **Git**: v2.30+
- **PHP**: v8.3+ (for local development)
- **Composer**: v2.6+

### Server Requirements

#### Production Server
- **OS**: Ubuntu 22.04 LTS or CentOS 8+
- **CPU**: 4 cores minimum
- **RAM**: 8GB minimum (16GB recommended)
- **Storage**: 50GB SSD minimum
- **Network**: 100Mbps bandwidth

#### Staging Server
- **OS**: Ubuntu 22.04 LTS
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 20GB SSD

---

## Environment Setup

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/my_profile_laravel.git
cd my_profile_laravel
```

### 2. Configure Environment Variables

```bash
# Copy environment template
cp .env.production.example .env.production

# Edit configuration
nano .env.production
```

**Critical Settings** (must change):
```ini
APP_KEY=base64:...  # Generate with: php artisan key:generate
APP_URL=https://yourdomain.com
DB_PASSWORD=STRONG_PASSWORD_HERE
DB_ROOT_PASSWORD=STRONG_ROOT_PASSWORD_HERE
JWT_SECRET=...  # Generate with: php artisan jwt:secret
REDIS_PASSWORD=STRONG_REDIS_PASSWORD_HERE
```

### 3. Generate SSL Certificates

```bash
# Self-signed (for testing)
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem

# Production (Let's Encrypt)
certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com
cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/key.pem
```

---

## Local Deployment

For local development and testing.

### Quick Start

```bash
# Start services
docker-compose up -d

# Install dependencies
docker exec my_profile_laravel_app composer install

# Run migrations
docker exec my_profile_laravel_app php artisan migrate

# Run seeders
docker exec my_profile_laravel_app php artisan db:seed

# Test
curl http://localhost:8082/api/health
```

### Local URLs

- **API**: http://localhost:8082/api
- **Health Check**: http://localhost:8082/api/health
- **Database**: localhost:3307 (MySQL)
- **Redis**: localhost:6379

---

## Staging Deployment

Pre-production environment for testing before production release.

### 1. Setup Staging Server

```bash
# SSH to staging server
ssh user@staging.yourdomain.com

# Install Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Clone repository
cd /opt
git clone https://github.com/yourusername/my_profile_laravel.git
cd my_profile_laravel
git checkout develop
```

### 2. Configure Environment

```bash
cp .env.staging.example .env
nano .env

# Update:
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.yourdomain.com
```

### 3. Deploy

```bash
# Build and start
docker-compose -f docker-compose.staging.yml up -d

# Run migrations
docker exec my_profile_laravel_app_staging php artisan migrate --force

# Verify deployment
curl https://staging.yourdomain.com/api/health
```

### 4. Automatic Deployment

GitHub Actions automatically deploys to staging when pushing to `develop` branch.

```bash
# Trigger deployment
git push origin develop
```

---

## Production Deployment

### Manual Deployment

#### 1. Prepare Production Server

```bash
# SSH to production server
ssh user@production.yourdomain.com

# Setup directory
cd /opt
git clone https://github.com/yourusername/my_profile_laravel.git
cd my_profile_laravel
git checkout main
```

#### 2. Configure Environment

```bash
cp .env.production.example .env.production
nano .env.production

# Configure production settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

#### 3. Initial Deployment

```bash
# Build production image
docker-compose -f docker-compose.prod.yml build

# Start services
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker exec my_profile_laravel_app_prod php artisan migrate --force

# Optimize Laravel
docker exec my_profile_laravel_app_prod php artisan config:cache
docker exec my_profile_laravel_app_prod php artisan route:cache
docker exec my_profile_laravel_app_prod php artisan view:cache

# Verify
curl https://yourdomain.com/api/health
```

### Automated Deployment (CI/CD)

#### 1. Setup GitHub Secrets

Go to `Settings` > `Secrets and variables` > `Actions`:

```
PRODUCTION_HOST=your-server-ip
PRODUCTION_USER=deploy
PRODUCTION_SSH_KEY=<private-key-content>
```

#### 2. Deploy

```bash
# Tag and push
git tag v1.0.0
git push origin main --tags

# GitHub Actions will automatically deploy to production
```

---

## Blue-Green Deployment

Zero-downtime deployment strategy using two identical environments (blue/green).

### How It Works

```
┌─────────┐
│  Nginx  │──────┬──▶ Blue Environment  (Current v1.0.0)
│ (Proxy) │      │
└─────────┘      └──▶ Green Environment (New v1.1.0)
                           ↓
                      After Health Check
                           ↓
┌─────────┐
│  Nginx  │──────┬──▶ Blue Environment  (Old v1.0.0) ◀─── Shutdown
│ (Proxy) │      │
└─────────┘      └──▶ Green Environment (Live v1.1.0) ◀─── Active
```

### Deployment Process

1. **Start new environment** (green) with new version
2. **Run migrations** on new environment
3. **Health check** - Ensure new environment is healthy
4. **Switch traffic** - Update Nginx to route to new environment
5. **Monitor** - Watch for errors (5 minutes)
6. **Shutdown old environment** (blue) or keep as rollback

### Manual Blue-Green Deployment

```bash
# Deploy to green (if blue is current)
cd /opt/my_profile_laravel
git pull origin main
docker pull ghcr.io/yourusername/my_profile_laravel:latest

docker-compose -f docker-compose.green.yml up -d

# Health check
./scripts/health-check.sh http://localhost:8082

# Switch traffic
./scripts/switch-traffic.sh green

# Verify
curl https://yourdomain.com/api/health

# After 5 minutes, shutdown blue
docker-compose -f docker-compose.blue.yml down
```

### Automated Blue-Green (GitHub Actions)

The GitHub Actions workflow automatically handles blue-green deployment:

```yaml
# .github/workflows/deploy-production.yml
# - Detects current environment (blue or green)
# - Deploys to opposite environment
# - Runs health checks
# - Switches traffic
# - Rolls back on failure
```

### Rollback

```bash
# If deployment fails, switch back to previous environment
CURRENT=$(cat .current_env)
PREVIOUS=$([[ "$CURRENT" == "blue" ]] && echo "green" || echo "blue")

./scripts/switch-traffic.sh $PREVIOUS
echo "Rolled back to $PREVIOUS"
```

---

## Database Management

### Backup

```bash
# Manual backup
./scripts/backup-database.sh

# Automated backups (cron)
0 2 * * * /opt/my_profile_laravel/scripts/backup-database.sh
```

### Restore

```bash
# List available backups
ls -lh /opt/backups/mysql/

# Restore
./scripts/restore-database.sh /opt/backups/mysql/my_profile_laravel_20260109_020000.sql.gz
```

### Migrations

```bash
# Production migrations (with backup)
./scripts/backup-database.sh
docker exec my_profile_laravel_app_prod php artisan migrate --force

# Rollback (if needed)
docker exec my_profile_laravel_app_prod php artisan migrate:rollback --force
```

---

## Monitoring & Logging

### Health Checks

```bash
# Basic health check
curl https://yourdomain.com/api/health

# Detailed health check
curl https://yourdomain.com/api/health/detailed
```

### Logs

```bash
# Application logs
docker logs my_profile_laravel_app_prod -f

# Nginx logs
docker logs my_profile_laravel_nginx_prod -f

# Laravel logs
docker exec my_profile_laravel_app_prod tail -f storage/logs/laravel.log

# View logs by type
docker exec my_profile_laravel_app_prod tail -f storage/logs/api.log
docker exec my_profile_laravel_app_prod tail -f storage/logs/security.log
```

### Performance Monitoring

```bash
# Container stats
docker stats

# Database queries
docker exec my_profile_laravel_db_prod mysql -u root -p -e "SHOW PROCESSLIST;"

# Redis stats
docker exec my_profile_laravel_redis_prod redis-cli INFO stats
```

---

## Troubleshooting

### Common Issues

#### 1. Container won't start

```bash
# Check logs
docker logs my_profile_laravel_app_prod

# Common fixes
docker-compose down
docker-compose up -d
```

#### 2. Database connection failed

```bash
# Check database container
docker exec my_profile_laravel_db_prod mysql -u root -p -e "SELECT 1;"

# Check environment variables
docker exec my_profile_laravel_app_prod env | grep DB_
```

#### 3. Permission errors

```bash
# Fix storage permissions
docker exec my_profile_laravel_app_prod chmod -R 775 storage bootstrap/cache
docker exec my_profile_laravel_app_prod chown -R www-data:www-data storage bootstrap/cache
```

#### 4. Nginx 502 Bad Gateway

```bash
# Check PHP-FPM is running
docker exec my_profile_laravel_app_prod ps aux | grep php-fpm

# Check upstream connection
docker exec my_profile_laravel_nginx_prod nginx -t
```

### Emergency Procedures

#### Complete Rollback

```bash
cd /opt/my_profile_laravel
git checkout <previous-tag>
docker-compose -f docker-compose.prod.yml down
./scripts/restore-database.sh /opt/backups/mysql/<latest-backup>
docker-compose -f docker-compose.prod.yml up -d
```

#### Force Restart

```bash
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml up -d --force-recreate
```

---

## Support

For issues or questions:
- Check logs first: `docker logs <container-name>`
- Review [Common Issues](#common-issues)
- Contact: DevOps Team

---

**Last Updated**: 2026-01-09
**Version**: 1.0.0
