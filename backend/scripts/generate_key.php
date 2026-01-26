<?php

require_once __DIR__ . '/../utils/Encryption.php';

echo "===========================================\n";
echo "  Face Recognition Encryption Key Generator\n";
echo "===========================================\n\n";

$key = Encryption::generateKey();

echo "Your new encryption key:\n";
echo "------------------------\n";
echo $key . "\n\n";

echo "Setup Instructions:\n";
echo "-------------------\n";
echo "1. Copy the key above\n";
echo "2. Set it as an environment variable:\n\n";

echo "   Windows (PowerShell):\n";
echo "   \$env:FACE_ENCRYPTION_KEY = \"$key\"\n";
echo "   Or permanently: [System.Environment]::SetEnvironmentVariable('FACE_ENCRYPTION_KEY', '$key', 'User')\n\n";

echo "   Linux/Mac:\n";
echo "   export FACE_ENCRYPTION_KEY=\"$key\"\n";
echo "   (Add to ~/.bashrc for persistence)\n\n";

echo "3. For Apache, add to .htaccess or httpd.conf:\n";
echo "   SetEnv FACE_ENCRYPTION_KEY \"$key\"\n\n";

echo "⚠️  IMPORTANT: Keep this key secure and never commit it to version control!\n";
echo "===========================================\n";
?>
