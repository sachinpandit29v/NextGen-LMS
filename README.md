# NextGen Learning Management System

NextGen LMS is a PHP and MySQL learning platform with a public student experience and a separate admin area for managing users, courses, and contact submissions. It uses plain PHP, Bootstrap 5, PDO, and session-based authentication, so it can run locally without a build step.

## Quick Start

### Prerequisites

- XAMPP or another Apache, PHP, and MySQL stack
- PHP 7.4 or newer
- MySQL 5.7 or newer

### 1. Place the project in your web root

Example XAMPP path:

```text
C:\xampp\htdocs\NextGen-LMS
```

You can use a different folder name. The application now detects its base path automatically.

### 2. Check database credentials

Default credentials are in `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nextgen_lms');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Update them if your MySQL setup is different.

### 3. Start Apache and MySQL

From the XAMPP Control Panel, start:

- Apache
- MySQL

### 4. Run the setup script

From the project root:

```powershell
C:\xampp\php\php.exe setup\setup.php
```

This script will:

- create the `nextgen_lms` database if needed
- import the schema from `sql/nextgen_lms.sql`
- seed demo users and sample courses

### 5. Open the app

Visit:

```text
http://localhost/NextGen-LMS/
```

If you renamed the folder, replace `NextGen-LMS` with your actual folder name.

## Demo Accounts

- Admin: `admin@nextgen.com` / `admin123`
- Student: `student@nextgen.com` / `student123`
- Demo student: `demo@nextgen.com` / `demo123`

## Manual Setup

If you prefer to import the database manually:

1. Start Apache and MySQL.
2. Open `http://localhost/phpmyadmin/`.
3. Create a database named `nextgen_lms`.
4. Import `sql/nextgen_lms.sql`.
5. Make sure `config/database.php` matches your local MySQL credentials.

## Project Highlights

### User features

- Registration and login
- Guest-only login and register pages
- Course browsing and course details
- Profile management
- Password reset flow
- Contact form

### Admin features

- Admin dashboard
- User management
- Course management
- Contact submission management

## Project Structure

```text
NextGen-LMS/
|-- index.php
|-- .htaccess
|-- README.md
|-- config/
|   |-- app.php
|   -- database.php
|-- includes/
|   |-- footer.php
|   -- navbar.php
|-- user/
|   |-- about.php
|   |-- contact.php
|   |-- course-details.php
|   |-- courses.php
|   |-- dashboard.php
|   |-- forgot-password.php
|   |-- home.php
|   |-- login.php
|   |-- logout.php
|   |-- profile.php
|   |-- register.php
|   |-- reset-password.php
|   |-- 404.php
|   -- style.css
|-- admin/
|   |-- admin-panel.php
|   |-- contact-management.php
|   |-- course-management.php
|   |-- user-management.php
|   |-- css/
|   |   -- admin-style.css
|   -- includes/
|       -- sidebar.php
|-- sql/
|   -- nextgen_lms.sql
|-- setup/
|   |-- migrate.php
|   -- setup.php
|-- setup.bat
```

## Sharing Checklist

Before sharing the project with someone else:

- Confirm `config/database.php` has the right MySQL credentials for their environment.
- Ask them to run `setup/setup.php` once.
- Share the correct local URL based on the folder name inside `htdocs`.
- If they are not using XAMPP, make sure Apache serves the project directory and MySQL is reachable with the configured credentials.

## Troubleshooting

### Setup script fails

- Make sure MySQL is running before executing `setup/setup.php`.
- Verify the credentials in `config/database.php`.
- Use the XAMPP PHP binary when running locally: `C:\xampp\php\php.exe`.

### Database connection failed

- Confirm the `nextgen_lms` database exists.
- Re-run the setup script after fixing credentials.

### Password reset link looks wrong

- Open the app using the same host and folder you want others to use.
- The reset link is built from the current request host and the detected app path.

## Security Notes

This codebase includes:

- password hashing with `password_hash()`
- PDO prepared statements
- CSRF protection
- session-based authentication
- basic rate limiting for registration and login

## License

This project is for educational purposes. Review any third-party assets or CDN dependencies before public distribution.
