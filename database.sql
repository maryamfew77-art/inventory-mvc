CREATE DATABASE IF NOT EXISTS inventory_mvc CHARACTER SET utf8mb4;
USE inventory_mvc;   -- choose the database first (no "No database selected" error)

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS suppliers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(20) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NULL,
  supplier_id INT UNSIGNED NULL,
  name VARCHAR(150) NOT NULL,
  sku VARCHAR(50) NOT NULL UNIQUE,                 -- stock code, never repeated
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  quantity INT UNSIGNED NOT NULL DEFAULT 0,
  reorder_level INT UNSIGNED NOT NULL DEFAULT 5,   -- at or below this = "low stock"
  photo VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demo logins:  admin@example.com / Admin@12345   and   user@example.com / User@12345
INSERT INTO users (name, email, password, role) VALUES
('Admin',     'admin@example.com', '$2y$10$yKNpSiwUazjX31xH8m1qXuZDzEFOZoCWnybDJkv8omsAA4/lrQbAC', 'admin'),
('Demo User', 'user@example.com',  '$2y$10$VSDObPew472lOKAuZqwq.OybZWfV2IH1afhvZBaq1.t/R6voH/7i.', 'user');

INSERT INTO categories (name) VALUES ('Electronics'), ('Stationery'), ('Groceries'), ('Furniture');

INSERT INTO suppliers (name, phone) VALUES
('Alpha Traders', '03001234567'), ('City Wholesale', '03007654321'), ('Prime Supplies', '03111234567');

INSERT INTO products (category_id, supplier_id, name, sku, price, quantity, reorder_level) VALUES
(1, 1, 'Wireless Mouse',      'EL-001',  1500.00, 25, 10),
(1, 1, 'USB Keyboard',        'EL-002',  2200.00,  8, 10),
(1, 2, 'LED Monitor 22 inch', 'EL-003', 28500.00,  5,  3),
(1, 2, 'Desk Lamp',           'EL-004',  1800.00, 14,  5),
(2, 3, 'A4 Paper Ream',       'ST-001',  1100.00, 60, 20),
(2, 3, 'Ball Pen Box',        'ST-002',   350.00,  4, 15),
(2, 3, 'Notebook Pack',       'ST-003',   600.00, 40, 15),
(2, 3, 'Whiteboard Marker',   'ST-004',   120.00,  0, 10),
(3, 2, 'Basmati Rice 5kg',    'GR-001',  2300.00, 18, 10),
(3, 2, 'Cooking Oil 5L',      'GR-002',  3400.00,  2,  6),
(4, 1, 'Office Chair',        'FU-001', 12500.00,  7,  3),
(4, 1, 'Study Table',         'FU-002', 15500.00,  3,  3);