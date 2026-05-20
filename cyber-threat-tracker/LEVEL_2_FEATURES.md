# Cyber Threat Tracker - Level 2: Advanced Features & Admin Panel

## 📋 Overview

This document outlines the Level 2 security enhancements and advanced features implemented for the Cyber Threat Tracker project. All features maintain strict security hardening from Level 1 while adding sophisticated threat analysis, risk scoring, and administrative capabilities.

**Completion Date**: 2024  
**Project Goal**: Create an impressive information security project for 3rd semester teacher assessment

---

## 🎯 Level 2 Features Completed

### 1. **Risk Scoring Model** ✅
**File**: `security_functions.php` (functions added)

#### Algorithm
Risk scores are calculated on a **0-100 scale** using a weighted formula with three components:

```
Total Risk Score = Base Score + Temporal Score + Environmental Score
```

**a) Base Score (0-40 points) - Severity-Based**
- CRITICAL: 40 points
- HIGH: 30 points
- MEDIUM: 20 points
- LOW: 10 points

**b) Temporal Score (0-30 points) - Threat Age**
- 0-1 day old: 30 points
- 1-7 days old: 20 points
- 7-30 days old: 10 points
- >30 days old: 5 points

**c) Environmental Score (0-30 points) - Industry Context**
- Critical Sectors (Healthcare, Government, Financial, Military, Infrastructure, Energy): 30 points
- Other Industries: 5 points

#### Risk Level Classification
- **MINIMAL** (0-20): Green - Low priority
- **LOW** (20-40): Yellow - Monitor
- **MEDIUM** (40-60): Orange - Investigate
- **HIGH** (60-80): Red-orange - Urgent action needed
- **CRITICAL** (80-100): Red - Immediate action required

#### Functions Available
```php
calculateRiskScore($severity, $reported_date, $affected_industry)
// Returns: integer (0-100)

getRiskLevel($score)
// Returns: string (MINIMAL, LOW, MEDIUM, HIGH, CRITICAL)

getRiskColor($score)
// Returns: hex color code for UI display
```

**Benefits**:
- Automatic threat prioritization
- Consistent risk assessment across all threats
- Visual color-coding for quick scanning
- Helps admins focus on critical threats first

---

### 2. **Advanced Admin Panel** ✅
**File**: `admin_panel.php`

#### Access Control
- **Admin-only**: Strict role validation on page entry
- **All actions logged**: Every admin action is recorded in `security_logs`
- **Session-based**: Requires active admin session

#### Features

**A. User Statistics Dashboard**
- Total users count
- Active/Inactive user breakdown
- Distribution by role (Admin, Analyzer, Student, Govt Employee, IT/CS)
- Visual stat boxes with color-coded metrics

**B. User Management Tab**
- Full user table with: ID, Username, Full Name, Email, Role, Status, Creation Date
- **Enable/Disable User**: Toggle user account status
  - Deactivated users cannot login
  - All actions logged for audit trail
  - Admin cannot disable their own account (safety check)
- **Delete User**: Permanent removal with transactional safety
  - Modal confirmation dialog required
  - Removes dependent records:
    - All activity logs (from `logs` table)
    - All security logs (from `security_logs` table)
    - All failed login attempts (from `failed_login_attempts` table)
  - Impossible to delete own account
  - Transaction-based (atomic) to prevent data inconsistency

**C. Audit Logs Tab**
- Last 50 security events displayed
- Shows: Timestamp, Username, Event Type, Details, IP Address
- Color-coded event type badges
- Quick reference for recent admin activity

#### Security Measures
- Immediate logging: `logSecurityEvent()` called on all operations
- Role validation: `if ($_SESSION['user_role'] !== 'admin') exit;`
- CSRF protection: Forms include token validation
- Input sanitization: `sanitizeInput()` applied to all filters

---

### 3. **Audit Logs Viewer** ✅
**File**: `audit_logs_viewer.php`

#### Access Control
- **Admin-only**: Strict role validation with exit on unauthorized access
- **All viewing logged**: Each access to audit logs is recorded

#### Features

**A. Activity Summary Statistics**
- Event types logged (count)
- Top 10 active users by activity count
- Failed login attempts (last 24 hours)
- Recent activity (last 24 hours)
- Color-coded display for quick scanning

**B. Three-Tab Log Viewer**

**Tab 1: Security Events Log** 🔒
- Advanced filtering:
  - By event type (regex search)
  - By username
  - By date range (from/to)
  - Configurable results limit (default: 100)
- Displays: Timestamp, User, Event Type, Details, IP Address
- Event type summary showing frequency distribution
- Color-coded event categories for quick identification

**Tab 2: Failed Login Attempts** ⚠️
- Shows all failed login attempts
- Includes: Timestamp, Username, IP Address, Reason
- Red background for visual warning
- Helps identify brute force attacks or credential issues
- Can spot patterns of repeated failures from specific IPs

**Tab 3: Activity Logs** 📝
- User registrations, threat submissions, account changes
- Includes: Timestamp, User, Action Type, Description
- Green-coded action badges
- Useful for tracking user lifecycle events

#### Database Tables Used
1. `security_logs` - Admin actions, logins, analytics access
2. `failed_login_attempts` - Failed login attempts with IP tracking
3. `logs` - General user activity (registrations, submissions)

#### Filtering Capabilities
- **Event Type Filter**: Partial string matching (e.g., "LOGIN" finds all login events)
- **Username Filter**: Search by full or partial username
- **Date Range**: From and To date filters for historical analysis
- **Result Limit**: Control number of results displayed (1-1000)

---

### 4. **Risk Score Integration** ✅
**Files Modified**:
- `view_threats.php` - All threats view
- `manage_threats_admin.php` - Admin threat management

#### Implementation
Every threat now displays:
- **Risk Score**: 0-100 numerical value
- **Risk Level**: Categorical label (MINIMAL, LOW, MEDIUM, HIGH, CRITICAL)
- **Color Badge**: Visually distinct background color

#### UI Styling
```css
.risk-minimal { background: #1a5f1a; color: #00ff00; }  /* Green */
.risk-low { background: #5f5f1a; color: #ffff00; }      /* Yellow */
.risk-medium { background: #5f4a1a; color: #ffaa00; }   /* Orange */
.risk-high { background: #5f2a1a; color: #ff6600; }     /* Red-orange */
.risk-critical { background: #5f1a1a; color: #ff4444; } /* Red */
```

#### User Experience
- Students/Analyzers can see risk scores on all threats
- Helps them understand threat prioritization
- Educates users about risk assessment factors
- Promotes security awareness

---

## 📱 Navigation Updates

### Admin Dashboard (`admin_dashboard.php`)
New navbar link: **🔧 Advanced Panel** → Links to `admin_panel.php`

### Admin Panel (`admin_panel.php`)
Navbar includes:
- Dashboard (back link)
- Analytics (admin dashboard charts)
- Advanced Panel (current page)
- **📋 Audit Logs** → Links to `audit_logs_viewer.php`
- Logout

### User Dashboards
Risk score displays integrated into threat viewing pages:
- All users can see risk scores
- Consistent color-coding across system
- Helps with threat understanding

---

## 🔒 Security Implementation

### Authentication & Authorization
✅ Session-based authentication  
✅ Role validation on every admin page  
✅ CSRF token protection on all forms  
✅ Input sanitization via `sanitizeInput()`  
✅ IP address tracking for all events  

### Audit Trail
✅ `logSecurityEvent()` called on:
- User activation/deactivation
- User deletion
- Admin panel access
- Audit logs viewer access
- All threat management actions

✅ `failed_login_attempts` tracks:
- Failed login timestamps
- Username attempted
- IP address
- Reason for failure

### Data Integrity
✅ Transactional deletes prevent orphaned records  
✅ Foreign key constraints maintained  
✅ No SQL injection (prepared statements)  
✅ Password hashing with Bcrypt (cost=12)  
✅ Session ID regeneration on login  

---

## 📊 Database Tables Used

| Table | Purpose | Used By |
|-------|---------|---------|
| `users` | User accounts & roles | All pages |
| `security_logs` | Admin actions, logins, analytics views | Audit viewer, Admin panel |
| `failed_login_attempts` | Login failure tracking | Audit viewer, Rate limiting |
| `logs` | General activity (registrations, submissions) | Audit viewer |
| `threats` | Threat records with severity/industry | Risk scoring, Admin panel |
| `alerts` | Alert notifications | Admin panel |

---

## 🧪 Testing Checklist

### Risk Scoring
- [ ] Verify risk scores calculated correctly for all severity levels
- [ ] Verify temporal scoring (older threats have lower scores)
- [ ] Verify environmental scoring (critical industries get higher scores)
- [ ] Verify color coding matches risk level
- [ ] Verify risk scores display on view_threats.php
- [ ] Verify risk scores display on manage_threats_admin.php

### Advanced Admin Panel
- [ ] Verify admin-only access restriction
- [ ] Test user status toggle (enable/disable)
- [ ] Test user deletion with modal confirmation
- [ ] Verify all actions are logged to security_logs
- [ ] Verify admin cannot disable own account
- [ ] Verify transactional deletion removes dependent records
- [ ] Check user statistics are accurate

### Audit Logs Viewer
- [ ] Verify admin-only access
- [ ] Test security events filtering by type
- [ ] Test security events filtering by username
- [ ] Test security events filtering by date range
- [ ] Verify failed login attempts display
- [ ] Verify activity logs display
- [ ] Check summary statistics accuracy

### Integration
- [ ] Verify navbar links work correctly
- [ ] Verify session security on all admin pages
- [ ] Verify CSRF tokens prevent form hijacking
- [ ] Check for any SQL injection vulnerabilities
- [ ] Verify all admin actions are logged

---

## 🔑 Test Credentials

**Admin Account**
- Username: `zaib`
- Password: `zaib123`

**Test User Accounts** (for integration testing)
- Analyzer: `nafay`
- Govt Employee: `sara`
- Student: `alice`

---

## 📈 Performance Considerations

- Risk score calculation is O(1) - no database queries
- Audit logs use pagination (100 results default) to prevent UI slowdown
- Admin panel uses prepared statements for efficient queries
- Security events logged asynchronously (no performance impact on user actions)

---

## 🎓 Educational Value

**For Teacher Assessment**:
1. **Comprehensive Risk Modeling**: Demonstrates understanding of threat prioritization
2. **Admin Controls**: Shows enterprise-level user management capabilities
3. **Audit Trail**: Proves security event logging and compliance awareness
4. **Role-Based Access**: Implements proper authorization controls
5. **Data Integrity**: Transaction-based operations show database expertise
6. **Security Logging**: All privileged actions audited for accountability

**Key Impressive Features**:
- Weighted risk scoring algorithm (not just severity-based)
- Transactional database operations with proper constraint handling
- Comprehensive audit trail for compliance
- Admin-only access controls with strict validation
- Color-coded UI for security awareness
- Advanced filtering and searching capabilities

---

## 📝 Future Enhancements

- [ ] Risk score trending analysis (how scores change over time)
- [ ] Threat correlation engine (automatically link related threats)
- [ ] Automated alert generation based on risk thresholds
- [ ] Bulk threat operations (apply tags, update status)
- [ ] Advanced threat search with saved filters
- [ ] Email notifications for high-risk threats
- [ ] Custom risk scoring rules by admin
- [ ] Threat export functionality (PDF, CSV)

---

## ✅ Level 2 Completion Status

| Feature | Status | File(s) |
|---------|--------|---------|
| Risk Scoring Algorithm | ✅ Complete | security_functions.php |
| Advanced Admin Panel | ✅ Complete | admin_panel.php |
| Audit Logs Viewer | ✅ Complete | audit_logs_viewer.php |
| Risk Score Integration | ✅ Complete | view_threats.php, manage_threats_admin.php |
| Navigation Updates | ✅ Complete | admin_dashboard.php |
| Security Logging | ✅ Complete | All admin pages |

**Overall Level 2**: **100% COMPLETE** ✅

---

## 📞 Support & Documentation

For questions about specific features:
- **Risk Scoring**: See `calculateRiskScore()` function in `security_functions.php`
- **Admin Panel**: See feature list in `admin_panel.php` comments
- **Audit Logs**: See filtering options in `audit_logs_viewer.php`
- **Security**: See Level 1 documentation in project folder

---

**Project:** Cyber Threat Tracker - Information Security Project  
**Level:** 2 - Advanced Features & Administration  
**Status:** ✅ Ready for Assessment  
**Last Updated:** 2024
