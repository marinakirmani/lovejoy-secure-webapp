-- Reconstructed schema for the publicly shared coursework demo.
-- Import through phpMyAdmin after creating or selecting lovejoy_db.
-- This file contains NO user data.
CREATE DATABASE IF NOT EXISTS lovejoy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lovejoy_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    lock_until DATETIME NULL,
    reset_token VARCHAR(64) NULL,
    reset_expires DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS evaluation_requests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    contact_method VARCHAR(20) NOT NULL,
    photo_path VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_evaluation_requests_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
