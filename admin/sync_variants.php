<?php
// admin/sync_variants.php - Varyantları teklif sistemine senkronize etme sayfası
require_once '../includes/config.php';
require_once 'includes/sync_variants.php'; // Senkronizasyon sınıfı

$pageTitle = 'Varyant Senkronizasyonu';
$success = '';
$error = '';

// AJAX isteği kontrolü
if (isset($_POST['action'])) {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $syncManager = new VariantSyncManager();
        
        switch ($_POST['action']) {
            case 'sync_all':
                $result = $syncManager->syncAllVariants();
                if ($result['success']) {
                    $response['success'] = true;
                    $response['message'] = "Senkronizasyon tamamlandı!\n" .
                                         "✓ {$result['synced']} ürün başarıyla senkronize edildi\n" .
                                         "⏭ {$result['skipped']} ürün atlandı\n" .
                                         "❌ {$result['errors']} hatası oluştu\n" .
                                         "📊 Toplam {$result['total']} varyant işlendi";
                } else {
                    $response['message'] = 'Hata: ' . $result['error'];
                }
                break;
                
            case 'sync_single':
                $variantId = intval($_POST['variant_id']);
                $result = $syncManager->syncVariantById($variantId);
                $response = $result;
                break;
                
            case 'delete_variant':
                $variantId = intval($_POST['variant_id']);
                if ($syncManager->deleteVariantFromTeklif($variantId)) {
                    $response['success'] = true;
                    $response['message'] = 'Ürün teklif sisteminden silindi';
                } else {
                    $response['message'] = 'Silme işlemi başarısız';
                }
                break;
        }
    } catch (Exception $e) {
        $response['message'] = 'Sistem hatası: ' . $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Varyant listesini al
$variants = fetchAll("
    SELECT 
        pv.*, 
        p.name as product_name, 
        c.name as category_name,
        (SELECT COUNT(*) FROM ecedekor_teklif.products tp WHERE tp.admin_variant_id = pv.id) as is_synced
    FROM product_variants pv
    JOIN products p ON pv.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE pv.is_active = 1 AND p.is_active = 1
    ORDER BY c.name, p.name, pv.sort_order, pv.name
");

// Senkronizasyon istatistikleri
$stats = [
    'total_variants' => count($variants),
    'synced_variants' => 0,
    'unsynced_variants' => 0
];

foreach ($variants as $variant) {
    if ($variant['is_synced'] > 0) {
        $stats['synced_variants']++;
    } else {
        $stats['unsynced_variants']++;
    }
}

include 'includes/header.php';
?>

<style>
.sync-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.variant-row.synced {
    background-color: #f9fdf9;
    border-left: 4px solid #10b981;
}

.variant-row.not-synced {
    background-color: #fefbf9;
    border-left: 4px solid #f59e0b;
}

.sync-status.synced {
    background-color: #d1fae5;
    color: #065f46;
}

.sync-status.not-synced {
    background-color: #fef3c7;
    color: #92400e;
}

#syncProgress {
    display: none;
}

.progress-bar {
    background: linear-gradient(90deg, #10b981, #059669);
    transition: width 0.3s ease;
}
</style>

<!-- Hero Section -->
<div class="sync-card rounded-lg shadow-lg p-8 mb-8">
    <div class="text-center">
        <h1 class="text-3xl font-bold mb-4">🔄 Varyant Senkronizasyonu</h1>
        <p class="text-lg opacity-90 mb-6">
            Admin panelindeki ürün varyantlarını teklif sistemine otomatik olarak aktarın
        </p>
        
        <!-- İstatistikler -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stat-card">
                <div class="text-3xl font-bold text-purple-600 mb-2"><?php echo $stats['total_variants']; ?></div>
                <div class="text-gray-600">Toplam Varyant</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold text-green-600 mb-2"><?php echo $stats['synced_variants']; ?></div>
                <div class="text-gray-600">Senkronize Edilmiş</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold text-orange-600 mb-2"><?php echo $stats['unsynced_variants']; ?></div>
                <div class="text-gray-600">Bekleyen</div>
            </div>
        </div>
        
        <!-- Ana Butonlar -->
        <div class="flex justify-center space-x-4">
            <button onclick="syncAllVariants()" class="bg-white text-purple-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                <i class="fas fa-sync-alt mr-2"></i>Tümünü Senkronize Et
            </button>
            <button onclick="location.reload()" class="bg-white bg-opacity-20 text-white px-8 py-3 rounded-lg font-semibold hover:bg-opacity-30 transition duration-300">
                <i class="fas fa-refresh mr-2"></i>Yenile
            </button>
        </div>
    </div>
</div>

<!-- Progress Bar -->
<div id="syncProgress" class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">Senkronizasyon İlerliyor...</h3>
        <span id="progressText">0%</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-3">
        <div id="progressBar" class="progress-bar h-3 rounded-full" style="width: 0%"></div>
    </div>
    <p id="progressMessage" class="text-sm text-gray-600 mt-2">Başlatılıyor...</p>
</div>

<!-- Varyant Listesi -->
<div class="bg-white rounded-lg shadow-md card">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-black">Varyant Listesi</h2>
        <p class="text-sm text-gray-600">Her varyantı tek tek senkronize edebilir veya senkronizasyon durumunu kontrol edebilirsiniz.</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Varyant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Özellikler</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fiyat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($variants)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aktif varyant bulunamadı.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($variants as $variant): ?>
                        <tr class="variant-row <?php echo $variant['is_synced'] > 0 ? 'synced' : 'not-synced'; ?> hover:bg-gray-50" id="variant-<?php echo $variant['id']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($variant['image']): ?>
                                        <img src="<?php echo $variant['image']; ?>" alt="<?php echo htmlspecialchars($variant['name']); ?>" class="w-10 h-10 object-cover rounded-lg mr-3">
                                    <?php else: ?>
                                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-palette text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="text-sm font-semibold text-black"><?php echo htmlspecialchars($variant['name']); ?></div>
                                        <?php if ($variant['sku']): ?>
                                            <div class="text-sm text-gray-500 font-mono"><?php echo htmlspecialchars($variant['sku']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-black"><?php echo htmlspecialchars($variant['product_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($variant['category_name']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-700">
                                    <?php if ($variant['color']): ?>
                                        <span class="inline-flex items-center bg-red-100 text-red-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                            <?php if ($variant['color_code']): ?>
                                                <span class="w-3 h-3 rounded-full mr-1 border border-gray-300" style="background-color: <?php echo $variant['color_code']; ?>;"></span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($variant['color']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($variant['size']): ?>
                                        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                            <?php echo htmlspecialchars($variant['size']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($variant['weight']): ?>
                                        <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                            <?php echo htmlspecialchars($variant['weight']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($variant['price']): ?>
                                    <span class="text-sm font-semibold text-black">₺<?php echo number_format($variant['price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="sync-status <?php echo $variant['is_synced'] > 0 ? 'synced' : 'not-synced'; ?> inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                    <?php if ($variant['is_synced'] > 0): ?>
                                        <i class="fas fa-check-circle mr-1"></i>Senkronize
                                    <?php else: ?>
                                        <i class="fas fa-clock mr-1"></i>Bekliyor
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="syncSingleVariant(<?php echo $variant['id']; ?>)" class="text-green-600 hover:text-green-700 mr-3" title="Senkronize Et">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <a href="variants.php?action=edit&id=<?php echo $variant['id']; ?>" class="text-red-600 hover:text-red-700" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Tüm varyantları senkronize et
function syncAllVariants() {
    if (!confirm('Tüm varyantları teklif sistemine senkronize etmek istediğinizden emin misiniz?')) {
        return;
    }
    
    showProgress();
    updateProgress(0, 'Senkronizasyon başlatılıyor...');
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=sync_all'
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        hideProgress();
        alert('❌ Bir hata oluştu: ' + error.message);
    });
}

// Tek varyant senkronize et
function syncSingleVariant(variantId) {
    const row = document.getElementById('variant-' + variantId);
    const originalContent = row.innerHTML;
    
    // Loading göster
    row.style.opacity = '0.5';
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=sync_single&variant_id=' + variantId
    })
    .then(response => response.json())
    .then(data => {
        row.style.opacity = '1';
        
        if (data.success) {
            // Başarılı senkronizasyon sonrası satırı güncelle
            row.classList.remove('not-synced');
            row.classList.add('synced');
            
            const statusCell = row.querySelector('.sync-status');
            statusCell.className = 'sync-status synced inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
            statusCell.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Senkronize';
            
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        row.style.opacity = '1';
        alert('❌ Bir hata oluştu: ' + error.message);
    });
}

// Progress bar fonksiyonları
function showProgress() {
    document.getElementById('syncProgress').style.display = 'block';
}

function hideProgress() {
    document.getElementById('syncProgress').style.display = 'none';
}

function updateProgress(percentage, message) {
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressText').textContent = percentage + '%';
    document.getElementById('progressMessage').textContent = message;
}
</script>

<?php include 'includes/footer.php'; ?>