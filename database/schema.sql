-- Padel Pitch Reservation System Database Schema

CREATE DATABASE IF NOT EXISTS padel_reservation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE padel_reservation;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Item categories (Ball, Pitch, Racket)
CREATE TABLE IF NOT EXISTS item_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Item types (basic, premium, open, covered) with price
CREATE TABLE IF NOT EXISTS item_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    type_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (category_id) REFERENCES item_categories(id) ON DELETE CASCADE
);

-- Reservations
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ball_type_id INT,
    pitch_type_id INT NOT NULL,
    racket_type_id INT,
    number_of_balls INT NOT NULL DEFAULT 0,
    number_of_rackets INT NOT NULL DEFAULT 0,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'approved', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ball_type_id) REFERENCES item_types(id) ON DELETE SET NULL,
    FOREIGN KEY (pitch_type_id) REFERENCES item_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (racket_type_id) REFERENCES item_types(id) ON DELETE SET NULL
);

-- Seed: categories
INSERT INTO item_categories (name) VALUES ('Ball'), ('Pitch'), ('Racket');

-- Seed: item types
-- Balls (category 1)
INSERT INTO item_types (category_id, type_name, price) VALUES
(1, 'Basic', 2.00),
(1, 'Premium', 4.00);

-- Pitches (category 2) — price per hour
INSERT INTO item_types (category_id, type_name, price) VALUES
(2, 'Open', 20.00),
(2, 'Covered', 30.00);

-- Rackets (category 3)
INSERT INTO item_types (category_id, type_name, price) VALUES
(3, 'Basic', 5.00),
(3, 'Premium', 10.00);

-- Default admin account (password: admin123)
INSERT INTO users (full_name, email, password, role)
VALUES ('Admin', 'admin@padel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
