<?php

class Validator {
    
    /**
     * Validate and sanitize a string
     * @param mixed $value Value to validate
     * @param int $minLength Minimum length
     * @param int $maxLength Maximum length
     * @param string $fieldName Field name for error messages
     * @return string Sanitized string
     * @throws Exception if validation fails
     */
    public static function validateString($value, $minLength = 1, $maxLength = 255, $fieldName = "Field") {
        if (!isset($value) || $value === null || $value === '') {
            throw new Exception("$fieldName is required");
        }
        
        $value = trim($value);
        
        if (strlen($value) < $minLength) {
            throw new Exception("$fieldName must be at least $minLength characters");
        }
        
        if (strlen($value) > $maxLength) {
            throw new Exception("$fieldName must not exceed $maxLength characters");
        }
        
        // Basic XSS prevention - remove any HTML tags
        $value = strip_tags($value);
        
        return $value;
    }
    
    /**
     * Validate a phone number
     * @param string $phone Phone number to validate
     * @return string Sanitized phone number
     * @throws Exception if validation fails
     */
    public static function validatePhone($phone) {
        if (!isset($phone) || $phone === null || $phone === '') {
            throw new Exception("Phone number is required");
        }
        
        $phone = trim($phone);
        
        // Remove common formatting characters
        $phone = preg_replace('/[\s\-\(\)\.]+/', '', $phone);
        
        // Validate: phone should be 10-15 digits
        if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            throw new Exception("Invalid phone number format");
        }
        
        return $phone;
    }
    
    /**
     * Validate a descriptor array (face descriptor)
     * @param mixed $descriptor Descriptor to validate
     * @return array Validated descriptor
     * @throws Exception if validation fails
     */
    public static function validateDescriptor($descriptor) {
        if (!is_array($descriptor)) {
            throw new Exception("Descriptor must be an array");
        }
        
        if (empty($descriptor)) {
            throw new Exception("Descriptor array cannot be empty");
        }
        
        // Face-api.js generates 128-dimensional descriptors
        if (count($descriptor) !== 128) {
            throw new Exception("Invalid descriptor format. Expected 128 dimensions, got " . count($descriptor));
        }
        
        // Validate each element is a float/number
        foreach ($descriptor as $key => $value) {
            if (!is_numeric($value)) {
                throw new Exception("Invalid descriptor value at index $key");
            }
        }
        
        return $descriptor;
    }
    
    /**
     * Validate user ID (positive integer)
     * @param mixed $userId User ID to validate
     * @return int Validated user ID
     * @throws Exception if validation fails
     */
    public static function validateUserId($userId) {
        if (!isset($userId) || $userId === null || $userId === '') {
            throw new Exception("User ID is required");
        }
        
        $userId = intval($userId);
        
        if ($userId <= 0) {
            throw new Exception("User ID must be a positive integer");
        }
        
        return $userId;
    }
    
    /**
     * Validate confidence score (0.0 to 1.0)
     * @param mixed $confidence Confidence score to validate
     * @return float Validated confidence score
     * @throws Exception if validation fails
     */
    public static function validateConfidence($confidence) {
        if (!isset($confidence) || $confidence === null || $confidence === '') {
            throw new Exception("Confidence score is required");
        }
        
        $confidence = floatval($confidence);
        
        if ($confidence < 0.0 || $confidence > 1.0) {
            throw new Exception("Confidence must be between 0.0 and 1.0");
        }
        
        return $confidence;
    }
    
    /**
     * Validate request method
     * @param string $method Expected method (GET, POST, PUT, DELETE)
     * @throws Exception if method doesn't match
     */
    public static function validateMethod($method) {
        $validMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        
        if (!in_array($method, $validMethods)) {
            throw new Exception("Invalid request method");
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            throw new Exception("Method $method required");
        }
    }
    
    /**
     * Validate JSON request
     * @return array Decoded JSON data
     * @throws Exception if JSON is invalid
     */
    public static function getJsonInput() {
        $input = file_get_contents("php://input");
        
        if (empty($input)) {
            throw new Exception("Request body is empty");
        }
        
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON: " . json_last_error_msg());
        }
        
        if (!is_array($data)) {
            throw new Exception("JSON must be an object");
        }
        
        return $data;
    }
    
    /**
     * Sanitize array keys (prevent injection)
     * @param array $data Array to sanitize
     * @return array Sanitized array
     */
    public static function sanitizeArray($data) {
        if (!is_array($data)) {
            return $data;
        }
        
        $sanitized = [];
        foreach ($data as $key => $value) {
            // Only allow alphanumeric, underscore, and hyphen in keys
            $key = preg_replace('/[^a-zA-Z0-9_\-]/', '', $key);
            
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}
?>
