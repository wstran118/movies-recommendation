<?php
declare(strict_types=1);
// config/database.php - Database configuration
const DB_CONFIG = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'movie_api',
    'user' => 'postgres',
    'password' => 'your_postgres_password'
];
const JWT_SECRET = 'your_jwt_secret_key_123456';
const JWT_EXPIRY = 3600; // 1 hour
?>