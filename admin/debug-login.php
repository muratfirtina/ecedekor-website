<?php
require_once '../includes/config.php';

// Debug mode - show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug Login Test</h2>";

// Test direct login
if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p><strong>Posted Data:</strong></p>";
    echo "<p>Username: " . htmlspecialchars($username) . "</p>";
    echo "<p>Password: " . htmlspecialchars($password) . "</p>";
    
    if ($username && $password) {
        try {
            $admin = fetchOne("SELECT * FROM admin_users WHERE username = ? OR email = ?", [$username, $username]);
            
            if ($admin) {
                echo "<p style='color: green;'>✅ User found in database</p>";
                echo "<p>DB Username: " . htmlspecialchars($admin['username']) . "</p>";
                echo "<p>DB Email: " . htmlspecialchars($admin['email']) . "</p>";
                
                if (password_verify($password, $admin['password'])) {
                    echo "<p style='color: green;'>✅ Password is correct!</p>";
                    
                    // Set session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];
                    
                    echo "<p style='color: green;'>✅ Session set successfully!</p>";
                    echo "<p><a href='dashboard.php'>Go to Dashboard →</a></p>";
                    
                } else {
                    echo "<p style='color: red;'>❌ Password is incorrect</p>";
                    
                    // Try to reset password
                    $newHash = password_hash('admin123', PASSWORD_DEFAULT);
                    query("UPDATE admin_users SET password = ? WHERE id = ?", [$newHash, $admin['id']]);
                    echo "<p style='color: blue;'>🔄 Password reset to 'admin123'. Try again.</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ User not found</p>";
                
                // Create admin user
                $hash = password_hash('admin123', PASSWORD_DEFAULT);
                query("INSERT INTO admin_users (username, email, password) VALUES (?, ?, ?)", 
                      ['admin', 'admin@test.com', $hash]);
                echo "<p style='color: green;'>✅ Admin user created. Try login again.</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p>Enter login credentials to test:</p>";
}
?>

<form method="POST" style="max-width: 400px; margin: 20px 0;">
    <div style="margin-bottom: 15px;">
        <label>Username:</label><br>
        <input type="text" name="username" value="admin" style="width: 100%; padding: 8px;">
    </div>
    <div style="margin-bottom: 15px;">
        <label>Password:</label><br>
        <input type="password" name="password" value="admin123" style="width: 100%; padding: 8px;">
    </div>
    <button type="submit" style="background: blue; color: white; padding: 10px 20px; border: none; cursor: pointer;">
        Test Login
    </button>
</form>

<p><a href="login.php">← Back to Normal Login</a></p>
