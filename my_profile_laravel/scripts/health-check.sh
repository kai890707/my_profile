#!/bin/bash
# ==============================================
# Application Health Check Script
# ==============================================

set -e

URL=${1:-http://localhost:8082}
MAX_RETRIES=${2:-10}
RETRY_DELAY=${3:-5}

echo "Performing health check on $URL..."

for i in $(seq 1 $MAX_RETRIES); do
    echo "Attempt $i/$MAX_RETRIES..."

    # Check HTTP response
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" $URL/health || echo "000")

    if [ "$HTTP_CODE" == "200" ]; then
        echo "✅ Health check passed (HTTP $HTTP_CODE)"

        # Additional checks
        echo "Checking database connection..."
        curl -s $URL/api/health/database > /dev/null || echo "⚠️  Database check failed"

        echo "Checking cache connection..."
        curl -s $URL/api/health/cache > /dev/null || echo "⚠️  Cache check failed"

        exit 0
    else
        echo "❌ Health check failed (HTTP $HTTP_CODE)"
        if [ $i -lt $MAX_RETRIES ]; then
            echo "Retrying in $RETRY_DELAY seconds..."
            sleep $RETRY_DELAY
        fi
    fi
done

echo "❌ Health check failed after $MAX_RETRIES attempts"
exit 1
