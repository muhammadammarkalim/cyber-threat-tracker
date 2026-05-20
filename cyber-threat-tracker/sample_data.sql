-- ===========================================
-- CYBER THREAT TRACKER - SAMPLE DATA
-- Run this in phpMyAdmin or MySQL command line
-- ===========================================

-- ===========================================
-- SCHEMA UPDATES (Add missing tables and columns)
-- ===========================================

-- Note: user_status and logs columns already exist, skipping ALTERs

-- Create security_logs table
CREATE TABLE IF NOT EXISTS security_logs (
  log_id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) DEFAULT NULL,
  event_type VARCHAR(50) DEFAULT NULL,
  details TEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  event_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (log_id),
  KEY user_id (user_id),
  CONSTRAINT security_logs_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create failed_login_attempts table
CREATE TABLE IF NOT EXISTS failed_login_attempts (
  attempt_id INT(11) NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  reason VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (attempt_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add reason column if it doesn't exist
ALTER TABLE failed_login_attempts ADD COLUMN IF NOT EXISTS reason VARCHAR(255) DEFAULT NULL;

-- ===========================================
-- CLEAR EXISTING DATA (optional - backup first!)
-- ===========================================

DELETE FROM logs WHERE user_id IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);
DELETE FROM security_logs WHERE user_id IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);
DELETE FROM failed_login_attempts WHERE username IN ('admin','john_analyst','sarah_gov','mike_it','alice_student','bob_analyst','emma_gov','david_it','lisa_student','tom_analyst','rachel_gov','kevin_it','maria_student','alex_analyst','nina_gov');
DELETE FROM threats WHERE id >= 1 AND id <= 30;
DELETE FROM alerts WHERE id >= 1 AND id <= 10;
DELETE FROM users WHERE user_id IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);

-- ===========================================
-- USERS (Sample users with different roles)
-- ===========================================

INSERT INTO users (user_id, full_name, username, email, password, user_role, created_at, user_status) VALUES
(1, 'Admin User', 'admin', 'admin@cybertracker.com', '$2y$10$yTgzByX8kRlCj65E4zBqCeSBg/J9n9h1C9O0O07jcQewfDSc7xgJG', 'admin', '2025-01-15 10:00:00', 'active'),
(2, 'John Analyst', 'john_analyst', 'john@securityfirm.com', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'analyzer', '2025-01-20 14:30:00', 'active'),
(3, 'Sarah Government', 'sarah_gov', 'sarah@govt.org', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'govt_emp', '2025-02-01 09:15:00', 'active'),
(4, 'Mike IT Pro', 'mike_it', 'mike@techcorp.com', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'it_cs', '2025-02-10 11:45:00', 'active'),
(5, 'Alice Student', 'alice_student', 'alice@university.edu', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'student', '2025-02-15 16:20:00', 'active'),
(6, 'Bob Analyst', 'bob_analyst', 'bob@cybersec.com', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'analyzer', '2025-03-01 08:30:00', 'active'),
(7, 'Emma Govt', 'emma_gov', 'emma@national.gov', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'govt_emp', '2025-03-05 13:10:00', 'active'),
(8, 'David IT', 'david_it', 'david@enterprise.com', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'it_cs', '2025-03-10 10:25:00', 'active'),
(9, 'Lisa Student', 'lisa_student', 'lisa@college.edu', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'student', '2025-03-15 15:40:00', 'active'),
(10, 'Tom Analyst', 'tom_analyst', 'tom@security.com', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'analyzer', '2025-03-20 12:55:00', 'active'),
(11, 'Rachel Govt', 'rachel_gov', 'rachel@federal.gov', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'govt_emp', '2025-04-01 09:30:00', 'active'),
(12, 'Kevin IT', 'kevin_it', 'kevin@techfirm.com', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'it_cs', '2025-04-05 14:15:00', 'active'),
(13, 'Maria Student', 'maria_student', 'maria@univ.edu', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'student', '2025-04-10 11:20:00', 'active'),
(14, 'Alex Analyst', 'alex_analyst', 'alex@cyberlab.com', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'analyzer', '2025-04-15 16:45:00', 'active'),
(15, 'Nina Govt', 'nina_gov', 'nina@state.gov', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'govt_emp', '2025-04-20 10:10:00', 'active');

-- ===========================================
-- THREATS (Sample threats with various data)
-- ===========================================

INSERT INTO threats (id, threat_name, description, severity, affected_industry, reported_date, submitted_by) VALUES
(1, 'Ransomware Attack on Healthcare', 'Hospital systems encrypted with WannaCry variant. Patient records compromised.', 'Critical', 'Healthcare', '2025-12-01', 'john_analyst'),
(2, 'Phishing Campaign Targeting Finance', 'Mass email campaign spoofing bank communications. 500+ users affected.', 'High', 'Financial', '2025-12-02', 'sarah_gov'),
(3, 'DDoS Attack on Government Portal', 'Government website taken down for 4 hours. Service disruption.', 'High', 'Government', '2025-12-03', 'mike_it'),
(4, 'SQL Injection in E-commerce Site', 'Database breach exposing customer payment information.', 'Critical', 'Retail', '2025-12-04', 'alice_student'),
(5, 'Zero-day Exploit in Windows', 'Unpatched vulnerability allowing remote code execution.', 'Critical', 'Technology', '2025-12-05', 'bob_analyst'),
(6, 'Data Breach in University System', 'Student records exposed due to misconfigured server.', 'Medium', 'Education', '2025-12-06', 'emma_gov'),
(7, 'Malware Infection in Corporate Network', 'Trojan horse spreading through USB drives.', 'High', 'Corporate', '2025-12-07', 'david_it'),
(8, 'Insider Threat - Data Exfiltration', 'Employee downloading sensitive files to external drive.', 'Medium', 'Manufacturing', '2025-12-08', 'lisa_student'),
(9, 'Cryptojacking Operation', 'Mining cryptocurrency using compromised company servers.', 'Low', 'Energy', '2025-12-09', 'tom_analyst'),
(10, 'Supply Chain Attack', 'Third-party software update contains backdoor.', 'Critical', 'Defense', '2025-12-10', 'rachel_gov'),
(11, 'IoT Botnet Formation', 'Smart devices compromised for DDoS network.', 'Medium', 'IoT', '2025-12-11', 'kevin_it'),
(12, 'Social Engineering Attack', 'CEO tricked into revealing credentials via phone call.', 'High', 'Executive', '2025-12-12', 'maria_student'),
(13, 'Rogue Access Point', 'Fake WiFi hotspot capturing login credentials.', 'Medium', 'Hospitality', '2025-12-13', 'alex_analyst'),
(14, 'API Key Exposure', 'Development keys leaked in public repository.', 'High', 'Software', '2025-12-14', 'nina_gov'),
(15, 'Physical Security Breach', 'Unauthorized access to server room.', 'Medium', 'Infrastructure', '2025-12-15', 'john_analyst'),
(16, 'Email Spoofing Campaign', 'Fake emails from trusted vendors requesting wire transfers.', 'High', 'Financial', '2025-11-20', 'sarah_gov'),
(17, 'Drive-by Download Attack', 'Malicious code injected into legitimate website.', 'Medium', 'Web Services', '2025-11-18', 'mike_it'),
(18, 'Man-in-the-Middle Attack', 'HTTPS interception on public WiFi.', 'High', 'Public Networks', '2025-11-15', 'alice_student'),
(19, 'Buffer Overflow Exploit', 'Legacy software vulnerability exploited.', 'Critical', 'Legacy Systems', '2025-11-10', 'bob_analyst'),
(20, 'Credential Stuffing Attack', 'Using breached passwords from other sites.', 'Medium', 'Authentication', '2025-11-05', 'emma_gov'),
(21, 'Watering Hole Attack', 'Compromised industry website targeting specific sector.', 'High', 'Healthcare', '2025-10-30', 'david_it'),
(22, 'Business Email Compromise', 'Executive email account hijacked for fraud.', 'Critical', 'Financial', '2025-10-25', 'lisa_student'),
(23, 'Ransomware as a Service', 'RaaS platform enabling novice attackers.', 'High', 'Cybercrime', '2025-10-20', 'tom_analyst'),
(24, 'Deepfake Audio Attack', 'AI-generated voice tricking employee into transfer.', 'Medium', 'Voice Authentication', '2025-10-15', 'rachel_gov'),
(25, 'Container Escape', 'Docker container breakout to host system.', 'Critical', 'Cloud Computing', '2025-10-10', 'kevin_it'),
(26, 'SIM Swapping Attack', 'Phone number takeover for account recovery.', 'High', 'Mobile', '2025-10-05', 'maria_student'),
(27, 'DNS Spoofing', 'Domain name system cache poisoning.', 'Medium', 'Network Infrastructure', '2025-09-30', 'alex_analyst'),
(28, 'USB Rubber Ducky Attack', 'Malicious USB device mimicking keyboard.', 'Low', 'Physical Access', '2025-09-25', 'nina_gov'),
(29, 'Cloud Misconfiguration', 'S3 bucket publicly exposed with sensitive data.', 'High', 'Cloud Storage', '2025-09-20', 'john_analyst'),
(30, 'AI-Powered Phishing', 'Machine learning crafted personalized attacks.', 'High', 'Email Security', '2025-09-15', 'sarah_gov');

-- ===========================================
-- ALERTS (Sample security alerts)
-- ===========================================

INSERT INTO alerts (id, title, message, type, created_at) VALUES
(1, 'Critical Ransomware Alert', 'New ransomware variant detected targeting healthcare systems worldwide.', 'Critical', '2025-12-01 08:00:00'),
(2, 'Phishing Campaign Warning', 'Mass phishing emails spoofing major banks. Exercise caution with email links.', 'Warning', '2025-12-02 10:30:00'),
(3, 'DDoS Attack Mitigation', 'Distributed denial of service attack patterns detected. Filters activated.', 'Alert', '2025-12-03 14:15:00'),
(4, 'Zero-Day Vulnerability', 'Critical vulnerability in popular software. Patch immediately.', 'Critical', '2025-12-05 09:45:00'),
(5, 'Data Breach Notification', 'Third-party vendor experienced security incident. Review access logs.', 'Warning', '2025-12-06 11:20:00'),
(6, 'Malware Outbreak', 'New malware strain spreading through removable media. Scan all USB devices.', 'Alert', '2025-12-07 16:00:00'),
(7, 'Insider Threat Detected', 'Unusual data access patterns from internal user. Investigation initiated.', 'Warning', '2025-12-08 13:30:00'),
(8, 'Cryptocurrency Mining Alert', 'Unauthorized mining activity detected on company servers.', 'Alert', '2025-12-09 15:45:00'),
(9, 'Supply Chain Compromise', 'Software update from trusted vendor contains malicious code.', 'Critical', '2025-12-10 10:15:00'),
(10, 'IoT Security Risk', 'Vulnerable IoT devices discovered on network. Firmware updates required.', 'Warning', '2025-12-11 12:00:00');

-- ===========================================
-- SECURITY LOGS (Sample audit events)
-- ===========================================

INSERT INTO security_logs (user_id, event_type, details, ip_address, event_timestamp) VALUES
(1, 'LOGIN_SUCCESS', 'Admin login successful', '192.168.1.100', '2025-12-01 09:00:00'),
(2, 'ANALYTICS_VIEW', 'Viewed threat analysis dashboard', '192.168.1.101', '2025-12-01 09:15:00'),
(3, 'THREAT_SUBMITTED', 'Submitted new threat report', '192.168.1.102', '2025-12-01 10:30:00'),
(1, 'USER_STATUS_CHANGE', 'Changed user status for user ID 5', '192.168.1.100', '2025-12-01 11:00:00'),
(4, 'LOGIN_SUCCESS', 'IT professional login successful', '192.168.1.103', '2025-12-01 14:20:00'),
(5, 'THREAT_VIEWED', 'Viewed threat details ID 1', '192.168.1.104', '2025-12-01 15:45:00'),
(1, 'ADMIN_PANEL_ACCESS', 'Accessed advanced admin panel', '192.168.1.100', '2025-12-01 16:00:00'),
(2, 'ANALYTICS_VIEW', 'Viewed analyzer dashboard', '192.168.1.101', '2025-12-02 08:30:00'),
(3, 'THREAT_SUBMITTED', 'Submitted critical threat report', '192.168.1.102', '2025-12-02 09:15:00'),
(6, 'LOGIN_SUCCESS', 'Analyzer login successful', '192.168.1.105', '2025-12-02 10:00:00'),
(1, 'AUDIT_LOGS_ACCESSED', 'Viewed audit logs', '192.168.1.100', '2025-12-02 11:30:00'),
(7, 'THREAT_VIEWED', 'Viewed government threat intelligence', '192.168.1.106', '2025-12-02 14:00:00'),
(4, 'ALERT_CREATED', 'Created new security alert', '192.168.1.103', '2025-12-02 15:20:00'),
(8, 'LOGIN_SUCCESS', 'IT specialist login successful', '192.168.1.107', '2025-12-03 08:45:00'),
(1, 'USER_DELETED', 'Deleted user account ID 99', '192.168.1.100', '2025-12-03 09:30:00'),
(9, 'THREAT_SUBMITTED', 'Submitted medium severity threat', '192.168.1.108', '2025-12-03 10:15:00'),
(2, 'ANALYTICS_VIEW', 'Viewed threat trends chart', '192.168.1.101', '2025-12-03 11:00:00'),
(10, 'LOGIN_SUCCESS', 'Security analyst login successful', '192.168.1.109', '2025-12-03 13:45:00'),
(3, 'THREAT_VIEWED', 'Reviewed critical infrastructure threats', '192.168.1.102', '2025-12-03 14:30:00'),
(1, 'ADMIN_PANEL_ACCESS', 'Accessed user management panel', '192.168.1.100', '2025-12-03 15:00:00');

-- ===========================================
-- FAILED LOGIN ATTEMPTS (Sample security events)
-- ===========================================

INSERT INTO failed_login_attempts (username, ip_address, attempt_time, reason) VALUES
('admin', '192.168.1.200', '2025-12-01 08:30:00', 'Invalid password'),
('admin', '192.168.1.200', '2025-12-01 08:31:00', 'Invalid password'),
('admin', '192.168.1.200', '2025-12-01 08:32:00', 'Invalid password'),
('john_analyst', '192.168.1.201', '2025-12-02 09:00:00', 'Invalid password'),
('john_analyst', '192.168.1.201', '2025-12-02 09:01:00', 'Invalid password'),
('sarah_gov', '192.168.1.202', '2025-12-03 10:15:00', 'Invalid password'),
('sarah_gov', '192.168.1.202', '2025-12-03 10:16:00', 'Invalid password'),
('sarah_gov', '192.168.1.202', '2025-12-03 10:17:00', 'Invalid password'),
('sarah_gov', '192.168.1.202', '2025-12-03 10:18:00', 'Invalid password'),
('mike_it', '192.168.1.203', '2025-12-04 11:30:00', 'Invalid password'),
('alice_student', '192.168.1.204', '2025-12-05 12:45:00', 'Invalid password'),
('alice_student', '192.168.1.204', '2025-12-05 12:46:00', 'Invalid password'),
('bob_analyst', '192.168.1.205', '2025-12-06 13:20:00', 'Invalid password'),
('bob_analyst', '192.168.1.205', '2025-12-06 13:21:00', 'Invalid password'),
('bob_analyst', '192.168.1.205', '2025-12-06 13:22:00', 'Invalid password');

-- ===========================================
-- ACTIVITY LOGS (Sample user actions)
-- ===========================================

INSERT INTO logs (user_id, action, action_timestamp, action_type, description) VALUES
(1, 'Admin login successful', '2025-12-01 09:00:00', 'LOGIN', 'Administrator logged into system'),
(2, 'Submitted new threat: Ransomware Attack on Healthcare', '2025-12-01 10:30:00', 'THREAT_SUBMIT', 'New critical threat reported'),
(3, 'Viewed threat analysis dashboard', '2025-12-01 11:15:00', 'ANALYTICS', 'Government employee accessed analytics'),
(4, 'Created security alert: Critical Ransomware Alert', '2025-12-01 12:00:00', 'ALERT_CREATE', 'New critical alert published'),
(5, 'Updated threat status ID 1', '2025-12-01 13:30:00', 'THREAT_UPDATE', 'Student updated threat information'),
(6, 'Viewed analyzer dashboard', '2025-12-01 14:45:00', 'ANALYTICS', 'Security analyst accessed dashboard'),
(7, 'Submitted threat report', '2025-12-01 15:20:00', 'THREAT_SUBMIT', 'Government employee reported threat'),
(8, 'Viewed IT security threats', '2025-12-01 16:00:00', 'THREAT_VIEW', 'IT professional reviewed threats'),
(9, 'Created user account', '2025-12-02 09:30:00', 'USER_CREATE', 'Student registered new account'),
(10, 'Updated alert ID 1', '2025-12-02 10:15:00', 'ALERT_UPDATE', 'Security analyst modified alert'),
(11, 'Viewed government intelligence dashboard', '2025-12-02 11:00:00', 'ANALYTICS', 'Government employee accessed intelligence'),
(12, 'Submitted medium severity threat', '2025-12-02 12:30:00', 'THREAT_SUBMIT', 'IT professional reported threat'),
(13, 'Viewed threat details ID 5', '2025-12-02 13:45:00', 'THREAT_VIEW', 'Student reviewed threat information'),
(14, 'Created critical alert', '2025-12-02 14:20:00', 'ALERT_CREATE', 'Security analyst published alert'),
(15, 'Updated user profile', '2025-12-02 15:00:00', 'PROFILE_UPDATE', 'Government employee modified profile'),
(1, 'Accessed admin panel', '2025-12-02 16:30:00', 'ADMIN_ACCESS', 'Administrator accessed management panel'),
(2, 'Viewed threat trends', '2025-12-03 09:15:00', 'ANALYTICS', 'Security analyst reviewed trends'),
(3, 'Submitted critical infrastructure threat', '2025-12-03 10:00:00', 'THREAT_SUBMIT', 'Government employee reported critical threat'),
(4, 'Updated security alert', '2025-12-03 11:30:00', 'ALERT_UPDATE', 'IT professional modified alert'),
(5, 'Viewed student dashboard', '2025-12-03 12:15:00', 'DASHBOARD', 'Student accessed personal dashboard');

-- ===========================================
-- SUCCESS MESSAGE
-- ===========================================

-- Data insertion completed successfully!
-- Your analytics panels will now show rich data visualizations.
-- Login with different user roles to see role-based analytics.
-- Check admin panel for user management and audit logs.