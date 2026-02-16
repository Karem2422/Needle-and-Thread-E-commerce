-- Migration: create order status history and inventory logs tables
USE `needle_thread`;

CREATE TABLE IF NOT EXISTS `order_status_history` (
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
  KEY `inventory_logs_product_idx` (`product_id`),
  CONSTRAINT `inventory_logs_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

