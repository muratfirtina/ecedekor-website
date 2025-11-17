-- Production'da phpMyAdmin'de çalıştırın:

-- 1. Blogs tablosu var mı kontrol et
SHOW TABLES LIKE 'blogs';

-- 2. Blogs tablosu yapısını göster
DESCRIBE blogs;

-- 3. Blogs tablosundaki tüm kayıtları göster
SELECT * FROM blogs;

-- 4. Eğer blogs tablosu yoksa, bu komutu çalıştırın:
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `focus_keyword` varchar(100) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `published_at` (`published_at`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Örnek blog ekle (test için)
INSERT INTO `blogs` 
(`title`, `slug`, `content`, `excerpt`, `meta_title`, `meta_description`, `focus_keyword`, `author_id`, `status`, `is_featured`, `published_at`) 
VALUES 
('Test Blog Yazısı', 
 'test-blog-yazisi', 
 '<h2>Bu Bir Test Yazısıdır</h2><p>Production ortamında blog sisteminin çalıştığını test ediyoruz.</p>', 
 'Blog sistemi test yazısı', 
 'Test Blog - ECEDEKOR', 
 'Blog sistemi test ve kontrol için oluşturulmuş örnek yazı', 
 'test blog',
 NULL,
 'published', 
 0, 
 NOW());

-- 6. Admin_users ve users tablosu kontrolü
SHOW TABLES LIKE 'admin_users';
SHOW TABLES LIKE 'users';

-- 7. Eğer admin_users tablosu varsa ilk kullanıcının ID'sini al
SELECT id, username, full_name FROM admin_users LIMIT 1;

-- 8. Blog yazarı olmayan kayıtları güncelle (opsiyonel)
-- UPDATE blogs SET author_id = 1 WHERE author_id IS NULL;
