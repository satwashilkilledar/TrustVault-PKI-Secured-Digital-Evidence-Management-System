CREATE DATABASE IF NOT EXISTS evidence_system;
USE evidence_system;

CREATE TABLE evidence1 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255),
    category VARCHAR(50),
    notes TEXT,
    upload_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    hash_value VARCHAR(128),
    status VARCHAR(50) DEFAULT 'Pending',
    uploaded_by VARCHAR(50) DEFAULT 'Investigator'
);

CREATE TABLE chain_of_custody (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evidence_id INT,
    action VARCHAR(255),
    performed_by VARCHAR(50),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evidence_id) REFERENCES evidence(id)
);
