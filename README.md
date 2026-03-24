# CMS375-GroupProject

## MySQL setup

1. Start MySQL locally.
2. Import the schema:
   ```bash
   mysql -u root -p < schema.sql
   ```
3. Update the connection settings in `/Users/escott/Desktop/CMS375-GroupProject/reserve.php` if your MySQL username, password, host, port, or database name are different.
4. Start the PHP server:
   ```bash
   php -S localhost:8000
   ```
5. Open `http://localhost:8000/index.php` and submit the reservation form.
