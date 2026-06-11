#!/bin/bash
set -e

# Create DB
sudo -u postgres psql -c "CREATE USER geo_app WITH PASSWORD 'geo_secret_2026';" 2>/dev/null || true
sudo -u postgres psql -c "CREATE DATABASE geo OWNER geo_app;" 2>/dev/null || true
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE geo TO geo_app;" 2>/dev/null || true

# Clone app
mkdir -p /var/www
cd /var/www
rm -rf geo
git clone https://github.com/l-howmun/geo-checker.git geo
cd geo

# Install dependencies
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

# Setup env
cp .env.example .env
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=pgsql/' .env
sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=127.0.0.1/' .env
sed -i 's/# DB_PORT=3306/DB_PORT=5432/' .env
sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=geo/' .env
sed -i 's/# DB_USERNAME=root/DB_USERNAME=geo_app/' .env
sed -i 's/# DB_PASSWORD=/DB_PASSWORD=geo_secret_2026/' .env
sed -i 's|APP_URL=http://localhost|APP_URL=https://geo.horizonit.dev|' .env
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env

# Add OpenAI key placeholder - replace after deploy
echo "" >> .env
echo "OPENAI_API_KEY=sk-placeholder-replace-me" >> .env

php artisan key:generate --force
php artisan migrate --force

# Permissions
chown -R www-data:www-data storage bootstrap/cache

# Update Caddy - append geo site alongside tms
cat > /etc/caddy/Caddyfile << 'EOF'
tms.horizonit.dev {
    root * /var/www/tms/public
    php_fastcgi unix//run/php/php8.5-fpm.sock
    file_server
    encode gzip
}

geo.horizonit.dev {
    root * /var/www/geo/public
    php_fastcgi unix//run/php/php8.5-fpm.sock
    file_server
    encode gzip
}
EOF

systemctl reload caddy

echo "DEPLOY_COMPLETE"
echo ""
echo "NEXT: SSH in and set your real OPENAI_API_KEY in /var/www/geo/.env"
