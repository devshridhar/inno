import React, { useState, useEffect } from 'react';
import { Article } from '../../types';
import { apiService } from '../../services/api';
import { useAuth } from '../../contexts/AuthContext';
import ArticleCard from '../articles/ArticleCard';
import './Views.css';

const BookmarksView: React.FC = () => {
  const { isAuthenticated } = useAuth();
  const [articles, setArticles] = useState<Article[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!isAuthenticated) {
      setLoading(false);
      return;
    }

    const loadBookmarks = async () => {
      try {
        setLoading(true);
        const response = await apiService.getBookmarkedArticles();
        setArticles(response.data || []);
      } catch (err: any) {
        setError(err.response?.data?.message || 'Failed to load bookmarks');
      } finally {
        setLoading(false);
      }
    };

    loadBookmarks();
  }, [isAuthenticated]);

  const handleBookmark = async (uuid: string) => {
    try {
      const article = articles.find(a => a.uuid === uuid);
      if (article?.is_bookmarked) {
        await apiService.removeBookmark(uuid);
        // Remove from bookmarks view
        setArticles(prev => prev.filter(a => a.uuid !== uuid));
      } else {
        await apiService.bookmarkArticle(uuid);
      }
    } catch (error) {
      console.error('Failed to toggle bookmark:', error);
    }
  };

  if (!isAuthenticated) {
    return (
      <div className="view-container">
        <div className="empty-state">
          <div className="empty-icon">🔒</div>
          <h3>Sign In Required</h3>
          <p>Please sign in to view your bookmarked articles.</p>
        </div>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="view-container">
        <div className="loading-state">
          <div className="loading-spinner"></div>
          <p>Loading bookmarks...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="view-container">
        <div className="error-state">
          <div className="error-icon">⚠️</div>
          <h3>Something went wrong</h3>
          <p>{error}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="view-container">
      <div className="view-header">
        <h2>🔖 Bookmarked Articles</h2>
        <p>Your saved articles for later reading</p>
      </div>

      {articles.length === 0 ? (
        <div className="empty-state">
          <div className="empty-icon">📚</div>
          <h3>No Bookmarks Yet</h3>
          <p>Start bookmarking articles you want to read later!</p>
        </div>
      ) : (
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
      )}
    </div>
  );
};

export default BookmarksView;
