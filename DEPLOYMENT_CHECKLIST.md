# ✅ HOSTINGER DEPLOYMENT CHECKLIST
## Your Credentials: u166093127

---

## STEP 1: Database Setup ✓ DONE
- [x] Created database: `u166093127_matrimony`
- [x] Created user: `u166093127_dbuser`
- [x] Password: `Uttu@2025`
- [x] Added user to database with ALL PRIVILEGES

---

## STEP 2: Update Files BEFORE Upload

### Option A: Replace Files (Recommended)
1. **Delete your current `includes/dbconn.php`**
2. **Rename `includes/dbconn-UPLOAD-THIS.php` to `dbconn.php`**
3. **Open `functions.php`**
4. **Replace lines 3-6 with:**
   ```php
   $host="localhost"; // Host name 
   $username="u166093127_dbuser"; // Mysql username 
   $password="Uttu@2025"; // Mysql password 
   $db_name="u166093127_matrimony"; // Database name
   ```

### Option B: Manual Edit
Edit these 2 files and change:
- `127.0.0.1` → `localhost`
- `root` → `u166093127_dbuser`
- `matrimony` → `u166093127_matrimony`

**Files to edit:**
- [ ] `includes/dbconn.php`
- [ ] `functions.php` (line 3-6)

---

## STEP 3: Import Database

1. **Go to cPanel → phpMyAdmin**
2. **Click on database:** `u166093127_matrimony`
3. **Click Import tab**
4. **Choose file:** `db/matrimony.sql`
5. **Click Go**
6. **Wait for "Import has been successfully finished"**

### Then Import Migrations (in order):
- [ ] `db/migrations/001_add_plans.sql`
- [ ] `db/migrations/002_add_payments.sql`
- [ ] `db/migrations/003_add_messages.sql`
- [ ] `db/migrations/004_add_interests.sql`
- [ ] `db/migrations/005_update_users.sql`
- [ ] `db/migrations/006_add_payment_refund_fields.sql`
- [ ] `db/migrations/007_add_sms_system.sql`
- [ ] `db/migrations/008_add_i18n_system.sql`
- [ ] `db/migrations/009_add_cms_and_homepage.sql`
- [ ] `db/migrations/010_add_advanced_search.sql`
- [ ] `db/migrations/011_add_interest_system.sql`
- [ ] `db/migrations/012_add_admin_activity_logs.sql`
- [ ] `db/migrations/013_add_security_tables.sql`
- [ ] `db/migrations/014_update_password_field.sql`
- [ ] `db/migrations/015_add_banner_and_custom_sections.sql`

**TIP:** You can import all at once or one by one. Ignore duplicate entry errors.

---

## STEP 4: Upload Files to Hostinger

### Using File Manager:
1. **Go to:** Hostinger Control Panel → File Manager
2. **Navigate to:** `public_html/`
3. **Delete any default files** (index.html, etc.)
4. **Upload ALL your project files**
5. **Ensure structure:**
   ```
   public_html/
   ├── index.php
   ├── login.php
   ├── register.php
   ├── functions.php (UPDATED!)
   ├── admin/
   ├── includes/
   │   └── dbconn.php (UPDATED!)
   ├── css/
   ├── js/
   ├── images/
   └── db/
   ```

### Create These Folders:
- [ ] `uploads/` (set permissions to 755)
- [ ] `uploads/banners/` (set permissions to 755)
- [ ] `profile/` (set permissions to 755)

**How to set permissions in File Manager:**
- Right-click folder → Permissions
- Enter: 755
- Check "Recursive" for subdirectories
- Click OK

---

## STEP 5: Test Your Site

### 1. Upload debug.php
- [ ] Upload `debug.php` to `public_html/`
- [ ] Visit: `http://yourdomain.com/debug.php`
- [ ] Check if database connection shows ✓ Success
- [ ] **DELETE debug.php after testing!**

### 2. Test Homepage
- [ ] Visit: `http://yourdomain.com/`
- [ ] Should show homepage with profiles

### 3. Test Admin Panel
- [ ] Visit: `http://yourdomain.com/admin/`
- [ ] Login: `admin` / `admin123`
- [ ] **Change password immediately!**

### 4. Test Registration
- [ ] Visit: `http://yourdomain.com/register.php`
- [ ] Create test account
- [ ] Verify account created

---

## STEP 6: Final Setup

- [ ] Change admin password
- [ ] Go to Admin → Homepage Configuration
- [ ] Upload banner image
- [ ] Configure homepage sections
- [ ] Enable SSL certificate (free in Hostinger)
- [ ] Set up automatic backups

---

## 🆘 TROUBLESHOOTING

### If you see "Cannot connect to database":
1. Check `includes/dbconn.php` has:
   - `localhost` (not 127.0.0.1)
   - `u166093127_dbuser`
   - `u166093127_matrimony`
2. Verify in cPanel → MySQL Databases that user has privileges
3. Run `debug.php` to see exact error

### If you see blank pages:
1. Check error_log in File Manager
2. Verify all files uploaded to `public_html/` (not a subfolder)
3. Check PHP version is 7.4+ in cPanel

### If admin login doesn't work:
1. Check database imported correctly
2. Run this SQL in phpMyAdmin:
   ```sql
   SELECT * FROM users WHERE userlevel = 1;
   ```
3. If no admin, run:
   ```sql
   UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
   WHERE username = 'admin';
   ```

---

## 📞 NEED HELP?

**Send me:**
1. Screenshot of debug.php output
2. Error messages from error_log
3. What step you're stuck on

**Your credentials (save this):**
```
Host: localhost
Database: u166093127_matrimony
Username: u166093127_dbuser
Password: Uttu@2025
```

---

## ✅ SUCCESS CHECKLIST

When everything works, you should be able to:
- [ ] Access homepage at yourdomain.com
- [ ] Login to admin at yourdomain.com/admin
- [ ] Register new users
- [ ] Upload banner images
- [ ] See profiles on homepage
- [ ] Admin dashboard shows metrics

---

**Next:** Start with STEP 2 - Update the database connection files!
