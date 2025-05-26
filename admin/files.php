<?php
require_once '../includes/config.php';

$pageTitle = 'Dosya Yöneticisi';

$success = '';
$error = '';
$currentDir = $_GET['dir'] ?? 'uploads';

// Sanitize directory path
$currentDir = str_replace(['..', '//', '\\'], '', $currentDir);
$fullPath = UPLOAD_DIR . $currentDir . '/';

// Create directory if it doesn't exist
if (!is_dir($fullPath)) {
    mkdir($fullPath, 0755, true);
}

// Handle file upload
if ($_POST && isset($_FILES['file'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        $uploadedFile = uploadFile($_FILES['file'], $currentDir);
        if ($uploadedFile) {
            $success = 'Dosya başarıyla yüklendi.';
        } else {
            $error = 'Dosya yüklenirken bir hata oluştu.';
        }
    }
}

// Handle file deletion
if ($_GET['action'] === 'delete' && isset($_GET['file'])) {
    $fileName = basename($_GET['file']);
    $filePath = $fullPath . $fileName;
    
    if (file_exists($filePath) && !is_dir($filePath)) {
        if (unlink($filePath)) {
            $success = 'Dosya başarıyla silindi.';
        } else {
            $error = 'Dosya silinirken bir hata oluştu.';
        }
    }
}

// Handle directory creation
if ($_POST && isset($_POST['new_dir'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        $newDirName = sanitizeInput($_POST['new_dir']);
        $newDirPath = $fullPath . $newDirName;
        
        if (!empty($newDirName) && !file_exists($newDirPath)) {
            if (mkdir($newDirPath, 0755)) {
                $success = 'Klasör başarıyla oluşturuldu.';
            } else {
                $error = 'Klasör oluşturulurken bir hata oluştu.';
            }
        } else {
            $error = 'Bu isimde bir klasör zaten mevcut veya geçersiz isim.';
        }
    }
}

// Get directory contents
$files = [];
$directories = [];

if (is_dir($fullPath)) {
    $items = scandir($fullPath);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $itemPath = $fullPath . $item;
        if (is_dir($itemPath)) {
            $directories[] = [
                'name' => $item,
                'path' => $currentDir . '/' . $item,
                'modified' => filemtime($itemPath)
            ];
        } else {
            $fileInfo = pathinfo($itemPath);
            $files[] = [
                'name' => $item,
                'path' => $itemPath,
                'url' => '/assets/images/' . $currentDir . '/' . $item,
                'size' => filesize($itemPath),
                'modified' => filemtime($itemPath),
                'extension' => $fileInfo['extension'] ?? '',
                'is_image' => in_array(strtolower($fileInfo['extension'] ?? ''), ['jpg', 'jpeg', 'png', 'gif', 'webp'])
            ];
        }
    }
}

// Sort files and directories
usort($directories, fn($a, $b) => strcmp($a['name'], $b['name']));
usort($files, fn($a, $b) => strcmp($a['name'], $b['name']));

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

<!-- Toolbar -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6 card">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2">
            <i class="fas fa-folder text-gray-400"></i>
            <span class="text-gray-600">Konum:</span>
            <?php
            $pathParts = explode('/', trim($currentDir, '/'));
            $breadcrumbPath = '';
            ?>
            <a href="?dir=uploads" class="text-red-600 hover:text-red-700">uploads</a>
            <?php foreach ($pathParts as $part): ?>
                <?php if (!empty($part)): ?>
                    <?php $breadcrumbPath .= '/' . $part; ?>
                    <span class="text-gray-400">/</span>
                    <a href="?dir=uploads<?php echo $breadcrumbPath; ?>" class="text-red-600 hover:text-red-700">
                        <?php echo htmlspecialchars($part); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <!-- Actions -->
        <div class="flex space-x-2">
            <button onclick="showUploadModal()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                <i class="fas fa-upload mr-2"></i>Dosya Yükle
            </button>
            <button onclick="showNewFolderModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300">
                <i class="fas fa-folder-plus mr-2"></i>Yeni Klasör
            </button>
        </div>
    </div>
</div>

<!-- File Browser -->
<div class="bg-white rounded-lg shadow-md card">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-black">Dosyalar ve Klasörler</h2>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 p-6">
        <!-- Parent Directory -->
        <?php if ($currentDir !== 'uploads'): ?>
            <?php
            $parentDir = dirname($currentDir);
            if ($parentDir === '.') $parentDir = 'uploads';
            ?>
            <a href="?dir=<?php echo $parentDir; ?>" class="group p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300 text-center">
                <i class="fas fa-level-up-alt text-3xl text-gray-400 group-hover:text-gray-600 mb-2"></i>
                <div class="text-sm text-gray-600 truncate">.. (Üst Klasör)</div>
            </a>
        <?php endif; ?>
        
        <!-- Directories -->
        <?php foreach ($directories as $dir): ?>
            <a href="?dir=<?php echo htmlspecialchars($dir['path']); ?>" class="group p-4 border border-gray-200 rounded-lg hover:bg-red-50 transition duration-300 text-center">
                <i class="fas fa-folder text-3xl text-red-500 group-hover:text-red-600 mb-2"></i>
                <div class="text-sm text-gray-700 truncate" title="<?php echo htmlspecialchars($dir['name']); ?>">
                    <?php echo htmlspecialchars($dir['name']); ?>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    <?php echo date('d.m.Y H:i', $dir['modified']); ?>
                </div>
            </a>
        <?php endforeach; ?>
        
        <!-- Files -->
        <?php foreach ($files as $file): ?>
            <div class="group p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300 text-center relative">
                <?php if ($file['is_image']): ?>
                    <div class="w-16 h-16 mx-auto mb-2 bg-gray-100 rounded-lg overflow-hidden">
                        <img src="<?php echo $file['url']; ?>" alt="<?php echo htmlspecialchars($file['name']); ?>" 
                             class="w-full h-full object-cover cursor-pointer" 
                             onclick="showImageModal('<?php echo $file['url']; ?>', '<?php echo htmlspecialchars($file['name']); ?>')">
                    </div>
                <?php else: ?>
                    <i class="fas fa-file text-3xl text-gray-400 mb-2"></i>
                <?php endif; ?>
                
                <div class="text-sm text-gray-700 truncate" title="<?php echo htmlspecialchars($file['name']); ?>">
                    <?php echo htmlspecialchars($file['name']); ?>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    <?php echo formatFileSize($file['size']); ?>
                </div>
                <div class="text-xs text-gray-500">
                    <?php echo date('d.m.Y H:i', $file['modified']); ?>
                </div>
                
                <!-- Actions -->
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition duration-300">
                    <div class="flex space-x-1">
                        <button onclick="copyToClipboard('<?php echo BASE_URL . $file['url']; ?>')" 
                                class="w-6 h-6 bg-red-600 text-white rounded text-xs hover:bg-red-700" 
                                title="Link Kopyala">
                            <i class="fas fa-link"></i>
                        </button>
                        <a href="?action=delete&file=<?php echo urlencode($file['name']); ?>&dir=<?php echo urlencode($currentDir); ?>" 
                           onclick="return confirmDelete('Bu dosyayı silmek istediğinizden emin misiniz?')"
                           class="w-6 h-6 bg-red-600 text-white rounded text-xs hover:bg-red-700 flex items-center justify-center" 
                           title="Sil">
                            <i class="fas fa-trash text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Empty State -->
        <?php if (empty($directories) && empty($files) && $currentDir === 'uploads'): ?>
            <div class="col-span-full text-center py-12">
                <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Henüz dosya yok</h3>
                <p class="text-gray-500">İlk dosyanızı yükleyerek başlayın</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-black">Dosya Yükle</h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Dosya Seçin</label>
                    <input type="file" name="file" id="file" required accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <p class="text-sm text-gray-500 mt-1">Maksimum boyut: 5MB</p>
                </div>
                
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition duration-300">
                        <i class="fas fa-upload mr-2"></i>Yükle
                    </button>
                    <button type="button" onclick="closeUploadModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition duration-300">
                        İptal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Folder Modal -->
<div id="newFolderModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-black">Yeni Klasör</h3>
                <button onclick="closeNewFolderModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="mb-6">
                    <label for="new_dir" class="block text-sm font-medium text-gray-700 mb-2">Klasör Adı</label>
                    <input type="text" name="new_dir" id="new_dir" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           placeholder="Klasör adını girin">
                </div>
                
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-300">
                        <i class="fas fa-folder-plus mr-2"></i>Oluştur
                    </button>
                    <button type="button" onclick="closeNewFolderModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition duration-300">
                        İptal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-full overflow-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 id="imageModalTitle" class="text-xl font-semibold text-black"></h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="text-center">
                <img id="imageModalContent" src="" alt="" class="max-w-full max-h-96 mx-auto rounded-lg">
            </div>
        </div>
    </div>
</div>

<script>
function showUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}

function showNewFolderModal() {
    document.getElementById('newFolderModal').classList.remove('hidden');
}

function closeNewFolderModal() {
    document.getElementById('newFolderModal').classList.add('hidden');
}

function showImageModal(src, title) {
    document.getElementById('imageModalContent').src = src;
    document.getElementById('imageModalTitle').textContent = title;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showMessage('Link panoya kopyalandı!', 'success');
    });
}
</script>

<?php
function formatFileSize($size) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}
?>

<?php include 'includes/footer.php'; ?>
