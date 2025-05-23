<?php
require_once '../includes/config.php';

$pageTitle = 'Ürün Yönetimi';

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
        $category_id = intval($_POST['category_id']);
        $name = sanitizeInput($_POST['name']);
        $short_description = sanitizeInput($_POST['short_description']);
        $description = sanitizeInput($_POST['description']);
        $features = sanitizeInput($_POST['features']);
        $usage_instructions = sanitizeInput($_POST['usage_instructions']);
        $sort_order = intval($_POST['sort_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $slug = generateSlug($name);
        
        // Handle file upload
        $image_path = '';
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadFile($_FILES['main_image'], 'products');
            if (!$image_path) {
                $error = 'Dosya yükleme hatası.';
            }
        }
        
        if (!$error) {
            if ($action === 'add') {
                // Check if slug already exists
                $existingProduct = fetchOne("SELECT id FROM products WHERE slug = ?", [$slug]);
                if ($existingProduct) {
                    $error = 'Bu isimde bir ürün zaten mevcut.';
                } else {
                    $sql = "INSERT INTO products (category_id, name, slug, short_description, description, features, usage_instructions, main_image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = [$category_id, $name, $slug, $short_description, $description, $features, $usage_instructions, $image_path, $sort_order, $is_active];
                    
                    if (query($sql, $params)) {
                        $success = 'Ürün başarıyla eklendi.';
                        $action = 'list';
                    } else {
                        $error = 'Ürün eklenirken bir hata oluştu.';
                    }
                }
            } elseif ($action === 'edit' && $id) {
                // Check if slug already exists (except current product)
                $existingProduct = fetchOne("SELECT id FROM products WHERE slug = ? AND id != ?", [$slug, $id]);
                if ($existingProduct) {
                    $error = 'Bu isimde bir ürün zaten mevcut.';
                } else {
                    $sql = "UPDATE products SET category_id = ?, name = ?, slug = ?, short_description = ?, description = ?, features = ?, usage_instructions = ?, sort_order = ?, is_active = ?";
                    $params = [$category_id, $name, $slug, $short_description, $description, $features, $usage_instructions, $sort_order, $is_active];
                    
                    if ($image_path) {
                        $sql .= ", main_image = ?";
                        $params[] = $image_path;
                    }
                    
                    $sql .= ", updated_at = NOW() WHERE id = ?";
                    $params[] = $id;
                    
                    if (query($sql, $params)) {
                        $success = 'Ürün başarıyla güncellendi.';
                        $action = 'list';
                    } else {
                        $error = 'Ürün güncellenirken bir hata oluştu.';
                    }
                }
            }
        }
    }
}

// Handle delete action
if ($action === 'delete' && $id) {
    // Delete related variants and images first
    query("DELETE FROM product_images WHERE product_id = ?", [$id]);
    query("DELETE FROM product_variants WHERE product_id = ?", [$id]);
    
    if (query("DELETE FROM products WHERE id = ?", [$id])) {
        $success = 'Ürün ve ilgili tüm veriler başarıyla silindi.';
    } else {
        $error = 'Ürün silinirken bir hata oluştu.';
    }
    $action = 'list';
}

// Get product for editing
$product = null;
if ($action === 'edit' && $id) {
    $product = fetchOne("SELECT * FROM products WHERE id = ?", [$id]);
    if (!$product) {
        $error = 'Ürün bulunamadı.';
        $action = 'list';
    }
}

// Get categories for form
$categories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name");

// Get all products for listing
if ($action === 'list') {
    $products = fetchAll("
        SELECT p.*, c.name as category_name,
               (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.id) as variant_count
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        ORDER BY p.sort_order, p.name
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
    <!-- Products List -->
    <div class="bg-white rounded-lg shadow-md card">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Ürünler</h2>
                <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Yeni Ürün
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Açıklama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Varyant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Henüz ürün eklenmemiş.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $prod): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($prod['main_image']): ?>
                                            <img src="<?php echo $prod['main_image']; ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="w-12 h-12 object-cover rounded-lg mr-4">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($prod['name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($prod['slug']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <?php echo htmlspecialchars($prod['category_name']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700">
                                        <?php echo htmlspecialchars(substr($prod['short_description'], 0, 100)) . (strlen($prod['short_description']) > 100 ? '...' : ''); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo $prod['variant_count']; ?> varyant
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($prod['is_active']): ?>
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
                                        <a href="<?php echo ADMIN_URL; ?>/variants.php?product_id=<?php echo $prod['id']; ?>" class="text-purple-600 hover:text-purple-700" title="Varyantlar">
                                            <i class="fas fa-palette"></i>
                                        </a>
                                        <a href="?action=edit&id=<?php echo $prod['id']; ?>" class="text-blue-600 hover:text-blue-700" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $prod['id']; ?>" 
                                           onclick="return confirmDelete('Bu ürünü ve tüm varyantlarını silmek istediğinizden emin misiniz?')" 
                                           class="text-red-600 hover:text-red-700" title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </a>
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
    <!-- Add/Edit Product Form -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">
                <?php echo $action === 'add' ? 'Yeni Ürün Ekle' : 'Ürün Düzenle'; ?>
            </h2>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                    <select name="category_id" id="category_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Kategori seçin</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($product && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sıra Numarası</label>
                    <input type="number" name="sort_order" id="sort_order"
                           value="<?php echo $product ? $product['sort_order'] : '0'; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0">
                </div>
            </div>
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ürün Adı *</label>
                <input type="text" name="name" id="name" required
                       value="<?php echo $product ? htmlspecialchars($product['name']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Ürün adını girin">
            </div>
            
            <div>
                <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">Kısa Açıklama</label>
                <textarea name="short_description" id="short_description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Ürünün kısa açıklamasını girin"><?php echo $product ? htmlspecialchars($product['short_description']) : ''; ?></textarea>
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Detaylı Açıklama</label>
                <textarea name="description" id="description" rows="6"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Ürünün detaylı açıklamasını girin"><?php echo $product ? htmlspecialchars($product['description']) : ''; ?></textarea>
            </div>
            
            <div>
                <label for="features" class="block text-sm font-medium text-gray-700 mb-2">Özellikler</label>
                <textarea name="features" id="features" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Ürün özelliklerini her satıra bir tane olmak üzere girin"><?php echo $product ? htmlspecialchars($product['features']) : ''; ?></textarea>
                <p class="text-sm text-gray-500 mt-1">Her özelliği yeni satıra yazın</p>
            </div>
            
            <div>
                <label for="usage_instructions" class="block text-sm font-medium text-gray-700 mb-2">Kullanım Talimatları</label>
                <textarea name="usage_instructions" id="usage_instructions" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Ürünün kullanım talimatlarını girin"><?php echo $product ? htmlspecialchars($product['usage_instructions']) : ''; ?></textarea>
            </div>
            
            <div>
                <label for="main_image" class="block text-sm font-medium text-gray-700 mb-2">Ana Ürün Görseli</label>
                <input type="file" name="main_image" id="main_image" accept="image/*"
                       onchange="previewImage(this, 'imagePreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-sm text-gray-500 mt-1">Desteklenen formatlar: JPG, PNG, GIF. Maksimum boyut: 5MB</p>
                
                <?php if ($product && $product['main_image']): ?>
                    <div class="mt-4">
                        <img src="<?php echo $product['main_image']; ?>" alt="Mevcut görsel" class="w-32 h-32 object-cover rounded-lg">
                        <p class="text-sm text-gray-500 mt-1">Mevcut görsel</p>
                    </div>
                <?php endif; ?>
                
                <img id="imagePreview" src="#" alt="Önizleme" class="hidden w-32 h-32 object-cover rounded-lg mt-4">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       <?php echo (!$product || $product['is_active']) ? 'checked' : ''; ?>
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Aktif</label>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>
                    <?php echo $action === 'add' ? 'Ürün Ekle' : 'Değişiklikleri Kaydet'; ?>
                </button>
                <a href="?action=list" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                    <i class="fas fa-times mr-2"></i>İptal
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
