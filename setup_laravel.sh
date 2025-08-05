#!/bin/bash
# Laravel Backend Setup Script - No Token Issues

echo "🚀 Setting up Laravel Backend for News Aggregator..."

# Navigate to backend directory
cd backend

# Create new Laravel project (if not already created)
if [ ! -f "artisan" ]; then
    echo "📦 Creating new Laravel project..."
    COMPOSER_AUTH='{}' composer create-project laravel/laravel . "^10.0" \
        --ignore-platform-reqs \
        --no-scripts \
        --prefer-dist \
        --no-interaction
fi

# Check if Laravel was created successfully
if [ ! -f "artisan" ]; then
    echo "❌ Laravel installation failed. Trying alternative method..."

    # Alternative: Download Laravel without token issues
    curl -s https://getcomposer.org/installer | php
    php composer.phar create-project laravel/laravel . "^10.0" \
        --ignore-platform-reqs \
        --no-scripts \
        --prefer-dist \
        --no-interaction
fi

# Verify Laravel installation
if [ ! -f "artisan" ]; then
    echo "❌ Failed to create Laravel project. Please check your internet connection."
    exit 1
fi

echo "✅ Laravel project created successfully!"

# Install essential packages only (avoid problematic ones)
echo "📦 Installing essential Laravel packages..."
COMPOSER_AUTH='{}' composer require \
    laravel/sanctum \
    guzzlehttp/guzzle \
    spatie/laravel-query-builder \
    predis/predis \
    --ignore-platform-reqs \
    --no-scripts \
    --prefer-dist \
    --no-interaction

# Install minimal development packages
echo "📦 Installing development packages..."
COMPOSER_AUTH='{}' composer require --dev \
    barryvdh/laravel-debugbar \
    --ignore-platform-reqs \
    --no-scripts \
    --prefer-dist \
    --no-interaction

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate --no-interaction

# Publish Sanctum configuration
echo "⚙️ Publishing Sanctum configuration..."
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --no-interaction

# Create directory structure
echo "📁 Creating directory structure..."
mkdir -p app/Services
mkdir -p app/Services/NewsScrapers
mkdir -p app/Traits

# Create basic test structure
echo "🧪 Creating test files..."
php artisan make:test Feature/AuthTest --quiet
php artisan make:test Feature/ArticleTest --quiet
php artisan make:test Feature/SearchTest --quiet
php artisan make:test Unit/ArticleModelTest --quiet

# Optional: Install additional packages one by one (safer approach)
echo "📦 Installing additional packages (optional)..."

# Try to install each package individually to avoid conflicts
packages=(
    "spatie/laravel-permission"
    "league/fractal"
)

for package in "${packages[@]}"; do
    echo "Installing $package..."
    COMPOSER_AUTH='{}' composer require $package \
        --ignore-platform-reqs \
        --no-scripts \
        --prefer-dist \
        --no-interaction \
        --quiet || echo "⚠️ Failed to install $package (skipping)"
done

# Clear any cached configurations
echo "🧹 Clearing configurations..."
php artisan config:clear --quiet
php artisan cache:clear --quiet

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "✅ Laravel backend structure created successfully!"
echo ""
echo "📋 What was installed:"
echo "  ✅ Laravel 10 framework"
echo "  ✅ Sanctum (API authentication)"
echo "  ✅ Guzzle (HTTP client)"
echo "  ✅ Query Builder (API filtering)"
echo "  ✅ Redis support"
echo "  ✅ Debug bar (development)"
echo ""
echo "📋 Next Steps:"
echo "1. Copy all your manually created Laravel files to their locations"
echo "2. Update .env file with your API keys"
echo "3. Run: docker-compose up -d"
echo "4. Run: docker-compose exec backend php artisan migrate:fresh --seed"
echo ""
echo "🎯 Your manually created files are safe and won't be overwritten!"
echo "The script only creates the Laravel foundation and installs packages."