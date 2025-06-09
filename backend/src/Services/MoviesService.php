<?php
declare(strict_types=1);
// src/Services/MoviesService.php - Movie handling and recommendations
namespace Src\Services;

use Src\Database;
use PDO;

class MoviesService
{
    private PDO $db;

    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }

    public function getMovies(): array
    {
        $starttime = microtime(true);
        $stmt = $this->db->query("SELECT * FROM movies");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //log
        $execution_time = microtime(true) - $starttime;
        $this->logActivity(null, 'GET_MOVIES', 'Retrieved all movies', $execution_time);
        return $result;
    }//

    public function rateMovie(array $data, int $userId): array
    {
        $movieId = $data['movie_id'] ?? 0;
        $rating = $data['rating'] ?? 0;

        if ($rating < 1 || $rating > 5) {
            throw new Exception('Rating must be between 1 and 5', 400);
        }

        $starttime = microtime(true);
        $stmt = $this->db->prepare(<<<'SQL'
            INSERT INTO ratings (user_id, movie_id, rating)
            VALUES (:user_id, :movie_id, :rating)
            ON CONFLICT (user_id, movie_id) DO UPDATE SET rating = EXCLUDED.rating
        SQL);
        $stmt->execute([
            'user_id' => $userId,
            'movie_id' => $movieId,
            'rating' => $rating
        ]);

        //log
        $execution_time = microtime(true) - $starttime;
        $this->logActivity($userId, 'RATE_MOVIE', "User rated movie ID $movieId with $rating", $execution_time);
        return ['message' => 'Rating submitted successfully'];
    }

    public function getRecommendations(?string $userId): array
    {
        $starttime = microtime(true);
        if ($userId) {
            $stmt = $this->db->prepare(<<<'SQL'
                SELECT m.*
                FROM movies m
                LEFT JOIN ratings r ON m.id = r.movie_id
                WHERE r.user_id IN (
                    SELECT r2.user_id
                    FROM ratings r1
                    JOIN ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id != r2.user_id
                    WHERE r1.user_id = :user_id AND r1.rating >= 4
                )
                AND m.id NOT IN (
                    SELECT movie_id FROM ratings WHERE user_id = :user_id
                )
                GROUP BY m.id
                ORDER BY AVG(r.rating) DESC
                LIMIT 5
            SQL);
            $stmt->execute(['user_id' => $userId]);
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //log
            $execution_time = microtime(true) - $starttime;
            $this->logActivity($userId, 'GET_RECOMMENDATIONS', "Retrieved recommendations for user $userId", $execution_time);
            return $movies ?: $this->getTrendingMovies();
        }
        return $this->getTrendingMovies();
    }

    public function getTrendingMovies(): array
    {
        $starttime = microtime(true);
        $stmt = $this->db->query(<<<'SQL'
            SELECT m.*, AVG(r.rating) as avg_rating
            FROM movies m
            LEFT JOIN ratings r ON m.id = r.movie_id
            GROUP BY m.id
            ORDER BY avg_rating DESC NULLS LAST, m.id
            LIMIT 5
        SQL);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //log
        $execution_time = microtime(true) - $starttime;
        $this->logActivity(null, 'GET_TRENDING', "Retrieved trending movies", $execution_time);
         return $result;
    }// end getTrendingMovvies()

    private function logActivity(?int $userId, string $action, string $description, float $execution_time): void
    {
        $stmt = $this->db->prepare("INSERT INTO activity_logs (user_id, action, description, execution_time_ms, craeted_at ) 
                                    VALUES (:user_id, :action, :description, :execution_time_ms, NOW())");
        $stmt->execute([
            "user_id"=> $userId,
            "action"=> $action,
            "description" => $description,
            "execution_time_ms" => round($execution_time * 10,2)
        ]);
    }
}
?>