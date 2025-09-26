-- ECEDEKOR Website Database Schema
-- Created for www.ecedekor.com.tr

CREATE DATABASE IF NOT EXISTS ecedekor_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecedekor_db;

-- Admin users table
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product categories table
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(500),
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text,
  `description` longtext,
  `features` longtext,
  `usage_instructions` longtext,
  `main_image` varchar(500),
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product variants table (for different colors, sizes, etc.)
CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(100),
  `size` varchar(100),
  `weight` varchar(100),
  `sku` varchar(100),
  `price` decimal(10,2),
  `image` varchar(500),
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product images table
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `image_path` varchar(500) NOT NULL,
  `alt_text` varchar(255),
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `variant_id` (`variant_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_images_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Homepage sections table (for carousel, hero sections, etc.)
CREATE TABLE `homepage_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_type` enum('hero','carousel','about','features','testimonials') NOT NULL,
  `title` varchar(255),
  `subtitle` varchar(255),
  `content` longtext,
  `image` varchar(500),
  `button_text` varchar(100),
  `button_link` varchar(255),
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonials table
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `company` varchar(255),
  `position` varchar(255),
  `content` text NOT NULL,
  `image` varchar(500),
  `rating` int(1) DEFAULT 5,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site settings table
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext,
  `setting_type` enum('text','textarea','image','number','boolean') DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `product_info` varchar(255) DEFAULT NULL, -- İlgili ürün bilgisi için
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0, -- 0: okunmadı, 1: okundu
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data

-- Admin user (password: admin123)
INSERT INTO `admin_users` (`username`, `email`, `password`) VALUES
('admin', 'admin@ecedekor.com.tr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Categories
INSERT INTO `categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Ahşap Tamir Macunları', 'ahsap-tamir-macunlari', 'Ahşap yüzeylerdeki çatlak, delik ve ezilmelerin tamiri için özel olarak geliştirilmiş macunlar', 1),
('Tüp Tamir Macunları', 'tup-tamir-macunlari', 'Kolay kullanım için tüp formunda hazırlanmış tamir macunları', 2),
('Zemin Koruyucu Keçeler', 'zemin-koruyucu-keceler', 'Mobilya ayaklarında kullanılan yapışkanlı zemin koruyucu keçeler', 3),
('Yapışkanlı Tapalar', 'yapiskanli-tapalar', 'Vida başlarını kapatmak için kullanılan PVC tapalar', 4);

-- Products
INSERT INTO `products` (`category_id`, `name`, `slug`, `short_description`, `description`, `features`, `usage_instructions`, `sort_order`) VALUES
(1, 'ECE Ahşap Tamir Macunu 200gr', 'ece-ahsap-tamir-macunu-200gr', 'Ahşap yüzeylerdeki tamirat işlemlerinde kullanılan profesyonel macun', 'Ahşap tamir macunu, kullanımı kolay kılan hoş ve ince taneli bir dokuya sahip geleneksel bir dolgu macunudur. Ahşap tamir macunu öncelikle mobilya endüstrisinde daha küçük tamirlerde ve zımparalama arasında yapılan işlerde kullanılır.', 'Yüksek dolgu gücü\nKolay uygulanabilir\nHızlı kuruma\nZımparalanabilir\nBoyama yapılabilir\nÇevre dostu', 'Tamir edilecek yüzey temizlenmelidir. Macun spatula veya parmakla uygulanır. 15-20 dakika kuruma süresinden sonra zımparalanabilir. İsteğe bağlı olarak üzerine boya uygulanabilir.', 1),
(1, 'ECE Ahşap Tamir Macunu 125ml', 'ece-ahsap-tamir-macunu-125ml', 'Küçük tamirat işleri için ideal boyutta ahşap macunu', 'Küçük ahşap tamiratlarda kullanım için ideal boyutta hazırlanmış kaliteli tamir macunu. Mobilya sektöründe yaygın olarak kullanılmaktadır.', 'Kompakt boyut\nEkonomik kullanım\nYüksek kalite\nKolay saklama', 'Yüzey temiz ve kuru olmalıdır. İnce tabakalar halinde uygulayın. Kuruma süresini bekleyin ve zımparalayın.', 2);

-- Product variants
INSERT INTO `product_variants` (`product_id`, `name`, `color`, `weight`, `sku`) VALUES
(1, 'Doğal Renk', 'Doğal', '200gr', 'ECE-200-DOGAL'),
(1, 'Meşe Rengi', 'Meşe', '200gr', 'ECE-200-MESE'),
(1, 'Ceviz Rengi', 'Ceviz', '200gr', 'ECE-200-CEVIZ'),
(1, 'Siyah', 'Siyah', '200gr', 'ECE-200-SIYAH'),
(1, 'Beyaz', 'Beyaz', '200gr', 'ECE-200-BEYAZ'),
(2, 'Doğal Renk', 'Doğal', '125ml', 'ECE-125-DOGAL'),
(2, 'Meşe Rengi', 'Meşe', '125ml', 'ECE-125-MESE');

-- Homepage sections
INSERT INTO `homepage_sections` (`section_type`, `title`, `subtitle`, `content`, `button_text`, `button_link`, `sort_order`) VALUES
('hero', 'Ahşap Tamir ve Dolgu Malzemelerinde Uzman', 'ECEDEKOR ile Kaliteli Çözümler', '1998 yılından bu yana mobilya sektöründe kullanılmak üzere dolgu macunu, pvc tapa ve keçe üretimi yapmaktayız. Kalite ve müşteri memnuniyeti odaklı hizmet anlayışımızla sektöre öncülük ediyoruz.', 'Ürünlerimizi İnceleyin', '/urunler', 1),
('about', 'Hakkımızda', '27+ Yıllık Deneyim', 'ECEDEKOR, 1998 yılından bu yana mobilya sektöründe kullanılmak üzere dolgu macunu, pvc tapa ve keçe üretimi yapmaktadır. Kuruluşundan itibaren sadece yurt içi değil yurt dışındaki müşterilerine de hizmet vermektedir.', 'Daha Fazla Bilgi', '/hakkimizda', 2);

-- Testimonials
INSERT INTO `testimonials` (`name`, `company`, `content`, `rating`, `sort_order`) VALUES
('Mehmet Yılmaz', 'Mobilya Atölyesi', 'Zemin koruyucu keçeleri ve yapışkanlı tapalar konusunda piyasadaki en kaliteli ürünleri sunduklarını rahatlıkla söyleyebilirim. Müşteri memnuniyeti bizim için kritik ve Ecedekor ürünleriyle bunu kolaylıkla sağlıyoruz.', 5, 1),
('Ayşe Kaya', 'İç Mimar', 'Ecedekor\'un sunduğu tamir macunları ve yapışkanlı keçeler, projelerimizin hem kalite hem de estetik standartlarını artırdı. Ayrıca müşteri temsilcileri her zaman çok ilgili ve destekleyici.', 5, 2),
('Ahmet Demir', 'Mobilya Fabrikası', 'Ecedekor ile çalışmaya başladığımızdan beri üretim süreçlerimiz çok daha sorunsuz ilerliyor. Ürün kalitesi ve teslimat hızı beklentilerimizin çok üzerinde. Her zaman çözüm odaklı bir iş ortağımız oldukları için teşekkür ederiz.', 5, 3);

-- Site settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_title', 'ECEDEKOR - Ahşap Tamir ve Dolgu Malzemeleri', 'text'),
('site_description', 'Ahşap tamir macunları, zemin koruyucu keçeler ve yapışkanlı tapalar konusunda 27+ yıllık deneyimle hizmet veriyoruz.', 'textarea'),
('company_name', 'ECEDEKOR', 'text'),
('company_address', 'Cevizli Mah. Yeşil Sk. Şaman San. Sitesi No: 1/D Maltepe - İSTANBUL', 'textarea'),
('company_phone', '+90 216 371 91 77', 'text'),
('company_email', 'info@ecedekor.com.tr', 'text'),
('company_founded', '1998', 'text'),
('logo_path', '/assets/images/logo.png', 'image'),
('hero_image', '/assets/images/hero-bg.jpg', 'image');

ALTER TABLE `product_variants` ADD COLUMN `color_code` VARCHAR(7) DEFAULT NULL AFTER `color`;

-- Mevcut varyantlara örnek renk kodları ekleyin
UPDATE `product_variants` SET `color_code` = '#8B4513' WHERE `color` = 'Meşe';
UPDATE `product_variants` SET `color_code` = '#654321' WHERE `color` = 'Ceviz';
UPDATE `product_variants` SET `color_code` = '#000000' WHERE `color` = 'Siyah';
UPDATE `product_variants` SET `color_code` = '#FFFFFF' WHERE `color` = 'Beyaz';
