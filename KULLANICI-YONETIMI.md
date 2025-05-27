# Kullanıcı Yönetimi ve Profil Sistemi

Bu güncelleme ile ECEDEKOR admin paneline gelişmiş kullanıcı yönetimi ve profil sistemi eklenmiştir.

## Yeni Özellikler

### 1. Kullanıcı Rolleri ve Yetkilendirme
- **Admin**: Tüm yetkilere sahip
- **Moderator**: Orta seviye yetkiler
- **User**: Sınırlı yetkiler

### 2. Profil Yönetimi
- Kullanıcı profil sayfası (`/admin/profile.php`)
- Profil fotoğrafı yükleme
- Kişisel bilgileri güncelleme
- Şifre değiştirme
- Oturum geçmişi görüntüleme

### 3. Kullanıcı Yönetimi (Sadece Admin)
- Yeni kullanıcı ekleme (`/admin/users.php`)
- Kullanıcı bilgilerini düzenleme
- Kullanıcı şifrelerini değiştirme
- Yetki atama ve kaldırma
- Kullanıcı silme (kendi hesabı hariç)

### 4. Güvenlik Özellikleri
- Oturum takibi ve loglama
- IP adresi ve tarayıcı bilgisi kaydetme
- Aktif/pasif kullanıcı kontrolü
- Son giriş zamanı takibi

## Kurulum

### 1. Veritabanı Güncellemesi
Veritabanınızı güncellemek için `database-update.sql` dosyasını çalıştırın:

```sql
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
```

### 2. Klasör İzinleri
Avatar yüklemesi için gerekli klasörü oluşturun:
```bash
mkdir -p assets/images/avatars
chmod 755 assets/images/avatars
```

### 3. Yeni Dosyalar
- `admin/profile.php` - Kullanıcı profil sayfası
- `admin/users.php` - Kullanıcı yönetimi sayfası
- `includes/config.php` - Güncellenmiş (yeni fonksiyonlar)
- `admin/includes/header.php` - Güncellenmiş (yeni menü öğeleri)
- `admin/login.php` - Güncellenmiş (oturum takibi)
- `admin/logout.php` - Güncellenmiş (oturum sonlandırma)

## Kullanım

### Profil Yönetimi
1. Admin paneline giriş yapın
2. Sağ üst köşedeki kullanıcı menüsünden "Profil" seçin
3. Profil bilgilerinizi güncelleyin
4. Şifrenizi değiştirin (isteğe bağlı)

### Kullanıcı Yönetimi (Sadece Admin)
1. Sol menüden "Kullanıcı Yönetimi" seçin
2. "Yeni Kullanıcı" butonuna tıklayın
3. Kullanıcı bilgilerini doldurun
4. Rol seçin (admin/moderator/user)
5. Gerekli yetkileri atayın
6. Kullanıcıyı kaydedin

### Yetki Sistemi
Mevcut yetkiler:
- `categories_manage` - Kategori Yönetimi
- `products_manage` - Ürün Yönetimi
- `variants_manage` - Varyant Yönetimi
- `testimonials_manage` - Yorum Yönetimi
- `homepage_manage` - Ana Sayfa Yönetimi
- `settings_manage` - Site Ayarları
- `files_manage` - Dosya Yönetimi
- `users_manage` - Kullanıcı Yönetimi
- `users_create` - Kullanıcı Oluşturma
- `reports_view` - Rapor Görüntüleme

### Kod Kullanımı
Sayfalarda yetki kontrolü için:

```php
// Rol kontrolü
requireRole('admin'); // Sadece admin erişebilir
if (hasRole('admin')) {
    // Admin işlemleri
}

// Yetki kontrolü
requirePermission('products_manage'); // Ürün yönetimi yetkisi gerekli
if (hasPermission('categories_manage')) {
    // Kategori yönetimi işlemleri
}
```

## Güvenlik Notları

1. **Varsayılan Admin Hesabı**: Kurulumdan sonra mutlaka admin şifresini değiştirin
2. **Yetki Kontrolleri**: Tüm hassas sayfalar için yetki kontrolü eklendi
3. **Oturum Güvenliği**: Tüm oturumlar loglanır ve takip edilir
4. **Dosya Güvenliği**: Avatar yüklemesinde dosya türü kontrolü yapılır

## Sorun Giderme

### Veritabanı Hataları
- Veritabanı güncellemelerinin başarıyla çalıştığını kontrol edin
- MySQL/MariaDB versiyonunun uyumlu olduğundan emin olun

### Yetki Sorunları
- Kullanıcı rollerinin doğru atandığını kontrol edin
- Admin kullanıcısının tüm yetkilere sahip olduğunu doğrulayın

### Dosya Yükleme Sorunları
- `assets/images/avatars` klasörünün yazma izinlerine sahip olduğunu kontrol edin
- PHP `upload_max_filesize` ayarını kontrol edin

## Yeni Özellikler İçin Planlanan Geliştirmeler

1. **Email Bildirimleri**: Yeni kullanıcı oluşturulduğunda email gönderimi
2. **İki Faktörlü Kimlik Doğrulama**: Google Authenticator desteği
3. **API Yetkilendirme**: REST API için token bazlı yetkilendirme
4. **Aktivite Logları**: Detaylı kullanıcı aktivite takibi
5. **Toplu İşlemler**: Çoklu kullanıcı yönetimi

Bu sistem ile admin paneli artık çok kullanıcılı bir ortamda güvenli şekilde kullanılabilir.
