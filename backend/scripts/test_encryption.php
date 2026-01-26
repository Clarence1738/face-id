<?php

require_once __DIR__ . '/../utils/Encryption.php';

echo "===========================================\n";
echo "  Encryption Test Script\n";
echo "===========================================\n\n";

echo "Test 1: Basic encryption/decryption\n";
echo "-----------------------------------\n";

$testData = [0.1, 0.2, 0.3, 0.4, 0.5];
echo "Original data: " . json_encode($testData) . "\n";

try {
    $encrypted = Encryption::encrypt($testData);
    echo "Encrypted: " . substr($encrypted, 0, 50) . "...\n";
    echo "Length: " . strlen($encrypted) . " bytes\n";
    
    $decrypted = Encryption::decrypt($encrypted);
    echo "Decrypted: " . json_encode($decrypted) . "\n";
    
    if ($testData === $decrypted) {
        echo "✓ Test 1 PASSED: Data matches!\n\n";
    } else {
        echo "✗ Test 1 FAILED: Data mismatch!\n\n";
    }
} catch (Exception $e) {
    echo "✗ Test 1 FAILED: " . $e->getMessage() . "\n\n";
}

echo "Test 2: Face descriptor (128 dimensions)\n";
echo "----------------------------------------\n";

$faceDescriptor = array_fill(0, 128, 0.0);
for ($i = 0; $i < 128; $i++) {
    $faceDescriptor[$i] = (float)rand(-1000, 1000) / 1000;
}

echo "Descriptor size: 128 dimensions\n";

try {
    $startTime = microtime(true);
    $encrypted = Encryption::encrypt($faceDescriptor);
    $encryptTime = microtime(true) - $startTime;
    
    $startTime = microtime(true);
    $decrypted = Encryption::decrypt($encrypted);
    $decryptTime = microtime(true) - $startTime;
    
    echo "Encryption time: " . round($encryptTime * 1000, 2) . " ms\n";
    echo "Decryption time: " . round($decryptTime * 1000, 2) . " ms\n";
    echo "Encrypted size: " . strlen($encrypted) . " bytes\n";
    
    $matches = true;
    for ($i = 0; $i < 128; $i++) {
        if (abs($faceDescriptor[$i] - $decrypted[$i]) > 0.0001) {
            $matches = false;
            break;
        }
    }
    
    if ($matches) {
        echo "✓ Test 2 PASSED: Face descriptor preserved!\n\n";
    } else {
        echo "✗ Test 2 FAILED: Descriptor data mismatch!\n\n";
    }
} catch (Exception $e) {
    echo "✗ Test 2 FAILED: " . $e->getMessage() . "\n\n";
}

echo "Test 3: Randomization (different IVs)\n";
echo "--------------------------------------\n";

try {
    $data = [1, 2, 3];
    $encrypted1 = Encryption::encrypt($data);
    $encrypted2 = Encryption::encrypt($data);
    
    if ($encrypted1 !== $encrypted2) {
        echo "✓ Test 3 PASSED: Each encryption uses unique IV!\n\n";
    } else {
        echo "✗ Test 3 FAILED: Encryptions are identical (security issue)!\n\n";
    }
} catch (Exception $e) {
    echo "✗ Test 3 FAILED: " . $e->getMessage() . "\n\n";
}

echo "Test 4: Error handling\n";
echo "----------------------\n";

try {
    $result = Encryption::decrypt("invalid_data");
    echo "✗ Test 4 FAILED: Should have thrown an exception!\n\n";
} catch (Exception $e) {
    echo "✓ Test 4 PASSED: Invalid data rejected properly!\n\n";
}

echo "===========================================\n";
echo "All tests completed!\n";
echo "===========================================\n";
?>
