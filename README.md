# Panel & Page Comic Shop Manager

**Project type:** Relational database application  
**Author:** Duane Harris  
**Technology:** PHP 8, PDO, MySQL 8, HTML, CSS, JavaScript  
**Local environment:** XAMPP  
**Repository:** https://github.com/duaneX1504/bookstore-management-system

Panel & Page is a small comic shop inventory and order manager. It tracks comics by series and issue range, publisher, lead creator, genre, format, condition grade, price, and stock. Its dashboard supports comic create, read, update, and delete actions, search and publisher filtering, CSV export, and order status updates. REST-style JSON endpoints connect the interface to MySQL.

The sample inventory uses actual Marvel and DC comic titles. Marvel's official catalog lists [The Amazing Spider-Man](https://www.marvel.com/comics/series/32866/the_amazing_spider-man) and [X-Men: Days of Future Past](https://www.marvel.com/comics/collection/41778/x-men_days_of_future_past_tpb_new_printing_trade_paperback). DC publishes [Batman: Year One](https://www.dc.com/graphic-novels/batman-year-one-1987/absolute-batman-year-one) and [Watchmen](https://www.dc.com/graphic-novels/watchmen-1986/watchmen). Store prices, inventory quantities, condition grades, and catalog_code values are fictional practice data. A catalog code is an in-store identifier, not a publisher UPC.

## Features

- Manage comic editions, including title, series, issue range, publisher, lead creator, genre, format, condition, price, and stock.
- Store publishers, creators, genres, customers, comics, orders, and order lines in related tables.
- Create orders containing multiple comics. The API checks stock, stores the order and line items in a transaction, and reduces stock.
- Cancel an order to restore the copies reserved for it.
- Search by comic title, series, issue range, creator, or catalog code; filter by publisher.
- Export the comic catalog as CSV.
- Use prepared statements, input validation, JSON responses, HTTP status codes, and relational foreign-key constraints.

## Database design

The schema has seven tables. comics references publishers, creators, and genres. orders references customers. order_items connects orders to comics with a composite primary key (order_id, comic_id), which prevents duplicate lines for the same edition in one order. Each order line stores quantity and the unit price charged at the time of sale.

Each comic record identifies one lead creator for this compact class project. A larger store could use a comic_creators junction table to record multiple writers, artists, inkers, and colorists per edition. See screenshots/database-erd.png for the relational diagram.

## Requirements

- XAMPP with Apache, PHP 8 or newer, and MySQL/MariaDB
- A browser
- Postman or another HTTP client for API demonstrations

## Recreate the project in XAMPP

1. Copy the bookstore-management-system folder into C:\xampp\htdocs\.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open http://localhost/phpmyadmin.
4. Select **Import**, choose database/schema.sql, then select **Import**. This creates and seeds comic_store_db.
5. Copy config.example.php to config.php. The defaults work with a standard local XAMPP installation (127.0.0.1, database comic_store_db, user root, blank password). If your MySQL password differs, update config.php.
6. Open http://localhost/bookstore-management-system/.

The SQL file supplies sample Marvel and DC editions, creators, customers, and orders, so the dashboard has data on first launch. This project uses its own comic_store_db database and does not modify bookstore_db or university_practice.

## API reference

Base URL: http://localhost/bookstore-management-system/api/api.php

Pass one of these resource names in the query string: comics, publishers, creators, genres, customers, or orders.

| Operation | HTTP request | Result |
| --- | --- | --- |
| List comics | GET ?resource=comics | Comic editions with publisher, creator, and genre names |
| Read one comic | GET ?resource=comics&id=1 | One comic or 404 |
| Add a comic | POST ?resource=comics | Creates a comic and returns 201 |
| Update a comic | PUT ?resource=comics&id=1 | Updates comic fields |
| Delete a comic | DELETE ?resource=comics&id=1 | Deletes an edition when no order line refers to it |
| List orders | GET ?resource=orders | Orders with customer, totals, and comic line items |
| Create an order | POST ?resource=orders | Saves an order and deducts stock |
| Update order status | PUT ?resource=orders&id=1 | Changes order status |
| Delete an order | DELETE ?resource=orders&id=1 | Deletes an order and restores reserved stock |

Example create-comic JSON:

    {
      "catalog_code": "DC-BAT-Y1",
      "title": "Batman: Year One",
      "series_title": "Batman",
      "issue_number": "404–407",
      "publisher_id": 2,
      "creator_id": 4,
      "genre_id": 4,
      "format": "Trade Paperback",
      "condition_grade": "VF",
      "price": 14.99,
      "stock_qty": 12
    }

Example create-order JSON:

    {
      "customer_id": 1,
      "items": [
        { "comic_id": 1, "quantity": 2 },
        { "comic_id": 5, "quantity": 1 }
      ]
    }

The API returns JSON. Typical responses include 200 for successful reads or updates, 201 for a successful create, 400 for malformed requests, 404 for missing records, 409 for a duplicate or related-record conflict, and 422 for invalid values.

## Postman demonstration

1. Send a **GET** request to http://localhost/bookstore-management-system/api/api.php?resource=comics and show the Marvel and DC records.
2. Send a **POST** request to the same URL. Choose **Body → raw → JSON**, paste the create-comic example, and show the 201 response.
3. Use **PUT** with ?resource=comics&id=9 and a complete comic record to change its price or stock.
4. Use **DELETE** with ?resource=comics&id=9 to remove the temporary test record.
5. POST the example order to ?resource=orders, GET ?resource=comics and point out the reduced stock, then change the order status to Cancelled with PUT ?resource=orders&id=<returned id> and show that stock is restored.

## Project structure

The source is organized into api (PHP JSON API and database connection), assets (dashboard JavaScript and CSS), database/schema.sql (schema and sample inventory), docs (report and presentation), screenshots (dashboard, comic form, and ER diagram), config.example.php (local database settings), and index.php (comic shop dashboard).

## Scope

This classroom demonstration does not include logins, staff permissions, payment processing, shipping-provider integration, barcode scanning, or public hosting. The sample stock, grades, and store prices are fictional. The comic titles are real, and the schema can be extended to track multiple credited creators and publisher issue identifiers.
