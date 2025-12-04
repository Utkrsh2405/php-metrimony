# 🚀 Quick Deploy to Hostinger

## Method 1: Easy Way (Install Wizard)

### Step 1: Upload Files
1. Download/clone this repository
2. Upload ALL files to Hostinger `public_html/` folder
3. Rename `htaccess-hostinger.txt` to `.htaccess`

### Step 2: Create Database
1. Login to Hostinger cPanel
2. Go to **MySQL Databases**
3. Create database: `matrimony`
4. Create user and assign ALL PRIVILEGES
5. Note your credentials:
   ```
   Host: localhost
   Database: cpanelusername_matrimony
   Username: cpanelusername_dbuser
   Password: YourPassword
   ```

### Step 3: Import Database
1. Open **phpMyAdmin**
2. Select your database
3. Click **Import**
4. Upload `db/matrimony.sql`
5. Click **Go**

### Step 4: Run Installer
1. Visit: `http://yourdomain.com/install.php`
2. Follow the wizard steps
3. Enter your database credentials
4. Complete installation

### Step 5: Secure Your Site
1. **Delete `install.php`** after installation
2. Login to admin: `http://yourdomain.com/admin/`
3. Default credentials: `admin` / `admin123`
4. **Change admin password immediately!**

---

## Method 2: Manual Configuration

If you prefer manual setup:

1. Copy `config.sample.php` to `config.php`
2. Edit `config.php` with your Hostinger credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cpanelusername_matrimony');
   define('DB_USER', 'cpanelusername_dbuser');
   define('DB_PASS', 'YourPassword');
   define('SITE_URL', 'https://yourdomain.com');
   ```
3. Upload all files to `public_html/`
4. Import database via phpMyAdmin

---

## File Structure on Hostinger

```
public_html/
├── .htaccess          ← Rename from htaccess-hostinger.txt
├── config.php         ← Created by installer
├── index.php
├── admin/
├── api/
├── includes/
├── css/
├── js/
├── images/
├── uploads/           ← chmod 755
├── profile/           ← chmod 755
└── db/
    └── matrimony.sql  ← Import this
```

---

## Troubleshooting

### "Database connection failed"
- Check credentials in cPanel → MySQL Databases
- Use `localhost` not `127.0.0.1`
- Ensure user has ALL PRIVILEGES

### "500 Internal Server Error"
- Check `.htaccess` syntax
- Look at error_log in File Manager
- Verify PHP version is 8.0+

### "Page not found"
- Check file permissions (755 for folders)
- Ensure mod_rewrite is enabled
- Verify files uploaded correctly

### "Upload not working"
- Set folder permissions: `chmod 755 uploads/ profile/`
- Check PHP upload limits in `.htaccess`

---

## Quick Checklist

- [ ] Files uploaded to public_html
- [ ] .htaccess renamed/created
- [ ] Database created in cPanel
- [ ] Database user created with ALL PRIVILEGES
- [ ] matrimony.sql imported
- [ ] install.php wizard completed
- [ ] install.php deleted
- [ ] Admin password changed
- [ ] SSL certificate enabled (Hostinger provides free SSL)
- [ ] Site tested and working

---

## Support

- Documentation: `HOSTINGER_SETUP.md` (detailed guide)
- Full deployment guide: `DEPLOYMENT.md`
