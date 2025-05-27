<?php
require_once '../includes/config.php';

$pageTitle = 'Profil Ayarları';

$success = '';
$error = '';

// Get current user info
$currentUser = getUserInfo($_SESSION['admin_id']);

if (!$currentUser) {
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

// Handle form submissions
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            
            // Check if email already exists for another user
            $existingUser = fetchOne("SELECT id FROM admin_users WHERE email = ? AND id != ?", [$email, $currentUser['id']]);
            if ($existingUser) {
                $error = 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.';
            } else {
                // Handle avatar upload
                $avatarPath = $currentUser['avatar'];
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $newAvatarPath = uploadFile($_FILES['avatar'], 'avatars');
                    if ($newAvatarPath) {
                        $avatarPath = $newAvatarPath;
                    }
                }
                
                $sql = "UPDATE admin_users SET first_name = ?, last_name = ?, email = ?, phone = ?, avatar = ?, updated_at = NOW() WHERE id = ?";
                $params = [$first_name, $last_name, $email, $phone, $avatarPath, $currentUser['id']];
                
                if (query($sql, $params)) {
                    $_SESSION['admin_email'] = $email;
                    $_SESSION['admin_full_name'] = trim($first_name . ' ' . $last_name);
                    $success = 'Profil bilgileriniz başarıyla güncellendi.';
                    $currentUser = getUserInfo($_SESSION['admin_id']); // Refresh user data
                } else {
                    $error = 'Profil güncellenirken bir hata oluştu.';
                }
            }
        } elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (!password_verify($current_password, $currentUser['password'])) {
                $error = 'Mevcut şifreniz hatalı.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Yeni şifreler uyuşmuyor.';
            } elseif (strlen($new_password) < 6) {
                $error = 'Yeni şifre en az 6 karakter olmalıdır.';
            } else {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                if (query("UPDATE admin_users SET password = ?, updated_at = NOW() WHERE id = ?", [$hashedPassword, $currentUser['id']])) {
                    $success = 'Şifreniz başarıyla değiştirildi.';
                } else {
                    $error = 'Şifre değiştirilirken bir hata oluştu.';
                }
            }
        }
    }
}

// Get user's recent sessions
$recentSessions = fetchAll("
    SELECT * FROM user_sessions 
    WHERE user_id = ? 
    ORDER BY login_time DESC 
    LIMIT 10
", [$currentUser['id']]);

// Get user permissions
$userPermissions = getUserPermissions($currentUser['id']);
$permissionNames = getAvailablePermissions();

include 'includes/header.php';
?>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo $success; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo $error; ?>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Profile Info Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6 card">
            <div class="text-center">
                <div class="mb-4">
                    <?php if ($currentUser['avatar']): ?>
                        <img src="<?php echo $currentUser['avatar']; ?>" alt="Avatar" class="w-24 h-24 rounded-full mx-auto object-cover">
                    <?php else: ?>
                        <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                            <i class="fas fa-user text-3xl text-red-600"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h2 class="text-xl font-semibold text-gray-900">
                    <?php echo htmlspecialchars(trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?: $currentUser['username']); ?>
                </h2>
                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                <p class="text-sm text-gray-500 mb-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        <?php echo $currentUser['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                        <i class="fas fa-<?php echo $currentUser['role'] === 'admin' ? 'crown' : 'user'; ?> mr-1"></i>
                        <?php echo ucfirst($currentUser['role']); ?>
                    </span>
                </p>
                
                <div class="text-sm text-gray-500 space-y-1">
                    <p><i class="fas fa-calendar-alt mr-2"></i>Üyelik: <?php echo date('d.m.Y', strtotime($currentUser['created_at'])); ?></p>
                    <?php if ($currentUser['last_login']): ?>
                        <p><i class="fas fa-clock mr-2"></i>Son giriş: <?php echo date('d.m.Y H:i', strtotime($currentUser['last_login'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- User Permissions -->
        <?php if (!empty($userPermissions) || $currentUser['role'] === 'admin'): ?>
        <div class="bg-white rounded-lg shadow-md p-6 card mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-key mr-2 text-blue-600"></i>Yetkilerim
            </h3>
            
            <?php if ($currentUser['role'] === 'admin'): ?>
                <div class="p-3 bg-purple-50 rounded-lg mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-crown text-purple-600 mr-2"></i>
                        <span class="font-medium text-purple-800">Tam Yetki (Admin)</span>
                    </div>
                    <p class="text-sm text-purple-600 mt-1">Tüm işlemleri gerçekleştirebilirsiniz.</p>
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($userPermissions as $permission): ?>
                        <div class="flex items-center p-2 bg-blue-50 rounded-lg">
                            <i class="fas fa-check-circle text-blue-600 mr-2"></i>
                            <span class="text-sm text-blue-800">
                                <?php echo $permissionNames[$permission['permission']] ?? $permission['permission']; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($userPermissions)): ?>
                        <p class="text-sm text-gray-500 italic">Henüz özel yetki tanımlanmamış.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Profile Settings -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Profile Information -->
        <div class="bg-white rounded-lg shadow-md p-6 card">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-user-edit mr-2 text-blue-600"></i>Profil Bilgileri
            </h3>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">Ad</label>
                        <input type="text" name="first_name" id="first_name"
                               value="<?php echo htmlspecialchars($currentUser['first_name'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Adınızı girin">
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Soyad</label>
                        <input type="text" name="last_name" id="last_name"
                               value="<?php echo htmlspecialchars($currentUser['last_name'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Soyadınızı girin">
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                    <input type="email" name="email" id="email" required
                           value="<?php echo htmlspecialchars($currentUser['email']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                    <input type="tel" name="phone" id="phone"
                           value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Telefon numaranızı girin">
                </div>
                
                <div>
                    <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">Profil Fotoğrafı</label>
                    <input type="file" name="avatar" id="avatar" accept="image/*"
                           onchange="previewImage(this, 'avatarPreview')"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-sm text-gray-500 mt-1">JPG, PNG formatları desteklenir. Maksimum boyut: 2MB</p>
                    
                    <img id="avatarPreview" src="#" alt="Önizleme" class="hidden w-20 h-20 object-cover rounded-full mt-4">
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                        <i class="fas fa-save mr-2"></i>Profili Güncelle
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="bg-white rounded-lg shadow-md p-6 card">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-lock mr-2 text-red-600"></i>Şifre Değiştir
            </h3>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="change_password">
                
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Mevcut Şifre</label>
                    <input type="password" name="current_password" id="current_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Mevcut şifrenizi girin">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre</label>
                        <input type="password" name="new_password" id="new_password" required minlength="6"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Yeni şifrenizi girin">
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Yeni şifrenizi tekrar girin">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                        <i class="fas fa-key mr-2"></i>Şifreyi Değiştir
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Recent Sessions -->
        <?php if (!empty($recentSessions)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 card">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-history mr-2 text-green-600"></i>Son Oturum Geçmişi
            </h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giriş Zamanı</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Adresi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarayıcı</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recentSessions as $session): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('d.m.Y H:i', strtotime($session['login_time'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="font-mono"><?php echo htmlspecialchars($session['ip_address']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    <?php echo htmlspecialchars($session['user_agent']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($session['is_active']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle mr-1 text-xs"></i>Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Sonlandı
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.setCustomValidity('Şifreler uyuşmuyor');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
