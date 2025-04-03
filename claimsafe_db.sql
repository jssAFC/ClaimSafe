CREATE DATABASE IF NOT EXISTS claimsafe_db;
USE claimsafe_db;

-- users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'agent', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insurance Companies table
CREATE TABLE IF NOT EXISTS insurance_companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Service Areas table
CREATE TABLE IF NOT EXISTS service_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insurance_company_id INT NOT NULL,
    state_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (insurance_company_id) REFERENCES insurance_companies(id) ON DELETE CASCADE,
    UNIQUE KEY (insurance_company_id, state_name)
);

-- Accidents table
CREATE TABLE IF NOT EXISTS accidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    location VARCHAR(255) NOT NULL,
    accident_date DATE NOT NULL,
    description TEXT NOT NULL,
    photo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insurance Agents table
CREATE TABLE IF NOT EXISTS insurance_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL,
    company_id INT,
    document_path VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES insurance_companies(id) ON DELETE SET NULL
);

-- Claims table
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

-- Messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);




-- AccidentAssist Database Schema


-- Users table
CREATE TABLE user2 (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Accident reports table
CREATE TABLE accident_reports (
    report_id VARCHAR(20) PRIMARY KEY,
    created_by INT NOT NULL,
    incident_date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    description TEXT,
    status ENUM('draft', 'in_progress', 'awaiting_resolution', 'resolved', 'closed') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES user2(user_id)
);

-- Vehicles involved in accidents
CREATE TABLE vehicles (
    vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id VARCHAR(20) NOT NULL,
    vehicle_position CHAR(1) NOT NULL, -- 'A', 'B', etc.
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
    FOREIGN KEY (report_id) REFERENCES accident_reports(report_id)
);

-- Evidence files (photos, documents, videos)
CREATE TABLE evidence_files (
    file_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id VARCHAR(20) NOT NULL,
    user_id INT NOT NULL,
    file_name VARCHAR(50) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES accident_reports(report_id),
    FOREIGN KEY (user_id) REFERENCES user2(user_id)
);

-- Timeline events
CREATE TABLE timeline_events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id VARCHAR(20) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    event_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (report_id) REFERENCES accident_reports(report_id),
    FOREIGN KEY (created_by) REFERENCES user2(user_id)
);

-- Party types
CREATE TABLE party_types (
    party_type_id INT AUTO_INCREMENT PRIMARY KEY,
    party_type_name VARCHAR(50) NOT NULL
);

-- Insert default party types
INSERT INTO party_types (party_type_name) VALUES 
('driver'), ('passenger'), ('witness'), ('insurance'), ('police'), ('legal');

-- Parties involved in accident
CREATE TABLE parties (
    party_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id VARCHAR(20) NOT NULL,
    user_id INT,
    party_type_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES accident_reports(report_id),
    FOREIGN KEY (user_id) REFERENCES user2(user_id),
    FOREIGN KEY (party_type_id) REFERENCES party_types(party_type_id)
);

-- Settlement proposals
CREATE TABLE settlement_proposals (
    proposal_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id VARCHAR(20) NOT NULL,
    created_by INT,
    vehicle_a_fault_percentage INT NOT NULL,
    vehicle_b_fault_percentage INT NOT NULL,
    vehicle_a_damage_amount DECIMAL(10, 2) NOT NULL,
    vehicle_b_damage_amount DECIMAL(10, 2) NOT NULL,
    resolution_notes TEXT,
    status ENUM('proposed', 'finalized', 'rejected') NOT NULL DEFAULT 'proposed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finalized_date TIMESTAMP NULL,
    FOREIGN KEY (report_id) REFERENCES accident_reports(report_id),
    FOREIGN KEY (created_by) REFERENCES user2(user_id)
);

-- Settlement responses
CREATE TABLE settlement_responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    proposal_id INT NOT NULL,
    user_id INT NOT NULL,
    response_status ENUM('pending', 'accepted', 'disputed') NOT NULL DEFAULT 'pending',
    response_date TIMESTAMP NULL,
    dispute_reason TEXT,
    FOREIGN KEY (proposal_id) REFERENCES settlement_proposals(proposal_id),
    FOREIGN KEY (user_id) REFERENCES user2(user_id)
);

-- Comments
CREATE TABLE comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id VARCHAR(20) NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES accident_reports(report_id),
    FOREIGN KEY (user_id) REFERENCES user2(user_id)
);

-- Create indexes for better performance
CREATE INDEX idx_vehicles_report ON vehicles(report_id);
CREATE INDEX idx_evidence_report ON evidence_files(report_id);
CREATE INDEX idx_timeline_report ON timeline_events(report_id);
CREATE INDEX idx_parties_report ON parties(report_id);
CREATE INDEX idx_settlement_report ON settlement_proposals(report_id);
CREATE INDEX idx_comments_report ON comments(report_id);