-- Add security-related tables to cyberthreat_db

-- Security Logs Table
CREATE TABLE IF NOT EXISTS `security_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `event_timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `event_timestamp` (`event_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Failed Login Attempts Table
CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempt_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attempt_id`),
  KEY `attempt_time` (`attempt_time`),
  KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Threat Intelligence Table
CREATE TABLE IF NOT EXISTS `threat_intelligence` (
  `ti_id` int(11) NOT NULL AUTO_INCREMENT,
  `threat_id` int(11) DEFAULT NULL,
  `cve_id` varchar(50) DEFAULT NULL,
  `risk_score` int(11) DEFAULT 0,
  `correlation_count` int(11) DEFAULT 0,
  `last_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ti_id`),
  KEY `threat_id` (`threat_id`),
  KEY `risk_score` (`risk_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Threat Correlation Events
CREATE TABLE IF NOT EXISTS `threat_correlations` (
  `correlation_id` int(11) NOT NULL AUTO_INCREMENT,
  `threat1_id` int(11) NOT NULL,
  `threat2_id` int(11) NOT NULL,
  `correlation_strength` float DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`correlation_id`),
  KEY `threat1_id` (`threat1_id`),
  KEY `threat2_id` (`threat2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
