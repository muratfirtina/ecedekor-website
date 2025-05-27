-- Admin users tablosuna yeni alanlar ekleme
ALTER TABLE `admin_users` ADD COLUMN `role` enum('admin','user','moderator') NOT NULL DEFAULT 'user' AFTER `password`;
ALTER TABLE `admin_users` ADD COLUMN `first_name` varchar(100) DEFAULT NULL AFTER `username`;
ALTER TABLE `admin_users` ADD COLUMN `last_name` varchar(100) DEFAULT NULL AFTER `first_name`;
ALTER TABLE `admin_users` ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `email`;
ALTER TABLE `admin_users` ADD COLUMN `avatar` varchar(500) DEFAULT NULL AFTER `phone`;
ALTER TABLE `admin_users` ADD COLUMN `is_active` tinyint(1) DEFAULT 1 AFTER `role`;
ALTER TABLE `admin_users` ADD COLUMN `last_login` timestamp NULL DEFAULT NULL AFTER `is_active`;

-- Mevcut admin kullanıcısını admin rolü ile güncelle
UPDATE `admin_users` SET `role` = 'admin', `first_name` = 'Admin', `last_name` = 'User' WHERE `username` = 'admin';

-- Kullanıcı izinleri tablosu
CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  UNIQUE KEY `user_permission` (`user_id`, `permission`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kullanıcı oturum logları
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `logout_time` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('company_mobile', '+90 555 000 00 00', 'text')
ON DUPLICATE KEY UPDATE setting_value = '+90 555 000 00 00';

-- WhatsApp numarası ayarını da ekle (sadece rakamlar, ülke kodu ile)
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('whatsapp_number', '905550000000', 'text')
ON DUPLICATE KEY UPDATE setting_value = '905550000000';
