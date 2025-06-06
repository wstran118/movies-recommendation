<?php
declare(strict_types=1);

namespace Backend\Src\Controllers;

use ErrorException;
use Exception;
use Src\Services\AuthService;
use Src\Services\MovieService;
use Src\Database;

class MoviesController {
    private AuthSerivce $authService;
    private MovieService $movieService;

    public function __construct(Database $db) {
        $this->authService = new AuthService($db);
        $this->movieService = new MovieService($db);
    }

    public function handle(string $method, array $segments): array {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if(!$this->authService->verifyToken($token)) {
            throw new Exception('Unauthorized', 401);
        }

        return match(true) {
            $method === 'GET' && !isset($segments[1]) => $this->movieService->getMovies(),
            $method === 'POST' && $segments[1] === 'recommend' => $this->movieService->getRecommendations($segments[2] ?? null),
            $method === 'POST' && $segments[1] === 'rate'  => $this->movieService->rateMovie(
                json_decode(file_get_contents('php://input'), true) ?? [], 
                $this->authService->getUserID($token)
            ),
            $method === 'GET' && $segments[1] === 'trending' => $this->movieService->getTrending(),
            default => throw new Exception('Invalid endpoint', 404)
        };
    }
}


?>