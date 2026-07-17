-- ============================================================
-- ShopZone Database Schema
-- PHP 8+ / MySQL 8+ compatible
-- Run this file once to set up the database
-- ============================================================

CREATE DATABASE IF NOT EXISTS shopzone
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE shopzone;

-- ============================================================
-- Table: categories
-- Stores product categories (e.g. Footwear, Electronics)
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100) NOT NULL,
    slug     VARCHAR(110) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: products
-- Core product catalogue
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id  INT UNSIGNED NOT NULL,
    name         VARCHAR(200) NOT NULL,
    slug         VARCHAR(220) NOT NULL UNIQUE,
    description  TEXT,
    price        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    old_price    DECIMAL(10,2) DEFAULT NULL,  -- NULL = no strikethrough
    badge        ENUM('none','sale','new','hot') DEFAULT 'none',
    image        VARCHAR(300) DEFAULT NULL,   -- path relative to /uploads/
    stock        INT UNSIGNED DEFAULT 100,
    is_featured  TINYINT(1) DEFAULT 0,        -- shown on homepage
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id)
        REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- Table: admins
-- Admin panel users (passwords stored as bcrypt hashes)
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(80) NOT NULL UNIQUE,
    email        VARCHAR(180) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,  -- bcrypt hash
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: contacts
-- Stores submitted contact form messages
-- ============================================================
CREATE TABLE IF NOT EXISTS contacts (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    email      VARCHAR(180) NOT NULL,
    subject    VARCHAR(250) NOT NULL,
    order_ref  VARCHAR(50) DEFAULT NULL,
    message    TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Seed: categories
-- ============================================================
INSERT IGNORE INTO categories (name, slug) VALUES
    ('Fashion',     'fashion'),
    ('Electronics', 'electronics'),
    ('Footwear',    'footwear'),
    ('Accessories', 'accessories'),
    ('Bags',        'bags'),
    ('Eyewear',     'eyewear'),
    ('Clothing',    'clothing');

-- ============================================================
-- Seed: products (demo data — images from Pexels)
-- ============================================================
INSERT IGNORE INTO products
    (category_id, name, slug, description, price, old_price, badge, image, is_featured)
VALUES
(3, 'Urban Runner Sneakers',
 'urban-runner-sneakers',
 'Lightweight and breathable everyday sneakers built for city life. Cushioned sole, mesh upper, available in multiple colourways.',
 79.99, 99.99, 'sale',
 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=400',
 1),

(4, 'Classic Chronograph Watch',
 'classic-chronograph-watch',
 'Stainless steel chronograph watch with sapphire-coated glass, 50m water resistance and a genuine leather strap.',
 149.99, NULL, 'new',
 'https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400',
 1),

(2, 'Pro Wireless Headphones',
 'pro-wireless-headphones',
 'Studio-quality sound in an over-ear design. Active noise cancellation, 30-hour battery, foldable frame.',
 199.99, 249.99, 'hot',
 'https://images.pexels.com/photos/2529148/pexels-photo-2529148.jpeg?auto=compress&cs=tinysrgb&w=400',
 1),

(5, 'Premium Leather Tote Bag',
 'premium-leather-tote-bag',
 'Full-grain leather tote with a spacious interior, magnetic closure and detachable shoulder strap.',
 119.99, NULL, 'none',
 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=400&h=300&fit=crop&crop=right',
 1),

(6, 'Polarized Aviator Sunglasses',
 'polarized-aviator-sunglasses',
 'UV400 polarized lenses in a classic metal aviator frame. Reduces glare, lightweight, unisex fit.',
 54.99, 79.99, 'sale',
 'https://images.pexels.com/photos/3394650/pexels-photo-3394650.jpeg?auto=compress&cs=tinysrgb&w=400',
 1),

(7, 'Classic Bomber Jacket',
 'classic-bomber-jacket',
 'Slim-fit bomber in premium satin-finish fabric. Ribbed cuffs, zip closure, inner phone pocket.',
 89.99, NULL, 'new',
 'https://images.pexels.com/photos/996329/pexels-photo-996329.jpeg?auto=compress&cs=tinysrgb&w=400',
 1),

(1, 'Summer Floral Dress',
 'summer-floral-dress',
 'Lightweight midi dress in a vibrant floral print. V-neckline, wrap silhouette, fully lined.',
 64.99, NULL, 'none',
 'https://images.pexels.com/photos/1536619/pexels-photo-1536619.jpeg?auto=compress&cs=tinysrgb&w=400',
 0),

(2, 'Smart Fitness Tracker',
 'smart-fitness-tracker',
 'Track steps, heart rate, sleep and workouts. 7-day battery, swimproof, compatible with iOS & Android.',
 89.99, 119.99, 'sale',
 'https://images.pexels.com/photos/356056/pexels-photo-356056.jpeg?auto=compress&cs=tinysrgb&w=400',
 0);

-- ============================================================
-- Seed: default admin account
-- Username : admin
-- Password : admin123  (bcrypt hash below — change in production!)
-- ============================================================
INSERT IGNORE INTO admins (username, email, password) VALUES (
    'admin',
    'admin@shopzone.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    -- Hash of "password" — replace with your own via password_hash()
);
