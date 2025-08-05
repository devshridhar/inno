// API Response Types
export interface ApiResponse<T> {
  data?: T;
  meta?: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
  };
  links?: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

// Core Entity Types
export interface Category {
  id: number;
  name: string;
  slug: string;
  description: string;
  color: string;
  icon: string | null;
  articles_count: number;
  is_active: boolean;
}

export interface NewsSource {
  id: number;
  name: string;
  slug: string;
  description: string;
  url: string;
  logo_url: string | null;
  language: string;
  country: string;
  articles_count: number;
  last_scraped_at: string | null;
  is_active: boolean;
}

export interface Article {
  id: number;
  uuid: string;
  title: string;
  description: string;
  content: string;
  url: string;
  image_url: string | null;
  published_at: string;
  author: string | null;
  news_source: NewsSource;
  category: Category;
  is_bookmarked?: boolean;
  created_at: string;
  updated_at: string;
}

// User Types
export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface UserPreferences {
  id: number;
  user_id: number;
  preferred_categories: number[];
  preferred_sources: number[];
  preferred_authors: string[];
  language: string;
  country: string;
  articles_per_page: number;
  email_notifications: boolean;
  created_at: string;
  updated_at: string;
}

// Auth Types
export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterCredentials {
  first_name: string;
  last_name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface AuthResponse {
  user: User;
  token: string;
  expires_at: string;
}

// Search and Filter Types
export interface SearchFilters {
  q?: string;
  category?: string;
  source?: string;
  author?: string;
  from_date?: string;
  to_date?: string;
  sort_by?: 'published_at' | 'title' | 'relevance';
  sort_order?: 'asc' | 'desc';
  per_page?: number;
  page?: number;
}

// Component Props Types
export interface ArticleCardProps {
  article: Article;
  onBookmark?: (articleId: string) => void;
  showBookmarkButton?: boolean;
}

export interface SearchBarProps {
  onSearch: (query: string) => void;
  placeholder?: string;
  initialValue?: string;
}

export interface FilterPanelProps {
  categories: Category[];
  sources: NewsSource[];
  filters: SearchFilters;
  onFiltersChange: (filters: SearchFilters) => void;
}
