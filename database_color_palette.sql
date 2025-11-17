-- Renk Kartelası Özelliği için Database Schema
-- Oluşturulma Tarihi: 2025-11-17

-- Renk kartelası ayarları tablosu
CREATE TABLE IF NOT EXISTS `color_palette_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT 'Renk Kartelamız',
  `subtitle` varchar(500) DEFAULT NULL,
  `description` text,
  `hero_image` varchar(500) DEFAULT NULL,
  `homepage_image` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `show_on_homepage` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Renk kartelasında gösterilecek kategoriler
CREATE TABLE IF NOT EXISTS `color_palette_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `color_palette_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan ayarları ekle
INSERT INTO `color_palette_settings` (`title`, `subtitle`, `description`, `is_active`, `show_on_homepage`)
VALUES (
  'Renk Kartelamız',
  'Geniş Renk Seçeneklerimizi Keşfedin',
  'Ürünlerimizde kullanılan tüm renk seçeneklerini bu sayfada inceleyebilirsiniz. Her rengin kodu ve ismi detaylı olarak listelenmiştir.',
  1,
  1
);
