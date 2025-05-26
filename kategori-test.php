<?php
require_once 'includes/config.php';

echo "<h1>Kategori URL Test</h1>";

// Mevcut kategorileri ve doğru URL'lerini göster
$categories = fetchAll("SELECT * FROM categories ORDER BY sort_order");

echo "<h2>Mevcut Kategoriler ve Doğru URL'leri:</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background-color: #f0f0f0;'><th>Kategori Adı</th><th>Slug</th><th>Doğru URL</th><th>Test Et</th></tr>";

foreach ($categories as $category) {
    $correctUrl = "http://localhost:8888/ecedekor-website/kategori/" . $category['slug'];
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($category['name']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($category['slug']) . "</td>";
    echo "<td><code>" . $correctUrl . "</code></td>";
    echo "<td><a href='" . $correctUrl . "' target='_blank' style='color: red;'>Test Et →</a></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Hata Ayıklama Bilgisi:</h2>";
echo "<p><strong>Mevcut URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";

// .htaccess kontrolü
echo "<h2>.htaccess Kontrol:</h2>";
$htaccessPath = dirname(__FILE__) . '/.htaccess';
if (file_exists($htaccessPath)) {
    echo "<p style='color: green;'>✅ .htaccess dosyası mevcut</p>";
} else {
    echo "<p style='color: red;'>❌ .htaccess dosyası bulunamadı</p>";
}

// Apache mod_rewrite kontrolü
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "<p style='color: green;'>✅ mod_rewrite etkin</p>";
    } else {
        echo "<p style='color: red;'>❌ mod_rewrite etkin değil</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Apache modülleri kontrol edilemiyor</p>";
}

echo "<h2>URL Test:</h2>";
echo "<p>Eğer kategoriler çalışmıyorsa, şu linklerden doğru URL'leri test edin:</p>";
echo "<ul>";
foreach ($categories as $category) {
    echo "<li><a href='" . BASE_URL . "/kategori/" . $category['slug'] . "'>" . htmlspecialchars($category['name']) . "</a></li>";
}
echo "</ul>";

echo "<br><p><a href='" . BASE_URL . "'>← Ana Sayfaya Dön</a></p>";
?>
