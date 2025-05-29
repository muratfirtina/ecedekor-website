-- Admin users tablosuna eksik kolonları ekleyelim
ALTER TABLE `admin_users` 
ADD COLUMN `first_name` varchar(100) DEFAULT NULL AFTER `password`,
ADD COLUMN `last_name` varchar(100) DEFAULT NULL AFTER `first_name`,
ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `last_name`,
ADD COLUMN `role` enum('admin','moderator','user') DEFAULT 'user' AFTER `phone`,
ADD COLUMN `avatar` varchar(500) DEFAULT NULL AFTER `role`,
ADD COLUMN `is_active` tinyint(1) DEFAULT 1 AFTER `avatar`,
ADD COLUMN `last_login` timestamp NULL DEFAULT NULL AFTER `is_active`;

-- User sessions tablosu oluşturalım
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `logout_time` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User permissions tablosu oluşturalım
CREATE TABLE IF NOT EXISTS `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_permission` (`user_id`, `permission`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin kullanıcısını güncelle
UPDATE `admin_users` SET 
  `first_name` = 'Admin',
  `last_name` = 'User',
  `role` = 'admin',
  `is_active` = 1
WHERE `username` = 'admin';
