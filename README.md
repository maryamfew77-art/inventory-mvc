# StockPilot: Inventory / Stock Manager

A web application for managing products, categories and stock levels, built from scratch with **PHP 8 (OOP and MVC)** and **MySQL**. It is the Final Project of the Tech SG Studio (SMC-Private) Limited web development internship.

## Features

| Area | What it does |
|---|---|
| MVC | Front controller, Router, Controllers, Models, Views, autoloader, clean URLs (`/products/edit/5`) |
| Authentication | Register, login, logout, `password_hash`, `session_regenerate_id`, login attempt limit |
| Roles | `admin` and `user`, checked on the server |
| Products (main CRUD) | Add, edit, delete, search, pagination, validation, flash messages, low stock badge |
| Categories (related table) | Full CRUD (admin only), linked to products with a foreign key |
| Suppliers | Linked to products with a foreign key (dropdown) |
| Photo upload | JPG, PNG or WEBP only, real file type check, 2 MB limit, random file name, PHP blocked in the uploads folder |
| Dashboard | 6 stat cards and a "Needs restocking" table |
| REST API | List, single product and create, protected by an API key |

## Roles

| Action | Admin | User |
|---|---|---|
| View dashboard, products, categories | Yes | Yes |
| Add and edit products | Yes | Yes |
| Delete products | Yes | No |
| Add, edit and delete categories | Yes | No |

## Setup (XAMPP)

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy this project folder into `C:\xampp\htdocs\` (any folder name works).
3. Open `http://localhost/phpmyadmin`, click the **SQL** tab (or **Import**), and run `database.sql`. It creates the `inventory_mvc` database, the tables and demo data.
4. In the `config` folder, copy `config.example.php` to `config.php`. Change `API_KEY` to your own secret. If MySQL uses another port, change `DB_PORT`.
5. Open `http://localhost/<your-folder-name>/public/`

If a page other than the login page shows "Not Found", make sure Apache's `mod_rewrite` is enabled and `AllowOverride All` is set for `htdocs`. This is the default in XAMPP.

## Demo logins

| Role | Email | Password |
|---|---|---|
| Admin | `admin@example.com` | `Admin@12345` |
| User | `user@example.com` | `User@12345` |

## REST API

Every request needs the header `X-API-KEY: <the API_KEY from config.php>`.

| Method | Address | Meaning | Success |
|---|---|---|---|
| GET | `/api/products` | List products (`?q=pen&page=2`) | 200 |
| GET | `/api/products/{id}` | One product | 200 |
| POST | `/api/products` | Create a product (JSON body) | 201 |

Other responses: `401` (missing or wrong key), `400` (invalid JSON), `404` (product not found), `405` (method not allowed), `422` (validation errors).

Example:

```
curl -H "X-API-KEY: your-key" http://localhost/<your-folder-name>/public/api/products/1
```

POST body:

```json
{ "name": "Power Bank", "sku": "EL-050", "price": 3500, "quantity": 12, "reorder_level": 5, "category_id": 1, "supplier_id": 1 }
```

## Security

- All database queries use PDO prepared statements
- Every output is escaped with `htmlspecialchars` (XSS)
- CSRF token on every POST form
- Passwords stored with `password_hash`, session ID renewed on login, login attempt limit
- Role checks on the server, not only in the menu
- Delete actions work only through POST, with a CSRF token and an admin role
- Uploads: error code, size limit, real MIME type check, random file name, PHP execution blocked in `public/uploads`
- API protected by a key sent in a header
- `APP_DEBUG` is `false` by default, and real settings (`config.php`) are kept out of GitHub

## Project structure

```
 Screenshots

| Dashboard | Products |
|---|---|
| <a href="https://github.com/user-attachments/assets/101816a9-515d-477d-b83c-f963b2fc41ee"><img src="https://github.com/user-attachments/assets/101816a9-515d-477d-b83c-f963b2fc41ee" width="400" alt="Dashboard"></a> | <a href="https://github.com/user-attachments/assets/375b4606-3eeb-42be-a31f-31cb12d842e4"><img src="https://github.com/user-attachments/assets/375b4606-3eeb-42be-a31f-31cb12d842e4" width="400" alt="Products"></a> |
| **Validation** | **Photo Upload** |
| <a href="https://github.com/user-attachments/assets/c2f5c468-7a5b-4c5c-b279-a4040af9775f"><img src="https://github.com/user-attachments/assets/c2f5c468-7a5b-4c5c-b279-a4040af9775f" width="400" alt="Validation"></a> | <a href="https://github.com/user-attachments/assets/3061ae47-144f-4386-b387-e96bd0db31ac"><img src="https://github.com/user-attachments/assets/3061ae47-144f-4386-b387-e96bd0db31ac" width="400" alt="Photo upload"></a> |
| **Categories** | **User Role** |
| <a href="https://github.com/user-attachments/assets/b657fc56-20e3-42fe-b94e-40f3529e4dd7"><img src="https://github.com/user-attachments/assets/b657fc56-20e3-42fe-b94e-40f3529e4dd7" width="400" alt="Categories"></a> | <a href="https://github.com/user-attachments/assets/b3c833b3-2334-4bb2-aace-85238c36ca88"><img src="https://github.com/user-attachments/assets/b3c833b3-2334-4bb2-aace-85238c36ca88" width="400" alt="User role"></a> |
| **API 200** | **API 201** |
| <a href="https://github.com/user-attachments/assets/8e72c475-faeb-4e0a-bbe0-b160b832e390"><img src="https://github.com/user-attachments/assets/8e72c475-faeb-4e0a-bbe0-b160b832e390" width="400" alt="API 200"></a> | <a href="https://github.com/user-attachments/assets/2d9cab64-8faa-4154-a96a-6956e37a05a3"><img src="https://github.com/user-attachments/assets/2d9cab64-8faa-4154-a96a-6956e37a05a3" width="400" alt="API 201"></a> |p/
  Core/         Router, Controller, Database, Model, Uploader, helpers
  Controllers/  Auth, Dashboard, Products, Categories, Api
  Models/       User, Product, Category, Supplier
  Views/        layout, auth, dashboard, products, categories
config/         config.example.php
public/         index.php, .htaccess, assets, uploads
database.sql    tables, demo data, demo logins
```

## Going live (summary)

Upload the project, create a MySQL database with a **non-root user** and a strong password, import `database.sql`, copy `config.example.php` to `config.php` with the live details, keep `APP_DEBUG` as `false`, point the domain's document root to the `public` folder, and enable HTTPS.

## Author

Maryam | Tech SG Studio Internship, Week 4 Final Project
