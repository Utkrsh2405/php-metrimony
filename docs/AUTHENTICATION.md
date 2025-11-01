# Authentication System Documentation

## Overview

The matrimonial platform has **two separate authentication systems**:

1. **User/Member Authentication** - For regular users (frontend)
2. **Admin Authentication** - For administrators (backend)

---

## 🔵 User Authentication (Frontend)

### Login Page
- **URL**: `/login.php`
- **Purpose**: Member login for frontend access
- **Authentication**: `auth/auth.php?user=1`
- **User Level**: `userlevel != 1` or `NULL` (non-admin users)

### Features
- ✅ Username/password authentication
- ✅ Password hashing (bcrypt)
- ✅ Auto-upgrade plain text passwords to bcrypt
- ✅ Rate limiting (10 attempts per 15 minutes)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (input sanitization)
- ✅ Security event logging
- ✅ Session management
- ✅ Error messages displayed on login page
- ✅ Link to registration page

### Registration
- **URL**: `/register.php`
- **Purpose**: New member registration
- **Access**: Public (anyone can register as member)

### After Login
- Redirects to: `/userhome.php?id={user_id}`
- Session variables set:
  - `$_SESSION['id']`
  - `$_SESSION['username']`
  - `$_SESSION['userlevel']`
  - `$_SESSION['last_activity']`

### Test Accounts
```
Username: test
Password: test

Username: aswin
Password: aswin

Username: reshma
Password: reshma
```

---

## 🔴 Admin Authentication (Backend)

### Login Page
- **URL**: `/admin/login.php`
- **Purpose**: Administrator login for admin panel
- **User Level**: `userlevel = 1` (admins only)
- **No Signup**: Admins cannot self-register

### Features
- ✅ Dedicated admin login interface
- ✅ CSRF token protection
- ✅ Rate limiting (5 attempts per 15 minutes)
- ✅ Bcrypt password hashing
- ✅ Session timeout (30 minutes of inactivity)
- ✅ Session hijacking prevention
- ✅ Activity logging (all logins/logouts tracked)
- ✅ Security event logging
- ✅ IP tracking
- ✅ Timeout notification message
- ✅ Link to member login page (for accidental admin visitors)

### After Login
- Redirects to: `/admin/index.php` (Admin Dashboard)
- Session variables set:
  - `$_SESSION['id']`
  - `$_SESSION['username']`
  - `$_SESSION['userlevel']` (must be 1)
  - `$_SESSION['last_activity']`
  - `$_SESSION['user_agent']` (for hijacking prevention)

### Session Timeout
- **Duration**: 30 minutes of inactivity
- **Behavior**: Auto-logout with message "Your session has expired due to inactivity"
- **Redirect**: Back to `/admin/login.php?timeout=1`

### Logout
- **URL**: `/admin/logout.php`
- **Actions**: 
  - Logs logout activity
  - Destroys session
  - Clears cookies
  - Redirects to login page

### Admin Credentials
```
Username: admin
Password: admin123
```

---

## 🔒 Security Features

### User Authentication
| Feature | Implementation |
|---------|---------------|
| Password Hashing | Bcrypt (auto-upgrade from plain text) |
| Rate Limiting | 10 attempts per 15 minutes |
| SQL Injection | Prepared statements |
| XSS Protection | Input sanitization |
| Session Security | Regenerate ID on login |
| Logging | Failed attempts logged to security_logs |

### Admin Authentication
| Feature | Implementation |
|---------|---------------|
| Password Hashing | Bcrypt (VARCHAR 255) |
| Rate Limiting | 5 attempts per 15 minutes |
| CSRF Protection | Token validation on all POST requests |
| Session Timeout | 30 minutes inactivity |
| Session Hijacking | User agent verification |
| Activity Logging | All actions in admin_activity_logs |
| Security Logging | Events in security_logs |

---

## 🚪 Access Control

### User vs Admin Separation

**Users CANNOT:**
- ❌ Login to admin panel (even if they know admin URL)
- ❌ Access admin pages (redirected to login)
- ❌ Have `userlevel = 1`

**Admins CANNOT:**
- ❌ Login through `/login.php` (filtered out)
- ❌ Self-register as admin
- ❌ Be created without database access

### Creating New Admins

Admins must be created via database or admin panel:

```sql
-- Option 1: Direct SQL
UPDATE users SET userlevel = 1 WHERE id = {user_id};

-- Option 2: Via admin panel (if implemented)
-- Go to: /admin/members.php
-- Edit user → Set "User Level" to 1
```

---

## 🔄 Authentication Flow

### User Login Flow
```
1. User visits /login.php
2. Enters username/password
3. Form posts to /auth/auth.php?user=1
4. System checks:
   - Rate limit OK?
   - Username exists?
   - User is NOT admin? (userlevel != 1)
   - Password correct?
5. If all OK:
   - Create session
   - Regenerate session ID
   - Redirect to /userhome.php
6. If failed:
   - Log security event
   - Set error in session
   - Redirect back to /login.php
```

### Admin Login Flow
```
1. Admin visits /admin/login.php
2. Enters username/password
3. Form posts to /admin/login.php
4. System checks:
   - CSRF token valid?
   - Rate limit OK?
   - Username exists?
   - User IS admin? (userlevel = 1)
   - Password correct?
5. If all OK:
   - Create session with timeout tracking
   - Log successful login to activity_logs
   - Regenerate session ID
   - Redirect to /admin/index.php
6. If failed:
   - Log security event
   - Show error on login page
```

---

## 📊 Database Tables

### Users Table
```sql
users
├── id (INT)
├── username (VARCHAR 60)
├── password (VARCHAR 255) -- bcrypt hash
├── userlevel (INT) -- 1 = admin, NULL = user
└── ... other fields
```

### Activity Logs (Admin)
```sql
admin_activity_logs
├── id
├── admin_id
├── action (login/logout/create/update/delete)
├── entity_type
├── entity_id
├── description
├── old_data (JSON)
├── new_data (JSON)
├── ip_address
├── user_agent
└── created_at
```

### Security Logs
```sql
security_logs
├── id
├── event_type (admin_login_failed, user_login_rate_limit, etc.)
├── description
├── user_id
├── ip_address
├── user_agent
└── created_at
```

---

## 🎯 Best Practices

### For Users
1. ✅ Always login at `/login.php`
2. ✅ Use strong passwords
3. ✅ Logout when done
4. ✅ Don't share credentials

### For Admins
1. ✅ Always login at `/admin/login.php`
2. ✅ Use very strong passwords
3. ✅ Review activity logs regularly
4. ✅ Check security logs for failed attempts
5. ✅ Be aware of 30-minute timeout
6. ✅ Never share admin credentials
7. ✅ Review IP blacklist for suspicious IPs

---

## 🐛 Troubleshooting

### "Invalid username or password" (User)
- Check username is correct
- Check password is correct
- Ensure user is NOT an admin (userlevel != 1)
- Check if rate limited (wait 15 minutes)

### "Invalid username or password" (Admin)
- Ensure using admin credentials
- Check user has `userlevel = 1` in database
- Check if rate limited (wait 15 minutes)
- Verify password hasn't been changed

### "Session expired"
- Normal after 30 minutes of inactivity
- Just login again
- Consider staying active if working on something

### "Too many login attempts"
- Wait 15 minutes
- IP address is rate limited
- Check security_logs for your IP

---

## 📝 Migration Notes

### Password Field Update
Migration **014** updates the password field:
- **Before**: `VARCHAR(40)` (too small for bcrypt)
- **After**: `VARCHAR(255)` (supports bcrypt hashes)
- **Admin Password**: Auto-hashed to bcrypt

All plain text passwords are automatically upgraded to bcrypt on first successful login.

---

## 🔗 Related Files

### User Authentication
- `/login.php` - Login form
- `/register.php` - Registration form
- `/auth/auth.php` - Authentication handler
- `/logout.php` - User logout

### Admin Authentication
- `/admin/login.php` - Admin login (with CSRF)
- `/admin/logout.php` - Admin logout (with activity log)
- `/includes/admin-header.php` - Session timeout check

### Security
- `/includes/security.php` - Security utilities
- `/includes/activity-logger.php` - Activity logging
- `/db/migrations/014_update_password_field.sql` - Password field update

---

**Last Updated**: November 1, 2025
