# Makefile for News Aggregator Development

.PHONY: help install build up down restart logs clean test backend-shell frontend-shell db-shell

# Default target
help:
	@echo "News Aggregator Development Commands"
	@echo "====================================="
	@echo "install     - Install and setup the project"
	@echo "build       - Build all Docker images"
	@echo "up          - Start all services"
	@echo "down        - Stop all services"
	@echo "restart     - Restart all services"
	@echo "logs        - Show logs for all services"
	@echo "clean       - Clean up containers and volumes"
	@echo "test        - Run tests for both frontend and backend"
	@echo ""
	@echo "Shell Access:"
	@echo "backend-shell  - Access backend container shell"
	@echo "frontend-shell - Access frontend container shell"
	@echo "db-shell      - Access PostgreSQL shell"
	@echo ""
	@echo "Development:"
	@echo "migrate       - Run Laravel migrations"
	@echo "seed          - Run database seeders"
	@echo "cache-clear   - Clear all Laravel caches"

# Project installation
install:
	@echo "Setting up News Aggregator project..."
	@if [ ! -f .env ]; then cp .env.example .env; echo "Created .env file - please update with your API keys"; fi
	@docker-compose build
	@docker-compose up -d postgres redis
	@echo "Waiting for database to be ready..."
	@sleep 10
	@docker-compose up -d backend
	@sleep 5
	@docker-compose exec backend php artisan key:generate
	@docker-compose exec backend php artisan migrate
	@docker-compose exec backend php artisan db:seed
	@docker-compose up -d
	@echo "Installation complete! Visit http://localhost:3000"

# Docker operations
build:
	@echo "Building Docker images..."
	@docker-compose build

up:
	@echo "Starting all services..."
	@docker-compose up -d
	@echo "Services started! Frontend: http://localhost:3000, API: http://localhost:8000"

down:
	@echo "Stopping all services..."
	@docker-compose down

restart:
	@echo "Restarting all services..."
	@docker-compose restart

logs:
	@docker-compose logs -f

# Database operations
migrate:
	@echo "Running Laravel migrations..."
	@docker-compose exec backend php artisan migrate

migrate-fresh:
	@echo "Fresh migration with seeding..."
	@docker-compose exec backend php artisan migrate:fresh --seed

seed:
	@echo "Running database seeders..."
	@docker-compose exec backend php artisan db:seed

# Cache operations
cache-clear:
	@echo "Clearing all caches..."
	@docker-compose exec backend php artisan cache:clear
	@docker-compose exec backend php artisan config:clear
	@docker-compose exec backend php artisan route:clear
	@docker-compose exec backend php artisan view:clear

# Shell access
backend-shell:
	@docker-compose exec backend sh

frontend-shell:
	@docker-compose exec frontend sh

db-shell:
	@docker-compose exec postgres psql -U postgres -d news_aggregator

redis-shell:
	@docker-compose exec redis redis-cli

# Testing
test:
	@echo "Running backend tests..."
	@docker-compose exec backend php artisan test
	@echo "Running frontend tests..."
	@docker-compose exec frontend npm test -- --coverage --watchAll=false

test-backend:
	@docker-compose exec backend php artisan test

test-frontend:
	@docker-compose exec frontend npm test -- --coverage --watchAll=false

# Development tools
artisan:
	@docker-compose exec backend php artisan $(filter-out $@,$(MAKECMDGOALS))

npm:
	@docker-compose exec frontend npm $(filter-out $@,$(MAKECMDGOALS))

composer:
	@docker-compose exec backend composer $(filter-out $@,$(MAKECMDGOALS))

# Queue management
queue-work:
	@docker-compose exec backend php artisan queue:work

queue-restart:
	@docker-compose exec backend php artisan queue:restart

# Data scraping
scrape-news:
	@echo "Manually triggering news scraping..."
	@docker-compose exec backend php artisan news:scrape

# Cleanup
clean:
	@echo "Cleaning up containers and volumes..."
	@docker-compose down -v
	@docker system prune -f
	@docker volume prune -f

clean-all:
	@echo "Removing all containers, images, and volumes..."
	@docker-compose down -v --rmi all
	@docker system prune -a -f
	@docker volume prune -f

# Production
prod-build:
	@echo "Building for production..."
	@docker-compose -f docker-compose.prod.yml build

prod-up:
	@echo "Starting production environment..."
	@docker-compose -f docker-compose.prod.yml up -d

# Health check
health:
	@echo "Checking service health..."
	@docker-compose ps
	@echo ""
	@echo "Backend Health:"
	@curl -s http://localhost:8000/api/health || echo "Backend not responding"
	@echo ""
	@echo "Frontend Health:"
	@curl -s http://localhost:3000 > /dev/null && echo "Frontend OK" || echo "Frontend not responding"

# Allow arbitrary targets for artisan, npm, composer commands
%:
	@: