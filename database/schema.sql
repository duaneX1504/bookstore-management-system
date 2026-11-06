CREATE DATABASE IF NOT EXISTS bookstore_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookstore_db;

CREATE TABLE IF NOT EXISTS authors (
  author_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS categories (
  category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS books (
  book_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  isbn VARCHAR(20) NOT NULL UNIQUE,
  title VARCHAR(180) NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  publisher VARCHAR(120) NULL,
  price DECIMAL(8,2) NOT NULL,
  stock_qty INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_book_price CHECK (price >= 0),
  CONSTRAINT fk_books_author FOREIGN KEY (author_id)
    REFERENCES authors(author_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_books_category FOREIGN KEY (category_id)
    REFERENCES categories(category_id) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS customers (
  customer_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(25) NULL
);

CREATE TABLE IF NOT EXISTS orders (
  order_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('Pending','Processing','Shipped','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id)
    REFERENCES customers(customer_id) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS order_items (
  order_id INT UNSIGNED NOT NULL,
  book_id INT UNSIGNED NOT NULL,
  quantity SMALLINT UNSIGNED NOT NULL,
  unit_price DECIMAL(8,2) NOT NULL,
  PRIMARY KEY (order_id, book_id),
  CONSTRAINT chk_order_item_quantity CHECK (quantity > 0),
  CONSTRAINT fk_items_order FOREIGN KEY (order_id)
    REFERENCES orders(order_id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_items_book FOREIGN KEY (book_id)
    REFERENCES books(book_id) ON UPDATE CASCADE ON DELETE RESTRICT
);

INSERT IGNORE INTO authors (author_id, name, email) VALUES
  (1, 'Octavia E. Butler', 'octavia@example.com'),
  (2, 'James Clear', 'james@example.com'),
  (3, 'Toni Morrison', 'toni@example.com');

INSERT IGNORE INTO categories (category_id, category_name) VALUES
  (1, 'Science Fiction'), (2, 'Personal Development'), (3, 'Literary Fiction');

INSERT IGNORE INTO books (book_id, isbn, title, author_id, category_id, publisher, price, stock_qty) VALUES
  (1, '9780446675505', 'Kindred', 1, 1, 'Beacon Press', 16.99, 14),
  (2, '9780735211292', 'Atomic Habits', 2, 2, 'Avery', 21.50, 22),
  (3, '9781400033416', 'Beloved', 3, 3, 'Vintage', 15.00, 8),
  (4, '9780807083697', 'Parable of the Sower', 1, 1, 'Grand Central', 17.25, 6);

INSERT IGNORE INTO customers (customer_id, full_name, email, phone) VALUES
  (1, 'Morgan Reed', 'morgan.reed@example.com', '555-0101'),
  (2, 'Avery Johnson', 'avery.johnson@example.com', '555-0102');

INSERT IGNORE INTO orders (order_id, customer_id, status) VALUES
  (1, 1, 'Processing'), (2, 2, 'Pending');

INSERT IGNORE INTO order_items (order_id, book_id, quantity, unit_price) VALUES
  (1, 1, 1, 16.99), (1, 2, 1, 21.50), (2, 3, 2, 15.00);
