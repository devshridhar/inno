#!/bin/bash
# Complete Laravel Backend Setup Script

echo "🚀 Setting up Laravel Backend for News Aggregator..."

# Step 1: Create Laravel project in backend directory
echo "📦 Creating Laravel project..."
if [ ! -d "backend" ]; then
    mkdir backend
fi

cd backend

# Create Laravel project if artisan doesn't exist
if [ ! -f "artisan" ]; then
    composer create-project laravel/laravel . "^10.0"
fi

# Step 2: Install required packages
echo "📦 Installing Laravel packages..."

# Core packages
composer require \
    laravel/sanctum \
    guzzlehttp/guzzle \
    spatie/laravel-query-builder \
    predis/predis

# Development packages
composer require --dev \
    laravel/telescope \
    barryvdh/laravel-debugbar

# Step 3: Copy all the files we created
echo "📄 Setting up configuration files..."

# Copy environment file
cp .env.example .env.backend

# Step 4: Configure Laravel
echo "⚙️ Configuring Laravel..."

# Publish Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Generate application key
php artisan key:generate

# Step 5: Create directory structure
echo "📁 Creating directory structure..."

# Create service directories
mkdir -p app/Services/NewsScrapers
mkdir -p app/Traits
mkdir -p app/Http/Resources
mkdir -p app/Http/Requests/Auth
mkdir -p app/Http/Requests/Article
mkdir -p app/Http/Requests/UserPreference

# Create config directory for custom configs
mkdir -p config

# Step 6: Database setup instructions
echo "🗄️ Database Setup Instructions:"
echo "1. Make sure your .env file has the correct database settings"
echo "2. Run the following commands after copying all files:"

cat << 'EOF'

# After copying all model files, run:
php artisan migrate:fresh
php artisan db:seed

# Or with Docker:
docker-compose exec backend php artisan migrate:fresh
docker-compose exec backend php artisan db:seed

EOF

# Step 7: File copying checklist
echo "📋 File Copying Checklist:"
echo "✅ Copy all migration files to database/migrations/"
echo "✅ Copy all model files to app/Models/"
echo "✅ Copy all controller files to app/Http/Controllers/Api/"
echo "✅ Copy all resource files to app/Http/Resources/"
echo "✅ Copy all request files to app/Http/Requests/"
echo "✅ Copy service files to app/Services/"
echo "✅ Copy job files to app/Jobs/"
echo "✅ Copy command files to app/Console/Commands/"
echo "✅ Copy seeder files to database/seeders/"
echo "✅ Copy routes/api.php"
echo "✅ Copy config files (cors.php, sanctum.php, news.php)"

# Step 8: Test commands
echo "🧪 Testing Commands (run after setup):"

cat << 'EOF'

# Test API endpoints:
curl http://localhost:8000/api/health
curl http://localhost:8000/api/categories
curl http://localhost:8000/api/sources
curl http://localhost:8000/api/articles

# Register a test user:
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"first_name":"Test","last_name":"User","email":"test@example.com","password":"password","password_confirmation":"password"}'

# Manual news scraping:
php artisan news:scrape

# Or with Docker:
docker-compose exec backend php artisan news:scrape

EOF

echo ""
echo "🎯 Next Steps:"
echo "1. Copy all the provided files to their respective locations"
echo "2. Update your .env file with actual API keys"
echo "3. Run: docker-compose up -d"
echo "4. Run: docker-compose exec backend php artisan migrate:fresh --seed"
echo "5. Test the API endpoints"
echo ""
echo "✨ Your Laravel backend will be ready!"

# Create a simple test script
cat > test_backend.sh << 'EOF'
#!/bin/bash
echo "Testing News Aggregator Backend..."

# Test health endpoint
echo "🏥 Testing health endpoint..."
curl -s http://localhost:8000/api/health | jq .

# Test categories
echo "📁 Testing categories endpoint..."
curl -s http://localhost:8000/api/categories | jq .

# Test sources
echo "📰 Testing sources endpoint..."
curl -s http://localhost:8000/api/sources | jq .

# Test articles
echo "📄 Testing articles endpoint..."
curl -s "http://localhost:8000/api/articles?per_page=5" | jq .

echo "✅ Backend testing complete!"
EOF

chmod +x test_backend.sh

echo "Created test_backend.sh for API testing"
echo "Run ./test_backend.sh after setup to test your backend"