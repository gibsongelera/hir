-- Campus Relief Hub Database Schema
-- Imported into: if0_41802178_campusrelief_db

-- Users table (students + admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) UNIQUE DEFAULT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    course VARCHAR(100) DEFAULT NULL,
    year_level VARCHAR(20) DEFAULT NULL,
    contact_number VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default.png',
    role ENUM('admin', 'student') DEFAULT 'student',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;

-- Assistance requests
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 1,
    urgency ENUM('low', 'medium', 'high') DEFAULT 'medium',
    details TEXT DEFAULT NULL,
    status ENUM(
        'pending',
        'approved',
        'rejected',
        'fulfilled'
    ) DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- Donations (item + monetary)
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    donor_name VARCHAR(150) NOT NULL,
    donation_type ENUM('item', 'monetary') DEFAULT 'item',
    items TEXT DEFAULT NULL,
    amount DECIMAL(10, 2) DEFAULT 0.00,
    payment_method VARCHAR(50) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    status ENUM(
        'pending',
        'received',
        'distributed'
    ) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE = InnoDB;

-- Volunteers
CREATE TABLE IF NOT EXISTS volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    course VARCHAR(100) DEFAULT NULL,
    year_level VARCHAR(20) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    availability VARCHAR(100) DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE = InnoDB;

-- Seed admin account (password: admin123)
INSERT INTO
    users (
        first_name,
        last_name,
        email,
        password,
        role,
        status
    )
VALUES (
        'System',
        'Administrator',
        'admin@zppsu.edu.com',
        '$2y$10$YOGiaKJl0xfNOYNk.fQmROhHYxui7vvGAaSc/SiK37YnlYdkgJu7W',
        'admin',
        'active'
    );