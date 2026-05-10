# AG203 eCommerce Web Application

A full-stack eCommerce web application built with PHP, MySQL, and vanilla JavaScript. Features user authentication, product catalogue, and persistent shopping cart.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)

## Features

- **Product Catalogue** — Browse products from database with images, prices, stock
- **User Authentication** — Register & login with bcrypt password hashing
- **Shopping Cart** — Add/remove/update items, persistent in database
- **Personalized Experience** — Welcome message, cart access for logged-in users
- **Responsive Design** — Mobile-first CSS Grid layout

## Security

- Prepared statements (PDO) — SQL injection prevention
- `password_hash()` / `password_verify()` — Bcrypt hashing
- `htmlspecialchars()` — XSS prevention
- Session-based access control
- Input validation (server-side)

## Quick Start

```bash
docker-compose up
```

| Service | URL |
|---------|-----|
| **App** | http://localhost:8080 |
| **phpMyAdmin** | http://localhost:8081 |

## Project Structure

```
├── config/database.php     # PDO connection
├── api/cart.php            # Cart API (add/update/remove)
├── css/styles.css          # Responsive stylesheet
├── js/main.js              # Fetch API for AJAX cart
├── images/                 # Product images
├── sql/
│   ├── schema.sql          # Database schema (3 tables)
│   └── seed.sql            # Sample product data
├── index.php               # Home — product catalogue
├── signup.php              # Registration
├── signin.php              # Login
├── cart.php                # Shopping cart
├── logout.php              # Session destroy
└── docker-compose.yml      # Docker services
```

## Database Schema

- **customers** — id, first_name, last_name, email (unique), password (bcrypt), created_at
- **products** — id, name, description, price, image_path, stock, created_at
- **cart** — id, customer_id (FK), product_id (FK), quantity, created_at

## Author

Achilleas Karatzas — AG203 Web Application Programming, Assignment 2, University of Essex.
