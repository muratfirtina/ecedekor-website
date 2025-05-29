-- Admin users tablosunu kontrol et ve eksik kolonları ekle
-- Önce mevcut yapıyı kontrol edelim
DESCRIBE admin_users;

-- Eksik kolonları tek tek ekleyelim (varsa hata verecek ama sorun değil)
ALTER TABLE `admin_users` ADD COLUMN `first_name` varchar(100) DEFAULT NULL AFTER `password`;
ALTER TABLE `admin_users` ADD COLUMN `last_name` varchar(100) DEFAULT NULL AFTER `first_name`;
ALTER TABLE `admin_users` ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `last_name`;
ALTER TABLE `admin_users` ADD COLUMN `role` enum('admin','moderator','user') DEFAULT 'user' AFTER `phone`;
ALTER TABLE `admin_users` ADD COLUMN `avatar` varchar(500) DEFAULT NULL AFTER `role`;
ALTER TABLE `admin_users` ADD COLUMN `is_active` tinyint(1) DEFAULT 1 AFTER `avatar`;
ALTER TABLE `admin_users` ADD COLUMN `last_login` timestamp NULL DEFAULT NULL AFTER `is_active`;

-- Admin kullanıcısının role ve is_active değerlerini güncelle
UPDATE `admin_users` SET 
  `role` = 'admin',
  `is_active` = 1,
  `first_name` = 'Admin',
  `last_name` = 'User'
WHERE `username` = 'admin';

-- Son kontrol
SELECT id, username, email, role, is_active, first_name, last_name FROM admin_users WHERE username = 'admin';
