# CMS375-GroupProject
## MySQL setup

1. Start MySQL locally.
2. Import the schema and seed data:
   ```
   mysql -u root -p < schema.sql
   ```
3. If needed, update the database connection settings in `/Users/escott/Desktop/CMS375-GroupProject/db.php`.
4. Start the PHP server:
   ```
   php -S localhost:8000
   ```
5. Open `http://localhost:8000/index.php`.

## login accounts

- Staff: `staff@harvestbistro.test`
- Admin: `admin@harvestbistro.test`
- Password: `password123`

