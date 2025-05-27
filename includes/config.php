<?php
/**
 * Database Configuration for ECEDEKOR Website
 */

// Database credentials
define('DB_HOST', 'localhost:8889');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_NAME', 'ecedekor_db');

// Try to connect to database
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions
function query($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return false;
    }
}

function fetchOne($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

function fetchAll($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

function getLastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}

function getSetting($key, $default = '') {
    $result = fetchOne("SELECT setting_value FROM site_settings WHERE setting_key = ?", [$key]);
    return $result ? $result['setting_value'] : $default;
}

function updateSetting($key, $value) {
    return query("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
}

// Website base URL
define('BASE_URL', 'http://localhost:8888/ecedekor-website');
define('ADMIN_URL', BASE_URL . '/admin');
define('ASSETS_URL', BASE_URL . '/assets');
define('IMAGES_URL', ASSETS_URL . '/images');

// File upload settings - DÜZELTİLDİ
define('UPLOAD_DIR', dirname(__DIR__) . '/assets/images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Security settings
define('ADMIN_SESSION_NAME', 'ecedekor_admin');
session_name(ADMIN_SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check admin login
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Helper function to require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

// Helper function to generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Helper function to verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// File upload helper
function uploadFile($file, $directory = 'uploads') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        error_log('Upload error: ' . ($file['error'] ?? 'File not set'));
        return false;
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = UPLOAD_DIR . $directory . '/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log('Could not create upload directory: ' . $uploadDir);
            return false;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        error_log('Upload directory is not writable: ' . $uploadDir);
        return false;
    }
    
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Check file size
    if ($fileSize > MAX_FILE_SIZE) {
        error_log('File too large: ' . $fileSize . ' bytes');
        return false;
    }
    
    // Check file extension
    if (!in_array($fileExt, ALLOWED_EXTENSIONS)) {
        error_log('Invalid file extension: ' . $fileExt);
        return false;
    }
    
    // Generate unique filename
    $newFileName = uniqid() . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;
    
    // Debug logging
    error_log("Upload attempt - From: $fileTmp To: $uploadPath");
    error_log("Directory: $uploadDir (exists: " . (is_dir($uploadDir) ? 'yes' : 'no') . ")");
    
    if (move_uploaded_file($fileTmp, $uploadPath)) {
        // Set proper file permissions
        chmod($uploadPath, 0644);
        
        // Return full URL to the file
        $fileUrl = BASE_URL . '/assets/images/' . $directory . '/' . $newFileName;
        error_log('File uploaded successfully: ' . $fileUrl);
        return $fileUrl;
    } else {
        error_log('Failed to move uploaded file from ' . $fileTmp . ' to ' . $uploadPath);
        return false;
    }
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Generate slug from string
function generateSlug($string) {
    $string = mb_strtolower($string, 'UTF-8');
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

// User role and permission functions
function getUserInfo($userId) {
    return fetchOne("SELECT * FROM admin_users WHERE id = ?", [$userId]);
}

function hasRole($role) {
    if (!isAdminLoggedIn()) return false;
    $user = getUserInfo($_SESSION['admin_id']);
    return $user && $user['role'] === $role;
}

function hasPermission($permission) {
    if (!isAdminLoggedIn()) return false;
    
    // Admins have all permissions
    if (hasRole('admin')) return true;
    
    $result = fetchOne("SELECT id FROM user_permissions WHERE user_id = ? AND permission = ?", 
                      [$_SESSION['admin_id'], $permission]);
    return $result !== null;
}

function requireRole($role) {
    if (!hasRole($role)) {
        header('Location: ' . ADMIN_URL . '/login.php?error=insufficient_permissions');
        exit;
    }
}

function requirePermission($permission) {
    if (!hasPermission($permission)) {
        header('Location: ' . ADMIN_URL . '/login.php?error=insufficient_permissions');
        exit;
    }
}

function logUserSession($userId, $ipAddress, $userAgent) {
    return query("INSERT INTO user_sessions (user_id, ip_address, user_agent) VALUES (?, ?, ?)", 
                [$userId, $ipAddress, $userAgent]);
}

function updateLastLogin($userId) {
    return query("UPDATE admin_users SET last_login = NOW() WHERE id = ?", [$userId]);
}

function getActiveUsers() {
    return fetchAll("SELECT u.*, s.login_time FROM admin_users u 
                    LEFT JOIN user_sessions s ON u.id = s.user_id AND s.is_active = 1 
                    WHERE u.is_active = 1 ORDER BY u.role, u.username");
}

function getUserPermissions($userId) {
    return fetchAll("SELECT permission FROM user_permissions WHERE user_id = ?", [$userId]);
}

function addUserPermission($userId, $permission) {
    return query("INSERT IGNORE INTO user_permissions (user_id, permission) VALUES (?, ?)", 
                [$userId, $permission]);
}

function removeUserPermission($userId, $permission) {
    return query("DELETE FROM user_permissions WHERE user_id = ? AND permission = ?", 
                [$userId, $permission]);
}

// Available permissions
function getAvailablePermissions() {
    return [
        'categories_manage' => 'Kategori Yönetimi',
        'products_manage' => 'Ürün Yönetimi',
        'variants_manage' => 'Varyant Yönetimi',
        'testimonials_manage' => 'Yorum Yönetimi',
        'homepage_manage' => 'Ana Sayfa Yönetimi',
        'settings_manage' => 'Site Ayarları',
        'files_manage' => 'Dosya Yönetimi',
        'users_manage' => 'Kullanıcı Yönetimi',
        'users_create' => 'Kullanıcı Oluşturma',
        'reports_view' => 'Rapor Görüntüleme'
    ];
}
?>