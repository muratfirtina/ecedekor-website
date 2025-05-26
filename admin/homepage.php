<?php
require_once '../includes/config.php';

$pageTitle = 'Ana Sayfa Yönetimi';

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
        $section_type = sanitizeInput($_POST['section_type']);
        $title = sanitizeInput($_POST['title']);
        $subtitle = sanitizeInput($_POST['subtitle']);
        $content = sanitizeInput($_POST['content']);
        $button_text = sanitizeInput($_POST['button_text']);
        $button_link = sanitizeInput($_POST['button_link']);
        $sort_order = intval($_POST['sort_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle file upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadFile($_FILES['image'], 'homepage');
            if (!$image_path) {
                $error = 'Dosya yükleme hatası.';
            }
        }
        
        if (!$error) {
            if ($action === 'add') {
                $sql = "INSERT INTO homepage_sections (section_type, title, subtitle, content, image, button_text, button_link, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$section_type, $title, $subtitle, $content, $image_path, $button_text, $button_link, $sort_order, $is_active];
                
                if (query($sql, $params)) {
                    $success = 'Bölüm başarıyla eklendi.';
                    $action = 'list';
                } else {
                    $error = 'Bölüm eklenirken bir hata oluştu.';
                }
            } elseif ($action === 'edit' && $id) {
                $sql = "UPDATE homepage_sections SET section_type = ?, title = ?, subtitle = ?, content = ?, button_text = ?, button_link = ?, sort_order = ?, is_active = ?";
                $params = [$section_type, $title, $subtitle, $content, $button_text, $button_link, $sort_order, $is_active];
                
                if ($image_path) {
                    $sql .= ", image = ?";
                    $params[] = $image_path;
                }
                
                $sql .= ", updated_at = NOW() WHERE id = ?";
                $params[] = $id;
                
                if (query($sql, $params)) {
                    $success = 'Bölüm başarıyla güncellendi.';
                    $action = 'list';
                } else {
                    $error = 'Bölüm güncellenirken bir hata oluştu.';
                }
            }
        }
    }
}

// Handle delete action
if ($action === 'delete' && $id) {
    if (query("DELETE FROM homepage_sections WHERE id = ?", [$id])) {
        $success = 'Bölüm başarıyla silindi.';
    } else {
        $error = 'Bölüm silinirken bir hata oluştu.';
    }
    $action = 'list';
}

// Get section for editing
$section = null;
if ($action === 'edit' && $id) {
    $section = fetchOne("SELECT * FROM homepage_sections WHERE id = ?", [$id]);
    if (!$section) {
        $error = 'Bölüm bulunamadı.';
        $action = 'list';
    }
}

// Get all homepage sections for listing
if ($action === 'list') {
    $sections = fetchAll("SELECT * FROM homepage_sections ORDER BY sort_order, section_type");
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
    <!-- Homepage Sections List -->
    <div class="bg-white rounded-lg shadow-md card">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-black">Ana Sayfa Bölümleri</h2>
                <a href="?action=add" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Yeni Bölüm
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bölüm</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tür</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İçerik</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sıra</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($sections)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Henüz bölüm eklenmemiş.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sections as $sect): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($sect['image']): ?>
                                            <img src="<?php echo $sect['image']; ?>" alt="<?php echo htmlspecialchars($sect['title']); ?>" class="w-12 h-12 object-cover rounded-lg mr-4">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-semibold text-black"><?php echo htmlspecialchars($sect['title']); ?></div>
                                            <?php if ($sect['subtitle']): ?>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($sect['subtitle']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $typeLabels = [
                                        'hero' => 'Hero Bölümü',
                                        'carousel' => 'Carousel',
                                        'about' => 'Hakkımızda',
                                        'features' => 'Özellikler',
                                        'testimonials' => 'Yorumlar'
                                    ];
                                    $typeColors = [
                                        'hero' => 'bg-red-100 text-red-800',
                                        'carousel' => 'bg-purple-100 text-purple-800',
                                        'about' => 'bg-green-100 text-green-800',
                                        'features' => 'bg-orange-100 text-orange-800',
                                        'testimonials' => 'bg-pink-100 text-pink-800'
                                    ];
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $typeColors[$sect['section_type']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $typeLabels[$sect['section_type']] ?? ucfirst($sect['section_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 max-w-xs">
                                        <?php echo htmlspecialchars(substr($sect['content'], 0, 100)) . (strlen($sect['content']) > 100 ? '...' : ''); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $sect['sort_order']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($sect['is_active']): ?>
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
                                        <button onclick="previewSection(<?php echo htmlspecialchars(json_encode($sect)); ?>)" 
                                                class="text-gray-600 hover:text-gray-700" title="Önizle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="?action=edit&id=<?php echo $sect['id']; ?>" class="text-red-600 hover:text-red-700" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $sect['id']; ?>" 
                                           onclick="return confirmDelete('Bu bölümü silmek istediğinizden emin misiniz?')" 
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
    <!-- Add/Edit Section Form -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-black">
                <?php echo $action === 'add' ? 'Yeni Bölüm Ekle' : 'Bölüm Düzenle'; ?>
            </h2>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="section_type" class="block text-sm font-medium text-gray-700 mb-2">Bölüm Türü *</label>
                    <select name="section_type" id="section_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">Bölüm türü seçin</option>
                        <option value="hero" <?php echo ($section && $section['section_type'] === 'hero') ? 'selected' : ''; ?>>Hero Bölümü</option>
                        <option value="carousel" <?php echo ($section && $section['section_type'] === 'carousel') ? 'selected' : ''; ?>>Carousel</option>
                        <option value="about" <?php echo ($section && $section['section_type'] === 'about') ? 'selected' : ''; ?>>Hakkımızda</option>
                        <option value="features" <?php echo ($section && $section['section_type'] === 'features') ? 'selected' : ''; ?>>Özellikler</option>
                        <option value="testimonials" <?php echo ($section && $section['section_type'] === 'testimonials') ? 'selected' : ''; ?>>Yorumlar</option>
                    </select>
                </div>
                
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sıra Numarası</label>
                    <input type="number" name="sort_order" id="sort_order"
                           value="<?php echo $section ? $section['sort_order'] : '0'; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           placeholder="0">
                </div>
            </div>
            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Başlık *</label>
                <input type="text" name="title" id="title" required
                       value="<?php echo $section ? htmlspecialchars($section['title']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                       placeholder="Bölüm başlığını girin">
            </div>
            
            <div>
                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Alt Başlık</label>
                <input type="text" name="subtitle" id="subtitle"
                       value="<?php echo $section ? htmlspecialchars($section['subtitle']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                       placeholder="Alt başlık girin">
            </div>
            
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">İçerik</label>
                <textarea name="content" id="content" rows="6"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                          placeholder="Bölüm içeriğini yazın..."><?php echo $section ? htmlspecialchars($section['content']) : ''; ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="button_text" class="block text-sm font-medium text-gray-700 mb-2">Buton Metni</label>
                    <input type="text" name="button_text" id="button_text"
                           value="<?php echo $section ? htmlspecialchars($section['button_text']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           placeholder="Buton metnini girin">
                </div>
                
                <div>
                    <label for="button_link" class="block text-sm font-medium text-gray-700 mb-2">Buton Linki</label>
                    <input type="url" name="button_link" id="button_link"
                           value="<?php echo $section ? htmlspecialchars($section['button_link']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           placeholder="Buton linkini girin">
                </div>
            </div>
            
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Bölüm Görseli</label>
                <input type="file" name="image" id="image" accept="image/*"
                       onchange="previewImage(this, 'imagePreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <p class="text-sm text-gray-500 mt-1">Desteklenen formatlar: JPG, PNG, GIF. Maksimum boyut: 5MB</p>
                
                <?php if ($section && $section['image']): ?>
                    <div class="mt-4">
                        <img src="<?php echo $section['image']; ?>" alt="Mevcut görsel" class="w-48 h-32 object-cover rounded-lg">
                        <p class="text-sm text-gray-500 mt-1">Mevcut görsel</p>
                        <div class="mt-2">
                            <div class="text-xs text-gray-500">Tam URL: <?php echo $section['image']; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <img id="imagePreview" src="#" alt="Önizleme" class="hidden w-48 h-32 object-cover rounded-lg mt-4">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       <?php echo (!$section || $section['is_active']) ? 'checked' : ''; ?>
                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                    Aktif (Ana sayfada gösterilsin)
                </label>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>
                    <?php echo $action === 'add' ? 'Bölüm Ekle' : 'Değişiklikleri Kaydet'; ?>
                </button>
                <a href="?action=list" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                    <i class="fas fa-times mr-2"></i>İptal
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-96 overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-black">Bölüm Önizleme</h3>
                <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="previewContent" class="space-y-4">
                <!-- Content will be dynamically inserted here -->
            </div>
        </div>
    </div>
</div>

<script>
function previewSection(section) {
    const modal = document.getElementById('previewModal');
    const content = document.getElementById('previewContent');
    
    content.innerHTML = `
        <div class="border rounded-lg p-6 bg-gray-50">
            ${section.image ? `<img src="${section.image}" alt="${section.title}" class="w-full h-48 object-cover rounded-lg mb-4">` : ''}
            
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        ${section.section_type.charAt(0).toUpperCase() + section.section_type.slice(1)}
                    </span>
                    <span class="text-sm text-gray-500">Sıra: ${section.sort_order}</span>
                </div>
                
                <h2 class="text-2xl font-bold text-black">${section.title}</h2>
                
                ${section.subtitle ? `<h3 class="text-lg text-red-600 font-semibold">${section.subtitle}</h3>` : ''}
                
                ${section.content ? `<p class="text-gray-700 leading-relaxed">${section.content}</p>` : ''}
                
                ${section.button_text ? `
                    <div class="pt-4">
                        <span class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg font-semibold">
                            ${section.button_text}
                        </span>
                        ${section.button_link ? `<div class="text-xs text-gray-500 mt-1">Link: ${section.button_link}</div>` : ''}
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('previewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
