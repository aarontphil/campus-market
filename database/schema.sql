-- ============================================================
-- Campus Second-Hand Marketplace — Database Schema
-- ============================================================
-- Run this file once against MySQL / MariaDB to create the
-- database, tables, indexes, and demo seed data.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `campus_marketplace`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `campus_marketplace`;

-- ------------------------------------------------------------
-- A. users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `full_name`     VARCHAR(120)    NOT NULL,
  `college_email` VARCHAR(255)    NOT NULL,
  `student_id`    VARCHAR(30)     NOT NULL,
  `password_hash` VARCHAR(255)    NOT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_college_email` (`college_email`),
  UNIQUE KEY `uq_student_id`    (`student_id`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- B. products
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `seller_id`    INT UNSIGNED     NOT NULL,
  `title`        VARCHAR(200)     NOT NULL,
  `description`  TEXT             NOT NULL,
  `price`        DECIMAL(10,2)    NOT NULL,
  `product_data` JSON             NOT NULL,
  `image_path`   VARCHAR(500)     DEFAULT NULL,
  `status`       ENUM('Available','Reserved','Sold','Archived') NOT NULL DEFAULT 'Available',
  `created_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived_at`  DATETIME         DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_seller`  (`seller_id`),
  KEY `idx_status`  (`status`),
  KEY `idx_price`   (`price`),
  CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- C. offers
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `offers` (
  `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `product_id`      INT UNSIGNED   NOT NULL,
  `buyer_id`        INT UNSIGNED   NOT NULL,
  `seller_id`       INT UNSIGNED   NOT NULL,
  `parent_offer_id` INT UNSIGNED   DEFAULT NULL,
  `offered_price`   DECIMAL(10,2)  NOT NULL,
  `message`         TEXT           DEFAULT NULL,
  `status`          ENUM('Pending','Accepted','Rejected','Countered','Withdrawn') NOT NULL DEFAULT 'Pending',
  `created_at`      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product`       (`product_id`),
  KEY `idx_buyer`         (`buyer_id`),
  KEY `idx_seller`        (`seller_id`),
  KEY `idx_parent_offer`  (`parent_offer_id`),
  CONSTRAINT `fk_offers_product` FOREIGN KEY (`product_id`)
    REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_offers_buyer`   FOREIGN KEY (`buyer_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_offers_seller`  FOREIGN KEY (`seller_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_offers_parent`  FOREIGN KEY (`parent_offer_id`)
    REFERENCES `offers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Passwords: all demo accounts use "Password123"
-- password_hash generated with password_hash('Password123', PASSWORD_BCRYPT)
SET @hash = '$2y$10$8KzQbKwKqFvRqKjZ0NJhXOQxLzKjCkJzQFnPxHVqYcHfKjQr9KjZe';

INSERT INTO `users` (`full_name`, `college_email`, `student_id`, `password_hash`) VALUES
  ('Aarav Sharma',   'aarav.sharma@campus.edu',   'STU2024001', @hash),
  ('Priya Patel',    'priya.patel@campus.edu',    'STU2024002', @hash),
  ('Rahul Mehra',    'rahul.mehra@campus.edu',     'STU2024003', @hash);

-- Products — realistic campus items
INSERT INTO `products` (`seller_id`, `title`, `description`, `price`, `product_data`, `image_path`, `status`) VALUES
(1, 'Database Management Systems — Navathe (4th Ed.)',
    'Comprehensive DBMS textbook by Elmasri & Navathe. Some highlighting in chapters 3-7 but otherwise in great shape. Perfect for CSE students.',
    450.00,
    '{"category":"Textbooks and Academic Materials","condition":"Good","specifications":{"subject":"Database Management Systems","edition":"Fourth Edition","language":"English","author":"Elmasri & Navathe"}}',
    NULL, 'Available'),

(1, 'Casio FX-991EX Scientific Calculator',
    'Advanced scientific calculator with spreadsheet, statistics, and matrix functions. Battery still holds charge. Comes with original case.',
    800.00,
    '{"category":"Electronics and Gadgets","condition":"Like New","specifications":{"brand":"Casio","model":"FX-991EX","type":"Scientific Calculator"}}',
    NULL, 'Available'),

(2, 'JBL Tune 510BT Wireless Headphones',
    'Over-ear Bluetooth headphones with 40-hour battery life. Used for one semester. Minor wear on the headband cushion.',
    1200.00,
    '{"category":"Electronics and Gadgets","condition":"Good","specifications":{"brand":"JBL","model":"Tune 510BT","connectivity":"Bluetooth 5.0","battery":"40 hours"}}',
    NULL, 'Available'),

(2, 'Wooden Study Table with Drawer',
    'Compact study table ideal for hostel rooms. Includes a single drawer for stationery. Dimensions: 90cm x 60cm x 75cm.',
    2500.00,
    '{"category":"Furniture","condition":"Fair","specifications":{"material":"Engineered Wood","dimensions":"90x60x75 cm","color":"Walnut"}}',
    NULL, 'Available'),

(3, 'Engineering Drawing Kit — Complete Set',
    'Full drawing set including mini-drafter, compass, set squares, protractor, and French curves. Used for ED course.',
    350.00,
    '{"category":"Textbooks and Academic Materials","condition":"Good","specifications":{"contents":"Mini-drafter, Compass, Set Squares, Protractor, French Curves","brand":"Rotring"}}',
    NULL, 'Available'),

(3, 'Logitech B175 Wireless Mouse',
    'Reliable wireless mouse with 12-month battery life. USB nano receiver included. Great for presentations and labs.',
    400.00,
    '{"category":"Electronics and Gadgets","condition":"Like New","specifications":{"brand":"Logitech","model":"B175","connectivity":"2.4 GHz Wireless","battery":"12 months"}}',
    NULL, 'Available'),

(1, 'Yonex Badminton Racquet — Nanoray 10F',
    'Lightweight badminton racquet suitable for beginners and intermediate players. Comes with a half cover.',
    600.00,
    '{"category":"Sports Equipment","condition":"Good","specifications":{"brand":"Yonex","model":"Nanoray 10F","weight":"Lightweight","skill_level":"Beginner-Intermediate"}}',
    NULL, 'Available'),

(2, 'Electric Kettle — 1.5L',
    'Stainless steel electric kettle, perfect for making tea, coffee, or instant noodles in the hostel. Auto shut-off feature.',
    500.00,
    '{"category":"Hostel Essentials","condition":"Good","specifications":{"capacity":"1.5 Litres","material":"Stainless Steel","power":"1500W","features":"Auto shut-off, Boil-dry protection"}}',
    NULL, 'Available'),

(3, 'Formal White Shirt — Size M',
    'Cotton formal shirt worn only twice for presentations. No stains or wrinkles. Brand: Van Heusen.',
    300.00,
    '{"category":"Clothing and Accessories","condition":"Like New","specifications":{"brand":"Van Heusen","size":"Medium","color":"White","material":"Cotton"}}',
    NULL, 'Available'),

(1, 'USB-C Hub — 7-in-1 Adapter',
    'Multiport USB-C hub with HDMI, USB 3.0 x2, SD card reader, micro-SD, USB-C PD pass-through. Works with all laptops.',
    900.00,
    '{"category":"Electronics and Gadgets","condition":"Like New","specifications":{"brand":"Anker","ports":"HDMI, USB 3.0 x2, SD, microSD, USB-C PD","compatibility":"Universal USB-C"}}',
    NULL, 'Available'),

(2, 'Desk Lamp with Clamp — LED',
    'Adjustable LED desk lamp with flexible arm and desk clamp. Three brightness levels. USB powered.',
    350.00,
    '{"category":"Hostel Essentials","condition":"Good","specifications":{"type":"LED","mounting":"Clamp","brightness_levels":3,"power":"USB"}}',
    NULL, 'Available'),

(3, 'Data Structures and Algorithms Made Easy — Narasimha Karumanchi',
    'Popular DSA book for placement preparation. Some pencil notes in margins, easily erasable.',
    250.00,
    '{"category":"Textbooks and Academic Materials","condition":"Fair","specifications":{"subject":"Data Structures & Algorithms","author":"Narasimha Karumanchi","language":"English"}}',
    NULL, 'Available');

-- Sample offers for demonstration
INSERT INTO `offers` (`product_id`, `buyer_id`, `seller_id`, `offered_price`, `message`, `status`) VALUES
(1, 2, 1, 350.00, 'Hi! Would you accept ₹350 for the DBMS book? I can pick it up today.', 'Pending'),
(3, 1, 2, 900.00, 'I am interested in the JBL headphones. Would ₹900 work?', 'Countered'),
(4, 1, 2, 2000.00, 'Is the study table still available? Can you do ₹2000?', 'Pending');

-- Counteroffer for offer #2
INSERT INTO `offers` (`product_id`, `buyer_id`, `seller_id`, `parent_offer_id`, `offered_price`, `message`, `status`) VALUES
(3, 1, 2, 2, 1050.00, 'How about ₹1050? That is the lowest I can go.', 'Pending');
