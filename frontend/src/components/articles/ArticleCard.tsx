import React from 'react';
import { format } from 'date-fns';
import { Article } from '../../types';
import { useAuth } from '../../contexts/AuthContext';
import './ArticleCard.css';

interface ArticleCardProps {
  article: Article;
  onBookmark?: (articleId: string) => void;
  showBookmarkButton?: boolean;
}

const ArticleCard: React.FC<ArticleCardProps> = ({ 
  article, 
  onBookmark, 
  showBookmarkButton = true 
}) => {
  const { isAuthenticated } = useAuth();

  const handleBookmarkClick = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (onBookmark) {
      onBookmark(article.uuid);
    }
  };

  const handleCardClick = () => {
    window.open(article.url, '_blank', 'noopener,noreferrer');
  };

  const formatDate = (dateString: string) => {
    try {
      return format(new Date(dateString), 'MMM d, yyyy • h:mm a');
    } catch {
      return 'Unknown date';
    }
  };

  return (
    <article className="article-card" onClick={handleCardClick}>
      {article.image_url && (
        <div className="article-image">
          <img 
            src={article.image_url} 
            alt={article.title}
            loading="lazy"
            onError={(e) => {
              const target = e.target as HTMLImageElement;
              target.style.display = 'none';
            }}
          />
        </div>
      )}
      
      <div className="article-content">
        <div className="article-header">
          <div className="article-meta">
            <span 
              className="category-badge" 
              style={{ backgroundColor: article.category.color }}
            >
              {article.category.name}
            </span>
            <span className="source-name">{article.news_source.name}</span>
          </div>
          
          {isAuthenticated && showBookmarkButton && (
            <button
              className={`bookmark-button ${article.is_bookmarked ? 'bookmarked' : ''}`}
              onClick={handleBookmarkClick}
              title={article.is_bookmarked ? 'Remove bookmark' : 'Add bookmark'}
            >
              {article.is_bookmarked ? '🔖' : '📑'}
            </button>
          )}
        </div>

        <h3 className="article-title">{article.title}</h3>
        
        {article.description && (
          <p className="article-description">{article.description}</p>
        )}

        <div className="article-footer">
          <div className="article-info">
            {article.author && (
              <span className="article-author">By {article.author}</span>
            )}
            <span className="article-date">
              {formatDate(article.published_at)}
            </span>
          </div>
          
          <div className="read-more">
            <span>Read more →</span>
          </div>
        </div>
      </div>
    </article>
  );
};

export default ArticleCard;
