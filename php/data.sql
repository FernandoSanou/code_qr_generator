-- Création de la base de données
CREATE DATABASE IF NOT EXISTS qr_event_db;
USE qr_event_db;

-- Table principale des tickets
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_code VARCHAR(255) NOT NULL UNIQUE,
    user_id VARCHAR(50) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_info VARCHAR(100) NOT NULL,
    user_uns VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    day_1 BOOLEAN DEFAULT 0,
    day_2 BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table pour les présences du jour 1
CREATE TABLE day1_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT,
    key_code VARCHAR(255) NOT NULL,
    user_id VARCHAR(50) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_info VARCHAR(100) NOT NULL,
    user_uns VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id)
);

-- Table pour les présences du jour 2
CREATE TABLE day2_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT,
    key_code VARCHAR(255) NOT NULL,
    user_id VARCHAR(50) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_info VARCHAR(100) NOT NULL,
    user_uns VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id)
);