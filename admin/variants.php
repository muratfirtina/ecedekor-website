<?php
require_once '../includes/config.php';

$pageTitle = 'Ürün Varyantları';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;
$product_id = $_GET['product_id'] ?? 0;
$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        $product_id = intval($_POST['product_id']);
        $name = sanitizeInput($_POST['name']);
        $color = sanitizeInput($_POST['color']);
        $size = sanitizeInput($_POST['size']);
        $weight = sanitizeInput($_POST['weight']);
        $sku = sanitizeInput($_POST['sku']);
        $price = floatval($_POST['price']);
        $sort_order = intval($_POST['sort_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle file upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadFile($_FILES['image'], 'variants');
            if (!$image_path) {
                $error = 'Dosya yükleme hatası.';
            }
        }
        
        if (!$error) {
            if ($action === 'add') {
                // Check if SKU already exists
                if ($sku) {
                    $existingSku = fetchOne("SELECT id FROM product_variants WHERE sku = ?", [$sku]);
                    if ($existingSku) {
                        $error = 'Bu SKU zaten kullanılıyor.';
                    }
                }
                
                if (!$error) {
                    $sql = "INSERT INTO product_variants (product_id, name, color, size, weight, sku, price, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = [$product_id, $name, $color, $size, $weight, $sku, $price, $image_path, $sort_order, $is_active];
                    
                    if (query($sql, $params)) {
                        $success = 'Varyant başarıyla eklendi.';
                        $action = 'list';
                    } else {
                        $error = 'Varyant eklenirken bir hata oluştu.';
                    }
                }
            } elseif ($action === 'edit' && $id) {
                // Check if SKU already exists (except current variant)
                if ($sku) {
                    $existingSku = fetchOne("SELECT id FROM product_variants WHERE sku = ? AND id != ?", [$sku, $id]);
                    if ($existingSku) {
                        $error = 'Bu SKU zaten kullanılıyor.';
                    }
                }
                
                if (!$error) {
                    $sql = "UPDATE product_variants SET product_id = ?, name = ?, color = ?, size = ?, weight = ?, sku = ?, price = ?, sort_order = ?, is_active = ?";
                    $params = [$product_id, $name, $color, $size, $weight, $sku, $price, $sort_order, $is_active];
                    
                    if ($image_path) {
                        $sql .= ", image = ?";
                        $params[] = $image_path;
                    }
                    
                    $sql .= ", updated_at = NOW() WHERE id = ?";
                    $params[] = $id;
                    
                    if (query($sql, $params)) {
                        $success = 'Varyant başarıyla güncellendi.';
                        $action = 'list';
                    } else {
                        $error = 'Varyant güncellenirken bir hata oluştu.';
                    }
                }
            }
        }
    }
}

// Handle delete action
if ($action === 'delete' && $id) {
    if (query("DELETE FROM product_variants WHERE id = ?", [$id])) {
        $success = 'Varyant başarıyla silindi.';
    } else {
        $error = 'Varyant silinirken bir hata oluştu.';
    }
    $action = 'list';
}

// Get variant for editing
$variant = null;
if ($action === 'edit' && $id) {
    $variant = fetchOne("SELECT * FROM product_variants WHERE id = ?", [$id]);
    if (!$variant) {
        $error = 'Varyant bulunamadı.';
        $action = 'list';
    } else {
        $product_id = $variant['product_id'];
    }
}

// Get products for form
$products = fetchAll("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY c.name, p.name");

// Get selected product info
$selectedProduct = null;
if ($product_id) {
    $selectedProduct = fetchOne("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?", [$product_id]);
}

// Get all variants for listing
if ($action === 'list') {
    $whereClause = '';
    $params = [];
    
    if ($product_id) {
        $whereClause = 'WHERE pv.product_id = ?';
        $params[] = $product_id;
    }
    
    $variants = fetchAll("
        SELECT pv.*, p.name as product_name, c.name as category_name
        FROM product_variants pv 
        JOIN products p ON pv.product_id = p.id 
        JOIN categories c ON p.category_id = c.id 
        $whereClause
        ORDER BY c.name, p.name, pv.sort_order, pv.name
    ", $params);
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
    <!-- Variants List -->
    <div class="bg-white rounded-lg shadow-md card">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Ürün Varyantları</h2>
                    <?php if ($selectedProduct): ?>
                        <p class="text-sm text-gray-600 mt-1">
                            <span class="font-medium"><?php echo htmlspecialchars($selectedProduct['category_name']); ?></span> > 
                            <span class="font-medium"><?php echo htmlspecialchars($selectedProduct['name']); ?></span>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="flex space-x-2">
                    <?php if ($product_id): ?>
                        <a href="?action=add&product_id=<?php echo $product_id; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                            <i class="fas fa-plus mr-2"></i>Yeni Varyant
                        </a>
                        <a href="?action=list" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                            <i class="fas fa-list mr-2"></i>Tüm Varyantlar
                        </a>
                    <?php else: ?>
                        <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                            <i class="fas fa-plus mr-2"></i>Yeni Varyant
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Filter by Product -->
        <?php if (!$product_id): ?>
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <form method="GET" class="flex items-center space-x-4">
                    <input type="hidden" name="action" value="list">
                    <label for="filter_product" class="text-sm font-medium text-gray-700">Ürüne göre filtrele:</label>
                    <select name="product_id" id="filter_product" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tüm ürünler</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>" 
                                    <?php echo $product_id == $product['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['category_name'] . ' > ' . $product['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Varyant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Özellikler</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fiyat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($variants)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                <?php echo $product_id ? 'Bu ürün için henüz varyant eklenmemiş.' : 'Henüz varyant eklenmemiş.'; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($variants as $var): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($var['image']): ?>
                                            <img src="<?php echo $var['image']; ?>" alt="<?php echo htmlspecialchars($var['name']); ?>" class="w-10 h-10 object-cover rounded-lg mr-3">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-palette text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($var['name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($var['product_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($var['category_name']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700">
                                        <?php if ($var['color']): ?>
                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                                Renk: <?php echo htmlspecialchars($var['color']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($var['size']): ?>
                                            <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                                Boyut: <?php echo htmlspecialchars($var['size']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($var['weight']): ?>
                                            <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                                <?php echo htmlspecialchars($var['weight']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($var['sku']): ?>
                                        <span class="text-sm font-mono text-gray-900"><?php echo htmlspecialchars($var['sku']); ?></span>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($var['price']): ?>
                                        <span class="text-sm font-semibold text-gray-900">₺<?php echo number_format($var['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($var['is_active']): ?>
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
                                        <a href="?action=edit&id=<?php echo $var['id']; ?>" class="text-blue-600 hover:text-blue-700" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $var['id']; ?><?php echo $product_id ? '&product_id=' . $product_id : ''; ?>" 
                                           onclick="return confirmDelete('Bu varyantı silmek istediğinizden emin misiniz?')" 
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
    <!-- Add/Edit Variant Form -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">
                <?php echo $action === 'add' ? 'Yeni Varyant Ekle' : 'Varyant Düzenle'; ?>
            </h2>
            <?php if ($selectedProduct): ?>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-medium"><?php echo htmlspecialchars($selectedProduct['category_name']); ?></span> > 
                    <span class="font-medium"><?php echo htmlspecialchars($selectedProduct['name']); ?></span>
                </p>
            <?php endif; ?>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">Ürün *</label>
                    <select name="product_id" id="product_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Ürün seçin</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>" 
                                    <?php echo ($variant ? $variant['product_id'] : $product_id) == $product['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['category_name'] . ' > ' . $product['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sıra Numarası</label>
                    <input type="number" name="sort_order" id="sort_order"
                           value="<?php echo $variant ? $variant['sort_order'] : '0'; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0">
                </div>
            </div>
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Varyant Adı *</label>
                <input type="text" name="name" id="name" required
                       value="<?php echo $variant ? htmlspecialchars($variant['name']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Varyant adını girin (örn: Doğal Renk, Meşe Rengi)">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-2">Renk</label>
                    <input type="text" name="color" id="color"
                           value="<?php echo $variant ? htmlspecialchars($variant['color']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Renk adını girin">
                </div>
                
                <div>
                    <label for="size" class="block text-sm font-medium text-gray-700 mb-2">Boyut</label>
                    <input type="text" name="size" id="size"
                           value="<?php echo $variant ? htmlspecialchars($variant['size']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Boyut bilgisini girin">
                </div>
                
                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Ağırlık/Hacim</label>
                    <input type="text" name="weight" id="weight"
                           value="<?php echo $variant ? htmlspecialchars($variant['weight']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ağırlık veya hacim (örn: 200gr, 125ml)">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700 mb-2">SKU (Stok Kodu)</label>
                    <input type="text" name="sku" id="sku"
                           value="<?php echo $variant ? htmlspecialchars($variant['sku']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Benzersiz stok kodu">
                </div>
                
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Fiyat (₺)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0"
                           value="<?php echo $variant ? $variant['price'] : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0.00">
                </div>
            </div>
            
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Varyant Görseli</label>
                <input type="file" name="image" id="image" accept="image/*"
                       onchange="previewImage(this, 'imagePreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-sm text-gray-500 mt-1">Desteklenen formatlar: JPG, PNG, GIF. Maksimum boyut: 5MB</p>
                
                <?php if ($variant && $variant['image']): ?>
                    <div class="mt-4">
                        <img src="<?php echo $variant['image']; ?>" alt="Mevcut görsel" class="w-32 h-32 object-cover rounded-lg">
                        <p class="text-sm text-gray-500 mt-1">Mevcut görsel</p>
                    </div>
                <?php endif; ?>
                
                <img id="imagePreview" src="#" alt="Önizleme" class="hidden w-32 h-32 object-cover rounded-lg mt-4">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       <?php echo (!$variant || $variant['is_active']) ? 'checked' : ''; ?>
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Aktif</label>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>
                    <?php echo $action === 'add' ? 'Varyant Ekle' : 'Değişiklikleri Kaydet'; ?>
                </button>
                <a href="?action=list<?php echo $product_id ? '&product_id=' . $product_id : ''; ?>" 
                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                    <i class="fas fa-times mr-2"></i>İptal
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
