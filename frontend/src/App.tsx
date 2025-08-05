import React, { useState, useEffect, useCallback } from 'react';
import { Toaster } from 'react-hot-toast';
import { AuthProvider } from './contexts/AuthContext';
import Header from './components/layout/Header';
import SearchBar from './components/search/SearchBar';
import FilterPanel from './components/filters/FilterPanel';
import ArticleCard from './components/articles/ArticleCard';
import Modal from './components/common/Modal';
import LoginForm from './components/auth/LoginForm';
import RegisterForm from './components/auth/RegisterForm';
import CategoriesView from './components/views/CategoriesView';
import SourcesView from './components/views/SourcesView';
import BookmarksView from './components/views/BookmarksView';
import { Article, SearchFilters, ApiResponse } from './types';
import { apiService } from './services/api';
import './App.css';

const App: React.FC = () => {
  // State management
  const [articles, setArticles] = useState<Article[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState<SearchFilters>({});
  const [isFilterPanelOpen, setIsFilterPanelOpen] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(false);

  // Navigation state
  const [currentView, setCurrentView] = useState<'home' | 'categories' | 'sources' | 'bookmarks'>('home');

  // Modal states
  const [isAuthModalOpen, setIsAuthModalOpen] = useState(false);
  const [authMode, setAuthMode] = useState<'login' | 'register'>('login');

  // Load articles function
  const loadArticles = useCallback(async (newFilters: SearchFilters = {}, page: number = 1) => {
    try {
      setLoading(true);
      setError(null);

      // Build query parameters
      const searchFilters: any = {
        page,
        per_page: 12,
        ...newFilters
      };

      // Use search endpoint if there's a query, otherwise use articles endpoint
      const response: ApiResponse<Article[]> = newFilters.q 
        ? await apiService.searchArticles(searchFilters)
        : await apiService.getArticles(searchFilters);

      if (page === 1) {
        setArticles(response.data || []);
      } else {
        setArticles(prev => [...prev, ...(response.data || [])]);
      }

      // Update pagination info
      if (response.meta) {
        setCurrentPage(response.meta.current_page);
        setHasMore(response.meta.current_page < response.meta.last_page);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to load articles');
      console.error('Error loading articles:', err);
    } finally {
      setLoading(false);
    }
  }, []);

  // Handle search
  const handleSearch = (newFilters: SearchFilters) => {
    setFilters(newFilters);
    setCurrentPage(1);
    loadArticles(newFilters, 1);
  };

  // Handle filter changes
  const handleFiltersChange = (newFilters: SearchFilters) => {
    setFilters(newFilters);
    setCurrentPage(1);
    loadArticles(newFilters, 1);
  };

  // Handle bookmark toggle
  const handleBookmark = async (articleUuid: string) => {
    try {
      const article = articles.find(a => a.uuid === articleUuid);
      if (!article) return;

      if (article.is_bookmarked) {
        await apiService.removeBookmark(articleUuid);
      } else {
        await apiService.bookmarkArticle(articleUuid);
      }

      // Update local state
      setArticles(prev => prev.map(a => 
        a.uuid === articleUuid 
          ? { ...a, is_bookmarked: !a.is_bookmarked }
          : a
      ));
    } catch (err) {
      console.error('Error toggling bookmark:', err);
    }
  };

  // Load more articles (pagination)
  const loadMore = () => {
    if (hasMore && !loading) {
      loadArticles(filters, currentPage + 1);
    }
  };

  // Authentication modal handlers
  const openAuthModal = (mode: 'login' | 'register' = 'login') => {
    setAuthMode(mode);
    setIsAuthModalOpen(true);
  };

  const closeAuthModal = () => {
    setIsAuthModalOpen(false);
  };

  const switchAuthMode = () => {
    setAuthMode(authMode === 'login' ? 'register' : 'login');
  };

  // Initial load
  useEffect(() => {
    loadArticles();
  }, [loadArticles]);

  return (
    <AuthProvider>
      <div className="app">
        <Header 
          onAuthModalOpen={() => openAuthModal('login')} 
          currentView={currentView} 
          onNavigate={(view) => setCurrentView(view)} 
        />
        
        <main className="main-content">
          <div className="container">
            {/* Search Section */}
            <section className="search-section">
              <div className="search-header">
                <h2>Discover News That Matters</h2>
                <p>Search through thousands of articles from trusted sources</p>
              </div>
              <SearchBar onSearch={handleSearch} />
            </section>

            {/* Filters */}
            <FilterPanel
              filters={filters}
              onFiltersChange={handleFiltersChange}
              isOpen={isFilterPanelOpen}
              onToggle={() => setIsFilterPanelOpen(!isFilterPanelOpen)}
            />

            {/* Results Section */}
            <section className="results-section">
              {currentView === 'home' && (
                <>
                  {loading && articles.length === 0 ? (
                    <div className="loading-state">
                      <div className="loading-spinner"></div>
                      <p>Loading articles...</p>
                    </div>
                  ) : error ? (
                    <div className="error-state">
                      <div className="error-icon">⚠️</div>
                      <h3>Something went wrong</h3>
                      <p>{error}</p>
                      <button 
                        onClick={() => loadArticles(filters, 1)}
                        className="retry-button"
                      >
                        Try Again
                      </button>
                    </div>
                  ) : articles.length === 0 ? (
                    <div className="empty-state">
                      <div className="empty-icon">📰</div>
                      <h3>No articles found</h3>
                      <p>Try adjusting your search terms or filters</p>
                      <button 
                        onClick={() => handleSearch({})}
                        className="clear-filters-button"
                      >
                        Clear All Filters
                      </button>
                    </div>
                  ) : (
                    <>
                      <div className="results-header">
                        <h3>
                          {filters.q ? `Search results for "${filters.q}"` : 'Latest News'}
                          <span className="results-count">({articles.length} articles)</span>
                        </h3>
                      </div>

                      <div className="articles-grid">
                        {articles.map((article) => (
                          <ArticleCard
                            key={article.uuid}
                            article={article}
                            onBookmark={handleBookmark}
                            showBookmarkButton={true}
                          />
                        ))}
                      </div>

                      {/* Load More Button */}
                      {hasMore && (
                        <div className="load-more-section">
                          <button
                            onClick={loadMore}
                            disabled={loading}
                            className="load-more-button"
                          >
                            {loading ? 'Loading...' : 'Load More Articles'}
                          </button>
                        </div>
                      )}
                    </>
                  )}
                </>
              )}
              {currentView === 'categories' && <CategoriesView />}
              {currentView === 'sources' && <SourcesView />}
              {currentView === 'bookmarks' && <BookmarksView />}
            </section>
          </div>
        </main>

        {/* Authentication Modal */}
        <Modal
          isOpen={isAuthModalOpen}
          onClose={closeAuthModal}
        >
          {authMode === 'login' ? (
            <LoginForm
              onSuccess={closeAuthModal}
              onSwitchToRegister={switchAuthMode}
            />
          ) : (
            <RegisterForm
              onSuccess={closeAuthModal}
              onSwitchToLogin={switchAuthMode}
            />
          )}
        </Modal>

        {/* Toast Notifications */}
        <Toaster
          position="top-right"
          toastOptions={{
            duration: 4000,
            style: {
              background: '#363636',
              color: '#fff',
            },
            success: {
              duration: 3000,
              iconTheme: {
                primary: '#4ade80',
                secondary: '#fff',
              },
            },
            error: {
              duration: 5000,
              iconTheme: {
                primary: '#ef4444',
                secondary: '#fff',
              },
            },
          }}
        />
      </div>
    </AuthProvider>
  );
};

export default App;
