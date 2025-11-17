<?php
/**
 * Database Configuration for ECEDEKOR Website - PRODUCTION
 */

// Database credentials - HOSTING BİLGİLERİNİZLE DEĞİŞTİRİN
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'admin');
define('DB_PASSWORD', 'root');
define('DB_NAME', 'ece_db');

// Database connection
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

// URL Configuration for Subdomain
define('MAIN_SITE_URL', 'https://www.ecedekor.com.tr');
define('ADMIN_BASE_URL', 'https://admin.ecedekor.com.tr');
define('BASE_URL', MAIN_SITE_URL); // Ana site için
define('ADMIN_URL', ADMIN_BASE_URL); // Admin için

// Assets URLs
define('ADMIN_ASSETS_URL', ADMIN_BASE_URL . '/assets');
define('MAIN_ASSETS_URL', MAIN_SITE_URL . '/assets');
define('IMAGES_URL', MAIN_ASSETS_URL . '/images');

// File upload paths - Ana sitedeki assets klasörünü kullan
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/../public_html/assets/images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Security settings
define('ADMIN_SESSION_NAME', 'ecedekor_admin');
session_name(ADMIN_SESSION_NAME);

// Güvenlik için admin subdomaininde olmadığımızı kontrol et
if (!isset($_SERVER['HTTP_HOST']) || 
    (strpos($_SERVER['HTTP_HOST'], 'admin.') === false && 
     !in_array(basename($_SERVER['PHP_SELF']), ['login.php']))) {
    
    // Ana siteden admin'e erişim engelle
    if (strpos($_SERVER['REQUEST_URI'], '/admin') !== false) {
        header('Location: ' . ADMIN_BASE_URL);
        exit;
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database helper functions (aynı kalır)
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

// Admin helper functions
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_BASE_URL . '/login.php');
        exit;
    }
}

// CSRF token functions
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// File upload helper - Ana site klasörüne yükle
function uploadFile($file, $directory = 'uploads') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        error_log('Upload error: ' . ($file['error'] ?? 'File not set'));
        return false;
    }
    
    // Ana sitenin assets klasörüne yükle
    $uploadDir = UPLOAD_DIR . $directory . '/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log('Could not create upload directory: ' . $uploadDir);
            return false;
        }
    }
    
    if (!is_writable($uploadDir)) {
        error_log('Upload directory is not writable: ' . $uploadDir);
        return false;
    }
    
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if ($fileSize > MAX_FILE_SIZE) {
        error_log('File too large: ' . $fileSize . ' bytes');
        return false;
    }
    
    if (!in_array($fileExt, ALLOWED_EXTENSIONS)) {
        error_log('Invalid file extension: ' . $fileExt);
        return false;
    }
    
    $newFileName = uniqid() . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmp, $uploadPath)) {
        chmod($uploadPath, 0644);
        // Ana site URL'i ile döndür
        $fileUrl = MAIN_SITE_URL . '/assets/images/' . $directory . '/' . $newFileName;
        error_log('File uploaded successfully: ' . $fileUrl);
        return $fileUrl;
    } else {
        error_log('Failed to move uploaded file from ' . $fileTmp . ' to ' . $uploadPath);
        return false;
    }
}

// Utility functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateSlug($string) {
    $string = mb_strtolower($string, 'UTF-8');
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

// User management functions (aynı kalır)
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
    if (hasRole('admin')) return true;
    
    $result = fetchOne("SELECT id FROM user_permissions WHERE user_id = ? AND permission = ?", 
                      [$_SESSION['admin_id'], $permission]);
    return $result !== null;
}

function requireRole($role) {
    if (!hasRole($role)) {
        header('Location: ' . ADMIN_BASE_URL . '/login.php?error=insufficient_permissions');
        exit;
    }
}

function requirePermission($permission) {
    if (!hasPermission($permission)) {
        header('Location: ' . ADMIN_BASE_URL . '/login.php?error=insufficient_permissions');
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