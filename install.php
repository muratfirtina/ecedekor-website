<?php
/**
 * ECEDEKOR Website Kurulum Scripti
 * Bu script sadece bir kez çalıştırılmalıdır.
 */

// Kurulum tamamlandı mı kontrol et
if (file_exists('install.lock')) {
    die('Kurulum daha önce tamamlanmış. Güvenlik için bu dosyayı silin.');
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Veritabanı bağlantısı test et
function testDatabaseConnection($host, $username, $password, $database) {
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Veritabanını oluştur
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$database`");
        
        return $pdo;
    } catch (PDOException $e) {
        return false;
    }
}

// SQL dosyasını çalıştır
function runSQLFile($pdo, $filepath) {
    try {
        $sql = file_get_contents($filepath);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        return true;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

// Klasörleri oluştur
function createDirectories() {
    $directories = [
        'assets/images/products',
        'assets/images/categories', 
        'assets/images/variants',
        'assets/images/testimonials',
        'assets/images/homepage',
        'assets/images/uploads',
        'assets/images/logo',
        'assets/images/hero',
        'assets/images/about'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        chmod($dir, 0755);
    }
    return true;
}

// Form gönderildi mi?
if ($_POST) {
    if ($step == 2) {
        // Veritabanı ayarları
        $host = $_POST['db_host'] ?? 'localhost';
        $username = $_POST['db_username'] ?? '';
        $password = $_POST['db_password'] ?? '';
        $database = $_POST['db_name'] ?? '';
        
        if ($host && $username && $database) {
            $pdo = testDatabaseConnection($host, $username, $password, $database);
            if ($pdo) {
                // Mevcut tabloları kontrol et
                $tables = $pdo->query("SHOW TABLES")->fetchAll();
                if (count($tables) > 0) {
                    // Tablolar zaten mevcut, doğrudan devam et
                    $sqlResult = true;
                } else {
                    // Veritabanı tabloları oluştur
                    $sqlResult = runSQLFile($pdo, 'database.sql');
                }
                if ($sqlResult === true) {
                    // Config dosyasını güncelle
                    $configContent = file_get_contents('includes/config.php');
                    $configContent = str_replace("define('DB_HOST', 'localhost');", "define('DB_HOST', '$host');", $configContent);
                    $configContent = str_replace("define('DB_USERNAME', 'root');", "define('DB_USERNAME', '$username');", $configContent);
                    $configContent = str_replace("define('DB_PASSWORD', '');", "define('DB_PASSWORD', '$password');", $configContent);
                    $configContent = str_replace("define('DB_NAME', 'ecedekor_db');", "define('DB_NAME', '$database');", $configContent);
                    
                    file_put_contents('includes/config.php', $configContent);
                    
                    $success = 'Veritabanı başarıyla kuruldu!';
                    $step = 3;
                } else {
                    $error = 'Veritabanı kurulumu hatası: ' . $sqlResult;
                }
            } else {
                $error = 'Veritabanı bağlantısı kurulamadı. Lütfen bilgileri kontrol edin.';
            }
        } else {
            $error = 'Lütfen tüm veritabanı bilgilerini girin.';
        }
    } elseif ($step == 3) {
        // Site ayarları
        $site_url = rtrim($_POST['site_url'] ?? '', '/');
        $admin_username = $_POST['admin_username'] ?? '';
        $admin_email = $_POST['admin_email'] ?? '';
        $admin_password = $_POST['admin_password'] ?? '';
        
        if ($site_url && $admin_username && $admin_email && $admin_password) {
            // Config dosyasında URL'yi güncelle
            $configContent = file_get_contents('includes/config.php');
            $configContent = str_replace("define('BASE_URL', 'http://localhost/ecedekor-website');", "define('BASE_URL', '$site_url');", $configContent);
            file_put_contents('includes/config.php', $configContent);
            
            // Admin kullanıcısını güncelle
            require_once 'includes/config.php';
            $hashedPassword = password_hash($admin_password, PASSWORD_DEFAULT);
            query("UPDATE admin_users SET username = ?, email = ?, password = ? WHERE id = 1", [$admin_username, $admin_email, $hashedPassword]);
            
            // Klasörleri oluştur
            createDirectories();
            
            // Kurulum tamamlandı
            file_put_contents('install.lock', date('Y-m-d H:i:s'));
            
            $success = 'Kurulum başarıyla tamamlandı!';
            $step = 4;
        } else {
            $error = 'Lütfen tüm bilgileri girin.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECEDEKOR Kurulum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-red-500 to-purple-600 min-h-screen">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-2xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cogs text-2xl text-red-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-black mb-2">ECEDEKOR Kurulum</h1>
                <p class="text-gray-600">Website kurulum sihirbazına hoş geldiniz</p>
            </div>
            
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between text-sm">
                    <span class="<?php echo $step >= 1 ? 'text-red-600' : 'text-gray-400'; ?>">Hoş Geldiniz</span>
                    <span class="<?php echo $step >= 2 ? 'text-red-600' : 'text-gray-400'; ?>">Veritabanı</span>
                    <span class="<?php echo $step >= 3 ? 'text-red-600' : 'text-gray-400'; ?>">Site Ayarları</span>
                    <span class="<?php echo $step >= 4 ? 'text-red-600' : 'text-gray-400'; ?>">Tamamlandı</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-red-600 h-2 rounded-full transition-all duration-300" style="width: <?php echo ($step / 4) * 100; ?>%"></div>
                </div>
            </div>
            
            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <!-- Step 1: Welcome -->
            <?php if ($step == 1): ?>
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-black mb-4">Kuruluma Hoş Geldiniz</h2>
                    <p class="text-gray-600 mb-8">
                        ECEDEKOR website sistemi kurulumuna başlamak için hazır mısınız? 
                        Bu süreç birkaç dakika sürecek ve sitenizi kullanıma hazır hale getirecek.
                    </p>
                    
                    <div class="bg-red-50 rounded-lg p-6 mb-8">
                        <h3 class="font-semibold text-red-900 mb-3">Kurulum öncesi gereksinimler:</h3>
                        <ul class="text-left text-red-800 space-y-2">
                            <li><i class="fas fa-check text-green-500 mr-2"></i>PHP 7.4 veya üstü</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>MySQL 5.7 veya üstü</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Apache mod_rewrite</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Dosya yazma izinleri</li>
                        </ul>
                    </div>
                    
                    <a href="?step=2" class="bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition duration-300 inline-block">
                        <i class="fas fa-arrow-right mr-2"></i>Kuruluma Başla
                    </a>
                </div>
            
            <!-- Step 2: Database Configuration -->
            <?php elseif ($step == 2): ?>
                <div>
                    <h2 class="text-2xl font-bold text-black mb-4">Veritabanı Ayarları</h2>
                    <p class="text-gray-600 mb-6">Veritabanı bağlantı bilgilerinizi girin:</p>
                    
                    <form method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Veritabanı Sunucusu</label>
                            <input type="text" name="db_host" value="localhost" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kullanıcı Adı</label>
                            <input type="text" name="db_username" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Şifre</label>
                            <input type="password" name="db_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Veritabanı Adı</label>
                            <input type="text" name="db_name" value="ecedekor_db" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        
                        <div class="flex space-x-4">
                            <a href="?step=1" class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-lg text-center hover:bg-gray-400 transition duration-300">
                                <i class="fas fa-arrow-left mr-2"></i>Geri
                            </a>
                            <button type="submit" class="flex-1 bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-300">
                                <i class="fas fa-database mr-2"></i>Veritabanını Kur
                            </button>
                        </div>
                    </form>
                </div>
            
            <!-- Step 3: Site Configuration -->
            <?php elseif ($step == 3): ?>
                <div>
                    <h2 class="text-2xl font-bold text-black mb-4">Site Ayarları</h2>
                    <p class="text-gray-600 mb-6">Site URL'si ve admin bilgilerinizi girin:</p>
                    
                    <form method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Site URL'si</label>
                            <input type="url" name="site_url" value="<?php echo 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']); ?>" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Admin Kullanıcı Adı</label>
                            <input type="text" name="admin_username" value="admin" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Admin E-posta</label>
                            <input type="email" name="admin_email" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Admin Şifre</label>
                            <input type="password" name="admin_password" required minlength="6"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        
                        <button type="submit" class="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-300">
                            <i class="fas fa-rocket mr-2"></i>Kurulumu Tamamla
                        </button>
                    </form>
                </div>
            
            <!-- Step 4: Completed -->
            <?php elseif ($step == 4): ?>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-black mb-4">Kurulum Tamamlandı!</h2>
                    <p class="text-gray-600 mb-8">
                        ECEDEKOR website sistemi başarıyla kuruldu. Artık sitenizi kullanmaya başlayabilirsiniz.
                    </p>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <span class="text-yellow-800 font-semibold">Güvenlik Uyarısı:</span>
                        </div>
                        <p class="text-yellow-700 mt-2">
                            Kurulum dosyası (install.php) güvenlik açısından silinmelidir.
                        </p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="index.php" class="flex-1 bg-red-600 text-white py-3 px-4 rounded-lg text-center hover:bg-red-700 transition duration-300">
                            <i class="fas fa-home mr-2"></i>Ana Sayfaya Git
                        </a>
                        <a href="admin/" class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg text-center hover:bg-green-700 transition duration-300">
                            <i class="fas fa-cogs mr-2"></i>Admin Paneli
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
