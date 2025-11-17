<?php
$pageTitle = isset($_GET['id']) ? 'Blog Düzenle' : 'Yeni Blog Ekle';
require_once 'includes/config.php';
// Header include
include 'includes/header.php';
requireAdminLogin();

$message = '';
$messageType = '';
$blog = null;
$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);

if ($isEdit) {
    $blog = fetchOne("SELECT * FROM blogs WHERE id = ?", [(int)$_GET['id']]);
    if (!$blog) {
        header('Location: blogs.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $focus_keyword = trim($_POST['focus_keyword'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $slug = $_POST['slug'] ?? '';
    if (empty($slug)) {
        $slug = turkishToSlug($title);
    } else {
        $slug = turkishToSlug($slug);
    }
    
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Başlık alanı zorunludur!';
    }
    if (empty($content)) {
        $errors[] = 'İçerik alanı zorunludur!';
    }
    
    if ($isEdit) {
        $existingSlug = fetchOne("SELECT id FROM blogs WHERE slug = ? AND id != ?", [$slug, $blog['id']]);
    } else {
        $existingSlug = fetchOne("SELECT id FROM blogs WHERE slug = ?", [$slug]);
    }
    
    if ($existingSlug) {
        $slug .= '-' . time();
    }
    
    $featured_image = $isEdit ? $blog['featured_image'] : null;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = UPLOAD_DIR . 'blogs/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            if ($isEdit && $blog['featured_image']) {
                $oldImagePath = UPLOAD_DIR . 'blogs/' . basename($blog['featured_image']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            $fileName = uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetPath)) {
                $featured_image = IMAGES_URL . '/blogs/' . $fileName;
            }
        } else {
            $errors[] = 'Geçersiz dosya formatı!';
        }
    }
    
    if (empty($errors)) {
        if ($isEdit) {
            $sql = "UPDATE blogs SET 
                    title = ?, slug = ?, content = ?, excerpt = ?, 
                    featured_image = ?, meta_title = ?, meta_description = ?, 
                    focus_keyword = ?, status = ?, is_featured = ?,
                    published_at = ?,
                    updated_at = NOW() 
                    WHERE id = ?";
            
            $published_at = ($status === 'published' && !$blog['published_at']) ? date('Y-m-d H:i:s') : $blog['published_at'];
            
            $params = [
                $title, $slug, $content, $excerpt, 
                $featured_image, $meta_title, $meta_description, 
                $focus_keyword, $status, $is_featured,
                $published_at,
                $blog['id']
            ];
            
            if (query($sql, $params)) {
                $message = 'Blog başarıyla güncellendi!';
                $messageType = 'success';
                $blog = fetchOne("SELECT * FROM blogs WHERE id = ?", [$blog['id']]);
            } else {
                $message = 'Blog güncellenirken bir hata oluştu!';
                $messageType = 'error';
            }
        } else {
            $sql = "INSERT INTO blogs 
                    (title, slug, content, excerpt, featured_image, meta_title, meta_description, 
                     focus_keyword, status, is_featured, author_id, published_at, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
            
            $params = [
                $title, $slug, $content, $excerpt, $featured_image, $meta_title, $meta_description,
                $focus_keyword, $status, $is_featured, $_SESSION['admin_id'], $published_at
            ];
            
            if (query($sql, $params)) {
                $message = 'Blog başarıyla eklendi!';
                $messageType = 'success';
                $blogId = getLastInsertId();
                header('Location: blog-edit.php?id=' . $blogId . '&success=1');
                exit;
            } else {
                $message = 'Blog eklenirken bir hata oluştu!';
                $messageType = 'error';
            }
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}

function turkishToSlug($text) {
    $turkish = array('İ','ı','Ğ','ğ','Ü','ü','Ş','ş','Ö','ö','Ç','ç','(',')','/',':',',');
    $english = array('i','i','g','g','u','u','s','s','o','o','c','c','','','','','');
    $text = str_replace($turkish, $english, $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Panel</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Quill CSS - Tailwind'den sonra yükle ki override edebilsin -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        /* Sidebar stilleri */
        .menu-item { transition: all 0.2s ease-in-out; position: relative; }
        .menu-item .badge { position: absolute; top: 50%; right: 0.75rem; transform: translateY(-50%); min-width: 1.25rem; height: 1.25rem; padding: 0 0.375rem; }
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        @media (min-width: 768px) {
            .sidebar { transform: translateX(0); }
            .content-transition { margin-left: 16rem; }
            .content-transition.sidebar-closed { margin-left: 0; }
            .sidebar.md\:translate-x-0 { transform: translateX(0) !important; }
        }
        [x-cloak] { display: none !important; }
        
        /* Quill Editor Özel Stilleri */
        #editor-container {
            height: 400px !important;
            background: white !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif !important;
        }
        
        .ql-toolbar.ql-snow {
            background: #f8f9fa !important;
            border: 1px solid #ccc !important;
            border-bottom: none !important;
            border-top-left-radius: 0.5rem !important;
            border-top-right-radius: 0.5rem !important;
            padding: 8px !important;
        }
        
        .ql-container.ql-snow {
            border: 1px solid #ccc !important;
            border-bottom-left-radius: 0.5rem !important;
            border-bottom-right-radius: 0.5rem !important;
            font-size: 16px !important;
            height: calc(100% - 42px) !important;
        }
        
        .ql-editor {
            min-height: 350px !important;
            padding: 15px !important;
            line-height: 1.6 !important;
            font-family: Arial, sans-serif !important;
            font-size: 14px !important;
        }
        
        .ql-editor.ql-blank::before {
            color: #9ca3af !important;
            font-style: normal !important;
            left: 15px !important;
        }
        
        /* Toolbar butonları */
        .ql-toolbar button {
            width: 28px !important;
            height: 28px !important;
            padding: 3px 5px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .ql-toolbar .ql-stroke {
            stroke: #444 !important;
            stroke-width: 2 !important;
            stroke-linecap: round !important;
            stroke-linejoin: round !important;
        }
        
        .ql-toolbar .ql-fill {
            fill: #444 !important;
        }
        
        .ql-toolbar .ql-picker-label {
            color: #444 !important;
            font-size: 14px !important;
            padding: 2px 5px !important;
        }
        
        .ql-toolbar button:hover,
        .ql-toolbar button:focus {
            background: #e5e7eb !important;
            border-radius: 3px !important;
        }
        
        .ql-toolbar button.ql-active {
            background: #dbeafe !important;
            border-radius: 3px !important;
        }
        
        .ql-toolbar button.ql-active .ql-stroke {
            stroke: #06b !important;
        }
        
        .ql-toolbar button.ql-active .ql-fill {
            fill: #06b !important;
        }
        
        /* SVG ikonları */
        .ql-toolbar svg {
            width: 18px !important;
            height: 18px !important;
            display: block !important;
        }
        
        /* Picker stilleri */
        .ql-toolbar .ql-picker {
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #444 !important;
        }
        
        /* Tooltip z-index fix */
        .ql-tooltip {
            z-index: 1070 !important;
        }
        
        .ql-editing {
            z-index: 1070 !important;
        }
    </style>
</head>
<body class="bg-gray-100" x-data="{ sidebarOpen: window.innerWidth >= 768 }">

<?php 
// Admin sidebar
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$unreadMessagesCount = fetchOne("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0")['count'] ?? 0;
?>


<!-- Main Content -->
<div class="md:ml-4">
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20">
        <div class="flex items-center justify-between px-6 py-3">
            <div class="flex items-center">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 mr-3 md:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h1 class="text-xl font-semibold text-gray-800"><?php echo $pageTitle; ?></h1>
            </div>
            <a href="blogs.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                <i class="fas fa-arrow-left mr-2"></i>Geri Dön
            </a>
        </div>
    </header>

    <main class="p-6">
        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                <div class="flex items-center">
                    <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <div><?php echo $message; ?></div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="blogForm">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sol Taraf -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Başlık -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Başlık <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" required
                               value="<?php echo htmlspecialchars($blog['title'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg"
                               onkeyup="document.getElementById('slug').value = generateSlug(this.value)">
                    </div>

                    <!-- URL -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL (Slug)</label>
                        <input type="text" name="slug" id="slug"
                               value="<?php echo htmlspecialchars($blog['slug'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <!-- İçerik -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            İçerik <span class="text-red-500">*</span>
                        </label>
                        <div id="editor-container"></div>
                        <input type="hidden" name="content" id="content" required>
                    </div>

                    <!-- Özet -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Özet</label>
                        <textarea name="excerpt" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg"><?php echo htmlspecialchars($blog['excerpt'] ?? ''); ?></textarea>
                    </div>

                    <!-- SEO -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-search text-red-600 mr-2"></i>SEO Ayarları
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Odak Anahtar Kelime</label>
                                <input type="text" name="focus_keyword" value="<?php echo htmlspecialchars($blog['focus_keyword'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Başlık</label>
                                <input type="text" name="meta_title" maxlength="60" value="<?php echo htmlspecialchars($blog['meta_title'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Açıklama</label>
                                <textarea name="meta_description" rows="3" maxlength="160" class="w-full px-4 py-2 border border-gray-300 rounded-lg"><?php echo htmlspecialchars($blog['meta_description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sağ Taraf -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Yayın -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Yayın</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="draft" <?php echo ($blog['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                                <option value="published" <?php echo ($blog['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Yayında</option>
                            </select>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" <?php echo ($blog['is_featured'] ?? 0) ? 'checked' : ''; ?> class="w-4 h-4 text-red-600">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-star text-yellow-500"></i> Öne Çıkan
                            </span>
                        </label>
                    </div>

                    <!-- Görsel -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Görsel</h3>
                        <?php if ($blog && $blog['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" alt="Görsel" class="w-full h-48 object-cover rounded-lg mb-4">
                        <?php endif; ?>
                        <input type="file" name="featured_image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                    </div>

                    <!-- Kaydet -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 font-semibold">
                            <i class="fas fa-save mr-2"></i>
                            <?php echo $isEdit ? 'Güncelle' : 'Kaydet'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<!-- Quill JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
<script>
console.log('🚀 Quill başlatılıyor...');

if (typeof Quill === 'undefined') {
    alert('Quill editor yüklenemedi!');
} else {
    const quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: {
            toolbar: {
                container: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['blockquote', 'code-block'],
                    ['clean']
                ],
                handlers: {
                    image: imageHandler
                }
            }
        },
        placeholder: 'Blog içeriğini yazın...'
    });
    
    // Resim yükleme fonksiyonu
    function imageHandler() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();
        
        input.onchange = async () => {
            const file = input.files[0];
            if (!file) return;
            
            // Dosya boyutu kontrolü (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Dosya boyutu çok büyük! Maksimum 5MB olabilir.');
                return;
            }
            
            // Yükleniyor mesajı
            const range = quill.getSelection(true);
            quill.insertText(range.index, 'Resim yükleniyor...');
            quill.setSelection(range.index + 20);
            
            // Sunucuya yükle
            const formData = new FormData();
            formData.append('image', file);
            
            try {
                const response = await fetch('upload-blog-image.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                // Yükleniyor metnini sil
                quill.deleteText(range.index, 20);
                
                if (result.success) {
                    // Resmi ekle
                    quill.insertEmbed(range.index, 'image', result.url);
                    quill.setSelection(range.index + 1);
                } else {
                    alert('Resim yüklenemedi: ' + (result.message || 'Bilinmeyen hata'));
                }
            } catch (error) {
                console.error('Resim yükleme hatası:', error);
                quill.deleteText(range.index, 20);
                alert('Resim yüklenirken bir hata oluştu!');
            }
        };
    }
    
    <?php if ($blog && $blog['content']): ?>
    quill.root.innerHTML = <?php echo json_encode($blog['content']); ?>;
    <?php endif; ?>
    
    document.getElementById('blogForm').addEventListener('submit', function(e) {
        document.getElementById('content').value = quill.root.innerHTML;
        if (quill.getText().trim().length === 0) {
            e.preventDefault();
            alert('İçerik boş olamaz!');
        }
    });
    
    console.log('✅ Quill başlatıldı');
    console.log('🖼️ Resim yükleme özelliği aktif');
}

function generateSlug(text) {
    return text.toLowerCase()
        .replace(/ğ/g, 'g').replace(/ü/g, 'u').replace(/ş/g, 's')
        .replace(/ı/g, 'i').replace(/ö/g, 'o').replace(/ç/g, 'c')
        .replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-')
        .replace(/-+/g, '-').trim('-');
}
</script>

</body>
</html>
<?php include 'includes/footer.php'; ?>
