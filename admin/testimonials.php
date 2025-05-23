<?php
require_once '../includes/config.php';

$pageTitle = 'Müşteri Yorumları';

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
        $company = sanitizeInput($_POST['company']);
        $position = sanitizeInput($_POST['position']);
        $content = sanitizeInput($_POST['content']);
        $rating = intval($_POST['rating']);
        $sort_order = intval($_POST['sort_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate rating
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }
        
        // Handle file upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadFile($_FILES['image'], 'testimonials');
            if (!$image_path) {
                $error = 'Dosya yükleme hatası.';
            }
        }
        
        if (!$error) {
            if ($action === 'add') {
                $sql = "INSERT INTO testimonials (name, company, position, content, image, rating, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$name, $company, $position, $content, $image_path, $rating, $sort_order, $is_active];
                
                if (query($sql, $params)) {
                    $success = 'Yorum başarıyla eklendi.';
                    $action = 'list';
                } else {
                    $error = 'Yorum eklenirken bir hata oluştu.';
                }
            } elseif ($action === 'edit' && $id) {
                $sql = "UPDATE testimonials SET name = ?, company = ?, position = ?, content = ?, rating = ?, sort_order = ?, is_active = ?";
                $params = [$name, $company, $position, $content, $rating, $sort_order, $is_active];
                
                if ($image_path) {
                    $sql .= ", image = ?";
                    $params[] = $image_path;
                }
                
                $sql .= " WHERE id = ?";
                $params[] = $id;
                
                if (query($sql, $params)) {
                    $success = 'Yorum başarıyla güncellendi.';
                    $action = 'list';
                } else {
                    $error = 'Yorum güncellenirken bir hata oluştu.';
                }
            }
        }
    }
}

// Handle delete action
if ($action === 'delete' && $id) {
    if (query("DELETE FROM testimonials WHERE id = ?", [$id])) {
        $success = 'Yorum başarıyla silindi.';
    } else {
        $error = 'Yorum silinirken bir hata oluştu.';
    }
    $action = 'list';
}

// Get testimonial for editing
$testimonial = null;
if ($action === 'edit' && $id) {
    $testimonial = fetchOne("SELECT * FROM testimonials WHERE id = ?", [$id]);
    if (!$testimonial) {
        $error = 'Yorum bulunamadı.';
        $action = 'list';
    }
}

// Get all testimonials for listing
if ($action === 'list') {
    $testimonials = fetchAll("SELECT * FROM testimonials ORDER BY sort_order, created_at DESC");
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
    <!-- Testimonials List -->
    <div class="bg-white rounded-lg shadow-md card">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Müşteri Yorumları</h2>
                <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Yeni Yorum
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yorum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puanlama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sıra</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($testimonials)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">Henüz yorum eklenmemiş.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($testimonials as $test): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($test['image']): ?>
                                            <img src="<?php echo $test['image']; ?>" alt="<?php echo htmlspecialchars($test['name']); ?>" class="w-12 h-12 object-cover rounded-full mr-4">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-user text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($test['name']); ?></div>
                                            <?php if ($test['company']): ?>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($test['company']); ?></div>
                                            <?php endif; ?>
                                            <?php if ($test['position']): ?>
                                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($test['position']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 max-w-xs">
                                        <?php echo htmlspecialchars(substr($test['content'], 0, 120)) . (strlen($test['content']) > 120 ? '...' : ''); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex text-yellow-400">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $test['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="text-xs text-gray-500"><?php echo $test['rating']; ?>/5</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $test['sort_order']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($test['is_active']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle mr-1 text-xs"></i>Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-circle mr-1 text-xs"></i>Pasif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d.m.Y', strtotime($test['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="showTestimonialPreview(<?php echo htmlspecialchars(json_encode($test)); ?>)" 
                                                class="text-gray-600 hover:text-gray-700" title="Önizle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="?action=edit&id=<?php echo $test['id']; ?>" class="text-blue-600 hover:text-blue-700" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $test['id']; ?>" 
                                           onclick="return confirmDelete('Bu yorumu silmek istediğinizden emin misiniz?')" 
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
    <!-- Add/Edit Testimonial Form -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">
                <?php echo $action === 'add' ? 'Yeni Yorum Ekle' : 'Yorum Düzenle'; ?>
            </h2>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Müşteri Adı *</label>
                    <input type="text" name="name" id="name" required
                           value="<?php echo $testimonial ? htmlspecialchars($testimonial['name']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Müşteri adını girin">
                </div>
                
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Şirket</label>
                    <input type="text" name="company" id="company"
                           value="<?php echo $testimonial ? htmlspecialchars($testimonial['company']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Şirket adını girin">
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700 mb-2">Pozisyon</label>
                    <input type="text" name="position" id="position"
                           value="<?php echo $testimonial ? htmlspecialchars($testimonial['position']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Pozisyon bilgisini girin">
                </div>
                
                <div>
                    <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">Puanlama *</label>
                    <select name="rating" id="rating" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>" 
                                    <?php echo ($testimonial && $testimonial['rating'] == $i) || (!$testimonial && $i == 5) ? 'selected' : ''; ?>>
                                <?php echo $i; ?> Yıldız
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sıra Numarası</label>
                    <input type="number" name="sort_order" id="sort_order"
                           value="<?php echo $testimonial ? $testimonial['sort_order'] : '0'; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0">
                </div>
            </div>
            
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Yorum İçeriği *</label>
                <textarea name="content" id="content" rows="6" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Müşteri yorumunu buraya yazın..."><?php echo $testimonial ? htmlspecialchars($testimonial['content']) : ''; ?></textarea>
                <p class="text-sm text-gray-500 mt-1">Müşterinin ürün veya hizmet hakkındaki görüşlerini yazın</p>
            </div>
            
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Müşteri Fotoğrafı</label>
                <input type="file" name="image" id="image" accept="image/*"
                       onchange="previewImage(this, 'imagePreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-sm text-gray-500 mt-1">Desteklenen formatlar: JPG, PNG, GIF. Maksimum boyut: 5MB</p>
                
                <?php if ($testimonial && $testimonial['image']): ?>
                    <div class="mt-4">
                        <img src="<?php echo $testimonial['image']; ?>" alt="Mevcut fotoğraf" class="w-24 h-24 object-cover rounded-full">
                        <p class="text-sm text-gray-500 mt-1">Mevcut fotoğraf</p>
                    </div>
                <?php endif; ?>
                
                <img id="imagePreview" src="#" alt="Önizleme" class="hidden w-24 h-24 object-cover rounded-full mt-4">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       <?php echo (!$testimonial || $testimonial['is_active']) ? 'checked' : ''; ?>
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                    Aktif (Ana sayfada gösterilsin)
                </label>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>
                    <?php echo $action === 'add' ? 'Yorum Ekle' : 'Değişiklikleri Kaydet'; ?>
                </button>
                <a href="?action=list" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                    <i class="fas fa-times mr-2"></i>İptal
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- Testimonial Preview Modal -->
<div id="testimonialModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Yorum Önizleme</h3>
                <button onclick="closeTestimonialPreview()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="testimonialPreviewContent" class="space-y-4">
                <!-- Content will be dynamically inserted here -->
            </div>
        </div>
    </div>
</div>

<script>
function showTestimonialPreview(testimonial) {
    const modal = document.getElementById('testimonialModal');
    const content = document.getElementById('testimonialPreviewContent');
    
    // Create rating stars
    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
        starsHtml += `<i class="fas fa-star ${i <= testimonial.rating ? 'text-yellow-400' : 'text-gray-300'}"></i>`;
    }
    
    content.innerHTML = `
        <div class="flex items-center mb-4">
            ${testimonial.image ? 
                `<img src="${testimonial.image}" alt="${testimonial.name}" class="w-16 h-16 object-cover rounded-full mr-4">` :
                `<div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-gray-400 text-xl"></i>
                </div>`
            }
            <div>
                <div class="font-semibold text-gray-900 text-lg">${testimonial.name}</div>
                ${testimonial.company ? `<div class="text-gray-600">${testimonial.company}</div>` : ''}
                ${testimonial.position ? `<div class="text-sm text-gray-500">${testimonial.position}</div>` : ''}
            </div>
        </div>
        
        <div class="flex mb-4">
            ${starsHtml}
        </div>
        
        <blockquote class="text-gray-700 italic border-l-4 border-blue-500 pl-4">
            "${testimonial.content}"
        </blockquote>
        
        <div class="text-sm text-gray-500 mt-4">
            Ekleme Tarihi: ${new Date(testimonial.created_at).toLocaleDateString('tr-TR')}
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeTestimonialPreview() {
    document.getElementById('testimonialModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('testimonialModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTestimonialPreview();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
