#!/bin/bash
set -e

echo "[*] Nac News Portal - Starting up..."

# Wait for MySQL to be ready
echo "[*] Waiting for database connection..."
MAX_RETRIES=30
RETRY=0
until php -r "
    \$conn = @new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
    if (\$conn->connect_error) { exit(1); }
    \$conn->close();
    exit(0);
" 2>/dev/null; do
    RETRY=$((RETRY + 1))
    if [ $RETRY -ge $MAX_RETRIES ]; then
        echo "[!] Failed to connect to database after $MAX_RETRIES attempts"
        exit 1
    fi
    echo "[*] Database not ready, retrying in 3s... ($RETRY/$MAX_RETRIES)"
    sleep 3
done

echo "[+] Database connected successfully"

# Run seed script
echo "[*] Seeding database..."
php /seed.php

echo "[+] Database seeded successfully"
echo "[+] Starting Apache web server..."

# Start Apache in foreground
apache2-foreground
