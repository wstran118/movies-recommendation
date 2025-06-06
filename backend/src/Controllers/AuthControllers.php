<?php
declare(strict_types=1);

namespace Src\Services\AuthService;
namespace Src\Database;

use Exception;

class AuthControllers {
    private AuthService $authService;

    public function __construct(Database $db){
        $this->authService = new AuthService($db);
    }

    public function handle(string $method, array $segments) : array {
        if($method !== 'POST' || !isset($segments[1])) {
            throw new Exception('Invalid request', 400);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        return match($segments[1]) {
            'register' => $this->authService->register($data['username'] ?? '', $data['password'] ?? ''),
            'login' => $this->authService->login($data['username']  ?? '', $data['password'] ?? ''),
            default => throw new Exception('Invalid auth point', 404)
        };
    }
}
?>