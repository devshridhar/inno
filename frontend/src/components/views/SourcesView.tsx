import React, { useState, useEffect } from 'react';
import { NewsSource } from '../../types';
import { apiService } from '../../services/api';
import './Views.css';

const SourcesView: React.FC = () => {
  const [sources, setSources] = useState<NewsSource[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadSources = async () => {
      try {
        setLoading(true);
        const data = await apiService.getSources();
        setSources(data);
      } catch (err: any) {
        setError(err.response?.data?.message || 'Failed to load sources');
      } finally {
        setLoading(false);
      }
    };

    loadSources();
  }, []);

  if (loading) {
    return (
      <div className="view-container">
        <div className="loading-state">
          <div className="loading-spinner"></div>
          <p>Loading sources...</p>
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
        <h2>📡 News Sources</h2>
        <p>Explore all available news sources</p>
      </div>

      <div className="sources-grid">
        {sources.map((source) => (
          <div key={source.id} className="source-card">
            <div className="source-header">
              {source.logo_url ? (
                <img src={source.logo_url} alt={source.name} className="source-logo" />
              ) : (
                <div className="source-logo-placeholder">📰</div>
              )}
              <div className="source-info">
                <h3>{source.name}</h3>
                <p>{source.description}</p>
              </div>
            </div>
            
            <div className="source-meta">
              <span className="source-language">
                🌐 {source.language?.toUpperCase() || 'EN'}
              </span>
              <span className="source-country">
                🏳️ {source.country?.toUpperCase() || 'US'}
              </span>
              {source.articles_count !== undefined && (
                <span className="article-count">
                  📄 {source.articles_count} articles
                </span>
              )}
            </div>

            {source.url && (
              <a 
                href={source.url} 
                target="_blank" 
                rel="noopener noreferrer"
                className="visit-source-button"
              >
                Visit Source →
              </a>
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default SourcesView;
