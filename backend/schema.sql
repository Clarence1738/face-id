-- Face Recognition System Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS face_recognition;
USE face_recognition;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    descriptor JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_is_active (is_active)
);

-- Recognition Logs table
CREATE TABLE IF NOT EXISTS recognition_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    confidence FLOAT NOT NULL,
    recognized_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    device_ip VARCHAR(45),
    status ENUM('SUCCESS', 'FAILED') NOT NULL,
    previous_hash CHAR(64),
    log_hash CHAR(64),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_recognized_at (recognized_at),
    INDEX idx_status (status)
);

-- Checkins table (legacy, kept for backward compatibility)
CREATE TABLE IF NOT EXISTS checkins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_checkin_time (checkin_time)
);
