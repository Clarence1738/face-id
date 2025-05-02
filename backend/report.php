<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "face_recognition");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}

$sql = "SELECT u.name, u.phone, c.checkin_time 
        FROM checkins c
        JOIN users u ON c.user_id = u.id
        ORDER BY c.checkin_time DESC";

$result = $conn->query($sql);

if ($result === false) {
    http_response_code(500);
    echo json_encode(["message" => "Query failed"]);
    exit;
}

$checkins = [];
while ($row = $result->fetch_assoc()) {
    $checkins[] = $row;
}

echo json_encode($checkins);
$conn->close();
?>
