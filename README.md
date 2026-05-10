# PadelPro — Padel Pitch Reservation System

A full-featured PHP + MySQL web application for managing padel court reservations.

## Tech Stack

- **Backend:** PHP (vanilla, no framework)
- **Frontend:** HTML5, CSS3, JavaScript (vanilla)
- **Database:** MySQL
- **Auth:** PHP sessions with password hashing

## Features

- Public landing page with hero, features, and CTA sections
- User registration & login (with role-based redirect)
- User dashboard: view, create, and cancel reservations
- Real-time price calculator on the booking page
- Overlap detection to prevent double bookings
- Admin dashboard with stats (users, reservations, revenue)
- Admin: manage reservations (approve / cancel / delete / filter)
- Admin: manage item categories and pricing types
- Admin: manage users (promote, demote, delete)
- CSRF protection on all forms
- Prepared SQL statements throughout
- Responsive dark-theme sports UI

## Setup

### 1. Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB
- A web server (Apache / Nginx) **with document root pointing to the project folder**

### 2. Database

```bash
mysql -u root -p < database/schema.sql
```

This creates the `padel_reservation` database, all tables, seed data, and a default admin account.

**Default admin credentials:**
- Email: `admin@padel.com`
- Password: `password`

### 3. Configure database connection

Edit `config/db.php` and update the credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'padel_reservation');
```

### 4. Run with PHP built-in server (development)

```bash
cd /path/to/padel_anas
php -S localhost:8000
```

Then open [http://localhost:8000](http://localhost:8000).

> **Note:** PHP's built-in server serves from the project root, so all absolute paths (`/assets/css/style.css`, `/pages/signin.php`, etc.) resolve correctly.

### 5. Apache (production)

Point your `DocumentRoot` to the project folder. Enable `mod_rewrite` if needed. No `.htaccess` rewrites are required; all routing is done via direct file paths.

## Folder Structure

```
padel_anas/
├── config/
│   └── db.php                 # Database connection singleton
├── database/
│   └── schema.sql             # Full DB schema + seed data
├── includes/
│   └── functions.php          # Auth helpers, sanitization, price calc
├── layouts/
│   ├── header.php             # HTML <head> + <body> open
│   ├── navbar.php             # Responsive navigation bar
│   └── footer.php             # Footer + JS include
├── pages/
│   ├── signin.php
│   ├── signup.php
│   ├── logout.php
│   ├── dashboard.php          # User dashboard
│   └── reservation.php        # Booking form
├── admin/
│   ├── index.php              # Admin dashboard
│   ├── reservations.php       # Manage all reservations
│   ├── items.php              # Manage categories & item types
│   └── users.php              # Manage users
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── images/
├── uploads/
├── index.php                  # Public landing page
└── README.md
```

## Price Formula

```
Total = (Pitch price/hr × hours) + (Ball unit price × qty) + (Racket unit price × qty)
```

The price updates live in the browser as users fill out the booking form.
