# Class presentation notes

**Date:** November 19, 2026  
**Project:** Page & Pine Bookstore Manager

## Short demonstration sequence

1. Introduce the system as a bookstore inventory and order manager.
2. Show the six database tables and explain that `order_items` connects orders and books.
3. Open the XAMPP dashboard and point out catalog, on-hand units, open orders, and low-stock titles.
4. Add or edit a book to demonstrate frontend CRUD.
5. Use Postman to create an order and show that stock decreases. Cancel the order and show that stock is restored.
6. Close by explaining the PHP/PDO API, MySQL foreign keys, and GitHub recreation instructions.

## Questions to prepare for

- Why are authors, categories, and customers separate tables? To avoid repeating the same data in every book or order record.
- Why does `order_items` have two key columns? Together they uniquely identify a book line in a given order and implement the many-to-many relationship.
- Why save `unit_price` on an order item? It preserves the price charged even if the catalog price changes later.
- How does the API avoid SQL injection? It uses prepared statements and a fixed set of resource and field names.
- What happens if a customer orders more copies than the store has? The API rejects the order and rolls the transaction back.
