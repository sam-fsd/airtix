# AirTix Setup Instructions

## Prerequisites
- XAMPP (or LAMP/WAMP/MAMP)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

## Installation Steps

### 1. Install XAMPP
- Download XAMPP from https://www.apachefriends.org
- Install with Apache and MySQL components
- Start Apache and MySQL from XAMPP Control Panel

### 2. Setup Project
- Copy the `airtix` folder to `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)
- Your project path should be: `C:\xampp\htdocs\airtix\`

### 3. Create Database
- Open browser and go to `http://localhost/phpmyadmin`
- Click "New" to create a database
- Name it `airtix`
- Click on the `airtix` database
- Click "Import" tab
- Choose file: `database/airtix.sql`
- Click "Go" to import
- Import `database/seed.sql` for sample data

### 4. Configure Application
- Open `config.php` in text editor
- Verify database settings:
  - DB_HOST: localhost
  - DB_USER: root
  - DB_PASS: (leave empty for XAMPP default)
  - DB_NAME: airtix

### 5. Test Installation
- Open browser and go to `http://localhost/airtix`
- You should see the AirTix homepage

## Default Login Credentials

### Admin Account
- Email: admin@airtix.com
- Password: admin123

### User Account
- Email: john.doe@email.com
- Password: user123

## Troubleshooting

### Cannot connect to database
- Check XAMPP MySQL is running
- Verify database name is `airtix`
- Check username is `root` with empty password

### Page not found errors
- Ensure `.htaccess` file exists
- Check Apache `mod_rewrite` is enabled
- Verify file permissions (Linux/Mac)

### PHP errors displayed
- Normal for development
- Will be turned off in production

## Next Steps
1. Login with admin account
2. Add more flights in admin panel
3. Test booking process with user account
4. Explore all features