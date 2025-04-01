-- Merged ClaimSafe Database Schema
CREATE DATABASE IF NOT EXISTS claimsafe_db;
USE claimsafe_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'agent', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insurance Companies Table
CREATE TABLE IF NOT EXISTS insurance_companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Service Areas Table
CREATE TABLE IF NOT EXISTS service_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insurance_company_id INT NOT NULL,
    state_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (insurance_company_id) REFERENCES insurance_companies(id) ON DELETE CASCADE,
    UNIQUE KEY (insurance_company_id, state_name)
);

-- Accidents Table
CREATE TABLE IF NOT EXISTS accidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    accident_date DATETIME NOT NULL,
    description TEXT NOT NULL,
    status ENUM('draft', 'in_progress', 'resolved', 'closed') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Vehicles Involved Table
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accident_id INT NOT NULL,
    vehicle_position CHAR(1) NOT NULL, 
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT,
    color VARCHAR(30),
    license_plate VARCHAR(20) NOT NULL,
    driver_name VARCHAR(100) NOT NULL,
    driver_license VARCHAR(50),
    insurance_company VARCHAR(100) NOT NULL,
    policy_number VARCHAR(50) NOT NULL,
    damage_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (accident_id) REFERENCES accidents(id) ON DELETE CASCADE
);

-- Evidence Files Table
CREATE TABLE IF NOT EXISTS evidence_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accident_id INT NOT NULL,
    user_id INT NOT NULL,
    file_name VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (accident_id) REFERENCES accidents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insurance Agents Table
CREATE TABLE IF NOT EXISTS insurance_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_id INT,
    region VARCHAR(100) NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES insurance_companies(id) ON DELETE SET NULL
);

-- Claims Table
CREATE TABLE IF NOT EXISTS claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accident_id INT NOT NULL,
    company_id INT NOT NULL,
    agent_id INT DEFAULT NULL,
    status ENUM('new', 'in_progress', 'resolved') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (accident_id) REFERENCES accidents(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES insurance_companies(id) ON DELETE CASCADE
);

-- Settlement Proposals Table
CREATE TABLE IF NOT EXISTS settlement_proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accident_id INT NOT NULL,
    created_by INT,
    vehicle_a_fault_percentage INT NOT NULL,
    vehicle_b_fault_percentage INT NOT NULL,
    vehicle_a_damage_amount DECIMAL(10, 2) NOT NULL,
    vehicle_b_damage_amount DECIMAL(10, 2) NOT NULL,
    resolution_notes TEXT,
    status ENUM('proposed', 'finalized', 'rejected') DEFAULT 'proposed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (accident_id) REFERENCES accidents(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Messages Table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accident_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (accident_id) REFERENCES accidents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for Performance
CREATE INDEX idx_vehicles_accident ON vehicles(accident_id);
CREATE INDEX idx_evidence_accident ON evidence_files(accident_id);
CREATE INDEX idx_claims_accident ON claims(accident_id);
CREATE INDEX idx_comments_accident ON comments(accident_id);
CREATE INDEX idx_messages_claim ON messages(claim_id);
