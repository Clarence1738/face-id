<?php

class Encryption {
    private static $cipher = "AES-256-CBC";
    private static $encryption_key = 'your-32-byte-secret-key-here!';
    
    public static function init() {
        $env_key = getenv('FACE_ENCRYPTION_KEY');
        if ($env_key && strlen($env_key) === 32) {
            self::$encryption_key = $env_key;
        }
    }
    
    public static function encrypt($data) {
        self::init();
        
        $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $iv_length = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($iv_length);
        
        $encrypted = openssl_encrypt(
            $json_data,
            self::$cipher,
            self::$encryption_key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($encrypted === false) {
            throw new Exception("Encryption failed");
        }
        
        return base64_encode($iv . $encrypted);
    }
    
    public static function decrypt($encrypted_data) {
        self::init();
        
        $decoded = base64_decode($encrypted_data);
        
        if ($decoded === false) {
            throw new Exception("Invalid encrypted data format");
        }
        
        $iv_length = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($decoded, 0, $iv_length);
        $encrypted = substr($decoded, $iv_length);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            self::$cipher,
            self::$encryption_key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($decrypted === false) {
            throw new Exception("Decryption failed");
        }
        
        $data = json_decode($decrypted, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to decode decrypted data: " . json_last_error_msg());
        }
        
        return $data;
    }
    
    public static function generateKey() {
        $key = openssl_random_pseudo_bytes(32);
        return base64_encode($key);
    }
}
?>
