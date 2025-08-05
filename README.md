# 📰 News Aggregator

A full-stack news aggregation application built with **Laravel** (backend) and **React TypeScript** (frontend), running in **Docker** containers. The app scrapes news from multiple sources, provides user authentication, advanced filtering, bookmarking, and a responsive mobile-friendly interface.

## 🚀 Quick Start Guide

### Prerequisites

Before you begin, make sure you have installed:
- **Docker** (version 20.0+)
- **Docker Compose** (version 2.0+)

### 📋 Step-by-Step Setup

#### 1. Clone the Repository
```bash
git clone <your-repo-url>
cd inno
```

#### 2. Setup Environment Files
Copy the example environment files and configure them:

```bash
# Copy backend environment file
cp backend/.env.example backend/.env

# Copy root environment file (for docker-compose)
cp .env.example .env
```

**Important**: The `backend/.env` file already contains working API keys. If you want to use your own API keys, update these values:
- `NEWS_API_KEY` - Get from [newsapi.org](https://newsapi.org)
- `GUARDIAN_API_KEY` - Get from [The Guardian API](https://open-platform.theguardian.com)
- `NYT_API_KEY` - Get from [New York Times API](https://developer.nytimes.com)

#### 3. Build and Start Docker Containers
```bash
# Build and start all services
docker compose up -d

# Check if all containers are running
docker compose ps
```

You should see 4 containers running:
- `news_aggregator_backend` (Laravel API)
- `news_aggregator_frontend` (React App)
- `news_aggregator_db` (PostgreSQL)
- `news_aggregator_redis` (Redis)

#### 4. Install Dependencies (First Time Setup)

If you're setting up the project for the first time or after cloning, you need to install dependencies:

**Backend Dependencies (Laravel/PHP):**
```bash
# Install PHP dependencies via Docker
docker compose exec backend composer install

# Generate application key
docker compose exec backend php artisan key:generate
```

**Frontend Dependencies (React/Node.js):**
```bash
# Install Node.js dependencies via Docker
docker compose exec frontend npm install
```

#### 5. Database Setup
Run Laravel migrations and seed the database:

```bash
# Run database migrations
docker compose exec backend php artisan migrate

# Seed the database with categories and news sources
docker compose exec backend php artisan db:seed
```

#### 6. Scrape News Articles
Populate the database with real news articles:

```bash
# Queue news scraping jobs
docker compose exec backend php artisan news:scrape

# Process the queued jobs
docker compose exec backend php artisan queue:work --once
```

#### 7. Access the Application
🎉 **Your News Aggregator is now ready!**

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000/api

## 📸 Screenshots

### 🏠 **Home Page - Latest News**
*Main dashboard showing latest news articles with filtering options*

![Home Page](screenshots/home-page.png)

### 🔐 **User Authentication**
*Clean and modern login/registration interface*

![Login Form](screenshots/login-form.png)
![Registration Form](screenshots/registration-form.png)

### 📂 **Categories View**
*Browse news by different categories with visual indicators*

![Categories Page](screenshots/categories-page.png)

### 📡 **News Sources**
*Explore all available news sources with metadata*

![Sources Page](screenshots/sources-page.png)

### 🔍 **Advanced Search & Filtering**
*Powerful search with real-time filtering capabilities*

![Search and Filters](screenshots/search-filters.png)

### 🔖 **Personal Bookmarks**
*Save and manage your favorite articles (requires authentication)*

![Bookmarks Page](screenshots/bookmarks-page.png)

### 📱 **Mobile Responsive Design**
*Fully optimized for mobile devices and tablets*

![Mobile View](screenshots/mobile-view.png)

### 🎨 **Dark/Light Theme Support**
*Beautiful interface that adapts to user preferences*

![Theme Options](screenshots/theme-options.png)

> **Note**: Screenshots will be added soon. The application is fully functional and ready to use!

## 📱 Features

### 🔐 **User Authentication**
- User registration with first name, last name, email, and password
- Secure login/logout with JWT tokens
- Protected routes for authenticated users

### 📰 **News Browsing**
- **Home**: Latest news articles with pagination
- **Categories**: Browse news by category (Technology, Sports, Politics, etc.)
- **Sources**: Explore different news sources (NewsAPI, The Guardian, etc.)
- **Search**: Find articles by keywords, author, or content

### 🔍 **Advanced Filtering**
- Filter by category, news source, author, and date range
- Filters apply automatically (no submit button needed)
- Sort by date, relevance, or popularity

### 🔖 **Personal Features** (Requires Authentication)
- Bookmark articles for later reading
- Personal bookmarks page
- User preferences and settings

### 📱 **Mobile Responsive**
- Fully responsive design works on all devices
- Touch-friendly interface
- Optimized for mobile browsing

## 🛠️ Development Commands

### Backend (Laravel)
```bash
# Access backend container
docker compose exec backend bash

# Run artisan commands
docker compose exec backend php artisan <command>

# View logs
docker compose logs backend

# Run tests
docker compose exec backend php artisan test
```

### Frontend (React)
```bash
# Access frontend container
docker compose exec frontend sh

# Install new packages
docker compose exec frontend npm install <package>

# View logs
docker compose logs frontend
```

### Database Operations
```bash
# Access PostgreSQL database
docker compose exec db psql -U postgres -d news_aggregator

# Reset database (careful!)
docker compose exec backend php artisan migrate:fresh --seed
```

### News Scraping
```bash
# Scrape from specific source
docker compose exec backend php artisan news:scrape --source=newsapi-general

# Process queue jobs
docker compose exec backend php artisan queue:work

# View queue status
docker compose exec backend php artisan queue:monitor
```

## 📊 API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/me` - Get current user

### Articles
- `GET /api/articles` - Get paginated articles
- `GET /api/articles/search` - Search articles
- `GET /api/articles/{uuid}` - Get single article
- `POST /api/articles/{uuid}/bookmark` - Bookmark article
- `DELETE /api/articles/{uuid}/bookmark` - Remove bookmark

### Categories & Sources
- `GET /api/categories` - Get all categories
- `GET /api/sources` - Get all news sources

## 🔧 Troubleshooting

### Container Issues
```bash
# Restart all containers
docker compose restart

# Rebuild containers (if code changes)
docker compose build --no-cache
docker compose up -d

# View container logs
docker compose logs <service-name>
```

### Database Issues
```bash
# Reset database
docker compose exec backend php artisan migrate:fresh --seed

# Check database connection
docker compose exec backend php artisan tinker
# Then run: DB::connection()->getPdo();
```

### No Articles Showing
```bash
# Check if articles exist in database
docker compose exec backend php artisan tinker
# Then run: App\Models\Article::count();

# Re-run news scraping
docker compose exec backend php artisan news:scrape
docker compose exec backend php artisan queue:work --once
```

### Frontend Not Loading
```bash
# Check if frontend container is running
docker compose ps

# Rebuild frontend
docker compose build frontend --no-cache
docker compose up frontend -d

# Check frontend logs
docker compose logs frontend
```

## 🏗️ Architecture

### Technology Stack
- **Backend**: Laravel 10, PHP 8.2, PostgreSQL 15, Redis 7
- **Frontend**: React 18, TypeScript, Node.js 18
- **Infrastructure**: Docker, Docker Compose, Nginx
- **APIs**: NewsAPI, The Guardian API, New York Times API

### Project Structure
```
inno/
├── backend/           # Laravel API application
├── frontend/          # React TypeScript application  
├── docker-compose.yml # Docker services configuration
├── .env              # Environment variables
└── README.md         # This file
```

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📧 Support

If you encounter any issues or have questions, please create an issue in the repository.

---

**Happy news reading! 📰✨**