<?php
/**
 * Simple test file to check PHP syntax
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>PHP Test Page</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
echo "<h3>Database Test</h3>";
try {
    $pdo = new PDO(
        "mysql:host=localhost:8889;charset=utf8mb4",
        'root',
        'root',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'ecedekor_db'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Database 'ecedekor_db' exists!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Database 'ecedekor_db' does not exist. Please create it.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<h3>File Permissions Test</h3>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Is writable: " . (is_writable(__DIR__) ? 'Yes' : 'No') . "</p>";

echo "<h3>PHP Extensions</h3>";
$required_extensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring'];
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>" . ($loaded ? '✅' : '❌') . " $ext: " . ($loaded ? 'Loaded' : 'Not loaded') . "</p>";
}

echo "<p><a href='install.php'>Go to Installation →</a></p>";
?>
