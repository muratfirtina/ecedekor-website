<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Quick Admin Test</h2>";

// Test database connection and admin user
require_once 'includes/config.php';

try {
    $admin = fetchOne("SELECT * FROM admin_users WHERE username = 'admin'");
    if ($admin) {
        echo "<p style='color: green;'>✅ Admin user found:</p>";
        echo "<ul>";
        echo "<li>Username: " . htmlspecialchars($admin['username']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($admin['email']) . "</li>";
        echo "<li>Password Hash: " . substr($admin['password'], 0, 20) . "...</li>";
        echo "</ul>";
        
        // Test password
        $testPassword = 'admin123';
        if (password_verify($testPassword, $admin['password'])) {
            echo "<p style='color: green;'>✅ Password 'admin123' is correct!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Password 'admin123' does not match. Updating...</p>";
            
            // Update password
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            query("UPDATE admin_users SET password = ? WHERE id = ?", [$newHash, $admin['id']]);
            echo "<p style='color: green;'>✅ Password updated to 'admin123'</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ No admin user found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='admin/'>Go to Admin Panel →</a></p>";
echo "<p><a href='index.php'>Go to Homepage →</a></p>";
?>
