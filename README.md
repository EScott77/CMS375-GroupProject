# Harvest Bistro

Restaurant Reservation and Menu Management System for a CMS375 database project.

## Overview

This project is a PHP + MySQL web application for a restaurant scenario. It supports:

- customer reservation booking
- customer reservation lookup and cancellation
- a full menu page backed by the database
- a staff dashboard for reservation management and dish order entry
- an admin dashboard for analytics, menu management, and staff account management

## Tech Stack

- PHP
- MySQL
- HTML / CSS / JavaScript

## Main Pages

- [index.php](/CMS375-GroupProject/index.php)
  Customer homepage with featured items, reservation form, and reservation lookup
- [menu.php](/CMS375-GroupProject/menu.php)
  Full SQL-backed menu page
- [login.php](/CMS375-GroupProject/login.php)
  Staff/admin login
- [staff.php](/CMS375-GroupProject/staff.php)
  Staff dashboard for reservation management and dish order logging
- [admin.php](/CMS375-GroupProject/admin.php)
  Admin dashboard for analytics, menu editor, and staff account creation

## Database

The app uses MySQL with connection settings in [db.php](/CMS375-GroupProject/db.php):

- host: `127.0.0.1`
- port: `3306`
- database: `restaurant_db`
- user: `root`

Core tables defined in [schema.sql](/CMS375-GroupProject/schema.sql):

- `customers`
- `restaurant_tables`
- `reservations`
- `menu_items`
- `staff_accounts`
- `menu_item_orders`

## Setup

1. Start MySQL.
2. Import the schema:

```bash
mysql -u root -p < schema.sql
```

3. Start the PHP development server from the project folder:

```bash
php -S localhost:8000
```

4. Open the site:

- [http://localhost:8000/index.php](http://localhost:8000/index.php)

## Login Accounts

Seeded accounts:

- Staff: `staff@harvestbistro.test`
- Admin: `admin@harvestbistro.test`
- Password: `password123`

## CRUD Features

The project includes insert, update, delete, and retrieve operations.

Examples:

- Insert
  - create reservations in [reserve.php](/CMS375-GroupProject/reserve.php)
  - record dish orders in [staff_actions.php](/CMS375-GroupProject/staff_actions.php)
  - create staff accounts in [staff_actions.php](/CMS375-GroupProject/staff_actions.php)
- Update
  - confirm/cancel reservations and assign tables in [staff_actions.php](CMS375-GroupProject/staff_actions.php)
  - edit menu items in [staff_actions.php](/CMS375-GroupProject/staff_actions.php)
- Delete
  - delete menu items in [staff_actions.php](CMS375-GroupProject/staff_actions.php)
- Retrieve
  - menu display in [index.php](CMS375-GroupProject/index.php) and [menu.php](/CMS375-GroupProject/menu.php)
  - reservation lookup in [index.php](CMS375-GroupProject/index.php)
  - analytics in [admin.php](/CMS375-GroupProject/admin.php)

## Security Notes

- Prepared statements are used for database writes and login validation.
- Passwords are hashed with `password_hash(...)`.
- Passwords are verified with `password_verify(...)`.
- Plain-text passwords are not stored in the database.

## Important Team Note

[schema.sql](CMS375-GroupProject/schema.sql) currently drops and recreates tables before inserting seed data.

That means:

- running `mysql -u root -p < schema.sql` resets the database
- reservations, order logs, and menu changes made through the website will be erased if the schema is re-imported

For day-to-day use:

- import `schema.sql` only when you want to reset the project database
- otherwise, just start MySQL and run:

```bash
php -S localhost:8000
```


Website changes to MySQL are not tracked by Git automatically. If the team wants shared sample data, export it separately and commit a seed SQL file.



## Project Goal

This project demonstrates a meaningful enterprise-style database application for restaurant operations, including normalized relational data, CRUD functionality, and role-based workflows for customers, staff, and admins.
