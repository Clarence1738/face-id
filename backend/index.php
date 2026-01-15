<?php

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Require dependencies
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/Router.php';

try {
    // Initialize database
    $database = new Database();
    $conn = $database->connect();

    // Initialize router
    $router = new Router($conn);

    // Define routes
    $router->route('POST', '/register', 'UserController', 'register');
    $router->route('POST', '/recognize', 'RecognitionController', 'recognize');
    $router->route('POST', '/checkin', 'RecognitionController', 'checkIn');
    $router->route('POST', '/checkin-status', 'RecognitionController', 'checkInStatus');
    $router->route('GET', '/report', 'ReportController', 'getReport');

    // Dispatch request
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestPath = $_SERVER['REQUEST_URI'];

    $router->dispatch($requestMethod, $requestPath);

    // Close database connection
    $database->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Server error: ' . $e->getMessage()]);
}
?>
