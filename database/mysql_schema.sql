-- Restaurant POS assessment data (MySQL / MariaDB)
-- Generated from the PDF data used by the CI4 seeder.

CREATE DATABASE IF NOT EXISTS `restaurant_pos` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `restaurant_pos`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `item_prices`;
DROP TABLE IF EXISTS `items`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `menus`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `menus` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `menu_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_menu_id_index` (`menu_id`),
  CONSTRAINT `categories_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `menu_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `items_category_id_index` (`category_id`),
  KEY `items_menu_id_index` (`menu_id`),
  CONSTRAINT `items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `items_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `item_prices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` INT UNSIGNED NOT NULL,
  `size` VARCHAR(30) DEFAULT NULL,
  `price` DECIMAL(14,5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_prices_item_id_index` (`item_id`),
  CONSTRAINT `item_prices_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` INT UNSIGNED NOT NULL,
  `order_date` DATE NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Completed',
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_order_date_index` (`order_date`),
  KEY `orders_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `item_id` INT UNSIGNED NOT NULL,
  `size` VARCHAR(30) DEFAULT NULL,
  `unit_price` DECIMAL(14,5) NOT NULL,
  `qty` INT UNSIGNED NOT NULL,
  `line_total` DECIMAL(16,5) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Completed',
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_item_id_index` (`item_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_date` DATE NOT NULL,
  `transaction_ref` VARCHAR(50) NOT NULL,
  `order_id` INT UNSIGNED NOT NULL,
  `amount_due` DECIMAL(16,5) NOT NULL,
  `tips` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `discount` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `total_paid` DECIMAL(16,5) NOT NULL,
  `payment_type` VARCHAR(20) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Completed',
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_transaction_ref_unique` (`transaction_ref`),
  KEY `payments_order_id_index` (`order_id`),
  KEY `payments_status_index` (`status`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `menus` (`id`, `name`) VALUES
(1, 'Food'),
(2, 'Drinks');

INSERT INTO `categories` (`id`, `name`, `menu_id`) VALUES
(1, 'Starters', 1),
(2, 'Soft Drinks', 2),
(3, 'Mains', 1),
(4, 'Desserts', 2),
(5, 'Hot Drinks', 2);

INSERT INTO `items` (`id`, `name`, `category_id`, `menu_id`) VALUES
(1, 'Item1', 1, 1),
(2, 'Item2', 1, 1),
(3, 'Item3', 2, 2),
(4, 'Item4', 2, 2),
(5, 'Item5', 2, 1),
(6, 'Item6', 3, 1),
(7, 'Item7', 3, 1),
(8, 'Item8', 4, 2),
(9, 'Item9', 4, 2),
(10, 'Item10', 5, 2);

INSERT INTO `item_prices` (`id`, `item_id`, `size`, `price`) VALUES
(1, 1, 'Small', '1.50'),
(2, 1, 'Large', '2.50'),
(3, 2, NULL, '3'),
(4, 3, NULL, '2.5'),
(5, 4, NULL, '1.5'),
(6, 5, NULL, '1'),
(7, 6, 'Small', '2.50'),
(8, 6, 'Large', '3.6'),
(9, 7, NULL, '2.5'),
(10, 8, 'Small', '3.75'),
(11, 8, 'Large', '6.5'),
(12, 9, NULL, '1.5'),
(13, 10, NULL, '2');

INSERT INTO `orders` (`id`, `order_number`, `order_date`, `status`) VALUES
(10, 10, '2025-10-01', 'Completed'),
(11, 11, '2025-10-01', 'Completed'),
(12, 12, '2025-10-01', 'Completed'),
(13, 13, '2025-10-01', 'Completed'),
(14, 14, '2025-10-01', 'Completed'),
(15, 15, '2025-10-02', 'Completed'),
(16, 16, '2025-10-03', 'Completed'),
(17, 17, '2025-10-01', 'Completed'),
(18, 18, '2025-10-05', 'Completed'),
(19, 19, '2025-10-01', 'Completed'),
(20, 20, '2025-10-01', 'Completed');

INSERT INTO `order_items` (`id`, `order_id`, `item_id`, `size`, `unit_price`, `qty`, `line_total`, `status`) VALUES
(1, 10, 2, NULL, '2.5', 1, '2.5', 'Completed'),
(2, 10, 3, NULL, '1.5', 2, '3', 'Completed'),
(3, 10, 1, 'Small', '3.75', 1, '3.75', 'Completed'),
(4, 11, 5, NULL, '2.75', 1, '2.75', 'Completed'),
(5, 11, 6, NULL, '1.75', 2, '3.5', 'Completed'),
(6, 11, 2, NULL, '2.5', 1, '2.5', 'Completed'),
(7, 11, 3, NULL, '3.5', 1, '3.5', 'Completed'),
(8, 11, 4, NULL, '3.75', 2, '7.5', 'Completed'),
(9, 11, 5, NULL, '1.5', 1, '1.5', 'Completed'),
(10, 12, 6, 'Large', '5.5', 2, '11', 'Completed'),
(11, 12, 7, NULL, '2.5', 1, '2.5', 'Completed'),
(12, 12, 1, 'Large', '3.5', 1, '3.5', 'Completed'),
(13, 13, 1, 'Small', '2.75', 2, '5.5', 'Completed'),
(14, 13, 6, 'Small', '1.5', 1, '1.5', 'Completed'),
(15, 13, 8, 'Small', '3.5', 1, '3.5', 'Completed'),
(16, 13, 1, 'Small', '2.5', 2, '5', 'Completed'),
(17, 14, 6, 'Large', '2.75', 1, '2.75', 'Completed'),
(18, 14, 1, 'Large', '2.75655', 2, '5.5131', 'Completed'),
(19, 14, 8, 'Large', '2.75', 2, '5.5', 'Completed'),
(20, 14, 1, 'Large', '2.7556', 2, '5.5112', 'Completed'),
(21, 14, 4, NULL, '5.5', 1, '5.5', 'Completed'),
(22, 14, 3, NULL, '2.75', 2, '5.5', 'Completed'),
(23, 14, 2, NULL, '3.5', 1, '3.5', 'Completed'),
(24, 14, 6, 'Large', '3.015', 3, '9.045', 'Completed'),
(25, 15, 2, NULL, '2.568', 2, '5.136', 'Completed'),
(26, 16, 6, 'Large', '6.586', 3, '19.758', 'Completed'),
(27, 17, 10, NULL, '2.5', 1, '2.5', 'Completed'),
(28, 17, 9, NULL, '2.75636', 1, '2.75636', 'Completed'),
(29, 17, 7, NULL, '5.63982', 1, '5.63982', 'Completed'),
(30, 18, 1, 'Small', '2.5698', 2, '5.1396', 'Completed'),
(31, 18, 6, 'Small', '5.36245', 2, '10.7249', 'Completed'),
(32, 18, 8, 'Small', '5.23569', 2, '10.47138', 'Completed'),
(33, 19, 2, NULL, '2.75698', 1, '2.75698', 'Completed'),
(34, 19, 4, NULL, '2.356', 1, '2.356', 'Completed'),
(35, 19, 5, NULL, '2.457', 2, '4.914', 'Completed'),
(36, 19, 7, NULL, '2.6359', 1, '2.6359', 'Completed'),
(37, 19, 9, NULL, '6.523', 1, '6.523', 'Completed'),
(38, 19, 10, NULL, '8.5412', 3, '25.6236', 'Completed'),
(39, 19, 6, 'Large', '5.683', 2, '11.366', 'Completed'),
(41, 19, 2, NULL, '6.3564', 1, '6.3564', 'Completed'),
(42, 19, 5, NULL, '7.235', 1, '7.235', 'Completed'),
(43, 19, 7, NULL, '2.365', 1, '2.365', 'Completed'),
(44, 20, 1, 'Large', '2.3658', 1, '2.3658', 'Completed'),
(45, 20, 3, NULL, '2.356', 1, '2.356', 'Completed'),
(46, 20, 6, 'Large', '1.256', 1, '1.256', 'Completed'),
(47, 20, 4, NULL, '2.635', 1, '2.635', 'Completed'),
(48, 20, 5, NULL, '5.21', 1, '5.21', 'Completed'),
(49, 20, 7, NULL, '6.325', 2, '12.65', 'Completed'),
(50, 20, 8, 'Small', '7.2514', 1, '7.2514', 'Completed'),
(51, 20, 9, NULL, '2.3999', 1, '2.3999', 'Completed'),
(52, 20, 4, NULL, '2.356', 3, '7.068', 'Completed'),
(53, 20, 6, 'Small', '4.5326', 2, '9.0652', 'Completed');

INSERT INTO `payments` (`id`, `payment_date`, `transaction_ref`, `order_id`, `amount_due`, `tips`, `discount`, `total_paid`, `payment_type`, `status`) VALUES
(1, '2025-10-01', '100', 10, '9.25', '0', '0', '9.25', 'Card', 'Completed'),
(2, '2025-10-01', '101', 11, '21.25', '0', '0', '10', 'Cash', 'Completed'),
(3, '2025-10-01', '102', 11, '21.25', '0', '0', '11.25', 'Card', 'Completed'),
(4, '2025-10-02', '103', 12, '17', '3', '4', '16', 'Card', 'Completed'),
(5, '2025-10-03', '104', 13, '15.5', '0', '2', '13.5', 'Card', 'Completed'),
(6, '2025-10-01', '105', 14, '42.8193', '0', '0', '20', 'Cash', 'Completed'),
(7, '2025-10-01', '106', 14, '42.8193', '0', '0', '22.82', 'Card', 'Completed'),
(8, '2025-10-02', '107', 15, '5.136', '0', '0', '5.14', 'Card', 'Refunded'),
(9, '2025-10-03', '108', 16, '19.758', '0', '0', '10', 'Cash', 'Completed'),
(10, '2025-10-03', '109', 16, '19.758', '0', '0', '9.76', 'Card', 'Completed'),
(11, '2025-10-01', '110', 17, '10.8918', '0', '0', '10.9', 'Card', 'Completed'),
(12, '2025-10-05', '111', 18, '26.33588', '2', '0', '25', 'Cash', 'Completed'),
(13, '2025-10-05', '115', 18, '26.33588', '0', '0', '3.34', 'Card', 'Completed'),
(14, '2025-10-01', '116', 19, '72.13188', '0', '0', '50', 'Cash', 'Completed'),
(15, '2025-10-01', '119', 19, '72.13188', '0', '0', '22.13', 'Card', 'Completed'),
(16, '2025-10-01', '120', 20, '52.2573', '0', '0', '25', 'Cash', 'Completed'),
(17, '2025-10-01', '121', 20, '52.2573', '0', '0', '27.28', 'Card', 'Completed');

