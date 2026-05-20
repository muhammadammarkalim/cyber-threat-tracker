<?php
// ==================== CSRF PROTECTION ====================

/**
 * Generate CSRF token and store in session
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from POST request
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
        return false;
    }
    return true;
}

/**
 * Output hidden CSRF token field for forms
 */
function csrfField() {
    $token = generateCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// ==================== INPUT VALIDATION & SANITIZATION ====================
/**
 * Sanitize string input to prevent XSS
 */
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength (minimum 8 chars, mix of uppercase, lowercase, numbers)
 */
function validatePasswordStrength($password) {
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

/**
 * Hash password using Argon2 (PHP 7.2+) or bcrypt fallback
 */
function hashPassword($password) {
    if (defined('PASSWORD_ARGON2I')) {
        return password_hash($password, PASSWORD_ARGON2I, array('memory_cost' => 1024, 'time_cost' => 4, 'threads' => 2));
    } else {
        return password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
    }
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ==================== SESSION SECURITY ====================

/**
 * Secure session start with hardened settings
 */
function secureSessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
        // Hardened session configuration
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
        ini_set('session.cookie_samesite', 'Strict');
        session_start();
    }
}

/**
 * Regenerate session ID to prevent session fixation attacks
 */
function regenerateSessionID() {
    session_regenerate_id(true);
}

/**
 * Destroy session securely
 */
function secureLogout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}

// ==================== LOGGING & AUDIT ====================

/**
 * Log security event
 */
function logSecurityEvent($conn, $user_id, $event_type, $details, $ip_address = null) {
    if ($ip_address === null) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    $log_stmt = $conn->prepare("INSERT INTO security_logs (user_id, event_type, details, ip_address, event_timestamp) VALUES (?, ?, ?, ?, NOW())");
    $log_stmt->bind_param("isss", $user_id, $event_type, $details, $ip_address);
    return $log_stmt->execute();
}

/**
 * Log failed login attempt
 */
function logFailedLogin($conn, $username, $ip_address = null) {
    if ($ip_address === null) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    $stmt = $conn->prepare("INSERT INTO failed_login_attempts (username, ip_address, attempt_time) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $username, $ip_address);
    return $stmt->execute();
}

/**
 * Check if IP is rate limited (brute force protection)
 */
function isRateLimited($conn, $ip_address, $max_attempts = 5, $time_window = 900) {
    $time_threshold = time() - $time_window;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM failed_login_attempts WHERE ip_address = ? AND UNIX_TIMESTAMP(attempt_time) > ?");
    $stmt->bind_param("si", $ip_address, $time_threshold);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['attempts'] >= $max_attempts;
}
// ==================== ACCESS CONTROL ====================

/**
 * Check if user has required role
 */
function hasRole($required_role) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    $user_role = $_SESSION['user_role'];
    $roles = explode(',', $required_role);
    
    return in_array($user_role, $roles);
}
/**
 * Require authentication
 */
function requireAuth() {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin'])) {
        header("Location: index.php");
        exit();
    }
}
/**
 * Require specific role
 */
function requireRole($required_role) {
    requireAuth();
    
    if (!hasRole($required_role)) {
        die('Access Denied: Insufficient permissions');
    }
}
/**
 * Require admin role
 */
function requireAdmin() {
    if (!isset($_SESSION['admin'])) {
        header("Location: admin_login.php");
        exit();
    }
}

// ==================== DATA VALIDATION ====================

/**
 * Validate threat severity
 */
function validateSeverity($severity) {
    $valid_severities = array('Low', 'Medium', 'High', 'Critical');
    return in_array($severity, $valid_severities);
}

/**
 * Validate threat data
 */
function validateThreatData($threat_name, $description, $severity, $affected_industry) {
    $errors = array();
    
    if (empty($threat_name) || strlen($threat_name) < 3) {
        $errors[] = 'Threat name must be at least 3 characters';
    }
    
    if (empty($description) || strlen($description) < 10) {
        $errors[] = 'Description must be at least 10 characters';
    }
    
    if (!validateSeverity($severity)) {
        $errors[] = 'Invalid severity level';
    }
    
    if (empty($affected_industry) || strlen($affected_industry) < 2) {
        $errors[] = 'Industry must be specified';
    }
    
    return $errors;
}

// ==================== RISK SCORING ====================

/**
 * Calculate threat risk score (0-100)
 * Formula: Base Score (severity) + Temporal Score (age) + Environmental Score (industry impact)
 */
function calculateRiskScore($severity, $reported_date, $affected_industry) {
    $base_score = 0;
    $temporal_score = 0;
    $environmental_score = 0;
    
    // BASE SCORE (Severity-based) - 0 to 40 points
    switch (strtolower($severity)) {
        case 'critical':
            $base_score = 40;
            break;
        case 'high':
            $base_score = 30;
            break;
        case 'medium':
            $base_score = 20;
            break;
        case 'low':
            $base_score = 10;
            break;
        default:
            $base_score = 5;
    }
    
    // TEMPORAL SCORE (Age of threat) - 0 to 30 points
    // Recent threats score higher
    $report_time = strtotime($reported_date);
    $current_time = time();
    $days_old = ($current_time - $report_time) / (60 * 60 * 24);
    
    if ($days_old <= 1) {
        $temporal_score = 30; // Recent = high risk
    } elseif ($days_old <= 7) {
        $temporal_score = 20;
    } elseif ($days_old <= 30) {
        $temporal_score = 10;
    } else {
        $temporal_score = 5; // Old threats score less
    }
    
    // ENVIRONMENTAL SCORE (Industry impact) - 0 to 30 points
    // Critical sectors score higher
    $critical_industries = ['healthcare', 'government', 'financial', 'military', 'infrastructure', 'energy'];
    $industry_lower = strtolower($affected_industry ?? '');
    
    $environmental_score = 5; // Base
    foreach ($critical_industries as $critical) {
        if (strpos($industry_lower, $critical) !== false) {
            $environmental_score = 30;
            break;
        }
    }
    
    // Calculate final score
    $total_score = min($base_score + $temporal_score + $environmental_score, 100);
    
    return round($total_score);
}

/**
 * Get risk level label based on score
 */
function getRiskLevel($score) {
    if ($score >= 80) return 'CRITICAL';
    if ($score >= 60) return 'HIGH';
    if ($score >= 40) return 'MEDIUM';
    if ($score >= 20) return 'LOW';
    return 'MINIMAL';
}

/**
 * Get risk level color
 */
function getRiskColor($score) {
    if ($score >= 80) return '#ff0000'; // Red
    if ($score >= 60) return '#ff6600'; // Orange
    if ($score >= 40) return '#ffdd00'; // Yellow
    if ($score >= 20) return '#00dd00'; // Green
    return '#00ff00'; // Light Green
}

// ==================== ERROR HANDLING ====================

/**
 * Safe error display (no SQL details exposed)
 */
function handleDBError($error) {
    error_log($error);
    return 'An error occurred. Please try again later.';
}

?>
