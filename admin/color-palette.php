<?php
require_once 'includes/config.php';
requireAdminLogin();

$pageTitle = 'Renk Kartelası Ayarları';

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_settings':
                    // Renk kartelası ayarlarını güncelle
                    $title = trim($_POST['title']);
                    $subtitle = trim($_POST['subtitle']);
                    $description = trim($_POST['description']);
                    $is_active = isset($_POST['is_active']) ? 1 : 0;
                    $show_on_homepage = isset($_POST['show_on_homepage']) ? 1 : 0;

                    // Resim yükleme işlemi - Hero Image
                    $hero_image = null;
                    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = '../assets/images/color-palette/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $fileName = 'hero-' . time() . '-' . basename($_FILES['hero_image']['name']);
                        $targetPath = $uploadDir . $fileName;

                        if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetPath)) {
                            $hero_image = '/assets/images/color-palette/' . $fileName;
                        }
                    }

                    // Resim yükleme işlemi - Homepage Image
                    $homepage_image = null;
                    if (isset($_FILES['homepage_image']) && $_FILES['homepage_image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = '../assets/images/color-palette/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $fileName = 'homepage-' . time() . '-' . basename($_FILES['homepage_image']['name']);
                        $targetPath = $uploadDir . $fileName;

                        if (move_uploaded_file($_FILES['homepage_image']['tmp_name'], $targetPath)) {
                            $homepage_image = '/assets/images/color-palette/' . $fileName;
                        }
                    }

                    // Mevcut ayarları kontrol et
                    $existing = fetchOne("SELECT * FROM color_palette_settings WHERE id = 1");

                    if ($existing) {
                        // Güncelle
                        $sql = "UPDATE color_palette_settings SET title = ?, subtitle = ?, description = ?, is_active = ?, show_on_homepage = ?, updated_at = NOW()";
                        $params = [$title, $subtitle, $description, $is_active, $show_on_homepage];

                        if ($hero_image) {
                            $sql .= ", hero_image = ?";
                            $params[] = $hero_image;
                        }

                        if ($homepage_image) {
                            $sql .= ", homepage_image = ?";
                            $params[] = $homepage_image;
                        }

                        $sql .= " WHERE id = 1";
                        query($sql, $params);
                    } else {
                        // Yeni kayıt ekle
                        query("INSERT INTO color_palette_settings (title, subtitle, description, hero_image, homepage_image, is_active, show_on_homepage) VALUES (?, ?, ?, ?, ?, ?, ?)",
                            [$title, $subtitle, $description, $hero_image, $homepage_image, $is_active, $show_on_homepage]);
                    }

                    $pdo->commit();
                    $success = "Ayarlar başarıyla güncellendi!";
                    break;

                case 'add_category':
                    $category_id = intval($_POST['category_id']);
                    $sort_order = intval($_POST['sort_order'] ?? 0);

                    // Kategori zaten ekli mi kontrol et
                    $existing = fetchOne("SELECT * FROM color_palette_categories WHERE category_id = ?", [$category_id]);
                    if ($existing) {
                        throw new Exception("Bu kategori zaten eklenmiş!");
                    }

                    query("INSERT INTO color_palette_categories (category_id, sort_order, is_active) VALUES (?, ?, 1)",
                        [$category_id, $sort_order]);

                    $pdo->commit();
                    $success = "Kategori başarıyla eklendi!";
                    break;

                case 'remove_category':
                    $id = intval($_POST['id']);
                    query("DELETE FROM color_palette_categories WHERE id = ?", [$id]);

                    $pdo->commit();
                    $success = "Kategori başarıyla kaldırıldı!";
                    break;

                case 'toggle_category':
                    $id = intval($_POST['id']);
                    query("UPDATE color_palette_categories SET is_active = NOT is_active, updated_at = NOW() WHERE id = ?", [$id]);

                    $pdo->commit();
                    $success = "Kategori durumu güncellendi!";
                    break;

                case 'update_sort_orders':
                    if (isset($_POST['sort_orders']) && is_array($_POST['sort_orders'])) {
                        foreach ($_POST['sort_orders'] as $id => $sort_order) {
                            query("UPDATE color_palette_categories SET sort_order = ?, updated_at = NOW() WHERE id = ?",
                                [intval($sort_order), intval($id)]);
                        }
                    }

                    $pdo->commit();
                    $success = "Sıralama başarıyla güncellendi!";
                    break;
            }
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Mevcut ayarları çek
$settings = fetchOne("SELECT * FROM color_palette_settings WHERE id = 1");
if (!$settings) {
    // Varsayılan ayarları oluştur
    query("INSERT INTO color_palette_settings (title, subtitle, description, is_active, show_on_homepage) VALUES (?, ?, ?, 1, 1)",
        ['Renk Kartelamız', 'Geniş Renk Seçeneklerimizi Keşfedin', 'Ürünlerimizde kullanılan tüm renk seçeneklerini bu sayfada inceleyebilirsiniz.']);
    $settings = fetchOne("SELECT * FROM color_palette_settings WHERE id = 1");
}

// Seçili kategorileri çek
$selectedCategories = fetchAll("
    SELECT cpc.*, c.name, c.slug, c.image
    FROM color_palette_categories cpc
    JOIN categories c ON cpc.category_id = c.id
    ORDER BY cpc.sort_order, c.name
");

// Tüm kategorileri çek (eklenebilecek kategoriler için)
$allCategories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");

// Her kategoride kaç renk olduğunu hesapla
$categoryColorCounts = [];
foreach ($allCategories as $category) {
    $count = fetchOne("
        SELECT COUNT(DISTINCT pv.color_code) as color_count
        FROM product_variants pv
        JOIN products p ON pv.product_id = p.id
        WHERE p.category_id = ? AND pv.color_code IS NOT NULL AND pv.color_code != '' AND pv.is_active = 1
    ", [$category['id']]);
    $categoryColorCounts[$category['id']] = $count['color_count'] ?? 0;
}

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <?php if (isset($success)): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <!-- Genel Ayarlar -->
    <div class="bg-white shadow-md rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-cog mr-2 text-red-600"></i>
                Genel Ayarlar
            </h2>
        </div>
        <div class="p-6">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_settings">

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Başlık</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($settings['title'] ?? 'Renk Kartelamız'); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alt Başlık</label>
                        <input type="text" name="subtitle" value="<?php echo htmlspecialchars($settings['subtitle'] ?? ''); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                        <textarea name="description" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"><?php echo htmlspecialchars($settings['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hero Resmi (Sayfa Üstü)</label>
                            <?php if (!empty($settings['hero_image'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo BASE_URL . htmlspecialchars($settings['hero_image']); ?>"
                                        alt="Hero Image" class="w-full h-48 object-cover rounded-lg border">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="hero_image" accept="image/*"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            <p class="text-xs text-gray-500 mt-1">Önerilen boyut: 1920x600px</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Anasayfa Resmi</label>
                            <?php if (!empty($settings['homepage_image'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo BASE_URL . htmlspecialchars($settings['homepage_image']); ?>"
                                        alt="Homepage Image" class="w-full h-48 object-cover rounded-lg border">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="homepage_image" accept="image/*"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            <p class="text-xs text-gray-500 mt-1">Önerilen boyut: 800x600px</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" <?php echo (!empty($settings['is_active'])) ? 'checked' : ''; ?>
                                class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Renk kartelası sayfası aktif</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="show_on_homepage" value="1" <?php echo (!empty($settings['show_on_homepage'])) ? 'checked' : ''; ?>
                                class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Anasayfada göster</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i>Ayarları Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kategori Seçimi -->
    <div class="bg-white shadow-md rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-tags mr-2 text-red-600"></i>
                Renk Kartelasında Gösterilecek Kategoriler
            </h2>
        </div>
        <div class="p-6">
            <!-- Kategori Ekleme Formu -->
            <form method="POST" class="mb-6">
                <input type="hidden" name="action" value="add_category">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <select name="category_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Kategori Seçin...</option>
                            <?php foreach ($allCategories as $category): ?>
                                <?php
                                // Zaten eklenmiş mi kontrol et
                                $isAdded = false;
                                foreach ($selectedCategories as $selected) {
                                    if ($selected['category_id'] == $category['id']) {
                                        $isAdded = true;
                                        break;
                                    }
                                }
                                if (!$isAdded):
                                ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        (<?php echo $categoryColorCounts[$category['id']]; ?> renk)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="w-32">
                        <input type="number" name="sort_order" value="0" placeholder="Sıra"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Ekle
                        </button>
                    </div>
                </div>
            </form>

            <!-- Seçili Kategoriler Listesi -->
            <?php if (empty($selectedCategories)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <p>Henüz kategori eklenmemiş. Yukarıdaki formdan kategori ekleyebilirsiniz.</p>
                </div>
            <?php else: ?>
                <form method="POST" id="sortForm">
                    <input type="hidden" name="action" value="update_sort_orders">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Renk Sayısı</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sıralama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($selectedCategories as $category): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <?php if (!empty($category['image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($category['image']); ?>"
                                                        alt="<?php echo htmlspecialchars($category['name']); ?>"
                                                        class="w-10 h-10 rounded-lg object-cover mr-3">
                                                <?php endif; ?>
                                                <div class="font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo $categoryColorCounts[$category['category_id']]; ?> renk
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="sort_orders[<?php echo $category['id']; ?>]"
                                                value="<?php echo $category['sort_order']; ?>"
                                                class="w-20 px-2 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_category">
                                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                <button type="submit" class="text-sm">
                                                    <?php if ($category['is_active']): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check mr-1"></i>Aktif
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            <i class="fas fa-times mr-1"></i>Pasif
                                                        </span>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Bu kategoriyi kaldırmak istediğinizden emin misiniz?');">
                                                <input type="hidden" name="action" value="remove_category">
                                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i> Kaldır
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
                            <i class="fas fa-sort mr-2"></i>Sıralamayı Kaydet
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Önizleme Butonu -->
    <div class="bg-white shadow-md rounded-lg">
        <div class="p-6 text-center">
            <a href="<?php echo BASE_URL; ?>/renk-kartelasi.php" target="_blank"
                class="inline-flex items-center bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-200">
                <i class="fas fa-external-link-alt mr-2"></i>
                Renk Kartelası Sayfasını Görüntüle
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
