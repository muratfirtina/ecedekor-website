# Renk Kartelası Özelliği - Kurulum ve Kullanım Kılavuzu

## 📋 Özellik Özeti

Renk kartelası özelliği, admin panelinden seçilen kategorilerdeki ürünlerin renklerini ve ürün görsellerini sergileyen profesyonel bir sayfa sistemidir.

### ✨ Özellikler

1. **Admin Panel Yönetimi**
   - Renk kartelası ayarları (başlık, açıklama, görseller)
   - Kategori seçimi ve sıralama
   - Anasayfa görünürlük kontrolü

2. **Frontend Görünüm**
   - Kategori bazlı ürün görselleri
   - Renk kartelası (renk isimleri ve kodları)
   - Responsive tasarım
   - Kategoriler arası hızlı geçiş

3. **Navigasyon Entegrasyonu**
   - Top bar'da "Renk Kartelamız" butonu
   - Anasayfa'da özel bölüm
   - Tıklanabilir görseller

---

## 🚀 Kurulum Adımları

### 1. Database Schema Yükleme

Database güncellemelerini yapmak için aşağıdaki SQL dosyasını çalıştırın:

```bash
# MySQL üzerinden
mysql -u ecedekor_admin -p ecedekor_db < database_color_palette.sql

# veya phpMyAdmin üzerinden
# database_color_palette.sql dosyasını Import edin
```

Bu işlem 2 yeni tablo oluşturacak:
- `color_palette_settings` - Genel ayarlar
- `color_palette_categories` - Seçili kategoriler

### 2. Dizin İzinlerini Ayarlama

Resim yükleme için gerekli dizini oluşturun:

```bash
mkdir -p assets/images/color-palette
chmod 755 assets/images/color-palette
```

---

## 📝 Kullanım Kılavuzu

### Admin Panel Kullanımı

#### 1. Renk Kartelası Ayarları Sayfasına Giriş

Admin panelde sol menüden **"Renk Kartelası"** linkine tıklayın.

#### 2. Genel Ayarları Yapılandırma

**Genel Ayarlar** bölümünde aşağıdaki bilgileri girin:

- **Başlık**: Sayfa başlığı (örn: "Renk Kartelamız")
- **Alt Başlık**: Kısa açıklama
- **Açıklama**: Detaylı açıklama metni
- **Hero Resmi**: Sayfa üst kısmında görünecek resim (1920x600px önerilir)
- **Anasayfa Resmi**: Anasayfa bölümünde görünecek resim (800x600px önerilir)
- **Renk kartelası sayfası aktif**: Sayfayı aktif/pasif yapma
- **Anasayfada göster**: Anasayfa bölümünü göster/gizle

#### 3. Kategori Ekleme

**Renk Kartelasında Gösterilecek Kategoriler** bölümünde:

1. Dropdown'dan kategori seçin (yanında kaç renk olduğu gösterilir)
2. Sıra numarası girin (0'dan başlar)
3. **"Ekle"** butonuna tıklayın

#### 4. Kategori Yönetimi

Eklenen kategoriler için:

- **Sıralama**: Her kategorinin sıra numarasını değiştirip "Sıralamayı Kaydet" butonuna tıklayın
- **Aktif/Pasif**: Duruma tıklayarak kategoriyi aktif veya pasif yapın
- **Kaldır**: Kategoriyi listeden tamamen kaldırın

#### 5. Önizleme

**"Renk Kartelası Sayfasını Görüntüle"** butonuna tıklayarak sayfayı önizleyin.

---

### Frontend Kullanımı

#### Renk Kartelası Sayfası

Kullanıcılar aşağıdaki yerlerden renk kartelasına erişebilir:

1. **Top Navigation Bar**: "Renk Kartelamız" butonu (Hakkımızda'dan sonra)
2. **Anasayfa**: Öne Çıkan Ürünler bölümünden sonraki özel alan
3. **Doğrudan URL**: `https://yourdomain.com/renk-kartelasi.php`

#### Sayfa İçeriği

Her kategori için:

1. **Kategori Başlığı ve Açıklaması**
2. **Ürün Görselleri** (en fazla 8 ürün)
   - Ürün resmine tıklayarak ürün detay sayfasına gidilebilir
3. **Renk Kartelası**
   - Her renk için renk kutusu, isim ve kod
   - Renkler grid düzeninde gösterilir

#### Kategoriler Arası Geçiş

Sayfa sonunda **"Kategorilere Hızlı Geçiş"** bölümü ile sayfada gezinme kolaylığı.

---

## 🎨 Renk Kodları Hakkında

### Varyantlarda Renk Kodu Ekleme

Renklerin görünebilmesi için product_variants tablosunda `color_code` alanının dolu olması gerekir.

#### Admin Variants Sayfasından

1. **Admin > Ürün Varyantları** sayfasına gidin
2. Varyantı düzenleyin
3. **Color Code** alanına hex kodu girin (örn: #8B4513)
4. Kaydedin

#### Excel İmport İle

1. Excel dosyanızda `color_code` kolonu ekleyin
2. Her varyant için hex kod girin
3. **Admin > Ürün Varyantları > Excel Import** ile yükleyin

#### Manuel SQL İle

```sql
-- Örnek: Meşe rengine kod atama
UPDATE product_variants
SET color_code = '#8B4513'
WHERE color = 'Meşe';

-- Örnek: Ceviz rengine kod atama
UPDATE product_variants
SET color_code = '#654321'
WHERE color = 'Ceviz';
```

---

## 🔧 Teknik Detaylar

### Yeni Dosyalar

| Dosya | Açıklama |
|-------|----------|
| `/renk-kartelasi.php` | Frontend renk kartelası sayfası |
| `/admin/color-palette.php` | Admin yönetim paneli |
| `/database_color_palette.sql` | Database schema |
| `/RENK_KARTELASI_KURULUM.md` | Bu dosya |

### Değişiklik Yapılan Dosyalar

| Dosya | Değişiklik |
|-------|-----------|
| `/includes/header.php` | Desktop ve mobile menüye "Renk Kartelamız" linki eklendi (satır 282, 376) |
| `/admin/includes/header.php` | Admin menüye "Renk Kartelası" linki eklendi (satır 208) |
| `/index.php` | Anasayfaya renk kartelası bölümü eklendi (satır 206-295) |

### Database Tabloları

#### `color_palette_settings`

Renk kartelası genel ayarlarını tutar.

| Kolon | Tip | Açıklama |
|-------|-----|----------|
| id | INT | Primary key |
| title | VARCHAR(255) | Sayfa başlığı |
| subtitle | VARCHAR(500) | Alt başlık |
| description | TEXT | Açıklama |
| hero_image | VARCHAR(500) | Hero resim yolu |
| homepage_image | VARCHAR(500) | Anasayfa resim yolu |
| is_active | TINYINT | Aktif durumu |
| show_on_homepage | TINYINT | Anasayfa görünürlüğü |

#### `color_palette_categories`

Renk kartelasında gösterilecek kategorileri tutar.

| Kolon | Tip | Açıklama |
|-------|-----|----------|
| id | INT | Primary key |
| category_id | INT | Kategori ID (FK) |
| sort_order | INT | Sıralama |
| is_active | TINYINT | Aktif durumu |

---

## 🎯 Örnekler

### Örnek Kullanım Senaryosu

1. **Admin Panele Giriş**
   - Admin > Renk Kartelası

2. **Ayarları Yapılandırma**
   - Başlık: "Renk Kartelamız"
   - Alt Başlık: "Geniş Renk Seçeneklerimizi Keşfedin"
   - Hero ve homepage resimleri yükleyin

3. **Kategorileri Seçme**
   - "Ahşap Tamir Macunları" kategorisini ekleyin (Sıra: 0)
   - "Zemin Koruyucu Keçeler" kategorisini ekleyin (Sıra: 1)
   - "Yapışkanlı Tapalar" kategorisini ekleyin (Sıra: 2)

4. **Renk Kodlarını Kontrol**
   - Admin > Ürün Varyantları
   - Her varyantın color_code alanının dolu olduğundan emin olun

5. **Önizleme ve Yayınlama**
   - "Renk Kartelası Sayfasını Görüntüle" ile kontrol edin
   - Gerekirse düzenlemeler yapın

---

## ❓ Sık Sorulan Sorular

### Renk kartelası sayfası boş görünüyor?

**Çözüm**:
1. Admin panelde en az bir kategori eklediğinizden emin olun
2. Seçili kategorilerin "Aktif" olduğunu kontrol edin
3. O kategorilerdeki ürünlerin color_code değerlerinin dolu olduğunu kontrol edin

### Anasayfada renk kartelası bölümü görünmüyor?

**Çözüm**:
1. Admin > Renk Kartelası > "Anasayfada göster" checkbox'ını işaretleyin
2. "Renk kartelası sayfası aktif" checkbox'ını işaretleyin

### Renk kutuları beyaz görünüyor?

**Çözüm**:
Product_variants tablosunda color_code değerlerini kontrol edin:

```sql
SELECT id, name, color, color_code
FROM product_variants
WHERE color_code IS NULL OR color_code = '';
```

Boş olanlar için hex kod ekleyin.

### Kategoride renk sayısı 0 olarak görünüyor?

**Çözüm**:
O kategorideki hiçbir ürün varyantının color_code'u yoktur. Varyantlara renk kodu ekleyin.

---

## 🛠️ Bakım ve Güncellemeler

### Yeni Renk Ekleme

1. Admin > Ürün Varyantları
2. Yeni varyant ekleyin veya mevcut varyantı düzenleyin
3. Color ve Color Code alanlarını doldurun
4. Kaydet

### Kategori Sıralamasını Değiştirme

1. Admin > Renk Kartelası
2. Seçili kategoriler listesinde sıra numaralarını değiştirin
3. "Sıralamayı Kaydet" butonuna tıklayın

### Görselleri Güncelleme

1. Admin > Renk Kartelası
2. Hero Image veya Homepage Image için yeni resim seçin
3. "Ayarları Kaydet" butonuna tıklayın

---

## 📞 Destek

Herhangi bir sorun yaşarsanız veya ek özellik talep ederseniz lütfen iletişime geçin.

---

## 📄 Lisans

Bu özellik ECEDEKOR projesi için özel olarak geliştirilmiştir.

**Geliştirme Tarihi**: 17 Kasım 2025
**Versiyon**: 1.0.0
