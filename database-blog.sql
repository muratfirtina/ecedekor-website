-- Blog Sistemi Veritabanı Tablosu
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

-- Örnek blog verisi (author_id NULL - daha sonra admin id'si ile güncellenebilir)
INSERT INTO `blogs` (`title`, `slug`, `content`, `excerpt`, `featured_image`, `meta_title`, `meta_description`, `focus_keyword`, `author_id`, `status`, `is_featured`, `published_at`) 
VALUES 
('Ahşap Mobilya Bakımı İçin En İyi Yöntemler', 
 'ahsap-mobilya-bakimi-icin-en-iyi-yontemler', 
 '<h2>Ahşap Mobilyalarınızı Nasıl Bakımlı Tutabilirsiniz?</h2><p>Ahşap mobilyalar evinize doğal bir güzellik katar ancak düzenli bakım gerektirir. Bu yazıda ahşap mobilyalarınızı uzun yıllar boyunca güzel tutmanın yollarını keşfedeceksiniz.</p><h3>1. Düzenli Temizlik</h3><p>Haftalık olarak yumuşak bir bez ile tozunu alın...</p>', 
 'Ahşap mobilyalarınızı uzun ömürlü tutmak için profesyonel bakım önerileri', 
 NULL, 
 'Ahşap Mobilya Bakımı - En İyi Yöntemler ve İpuçları', 
 'Ahşap mobilyalarınızı nasıl temizleyeceğinizi ve koruyacağınızı öğrenin. Uzman tavsiyeleri ile mobilyalarınızı yıllarca güzel tutun.', 
 'ahşap mobilya bakımı',
 NULL,
 'published', 
 1, 
 NOW());
