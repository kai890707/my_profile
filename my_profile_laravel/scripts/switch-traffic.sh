#!/bin/bash
# ==============================================
# Blue-Green Deployment Traffic Switching Script
# ==============================================

set -e

TARGET=$1

if [ -z "$TARGET" ]; then
    echo "Usage: $0 <blue|green>"
    exit 1
fi

if [ "$TARGET" != "blue" ] && [ "$TARGET" != "green" ]; then
    echo "Error: TARGET must be 'blue' or 'green'"
    exit 1
fi

# Determine port based on environment
if [ "$TARGET" == "blue" ]; then
    PORT=8081
else
    PORT=8082
fi

echo "Switching traffic to $TARGET environment (port $PORT)..."

# Update nginx upstream configuration
cat > /etc/nginx/sites-available/laravel-upstream.conf << EOF
upstream laravel_backend {
    server localhost:$PORT;
}

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    location / {
        proxy_pass http://laravel_backend;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }

    # Health check endpoint
    location /health {
        access_log off;
        proxy_pass http://laravel_backend/health;
    }
}
EOF

# Test nginx configuration
nginx -t

# Reload nginx without downtime
nginx -s reload

echo "✅ Traffic switched to $TARGET environment successfully"
