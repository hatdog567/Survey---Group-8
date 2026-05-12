-- CREATE DATABASE
CREATE DATABASE IF NOT EXISTS servicio_db;
USE servicio_db;

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    contact_number VARCHAR(20) DEFAULT NULL,
    blood_type VARCHAR(10) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT 'default_avatar.png',
    profile_status ENUM('approved', 'pending_review') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- HEALTH RECORDS TABLE
CREATE TABLE IF NOT EXISTS health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    head_of_family VARCHAR(100) NOT NULL,
    household_number VARCHAR(50) NOT NULL,
    zone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    age INT NOT NULL,
    gender VARCHAR(20) NOT NULL,
    blood_type VARCHAR(10) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    existing_condition VARCHAR(10) DEFAULT 'No',
    condition_details VARCHAR(255) DEFAULT NULL,
    taking_medication VARCHAR(10) DEFAULT 'No',
    recent_surgery VARCHAR(10) DEFAULT 'No',
    pregnant VARCHAR(10) DEFAULT 'N/A',
    donor_consent VARCHAR(10) DEFAULT 'No',
    donated_before VARCHAR(10) DEFAULT 'No',
    last_donation_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- FAMILY MEMBERS TABLE
CREATE TABLE IF NOT EXISTS family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    health_record_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender VARCHAR(20) NOT NULL,
    blood_type VARCHAR(10) NOT NULL,
    relationship VARCHAR(50) NOT NULL,
    existing_condition VARCHAR(10) DEFAULT 'No',
    condition_details VARCHAR(255) DEFAULT NULL,
    taking_medication VARCHAR(10) DEFAULT 'No',
    recent_surgery VARCHAR(10) DEFAULT 'No',
    pregnant VARCHAR(10) DEFAULT 'N/A',
    donor_consent VARCHAR(10) DEFAULT 'No',
    donated_before VARCHAR(10) DEFAULT 'No',
    last_donation_date DATE DEFAULT NULL,
    FOREIGN KEY (health_record_id) REFERENCES health_records(id) ON DELETE CASCADE
);

-- DONORS TABLE
CREATE TABLE IF NOT EXISTS donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    blood_type VARCHAR(10) NOT NULL,
    status ENUM('eligible', 'screening', 'not_eligible') DEFAULT 'screening',
    last_donation DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- VENDORS TABLE
CREATE TABLE IF NOT EXISTS vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    business_name VARCHAR(150) NOT NULL,
    owner_name VARCHAR(100) NOT NULL,
    business_type VARCHAR(50) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    id_front TEXT,
    id_back TEXT,
    brgy_clearance TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- NOTIFICATIONS TABLE
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- INSERT DEFAULT ACCOUNTS (Password is 'password123' hashed with BCRYPT)
INSERT INTO users (email, password, full_name, role) VALUES 
('admin@servicio.gov', '$2y$10$uEVBYOz6ii0J3BD.07.M5eOJdyxZZ/FWhmVJjleJ5C4w0m1DMnz6q', 'System Admin', 'admin'),
('user@gmail.com', '$2y$10$uEVBYOz6ii0J3BD.07.M5eOJdyxZZ/FWhmVJjleJ5C4w0m1DMnz6q', 'Juan Dela Cruz', 'user');
