-- Footer ve Ana Şirket Ayarları için Database Güncellemesi

-- Footer logosu ayarı
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) 
VALUES ('footer_logo_path', '', 'image')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- Ana şirket ayarı
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) 
VALUES ('parent_company', '', 'text')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`)
VALUES ('company_story_image', '', 'image')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`)
VALUES ('footer_bottom_logo_path', '', 'image')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- Diğer eksik ayarları da ekleyelim
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) 
VALUES 
('hero_image', '', 'image'),
('about_image', '', 'image'),
('contact_image', '', 'image'),
('company_mobile', '', 'text'),
('whatsapp_number', '', 'text'),
('meta_keywords', '', 'textarea'),
('google_analytics', '', 'text'),
('google_search_console', '', 'text'),
('facebook_url', '', 'text'),
('instagram_url', '', 'text'),
('linkedin_url', '', 'text'),
('maintenance_mode', '0', 'boolean'),
('cache_enabled', '1', 'boolean'),
('custom_css', '', 'textarea'),
('custom_js', '', 'textarea')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;
