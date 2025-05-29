-- Admin users tablosunun mevcut yapısını kontrol edin
DESCRIBE admin_users;

-- Mevcut admin kullanıcısını kontrol edin  
SELECT id, username, email, role, is_active, first_name, last_name FROM admin_users WHERE username = 'admin';
