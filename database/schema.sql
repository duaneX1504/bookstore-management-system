CREATE DATABASE IF NOT EXISTS comic_store_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE comic_store_db;

CREATE TABLE IF NOT EXISTS publishers (
  publisher_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  publisher_name VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS creators (
  creator_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  creator_name VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS genres (
  genre_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  genre_name VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS comics (
  comic_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  catalog_code VARCHAR(30) NOT NULL UNIQUE,
  title VARCHAR(180) NOT NULL,
  series_title VARCHAR(140) NOT NULL,
  issue_number VARCHAR(30) NOT NULL,
  publisher_id INT UNSIGNED NOT NULL,
  creator_id INT UNSIGNED NOT NULL,
  genre_id INT UNSIGNED NOT NULL,
  format ENUM('Single Issue','Trade Paperback','Graphic Novel','Hardcover') NOT NULL,
  condition_grade ENUM('NM','VF','FN','VG','G') NOT NULL DEFAULT 'VF',
  price DECIMAL(8,2) NOT NULL,
  stock_qty INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_comic_price CHECK (price >= 0),
  CONSTRAINT fk_comics_publisher FOREIGN KEY (publisher_id)
    REFERENCES publishers(publisher_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_comics_creator FOREIGN KEY (creator_id)
    REFERENCES creators(creator_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_comics_genre FOREIGN KEY (genre_id)
    REFERENCES genres(genre_id) ON UPDATE CASCADE ON DELETE RESTRICT
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
  comic_id INT UNSIGNED NOT NULL,
  quantity SMALLINT UNSIGNED NOT NULL,
  unit_price DECIMAL(8,2) NOT NULL,
  PRIMARY KEY (order_id, comic_id),
  CONSTRAINT chk_order_item_quantity CHECK (quantity > 0),
  CONSTRAINT fk_items_order FOREIGN KEY (order_id)
    REFERENCES orders(order_id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_items_comic FOREIGN KEY (comic_id)
    REFERENCES comics(comic_id) ON UPDATE CASCADE ON DELETE RESTRICT
);

INSERT IGNORE INTO publishers (publisher_id, publisher_name) VALUES
  (1, 'Marvel Comics'), (2, 'DC Comics');

INSERT IGNORE INTO creators (creator_id, creator_name) VALUES
  (1, 'Stan Lee'),
  (2, 'Chris Claremont'),
  (3, 'Ta-Nehisi Coates'),
  (4, 'Frank Miller'),
  (5, 'Alan Moore'),
  (6, 'Mark Millar'),
  (7, 'Greg Rucka'),
  (8, 'Brian Michael Bendis');

INSERT IGNORE INTO genres (genre_id, genre_name) VALUES
  (1, 'Superhero'),
  (2, 'Team Adventure'),
  (3, 'Science Fiction'),
  (4, 'Crime and Noir'),
  (5, 'Fantasy');

-- Comic titles below are real Marvel and DC publications. Catalog codes are fictional
-- identifiers for this sample store and are not publisher-issued UPCs.
INSERT IGNORE INTO comics
  (comic_id, catalog_code, title, series_title, issue_number, publisher_id, creator_id, genre_id, format, condition_grade, price, stock_qty)
VALUES
  (1, 'MAR-SPM-001', 'The Amazing Spider-Man', 'The Amazing Spider-Man', '1', 1, 1, 1, 'Single Issue', 'VF', 5.99, 17),
  (2, 'MAR-XM-DFP', 'X-Men: Days of Future Past', 'Uncanny X-Men', '141–142', 1, 2, 3, 'Trade Paperback', 'NM', 19.99, 8),
  (3, 'MAR-BP-NOF', 'Black Panther: A Nation Under Our Feet', 'Black Panther', 'Book 1', 1, 3, 1, 'Trade Paperback', 'VF', 17.99, 7),
  (4, 'MAR-DD-BA', 'Daredevil: Born Again', 'Daredevil', '227–233', 1, 4, 4, 'Trade Paperback', 'VG', 16.99, 5),
  (5, 'DC-BAT-Y1', 'Batman: Year One', 'Batman', '404–407', 2, 4, 4, 'Trade Paperback', 'VF', 14.99, 12),
  (6, 'DC-WM-001', 'Watchmen', 'Watchmen', '1–12', 2, 5, 3, 'Graphic Novel', 'NM', 19.99, 5),
  (7, 'DC-SUP-RS', 'Superman: Red Son', 'Superman: Red Son', '1–3', 2, 6, 1, 'Trade Paperback', 'NM', 16.99, 11),
  (8, 'DC-WW-Y1', 'Wonder Woman: Year One', 'Wonder Woman', '2–14', 2, 7, 1, 'Trade Paperback', 'VF', 18.99, 4);

INSERT IGNORE INTO customers (customer_id, full_name, email, phone) VALUES
  (1, 'Morgan Reed', 'morgan.reed@example.com', '555-0101'),
  (2, 'Avery Johnson', 'avery.johnson@example.com', '555-0102');

INSERT IGNORE INTO orders (order_id, customer_id, status) VALUES
  (1, 1, 'Processing'), (2, 2, 'Pending');

INSERT IGNORE INTO order_items (order_id, comic_id, quantity, unit_price) VALUES
  (1, 1, 1, 5.99), (1, 2, 1, 19.99), (2, 3, 1, 17.99);
