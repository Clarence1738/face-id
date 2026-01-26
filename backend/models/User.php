<?php

require_once __DIR__ . '/../utils/Encryption.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($name, $phone, $descriptor) {
        $query = "INSERT INTO " . $this->table . " (name, phone, descriptor) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->conn->error);
        }

        $encrypted_descriptor = Encryption::encrypt($descriptor);
        $stmt->bind_param("sss", $name, $phone, $encrypted_descriptor);

        if ($stmt->execute()) {
            return ['success' => true, 'user_id' => $this->conn->insert_id];
        } else {
            throw new Exception("Failed to create user: " . $stmt->error);
        }
    }

    public function findAll() {
        $query = "SELECT id, name, phone, descriptor FROM " . $this->table;
        $result = $this->conn->query($query);

        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $row['descriptor'] = Encryption::decrypt($row['descriptor']);
            $users[] = $row;
        }
        return $users;
    }

    public function findById($id) {
        $query = "SELECT id, name, phone, descriptor FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc();
        
        if ($user && isset($user['descriptor'])) {
            $user['descriptor'] = Encryption::decrypt($user['descriptor']);
        }
        
        return $user;
    }

    public function phoneExists($phone) {
        $query = "SELECT id FROM " . $this->table . " WHERE phone = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
}
?>
