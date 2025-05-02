<?php
// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    exit(0);
}

// Set headers for POST requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// Read and decode JSON input
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!isset($data['user_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "User ID is required"]);
    exit;
}

$user_id = $data['user_id'];

// Connect to database
$conn = new mysqli("localhost", "root", "", "face_recognition");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}

// Insert check-in record
$query = "INSERT INTO checkins (user_id) VALUES (?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Check-in successful"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to check in"]);
}

$conn->close();
?>
