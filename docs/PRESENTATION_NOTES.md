# Class presentation notes

**Date:** November 19, 2026  
**Project:** Panel and Page Comic Shop Manager

## Short demonstration

1. Introduce the app as a comic shop inventory and order manager, with example Marvel and DC editions.
2. Show the seven database tables and explain how publishers, creators, genres, customers, comics, orders, and order_items relate.
3. Open the dashboard and point out catalog count, copies on hand, open orders, and low-stock editions.
4. Add or edit a comic edition. Show its series, issue range, publisher, creator, genre, format, grade, price, and stock fields.
5. Use Postman to create an order with comic IDs. Show the stock deduction, then cancel the order and show the stock restored.
6. Close by describing the PHP/PDO API, MySQL foreign keys, and GitHub setup instructions.

## Questions to prepare for

- Why are publishers, creators, and genres separate tables? This reduces repeated values and provides consistent references for comic records.
- Why does each comic record have a lead creator instead of all the credited creators? The class version keeps the model manageable. A comic_creators junction table could record writers, artists, inkers, and colorists.
- Why does order_items have a two-column key? It allows a comic edition once per order while letting one order contain multiple editions.
- Why save unit_price on an order item? It preserves the amount charged when the order was placed even if the catalog price later changes.
- How does the API reduce SQL injection risk? It uses prepared statements and a fixed list of resources and fields.
- What happens if an order requests more copies than are in stock? The API rejects the order and rolls the database transaction back.

## Live setup

Use XAMPP with Apache and MySQL running, import database/schema.sql through phpMyAdmin, and open http://localhost/bookstore-management-system/. The complete recreation steps and sample Postman requests are in README.md.
