<?php
require_once 'includes/config.php';

echo "<h2>Settings Test</h2>";

// Test logo path
$logoPath = getSetting('logo_path');
echo "<p><strong>Logo Path:</strong> " . ($logoPath ?: 'BOŞ') . "</p>";

if ($logoPath) {
    echo "<p><strong>Logo URL Test:</strong></p>";
    echo "<img src='$logoPath' style='max-width: 200px; border: 1px solid #000;'>";
    
    // Check if file exists physically
    $relativePath = str_replace(BASE_URL . '/assets/images/', '', $logoPath);
    $physicalPath = dirname(__FILE__) . '/assets/images/' . $relativePath;
    echo "<p><strong>Physical Path:</strong> $physicalPath</p>";
    echo "<p><strong>File Exists:</strong> " . (file_exists($physicalPath) ? 'YES' : 'NO') . "</p>";
}

// Directory info
echo "<h3>Directory Info</h3>";
echo "<p><strong>UPLOAD_DIR:</strong> " . UPLOAD_DIR . "</p>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";

$assetDir = dirname(__FILE__) . '/assets/images/';
echo "<p><strong>Assets Directory:</strong> $assetDir</p>";
echo "<p><strong>Assets Exists:</strong> " . (is_dir($assetDir) ? 'YES' : 'NO') . "</p>";
?>