<?php
require_once 'includes/config.php';

echo "<h1>Kategori Debug</h1>";

// Tüm kategorileri listele
$categories = fetchAll("SELECT * FROM categories ORDER BY sort_order");

echo "<h2>Tüm Kategoriler</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Slug</th><th>Active</th><th>Link</th></tr>";

foreach ($categories as $category) {
    $link = BASE_URL . "/kategori/" . $category['slug'];
    echo "<tr>";
    echo "<td>" . $category['id'] . "</td>";
    echo "<td>" . htmlspecialchars($category['name']) . "</td>";
    echo "<td>" . htmlspecialchars($category['slug']) . "</td>";
    echo "<td>" . ($category['is_active'] ? 'Evet' : 'Hayır') . "</td>";
    echo "<td><a href='" . $link . "' target='_blank'>Test Et</a></td>";
    echo "</tr>";
}

echo "</table>";

// URL parsing test
echo "<h2>URL Parsing Test</h2>";
$testUrls = [
    '/kategori/ahsap-tamir-macunlari',
    '/kategori/tup-tamir-macunlari',
    '/kategori/zemin-koruyucu-keceler',
    '/kategori/yapiskanli-tapalar'
];

foreach ($testUrls as $testUrl) {
    if (preg_match('/^\/kategori\/([a-zA-Z0-9-]+)\/?$/', $testUrl, $matches)) {
        $slug = $matches[1];
        $category = fetchOne("SELECT name FROM categories WHERE slug = ? AND is_active = 1", [$slug]);
        echo "<p><strong>URL:</strong> " . $testUrl . " → <strong>Slug:</strong> " . $slug;
        if ($category) {
            echo " → <strong>Kategori:</strong> " . htmlspecialchars($category['name']);
        } else {
            echo " → <strong>Kategori:</strong> Bulunamadı";
        }
        echo "</p>";
    }
}

echo "<h2>Apache mod_rewrite Test</h2>";
echo "<p>mod_rewrite aktif mi?: " . (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) ? 'Evet' : 'Bilinmiyor') . "</p>";

echo "<h2>Request Info</h2>";
echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>PATH_INFO:</strong> " . ($_SERVER['PATH_INFO'] ?? 'Yok') . "</p>";

echo "<br><a href='" . BASE_URL . "'>← Ana Sayfaya Dön</a>";
?>
