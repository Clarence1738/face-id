<?php
// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Read JSON input
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Validate input
if (!isset($data['name'], $data['phone'], $data['descriptor']) || !is_array($data['descriptor'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input: name, phone, and descriptor required."]);
    exit;
}

$name = trim($data['name']);
$phone = trim($data['phone']);
$descriptor = json_encode($data['descriptor'], JSON_UNESCAPED_UNICODE);

// Connect to MySQL
$conn = new mysqli("localhost", "root", "", "face_recognition");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}

// Prepare SQL statement
$stmt = $conn->prepare("INSERT INTO users (name, phone, descriptor) VALUES (?, ?, ?)");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Prepare statement failed"]);
    $conn->close();
    exit;
}

$stmt->bind_param("sss", $name, $phone, $descriptor);

// Execute and respond
if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["message" => "✅ User registered successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "❌ Failed to insert user"]);
}

$stmt->close();
$conn->close();
