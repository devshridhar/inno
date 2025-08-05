import React, { useState, useEffect } from 'react';
import { Category, NewsSource, SearchFilters } from '../../types';
import { apiService } from '../../services/api';
import './FilterPanel.css';

interface FilterPanelProps {
  filters: SearchFilters;
  onFiltersChange: (filters: SearchFilters) => void;
  isOpen: boolean;
  onToggle: () => void;
}

const FilterPanel: React.FC<FilterPanelProps> = ({
  filters,
  onFiltersChange,
  isOpen,
  onToggle
}) => {
  const [categories, setCategories] = useState<Category[]>([]);
  const [sources, setSources] = useState<NewsSource[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadFilterData = async () => {
      try {
        const [categoriesData, sourcesData] = await Promise.all([
          apiService.getCategories(),
          apiService.getSources()
        ]);
        setCategories(categoriesData);
        setSources(sourcesData);
      } catch (error) {
        console.error('Failed to load filter data:', error);
      } finally {
        setLoading(false);
      }
    };

    loadFilterData();
  }, []);

  const handleFilterChange = (key: keyof SearchFilters, value: any) => {
    const newFilters = { ...filters, [key]: value };
    onFiltersChange(newFilters);
  };

  const clearAllFilters = () => {
    onFiltersChange({});
  };

  const getActiveFiltersCount = () => {
    return Object.values(filters).filter(value => 
      value !== undefined && value !== null && value !== ''
    ).length;
  };

  if (loading) {
    return (
      <div className="filter-panel">
        <div className="filter-header">
          <button onClick={onToggle} className="filter-toggle">
            Filters <span className="loading-spinner">⏳</span>
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className={`filter-panel ${isOpen ? 'open' : ''}`}>
      <div className="filter-header">
        <button onClick={onToggle} className="filter-toggle">
          <span>Filters</span>
          {getActiveFiltersCount() > 0 && (
            <span className="filter-count">{getActiveFiltersCount()}</span>
          )}
          <span className={`chevron ${isOpen ? 'up' : 'down'}`}>
            {isOpen ? '▲' : '▼'}
          </span>
        </button>
        
        {getActiveFiltersCount() > 0 && (
          <button onClick={clearAllFilters} className="clear-all-button">
            Clear All
          </button>
        )}
      </div>

      {isOpen && (
        <div className="filter-content">
          {/* Categories Filter */}
          <div className="filter-section">
            <h4 className="filter-section-title">Categories</h4>
            <div className="filter-options">
              {categories.map((category) => (
                <label key={category.id} className="filter-option">
                  <input
                    type="radio"
                    name="category"
                    value={category.slug}
                    checked={filters.category === category.slug}
                    onChange={(e) => handleFilterChange('category', e.target.value)}
                  />
                  <span 
                    className="category-indicator"
                    style={{ backgroundColor: category.color }}
                  ></span>
                  <span className="filter-label">
                    {category.name}
                    <span className="article-count">({category.articles_count})</span>
                  </span>
                </label>
              ))}
              {filters.category && (
                <button
                  onClick={() => handleFilterChange('category', undefined)}
                  className="clear-filter-button"
                >
                  Clear Category
                </button>
              )}
            </div>
          </div>

          {/* Sources Filter */}
          <div className="filter-section">
            <h4 className="filter-section-title">News Sources</h4>
            <div className="filter-options">
              {sources.map((source) => (
                <label key={source.id} className="filter-option">
                  <input
                    type="radio"
                    name="source"
                    value={source.slug}
                    checked={filters.source === source.slug}
                    onChange={(e) => handleFilterChange('source', e.target.value)}
                  />
                  <span className="filter-label">
                    {source.name}
                    <span className="article-count">({source.articles_count})</span>
                  </span>
                </label>
              ))}
              {filters.source && (
                <button
                  onClick={() => handleFilterChange('source', undefined)}
                  className="clear-filter-button"
                >
                  Clear Source
                </button>
              )}
            </div>
          </div>

          {/* Author Filter */}
          <div className="filter-section">
            <h4 className="filter-section-title">Author</h4>
            <input
              type="text"
              placeholder="Filter by author name..."
              value={filters.author || ''}
              onChange={(e) => handleFilterChange('author', e.target.value || undefined)}
              className="author-input"
            />
          </div>

          {/* Date Range Filter */}
          <div className="filter-section">
            <h4 className="filter-section-title">Date Range</h4>
            <div className="date-range-inputs">
              <div className="date-input-group">
                <label htmlFor="from-date">From:</label>
                <input
                  id="from-date"
                  type="date"
                  value={filters.from_date || ''}
                  onChange={(e) => handleFilterChange('from_date', e.target.value || undefined)}
                  className="date-input"
                />
              </div>
              <div className="date-input-group">
                <label htmlFor="to-date">To:</label>
                <input
                  id="to-date"
                  type="date"
                  value={filters.to_date || ''}
                  onChange={(e) => handleFilterChange('to_date', e.target.value || undefined)}
                  className="date-input"
                />
              </div>
            </div>
          </div>

          {/* Sort Options */}
          <div className="filter-section">
            <h4 className="filter-section-title">Sort By</h4>
            <div className="sort-options">
              <select
                value={filters.sort_by || 'published_at'}
                onChange={(e) => handleFilterChange('sort_by', e.target.value)}
                className="sort-select"
              >
                <option value="published_at">Date Published</option>
                <option value="relevance">Relevance</option>
                <option value="title">Title</option>
              </select>
              <select
                value={filters.sort_order || 'desc'}
                onChange={(e) => handleFilterChange('sort_order', e.target.value)}
                className="sort-select"
              >
                <option value="desc">Newest First</option>
                <option value="asc">Oldest First</option>
              </select>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default FilterPanel;
