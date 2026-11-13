-- Run this once in phpMyAdmin (database: clothing_shop_db)

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`name`, `category`, `description`, `price`, `stock`) VALUES
('Classic White Tee', 'Tops', 'Soft 100% cotton crew-neck t-shirt.', 399.00, 40),
('Slim Fit Denim Jeans', 'Bottoms', 'Stretch denim with a tapered leg.', 1299.00, 18),
('Linen Button-Up Shirt', 'Tops', 'Lightweight and breathable for warm days.', 899.00, 4),
('Pleated Midi Skirt', 'Bottoms', 'Flowing pleated skirt with elastic waist.', 749.00, 0);
