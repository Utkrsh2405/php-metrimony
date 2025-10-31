# PHP Matrimony - Deployment Guide

## 📋 Table of Contents
1. [Server Requirements](#server-requirements)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Installation Steps](#installation-steps)
4. [Configuration](#configuration)
5. [Database Setup](#database-setup)
6. [Security Hardening](#security-hardening)
7. [Performance Optimization](#performance-optimization)
8. [Backup & Recovery](#backup--recovery)
9. [Monitoring](#monitoring)
10. [Troubleshooting](#troubleshooting)

---

## 🖥️ Server Requirements

### Minimum Requirements
- **OS**: Linux (Ubuntu 20.04+, CentOS 8+, or similar)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 8.0+ with extensions:
  - mysqli
  - json
  - mbstring
  - curl
  - gd (for image processing)
  - zip
  - xml
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Memory**: 2GB RAM minimum (4GB recommended)
- **Storage**: 20GB minimum (SSD recommended)
- **SSL Certificate**: Required for production

### Recommended Production Setup
- **OS**: Ubuntu 22.04 LTS
- **Web Server**: Nginx 1.22+ with PHP-FPM
- **PHP**: 8.3+
- **Database**: MySQL 8.0+ or MariaDB 10.11+
- **Memory**: 8GB RAM
- **Storage**: 50GB SSD
- **CDN**: CloudFlare or similar
- **SSL**: Let's Encrypt or commercial certificate

---

## ✅ Pre-Deployment Checklist

### Code Preparation
- [ ] All code committed to version control
- [ ] Dependencies installed via Composer (if applicable)
- [ ] Environment-specific configuration separated
- [ ] Debug mode disabled
- [ ] Error logging configured
- [ ] All migrations tested

### Security Checks
- [ ] All passwords changed from defaults
- [ ] Database credentials secured
- [ ] API keys stored in environment variables
- [ ] CSRF protection enabled
- [ ] SQL injection prevention verified
- [ ] XSS sanitization implemented
- [ ] File upload restrictions in place
- [ ] Rate limiting configured

### Database
- [ ] Database backups configured
- [ ] Indexes optimized
- [ ] All migrations applied
- [ ] Sample data removed
- [ ] Database connection pooling configured

### Third-Party Services
- [ ] SMS gateway configured (Twilio/MSG91)
- [ ] Payment gateway credentials set
- [ ] Email service configured
- [ ] Storage/CDN configured (if applicable)

---

## 🚀 Installation Steps

### 1. Server Setup (Ubuntu 22.04)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Nginx
sudo apt install nginx -y

# Install PHP 8.3 and extensions
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.3-fpm php8.3-mysqli php8.3-json php8.3-mbstring \
    php8.3-curl php8.3-gd php8.3-zip php8.3-xml -y

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Start services
sudo systemctl start nginx
sudo systemctl start php8.3-fpm
sudo systemctl start mysql
sudo systemctl enable nginx
sudo systemctl enable php8.3-fpm
sudo systemctl enable mysql
```

### 2. Application Deployment

```bash
# Create application directory
sudo mkdir -p /var/www/matrimony
cd /var/www/matrimony

# Clone repository
git clone https://github.com/Utkrsh2405/php-metrimony.git .

# Set permissions
sudo chown -R www-data:www-data /var/www/matrimony
sudo chmod -R 755 /var/www/matrimony
sudo chmod -R 775 /var/www/matrimony/uploads

# Create uploads directory if not exists
mkdir -p uploads/profiles uploads/documents
```

### 3. Nginx Configuration

Create `/etc/nginx/sites-available/matrimony`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/matrimony;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # File upload limit
    client_max_body_size 10M;

    # Logging
    access_log /var/log/nginx/matrimony_access.log;
    error_log /var/log/nginx/matrimony_error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ ^/(includes|db)/ {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/matrimony /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 4. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

---

## ⚙️ Configuration

### 1. Database Configuration

Edit `includes/dbconn.php`:

```php
<?php
$host = 'localhost';
$username = 'matrimony_user';
$password = 'STRONG_PASSWORD_HERE';
$database = 'matrimony_db';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection error. Please contact support.");
}

mysqli_set_charset($conn, 'utf8mb4');
?>
```

### 2. Create Database User

```sql
CREATE DATABASE matrimony_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'matrimony_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON matrimony_db.* TO 'matrimony_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Environment Configuration

Create `config.php`:

```php
<?php
// Environment
define('ENVIRONMENT', 'production'); // development, staging, production

// Debug mode
define('DEBUG_MODE', false);

// Base URL
define('BASE_URL', 'https://yourdomain.com');

// SMS Gateway
define('SMS_GATEWAY', 'twilio'); // twilio, msg91, mock
define('TWILIO_SID', 'your_twilio_sid');
define('TWILIO_AUTH_TOKEN', 'your_twilio_token');
define('TWILIO_FROM_NUMBER', '+1234567890');

// Payment Gateway
define('PAYMENT_GATEWAY', 'stripe'); // stripe, razorpay
define('STRIPE_PUBLIC_KEY', 'your_stripe_public_key');
define('STRIPE_SECRET_KEY', 'your_stripe_secret_key');

// Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your_smtp_password');

// File uploads
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Session
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
?>
```

---

## 🗄️ Database Setup

### 1. Run Migrations

```bash
cd /var/www/matrimony

# Run migrations in order
for file in db/migrations/*.sql; do
    mysql -u matrimony_user -p matrimony_db < "$file"
done
```

### 2. Verify Tables

```sql
USE matrimony_db;
SHOW TABLES;
```

Expected tables:
- users
- customer
- plans
- user_subscriptions
- payments
- messages
- interests
- shortlists
- sms_templates
- sms_logs
- cms_pages
- translations
- homepage_config
- saved_searches
- search_history
- admin_activity_logs
- security_logs
- ip_blacklist

---

## 🔒 Security Hardening

### 1. PHP Configuration

Edit `/etc/php/8.3/fpm/php.ini`:

```ini
; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

; Hide PHP version
expose_php = Off

; Error handling
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; File uploads
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20

; Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = Strict
session.use_strict_mode = 1
session.use_only_cookies = 1
```

### 2. MySQL Security

```sql
-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove test database
DROP DATABASE IF EXISTS test;

-- Change root password
ALTER USER 'root'@'localhost' IDENTIFIED BY 'STRONG_ROOT_PASSWORD';

-- Only allow local connections
BIND-ADDRESS = 127.0.0.1
```

### 3. Firewall Configuration

```bash
# Install UFW
sudo apt install ufw -y

# Allow SSH, HTTP, HTTPS
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
```

### 4. File Permissions

```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/matrimony

# Set directory permissions
sudo find /var/www/matrimony -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/matrimony -type f -exec chmod 644 {} \;

# Writable directories
sudo chmod -R 775 /var/www/matrimony/uploads
```

---

## ⚡ Performance Optimization

### 1. PHP-FPM Tuning

Edit `/etc/php/8.3/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

### 2. MySQL Optimization

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_type = 1
query_cache_size = 128M
```

### 3. Enable Caching

```bash
# Install Redis
sudo apt install redis-server -y
sudo systemctl enable redis-server

# Install PHP Redis extension
sudo apt install php8.3-redis -y
sudo systemctl restart php8.3-fpm
```

### 4. Enable Gzip Compression

Add to Nginx config:

```nginx
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;
```

---

## 💾 Backup & Recovery

### 1. Database Backup Script

Create `/usr/local/bin/backup-matrimony-db.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/backup/matrimony/db"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

mysqldump -u matrimony_user -pPASSWORD matrimony_db | gzip > $BACKUP_DIR/matrimony_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "matrimony_*.sql.gz" -mtime +30 -delete
```

```bash
chmod +x /usr/local/bin/backup-matrimony-db.sh
```

### 2. Files Backup Script

Create `/usr/local/bin/backup-matrimony-files.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/backup/matrimony/files"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/matrimony/uploads

# Keep only last 30 days
find $BACKUP_DIR -name "files_*.tar.gz" -mtime +30 -delete
```

### 3. Automated Backups (Cron)

```bash
# Edit crontab
sudo crontab -e

# Add daily backups at 2 AM
0 2 * * * /usr/local/bin/backup-matrimony-db.sh
0 3 * * * /usr/local/bin/backup-matrimony-files.sh
```

### 4. Recovery Process

```bash
# Restore database
gunzip < /backup/matrimony/db/matrimony_YYYYMMDD_HHMMSS.sql.gz | mysql -u matrimony_user -p matrimony_db

# Restore files
tar -xzf /backup/matrimony/files/files_YYYYMMDD_HHMMSS.tar.gz -C /
```

---

## 📊 Monitoring

### 1. Error Monitoring

Create `/var/log/matrimony/` directory:

```bash
sudo mkdir -p /var/log/matrimony
sudo chown www-data:www-data /var/log/matrimony
```

### 2. Health Check Endpoint

Create `health.php`:

```php
<?php
header('Content-Type: application/json');

$status = [
    'status' => 'healthy',
    'timestamp' => time(),
    'checks' => []
];

// Database check
try {
    require_once 'includes/dbconn.php';
    $status['checks']['database'] = mysqli_ping($conn) ? 'ok' : 'failed';
} catch (Exception $e) {
    $status['checks']['database'] = 'failed';
    $status['status'] = 'unhealthy';
}

// Disk space check
$free = disk_free_space('/');
$total = disk_total_space('/');
$percent = ($free / $total) * 100;
$status['checks']['disk_space'] = $percent > 10 ? 'ok' : 'warning';

echo json_encode($status);
?>
```

### 3. Uptime Monitoring

Use external services:
- UptimeRobot (free)
- Pingdom
- New Relic
- Datadog

---

## 🐛 Troubleshooting

### Common Issues

**1. White Screen / 500 Error**
```bash
# Check PHP error log
sudo tail -f /var/log/php/error.log

# Check Nginx error log
sudo tail -f /var/log/nginx/matrimony_error.log

# Check PHP-FPM log
sudo tail -f /var/log/php8.3-fpm.log
```

**2. Database Connection Errors**
```bash
# Verify MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u matrimony_user -p matrimony_db

# Check MySQL error log
sudo tail -f /var/log/mysql/error.log
```

**3. File Upload Failures**
```bash
# Check permissions
ls -la /var/www/matrimony/uploads

# Check PHP settings
php -i | grep upload_max_filesize
php -i | grep post_max_size

# Check Nginx settings
sudo nginx -T | grep client_max_body_size
```

**4. Performance Issues**
```bash
# Check system resources
htop
df -h
free -m

# Check slow queries
sudo mysql -e "SHOW FULL PROCESSLIST;"

# Enable slow query log
sudo mysql -e "SET GLOBAL slow_query_log = 'ON';"
```

---

## 📞 Support

- **Documentation**: https://github.com/Utkrsh2405/php-metrimony
- **Issues**: https://github.com/Utkrsh2405/php-metrimony/issues
- **Email**: support@yourdomain.com

---

## 📄 License

Copyright © 2025 PHP Matrimony. All rights reserved.
