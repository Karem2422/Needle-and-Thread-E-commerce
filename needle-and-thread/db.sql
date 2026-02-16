-- Needle and Thread Database Schema
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `needle_thread` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `needle_thread`;

-- Users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE `products` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product images table
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments table
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `approved` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','cancelled') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order status history table
CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `old_status` enum('pending','paid','shipped','cancelled') DEFAULT NULL,
  `new_status` enum('pending','paid','shipped','cancelled') NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_idx` (`order_id`),
  CONSTRAINT `order_status_history_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_history_user_fk` FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory logs table
CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `change_type` enum('order','refund','manual','adjustment') DEFAULT 'manual',
  `quantity_change` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inventory_logs_product_idx` (`product_id`),
  CONSTRAINT `inventory_logs_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact messages table
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample admin user (password: admin123)
INSERT INTO `users` (`name`, `email`, `password_hash`, `is_admin`) VALUES
('Admin User', 'admin@needlethread.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert sample products
INSERT INTO `products` (`title`, `slug`, `description`, `price`, `quantity`, `tags`) VALUES
('Desert Nomad Tote', 'desert-nomad-tote', 'Handcrafted leather tote with intricate stitching. Perfect for everyday use with ample space for your essentials.', 189.99, 5, 'tote,leather,everyday'),
('Sahara Crossbody Bag', 'sahara-crossbody-bag', 'Elegant crossbody bag featuring traditional patterns and durable materials. Adjustable strap for comfort.', 145.50, 3, 'crossbody,pattern,adjustable'),
('Oasis Clutch', 'oasis-clutch', 'Evening clutch with metallic accents and secure clasp. Hand-embroidered details make this piece unique.', 95.00, 8, 'clutch,evening,embroidered');

-- Insert product images
INSERT INTO `product_images` (`product_id`, `filename`, `is_primary`) VALUES
(1, 'tote_main.jpg', 1),
(1, 'tote_detail.jpg', 0),
(2, 'crossbody_main.jpg', 1),
(3, 'clutch_main.jpg', 1);

-- Insert sample comments
INSERT INTO `comments` (`product_id`, `user_id`, `name`, `email`, `comment`, `approved`) VALUES
(1, NULL, 'Sarah Johnson', 'sarah@email.com', 'Beautiful craftsmanship! The attention to detail is amazing.', 1),
(2, NULL, 'Mike Chen', 'mike@email.com', 'Perfect size and very comfortable to wear. Highly recommended!', 1);

COMMIT;