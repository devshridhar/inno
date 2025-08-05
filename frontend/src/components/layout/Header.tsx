import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import './Header.css';

interface HeaderProps {
  onAuthModalOpen: () => void;
  currentView: 'home' | 'categories' | 'sources' | 'bookmarks';
  onNavigate: (view: 'home' | 'categories' | 'sources' | 'bookmarks') => void;
}

const Header: React.FC<HeaderProps> = ({ onAuthModalOpen, currentView, onNavigate }) => {
  const { user, isAuthenticated, logout } = useAuth();
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);

  const handleLogout = async () => {
    await logout();
    setIsUserMenuOpen(false);
  };

  return (
    <header className="app-header">
      <div className="header-container">
        <div className="header-left">
          <div className="logo">
            <h1>📰 News Aggregator</h1>
            <span className="tagline">Stay informed, stay ahead</span>
          </div>
        </div>

        <nav className="header-nav">
          <button 
            onClick={() => onNavigate('home')} 
            className={`nav-link ${currentView === 'home' ? 'active' : ''}`}
          >
            Home
          </button>
          <button 
            onClick={() => onNavigate('categories')} 
            className={`nav-link ${currentView === 'categories' ? 'active' : ''}`}
          >
            Categories
          </button>
          <button 
            onClick={() => onNavigate('sources')} 
            className={`nav-link ${currentView === 'sources' ? 'active' : ''}`}
          >
            Sources
          </button>
          {isAuthenticated && (
            <button 
              onClick={() => onNavigate('bookmarks')} 
              className={`nav-link ${currentView === 'bookmarks' ? 'active' : ''}`}
            >
              Bookmarks
            </button>
          )}
        </nav>

        <div className="header-right">
          {isAuthenticated ? (
            <div className="user-menu">
              <button
                className="user-menu-trigger"
                onClick={() => setIsUserMenuOpen(!isUserMenuOpen)}
              >
                <div className="user-avatar">
                  {user?.name.charAt(0).toUpperCase()}
                </div>
                <span className="user-name">{user?.name}</span>
                <span className={`chevron ${isUserMenuOpen ? 'up' : 'down'}`}>
                  ▼
                </span>
              </button>

              {isUserMenuOpen && (
                <div className="user-menu-dropdown">
                  <div className="user-info">
                    <div className="user-details">
                      <strong>{user?.name}</strong>
                      <span>{user?.email}</span>
                    </div>
                  </div>
                  <div className="menu-divider"></div>
                  <button className="menu-item">
                    ⚙️ Preferences
                  </button>
                  <button className="menu-item">
                    🔖 My Bookmarks
                  </button>
                  <button className="menu-item">
                    📊 Reading Stats
                  </button>
                  <div className="menu-divider"></div>
                  <button className="menu-item logout" onClick={handleLogout}>
                    🚪 Sign Out
                  </button>
                </div>
              )}
            </div>
          ) : (
            <button
              className="auth-button"
              onClick={onAuthModalOpen}
            >
              Sign In
            </button>
          )}
        </div>
      </div>
    </header>
  );
};

export default Header;
