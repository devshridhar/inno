import axios, { AxiosInstance } from 'axios';
import {
  ApiResponse,
  Article,
  Category,
  NewsSource,
  User,
  UserPreferences,
  LoginCredentials,
  RegisterCredentials,
  AuthResponse,
  SearchFilters
} from '../types';

// Create axios instance with base configuration
const api: AxiosInstance = axios.create({
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor to add auth token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Clear token and redirect to login
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// API Service Class
class ApiService {
  // Health Check
  async healthCheck(): Promise<{ status: string; timestamp: string; service: string }> {
    const response = await api.get('/health');
    return response.data;
  }

  // Authentication
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response = await api.post('/auth/login', credentials);
    return response.data;
  }

  async register(credentials: RegisterCredentials): Promise<AuthResponse> {
    const response = await api.post('/auth/register', credentials);
    return response.data;
  }

  async logout(): Promise<void> {
    await api.post('/auth/logout');
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
  }

  async getCurrentUser(): Promise<User> {
    const response = await api.get('/auth/me');
    return response.data.user;
  }

  // Categories
  async getCategories(): Promise<Category[]> {
    const response = await api.get('/categories');
    return response.data.categories;
  }

  async getCategory(slug: string): Promise<Category> {
    const response = await api.get(`/categories/${slug}`);
    return response.data.category;
  }

  // News Sources
  async getSources(): Promise<NewsSource[]> {
    const response = await api.get('/sources');
    return response.data.sources;
  }

  async getSource(slug: string): Promise<NewsSource> {
    const response = await api.get(`/sources/${slug}`);
    return response.data.source;
  }

  // Articles
  async getArticles(filters: SearchFilters = {}): Promise<ApiResponse<Article[]>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await api.get(`/articles?${params.toString()}`);
    return response.data;
  }

  async getArticle(uuid: string): Promise<Article> {
    const response = await api.get(`/articles/${uuid}`);
    return response.data.article;
  }

  // Search
  async searchArticles(filters: SearchFilters = {}): Promise<ApiResponse<Article[]>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await api.get(`/search?${params.toString()}`);
    return response.data;
  }

  async getSearchSuggestions(query: string): Promise<string[]> {
    const response = await api.get(`/search/suggestions?q=${encodeURIComponent(query)}`);
    return response.data.suggestions;
  }

  // User Preferences (Protected routes)
  async getUserPreferences(): Promise<UserPreferences> {
    const response = await api.get('/preferences');
    return response.data.preferences;
  }

  async updateUserPreferences(preferences: Partial<UserPreferences>): Promise<UserPreferences> {
    const response = await api.put('/preferences', preferences);
    return response.data.preferences;
  }

  async addPreferredSource(sourceId: number): Promise<void> {
    await api.post('/preferences/sources', { source_id: sourceId });
  }

  async removePreferredSource(sourceId: number): Promise<void> {
    await api.delete('/preferences/sources', { data: { source_id: sourceId } });
  }

  // Bookmarks (Protected routes)
  async bookmarkArticle(uuid: string): Promise<void> {
    await api.post(`/articles/${uuid}/bookmark`);
  }

  async removeBookmark(uuid: string): Promise<void> {
    await api.delete(`/articles/${uuid}/bookmark`);
  }

  async getBookmarkedArticles(): Promise<ApiResponse<Article[]>> {
    const response = await api.get('/articles/bookmarks');
    return response.data;
  }
}

// Export singleton instance
export const apiService = new ApiService();
export default apiService;
