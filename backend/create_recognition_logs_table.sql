-- Create recognition_logs table for tracking face recognition attempts
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
