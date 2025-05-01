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

if (!isset($data['descriptor'])) {
    http_response_code(400);
    echo json_encode(["message" => "Descriptor is required"]);
    exit;
}

$inputDescriptor = $data['descriptor'];

// Connect to database
$conn = new mysqli("localhost", "root", "", "face_recognition");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}

// Fetch all stored users and descriptors
$result = $conn->query("SELECT id, name, phone, descriptor FROM users");
if (!$result) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to query database"]);
    exit;
}

function euclideanDistance($arr1, $arr2) {
    if (count($arr1) !== count($arr2)) return PHP_FLOAT_MAX;
    $sum = 0.0;
    for ($i = 0; $i < count($arr1); $i++) {
        $diff = $arr1[$i] - $arr2[$i];
        $sum += $diff * $diff;
    }
    return sqrt($sum);
}

// Log the captured descriptor for debugging
file_put_contents("debug.log", "Captured Descriptor: " . json_encode($inputDescriptor) . "\n", FILE_APPEND);

$threshold = 0.5; // Adjust this to make the matching stricter
$minDistance = PHP_FLOAT_MAX;
$matchedUser = null;

// Compare descriptors
while ($row = $result->fetch_assoc()) {
    $dbDescriptor = json_decode($row['descriptor'], true);
    
    // Log the database descriptor for debugging
    file_put_contents("debug.log", "Stored Descriptor: " . json_encode($dbDescriptor) . "\n", FILE_APPEND);

    $distance = euclideanDistance($inputDescriptor, $dbDescriptor);

    // Log the distance for debugging
    file_put_contents("debug.log", "Calculated Distance: " . $distance . "\n", FILE_APPEND);
    
    if ($distance < $threshold && $distance < $minDistance) {
        $minDistance = $distance;
        $matchedUser = [
            "id" => $row["id"],
            "name" => $row["name"],
            "phone" => $row["phone"],
            "distance" => $distance
        ];
    }
}

if ($matchedUser) {
    echo json_encode(["match" => true, "user" => $matchedUser]);
} else {
    echo json_encode(["match" => false, "message" => "No match found"]);
}

$conn->close();
?>
