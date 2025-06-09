-- schema.sql - PostgreSQL database schema
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS movies (
    id SERIAL PRIMARY KEY,
    title TEXT NOT NULL,
    genre TEXT NOT NULL,
    release_year INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS ratings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    movie_id INTEGER REFERENCES movies(id),
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    UNIQUE (user_id, movie_id)
);

CREATE TABLE IF NOT EXISTS activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES

 users(id) ON DELETE SET NULL,
    action TEXT NOT NULL,
    description TEXT NOT NULL,
    execution_time_ms FLOAT NOT NULL,
    created_at TIMESTAMP NOT NULL
);

-- Insert sample movies
INSERT INTO movies (title, genre, release_year) VALUES
('Inception', 'Sci-Fi', 2010),
('The Shawshank Redemption', 'Drama', 1994),
('The Dark Knight', 'Action', 2008),
('Pulp Fiction', 'Crime', 1994),
('Interstellar', 'Sci-Fi', 2014)
ON CONFLICT DO NOTHING;