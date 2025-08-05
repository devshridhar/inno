import React, { useState, useEffect, useRef } from 'react';
import { SearchFilters } from '../../types';
import './SearchBar.css';

interface SearchBarProps {
  onSearch: (filters: SearchFilters) => void;
  placeholder?: string;
  initialValue?: string;
  showAdvancedFilters?: boolean;
}

const SearchBar: React.FC<SearchBarProps> = ({ 
  onSearch, 
  placeholder = "Search news articles...", 
  initialValue = "",
  showAdvancedFilters = false
}) => {
  const [query, setQuery] = useState(initialValue);
  const [isAdvancedOpen, setIsAdvancedOpen] = useState(showAdvancedFilters);
  const [suggestions, setSuggestions] = useState<string[]>([]);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const searchInputRef = useRef<HTMLInputElement>(null);
  const suggestionsRef = useRef<HTMLDivElement>(null);

  // Advanced filter states
  const [sortBy, setSortBy] = useState<'published_at' | 'title' | 'relevance'>('published_at');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('desc');
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    performSearch();
  };

  const performSearch = () => {
    const filters: SearchFilters = {
      q: query.trim() || undefined,
      sort_by: sortBy,
      sort_order: sortOrder,
      from_date: fromDate || undefined,
      to_date: toDate || undefined,
    };

    onSearch(filters);
    setShowSuggestions(false);
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value;
    setQuery(value);

    // Show suggestions for queries longer than 2 characters
    if (value.length > 2) {
      // Mock suggestions - in real app, you'd call API
      const mockSuggestions = [
        'technology trends',
        'climate change',
        'artificial intelligence',
        'renewable energy',
        'space exploration'
      ].filter(suggestion => 
        suggestion.toLowerCase().includes(value.toLowerCase())
      );
      setSuggestions(mockSuggestions);
      setShowSuggestions(true);
    } else {
      setShowSuggestions(false);
    }
  };

  const handleSuggestionClick = (suggestion: string) => {
    setQuery(suggestion);
    setShowSuggestions(false);
    // Trigger search with the suggestion
    setTimeout(() => {
      const filters: SearchFilters = {
        q: suggestion,
        sort_by: sortBy,
        sort_order: sortOrder,
        from_date: fromDate || undefined,
        to_date: toDate || undefined,
      };
      onSearch(filters);
    }, 100);
  };

  const clearSearch = () => {
    setQuery('');
    setFromDate('');
    setToDate('');
    setSortBy('published_at');
    setSortOrder('desc');
    onSearch({});
    searchInputRef.current?.focus();
  };

  // Close suggestions when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        suggestionsRef.current && 
        !suggestionsRef.current.contains(event.target as Node) &&
        !searchInputRef.current?.contains(event.target as Node)
      ) {
        setShowSuggestions(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <div className="search-bar-container">
      <form onSubmit={handleSubmit} className="search-form">
        <div className="search-input-container">
          <div className="search-input-wrapper">
            <input
              ref={searchInputRef}
              type="text"
              value={query}
              onChange={handleInputChange}
              placeholder={placeholder}
              className="search-input"
            />
            <div className="search-buttons">
              {query && (
                <button
                  type="button"
                  onClick={clearSearch}
                  className="clear-button"
                  title="Clear search"
                >
                  ✕
                </button>
              )}
              <button
                type="submit"
                className="search-button"
                title="Search"
              >
                🔍
              </button>
            </div>
          </div>

          {showSuggestions && suggestions.length > 0 && (
            <div ref={suggestionsRef} className="suggestions-dropdown">
              {suggestions.map((suggestion, index) => (
                <button
                  key={index}
                  type="button"
                  className="suggestion-item"
                  onClick={() => handleSuggestionClick(suggestion)}
                >
                  🔍 {suggestion}
                </button>
              ))}
            </div>
          )}
        </div>

        <button
          type="button"
          onClick={() => setIsAdvancedOpen(!isAdvancedOpen)}
          className="advanced-toggle"
        >
          {isAdvancedOpen ? 'Hide Filters' : 'Advanced Filters'}
        </button>
      </form>

      {isAdvancedOpen && (
        <div className="advanced-filters">
          <div className="filter-row">
            <div className="filter-group">
              <label htmlFor="sortBy">Sort by:</label>
              <select
                id="sortBy"
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value as any)}
                className="filter-select"
              >
                <option value="published_at">Date Published</option>
                <option value="relevance">Relevance</option>
                <option value="title">Title</option>
              </select>
            </div>

            <div className="filter-group">
              <label htmlFor="sortOrder">Order:</label>
              <select
                id="sortOrder"
                value={sortOrder}
                onChange={(e) => setSortOrder(e.target.value as any)}
                className="filter-select"
              >
                <option value="desc">Newest First</option>
                <option value="asc">Oldest First</option>
              </select>
            </div>
          </div>

          <div className="filter-row">
            <div className="filter-group">
              <label htmlFor="fromDate">From Date:</label>
              <input
                id="fromDate"
                type="date"
                value={fromDate}
                onChange={(e) => setFromDate(e.target.value)}
                className="filter-input"
              />
            </div>

            <div className="filter-group">
              <label htmlFor="toDate">To Date:</label>
              <input
                id="toDate"
                type="date"
                value={toDate}
                onChange={(e) => setToDate(e.target.value)}
                className="filter-input"
              />
            </div>
          </div>

          <div className="filter-actions">
            <button
              type="button"
              onClick={performSearch}
              className="apply-filters-button"
            >
              Apply Filters
            </button>
            <button
              type="button"
              onClick={clearSearch}
              className="clear-filters-button"
            >
              Clear All
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

export default SearchBar;
