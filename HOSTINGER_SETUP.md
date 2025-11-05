# Hostinger Deployment Guide for PHP Matrimony

Complete step-by-step guide to deploy your matrimony portal on Hostinger.

## Prerequisites

- Hostinger hosting account (Business or Premium plan recommended)
- cPanel access
- FTP/SFTP client (FileZilla) or use Hostinger File Manager
- Your database password: `Uttu@2025`

---

## Step 1: Prepare Your Files

### Files to Upload
Upload ALL files from your project to Hostinger, including:
- All PHP files
- `css/`, `js/`, `images/`, `fonts/` directories
- `includes/`, `admin/`, `auth/` directories
- `db/` directory (for migrations)

### Files to Modify BEFORE Upload

**IMPORTANT:** Update these files with your Hostinger database credentials:

#### 1. `includes/dbconn.php`
```php
<?php 
$host="localhost"; // Change from 127.0.0.1 to localhost
$username="YOUR_CPANEL_USERNAME_dbuser"; // Get from cPanel MySQL
$password="Uttu@2025"; // Your database password
$db_name="YOUR_CPANEL_USERNAME_matrimony"; // Database name

$conn=mysqli_connect("$host", "$username", "$password")or die("cannot connect"); 
mysqli_select_db($conn,"$db_name")or die("cannot select DB");
?>
```

#### 2. `functions.php`
```php
function mysqlexec(){
	$host="localhost"; // Change from 127.0.0.1 to localhost
	$username="YOUR_CPANEL_USERNAME_dbuser"; // Same as above
	$password="Uttu@2025"; // Your database password
	$db_name="YOUR_CPANEL_USERNAME_matrimony"; // Same as above
	
	$conn=mysqli_connect("$host", "$username", "$password")or die("cannot connect"); 
	mysqli_select_db($conn,"$db_name")or die("cannot select DB");
	
	return $conn;
}
```

---

## Step 2: Create MySQL Database in cPanel

1. **Login to cPanel** (usually: https://yourdomain.com:2083)

2. **Create Database:**
   - Go to **MySQL Databases**
   - Under "Create New Database":
     - Database Name: `matrimony`
     - Click **Create Database**
   - Your full database name will be: `cpanelusername_matrimony`

3. **Create Database User:**
   - Scroll to "MySQL Users"
   - Username: `dbuser`
   - Password: `Uttu@2025`
   - Click **Create User**
   - Your full username will be: `cpanelusername_dbuser`

4. **Add User to Database:**
   - Scroll to "Add User To Database"
   - Select User: `cpanelusername_dbuser`
   - Select Database: `cpanelusername_matrimony`
   - Click **Add**
   - On privileges page, check **ALL PRIVILEGES**
   - Click **Make Changes**

5. **Note Your Credentials:**
   ```
   Host: localhost
   Database: cpanelusername_matrimony
   Username: cpanelusername_dbuser
   Password: Uttu@2025
   ```

---

## Step 3: Import Database

### Option A: Using phpMyAdmin (Recommended)

1. **Access phpMyAdmin** from cPanel
2. Select your database (`cpanelusername_matrimony`)
3. Click **Import** tab
4. Upload `db/matrimony.sql` file
5. Click **Go**
6. Wait for success message

### Option B: Using Terminal (if you have SSH access)

```bash
mysql -u cpanelusername_dbuser -p cpanelusername_matrimony < db/matrimony.sql
# Enter password: Uttu@2025
```

### Apply Migrations (After Base Import)

In phpMyAdmin, click **SQL** tab and run each migration file in order:

```sql
-- Copy and paste content from each file in this order:
-- 1. db/migrations/001_add_plans.sql
-- 2. db/migrations/002_add_payments.sql
-- 3. db/migrations/003_add_messages.sql
-- ... continue through 015_add_banner_and_custom_sections.sql
```

**OR** use SQL import for each migration file.

---

## Step 4: Upload Files to Hostinger

### Using Hostinger File Manager:

1. Login to **Hostinger Control Panel**
2. Go to **File Manager**
3. Navigate to `public_html` directory
4. Delete default files (index.html, etc.)
5. Upload all your project files
6. Ensure file structure:
   ```
   public_html/
   ├── index.php
   ├── login.php
   ├── register.php
   ├── admin/
   ├── includes/
   ├── css/
   ├── js/
   ├── images/
   ├── uploads/
   └── ... other files
   ```

### Using FTP/SFTP:

1. Get FTP credentials from Hostinger panel
2. Connect using FileZilla
3. Upload all files to `public_html/`

---

## Step 5: Set File Permissions

### Critical Permissions:

```bash
# Directories that need write permissions:
chmod 755 public_html/
chmod 755 public_html/uploads/
chmod 755 public_html/uploads/banners/
chmod 755 public_html/profile/

# If using File Manager:
# Right-click folder → Permissions → Set to 755
```

### Create Missing Directories:

If they don't exist, create:
```
uploads/
uploads/banners/
profile/
```

---

## Step 6: Configure PHP Settings

### Create `.htaccess` file in `public_html/`:

```apache
# Enable error logging
php_flag display_errors Off
php_flag log_errors On
php_value error_log /home/cpanelusername/public_html/error_log

# Increase limits
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 256M

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Redirect to HTTPS (if you have SSL)
# RewriteEngine On
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Protect sensitive files
<FilesMatch "^(dbconn\.php|config\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Pretty URLs (optional)
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# RewriteRule ^profile/([0-9]+)$ profile.php?id=$1 [L,QSA]
```

---

## Step 7: Test Your Installation

### 1. Test Homepage:
Visit: `http://yourdomain.com/`

**Expected:** Homepage loads with profiles

**If Error:** Check error_log file in cPanel or enable display_errors temporarily

### 2. Test Admin Login:
Visit: `http://yourdomain.com/admin/`

**Default Admin Credentials:**
- Username: `admin`
- Password: `admin123`

**Important:** Change admin password immediately after first login!

### 3. Test User Registration:
Visit: `http://yourdomain.com/register.php`

Create a test account to verify registration works.

### 4. Test Database Connection:

Create a test file `test-db.php` in `public_html/`:
```php
<?php
require_once('includes/dbconn.php');

if ($conn) {
    echo "✓ Database connected successfully!<br>";
    echo "MySQL version: " . mysqli_get_server_info($conn) . "<br>";
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    $row = mysqli_fetch_assoc($result);
    echo "Users in database: " . $row['count'];
} else {
    echo "✗ Database connection failed!<br>";
    echo "Error: " . mysqli_connect_error();
}
?>
```

Visit: `http://yourdomain.com/test-db.php`

**Delete this file after testing!**

---

## Common Issues & Solutions

### Issue 1: "Cannot connect to database"

**Causes:**
- Wrong database credentials
- Database user not added to database
- Wrong host (should be `localhost` not `127.0.0.1`)

**Fix:**
1. Double-check credentials in cPanel → MySQL Databases
2. Update `includes/dbconn.php` and `functions.php`
3. Ensure user has ALL PRIVILEGES on database
4. Use `localhost` as host, not IP address

### Issue 2: "500 Internal Server Error"

**Causes:**
- PHP syntax error
- Wrong file permissions
- Missing PHP extensions
- .htaccess misconfiguration

**Fix:**
1. Check error_log in cPanel File Manager
2. Set correct permissions (755 for folders, 644 for files)
3. Comment out .htaccess contents to test
4. Enable error display temporarily:
   ```php
   // Add to top of index.php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

### Issue 3: "Admin panel not loading"

**Fix:**
1. Clear browser cache
2. Check if `admin/` folder uploaded correctly
3. Verify database has `admin_activity_logs` table
4. Check session settings in php.ini

### Issue 4: "Upload not working"

**Fix:**
1. Check folder permissions: `chmod 755 uploads/`
2. Verify PHP upload settings in .htaccess
3. Check disk space in cPanel

### Issue 5: "Page not found" or blank page

**Fix:**
1. Check if PHP version is 7.4+ (Settings → PHP Configuration)
2. Ensure mysqli extension enabled
3. Check file paths are correct (case-sensitive on Linux)
4. Look for syntax errors in error_log

### Issue 6: "Session errors"

**Fix:**
1. Ensure `session_start()` is called before any output
2. Check session.save_path permissions
3. Clear browser cookies
4. Verify PHP session extension enabled

---

## Security Checklist for Production

- [ ] Change admin password from default
- [ ] Update all default credentials
- [ ] Enable HTTPS/SSL certificate (free with Let's Encrypt)
- [ ] Set display_errors = Off in production
- [ ] Enable error logging
- [ ] Set secure file permissions (755/644)
- [ ] Add .htaccess protection for sensitive files
- [ ] Regular database backups
- [ ] Keep PHP version updated
- [ ] Remove test-db.php file
- [ ] Review security settings in includes/security.php

---

## Database Backup (Important!)

### Automatic Backup (Recommended):

1. Go to cPanel → **Backups**
2. Enable automatic backups
3. Set backup schedule (daily/weekly)

### Manual Backup:

1. Go to **phpMyAdmin**
2. Select your database
3. Click **Export**
4. Select format: SQL
5. Click **Go**
6. Save file safely

### Restore from Backup:

1. phpMyAdmin → Select database
2. Click **Import**
3. Choose backup file
4. Click **Go**

---

## Performance Optimization

### 1. Enable OPCache (cPanel):
- PHP Settings → Enable OPCache

### 2. Enable Gzip Compression (.htaccess):
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### 3. Browser Caching (.htaccess):
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## Monitoring & Maintenance

### Check Error Logs:
- cPanel → File Manager → `error_log`
- cPanel → Metrics → Errors

### Monitor Resources:
- cPanel → Resource Usage
- Watch CPU and Memory usage

### Regular Maintenance:
- Weekly database optimization (phpMyAdmin → Optimize table)
- Monthly security updates
- Regular backups
- Monitor disk space

---

## Getting Help

If you still have issues:

1. **Check Error Logs:**
   - cPanel → File Manager → error_log
   - Read last 50 lines for clues

2. **Contact Hostinger Support:**
   - Provide error messages
   - Share error_log contents
   - Mention PHP version and hosting plan

3. **Common Hostinger Settings:**
   - PHP Version: 8.0+ recommended
   - Required Extensions: mysqli, session, json, gd, mbstring
   - Memory Limit: 256M minimum

---

## Quick Reference

### Database Connection Template:
```php
$host = "localhost";
$username = "cpanelusername_dbuser";
$password = "Uttu@2025";
$db_name = "cpanelusername_matrimony";
```

### File Structure:
```
public_html/
├── .htaccess (create this)
├── index.php
├── admin/
│   ├── index.php
│   ├── login.php
│   └── api/
├── includes/
│   ├── dbconn.php (UPDATE!)
│   └── security.php
├── uploads/ (chmod 755)
└── profile/ (chmod 755)
```

### Test URLs:
- Homepage: http://yourdomain.com/
- Admin: http://yourdomain.com/admin/
- User Login: http://yourdomain.com/login.php
- Register: http://yourdomain.com/register.php

---

## Success! 🎉

Your matrimony portal should now be live on Hostinger!

**Next Steps:**
1. Change admin password
2. Add profiles
3. Configure homepage (Admin → Homepage Configuration)
4. Set up payment gateway (if needed)
5. Enable SSL certificate
6. Share with users!

**Need help?** Check error_log or contact support with specific error messages.
