-- Database Schema for Hmola App
-- Run this SQL script to create the database and tables

CREATE DATABASE IF NOT EXISTS hmola_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hmola_db;

-- 1. App Settings (Dynamic Settings)
CREATE TABLE IF NOT EXISTS app_settings (
    `key` VARCHAR(50) PRIMARY KEY,
    `value` TEXT NOT NULL,
    `description` VARCHAR(255) NULL
);

-- 2. Lookup Tables (Dynamic Data)
CREATE TABLE IF NOT EXISTS vehicle_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    icon_url VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS labor_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1
);

-- 3. Subscription System (Foundation)
CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    target_role ENUM('client', 'driver') NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    duration_days INT NOT NULL DEFAULT 30,
    description_ar TEXT,
    description_en TEXT,
    is_active TINYINT(1) DEFAULT 1
);

-- 4. Users & Auth
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('client', 'driver', 'admin') NOT NULL,
    rating DECIMAL(3, 2) DEFAULT 5.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS driver_details (
    user_id INT PRIMARY KEY,
    vehicle_type_id INT NULL,
    plate_number VARCHAR(20) NOT NULL,
    license_photo VARCHAR(255) NULL,
    is_verified TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_type_id) REFERENCES vehicle_types(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS user_wallets (
    user_id INT PRIMARY KEY,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id)
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    type ENUM('subscription_fee', 'deposit', 'refund', 'ride_commission') NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Operations (Requests & Offers)
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    vehicle_type_id INT NULL,
    labor_option_id INT NULL,
    pickup_lat DECIMAL(10, 8) NOT NULL,
    pickup_lng DECIMAL(11, 8) NOT NULL,
    dropoff_lat DECIMAL(10, 8) NOT NULL,
    dropoff_lng DECIMAL(11, 8) NOT NULL,
    pickup_address TEXT NULL,
    dropoff_address TEXT NULL,
    description TEXT NULL,
    images JSON NULL, -- Stores array of image URLs
    status ENUM('open', 'accepted', 'in_progress', 'completed', 'cancelled') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_type_id) REFERENCES vehicle_types(id),
    FOREIGN KEY (labor_option_id) REFERENCES labor_options(id)
);

CREATE TABLE IF NOT EXISTS offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    driver_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    comment TEXT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Driver Live Location
CREATE TABLE IF NOT EXISTS driver_locations (
    driver_id INT PRIMARY KEY,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- SEED DATA (Internal Data)
-- =============================================

-- App Settings
INSERT INTO app_settings (`key`, `value`, `description`) VALUES
('free_mode_enabled', '1', 'If 1, subscription checks are skipped. If 0, subscriptions are enforced.'),
('support_phone', '+96650000000', 'Contact number for support'),
('commission_percentage', '10', 'Percentage taken from driver per ride (future use)');

-- Vehicle Types
INSERT INTO vehicle_types (name_ar, name_en, icon_url) VALUES
('شاحنة صغيرة (وانيت)', 'Pickup Truck', 'assets/icons/pickup.png'),
('شاحنة ثقيلة (تريلا)', 'Heavy Truck (Trailer)', 'assets/icons/trailer.png'),
('سطحة', 'Flatbed Tow Truck', 'assets/icons/flatbed.png'),
('دينا (صندوق)', 'Dyna (Box Truck)', 'assets/icons/dyna.png'),
('براد', 'Refrigerated Truck', 'assets/icons/refrigerator.png'),
('حاوية', 'Container', 'assets/icons/container.png');

-- Labor Options
INSERT INTO labor_options (name_ar, name_en) VALUES
('لا أحتاج مساعدة', 'No Help Needed'),
('تحميل فقط', 'Loading Only'),
('تنزيل فقط', 'Unloading Only'),
('تحميل وتنزيل', 'Loading & Unloading'),
('توفير رافعة شوكية', 'Provide Forklift');

-- Subscription Plans (Examples)
INSERT INTO subscription_plans (name_ar, name_en, target_role, price, duration_days, description_ar, description_en) VALUES
('باقة السائق الأساسية', 'Driver Basic Plan', 'driver', 100.00, 30, 'حساب سائق لمدة شهر مع وصول لجميع الطلبات', 'Monthly driver account with access to all requests'),
('باقة العميل المميز', 'VIP Client', 'client', 50.00, 30, 'أولوية في الطلبات ودعم خاص', 'Priority requests and premium support');
