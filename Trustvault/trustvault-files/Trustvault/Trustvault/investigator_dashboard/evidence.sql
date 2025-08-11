CREATE TABLE evidence (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255),
    category VARCHAR(50),
    notes TEXT,
    upload_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    hash_value VARCHAR(128),
    status VARCHAR(50) DEFAULT 'Pending',
    uploaded_by VARCHAR(50) DEFAULT 'Investigator'
);