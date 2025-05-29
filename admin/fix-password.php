<?php
// Admin123 şifresi için yeni hash oluştur
$password = 'admin123';
$newHash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Şifre Hash Güncelleme</h2>";
echo "<p><strong>Şifre:</strong> admin123</p>";
echo "<p><strong>Yeni Hash:</strong> " . $newHash . "</p>";

// Mevcut hash'i kontrol et
$currentHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "<p><strong>Mevcut Hash:</strong> " . $currentHash . "</p>";
echo "<p><strong>Mevcut Hash Doğru mu:</strong> " . (password_verify($password, $currentHash) ? 'EVET' : 'HAYIR') . "</p>";
echo "<p><strong>Yeni Hash Doğru mu:</strong> " . (password_verify($password, $newHash) ? 'EVET' : 'HAYIR') . "</p>";

echo "<h3>SQL Komutu:</h3>";
echo "<textarea rows='5' cols='80' readonly>";
echo "UPDATE admin_users SET password = '" . $newHash . "' WHERE username = 'admin';";
echo "</textarea>";

echo "<h3>Veya Manuel Test:</h3>";
require_once 'includes/config.php';

if (isset($_GET['update_password'])) {
    $result = query("UPDATE admin_users SET password = ? WHERE username = 'admin'", [$newHash]);
    if ($result) {
        echo "<p style='color: green;'>✅ Şifre başarıyla güncellendi!</p>";
        echo "<p><a href='login.php'>Login sayfasına git</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Güncelleme başarısız!</p>";
    }
} else {
    echo "<p><a href='?update_password=1' onclick='return confirm(\"Şifreyi güncellemek istediğinizden emin misiniz?\")'>Şifreyi Otomatik Güncelle</a></p>";
}
?>
