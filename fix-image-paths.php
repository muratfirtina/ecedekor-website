<?php
require_once 'includes/config.php';

echo "<h1>Görsel Yolları Düzeltme</h1>";
echo "<p>Bu script, veritabanındaki görsel yollarını tam URL olarak düzeltecektir.</p>";

// Ayarları düzelt
$settings = fetchAll("SELECT * FROM site_settings WHERE setting_key LIKE '%image%' OR setting_key LIKE '%logo%'");
echo "<h2>Ayarlar Tablosu Düzeltiliyor</h2>";

foreach($settings as $setting) {
    $current_value = $setting['setting_value'];
    $setting_key = $setting['setting_key'];
    
    // Eğer değer / ile başlıyorsa ve http içermiyorsa düzelt
    if ($current_value && strpos($current_value, '/') === 0 && strpos($current_value, 'http') === false) {
        $new_value = BASE_URL . $current_value;
        
        // Güncelle
        if (query("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?", [$new_value, $setting_key])) {
            echo "<p style='color:green'>✅ $setting_key: $current_value → $new_value</p>";
        } else {
            echo "<p style='color:red'>❌ $setting_key güncelleme hatası!</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ $setting_key: Düzeltme gerekmedi ($current_value)</p>";
    }
}

// Homepage sections tablosunu düzelt
$sections = fetchAll("SELECT * FROM homepage_sections");
echo "<h2>Ana Sayfa Bölümleri Tablosu Düzeltiliyor</h2>";

foreach($sections as $section) {
    $current_image = $section['image'];
    $section_id = $section['id'];
    
    // Eğer değer / ile başlıyorsa ve http içermiyorsa düzelt
    if ($current_image && strpos($current_image, '/') === 0 && strpos($current_image, 'http') === false) {
        $new_image = BASE_URL . $current_image;
        
        // Güncelle
        if (query("UPDATE homepage_sections SET image = ? WHERE id = ?", [$new_image, $section_id])) {
            echo "<p style='color:green'>✅ Section ID $section_id: $current_image → $new_image</p>";
        } else {
            echo "<p style='color:red'>❌ Section ID $section_id güncelleme hatası!</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Section ID $section_id: Düzeltme gerekmedi ($current_image)</p>";
    }
}

// Kategoriler tablosunu düzelt
$categories = fetchAll("SELECT * FROM categories");
echo "<h2>Kategoriler Tablosu Düzeltiliyor</h2>";

foreach($categories as $category) {
    $current_image = $category['image'];
    $category_id = $category['id'];
    
    // Eğer değer / ile başlıyorsa ve http içermiyorsa düzelt
    if ($current_image && strpos($current_image, '/') === 0 && strpos($current_image, 'http') === false) {
        $new_image = BASE_URL . $current_image;
        
        // Güncelle
        if (query("UPDATE categories SET image = ? WHERE id = ?", [$new_image, $category_id])) {
            echo "<p style='color:green'>✅ Category ID $category_id: $current_image → $new_image</p>";
        } else {
            echo "<p style='color:red'>❌ Category ID $category_id güncelleme hatası!</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Category ID $category_id: Düzeltme gerekmedi ($current_image)</p>";
    }
}

// Ürün tablosunu düzelt
$products = fetchAll("SELECT * FROM products");
echo "<h2>Ürünler Tablosu Düzeltiliyor</h2>";

foreach($products as $product) {
    $current_image = $product['main_image'];
    $product_id = $product['id'];
    
    // Eğer değer / ile başlıyorsa ve http içermiyorsa düzelt
    if ($current_image && strpos($current_image, '/') === 0 && strpos($current_image, 'http') === false) {
        $new_image = BASE_URL . $current_image;
        
        // Güncelle
        if (query("UPDATE products SET main_image = ? WHERE id = ?", [$new_image, $product_id])) {
            echo "<p style='color:green'>✅ Product ID $product_id: $current_image → $new_image</p>";
        } else {
            echo "<p style='color:red'>❌ Product ID $product_id güncelleme hatası!</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Product ID $product_id: Düzeltme gerekmedi ($current_image)</p>";
    }
}

// Ürün varyantları tablosunu düzelt
$variants = fetchAll("SELECT * FROM product_variants");
echo "<h2>Ürün Varyantları Tablosu Düzeltiliyor</h2>";

foreach($variants as $variant) {
    $current_image = $variant['image'];
    $variant_id = $variant['id'];
    
    // Eğer değer / ile başlıyorsa ve http içermiyorsa düzelt
    if ($current_image && strpos($current_image, '/') === 0 && strpos($current_image, 'http') === false) {
        $new_image = BASE_URL . $current_image;
        
        // Güncelle
        if (query("UPDATE product_variants SET image = ? WHERE id = ?", [$new_image, $variant_id])) {
            echo "<p style='color:green'>✅ Variant ID $variant_id: $current_image → $new_image</p>";
        } else {
            echo "<p style='color:red'>❌ Variant ID $variant_id güncelleme hatası!</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Variant ID $variant_id: Düzeltme gerekmedi ($current_image)</p>";
    }
}

// Ürün görselleri tablosunu düzelt
$images = fetchAll("SELECT * FROM product_images");
echo "<h2>Ürün Görselleri Tablosu Düzeltiliyor</h2>";

foreach($images as $image) {
    $current_path = $image['image_path'];
    $image_id = $image['id'];
    
    // Eğer değer / ile başlıyorsa ve http içermiyorsa düzelt
    if ($current_path && strpos($current_path, '/') === 0 && strpos($current_path, 'http') === false) {
        $new_path = BASE_URL . $current_path;
        
        // Güncelle
        if (query("UPDATE product_images SET image_path = ? WHERE id = ?", [$new_path, $image_id])) {
            echo "<p style='color:green'>✅ Image ID $image_id: $current_path → $new_path</p>";
        } else {
            echo "<p style='color:red'>❌ Image ID $image_id güncelleme hatası!</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Image ID $image_id: Düzeltme gerekmedi ($current_path)</p>";
    }
}

// Müşteri yorumları tablosunu düzelt
$testimonials = fetchAll("SELECT * FROM testimonials");
echo "<h2>Müşteri Yorumları Tablosu Düzeltiliyor</h2>";

foreach($testimonials as $testimonial) {
    $current_image = $testimonial['image'];
    $testimonial_id = $testimonial['id'];
    
    // Eğer değer / ile başlıyorsa ve http içermiyorsa düzelt
    if ($current_image && strpos($current_image, '/') === 0 && strpos($current_image, 'http') === false) {
        $new_image = BASE_URL . $current_image;
        
        // Güncelle
        if (query("UPDATE testimonials SET image = ? WHERE id = ?", [$new_image, $testimonial_id])) {
            echo "<p style='color:green'>✅ Testimonial ID $testimonial_id: $current_image → $new_image</p>";
        } else {
            echo "<p style='color:red'>❌ Testimonial ID $testimonial_id güncelleme hatası!</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Testimonial ID $testimonial_id: Düzeltme gerekmedi ($current_image)</p>";
    }
}

echo "<h2>İşlem Tamamlandı</h2>";
echo "<p>Tüm görsel yolları tam URL'ye dönüştürüldü.</p>";
echo "<p><a href='index.php'>Ana Sayfaya Dön</a></p>";
?>