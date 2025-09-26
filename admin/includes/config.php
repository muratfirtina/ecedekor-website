<?php
/**
 * Admin Panel Configuration for ECEDEKOR - SUBDOMAIN
 * admin.ecedekor.com.tr
 */

// Output buffering başlat (session problem çözümü için)
if (!ob_get_level()) {
    ob_start();
}

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'ecedekor_admin');
define('DB_PASSWORD', '2309Mf1983.');
define('DB_NAME', 'ecedekor_db');

// Senkronizasyon için Teklif Sistemi Database Bilgileri
define('TEKLIF_DB_HOST', 'localhost');
define('TEKLIF_DB_USERNAME', 'ecedekor_admin');
define('TEKLIF_DB_PASSWORD', '2309Mf1983.');
define('TEKLIF_DB_NAME', 'ecedekor_teklif');

// Senkronizasyon Ayarları
define('AUTO_SYNC_ENABLED', true);
define('SYNC_LOG_ENABLED', true);

// Database connection (Admin DB)
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

// URL Configuration
define('MAIN_SITE_URL', 'https://www.ecedekor.com.tr');
define('ADMIN_BASE_URL', 'https://admin.ecedekor.com.tr');
define('BASE_URL', MAIN_SITE_URL);
define('ADMIN_URL', ADMIN_BASE_URL);

// Assets URLs
define('ADMIN_ASSETS_URL', MAIN_SITE_URL . '/assets');
define('MAIN_ASSETS_URL', MAIN_SITE_URL . '/assets');
define('IMAGES_URL', MAIN_ASSETS_URL . '/images');

// File upload paths
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/../assets/images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Session ayarları - SADECE session başlatılmadan önce
if (session_status() === PHP_SESSION_NONE) {
    // Session ayarlarını güvenli şekilde ayarla
    if (!headers_sent()) {
        session_name('ecedekor_admin');
        
        // Sadece HTTPS üzerindeyse secure session kullan
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', '1');
        }
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
    }
    
    session_start();
}

// Admin subdomain güvenlik kontrolü
$isValidAdminDomain = strpos($_SERVER['HTTP_HOST'] ?? '', 'admin.ecedekor.com.tr') !== false;

if (!$isValidAdminDomain) {
    // Eğer admin subdomain'i değilse ve login sayfası değilse yönlendir
    $currentPage = basename($_SERVER['PHP_SELF'] ?? '');
    if (!in_array($currentPage, ['login.php', 'index.php'])) {
        header('Location: ' . ADMIN_BASE_URL . '/login.php');
        exit;
    }
}

// Database helper functions
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

// Teklif DB Bağlantısı
function getTeklifDbConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . TEKLIF_DB_HOST . ";dbname=" . TEKLIF_DB_NAME . ";charset=utf8mb4",
            TEKLIF_DB_USERNAME,
            TEKLIF_DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        throw new Exception("Teklif DB bağlantı hatası: " . $e->getMessage());
    }
}

// Teklif DB için Query Helper Fonksiyonları
function teklifQuery($sql, $params = []) {
    try {
        $teklifDb = getTeklifDbConnection();
        $stmt = $teklifDb->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Teklif DB query error: " . $e->getMessage());
        return false;
    }
}

function teklifFetchOne($sql, $params = []) {
    $stmt = teklifQuery($sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

function teklifFetchAll($sql, $params = []) {
    $stmt = teklifQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
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

// Utility functions
function sanitizeInput($input) {
    if ($input === null) {
        return '';
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateSlug($string) {
    if ($string === null) {
        return '';
    }
    $string = mb_strtolower($string, 'UTF-8');
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

// File upload helper
function uploadFile($file, $directory = 'uploads') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $uploadDir = UPLOAD_DIR . $directory . '/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return false;
        }
    }
    
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if ($fileSize > MAX_FILE_SIZE) {
        return false;
    }
    
    if (!in_array($fileExt, ALLOWED_EXTENSIONS)) {
        return false;
    }
    
    $newFileName = uniqid() . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmp, $uploadPath)) {
        chmod($uploadPath, 0644);
        return MAIN_SITE_URL . '/assets/images/' . $directory . '/' . $newFileName;
    }
    
    return false;
}

// User management functions
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
        'reports_view' => 'Rapor Görüntüleme',
        'sync_manage' => 'Senkronizasyon Yönetimi'
    ];
}

// Senkronizasyon fonksiyonları
function checkSyncStatus($variantId) {
    try {
        $teklifDb = getTeklifDbConnection();
        $stmt = $teklifDb->prepare("SELECT COUNT(*) FROM products WHERE admin_variant_id = ?");
        $stmt->execute([$variantId]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Senkronizasyon durumu kontrolü hatası: " . $e->getMessage());
        return false;
    }
}

function logSync($variantId, $action, $status, $message = '') {
    if (!SYNC_LOG_ENABLED) return true;
    
    try {
        return query("INSERT INTO sync_logs (admin_variant_id, sync_type, status, message, created_at) VALUES (?, ?, ?, ?, NOW())", 
                    [$variantId, $action, $status, $message]);
    } catch (Exception $e) {
        error_log("Sync log error: " . $e->getMessage());
        return false;
    }
}

function testCrossDbConnection() {
    try {
        $adminCount = fetchOne("SELECT COUNT(*) as count FROM product_variants");
        $teklifCount = teklifFetchOne("SELECT COUNT(*) as count FROM products");
        
        return [
            'success' => true,
            'admin_variants' => $adminCount['count'] ?? 0,
            'teklif_products' => $teklifCount['count'] ?? 0
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

?>