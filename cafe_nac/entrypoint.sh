#!/bin/bash
set -e

echo "[*] NAC Cafe CTF - Starting up..."

# Wait for MySQL to be ready
echo "[*] Waiting for MySQL..."
until php -r "
try {
    \$host = getenv('DB_HOST') ?: 'db';
    \$db = getenv('DB_NAME') ?: 'nac_cafe';
    \$user = getenv('DB_USER') ?: 'nac_user';
    \$pass = getenv('DB_PASS') ?: 'nac_password';
    new PDO(\"mysql:host=\$host;dbname=\$db\", \$user, \$pass);
    echo 'Connected';
} catch(Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    sleep 2
done
echo "[+] MySQL is ready!"

# Generate encrypted financial report
echo "[*] Generating encrypted assets..."
php /generate_encrypted.php

# Ensure proper permissions
chown -R www-data:www-data /var/www/html/uploads
chown -R www-data:www-data /var/www/html/admin/data
chown -R www-data:www-data /var/www/html/logs
chmod -R 755 /var/www/html/uploads

echo "[+] NAC Cafe CTF is ready!"

# Start Apache
exec apache2-foreground
