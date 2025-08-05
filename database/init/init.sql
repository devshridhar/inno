-- ./database/init/init.sql
-- Initial database setup for News Aggregator

-- Create database if not exists (handled by environment variables)
-- This file runs automatically when PostgreSQL container starts

-- Enable necessary extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "unaccent";

-- Create full-text search configuration for better article search
CREATE TEXT SEARCH CONFIGURATION english_unaccent (COPY = english);
ALTER TEXT SEARCH CONFIGURATION english_unaccent
    ALTER MAPPING FOR asciiword, asciihword, hword_asciipart, word, hword, hword_part
    WITH unaccent, simple;

-- Create initial indexes for better performance
-- These will be created by Laravel migrations, but having them here ensures optimization

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
RETURN NEW;
END;
$$ language 'plpgsql';

-- Create sequence for article IDs (if needed)
CREATE SEQUENCE IF NOT EXISTS articles_id_seq START 1;

-- Grant necessary permissions
GRANT ALL PRIVILEGES ON DATABASE news_aggregator TO postgres;
GRANT ALL PRIVILEGES ON SCHEMA public TO postgres;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO postgres;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO postgres;

-- Insert initial data for news sources
-- This will be handled by Laravel seeders, but keeping reference here

-- Success message
DO $$
BEGIN
    RAISE NOTICE 'Database initialization completed successfully!';
RAISE NOTICE 'Extensions enabled: uuid-ossp, pg_trgm, unaccent';
RAISE NOTICE 'Ready for Laravel migrations and seeders';
END $$;