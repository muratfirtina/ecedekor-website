<?php
$pageTitle = 'Blog Yönetimi';
require_once 'includes/config.php';
requireAdminLogin();

// Mesajlar için
$message = '';
$messageType = '';

// Blog silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Önce görseli sil
    $blog = fetchOne("SELECT featured_image FROM blogs WHERE id = ?", [$id]);
    if ($blog && $blog['featured_image']) {
        $imagePath = UPLOAD_DIR . 'blogs/' . basename($blog['featured_image']);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    if (query("DELETE FROM blogs WHERE id = ?", [$id])) {
        $message = 'Blog başarıyla silindi!';
        $messageType = 'success';
    } else {
        $message = 'Blog silinirken bir hata oluştu!';
        $messageType = 'error';
    }
}

// Blog durumu değiştirme
if (isset($_POST['toggle_status'])) {
    $id = (int)$_POST['blog_id'];
    $currentStatus = $_POST['current_status'];
    $newStatus = ($currentStatus === 'published') ? 'draft' : 'published';
    
    if (query("UPDATE blogs SET status = ? WHERE id = ?", [$newStatus, $id])) {
        $message = 'Blog durumu güncellendi!';
        $messageType = 'success';
    }
}

// Blogları getir - BASIT SORGU (JOIN OLMADAN)
try {
    $blogs = fetchAll("
        SELECT * FROM blogs 
        ORDER BY created_at DESC
    ");
    
    // Her blog için yazar bilgisini ayrı ayrı çek
    foreach ($blogs as &$blog) {
        if ($blog['author_id']) {
            // Önce admin_users'dan dene
            $author = fetchOne("SELECT full_name FROM admin_users WHERE id = ?", [$blog['author_id']]);
            if (!$author) {
                // users tablosundan dene
                $author = fetchOne("SELECT full_name FROM users WHERE id = ?", [$blog['author_id']]);
            }
            $blog['author_name'] = $author ? $author['full_name'] : 'Bilinmiyor';
        } else {
            $blog['author_name'] = 'Sistem';
        }
    }
} catch (Exception $e) {
    $message = 'Bloglar yüklenirken hata oluştu: ' . $e->getMessage();
    $messageType = 'error';
    $blogs = [];
}

include 'includes/header.php';
?>

<div class="container mx-auto">
    <!-- Başlık ve Yeni Blog Butonu -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Blog Yönetimi</h1>
            <p class="text-gray-600 mt-1">Blog yazılarınızı buradan yönetebilirsiniz</p>
        </div>
        <div class="flex gap-2">
            <a href="blog-add.php" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                <i class="fas fa-plus mr-2"></i>
                Yeni Blog Ekle
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
            <div class="flex items-center">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Blog Listesi -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Blog Bilgileri
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            SEO
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            İstatistikler
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Durum
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            İşlemler
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($blogs)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-blog text-4xl mb-2 text-gray-300"></i>
                                <p class="mb-2">Henüz blog eklenmemiş</p>
                                <a href="debug-blog.php" class="text-blue-600 hover:underline">Debug sayfasını kontrol edin →</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($blogs as $blog): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if ($blog['featured_image']): ?>
                                            <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($blog['title']); ?>" 
                                                 class="w-16 h-16 rounded-lg object-cover mr-4">
                                        <?php else: ?>
                                            <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center mr-4">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($blog['title']); ?>
                                                <?php if ($blog['is_featured']): ?>
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-star mr-1"></i> Öne Çıkan
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($blog['excerpt'] ? substr($blog['excerpt'], 0, 60) . '...' : ''); ?>
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                <i class="far fa-user mr-1"></i><?php echo htmlspecialchars($blog['author_name'] ?? 'Bilinmiyor'); ?>
                                                <span class="mx-2">•</span>
                                                <i class="far fa-clock mr-1"></i><?php echo date('d.m.Y H:i', strtotime($blog['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <?php if ($blog['focus_keyword']): ?>
                                            <div class="mb-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-key mr-1"></i><?php echo htmlspecialchars($blog['focus_keyword']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($blog['meta_description']): ?>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-align-left mr-1"></i>Meta açıklama var
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div class="flex items-center mb-1">
                                            <i class="far fa-eye text-gray-400 mr-2"></i>
                                            <?php echo number_format($blog['views']); ?> görüntüleme
                                        </div>
                                        <?php if ($blog['published_at']): ?>
                                            <div class="text-xs text-gray-500">
                                                Yayın: <?php echo date('d.m.Y', strtotime($blog['published_at'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $blog['status']; ?>">
                                        <button type="submit" name="toggle_status" 
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium transition duration-200 
                                                <?php echo $blog['status'] === 'published' 
                                                    ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                                                    : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'; ?>">
                                            <i class="fas <?php echo $blog['status'] === 'published' ? 'fa-check-circle' : 'fa-clock'; ?> mr-1"></i>
                                            <?php echo $blog['status'] === 'published' ? 'Yayında' : 'Taslak'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $blog['slug']; ?>" 
                                           target="_blank"
                                           class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition duration-200" 
                                           title="Görüntüle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="blog-edit.php?id=<?php echo $blog['id']; ?>" 
                                           class="text-indigo-600 hover:text-indigo-900 p-2 rounded-lg hover:bg-indigo-50 transition duration-200" 
                                           title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $blog['id']; ?>" 
                                           onclick="return confirm('Bu blogu silmek istediğinizden emin misiniz?')" 
                                           class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition duration-200" 
                                           title="Sil">
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
</div>

<?php include 'includes/footer.php'; ?>
