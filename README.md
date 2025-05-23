# ECEDEKOR Website

Ahşap tamir ve dolgu malzemeleri satan şirketler için hazırlanmış profesyonel PHP website sistemi.

## Özellikler

### Frontend Özellikleri
- ✨ Modern ve responsive tasarım (Tailwind CSS)
- 🏠 Dinamik ana sayfa yönetimi
- 📦 Ürün kategori ve varyant sistemi
- 💬 Müşteri yorumları bölümü
- 📱 Mobile-first responsive tasarım
- 🎨 Parallax ve carousel efektleri
- 🔍 SEO dostu URL yapısı
- 📞 WhatsApp entegrasyonu

### Admin Panel Özellikleri
- 🛡️ Güvenli admin girişi
- 📊 Dashboard ve istatistikler
- 🏷️ Kategori yönetimi
- 📦 Ürün yönetimi
- 🎨 Ürün varyant yönetimi (renk, boyut, ağırlık)
- 🖼️ Görsel yükleme ve yönetimi
- 🏠 Ana sayfa içerik yönetimi
- 💬 Müşteri yorumları yönetimi
- ⚙️ Site ayarları
- 📁 Dosya yöneticisi

### Teknik Özellikler
- PHP 7.4+ uyumlu
- MySQL/MariaDB veritabanı
- PDO veritabanı bağlantısı
- CSRF koruması
- XSS koruması
- Dosya yükleme güvenliği
- Responsive tasarım

## Kurulum

### Gereksinimler
- PHP 7.4 veya üstü
- MySQL 5.7 veya üstü / MariaDB 10.2 veya üstü
- Apache/Nginx web sunucusu
- mod_rewrite etkin

### 1. Dosyaları Yükleme
```bash
# Projeyi klonlayın veya indirin
git clone [repository-url]
cd ecedekor-website
```

### 2. Veritabanı Kurulumu
1. MySQL/MariaDB'de yeni bir veritabanı oluşturun:
```sql
CREATE DATABASE ecedekor_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. `database.sql` dosyasını çalıştırarak tabloları ve örnek verileri yükleyin:
```bash
mysql -u username -p ecedekor_db < database.sql
```

### 3. Yapılandırma
1. `includes/config.php` dosyasını açın
2. Veritabanı bağlantı bilgilerini güncelleyin:
```php
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'your_username');
define('DB_PASSWORD', 'your_password');
define('DB_NAME', 'ecedekor_db');
```
3. Base URL'yi sitenizin adresine göre güncelleyin:
```php
define('BASE_URL', 'http://yourdomain.com');
```

### 4. Dosya İzinleri
```bash
# Görsel yükleme klasörüne yazma izni verin
chmod 755 assets/images/
chmod 755 assets/images/products/
chmod 755 assets/images/categories/
chmod 755 assets/images/uploads/
```

### 5. Apache/Nginx Yapılandırması

#### Apache için .htaccess
```apache
RewriteEngine On
RewriteBase /

# Remove .php extension
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^([^\.]+)$ $1.php [NC,L]

# SEO friendly URLs
RewriteRule ^kategori/([^/]+)/?$ kategori.php?slug=$1 [NC,L]
RewriteRule ^urun/([^/]+)/?$ urun.php?slug=$1 [NC,L]
```

#### Nginx için
```nginx
location / {
    try_files $uri $uri.php $uri/ /index.php?$query_string;
}

location ~ ^/kategori/([^/]+)/?$ {
    try_files $uri /kategori.php?slug=$1;
}

location ~ ^/urun/([^/]+)/?$ {
    try_files $uri /urun.php?slug=$1;
}
```

## Admin Panel

### Giriş Bilgileri
- **URL:** `http://yourdomain.com/admin/`
- **Kullanıcı Adı:** `admin`
- **Şifre:** `admin123`

> ⚠️ **Güvenlik:** İlk girişte mutlaka admin şifresini değiştirin!

### Admin Panel Bölümleri

#### Dashboard
- Sistem genel durumu
- İstatistikler
- Son eklenen ürünler
- Hızlı işlemler

#### Kategori Yönetimi
- Kategori ekleme/düzenleme/silme
- Kategori görseli yükleme
- Sıralama ve aktiflik durumu

#### Ürün Yönetimi
- Ürün ekleme/düzenleme/silme
- Ürün detayları ve özellikler
- Ana ürün görseli yükleme
- Kategori atama

#### Ürün Varyantları
- Renk, boyut, ağırlık varyantları
- Varyant görselleri
- SKU ve fiyat bilgileri

#### Ana Sayfa Yönetimi
- Hero section düzenleme
- Carousel yönetimi
- Hakkımızda bölümü
- Özellik alanları

#### Müşteri Yorumları
- Yorum ekleme/düzenleme
- Yıldız puanlama
- Müşteri bilgileri

#### Site Ayarları
- Genel site bilgileri
- İletişim bilgileri
- SEO ayarları
- Logo ve görsel yönetimi

## Dosya Yapısı

```
ecedekor-website/
├── admin/                  # Admin panel
│   ├── includes/          # Admin header/footer
│   ├── login.php          # Admin girişi
│   ├── dashboard.php      # Ana panel
│   ├── categories.php     # Kategori yönetimi
│   ├── products.php       # Ürün yönetimi
│   └── ...
├── assets/                # Static dosyalar
│   ├── images/           # Görseller
│   └── css/              # CSS dosyaları
├── includes/             # Ortak dosyalar
│   ├── config.php        # Yapılandırma
│   ├── header.php        # Site header
│   └── footer.php        # Site footer
├── index.php             # Ana sayfa
├── urunler.php           # Ürünler sayfası
├── kategori.php          # Kategori sayfası
├── urun.php              # Ürün detay sayfası
├── hakkimizda.php        # Hakkımızda sayfası
├── iletisim.php          # İletişim sayfası
└── database.sql          # Veritabanı schema
```

## Güvenlik

### Önerilen Güvenlik Önlemleri
1. Admin şifresini güçlü bir şifre ile değiştirin
2. Düzenli olarak sistem güncellemelerini yapın
3. Dosya yükleme dizinlerini web erişiminden koruyun
4. SSL sertifikası kullanın
5. Güvenlik duvarı kuralları ayarlayın

### Güvenlik Özellikleri
- CSRF token koruması
- XSS filtreleme
- SQL injection koruması
- Dosya uzantısı kontrolü
- Dosya boyutu sınırı

## Özelleştirme

### Tasarım Değişiklikleri
- `includes/header.php` ve `includes/footer.php` dosyalarını düzenleyin
- Tailwind CSS sınıflarını kullanarak tasarımı özelleştirin
- `assets/images/` klasörüne kendi görsellerinizi yükleyin

### Yeni Özellikler Ekleme
- Veritabanı şemasını `database.sql` dosyasına ekleyin
- İlgili PHP dosyalarını oluşturun
- Admin paneline yönetim sayfaları ekleyin

## Sorun Giderme

### Yaygın Sorunlar

#### Veritabanı Bağlantı Hatası
- `includes/config.php` dosyasındaki bilgileri kontrol edin
- MySQL servisinin çalıştığını doğrulayın

#### Görsel Yükleme Sorunu
- `assets/images/` klasörü izinlerini kontrol edin (755)
- PHP `upload_max_filesize` ayarını kontrol edin

#### Admin Paneline Giriş Yapamama
- Veritabanında `admin_users` tablosunu kontrol edin
- Session ayarlarını kontrol edin

#### 404 Hataları
- `.htaccess` dosyasının mevcut olduğunu kontrol edin
- Apache mod_rewrite modülünün etkin olduğunu doğrulayın

## Destek

Herhangi bir sorun yaşadığınızda:
1. Önce bu dokümantasyonu kontrol edin
2. Error loglarını kontrol edin
3. Veritabanı bağlantısını test edin

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## Katkıda Bulunma

1. Projeyi fork edin
2. Yeni bir branch oluşturun (`git checkout -b feature/YeniOzellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik eklendi'`)
4. Branch'inizi push edin (`git push origin feature/YeniOzellik`)
5. Pull Request oluşturun

---

**ECEDEKOR Website** - Ahşap tamir ve dolgu malzemeleri için profesyonel web çözümü
