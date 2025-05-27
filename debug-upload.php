<?php
require_once 'includes/config.php';

echo "<h2>Upload Debug</h2>";

// Check upload directory
echo "<h3>Upload Directory Check</h3>";
$uploadBaseDir = UPLOAD_DIR;
echo "<p><strong>UPLOAD_DIR:</strong> " . $uploadBaseDir . "</p>";
echo "<p><strong>Directory exists:</strong> " . (is_dir($uploadBaseDir) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Directory writable:</strong> " . (is_writable($uploadBaseDir) ? 'YES' : 'NO') . "</p>";

// Check subdirectories
$subDirs = ['logo', 'hero', 'about', 'contact', 'footer'];
foreach ($subDirs as $subDir) {
    $fullPath = $uploadBaseDir . $subDir . '/';
    echo "<p><strong>$subDir directory:</strong> " . ($fullPath) . "</p>";
    echo "<p>- Exists: " . (is_dir($fullPath) ? 'YES' : 'NO') . "</p>";
    echo "<p>- Writable: " . (is_writable($fullPath) ? 'YES' : 'NO') . "</p>";
    
    // Try to create if not exists
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            echo "<p style='color: green;'>- Created successfully</p>";
        } else {
            echo "<p style='color: red;'>- Failed to create</p>";
        }
    }
}

// PHP Upload settings
echo "<h3>PHP Upload Settings</h3>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>max_file_uploads:</strong> " . ini_get('max_file_uploads') . "</p>";
echo "<p><strong>file_uploads:</strong> " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "</p>";

// Test upload form
if ($_POST && isset($_FILES['test_file'])) {
    echo "<h3>Upload Test Result</h3>";
    
    $result = uploadFile($_FILES['test_file'], 'test');
    if ($result) {
        echo "<p style='color: green;'><strong>SUCCESS:</strong> File uploaded to: " . $result . "</p>";
        echo "<img src='" . $result . "' style='max-width: 200px; max-height: 200px;'>";
    } else {
        echo "<p style='color: red;'><strong>FAILED:</strong> Upload failed</p>";
    }
    
    echo "<h4>Upload Details:</h4>";
    echo "<pre>";
    print_r($_FILES['test_file']);
    echo "</pre>";
}
?>

<h3>Test Upload</h3>
<form method="POST" enctype="multipart/form-data">
    <p>
        <label>Select image file:</label><br>
        <input type="file" name="test_file" accept="image/*" required>
    </p>
    <p>
        <button type="submit">Test Upload</button>
    </p>
</form>

<p><a href="admin/settings.php">← Back to Settings</a></p>
