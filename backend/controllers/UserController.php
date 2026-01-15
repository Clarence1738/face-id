<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../Validator.php';

class UserController {
    private $userModel;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->userModel = new User($conn);
    }

    public function register() {
        try {
            // Validate request method
            Validator::validateMethod('POST');

            // Get and validate JSON input
            $data = Validator::getJsonInput();
            $data = Validator::sanitizeArray($data);

            // Validate required fields
            if (!isset($data['name'], $data['phone'], $data['descriptor'])) {
                $this->sendResponse(400, ['message' => 'Missing required fields: name, phone, descriptor']);
                return;
            }

            // Validate each field
            $name = Validator::validateString($data['name'], 2, 100, 'Name');
            $phone = Validator::validatePhone($data['phone']);
            $descriptor = Validator::validateDescriptor($data['descriptor']);

            // Validate phone doesn't already exist
            if ($this->userModel->phoneExists($phone)) {
                $this->sendResponse(400, ['message' => 'Phone number already registered']);
                return;
            }

            $result = $this->userModel->create($name, $phone, $descriptor);
            $this->sendResponse(200, [
                'message' => 'User registered successfully',
                'user_id' => $result['user_id']
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, ['message' => $e->getMessage()]);
        }
    }

    public function getAllUsers() {
        try {
            $users = $this->userModel->findAll();
            $this->sendResponse(200, ['users' => $users]);
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Failed to retrieve users: ' . $e->getMessage()]);
        }
    }

    private function sendResponse($code, $data) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
?>
