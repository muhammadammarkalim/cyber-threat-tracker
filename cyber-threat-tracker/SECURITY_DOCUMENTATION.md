# 🛡️ Cyber Threat Tracker - Information Security Documentation

## Version 2.0 - Enhanced Security

### Table of Contents
1. [Security Improvements](#security-improvements)
2. [Installation & Setup](#installation--setup)
3. [Architecture](#architecture)
4. [Security Features](#security-features)
5. [Best Practices](#best-practices)
6. [Threat Model](#threat-model)
7. [API Security](#api-security)

---

## Security Improvements

### Level 1: Core Security Hardening ✅

#### 1. **Password Security**
- **Status**: ✅ IMPLEMENTED
- **Algorithm**: Bcrypt (PASSWORD_BCRYPT, cost=12)
- **Fallback**: Argon2 (if PHP 7.2+)
- **Migration**: Run `migrate_passwords.php` to hash existing plain text passwords

```php
// Example
$hashedPassword = hashPassword($userPassword);
if (verifyPassword($inputPassword, $hashedPassword)) {
    // Login success
}
```

#### 2. **Input Validation & Sanitization**
- **Status**: ✅ IMPLEMENTED
- **XSS Protection**: All user inputs sanitized with `htmlspecialchars()`
- **SQL Injection Protection**: Prepared statements throughout
- **Email Validation**: RFC compliant validation

```php
// Example
$email = sanitizeInput($_POST['email']);
if (!validateEmail($email)) {
    $error = "Invalid email format";
}
```

#### 3. **CSRF Protection**
- **Status**: ✅ IMPLEMENTED
- **Method**: Token-based CSRF prevention
- **Token Generation**: 32-byte random hex tokens
- **Token Storage**: Session-based

```php
// In forms
<?php csrfField(); ?>

// In form processing
if (!verifyCSRFToken($_POST['csrf_token'])) {
    die('CSRF validation failed');
}
```

#### 4. **Session Security**
- **Status**: ✅ IMPLEMENTED
- **Hardened Settings**:
  - HttpOnly cookies (prevents JavaScript access)
  - SameSite=Strict (CSRF defense)
  - Session ID regeneration on login
  - Secure session configuration

```php
// Session regeneration on login
regenerateSessionID();

// Secure logout
secureLogout();
```

#### 5. **Rate Limiting**
- **Status**: ✅ IMPLEMENTED
- **Brute Force Protection**: Max 5 attempts per 15 minutes
- **IP-based tracking**: Prevents distributed attacks

```php
if (isRateLimited($conn, $ip_address)) {
    die('Too many login attempts');
}
```

---

## Installation & Setup

### Step 1: Database Setup
```bash
# Import the database
mysql -u root < cyberthreat_db.sql

# Add security tables
mysql -u root cyberthreat_db < add_security_tables.sql
```

### Step 2: Migrate Existing Passwords
```bash
# Run migration script (IMPORTANT - DO THIS FIRST!)
# Access: http://localhost/cyberthreat-tracker/migrate_passwords.php
```

### Step 3: Update Database Configuration
Edit `db_connect.php`:
```php
$host = "localhost";
$username = "root";
$password = "";
$database = "cyberthreat_db";
```

### Step 4: Test Installation
- Visit: `http://localhost/cyberthreat-tracker/index.php`
- Admin Login: Use credentials from your database
- User Registration: Create a new account

---

## Architecture

### Security Layers

```
┌─────────────────────────────────────────┐
│   PRESENTATION LAYER (HTML/CSS/JS)      │
│   - XSS Protection                      │
│   - CSRF Tokens                         │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│   APPLICATION LAYER (PHP)               │
│   - Input Validation                    │
│   - Authorization Checks                │
│   - Audit Logging                       │
│   - Session Management                  │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│   DATA LAYER (MySQL)                    │
│   - Prepared Statements                 │
│   - Parameterized Queries               │
│   - Hashed Passwords                    │
└─────────────────────────────────────────┘
```

### File Structure

```
cyberthreat-tracker/
├── security_functions.php      # All security functions
├── db_connect.php              # Database configuration
├── migrate_passwords.php       # Password migration
│
├── Authentication/
│   ├── user_login.php
│   ├── user_login_process.php
│   ├── user_register.php
│   ├── register_user_process.php
│   ├── admin_login.php
│   └── logout.php
│
├── Admin/
│   ├── admin_dashboard.php
│   ├── add_threat.php
│   ├── add_alert.php
│   ├── manage_threats_admin.php
│   ├── manage_alerts_admin.php
│   └── (others...)
│
└── User/
    ├── user_dashboard.php
    ├── view_threats.php
    ├── search_threats_user.php
    └── (others...)
```

---

## Security Features

### Core Functions

#### `security_functions.php` - Centralized Security

**CSRF Protection**
```php
generateCSRFToken()           // Generate/retrieve token
verifyCSRFToken($token)       // Verify token
csrfField()                   // Output hidden field
```

**Input Handling**
```php
sanitizeInput($input)         // XSS protection
validateEmail($email)         // Email validation
validatePasswordStrength()    // Password validation
```

**Password Management**
```php
hashPassword($password)       // Hash with Bcrypt/Argon2
verifyPassword($pass, $hash)  // Verify password
```

**Session Management**
```php
secureSessionStart()          // Hardened session start
regenerateSessionID()         // Prevent fixation attacks
secureLogout()                // Secure logout
```

**Access Control**
```php
requireAuth()                 // Require login
requireAdmin()                // Require admin role
hasRole($required_role)       // Role checking
```

**Audit Logging**
```php
logSecurityEvent()            // Log security events
logFailedLogin()              // Log failed attempts
isRateLimited()               // Check rate limits
```

### Database Tables

**security_logs**
```
- log_id          INT (Primary Key)
- user_id         INT (User ID)
- event_type      VARCHAR (LOGIN, LOGOUT, UPDATE, etc.)
- details         TEXT (Event details)
- ip_address      VARCHAR (IP address)
- event_timestamp DATETIME (When it happened)
```

**failed_login_attempts**
```
- attempt_id      INT (Primary Key)
- username        VARCHAR
- ip_address      VARCHAR
- attempt_time    DATETIME
```

---

## Best Practices

### For Developers

#### 1. Always Use These Functions
```php
// ALWAYS include at top of files
session_start();
include 'security_functions.php';

// ALWAYS check authorization
requireAdmin();  // or requireAuth() for users

// ALWAYS sanitize input
$input = sanitizeInput($_POST['field']);

// ALWAYS validate data
$errors = validateThreatData($name, $desc, $severity, $industry);

// ALWAYS add CSRF token to forms
<?php csrfField(); ?>

// ALWAYS verify CSRF in POST handling
if (!verifyCSRFToken($_POST['csrf_token'])) {
    die('CSRF validation failed');
}
```

#### 2. SQL Query Pattern
```php
// ✓ CORRECT - Use prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();

// ✗ WRONG - String concatenation (SQL injection)
$result = $conn->query("SELECT * FROM users WHERE username = '$username'");
```

#### 3. Password Handling
```php
// ✓ CORRECT - Hash password
$hashed = hashPassword($password);
$stmt = $conn->prepare("INSERT INTO users (password) VALUES (?)");
$stmt->bind_param("s", $hashed);

// ✗ WRONG - Store plain text
$stmt = $conn->prepare("INSERT INTO users (password) VALUES (?)");
$stmt->bind_param("s", $password);
```

#### 4. Data Output
```php
// ✓ CORRECT - Escape output
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// ✗ WRONG - Echo raw user input
echo $user_input;  // XSS vulnerability
```

### For Users

#### Password Requirements
- Minimum 8 characters
- At least one uppercase letter (A-Z)
- At least one lowercase letter (a-z)
- At least one number (0-9)

**Example**: `SecurePass123`

#### Account Security
- Never share your password
- Use unique passwords
- Enable two-factor authentication (coming soon)
- Report suspicious activity

---

## Threat Model

### Assumed Threats
1. **SQL Injection**: Mitigated by prepared statements
2. **XSS (Cross-Site Scripting)**: Mitigated by input sanitization
3. **CSRF (Cross-Site Request Forgery)**: Mitigated by token validation
4. **Brute Force Attacks**: Mitigated by rate limiting
5. **Session Hijacking**: Mitigated by secure session settings
6. **Password Compromise**: Mitigated by strong hashing

### Out of Scope
- Network attacks (use HTTPS in production)
- Physical security
- Social engineering
- Zero-day vulnerabilities

---

## API Security (Phase 2 - Coming Soon)

### Planned Security Features
- [ ] API Token Authentication
- [ ] Rate Limiting (per API key)
- [ ] API Logging
- [ ] OAuth 2.0 Support
- [ ] API Documentation

---

## Compliance

### OWASP Top 10 Alignment
- **A01:2021 - Broken Access Control**: ✅ RBAC implemented
- **A02:2021 - Cryptographic Failures**: ✅ Strong password hashing
- **A03:2021 - Injection**: ✅ Prepared statements
- **A04:2021 - Insecure Design**: ✅ Security review done
- **A05:2021 - Security Misconfiguration**: ✅ Hardened defaults
- **A06:2021 - Vulnerable Components**: ✅ Regular updates
- **A07:2021 - Authentication Failures**: ✅ Multi-layer auth
- **A08:2021 - Software/Data Integrity**: ✅ Integrity checks
- **A09:2021 - Logging & Monitoring**: ✅ Audit logs
- **A10:2021 - SSRF**: ✅ Input validation

---

## Support & Questions

For security issues, please email: security@cyberthreat-tracker.local

For documentation, visit: `SECURITY.md`

---

**Last Updated**: November 16, 2025
**Version**: 2.0
**Security Status**: 🟢 HARDENED
