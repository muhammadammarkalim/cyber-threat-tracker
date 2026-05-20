# ⚡ Quick Reference Card

## 🔐 Security Functions Quick Reference

### CSRF Protection
```php
csrfField();                          // Add to HTML form
verifyCSRFToken($_POST['csrf_token']) // Verify in processing
```

### Input Handling
```php
$safe_input = sanitizeInput($input);  // XSS protection
validateEmail($email);                // Email check
validatePasswordStrength($pass);      // Password check
```

### Password Management
```php
$hash = hashPassword($password);      // Hash password
verifyPassword($input, $hash);        // Verify password
```

### Session Management
```php
secureSessionStart();                 // Secure start
regenerateSessionID();                // After login
secureLogout();                       // On logout
```

### Access Control
```php
requireAuth();                        // Require login
requireAdmin();                       // Require admin
hasRole('admin,analyzer');            // Check roles
```

### Logging
```php
logSecurityEvent($conn, $id, 'LOGIN', 'User logged in', $ip);
logFailedLogin($conn, $username, $ip);
isRateLimited($conn, $ip, 5, 900);    // 5 attempts / 15 min
```

### Data Validation
```php
validateThreatData($name, $desc, $severity, $industry);
validateSeverity($severity);
```

---

## 📝 File Checklist

### Must Include in Every File:
```php
<?php
session_start();
include 'security_functions.php';
requireAuth();  // or requireAdmin()
```

### For Admin Pages:
```php
<?php
session_start();
include 'db_connect.php';
include 'security_functions.php';

requireAdmin();
```

### For Form Pages:
```php
// In form:
<?php csrfField(); ?>

// In processing:
if (!verifyCSRFToken($_POST['csrf_token'])) {
    die('CSRF validation failed');
}
```

---

## 🛡️ Security Checklist for New Features

- [ ] Check authorization (requireAuth/requireAdmin)
- [ ] Sanitize all user inputs
- [ ] Validate data format
- [ ] Add CSRF token to forms
- [ ] Verify CSRF before processing
- [ ] Use prepared statements for DB
- [ ] Log security events
- [ ] Escape output with htmlspecialchars()
- [ ] Check for SQL injection
- [ ] Test with malicious input

---

## 🚨 Common Security Mistakes

❌ WRONG:
```php
$result = $conn->query("SELECT * FROM users WHERE username = '$username'");
echo $user_input;
if ($_POST['action'] == 'delete') { ... }
$password = $_POST['password'];
```

✅ RIGHT:
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
if (verifyCSRFToken($_POST['csrf_token']) && $_POST['action'] == 'delete') { ... }
if (verifyPassword($_POST['password'], $stored_hash)) { ... }
```

---

## 📊 Database Quick Reference

### User Roles
```
student, govt_emp, it_cs, analyzer, admin
```

### Threat Severity
```
Low, Medium, High, Critical
```

### Security Tables
- `security_logs` - All security events
- `failed_login_attempts` - Failed logins
- `threat_intelligence` - Risk scores
- `threat_correlations` - Threat patterns

---

## 🔗 Important Links

- Home: `/index.php`
- Admin Login: `/admin_login.php`
- User Register: `/user_register.php`
- Migrate Passwords: `/migrate_passwords.php`
- Admin Dashboard: `/admin_dashboard.php`
- User Dashboard: `/user_dashboard.php`

---

## 📚 Documentation Files

- `SECURITY_DOCUMENTATION.md` - Full security guide
- `SETUP_GUIDE.md` - Installation instructions
- `IMPLEMENTATION_REPORT.md` - What was done
- `security_functions.php` - Source code

---

## ⏱️ Rate Limiting
```
Max: 5 attempts
Time: 900 seconds (15 minutes)
Blocks: By IP address
```

---

## 🎨 Color Reference
```
Primary: #00ffe7 (Cyan)
Dark: #181f2a (Dark Blue)
Background: #10141a (Very Dark)
Text: #e0e6ed (Light Gray)
```

---

**Keep this handy for development!** 📌
