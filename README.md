# Online Matrimonial Project in PHP

A comprehensive matrimonial web portal built with PHP and MySQL that enables users to find life partners based on their preferences.

## 🌟 Features

- **User Registration & Authentication** - Secure user registration and login system
- **Profile Creation** - Detailed profile creation with personal, educational, and professional information
- **Photo Gallery** - Upload up to 4 photos per profile
- **Advanced Search** - Search profiles by age, religion, caste, location, marital status, and more
- **Partner Preferences** - Set and manage partner preference criteria
- **Profile Viewing** - Browse and view detailed profiles of potential matches
- **Match Recommendations** - Get matched profiles based on preferences

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 3, JavaScript, jQuery
- **Backend**: PHP 8.3
- **Database**: MySQL 5.7
- **Server**: PHP Built-in Development Server / Apache

## 📋 Prerequisites

- PHP 8.3 or higher with mysqli extension
- MySQL 5.7 or higher / MariaDB
- Docker (optional, for containerized MySQL)
- Git

## 🚀 Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/Utkrsh2405/php-metrimony.git
cd php-metrimony
```

### 2. Database Setup

#### Option A: Using Docker (Recommended)

```bash
# Start MySQL container
docker run -d --name matrimony-mysql \
  -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
  -e MYSQL_DATABASE=matrimony \
  -p 3306:3306 \
  mysql:5.7

# Wait for MySQL to initialize (15 seconds)
sleep 15

# Import the database
docker exec -i matrimony-mysql mysql -uroot matrimony < db/matrimony.sql
```

#### Option B: Using Local MySQL

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE matrimony;"

# Import the SQL file
mysql -u root -p matrimony < db/matrimony.sql
```

### 3. Configure Database Connection

The database configuration is already set in:
- `functions.php` (line 3)
- `includes/dbconn.php` (line 3)

Default settings:
```php
$host = "127.0.0.1";
$username = "root";
$password = "";
$db_name = "matrimony";
```

Update these values if your database configuration differs.

### 4. Install PHP Extensions (if needed)

```bash
# For Debian/Ubuntu
sudo apt install php-cli php-mysql

# Verify mysqli extension
php -m | grep mysqli
```

### 5. Run the Application

```bash
# Start PHP development server
php -S localhost:8080

# Or use system PHP
/usr/bin/php -S 0.0.0.0:8080
```

Access the application at: **http://localhost:8080**

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
├── auth/                   # Authentication scripts
├── css/                    # Stylesheets
├── db/                     # Database SQL file
├── fonts/                  # Font files
├── images/                 # Image assets
├── includes/               # PHP includes and utilities
├── js/                     # JavaScript files
├── profile/                # User profile photos
├── index.php               # Homepage
├── login.php               # Login page
├── register.php            # Registration page
├── create_profile.php      # Profile creation
├── search.php              # Search functionality
├── view_profile.php        # View profile details
├── userhome.php           # User dashboard
└── README.md              # This file
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

