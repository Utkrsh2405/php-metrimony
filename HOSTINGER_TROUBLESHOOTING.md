# Hostinger Backend Troubleshooting Checklist

## ⚠️ IMMEDIATE ACTIONS

### 1. Upload debug.php
1. Upload `debug.php` to your `public_html/` directory
2. Visit: `http://yourdomain.com/debug.php`
3. Read all errors and warnings
4. **DELETE debug.php after use!**

### 2. Check Database Connection
Most common issue! Update these files:

**File: `includes/dbconn.php`**
```php
$host = "localhost";  // NOT 127.0.0.1!
$username = "cpanelusername_dbuser";  // Replace cpanelusername
$password = "Uttu@2025";
$db_name = "cpanelusername_matrimony";  // Replace cpanelusername
```

**File: `functions.php`** (Line 3-6)
```php
$host = "localhost";  // NOT 127.0.0.1!
$username = "cpanelusername_dbuser";  // Same as above
$password = "Uttu@2025";
$db_name = "cpanelusername_matrimony";  // Same as above
```

---

## 🔍 COMMON HOSTINGER ISSUES

### Issue #1: "Cannot connect to database"

**Symptoms:**
- Blank pages
- "Cannot connect" errors
- Admin panel doesn't load

**Quick Fixes:**

1. **Wrong Host:**
   ```php
   // WRONG:
   $host = "127.0.0.1";
   
   // CORRECT for Hostinger:
   $host = "localhost";
   ```

2. **Wrong Database Name:**
   - cPanel prefixes all database names
   - If cPanel username is `john123`, database is `john123_matrimony`
   - NOT just `matrimony`

3. **Check Credentials in cPanel:**
   - cPanel → MySQL Databases
   - Note the EXACT names (with prefix)
   - Copy-paste to avoid typos

4. **User Permissions:**
   - cPanel → MySQL Databases → Current Users
   - Click "Privileges" next to your user
   - Ensure ALL PRIVILEGES are checked

### Issue #2: "500 Internal Server Error"

**Causes & Fixes:**

1. **PHP Syntax Error:**
   ```bash
   # Check error_log in File Manager
   # Look for "PHP Parse error" or "syntax error"
   ```

2. **Wrong PHP Version:**
   - cPanel → Select PHP Version
   - Choose PHP 7.4 or 8.0
   - Avoid PHP 5.x

3. **File Permissions:**
   ```bash
   # Files should be 644
   # Folders should be 755
   # Fix in File Manager: Right-click → Permissions
   ```

4. **.htaccess Issues:**
   - Rename .htaccess to .htaccess.bak temporarily
   - If site works, there's an .htaccess problem
   - Check for invalid directives

### Issue #3: Admin Panel Not Working

**Symptoms:**
- Can't login to admin
- Redirects to login page
- Session errors

**Fixes:**

1. **Check Admin Table:**
   - phpMyAdmin → users table
   - Verify admin user exists with userlevel = 1
   - Password should be hashed (bcrypt)

2. **Session Issues:**
   - Clear browser cookies
   - Try different browser
   - Check PHP session settings

3. **Run This SQL in phpMyAdmin:**
   ```sql
   -- Check if admin exists
   SELECT id, username, userlevel FROM users WHERE userlevel = 1;
   
   -- If no admin, create one:
   INSERT INTO users (username, password, userlevel, email) 
   VALUES ('admin', '$2y$10$YourHashedPasswordHere', 1, 'admin@example.com');
   ```

4. **Reset Admin Password:**
   ```sql
   -- Generate hash for 'admin123' password
   -- Use: https://bcrypt-generator.com/
   -- Then run:
   UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
   WHERE username = 'admin';
   ```

### Issue #4: "Page Not Found" / Blank Pages

**Fixes:**

1. **Check File Upload:**
   - Ensure ALL files uploaded to `public_html/`
   - Not in a subfolder like `public_html/matrimony/`
   - File structure should be:
     ```
     public_html/
     ├── index.php
     ├── admin/
     ├── includes/
     └── ...
     ```

2. **Case Sensitivity:**
   - Linux servers are case-sensitive
   - `Index.php` ≠ `index.php`
   - Check all file/folder names match exactly

3. **Missing Index File:**
   - Ensure `index.php` exists in `public_html/`
   - Set as default in cPanel if needed

### Issue #5: Database Tables Missing

**Symptoms:**
- "Table doesn't exist" errors
- Features not working

**Fix:**

1. **Import Base Schema:**
   - phpMyAdmin → Select your database
   - Import → Choose `db/matrimony.sql`
   - Wait for success

2. **Apply Migrations:**
   - Import each file from `db/migrations/` folder
   - In order: 001, 002, 003... through 015
   - Or run SQL from each file in phpMyAdmin

3. **Verify Tables:**
   ```sql
   SHOW TABLES;
   -- Should show 30+ tables
   ```

### Issue #6: Upload/Profile Images Not Working

**Fixes:**

1. **Create Directories:**
   - File Manager → Create folders:
     - `uploads/`
     - `uploads/banners/`
     - `profile/`

2. **Set Permissions:**
   - Right-click folder → Permissions
   - Set to `755` (rwxr-xr-x)
   - Check "Recursive" for subdirectories

3. **PHP Upload Settings:**
   - Check in .htaccess:
     ```apache
     php_value upload_max_filesize 10M
     php_value post_max_size 10M
     ```

---

## 📋 STEP-BY-STEP DEPLOYMENT CHECKLIST

### Pre-Upload:
- [ ] Update `includes/dbconn.php` with Hostinger credentials
- [ ] Update `functions.php` with Hostinger credentials
- [ ] Change `127.0.0.1` to `localhost` in both files
- [ ] Create database in cPanel
- [ ] Create database user and set password
- [ ] Add user to database with ALL PRIVILEGES

### Upload:
- [ ] Upload all files to `public_html/`
- [ ] Import `db/matrimony.sql` in phpMyAdmin
- [ ] Import migration files (001 through 015)
- [ ] Create `uploads/` and `profile/` directories
- [ ] Set directory permissions to 755

### Testing:
- [ ] Upload and run `debug.php`
- [ ] Check database connection
- [ ] Verify all tables exist
- [ ] Test homepage: http://yourdomain.com/
- [ ] Test admin: http://yourdomain.com/admin/
- [ ] Test user registration
- [ ] Delete `debug.php` after testing

### Post-Deployment:
- [ ] Change admin password
- [ ] Enable SSL/HTTPS
- [ ] Set up automatic backups
- [ ] Monitor error_log
- [ ] Test all features

---

## 🆘 EMERGENCY SQL QUERIES

### If Admin Login Not Working:

```sql
-- Check admin user
SELECT id, username, userlevel, password FROM users WHERE userlevel = 1;

-- Reset admin password to 'admin123'
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'admin';
```

### If Tables Missing:

```sql
-- Check existing tables
SHOW TABLES;

-- Count users
SELECT COUNT(*) FROM users;

-- Check table structure
DESCRIBE users;
```

### If Database Corrupted:

```sql
-- Drop and recreate (CAUTION: Deletes all data!)
DROP DATABASE cpanelusername_matrimony;
CREATE DATABASE cpanelusername_matrimony CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Then re-import matrimony.sql
```

---

## 📞 GETTING DETAILED ERROR INFO

### Method 1: Enable Error Display (Temporary!)

Add to top of `index.php`:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
?>
```

**IMPORTANT:** Remove after debugging!

### Method 2: Check Error Log

1. cPanel → File Manager
2. Find `error_log` file in `public_html/`
3. Download and read
4. Look for latest errors

### Method 3: PHP Info

Create `info.php`:
```php
<?php phpinfo(); ?>
```
Upload to public_html, visit yourdomain.com/info.php
**DELETE after checking!**

---

## 🔧 HOSTINGER-SPECIFIC SETTINGS

### Recommended PHP Version:
- PHP 8.0 or 7.4
- Change in: cPanel → Select PHP Version

### Required PHP Extensions:
Enable in cPanel → Select PHP Version → Extensions:
- [x] mysqli
- [x] session
- [x] json
- [x] mbstring
- [x] gd
- [x] curl
- [x] openssl
- [x] zip

### Recommended Limits:
In .htaccess:
```apache
php_value memory_limit 256M
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
```

---

## ✅ VERIFICATION TESTS

### Test 1: Database Connection
Visit: `http://yourdomain.com/debug.php`
- Should show "✓ Database Connected"
- Should list table count

### Test 2: Homepage
Visit: `http://yourdomain.com/`
- Should display homepage
- Should show profiles
- No errors

### Test 3: Admin Access
Visit: `http://yourdomain.com/admin/`
- Should redirect to admin login
- Login with: admin / admin123
- Should access dashboard

### Test 4: User Registration
Visit: `http://yourdomain.com/register.php`
- Fill form and submit
- Should create account
- Check in database

---

## 📧 CONTACT SUPPORT

If nothing works, contact Hostinger Support with:

1. **Error Log:** Last 50 lines from error_log
2. **Debug Output:** Screenshot from debug.php
3. **PHP Version:** From cPanel
4. **Specific Error:** Exact error message
5. **What You Tried:** List of fixes attempted

---

## 🎯 MOST LIKELY ISSUE

**90% of Hostinger backend issues are:**
1. Wrong database host (`127.0.0.1` instead of `localhost`)
2. Database credentials don't include cPanel username prefix
3. Database user doesn't have privileges
4. Files uploaded to wrong directory

**Fix these first before trying anything else!**
