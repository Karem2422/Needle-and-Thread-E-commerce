-- Needle and Thread Database Schema for Mobile App
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `needle_thread` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `needle_thread`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('available','sold_out') DEFAULT 'available',
  `tags` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product images table
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments table
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `approved` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_approved` (`approved`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','cancelled') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order status history table
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `old_status` enum('pending','paid','shipped','cancelled') DEFAULT NULL,
  `new_status` enum('pending','paid','shipped','cancelled') NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `order_status_history_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_history_user_fk` FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory logs table
CREATE TABLE IF NOT EXISTS `inventory_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `change_type` enum('order','refund','manual','adjustment') DEFAULT 'manual',
  `quantity_change` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `inventory_logs_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact messages table
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password reset table
CREATE TABLE IF NOT EXISTS `password_reset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample admin user (password: admin123)
INSERT INTO `users` (`name`, `email`, `password_hash`, `is_admin`) VALUES
('Admin User', 'admin@needlethread.com', '$2a$10$YourHashedPasswordHere', 1)
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample products
INSERT INTO `products` (`title`, `slug`, `description`, `price`, `quantity`, `tags`) VALUES
('Desert Nomad Tote', 'desert-nomad-tote', 'Handcrafted leather tote with intricate stitching. Perfect for everyday use with ample space for your essentials.', 189.99, 5, 'tote,leather,everyday'),
('Sahara Crossbody Bag', 'sahara-crossbody-bag', 'Elegant crossbody bag featuring traditional patterns and durable materials. Adjustable strap for comfort.', 145.50, 3, 'crossbody,pattern,adjustable'),
('Oasis Clutch', 'oasis-clutch', 'Evening clutch with metallic accents and secure clasp. Hand-embroidered details make this piece unique.', 95.00, 8, 'clutch,evening,embroidered')
ON DUPLICATE KEY UPDATE title=title;

COMMIT;
