<?php

class RecognitionLog {
    private $conn;
    private $table = 'recognition_logs';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($user_id, $confidence, $device_ip, $status, $previous_hash = null, $log_hash = null) {
        $query = "INSERT INTO " . $this->table . " (user_id, confidence, device_ip, status, previous_hash, log_hash) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->conn->error);
        }

        $stmt->bind_param("idssss", $user_id, $confidence, $device_ip, $status, $previous_hash, $log_hash);

        if ($stmt->execute()) {
            return ['success' => true, 'log_id' => $this->conn->insert_id];
        } else {
            throw new Exception("Failed to create log: " . $stmt->error);
        }
    }

    public function getSuccessfulCheckIns() {
        $query = "SELECT u.name, u.phone, rl.recognized_at as checkin_time, rl.confidence, rl.device_ip, rl.status
                  FROM " . $this->table . " rl
                  JOIN users u ON rl.user_id = u.id
                  WHERE rl.status = 'SUCCESS'
                  ORDER BY rl.recognized_at DESC";

        $result = $this->conn->query($query);

        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        return $logs;
    }

    public function hasCheckedInToday($user_id) {
        $today = date('Y-m-d');
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE user_id = ? AND DATE(recognized_at) = ? AND status = 'SUCCESS' LIMIT 1";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->conn->error);
        }

        $stmt->bind_param("is", $user_id, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    public function getPreviousHash($user_id) {
        $query = "SELECT log_hash FROM " . $this->table . " WHERE user_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['log_hash'];
        }
        return null;
    }
}
?>
