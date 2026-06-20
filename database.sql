CREATE DATABASE IF NOT EXISTS iot_sensor_db;

USE iot_sensor_db;

DROP TABLE IF EXISTS sensor_data;

CREATE TABLE sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(50) NOT NULL,
    temperature DECIMAL(6,2) NOT NULL,
    humidity DECIMAL(6,2) NOT NULL,
    co DECIMAL(10,6) NOT NULL,
    lpg DECIMAL(10,6) NOT NULL,
    smoke DECIMAL(10,6) NOT NULL,
    light_intensity INT NOT NULL,
    motion_status TINYINT NOT NULL,
    prediction_label ENUM('NORMAL', 'WARNING', 'DANGER') NULL,
    prediction_score DECIMAL(6,4) NULL,
    prediction_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);