<?php
require_once 'includes/config.php';

// Debug: Session ve CSRF token durumunu kontrol et
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "Session Status: " . session_status() . "\n";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Name: " . session_name() . "\n";
    echo "Host: " . $_SERVER['HTTP_HOST'] . "\n";
    echo "CSRF Token in Session: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "\n";
    $generatedToken = generateCSRFToken();
    echo "Generated Token: " . $generatedToken . "\n";
    echo "Token Length: " . strlen($generatedToken) . "\n";
    echo "Session Array: " . print_r($_SESSION, true) . "\n";
    echo "</pre>";
    exit;
}

// Auto-test login with debug_login URL
if (isset($_GET['debug_login']) && !$_POST) {
    // Otomatik olarak admin/admin123 ile login deneyelim
    $_POST['username'] = 'admin';
    $_POST['password'] = 'admin123';
    $_POST['csrf_token'] = generateCSRFToken();
}

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Check for error messages from redirects
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'insufficient_permissions':
            $error = 'Bu işlem için yeterli yetkiye sahip değilsiniz.';
            break;
    }
}

if ($_POST) {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Debug POST data
    if (isset($_GET['debug_login'])) {
        echo "<pre>";
        echo "=== POST DEBUG ===\n";
        echo "Username: " . $username . "\n";
        echo "Password Length: " . strlen($password) . "\n";
        echo "POST CSRF Token: " . $csrf_token . "\n";
        echo "Session CSRF Token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "\n";
        echo "Tokens Match: " . (verifyCSRFToken($csrf_token) ? 'YES' : 'NO') . "\n";
        echo "\n=== USER LOOKUP ===\n";
        
        if ($username) {
            $admin = fetchOne("SELECT id, username, email, role, is_active, password FROM admin_users WHERE username = ? OR email = ?", [$username, $username]);
            if ($admin) {
                echo "User found: YES\n";
                echo "User ID: " . $admin['id'] . "\n";
                echo "Username: " . $admin['username'] . "\n";
                echo "Email: " . $admin['email'] . "\n";
                echo "Role: " . $admin['role'] . "\n";
                echo "Is Active: " . ($admin['is_active'] ? 'YES' : 'NO') . "\n";
                echo "Password Hash: " . substr($admin['password'], 0, 20) . "...\n";
                if ($password) {
                    echo "Password Verify: " . (password_verify($password, $admin['password']) ? 'YES' : 'NO') . "\n";
                }
            } else {
                echo "User found: NO\n";
            }
        }
        echo "</pre>";
        exit;
    }
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin. (Token: ' . substr($csrf_token, 0, 10) . '... / Session: ' . substr($_SESSION['csrf_token'] ?? 'NONE', 0, 10) . '...)';
    } else if ($username && $password) {
        $admin = fetchOne("SELECT * FROM admin_users WHERE username = ? OR email = ?", [$username, $username]);
        
        if ($admin && password_verify($password, $admin['password'])) {
            if (!$admin['is_active']) {
                $error = 'Hesabınız devre dışı bırakılmış. Lütfen yönetici ile iletişime geçin.';
            } else {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_full_name'] = trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? ''));
                
                // Log session and update last login
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                logUserSession($admin['id'], $ipAddress, $userAgent);
                updateLastLogin($admin['id']);
                
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı.';
        }
    } else {
        $error = 'Lütfen tüm alanları doldurun.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - ECEDEKOR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="mb-4">
                <i class="fas fa-shield-alt text-4xl text-indigo-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-black mb-2">Admin Paneli</h1>
            <p class="text-gray-600">ECEDEKOR Yönetim Sistemi</p>
            <p class="text-sm text-gray-500 mt-2">admin.ecedekor.com.tr</p>
        </div>
        
        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $error; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    Kullanıcı Adı veya E-posta
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        name="username" 
                        id="username" 
                        required
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300"
                        placeholder="Kullanıcı adınızı girin"
                    >
                </div>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Şifre
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300"
                        placeholder="Şifrenizi girin"
                    >
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        id="remember" 
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Beni hatırla
                    </label>
                </div>
                <a href="<?php echo MAIN_SITE_URL; ?>" class="text-sm text-indigo-600 hover:text-indigo-500 transition duration-300">
                    Ana Siteye Dön
                </a>
            </div>
            
            <button 
                type="submit"
                class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-300 font-semibold"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>
                Giriş Yap
            </button>
        </form>
        
        <!-- Debug Buttons (Geliştirme için) -->
        <div class="mt-6 text-center space-x-2">
            <a href="?debug=1" class="text-xs text-gray-500 hover:text-gray-700">Debug Session</a>
            <span class="text-gray-300">|</span>
            <a href="?debug_login=1" class="text-xs text-gray-500 hover:text-gray-700">Test Login Debug</a>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> ECEDEKOR. Tüm hakları saklıdır.</p>
        </div>
    </div>
    
    <script>
        // Auto focus on username field
        document.getElementById('username').focus();
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Lütfen tüm alanları doldurun.');
            }
        });
    </script>
</body>
</html>