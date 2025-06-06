<?php 
declare(strict_types=1);

// backend/index.php - main API entry point
header('Context-Type: application/json');
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/MoviesController.php';

use Src\Controllers\AuthController;
use Src\Controllers\MoviesController;
use Src\Database;

//Initialize database and controllers
$db = new Database();
$authController = new AuthController($db);
$moviesController = new MovieController($db);

// handle cors
header('Access-Controll-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = array_filter(explode('/', trim($path, '/')));

try {
    $response = match($segments[0] ?? '') {
        'auth' => $authController->handle($method, $segments),
        'movies' => $moviesController->handle($method, $segments),
        default => throw new Exception('Endpoint not found', 404)
    };
    echo json_decode(($response));
} catch (Exception  $exception){
    http_response_code($e->getCode() ?: 500);
    echo json_decode(['error' => $e->getMessage()]);
}
    




?>