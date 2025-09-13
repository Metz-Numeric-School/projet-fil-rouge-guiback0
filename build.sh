#!/bin/bash
set -e

echo "🚀 Building ACCESS MNS for production..."

# Install backend dependencies
echo "📦 Installing Symfony dependencies..."
cd access_mns_manager
composer install --no-dev --optimize-autoloader --no-interaction

# Generate JWT keys if they don't exist
if [ ! -f "config/jwt/private.pem" ]; then
    echo "🔑 Generating JWT keys..."
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
fi

# Clear and warm cache for production
echo "🔧 Preparing cache for production..."
php bin/console cache:clear --env=prod --no-interaction
php bin/console cache:warmup --env=prod --no-interaction

# Install frontend dependencies and build
echo "📦 Installing and building Angular frontend..."
cd ../access_mns_client
npm ci --only=production
npm run build

echo "✅ Build completed successfully!"