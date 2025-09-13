#!/bin/bash
set -e

echo "🚀 Starting ACCESS MNS application..."

cd access_mns_manager

# Run database migrations
echo "🗃️ Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Start the application
echo "✅ Starting Symfony server..."
php -S 0.0.0.0:$PORT public/index.php