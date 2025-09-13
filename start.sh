#!/bin/bash
set -e

echo "🚀 Starting ACCESS MNS deployment..."

# Install dependencies for backend
echo "📦 Installing Symfony dependencies..."
cd access_mns_manager
composer install --no-dev --optimize-autoloader

# Generate JWT keys if they don't exist
if [ ! -f "config/jwt/private.pem" ]; then
    echo "🔑 Generating JWT keys..."
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
fi

# Run database migrations
echo "🗃️ Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Clear and warm cache
echo "🔧 Clearing cache..."
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Install frontend dependencies
echo "📦 Installing Angular dependencies..."
cd ../access_mns_client
npm ci --only=production

# Build frontend for production
echo "🏗️ Building frontend..."
npm run build

# Start the application
echo "✅ Starting application..."
cd ../access_mns_manager
php -S 0.0.0.0:$PORT public/index.php