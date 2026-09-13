# Kamal Car Wash

Kamal Car Wash is a PHP/MySQL web application for managing car-wash bookings and day-to-day staff operations. It provides a public landing page, customer registration and booking flows, a staff portal, and additional administration pages for authorized staff.

## Features

### Customer portal

- Register a customer account together with vehicle details
- Sign in and manage customer information
- Manage registered vehicles
- Browse car-wash packages
- Check available booking slots and create reservations
- View booking, payment, receipt, and profile-related pages
- Change the customer password and contact the business

### Staff portal

- Staff login and dashboard
- View and search reservations
- Record attendance
- Submit overtime requests
- Submit leave requests
- Submit salary-advance requests
- View salary information
- Update staff profile and password

### Administration

Owner, Manager, and Supervisor accounts can access staff administration features, including:

- Add, edit, and manage staff records
- Manage leave requests
- Manage overtime requests
- Manage salary advances

## Technology

- PHP
- MySQL or MariaDB
- PDO for database access
- HTML, CSS, and JavaScript
- SweetAlert2 and Notiflix are referenced by some pages through CDN or local assets

## Requirements

- PHP 8.2 or later recommended
- MySQL or MariaDB
- A PHP web server such as Apache, XAMPP, or Laragon
- PHP PDO MySQL extension enabled

## Local setup

1. Clone or copy the repository into the web server document root.
2. Create a database named `carwash`.
3. Import [`carwash.sql`](carwash.sql) into that database using phpMyAdmin or the MySQL client.
4. Update the database credentials in [`config.php`](config.php):

	```php
	$host = "localhost";
	$dbname = "carwash";
	$username = "root";
	$password = "";
	```

5. Start the web server and MySQL/MariaDB service.
6. Open `http://localhost/Kamal-Car-Wash/` in a browser, adjusting the URL to match the directory name used by the web server.

## Main entry points

- `index.php` - public landing page
- `auth/cust_register.php` - customer registration
- `auth/cust_login.php` - customer login
- `auth/staff_login.php` - staff login
- `customer/pages/cust_home.php` - customer dashboard
- `customer/pages/booking.php` - customer booking page
- `staff/staff_home.php` - staff dashboard
- `staff/pages/reservation.php` - reservation management
- `staff/admin/staff_management.php` - privileged staff management

## Project structure

```text
.
├── auth/                  Authentication and registration pages
├── customer/              Customer pages and shared includes
├── staff/                 Staff pages, administration, and shared includes
├── css/                   Shared stylesheets
├── images/                Logos, backgrounds, and uploaded images
├── config.php             PDO database connection
├── carwash.sql            Database export
└── index.php              Public home page
```

## Database

The supplied dump defines the core entities used by the application, including customers, staff, vehicles, packages, services, receipts, and payments. The PHP pages also reference operational tables and columns for features such as authentication, booking status, attendance, leave, overtime, and salary advances.

Before deploying, compare the current PHP queries with [`carwash.sql`](carwash.sql) and apply any required migrations. The dump is a phpMyAdmin export and may not include every table or column referenced by the current application code.

## Notes

- The application currently uses development-style database defaults in `config.php`; replace them with environment-specific credentials before deployment.
- Several pages assume that sessions are available and that the corresponding customer or staff record exists.
- Uploaded customer and staff images are stored beneath `images/uploads/`.
- This repository does not currently include automated tests or a dependency manifest.