<?php
require_once 'includes/config.php';

echo "<h1>Görsel Yolları Hata Ayıklama</h1>";

// Tüm ayarları yazdır
$settings = fetchAll("SELECT * FROM site_settings WHERE setting_key LIKE '%image%' OR setting_key LIKE '%logo%'");
echo "<h2>Veritabanındaki Görsel Ayarları</h2>";
echo "<pre>";
foreach($settings as $setting) {
    echo "Ayar: " . $setting['setting_key'] . " = " . $setting['setting_value'] . "\n";
    echo "Tam URL: " . BASE_URL . $setting['setting_value'] . "\n\n";
    
    // Test görsel
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
    echo "<p>Test: " . $setting['setting_key'] . "</p>";
    echo "<img src='" . $setting['setting_value'] . "' style='max-width: 300px; height: auto;' alt='Test 1'><br>";
    echo "<p>↑ Sadece değer kullanılarak</p>";
    
    echo "<img src='" . BASE_URL . $setting['setting_value'] . "' style='max-width: 300px; height: auto;' alt='Test 2'><br>";
    echo "<p>↑ BASE_URL + değer kullanılarak</p>";
    echo "</div>";
}

// Sabit değerler
echo "<h2>Sabit Görsel Yolları</h2>";
echo "BASE_URL: " . BASE_URL . "<br>";
echo "IMAGES_URL: " . IMAGES_URL . "<br><br>";

// Test görsel
echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<p>Test: IMAGES_URL/logo.png</p>";
echo "<img src='" . IMAGES_URL . "/logo.png' style='max-width: 300px; height: auto;' alt='Test Logo'><br>";
echo "<p>↑ IMAGES_URL kullanılarak</p>";
echo "</div>";

// uploadFile fonksiyonu incelemesi
echo "<h2>uploadFile Fonksiyonu İncelemesi</h2>";
echo "<pre>";
$file_function = "
function uploadFile(\$file, \$directory = 'uploads') {
    if (!isset(\$file) || \$file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    \$uploadDir = UPLOAD_DIR . \$directory . '/';
    if (!is_dir(\$uploadDir)) {
        mkdir(\$uploadDir, 0755, true);
    }
    
    \$fileName = \$file['name'];
    \$fileSize = \$file['size'];
    \$fileTmp = \$file['tmp_name'];
    \$fileExt = strtolower(pathinfo(\$fileName, PATHINFO_EXTENSION));
    
    // Check file size
    if (\$fileSize > MAX_FILE_SIZE) {
        return false;
    }
    
    // Check file extension
    if (!in_array(\$fileExt, ALLOWED_EXTENSIONS)) {
        return false;
    }
    
    // Generate unique filename
    \$newFileName = uniqid() . '.' . \$fileExt;
    \$uploadPath = \$uploadDir . \$newFileName;
    
    if (move_uploaded_file(\$fileTmp, \$uploadPath)) {
        return '/assets/images/' . \$directory . '/' . \$newFileName;
    }
    
    return false;
}";
echo htmlspecialchars($file_function);
echo "</pre>";
echo "UPLOAD_DIR: " . UPLOAD_DIR . "<br>";

// Fiziksel olarak dosyaları kontrol et
echo "<h2>Fiziksel Dosya Kontrolü</h2>";
$upload_folders = ['logo', 'hero', 'about', 'contact', 'homepage'];
foreach($upload_folders as $folder) {
    $dir = UPLOAD_DIR . $folder;
    echo "<h3>$folder Klasörü</h3>";
    if (is_dir($dir)) {
        echo "Klasör mevcut: $dir<br>";
        $files = scandir($dir);
        echo "Dosyalar: <br>";
        echo "<ul>";
        foreach($files as $file) {
            if ($file != "." && $file != "..") {
                echo "<li>$file - " . filesize($dir . "/" . $file) . " bytes</li>";
                // Dosya izinlerini göster
                echo "<small>İzinler: " . substr(sprintf('%o', fileperms($dir . "/" . $file)), -4) . "</small>";
            }
        }
        echo "</ul>";
    } else {
        echo "Klasör mevcut değil: $dir<br>";
    }
}
?>