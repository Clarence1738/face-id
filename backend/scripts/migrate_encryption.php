<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Encryption.php';

echo "===========================================\n";
echo "  Face Descriptor Encryption Migration\n";
echo "===========================================\n\n";

try {
    Encryption::init();
    
    echo "Connecting to database...\n";
    $db = new Database();
    $conn = $db->connect();
    
    echo "Connected successfully!\n\n";
    
    echo "Updating database schema...\n";
    $conn->query("ALTER TABLE users MODIFY COLUMN descriptor TEXT");
    echo "Schema updated.\n\n";
    
    echo "Fetching all users...\n";
    $result = $conn->query("SELECT id, name, descriptor FROM users");
    $totalUsers = $result->num_rows;
    
    if ($totalUsers === 0) {
        echo "No users found in database. Migration not needed.\n";
        exit(0);
    }
    
    echo "Found $totalUsers user(s) to process.\n\n";
    
    $encrypted = 0;
    $skipped = 0;
    $errors = 0;
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $name = $row['name'];
        
        echo "Processing User ID $id ($name)... ";
        
        try {
            Encryption::decrypt($row['descriptor']);
            echo "Already encrypted, skipping.\n";
            $skipped++;
            continue;
        } catch (Exception $e) {
        }
        
        try {
            $descriptor = json_decode($row['descriptor'], true);
            
            if ($descriptor === null) {
                throw new Exception("Invalid JSON format");
            }
            
            $encryptedData = Encryption::encrypt($descriptor);
            
            $stmt = $conn->prepare("UPDATE users SET descriptor = ? WHERE id = ?");
            $stmt->bind_param("si", $encryptedData, $id);
            
            if ($stmt->execute()) {
                echo "Encrypted successfully!\n";
                $encrypted++;
            } else {
                throw new Exception("Database update failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    
    echo "\n===========================================\n";
    echo "Migration Summary:\n";
    echo "-------------------\n";
    echo "Total users:      $totalUsers\n";
    echo "Encrypted:        $encrypted\n";
    echo "Already encrypted: $skipped\n";
    echo "Errors:           $errors\n";
    echo "===========================================\n";
    
    if ($errors === 0) {
        echo "\n✓ Migration completed successfully!\n";
    } else {
        echo "\n⚠ Migration completed with errors. Please review the output above.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Migration failed!\n";
    exit(1);
}
?>
