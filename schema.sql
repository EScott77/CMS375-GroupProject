CREATE DATABASE IF NOT EXISTS restaurant_db;
USE restaurant_db;

DROP TABLE IF EXISTS menu_item_orders;
DROP TABLE IF EXISTS reservation_menu_items;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS restaurant_tables;
DROP TABLE IF EXISTS staff_accounts;
DROP TABLE IF EXISTS customers;

CREATE TABLE customers (
  customer_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE restaurant_tables (
  table_id INT AUTO_INCREMENT PRIMARY KEY,
  table_name VARCHAR(50) NOT NULL UNIQUE,
  seats INT NOT NULL,
  location VARCHAR(100) NOT NULL,
  availability_status ENUM('available', 'maintenance') NOT NULL DEFAULT 'available'
);

CREATE TABLE menu_items (
  menu_item_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(8, 2) NOT NULL,
  category VARCHAR(80) NOT NULL,
  availability_status ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE staff_accounts (
  staff_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  role ENUM('staff', 'admin') NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reservations (
  reservation_id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  table_id INT NULL,
  reservation_date DATE NOT NULL,
  reservation_time TIME NOT NULL,
  guests INT NOT NULL,
  status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
  special_request VARCHAR(255) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reservations_customer
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_reservations_table
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(table_id)
    ON DELETE SET NULL
);

CREATE TABLE menu_item_orders (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  menu_item_id INT NOT NULL,
  reservation_id INT NULL,
  quantity INT NOT NULL DEFAULT 1,
  ordered_at DATETIME NOT NULL,
  recorded_by_staff_id INT NULL,
  CONSTRAINT fk_menu_orders_item
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(menu_item_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_menu_orders_reservation
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id)
    ON DELETE SET NULL,
  CONSTRAINT fk_menu_orders_staff
    FOREIGN KEY (recorded_by_staff_id) REFERENCES staff_accounts(staff_id)
    ON DELETE SET NULL
);

INSERT INTO restaurant_tables (table_name, seats, location, availability_status) VALUES
('T1', 2, 'Window', 'available'),
('T2', 2, 'Patio', 'available'),
('T3', 4, 'Main Floor', 'available'),
('T4', 4, 'Main Floor', 'available'),
('T5', 6, 'Booth', 'available'),
('T6', 8, 'Private Room', 'available');

INSERT INTO menu_items (name, description, price, category, availability_status) VALUES
('Harvest Burger', 'Grass-fed beef, smoked cheddar, tomato jam, and fries.', 16.00, 'Main', 'available'),
('Wood-Fired Pizza', 'San Marzano tomato, mozzarella, basil, and chili oil.', 18.00, 'Main', 'available'),
('Spring Salad', 'Citrus greens, goat cheese, candied pecans, and vinaigrette.', 12.00, 'Appetizer', 'available'),
('Braised Pasta', 'Fresh pasta with slow-braised beef ragu and parmesan.', 19.00, 'Main', 'available'),
('Roasted Salmon', 'Lemon dill salmon with fingerling potatoes.', 24.00, 'Entree', 'available'),
('Chocolate Torte', 'Dark chocolate torte with whipped cream and sea salt.', 9.00, 'Dessert', 'available');

INSERT INTO customers (name, email, phone) VALUES
('Avery Johnson', 'avery@example.com', '555-0100'),
('Jordan Lee', 'jordan@example.com', '555-0101');

INSERT INTO staff_accounts (name, email, role, password_hash) VALUES
('Mia Staff', 'staff@harvestbistro.test', 'staff', '$2y$12$LpEMAT83HJsMQgVHFdzIr.jwlO/RPdgOKcm9yglOzMLeyV3qBDpgO'),
('Noah Manager', 'admin@harvestbistro.test', 'admin', '$2y$12$LpEMAT83HJsMQgVHFdzIr.jwlO/RPdgOKcm9yglOzMLeyV3qBDpgO');

INSERT INTO reservations (customer_id, table_id, reservation_date, reservation_time, guests, status, special_request) VALUES
(
  (SELECT customer_id FROM customers WHERE email = 'avery@example.com'),
  (SELECT table_id FROM restaurant_tables WHERE table_name = 'T3'),
  CURDATE(),
  '18:00:00',
  4,
  'confirmed',
  'Birthday table if possible'
),
(
  (SELECT customer_id FROM customers WHERE email = 'jordan@example.com'),
  NULL,
  DATE_ADD(CURDATE(), INTERVAL 1 DAY),
  '19:30:00',
  2,
  'pending',
  ''
);

INSERT INTO menu_item_orders (menu_item_id, reservation_id, quantity, ordered_at, recorded_by_staff_id) VALUES
(
  (SELECT menu_item_id FROM menu_items WHERE name = 'Wood-Fired Pizza' LIMIT 1),
  (SELECT reservation_id FROM reservations WHERE special_request = 'Birthday table if possible' LIMIT 1),
  2,
  CONCAT(CURDATE(), ' 18:20:00'),
  (SELECT staff_id FROM staff_accounts WHERE email = 'staff@harvestbistro.test' LIMIT 1)
),
(
  (SELECT menu_item_id FROM menu_items WHERE name = 'Chocolate Torte' LIMIT 1),
  (SELECT reservation_id FROM reservations WHERE special_request = 'Birthday table if possible' LIMIT 1),
  1,
  CONCAT(CURDATE(), ' 19:10:00'),
  (SELECT staff_id FROM staff_accounts WHERE email = 'staff@harvestbistro.test' LIMIT 1)
),
(
  (SELECT menu_item_id FROM menu_items WHERE name = 'Harvest Burger' LIMIT 1),
  NULL,
  3,
  CONCAT(CURDATE(), ' 12:30:00'),
  (SELECT staff_id FROM staff_accounts WHERE email = 'admin@harvestbistro.test' LIMIT 1)
);
