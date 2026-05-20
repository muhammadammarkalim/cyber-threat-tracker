# 🚀 Cyber Threat Tracker - Setup Guide

## Quick Start

### Prerequisites
- XAMPP (PHP 7.4+, MySQL)
- Web Browser
- Text Editor (VS Code recommended)

---

## Step-by-Step Installation

### 1️⃣ Database Setup

**Import main database:**
```bash
# Open Command Prompt/Terminal
cd C:\xampp\htdocs\cyberthreat-tracker

# Import database
mysql -u root < cyberthreat_db.sql
```

**Add security tables:**
```bash
mysql -u root cyberthreat_db < add_security_tables.sql
```

### 2️⃣ Migrate Existing Passwords

**IMPORTANT: Do this FIRST!**

1. Access: `http://localhost/cyberthreat-tracker/migrate_passwords.php`
2. Wait for migration to complete
3. Passwords are now secure! ✅

### 3️⃣ Test Login

**Admin Account Example:**
- **Username**: `zaib`
- **Password**: `zaib123`

**Access**: `http://localhost/cyberthreat-tracker/admin_login.php`

---

## User Roles

### 1. **Admin Role**
- Create/edit/delete threats
- Create/edit/delete alerts
- View all users
- View audit logs
- Manage system

**Access**: `admin_login.php`

### 2. **User Roles** (Register at `user_register.php`)
- **Student**: Can submit and view threats
- **IT/CS Professional**: Enhanced analytics
- **Government Employee**: Special access
- **Security Analyst**: Full read access + correlations

---

## File Structure After Installation

```
C:\xampp\htdocs\cyberthreat-tracker\
├── index.php                      # Home page
├── db_connect.php                 # Database config
├── security_functions.php         # Security library
│
├── 🔐 Authentication
│   ├── user_login.php
│   ├── user_login_process.php
│   ├── user_register.php
│   ├── register_user_process.php
│   ├── admin_login.php
│   └── logout.php
│
├── 👨‍💼 Admin Dashboard
│   ├── admin_dashboard.php
│   ├── add_threat.php
│   ├── edit_threat.php
│   ├── delete_threat.php
│   ├── update_threat.php
│   ├── manage_threats_admin.php
│   ├── add_alert.php
│   ├── edit_alert.php
│   ├── delete_alert.php
│   └── manage_alerts_admin.php
│
├── 👤 User Dashboard
│   ├── user_dashboard.php
│   ├── dashboard.php
│   ├── submit_threat.php
│   ├── insert_threat.php
│   ├── view_threats.php
│   ├── search_threats_user.php
│   └── view_threats.php
│
├── 🎨 Styling
│   └── style.css
│
├── 📚 Documentation
│   ├── SECURITY_DOCUMENTATION.md
│   ├── SETUP_GUIDE.md
│   └── cyberthreat_db.sql
│
└── 🛠️ Utilities
    ├── migrate_passwords.php
    ├── add_security_tables.sql
    └── db_connect.php
```

---

## Security Features (Now Active)

### ✅ Implemented
1. **Password Hashing**
   - Bcrypt (cost=12) for all passwords
   - Migration script for existing users

2. **CSRF Protection**
   - Token-based on all forms
   - Session-bound validation

3. **Input Validation**
   - XSS protection via sanitization
   - Email validation
   - Password strength validation

4. **Session Security**
   - HttpOnly cookies
   - SameSite protection
   - Session ID regeneration

5. **Rate Limiting**
   - Brute force protection
   - IP-based tracking

6. **Audit Logging**
   - All logins tracked
   - Failed attempts recorded
   - Event logging

---

## Accessing Features

### 🏠 Home Page
```
http://localhost/cyberthreat-tracker/index.php
```

### 👤 User Features
```
User Login:           /user_login.php
Register:             /user_register.php
Dashboard:            /user_dashboard.php
Submit Threat:        /submit_threat.php or /insert_threat.php
View All Threats:     /view_threats.php
Search Threats:       /search_threats_user.php
```

### 👨‍💼 Admin Features
```
Admin Login:          /admin_login.php
Dashboard:            /admin_dashboard.php
Add Threat:           /add_threat.php
Manage Threats:       /manage_threats_admin.php
Add Alert:            /add_alert.php
Manage Alerts:        /manage_alerts_admin.php
```

### 🔧 Admin Tools
```
Migrate Passwords:    /migrate_passwords.php
Security Logs:        DATABASE: security_logs table
```

---

## Database Tables

### Users Table
```
user_id, full_name, username, email, password (hashed), user_role, created_at
```

### Threats Table
```
id, threat_name, description, severity, affected_industry, reported_date, submitted_by
```

### Alerts Table
```
id, title, message, type, created_at
```

### Security Logs Table ⭐ NEW
```
log_id, user_id, event_type, details, ip_address, event_timestamp
```

### Failed Login Attempts Table ⭐ NEW
```
attempt_id, username, ip_address, attempt_time
```

---

## Color Scheme Reference

The project maintains a **Cyber-Themed** aesthetic:

```css
Primary Background:     #10141a (Very Dark Blue)
Secondary Background:   #181f2a (Dark Blue)
Tertiary Background:    #232b39 (Medium Dark Blue)
Accent (Cyan):          #00ffe7 (Glowing Cyan)
Text:                   #e0e6ed (Light Gray)
Success:                #00ff00 (Green)
Warning:                #ffaa00 (Orange)
Error:                  #ff0000 (Red)
```

---

## Troubleshooting

### Issue: "Connection failed" error
**Solution**: Check `db_connect.php` configuration
```php
$host = "localhost";
$username = "root";
$password = "";        // XAMPP default
$database = "cyberthreat_db";
```

### Issue: "CSRF token validation failed"
**Solution**: This is expected if you:
- Changed pages before submitting
- Cleared session/cookies
- Used an old form

Just reload and try again.

### Issue: "Too many login attempts"
**Solution**: You've tried 5+ logins in 15 minutes
**Wait**: 15 minutes before trying again

### Issue: Login with old password doesn't work
**Solution**: Run `migrate_passwords.php` FIRST
Then use new password (min 8 chars, upper, lower, number)

---

## Testing Accounts

After running `migrate_passwords.php`, test with:

| Username | Password | Role |
|----------|----------|------|
| zaib | zaib123 | admin |
| ammar | ammar123 | admin |
| nafay | nafay@gmail.com | analyzer |

**Note**: Old plain-text passwords will be hashed by the migration script.

---

## Next Steps

### Phase 2: Analytics & Intelligence
- [ ] Threat Analytics Dashboard
- [ ] Threat Correlation Engine
- [ ] CVE Integration
- [ ] Risk Scoring Model

### Phase 3: Advanced Security
- [ ] Two-Factor Authentication
- [ ] API with OAuth
- [ ] Data Encryption
- [ ] Advanced Logging

---

## Support

For issues, check:
1. `SECURITY_DOCUMENTATION.md` - Security details
2. `style.css` - Styling reference
3. `security_functions.php` - Available functions

---

**Installation Complete! 🎉**

Your Cyber Threat Tracker is now **SECURE and READY** to use!

Next: Log in and start tracking threats! 🚀
