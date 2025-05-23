<?php
require_once '../includes/config.php';

$pageTitle = 'Kategori Yönetimi';

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
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $sort_order = intval($_POST['sort_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $slug = generateSlug($name);
        
        // Handle file upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadFile($_FILES['image'], 'categories');
            if (!$image_path) {
                $error = 'Dosya yükleme hatası.';
            }
        }
        
        if (!$error) {
            if ($action === 'add') {
                // Check if slug already exists
                $existingCategory = fetchOne("SELECT id FROM categories WHERE slug = ?", [$slug]);
                if ($existingCategory) {
                    $error = 'Bu isimde bir kategori zaten mevcut.';
                } else {
                    $sql = "INSERT INTO categories (name, slug, description, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)";
                    $params = [$name, $slug, $description, $image_path, $sort_order, $is_active];
                    
                    if (query($sql, $params)) {
                        $success = 'Kategori başarıyla eklendi.';
                        $action = 'list';
                    } else {
                        $error = 'Kategori eklenirken bir hata oluştu.';
                    }
                }
            } elseif ($action === 'edit' && $id) {
                // Check if slug already exists (except current category)
                $existingCategory = fetchOne("SELECT id FROM categories WHERE slug = ? AND id != ?", [$slug, $id]);
                if ($existingCategory) {
                    $error = 'Bu isimde bir kategori zaten mevcut.';
                } else {
                    $sql = "UPDATE categories SET name = ?, slug = ?, description = ?, sort_order = ?, is_active = ?";
                    $params = [$name, $slug, $description, $sort_order, $is_active];
                    
                    if ($image_path) {
                        $sql .= ", image = ?";
                        $params[] = $image_path;
                    }
                    
                    $sql .= ", updated_at = NOW() WHERE id = ?";
                    $params[] = $id;
                    
                    if (query($sql, $params)) {
                        $success = 'Kategori başarıyla güncellendi.';
                        $action = 'list';
                    } else {
                        $error = 'Kategori güncellenirken bir hata oluştu.';
                    }
                }
            }
        }
    }
}

// Handle delete action
if ($action === 'delete' && $id) {
    // Check if category has products
    $productCount = fetchOne("SELECT COUNT(*) as count FROM products WHERE category_id = ?", [$id]);
    if ($productCount['count'] > 0) {
        $error = 'Bu kategoriye ait ürünler bulunduğu için silinemez.';
    } else {
        if (query("DELETE FROM categories WHERE id = ?", [$id])) {
            $success = 'Kategori başarıyla silindi.';
        } else {
            $error = 'Kategori silinirken bir hata oluştu.';
        }
    }
    $action = 'list';
}

// Get category for editing
$category = null;
if ($action === 'edit' && $id) {
    $category = fetchOne("SELECT * FROM categories WHERE id = ?", [$id]);
    if (!$category) {
        $error = 'Kategori bulunamadı.';
        $action = 'list';
    }
}

// Get all categories for listing
if ($action === 'list') {
    $categories = fetchAll("
        SELECT c.*, 
               (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count
        FROM categories c 
        ORDER BY c.sort_order, c.name
    ");
}

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
    <!-- Categories List -->
    <div class="bg-white rounded-lg shadow-md card">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Kategoriler</h2>
                <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Yeni Kategori
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Açıklama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün Sayısı</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sıra</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Henüz kategori eklenmemiş.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($cat['image']): ?>
                                            <img src="<?php echo $cat['image']; ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" class="w-10 h-10 object-cover rounded-lg mr-3">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($cat['name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($cat['slug']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700">
                                        <?php echo htmlspecialchars(substr($cat['description'], 0, 100)) . (strlen($cat['description']) > 100 ? '...' : ''); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo $cat['product_count']; ?> ürün
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $cat['sort_order']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($cat['is_active']): ?>
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
                                        <a href="?action=edit&id=<?php echo $cat['id']; ?>" class="text-blue-600 hover:text-blue-700" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($cat['product_count'] == 0): ?>
                                            <a href="?action=delete&id=<?php echo $cat['id']; ?>" 
                                               onclick="return confirmDelete('Bu kategoriyi silmek istediğinizden emin misiniz?')" 
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
    <!-- Add/Edit Category Form -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">
                <?php echo $action === 'add' ? 'Yeni Kategori Ekle' : 'Kategori Düzenle'; ?>
            </h2>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Kategori Adı *</label>
                    <input type="text" name="name" id="name" required
                           value="<?php echo $category ? htmlspecialchars($category['name']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Kategori adını girin">
                </div>
                
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sıra Numarası</label>
                    <input type="number" name="sort_order" id="sort_order"
                           value="<?php echo $category ? $category['sort_order'] : '0'; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0">
                </div>
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Kategori açıklamasını girin"><?php echo $category ? htmlspecialchars($category['description']) : ''; ?></textarea>
            </div>
            
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Kategori Görseli</label>
                <input type="file" name="image" id="image" accept="image/*"
                       onchange="previewImage(this, 'imagePreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-sm text-gray-500 mt-1">Desteklenen formatlar: JPG, PNG, GIF. Maksimum boyut: 5MB</p>
                
                <?php if ($category && $category['image']): ?>
                    <div class="mt-4">
                        <img src="<?php echo $category['image']; ?>" alt="Mevcut görsel" class="w-32 h-32 object-cover rounded-lg">
                        <p class="text-sm text-gray-500 mt-1">Mevcut görsel</p>
                    </div>
                <?php endif; ?>
                
                <img id="imagePreview" src="#" alt="Önizleme" class="hidden w-32 h-32 object-cover rounded-lg mt-4">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       <?php echo (!$category || $category['is_active']) ? 'checked' : ''; ?>
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Aktif</label>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>
                    <?php echo $action === 'add' ? 'Kategori Ekle' : 'Değişiklikleri Kaydet'; ?>
                </button>
                <a href="?action=list" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                    <i class="fas fa-times mr-2"></i>İptal
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
