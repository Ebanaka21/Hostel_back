-- Enable pg_stat_statements extension for query monitoring
CREATE EXTENSION IF NOT EXISTS pg_stat_statements;

-- Create optimized indexes for common queries
-- Users table indexes
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_users_created_at ON users(created_at);

-- Friends table indexes
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_friends_user_id ON friends(user_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_friends_friend_id ON friends(friend_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_friends_status ON friends(status);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_friends_created_at ON friends(created_at);

-- Optimize autovacuum settings for better performance
ALTER TABLE users SET (autovacuum_vacuum_scale_factor = 0.02);
ALTER TABLE friends SET (autovacuum_vacuum_scale_factor = 0.02);
