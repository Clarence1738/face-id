<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/RecognitionLog.php';
require_once __DIR__ . '/../Validator.php';

class RecognitionController {
    private $userModel;
    private $logModel;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->userModel = new User($conn);
        $this->logModel = new RecognitionLog($conn);
    }

    public function recognize() {
        try {
            // Validate request method
            Validator::validateMethod('POST');

            // Get and validate JSON input
            $data = Validator::getJsonInput();
            $data = Validator::sanitizeArray($data);

            // Validate descriptor
            if (!isset($data['descriptor'])) {
                $this->sendResponse(400, ['message' => 'Descriptor is required']);
                return;
            }

            $descriptor = Validator::validateDescriptor($data['descriptor']);

            $inputDescriptor = $descriptor;
            $users = $this->userModel->findAll();
            
            $threshold = 0.35;
            $minDistance = PHP_FLOAT_MAX;
            $matchedUser = null;

            foreach ($users as $user) {
                $dbDescriptor = $user['descriptor'];
                $distance = $this->euclideanDistance($inputDescriptor, $dbDescriptor);

                if ($distance < $threshold && $distance < $minDistance) {
                    $minDistance = $distance;
                    $matchedUser = [
                        "id" => $user["id"],
                        "name" => $user["name"],
                        "phone" => $user["phone"],
                        "distance" => $distance,
                        "confidence" => 1.0 - $distance
                    ];
                }
            }

            if ($matchedUser) {
                $this->sendResponse(200, ["match" => true, "user" => $matchedUser]);
            } else {
                $this->sendResponse(200, ["match" => false, "message" => "No match found"]);
            }
        } catch (Exception $e) {
            $this->sendResponse(400, ['message' => 'Validation error: ' . $e->getMessage()]);
        }
    }

    public function checkIn() {
        try {
            // Validate request method
            Validator::validateMethod('POST');

            // Get and validate JSON input
            $data = Validator::getJsonInput();
            $data = Validator::sanitizeArray($data);

            // Validate required fields
            if (!isset($data['user_id'])) {
                $this->sendResponse(400, ['message' => 'User ID is required']);
                return;
            }

            // Validate inputs
            $user_id = Validator::validateUserId($data['user_id']);
            $confidence = isset($data['confidence']) ? Validator::validateConfidence($data['confidence']) : 1.0;
            $device_ip = $_SERVER['REMOTE_ADDR'];
            $status = 'SUCCESS';

            // Check if already checked in today
            if ($this->logModel->hasCheckedInToday($user_id)) {
                $this->sendResponse(409, ['message' => 'User has already checked in today']);
                return;
            }

            // Get previous hash
            $previousHash = $this->logModel->getPreviousHash($user_id);

            // Generate log hash
            $log_data = $user_id . $confidence . date('Y-m-d H:i:s') . $device_ip . $status;
            $log_hash = hash('sha256', $log_data);

            // Create log
            $result = $this->logModel->create($user_id, $confidence, $device_ip, $status, $previousHash, $log_hash);

            // Also insert into legacy checkins table
            $checkin_query = "INSERT INTO checkins (user_id) VALUES (?)";
            $stmt = $this->conn->prepare($checkin_query);
            
            if (!$stmt) {
                throw new Exception("Failed to prepare checkin statement: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert into checkins table: " . $stmt->error);
            }

            $this->sendResponse(200, [
                "message" => "Check-in successful",
                "log_hash" => $log_hash
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, ['message' => 'Validation error: ' . $e->getMessage()]);
        }
    }

    public function checkInStatus() {
        try {
            // Validate request method
            Validator::validateMethod('POST');

            // Get and validate JSON input
            $data = Validator::getJsonInput();
            $data = Validator::sanitizeArray($data);

            // Validate required fields
            if (!isset($data['user_id'])) {
                $this->sendResponse(400, ['message' => 'User ID is required']);
                return;
            }

            // Validate user ID
            $user_id = Validator::validateUserId($data['user_id']);

            $hasCheckedIn = $this->logModel->hasCheckedInToday($user_id);
            $this->sendResponse(200, ['checkedIn' => $hasCheckedIn]);
        } catch (Exception $e) {
            $this->sendResponse(400, ['message' => 'Validation error: ' . $e->getMessage()]);
        }
    }

    private function euclideanDistance($arr1, $arr2) {
        if (count($arr1) !== count($arr2)) return PHP_FLOAT_MAX;
        $sum = 0.0;
        for ($i = 0; $i < count($arr1); $i++) {
            $diff = $arr1[$i] - $arr2[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }

    private function sendResponse($code, $data) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
?>
