# PHP Matrimony - Complete Matrimonial Platform

A comprehensive, production-ready matrimonial web portal built with PHP and MySQL. Features include advanced search, messaging, interests, subscriptions, admin dashboard, and much more.

## 🌟 Key Features

### Member Features
- **User Registration & Authentication** - Secure registration with email verification
- **Profile Creation** - Comprehensive profile with 30+ data points
- **Profile Completion Widget** - Smart progress tracker with suggestions
- **Advanced Search** - 12+ filters (age, religion, location, education, etc.)
- **Saved Searches** - Save and reuse search criteria
- **Express Interests** - Send/receive interests with SMS notifications
- **Shortlist & Favorites** - Save profiles with private notes
- **Private Messaging** - Threaded conversations with read receipts
- **Plan Quotas Dashboard** - Track subscription usage in real-time

### Admin Features
- **Interactive Dashboard** - Charts showing users, revenue, subscriptions
- **Member Management** - CRUD operations, photo verification, bulk actions
- **Subscription Plans** - Create plans with custom quotas
- **Payment Management** - Transaction logs, refunds, revenue analytics
- **SMS Template System** - Multi-gateway support (Twilio, MSG91)
- **CMS Pages** - Create About Us, Privacy Policy, Terms pages
- **Homepage Configuration** - Manage hero, features, testimonials
- **Interest/Message Logs** - View and moderate all interactions
- **Activity Logging** - Comprehensive audit trail with IP tracking
- **Multi-language** - i18n system with translation editor

### System Features
- **Plan Quotas Middleware** - Centralized quota enforcement
- **Security Enhancements** - CSRF protection, rate limiting, XSS prevention
- **Profile Completion** - Weighted calculation with category breakdown
- **SMS Notifications** - Interest accepted, new message alerts
- **Search Analytics** - Admin dashboard with popular filters
- **Responsive Design** - Mobile-friendly Bootstrap interface

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 3, JavaScript, jQuery, Chart.js, SortableJS, TinyMCE
- **Backend**: PHP 8.3+ with OOP design patterns
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Architecture**: MVC-style with API endpoints
- **Security**: CSRF protection, XSS prevention, rate limiting
- **Notifications**: SMS (Twilio/MSG91), Email
- **Server**: Nginx/Apache with PHP-FPM

## 📋 Prerequisites

- PHP 8.0+ with extensions: mysqli, json, mbstring, curl, gd
- MySQL 5.7+ or MariaDB 10.3+
- Docker (recommended for development)
- Git
- SSL certificate (for production)

## 🚀 Quick Start

### Development Setup (Docker)

```bash
# Clone repository
git clone https://github.com/Utkrsh2405/php-metrimony.git
cd php-metrimony

# Start MySQL container
docker run -d --name matrimony-mysql \
  -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
  -e MYSQL_DATABASE=matrimony \
  -p 3306:3306 \
  mysql:5.7

# Wait for MySQL initialization
sleep 15

# Run migrations
for file in db/migrations/*.sql; do
    docker exec -i matrimony-mysql mysql -uroot matrimony < "$file"
done

# Start PHP server
php -S 0.0.0.0:8080

# Access at http://localhost:8080
```

### Production Deployment

See **[DEPLOYMENT.md](DEPLOYMENT.md)** for comprehensive production setup guide including:
- Server requirements and setup
- Nginx/Apache configuration
- SSL certificate installation
- Security hardening
- Performance optimization
- Backup procedures
- Monitoring setup

## 👤 Default Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin`
- **User Level**: 1 (Admin)

### Test User Accounts
The database includes several test profiles:
- `test`, `aswin`, `reshma`, `rahul`, etc.

## 📂 Project Structure

```
php-metrimony/
├── admin/                  # Admin panel
│   ├── index.php          # Dashboard with charts
│   ├── members.php        # Member management
│   ├── plans.php          # Subscription plans
│   ├── payments.php       # Payment management
│   ├── sms-templates.php  # SMS template editor
│   ├── translations.php   # i18n management
│   ├── cms-pages.php      # CMS editor
│   ├── homepage-config.php # Homepage sections
│   ├── interest-logs.php  # Interest monitoring
│   ├── message-logs.php   # Message moderation
│   ├── activity-logs.php  # Audit trail
│   └── api/               # Admin API endpoints
├── api/                    # RESTful APIs
│   ├── messages.php       # Messaging system
│   ├── interest.php       # Express interest
│   ├── interest-response.php # Accept/decline
│   ├── shortlist.php      # Favorites management
│   ├── search.php         # Advanced search
│   ├── saved-searches.php # Saved searches
│   └── profile-completion.php # Profile widget
├── db/migrations/          # Database migrations (013 files)
├── includes/               # Utilities & classes
│   ├── dbconn.php         # Database connection
│   ├── sms-sender.php     # SMS gateway integration
│   ├── quota-manager.php  # Quota enforcement
│   ├── profile-completion.php # Profile calculator
│   ├── activity-logger.php # Audit logging
│   └── security.php       # Security utilities
├── advanced-search.php     # Member search interface
├── interests.php          # Interest inbox
├── shortlist.php          # Shortlist viewer
├── messages.php           # Messaging inbox
├── quota-dashboard.php    # Plan quotas
├── DEPLOYMENT.md          # Production deployment guide
└── README.md              # This file
```

## 🎯 Core Components

### 1. Quota Management System
```php
require_once 'includes/quota-manager.php';
$quota = getQuotaManager($conn, $user_id);

// Check if user has quota
if ($quota->hasQuota('interests_express', 'interests')) {
    // Send interest
} else {
    // Show upgrade message
}

// Get all quotas
$quotas = $quota->getAllQuotas();
```

### 2. Profile Completion
```php
require_once 'includes/profile-completion.php';
$profile = getProfileCompletion($conn, $user_id);

// Get completion percentage
$percentage = $profile->getCompletionPercentage();

// Get missing fields
$suggestions = $profile->getTopSuggestions(5);
```

### 3. Activity Logging
```php
require_once 'includes/activity-logger.php';
$logger = getActivityLogger($conn);

// Log admin action
$logger->logUserUpdate($user_id, $old_data, $new_data);
$logger->logPayment('refund', $payment_id, 'Refunded $50');
```

### 4. Security Utilities
```php
require_once 'includes/security.php';

// CSRF protection
echo Security::csrfField();
if (!Security::validateCSRFToken($_POST['csrf_token'])) {
    die('Invalid token');
}

// Rate limiting
if (!Security::checkRateLimit('login_' . $ip, 5, 300)) {
    die('Too many attempts');
}

// XSS prevention
$clean = Security::sanitizeInput($_POST['input']);
```

## 🔧 Configuration

### Database Host Configuration

If you encounter "No such file or directory" errors, ensure the database host is set to `127.0.0.1` instead of `localhost` in:
- `functions.php`
- `includes/dbconn.php`

### File Upload Directory

Ensure the `profile/` directory has write permissions for photo uploads:
```bash
chmod 755 profile/
```

## 🎯 Usage

1. **Register** - Create a new account at `/register.php`
2. **Login** - Sign in at `/login.php`
3. **Create Profile** - Fill in detailed profile information
4. **Upload Photos** - Add up to 4 photos to your profile
5. **Set Preferences** - Define your partner preferences
6. **Search** - Find matches based on criteria
7. **View Profiles** - Browse and view potential matches

## 🐛 Troubleshooting

### MySQL Connection Error
```
Fatal error: Uncaught mysqli_sql_exception: No such file or directory
```
**Solution**: Change `localhost` to `127.0.0.1` in database configuration files.

### Port Already in Use
```
Failed to listen on 0.0.0.0:8080
```
**Solution**: Use a different port `php -S localhost:8081`

### Missing mysqli Extension
```
Call to undefined function mysqli_connect()
```
**Solution**: Install php-mysql package: `sudo apt install php-mysql`

## 📝 Notes

- This is an educational project for learning PHP and MySQL
- Admin dashboard (`admin.php`) is not included in this version
- Default passwords are stored in plain text - implement proper hashing for production use
- Some features may require additional configuration

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is open source and available for educational purposes.

## 🔗 Links

- **Original Project**: http://projectworlds.in/online-matrimonial-project-in-php/
- **Repository**: https://github.com/Utkrsh2405/php-metrimony

## 👨‍💻 Developer

**Utkrsh2405**

---

⭐ If you find this project helpful, please give it a star!

