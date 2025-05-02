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

// Check if user has already checked in
$query = "SELECT * FROM checkins WHERE user_id = ? ORDER BY checkin_time DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["checkedIn" => true]);
} else {
    echo json_encode(["checkedIn" => false]);
}

$conn->close();
?>
