-- Admin şifresini admin123 olarak güncelle
-- Bu hash admin123 şifresi için oluşturulmuştur

UPDATE admin_users SET 
password = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm' 
WHERE username = 'admin';

-- Alternatif hash (her seferinde farklı olur ama hepsi admin123 için çalışır):
-- '$2y$10$8K/QyUwOhI8yB3BgB0xYQefQF8k4QDvGz0xYy8QNjE2Y8QFf8F2XK'

-- Kontrolü:
SELECT id, username, email, password FROM admin_users WHERE username = 'admin';
