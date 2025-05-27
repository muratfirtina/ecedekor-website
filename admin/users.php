<?php
require_once '../includes/config.php';

// Only admins can access user management
requireRole('admin');

$pageTitle = 'Kullanıcı Yönetimi';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;
$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        if ($action === 'add') {
            $username = sanitizeInput($_POST['username']);
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $phone = sanitizeInput($_POST['phone']);
            $role = sanitizeInput($_POST['role']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if (strlen($password) < 6) {
                $error = 'Şifre en az 6 karakter olmalıdır.';
            } else {
                // Check if username or email already exists
                $existingUser = fetchOne("SELECT id FROM admin_users WHERE username = ? OR email = ?", [$username, $email]);
                if ($existingUser) {
                    $error = 'Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Handle avatar upload
                    $avatarPath = '';
                    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                        $avatarPath = uploadFile($_FILES['avatar'], 'avatars');
                    }
                    
                    $sql = "INSERT INTO admin_users (username, email, password, first_name, last_name, phone, role, avatar, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = [$username, $email, $hashedPassword, $first_name, $last_name, $phone, $role, $avatarPath, $is_active];
                    
                    if (query($sql, $params)) {
                        $newUserId = getLastInsertId();
                        
                        // Add permissions if selected
                        if (isset($_POST['permissions']) && is_array($_POST['permissions'])) {
                            foreach ($_POST['permissions'] as $permission) {
                                addUserPermission($newUserId, $permission);
                            }
                        }
                        
                        $success = 'Kullanıcı başarıyla eklendi.';
                        $action = 'list';
                    } else {
                        $error = 'Kullanıcı eklenirken bir hata oluştu.';
                    }
                }
            }
        } elseif ($action === 'edit' && $id) {
            $username = sanitizeInput($_POST['username']);
            $email = sanitizeInput($_POST['email']);
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $phone = sanitizeInput($_POST['phone']);
            $role = sanitizeInput($_POST['role']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Check if username or email already exists for another user
            $existingUser = fetchOne("SELECT id FROM admin_users WHERE (username = ? OR email = ?) AND id != ?", [$username, $email, $id]);
            if ($existingUser) {
                $error = 'Bu kullanıcı adı veya e-posta adresi başka bir kullanıcı tarafından kullanılıyor.';
            } else {
                // Get current user data
                $currentUserData = fetchOne("SELECT * FROM admin_users WHERE id = ?", [$id]);
                
                // Handle avatar upload
                $avatarPath = $currentUserData['avatar'];
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $newAvatarPath = uploadFile($_FILES['avatar'], 'avatars');
                    if ($newAvatarPath) {
                        $avatarPath = $newAvatarPath;
                    }
                }
                
                $sql = "UPDATE admin_users SET username = ?, email = ?, first_name = ?, last_name = ?, phone = ?, role = ?, avatar = ?, is_active = ?, updated_at = NOW() WHERE id = ?";
                $params = [$username, $email, $first_name, $last_name, $phone, $role, $avatarPath, $is_active, $id];
                
                if (query($sql, $params)) {
                    // Update permissions
                    // First remove all existing permissions
                    query("DELETE FROM user_permissions WHERE user_id = ?", [$id]);
                    
                    // Add new permissions if selected
                    if (isset($_POST['permissions']) && is_array($_POST['permissions'])) {
                        foreach ($_POST['permissions'] as $permission) {
                            addUserPermission($id, $permission);
                        }
                    }
                    
                    $success = 'Kullanıcı başarıyla güncellendi.';
                    $action = 'list';
                } else {
                    $error = 'Kullanıcı güncellenirken bir hata oluştu.';
                }
            }
        } elseif ($action === 'change_password' && $id) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if ($new_password !== $confirm_password) {
                $error = 'Şifreler uyuşmuyor.';
            } elseif (strlen($new_password) < 6) {
                $error = 'Şifre en az 6 karakter olmalıdır.';
            } else {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                if (query("UPDATE admin_users SET password = ?, updated_at = NOW() WHERE id = ?", [$hashedPassword, $id])) {
                    $success = 'Kullanıcı şifresi başarıyla değiştirildi.';
                    $action = 'list';
                } else {
                    $error = 'Şifre değiştirilirken bir hata oluştu.';
                }
            }
        }
    }
}

// Handle delete action
if ($action === 'delete' && $id) {
    // Don't allow deleting the current user
    if ($id == $_SESSION['admin_id']) {
        $error = 'Kendi hesabınızı silemezsiniz.';
    } else {
        // Delete user sessions and permissions first
        query("DELETE FROM user_sessions WHERE user_id = ?", [$id]);
        query("DELETE FROM user_permissions WHERE user_id = ?", [$id]);
        
        if (query("DELETE FROM admin_users WHERE id = ?", [$id])) {
            $success = 'Kullanıcı başarıyla silindi.';
        } else {
            $error = 'Kullanıcı silinirken bir hata oluştu.';
        }
    }
    $action = 'list';
}

// Get user for editing
$user = null;
if (($action === 'edit' || $action === 'change_password') && $id) {
    $user = fetchOne("SELECT * FROM admin_users WHERE id = ?", [$id]);
    if (!$user) {
        $error = 'Kullanıcı bulunamadı.';
        $action = 'list';
    }
}

// Get user permissions for editing
$userPermissions = [];
if ($user) {
    $userPermissionData = getUserPermissions($user['id']);
    foreach ($userPermissionData as $perm) {
        $userPermissions[] = $perm['permission'];
    }
}

// Get all users for listing
if ($action === 'list') {
    $users = fetchAll("
        SELECT u.*, 
               (SELECT COUNT(*) FROM user_permissions WHERE user_id = u.id) as permission_count,
               (SELECT login_time FROM user_sessions WHERE user_id = u.id ORDER BY login_time DESC LIMIT 1) as last_session
        FROM admin_users u 
        ORDER BY u.role DESC, u.created_at DESC
    ");
}

$availablePermissions = getAvailablePermissions();

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

<?php if ($action === 'list'): ?>
    <!-- Users List -->
    <div class="bg-white rounded-lg shadow-md card">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Kullanıcılar</h2>
                <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Yeni Kullanıcı
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kullanıcı</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İletişim</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yetkiler</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Son Giriş</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">Henüz kullanıcı bulunmuyor.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($u['avatar']): ?>
                                            <img src="<?php echo $u['avatar']; ?>" alt="Avatar" class="w-10 h-10 rounded-full object-cover mr-4">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-user text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                <?php echo htmlspecialchars(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: $u['username']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">@<?php echo htmlspecialchars($u['username']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($u['email']); ?></div>
                                    <?php if ($u['phone']): ?>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($u['phone']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?php echo $u['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : ($u['role'] === 'moderator' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'); ?>">
                                        <i class="fas fa-<?php echo $u['role'] === 'admin' ? 'crown' : ($u['role'] === 'moderator' ? 'shield-alt' : 'user'); ?> mr-1"></i>
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="text-xs text-purple-600 font-medium">Tam Yetki</span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-600"><?php echo $u['permission_count']; ?> yetki</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($u['last_session']): ?>
                                        <div class="text-sm text-gray-900"><?php echo date('d.m.Y', strtotime($u['last_session'])); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo date('H:i', strtotime($u['last_session'])); ?></div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-500">Hiç giriş yapmamış</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($u['is_active']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle mr-1 text-xs"></i>Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-circle mr-1 text-xs"></i>Pasif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="?action=edit&id=<?php echo $u['id']; ?>" class="text-blue-600 hover:text-blue-700" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=change_password&id=<?php echo $u['id']; ?>" class="text-orange-600 hover:text-orange-700" title="Şifre Değiştir">
                                            <i class="fas fa-key"></i>
                                        </a>
                                        <?php if ($u['id'] != $_SESSION['admin_id']): ?>
                                            <a href="?action=delete&id=<?php echo $u['id']; ?>" 
                                               onclick="return confirmDelete('Bu kullanıcıyı silmek istediğinizden emin misiniz?')" 
                                               class="text-red-600 hover:text-red-700" title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit User Form -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">
                <?php echo $action === 'add' ? 'Yeni Kullanıcı Ekle' : 'Kullanıcı Düzenle'; ?>
            </h2>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Kullanıcı Adı *</label>
                    <input type="text" name="username" id="username" required
                           value="<?php echo $user ? htmlspecialchars($user['username']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Kullanıcı adını girin">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta *</label>
                    <input type="email" name="email" id="email" required
                           value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="E-posta adresini girin">
                </div>
            </div>
            
            <?php if ($action === 'add'): ?>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Şifre *</label>
                <input type="password" name="password" id="password" required minlength="6"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Şifre girin (en az 6 karakter)">
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">Ad</label>
                    <input type="text" name="first_name" id="first_name"
                           value="<?php echo $user ? htmlspecialchars($user['first_name'] ?? '') : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ad girin">
                </div>
                
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Soyad</label>
                    <input type="text" name="last_name" id="last_name"
                           value="<?php echo $user ? htmlspecialchars($user['last_name'] ?? '') : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Soyad girin">
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                    <input type="tel" name="phone" id="phone"
                           value="<?php echo $user ? htmlspecialchars($user['phone'] ?? '') : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Telefon numarası girin">
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Rol *</label>
                    <select name="role" id="role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Rol seçin</option>
                        <option value="admin" <?php echo ($user && $user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="moderator" <?php echo ($user && $user['role'] === 'moderator') ? 'selected' : ''; ?>>Moderatör</option>
                        <option value="user" <?php echo ($user && $user['role'] === 'user') ? 'selected' : ''; ?>>Kullanıcı</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">Profil Fotoğrafı</label>
                <input type="file" name="avatar" id="avatar" accept="image/*"
                       onchange="previewImage(this, 'avatarPreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-sm text-gray-500 mt-1">JPG, PNG formatları desteklenir. Maksimum boyut: 2MB</p>
                
                <?php if ($user && $user['avatar']): ?>
                    <div class="mt-4">
                        <img src="<?php echo $user['avatar']; ?>" alt="Mevcut avatar" class="w-20 h-20 object-cover rounded-full">
                        <p class="text-sm text-gray-500 mt-1">Mevcut profil fotoğrafı</p>
                    </div>
                <?php endif; ?>
                
                <img id="avatarPreview" src="#" alt="Önizleme" class="hidden w-20 h-20 object-cover rounded-full mt-4">
            </div>
            
            <!-- Permissions -->
            <div id="permissionsSection" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Yetkiler</label>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($availablePermissions as $permission => $label): ?>
                        <div class="flex items-center">
                            <input type="checkbox" name="permissions[]" value="<?php echo $permission; ?>" 
                                   id="perm_<?php echo $permission; ?>"
                                   <?php echo in_array($permission, $userPermissions) ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="perm_<?php echo $permission; ?>" class="ml-2 block text-sm text-gray-700">
                                <?php echo $label; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-sm text-gray-500 mt-2">Admin kullanıcıları için yetki seçimi gerekli değildir (tüm yetkilere sahiptir).</p>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       <?php echo (!$user || $user['is_active']) ? 'checked' : ''; ?>
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Aktif</label>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>
                    <?php echo $action === 'add' ? 'Kullanıcı Ekle' : 'Değişiklikleri Kaydet'; ?>
                </button>
                <a href="?action=list" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                    <i class="fas fa-times mr-2"></i>İptal
                </a>
            </div>
        </form>
    </div>

<?php elseif ($action === 'change_password'): ?>
    <!-- Change Password Form -->
    <div class="bg-white rounded-lg shadow-md p-6 card max-w-2xl">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">
                <?php echo htmlspecialchars($user['username']); ?> - Şifre Değiştir
            </h2>
        </div>
        
        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre *</label>
                    <input type="password" name="new_password" id="new_password" required minlength="6"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Yeni şifre girin">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre (Tekrar) *</label>
                    <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Yeni şifreyi tekrar girin">
                </div>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                    <i class="fas fa-key mr-2"></i>Şifreyi Değiştir
                </button>
                <a href="?action=list" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                    <i class="fas fa-times mr-2"></i>İptal
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

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

// Show/hide permissions based on role
document.getElementById('role')?.addEventListener('change', function() {
    const permissionsSection = document.getElementById('permissionsSection');
    if (this.value === 'admin') {
        permissionsSection.classList.add('hidden');
    } else {
        permissionsSection.classList.remove('hidden');
    }
});

// Initialize permissions visibility
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        const event = new Event('change');
        roleSelect.dispatchEvent(event);
    }
});

// Password confirmation validation
document.getElementById('confirm_password')?.addEventListener('input', function() {
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
