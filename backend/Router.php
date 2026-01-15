<?php

class Router {
    private $routes = [];
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function route($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function dispatch($requestMethod, $requestPath) {
        // Remove query string from path
        $requestPath = parse_url($requestPath, PHP_URL_PATH);
        
        // Remove the directory path to get only the route part after index.php
        // Handle both /face-id/backend/index.php/route and /index.php/route formats
        if (strpos($requestPath, 'index.php') !== false) {
            $requestPath = substr($requestPath, strpos($requestPath, 'index.php') + strlen('index.php'));
        }
        
        // Remove leading/trailing slashes and make lowercase
        $requestPath = strtolower(trim($requestPath, '/'));

        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($requestMethod) && $this->pathMatches($route['path'], $requestPath)) {
                return $this->executeRoute($route);
            }
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Route not found']);
        exit();
    }

    private function pathMatches($routePath, $requestPath) {
        $routePath = strtolower(trim($routePath, '/'));
        return $routePath === $requestPath;
    }

    private function executeRoute($route) {
        $controllerName = $route['controller'];
        $action = $route['action'];

        require_once __DIR__ . '/controllers/' . $controllerName . '.php';

        $controller = new $controllerName($this->conn);
        
        if (method_exists($controller, $action)) {
            return $controller->$action();
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Action not found']);
            exit();
        }
    }
}
?>
