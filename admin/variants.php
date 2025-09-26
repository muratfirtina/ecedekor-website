<?php
require_once '../includes/config.php';

$pageTitle = 'Ürün Varyantları';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;
$product_id = $_GET['product_id'] ?? 0;
$success = '';
$error = '';

// Helper function for Excel import
function processExcelImport($data) {
    $addedCount = 0;
    $updatedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($data as $index => $row) {
        $rowNumber = $index + 2; // Excel'de satır numarası (başlık hariç)
        
        try {
            // Zorunlu alanları kontrol et
            if (empty($row['product_id']) || empty($row['name'])) {
                $skippedCount++;
                $errors[] = "Satır $rowNumber: Ürün ID ve Varyant Adı zorunludur";
                continue;
            }
            
            $product_id = intval($row['product_id']);
            $name = sanitizeInput($row['name']);
            $color = sanitizeInput($row['color'] ?? '');
            $color_code = sanitizeInput($row['color_code'] ?? '');
            $size = sanitizeInput($row['size'] ?? '');
            $weight = sanitizeInput($row['weight'] ?? '');
            $sku = sanitizeInput($row['sku'] ?? '');
            $price = floatval($row['price'] ?? 0);
            $sort_order = intval($row['sort_order'] ?? 0);
            $is_active = ($row['is_active'] ?? 'aktif') === 'aktif' ? 1 : 0;
            
            // Ürün var mı kontrol et
            $productExists = fetchOne("SELECT id FROM products WHERE id = ?", [$product_id]);
            if (!$productExists) {
                $errorCount++;
                $errors[] = "Satır $rowNumber: Ürün ID $product_id bulunamadı";
                continue;
            }
            
            // Mevcut varyant kontrolü - SKU ile önce kontrol et
            $existingVariant = null;
            if ($sku) {
                $existingVariant = fetchOne("SELECT id FROM product_variants WHERE sku = ?", [$sku]);
                if ($existingVariant) {
                    // SKU ile eşleşen varyant var, güncelle
                    $updateSuccess = updateExistingVariant($existingVariant['id'], $name, $color, $color_code, $size, $weight, $price, $sort_order, $is_active);
                    if ($updateSuccess) {
                        $updatedCount++;
                        $errors[] = "Satır $rowNumber: SKU '$sku' ile eşleşen varyant güncellendi";
                    } else {
                        $errorCount++;
                        $errors[] = "Satır $rowNumber: SKU '$sku' güncellenirken hata oluştu";
                    }
                    continue;
                }
            }
            
            // SKU yoksa, product_id + name + color kombinasyonuyla kontrol et
            if (!$sku) {
                $whereClause = "product_id = ? AND name = ?";
                $params = [$product_id, $name];
                
                if ($color) {
                    $whereClause .= " AND color = ?";
                    $params[] = $color;
                }
                
                $existingVariant = fetchOne("SELECT id FROM product_variants WHERE $whereClause", $params);
                if ($existingVariant) {
                    // Aynı isim ve renkte varyant var, güncelle
                    $updateSuccess = updateExistingVariant($existingVariant['id'], $name, $color, $color_code, $size, $weight, $price, $sort_order, $is_active, $sku);
                    if ($updateSuccess) {
                        $updatedCount++;
                        $errors[] = "Satır $rowNumber: Mevcut varyant '$name' güncellendi";
                    } else {
                        $errorCount++;
                        $errors[] = "Satır $rowNumber: Varyant '$name' güncellenirken hata oluştu";
                    }
                    continue;
                }
            }
            
            // Yeni varyant ekle
            $sql = "INSERT INTO product_variants (product_id, name, color, color_code, size, weight, sku, price, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$product_id, $name, $color, $color_code, $size, $weight, $sku, $price, $sort_order, $is_active];
            
            $result = query($sql, $params);
            if ($result) {
                $addedCount++;
                $errors[] = "Satır $rowNumber: Yeni varyant '$name' eklendi";
            } else {
                $errorCount++;
                $errors[] = "Satır $rowNumber: Veritabanı kayıt hatası - Muhtemelen duplicate entry";
            }
            
        } catch (Exception $e) {
            $errorCount++;
            $errors[] = "Satır $rowNumber: " . $e->getMessage();
        }
    }
    
    return [
        'success' => $addedCount > 0 || $updatedCount > 0,
        'added' => $addedCount,
        'updated' => $updatedCount,
        'skipped' => $skippedCount,
        'errors' => $errorCount,
        'error_details' => $errors,
        'message' => ($addedCount > 0 || $updatedCount > 0) ? 'İçe aktarım başarılı' : 'Hiçbir varyant eklenemedi veya güncellenmedi'
    ];
}

// Yardımcı fonksiyon - mevcut varyantı güncelle
function updateExistingVariant($variantId, $name, $color, $color_code, $size, $weight, $price, $sort_order, $is_active, $sku = null) {
    $sql = "UPDATE product_variants SET name = ?, color = ?, color_code = ?, size = ?, weight = ?, price = ?, sort_order = ?, is_active = ?, updated_at = NOW()";
    $params = [$name, $color, $color_code, $size, $weight, $price, $sort_order, $is_active];
    
    if ($sku !== null) {
        $sql .= ", sku = ?";
        $params[] = $sku;
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $variantId;
    
    return query($sql, $params);
}

// Handle form submissions
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        if ($action === 'inline_update') {
            // Handle inline editing
            $variant_id = intval($_POST['variant_id']);
            $field = $_POST['field'];
            $value = $_POST['value'];
            
            $allowed_fields = ['sku', 'price', 'weight', 'name'];
            
            if (in_array($field, $allowed_fields) && $variant_id > 0) {
                // Special validation for SKU
                if ($field === 'sku' && $value) {
                    $existingSku = fetchOne("SELECT id FROM product_variants WHERE sku = ? AND id != ?", [$value, $variant_id]);
                    if ($existingSku) {
                        echo json_encode(['success' => false, 'message' => 'Bu SKU zaten kullanılıyor.']);
                        exit;
                    }
                }
                
                // Special validation for price
                if ($field === 'price') {
                    $value = floatval($value);
                    if ($value < 0) {
                        echo json_encode(['success' => false, 'message' => 'Fiyat negatif olamaz.']);
                        exit;
                    }
                }
                
                $sql = "UPDATE product_variants SET $field = ?, updated_at = NOW() WHERE id = ?";
                $params = [$value, $variant_id];
                
                if (query($sql, $params)) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Güncellendi.',
                        'formatted_value' => $field === 'price' ? '₺' . number_format($value, 2) : $value
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Güncelleme hatası.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Geçersiz alan.']);
            }
            exit;
        } elseif ($action === 'bulk_update') {
            // Handle bulk update
            $selected_variants = $_POST['selected_variants'] ?? [];
            $bulk_price = $_POST['bulk_price'] ?? '';
            $bulk_sku_prefix = $_POST['bulk_sku_prefix'] ?? '';
            $bulk_weight = $_POST['bulk_weight'] ?? '';
            $bulk_is_active = $_POST['bulk_is_active'] ?? '';
            
            if (empty($selected_variants)) {
                $error = 'Güncellenecek varyant seçmediniz.';
            } else {
                $update_count = 0;
                $errors = [];
                
                foreach ($selected_variants as $variant_id) {
                    $variant_id = intval($variant_id);
                    $updates = [];
                    $params = [];
                    
                    // Build update query based on filled fields
                    if ($bulk_price !== '') {
                        $updates[] = "price = ?";
                        $params[] = floatval($bulk_price);
                    }
                    
                    if ($bulk_sku_prefix !== '') {
                        // Generate SKU with prefix + variant ID
                        $new_sku = $bulk_sku_prefix . $variant_id;
                        // Check if SKU already exists
                        $existingSku = fetchOne("SELECT id FROM product_variants WHERE sku = ? AND id != ?", [$new_sku, $variant_id]);
                        if (!$existingSku) {
                            $updates[] = "sku = ?";
                            $params[] = $new_sku;
                        } else {
                            $errors[] = "SKU '$new_sku' zaten kullanılıyor (ID: $variant_id)";
                        }
                    }
                    
                    if ($bulk_weight !== '') {
                        $updates[] = "weight = ?";
                        $params[] = $bulk_weight;
                    }
                    
                    if ($bulk_is_active !== '') {
                        $updates[] = "is_active = ?";
                        $params[] = intval($bulk_is_active);
                    }
                    
                    if (!empty($updates)) {
                        $updates[] = "updated_at = NOW()";
                        $params[] = $variant_id;
                        
                        $sql = "UPDATE product_variants SET " . implode(", ", $updates) . " WHERE id = ?";
                        
                        if (query($sql, $params)) {
                            $update_count++;
                        } else {
                            $errors[] = "ID $variant_id güncellenemedi";
                        }
                    }
                }
                
                if ($update_count > 0) {
                    $success = "$update_count varyant başarıyla güncellendi.";
                    if (!empty($errors)) {
                        $success .= " Bazı hatalar: " . implode(', ', $errors);
                    }
                } else {
                    $error = "Hiçbir varyant güncellenemedi. " . implode(', ', $errors);
                }
            }
        } elseif ($action === 'bulk_delete') {
            // Handle bulk delete
            $selected_variants = $_POST['selected_variants'] ?? [];
            
            if (empty($selected_variants)) {
                $error = 'Silinecek varyant seçmediniz.';
            } else {
                $delete_count = 0;
                $errors = [];
                
                foreach ($selected_variants as $variant_id) {
                    $variant_id = intval($variant_id);
                    
                    // Varyant bilgisini al (log için)
                    $variantInfo = fetchOne("SELECT name, sku FROM product_variants WHERE id = ?", [$variant_id]);
                    
                    if (query("DELETE FROM product_variants WHERE id = ?", [$variant_id])) {
                        $delete_count++;
                    } else {
                        $variantName = $variantInfo ? $variantInfo['name'] : "ID: $variant_id";
                        $errors[] = "$variantName silinemedi";
                    }
                }
                
                if ($delete_count > 0) {
                    $success = "$delete_count varyant başarıyla silindi.";
                    if (!empty($errors)) {
                        $success .= " Bazı hatalar: " . implode(', ', $errors);
                    }
                } else {
                    $error = "Hiçbir varyant silinemedi. " . implode(', ', $errors);
                }
            }
        } elseif ($action === 'excel_import') {
            // Handle Excel Import
            $importData = $_POST['import_data'] ?? '';
            
            if (empty($importData)) {
                $error = 'İçe aktarılacak veri bulunamadı.';
            } else {
                $data = json_decode($importData, true);
                
                if (!$data || !is_array($data)) {
                    $error = 'Geçersiz veri formatı.';
                } else {
                    $importResults = processExcelImport($data);
                    
                    if ($importResults['success']) {
                        $success = "Excel içe aktarımı tamamlandı!\n" .
                                 "✓ {$importResults['added']} yeni varyant eklendi\n" .
                                 "🔄 {$importResults['updated']} mevcut varyant güncellendi\n" .
                                 "⏭ {$importResults['skipped']} satır atlandı\n" .
                                 "❌ {$importResults['errors']} hatası oluştu\n" .
                                 "📊 Toplam " . (count($data)) . " satır işlendi";
                        
                        if (!empty($importResults['error_details'])) {
                            $success .= "\n\nDetaylar:\n" . implode("\n", array_slice($importResults['error_details'], 0, 10));
                            if (count($importResults['error_details']) > 10) {
                                $success .= "\n... ve " . (count($importResults['error_details']) - 10) . " detay daha.";
                            }
                        }
                    } else {
                        $error = 'İçe aktarım hatası: ' . $importResults['message'];
                        if (!empty($importResults['error_details'])) {
                            $error .= "\n\nDetaylar:\n" . implode("\n", array_slice($importResults['error_details'], 0, 5));
                        }
                    }
                    
                    $action = 'list';
                }
            }
        } else {
            // Existing individual add/edit logic
            $product_id = intval($_POST['product_id']);
            $name = sanitizeInput($_POST['name']);
            $color = sanitizeInput($_POST['color']);
            $color_code = sanitizeInput($_POST['color_code'] ?: $_POST['color_code_text']);
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
                        $sql = "INSERT INTO product_variants (product_id, name, color, color_code, size, weight, sku, price, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $params = [$product_id, $name, $color, $color_code, $size, $weight, $sku, $price, $image_path, $sort_order, $is_active];
                        
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
                        $sql = "UPDATE product_variants SET product_id = ?, name = ?, color = ?, color_code = ?, size = ?, weight = ?, sku = ?, price = ?, sort_order = ?, is_active = ?";
                        $params = [$product_id, $name, $color, $color_code, $size, $weight, $sku, $price, $sort_order, $is_active];
                        
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

// Excel import için ürünleri hazırla
if ($action === 'excel_import') {
    $products = fetchAll("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY c.name, p.name");
}

include 'includes/header.php';
?>

<style>
.bulk-update-panel {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.bulk-update-panel.active {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border-color: #fc8181;
    box-shadow: 0 4px 15px rgba(252, 129, 129, 0.1);
}

.selection-counter {
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(229, 62, 62, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 2px 8px rgba(229, 62, 62, 0.3); }
    50% { box-shadow: 0 4px 15px rgba(229, 62, 62, 0.5); }
    100% { box-shadow: 0 2px 8px rgba(229, 62, 62, 0.3); }
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.checkbox-wrapper {
    position: relative;
}

.checkbox-wrapper input[type="checkbox"] {
    transform: scale(1.2);
    cursor: pointer;
}

.selected-row {
    background-color: #fff5f5 !important;
    border-left: 4px solid #e53e3e;
}

.editable-field {
    transition: all 0.2s ease;
    border-radius: 4px;
}

.editable-field:hover {
    background-color: #f7fafc !important;
    transform: scale(1.02);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.editable-field.editing {
    background-color: #fff5f5;
    border: 2px solid #e53e3e;
}

.notification {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<script>
let selectedVariants = new Set();

function toggleVariantSelection(variantId, checkbox) {
    const variantIdStr = variantId.toString();
    if (checkbox.checked) {
        selectedVariants.add(variantIdStr);
        checkbox.closest('tr').classList.add('selected-row');
    } else {
        selectedVariants.delete(variantIdStr);
        checkbox.closest('tr').classList.remove('selected-row');
    }
    updateBulkPanel();
    updateSelectAllCheckbox();
}

function toggleAllVariants(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.variant-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        const variantId = checkbox.getAttribute('data-variant-id');
        
        if (selectAllCheckbox.checked) {
            selectedVariants.add(variantId);
            checkbox.closest('tr').classList.add('selected-row');
        } else {
            selectedVariants.delete(variantId);
            checkbox.closest('tr').classList.remove('selected-row');
        }
    });
    
    updateBulkPanel();
}

function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.variant-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.variant-checkbox:checked');
    
    if (checkboxes.length === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (checkedCheckboxes.length === checkboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else if (checkedCheckboxes.length > 0) {
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.checked = false;
    } else {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    }
}

function updateBulkPanel() {
    const bulkPanel = document.getElementById('bulkUpdatePanel');
    const selectionCounter = document.getElementById('selectionCounter');
    
    if (selectedVariants.size > 0) {
        bulkPanel.classList.add('active');
        bulkPanel.style.display = 'block';
        selectionCounter.textContent = selectedVariants.size + ' varyant seçildi';
    } else {
        bulkPanel.classList.remove('active');
        bulkPanel.style.display = 'none';
    }
}

function clearSelection() {
    selectedVariants.clear();
    document.querySelectorAll('.variant-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        checkbox.closest('tr').classList.remove('selected-row');
    });
    document.getElementById('selectAll').checked = false;
    document.getElementById('selectAll').indeterminate = false;
    updateBulkPanel();
}

function resetBulkForm() {
    document.getElementById('bulkUpdateForm').reset();
}

function submitBulkUpdate() {
    if (selectedVariants.size === 0) {
        alert('Lütfen güncellenecek varyantları seçin.');
        return false;
    }
    
    const form = document.getElementById('bulkUpdateForm');
    const bulkPrice = form.bulk_price.value;
    const bulkSku = form.bulk_sku_prefix.value;
    const bulkWeight = form.bulk_weight.value;
    const bulkStatus = form.bulk_is_active.value;
    
    if (!bulkPrice && !bulkSku && !bulkWeight && !bulkStatus) {
        alert('Lütfen en az bir alanı doldurun.');
        return false;
    }
    
    const confirmMessage = `${selectedVariants.size} varyant güncellenecek. Devam etmek istiyor musunuz?`;
    if (confirm(confirmMessage)) {
        // Add selected variants to form
        const hiddenInputs = document.getElementById('hiddenInputs');
        hiddenInputs.innerHTML = '';
        
        selectedVariants.forEach(variantId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_variants[]';
            input.value = variantId;
            hiddenInputs.appendChild(input);
        });
        
        form.submit();
    }
    
    return false;
}

// Toplu silme fonksiyonu
function submitBulkDelete() {
    if (selectedVariants.size === 0) {
        alert('Lütfen silinecek varyantları seçin.');
        return false;
    }
    
    const confirmMessage = `⚠️ DİKKAT!\n\n${selectedVariants.size} varyant kalıcı olarak silinecek.\n\nBu işlem geri alınamaz!\n\nDevam etmek istediğinizden emin misiniz?`;
    
    if (confirm(confirmMessage)) {
        // İkinci onay
        const finalConfirm = `Son onay: ${selectedVariants.size} varyantı silmek istediğinizden EMİN MİSİNİZ?\n\nBu işlem GERİ ALINAMAZ!`;
        
        if (confirm(finalConfirm)) {
            // Create a temporary form for bulk delete
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href;
            
            // CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?php echo generateCSRFToken(); ?>';
            form.appendChild(csrfInput);
            
            // Action
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'bulk_delete';
            form.appendChild(actionInput);
            
            // Selected variants
            selectedVariants.forEach(variantId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_variants[]';
                input.value = variantId;
                form.appendChild(input);
            });
            
            // Submit form
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    return false;
}

// Inline editing functions
function makeEditable(element, field, variantId, currentValue) {
    // Prevent multiple edits
    if (element.querySelector('input')) return;
    
    const originalContent = element.innerHTML;
    const input = document.createElement('input');
    
    // Set input properties based on field type
    if (field === 'price') {
        input.type = 'number';
        input.step = '0.01';
        input.min = '0';
        input.value = currentValue.replace('₺', '').replace(',', '');
        input.className = 'w-full px-2 py-1 border border-red-300 rounded focus:ring-2 focus:ring-red-500 text-sm';
    } else {
        input.type = 'text';
        input.value = currentValue;
        input.className = 'w-full px-2 py-1 border border-red-300 rounded focus:ring-2 focus:ring-red-500 text-sm';
    }
    
    element.innerHTML = '';
    element.appendChild(input);
    input.focus();
    input.select();
    
    // Save on Enter or blur
    function saveValue() {
        const newValue = input.value.trim();
        
        if (newValue !== currentValue) {
            // Show loading
            element.innerHTML = '<i class="fas fa-spinner fa-spin text-gray-400"></i>';
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('action', 'inline_update');
            formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
            formData.append('variant_id', variantId);
            formData.append('field', field);
            formData.append('value', newValue);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (field === 'price') {
                        element.innerHTML = `<span class="editable-field text-sm font-semibold text-black cursor-pointer hover:bg-gray-100 px-2 py-1 rounded" onclick="makeEditable(this, '${field}', ${variantId}, '${newValue}')">${data.formatted_value}</span>`;
                    } else {
                        element.innerHTML = `<span class="editable-field text-sm font-mono text-black cursor-pointer hover:bg-gray-100 px-2 py-1 rounded" onclick="makeEditable(this, '${field}', ${variantId}, '${newValue}')">${newValue || '-'}</span>`;
                    }
                    
                    // Show success message
                    showNotification('Güncellendi!', 'success');
                } else {
                    element.innerHTML = originalContent;
                    showNotification(data.message || 'Güncelleme hatası', 'error');
                }
            })
            .catch(error => {
                element.innerHTML = originalContent;
                showNotification('Bağlantı hatası', 'error');
            });
        } else {
            element.innerHTML = originalContent;
        }
    }
    
    input.addEventListener('blur', saveValue);
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            saveValue();
        } else if (e.key === 'Escape') {
            element.innerHTML = originalContent;
        }
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white text-sm font-medium z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 2000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateBulkPanel();
});
</script>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <pre class="whitespace-pre-wrap"><?php echo $success; ?></pre>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <pre class="whitespace-pre-wrap"><?php echo $error; ?></pre>
        </div>
    </div>
<?php endif; ?>

<?php if ($action === 'excel_import'): ?>
    <!-- Excel Import Page -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-black">Excel ile Toplu Varyant İçe Aktarımı</h2>
            <p class="text-sm text-gray-600 mt-2">Excel dosyanızı yükleyerek birden fazla varyantı aynı anda ekleyebilir veya güncelleyebilirsiniz.</p>
        </div>

        <!-- Örnek Excel Şablonu İndirme -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">📋 Excel Şablonu</h3>
            <p class="text-sm text-blue-700 mb-3">Aşağıdaki formatta Excel dosyanızı hazırlayın:</p>
            <button onclick="downloadTemplate()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                <i class="fas fa-download mr-2"></i>Örnek Şablon İndir
            </button>
        </div>

        <!-- Kolon Açıklamaları -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-gray-800 mb-3">📝 Kolon Açıklamaları</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <strong>product_id:</strong> <span class="text-red-600">*Zorunlu</span> - Ürün ID numarası<br>
                    <strong>name:</strong> <span class="text-red-600">*Zorunlu</span> - Varyant adı<br>
                    <strong>color:</strong> Renk adı (örn: Meşe)<br>
                    <strong>color_code:</strong> Hex renk kodu (örn: #8B4513)<br>
                    <strong>size:</strong> Boyut bilgisi
                </div>
                <div>
                    <strong>weight:</strong> Ağırlık/hacim (örn: 200gr)<br>
                    <strong>sku:</strong> Stok kodu (benzersiz olmalı)<br>
                    <strong>price:</strong> Fiyat (sadece sayı)<br>
                    <strong>sort_order:</strong> Sıra numarası (0, 1, 2, ...)<br>
                    <strong>is_active:</strong> aktif/pasif
                </div>
            </div>
            
            <!-- Yeni: Güncelleme Kuralları -->
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">🔄 Güncelleme Kuralları</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li><strong>SKU var:</strong> Aynı SKU'ya sahip varyant güncellenir</li>
                    <li><strong>SKU yok:</strong> Aynı product_id + name + color'a sahip varyant güncellenir</li>
                    <li><strong>Hiçbiri yoksa:</strong> Yeni varyant eklenir</li>
                    <li><strong>Boş satırlar:</strong> Otomatik olarak atlanır</li>
                </ul>
            </div>
        </div>

        <!-- Yeni: Önemli Notlar -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-yellow-800 mb-3">⚠️ Önemli Notlar</h3>
            <ul class="text-sm text-yellow-700 space-y-2">
                <li><strong>Mevcut Varyantlar:</strong> Excel'de aynı varyant adı/rengi varsa güncelleme yapılır</li>
                <li><strong>SKU Kontrolü:</strong> SKU benzersiz olmalıdır, duplicate SKU güncelleme yapar</li>
                <li><strong>Zorunlu Alanlar:</strong> product_id ve name boş bırakılamaz</li>
                <li><strong>Veri Formatı:</strong> Excel sütun başlıkları tam olarak eşleşmelidir</li>
                <li><strong>Test Önerisi:</strong> Büyük yüklemeler öncesi 2-3 satırla test yapın</li>
            </ul>
        </div>

        <!-- Mevcut Ürünler Listesi -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-yellow-800 mb-3">🏷️ Mevcut Ürün ID Listesi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-sm max-h-40 overflow-y-auto">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white p-2 rounded border">
                        <strong>ID: <?php echo $product['id']; ?></strong><br>
                        <span class="text-gray-600"><?php echo htmlspecialchars($product['category_name']); ?></span><br>
                        <span class="text-black"><?php echo htmlspecialchars($product['name']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- File Upload Area -->
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center mb-6" id="dropZone">
            <div id="uploadArea">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                <p class="text-lg font-medium text-gray-700 mb-2">Excel dosyanızı buraya sürükleyin</p>
                <p class="text-sm text-gray-500 mb-4">veya</p>
                <label for="excelFile" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition duration-300 cursor-pointer">
                    <i class="fas fa-folder-open mr-2"></i>Dosya Seç
                </label>
                <input type="file" id="excelFile" class="hidden" accept=".xlsx,.xls" onchange="handleFile(this.files[0])">
                <p class="text-xs text-gray-500 mt-3">Desteklenen formatlar: .xlsx, .xls (Maksimum 5MB)</p>
            </div>
            
            <div id="loadingArea" class="hidden">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-4"></i>
                <p class="text-lg font-medium text-blue-700">Excel dosyası işleniyor...</p>
            </div>
        </div>

        <!-- Preview Area -->
        <div id="previewArea" class="hidden mb-6">
            <h3 class="text-lg font-semibold text-black mb-4">📊 Veri Önizleme</h3>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                <div id="previewStats" class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center"></div>
            </div>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table id="previewTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satır</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ürün ID</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Varyant Adı</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Renk</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fiyat</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="previewTableBody">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Form -->
        <form id="importForm" method="POST" class="hidden">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="excel_import">
            <input type="hidden" name="import_data" id="importData">
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition duration-300">
                    <i class="fas fa-upload mr-2"></i>Varyantları İçe Aktar
                </button>
                <button type="button" onclick="resetImport()" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition duration-300">
                    <i class="fas fa-times mr-2"></i>İptal
                </button>
                <a href="?action=list" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-list mr-2"></i>Varyant Listesi
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        let excelData = [];
        
        // Drag & Drop functionality
        const dropZone = document.getElementById('dropZone');
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-red-500', 'bg-red-50');
        });
        
        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-red-500', 'bg-red-50');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-red-500', 'bg-red-50');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });
        
        // Excel dosyasını işle
        function handleFile(file) {
            if (!file) return;
            
            // Dosya boyutu kontrolü (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Dosya boyutu 5MB\'dan büyük olamaz!');
                return;
            }
            
            // Dosya tipi kontrolü
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
            if (!allowedTypes.includes(file.type)) {
                alert('Sadece .xlsx ve .xls dosyaları desteklenir!');
                return;
            }
            
            showLoading();
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {type: 'array'});
                    const sheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[sheetName];
                    
                    // Excel verisini JSON'a çevir
                    const jsonData = XLSX.utils.sheet_to_json(worksheet, {header: 1});
                    
                    if (jsonData.length < 2) {
                        throw new Error('Excel dosyası en az 2 satır olmalıdır (başlık + veri)');
                    }
                    
                    // Başlık satırını al
                    const headers = jsonData[0];
                    
                    // Veri satırlarını işle
                    excelData = [];
                    for (let i = 1; i < jsonData.length; i++) {
                        const row = jsonData[i];
                        if (row.length === 0) continue; // Boş satırları atla
                        
                        const rowData = {};
                        headers.forEach((header, index) => {
                            if (header) {
                                rowData[header.toLowerCase().replace(/\s+/g, '_')] = row[index] || '';
                            }
                        });
                        
                        // En az product_id ve name olmalı
                        if (rowData.product_id && rowData.name) {
                            excelData.push(rowData);
                        }
                    }
                    
                    if (excelData.length === 0) {
                        throw new Error('Geçerli veri bulunamadı. product_id ve name kolonları zorunludur.');
                    }
                    
                    showPreview();
                    hideLoading();
                    
                } catch (error) {
                    hideLoading();
                    alert('Excel dosyası okunurken hata oluştu: ' + error.message);
                }
            };
            
            reader.readAsArrayBuffer(file);
        }
        
        // Loading göster
        function showLoading() {
            document.getElementById('uploadArea').classList.add('hidden');
            document.getElementById('loadingArea').classList.remove('hidden');
        }
        
        // Loading gizle
        function hideLoading() {
            document.getElementById('uploadArea').classList.remove('hidden');
            document.getElementById('loadingArea').classList.add('hidden');
        }
        
        // Önizleme göster
        function showPreview() {
            const previewArea = document.getElementById('previewArea');
            const previewStats = document.getElementById('previewStats');
            const previewTableBody = document.getElementById('previewTableBody');
            const importForm = document.getElementById('importForm');
            
            // İstatistikleri güncelle
            previewStats.innerHTML = `
                <div class="bg-white p-3 rounded-lg border">
                    <div class="text-2xl font-bold text-blue-600">${excelData.length}</div>
                    <div class="text-sm text-gray-600">Toplam Satır</div>
                </div>
                <div class="bg-white p-3 rounded-lg border">
                    <div class="text-2xl font-bold text-green-600">${excelData.filter(row => row.product_id && row.name).length}</div>
                    <div class="text-sm text-gray-600">Geçerli Satır</div>
                </div>
                <div class="bg-white p-3 rounded-lg border">
                    <div class="text-2xl font-bold text-orange-600">${excelData.filter(row => row.sku).length}</div>
                    <div class="text-sm text-gray-600">SKU'lu Satır</div>
                </div>
                <div class="bg-white p-3 rounded-lg border">
                    <div class="text-2xl font-bold text-purple-600">${excelData.filter(row => row.price && parseFloat(row.price) > 0).length}</div>
                    <div class="text-sm text-gray-600">Fiyatlı Satır</div>
                </div>
            `;
            
            // Tabloyu güncelle
            previewTableBody.innerHTML = '';
            excelData.slice(0, 10).forEach((row, index) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                
                const hasError = !row.product_id || !row.name;
                
                tr.innerHTML = `
                    <td class="px-3 py-2 text-sm">${index + 1}</td>
                    <td class="px-3 py-2 text-sm ${!row.product_id ? 'text-red-600 font-bold' : ''}">${row.product_id || '-'}</td>
                    <td class="px-3 py-2 text-sm ${!row.name ? 'text-red-600 font-bold' : ''}">${row.name || '-'}</td>
                    <td class="px-3 py-2 text-sm">${row.color || '-'}</td>
                    <td class="px-3 py-2 text-sm font-mono">${row.sku || '-'}</td>
                    <td class="px-3 py-2 text-sm">${row.price ? '₺' + parseFloat(row.price).toFixed(2) : '-'}</td>
                    <td class="px-3 py-2 text-sm">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs ${hasError ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                            ${hasError ? '❌ Hata' : '✅ Geçerli'}
                        </span>
                    </td>
                `;
                
                previewTableBody.appendChild(tr);
            });
            
            if (excelData.length > 10) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="7" class="px-3 py-2 text-center text-gray-500 italic">ve ${excelData.length - 10} satır daha...</td>`;
                previewTableBody.appendChild(tr);
            }
            
            // Form verilerini hazırla
            document.getElementById('importData').value = JSON.stringify(excelData);
            
            // Alanları göster
            previewArea.classList.remove('hidden');
            importForm.classList.remove('hidden');
        }
        
        // Import'u sıfırla
        function resetImport() {
            excelData = [];
            document.getElementById('previewArea').classList.add('hidden');
            document.getElementById('importForm').classList.add('hidden');
            document.getElementById('excelFile').value = '';
        }
        
        // Örnek şablon indir
        function downloadTemplate() {
            const templateData = [
                ['product_id', 'name', 'color', 'color_code', 'size', 'weight', 'sku', 'price', 'sort_order', 'is_active'],
                [1, 'Doğal Renk 200gr', 'Doğal', '#D2B48C', '', '200gr', 'ECE-200-DOGAL', 25.50, 1, 'aktif'],
                [1, 'Meşe Rengi 200gr', 'Meşe', '#8B4513', '', '200gr', 'ECE-200-MESE', 25.50, 2, 'aktif'],
                [1, 'Ceviz Rengi 200gr', 'Ceviz', '#654321', '', '200gr', 'ECE-200-CEVIZ', 25.50, 3, 'aktif'],
                [2, 'Doğal Renk 125ml', 'Doğal', '#D2B48C', '', '125ml', 'ECE-125-DOGAL', 18.75, 1, 'aktif'],
                [2, 'Güncellenecek Varyant', 'Yeşil', '#008000', '', '125ml', 'ECE-125-YESIL', 19.99, 2, 'aktif'],
                ['', '// BOŞ SATIRLAR ATLANIR', '', '', '', '', '', '', '', ''],
                ['', '// MEVCUT SKU VARSA GÜNCELLEME YAPAR', '', '', '', '', '', '', '', ''],
                ['', '// SKU YOKSA NAME+COLOR KONTROLÜ YAPAR', '', '', '', '', '', '', '', '']
            ];
            
            const ws = XLSX.utils.aoa_to_sheet(templateData);
            
            // Sütun genişlikleri ayarla
            ws['!cols'] = [
                {wch: 12}, // product_id
                {wch: 25}, // name
                {wch: 15}, // color
                {wch: 10}, // color_code
                {wch: 10}, // size
                {wch: 12}, // weight
                {wch: 18}, // sku
                {wch: 8},  // price
                {wch: 12}, // sort_order
                {wch: 10}  // is_active
            ];
            
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Varyantlar');
            
            XLSX.writeFile(wb, 'varyant_sablonu_guncel.xlsx');
        }
    </script>

<?php elseif ($action === 'list'): ?>
    <!-- Bulk Update Panel -->
    <div id="bulkUpdatePanel" class="bulk-update-panel p-6 mb-6" style="display: none;">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <div class="selection-counter" id="selectionCounter">0 varyant seçildi</div>
                <button onclick="clearSelection()" class="text-gray-600 hover:text-gray-800 text-sm">
                    <i class="fas fa-times mr-1"></i>Seçimi Temizle
                </button>
            </div>
            <div class="text-lg font-semibold text-gray-700">
                <i class="fas fa-edit mr-2"></i>Topluca Düzenle / Sil
            </div>
        </div>
        
        <form id="bulkUpdateForm" method="POST" onsubmit="return submitBulkUpdate();">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="bulk_update">
            <div id="hiddenInputs"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="form-group">
                    <label class="text-sm font-medium text-gray-700">Fiyat (₺)</label>
                    <input type="number" name="bulk_price" step="0.01" min="0"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                           placeholder="Yeni fiyat">
                </div>
                
                <div class="form-group">
                    <label class="text-sm font-medium text-gray-700">SKU Öneki</label>
                    <input type="text" name="bulk_sku_prefix"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                           placeholder="SKU öneki (otomatik ID eklenecek)">
                </div>
                
                <div class="form-group">
                    <label class="text-sm font-medium text-gray-700">Ağırlık/Hacim</label>
                    <input type="text" name="bulk_weight"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                           placeholder="Ağırlık/hacim">
                </div>
                
                <div class="form-group">
                    <label class="text-sm font-medium text-gray-700">Durum</label>
                    <select name="bulk_is_active"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        <option value="">Değiştirme</option>
                        <option value="1">Aktif</option>
                        <option value="0">Pasif</option>
                    </select>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>Seçilenleri Güncelle
                </button>
                <button type="button" onclick="submitBulkDelete()" class="bg-red-800 text-white px-6 py-2 rounded-lg hover:bg-red-900 transition duration-300">
                    <i class="fas fa-trash mr-2"></i>Seçilenleri Sil
                </button>
                <button type="button" onclick="resetBulkForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                    <i class="fas fa-undo mr-2"></i>Formu Temizle
                </button>
            </div>
        </form>
    </div>

    <!-- Variants List -->
    <div class="bg-white rounded-lg shadow-md card">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-black">Ürün Varyantları</h2>
                    <?php if ($selectedProduct): ?>
                        <p class="text-sm text-gray-600 mt-1">
                            <span class="font-medium"><?php echo htmlspecialchars($selectedProduct['category_name']); ?></span> > 
                            <span class="font-medium"><?php echo htmlspecialchars($selectedProduct['name']); ?></span>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="flex space-x-2">
                    <a href="?action=excel_import" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300">
                        <i class="fas fa-file-excel mr-2"></i>Excel İçe Aktar
                    </a>
                    <?php if ($product_id): ?>
                        <a href="?action=add&product_id=<?php echo $product_id; ?>" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                            <i class="fas fa-plus mr-2"></i>Yeni Varyant
                        </a>
                        <a href="?action=list" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-300">
                            <i class="fas fa-list mr-2"></i>Tüm Varyantlar
                        </a>
                    <?php else: ?>
                        <a href="?action=add" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300">
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
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="selectAll" onchange="toggleAllVariants(this)"
                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <label for="selectAll" class="ml-2 text-xs">Tümü</label>
                            </div>
                        </th>
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
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                <?php echo $product_id ? 'Bu ürün için henüz varyant eklenmemiş.' : 'Henüz varyant eklenmemiş.'; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($variants as $var): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" class="variant-checkbox h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                                               value="<?php echo $var['id']; ?>"
                                               data-variant-id="<?php echo $var['id']; ?>"
                                               onchange="toggleVariantSelection('<?php echo $var['id']; ?>', this)">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($var['image']): ?>
                                            <img src="<?php echo $var['image']; ?>" alt="<?php echo htmlspecialchars($var['name']); ?>" class="w-10 h-10 object-cover rounded-lg mr-3">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-palette text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-1">
                                            <span class="editable-field text-sm font-semibold text-black cursor-pointer hover:bg-gray-100 px-2 py-1 rounded" 
                                                  onclick="makeEditable(this, 'name', <?php echo $var['id']; ?>, '<?php echo htmlspecialchars($var['name']); ?>')"
                                                  title="Tıklayarak düzenle">
                                                <?php echo htmlspecialchars($var['name']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-black"><?php echo htmlspecialchars($var['product_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($var['category_name']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700">
                                        <?php if ($var['color']): ?>
                                            <span class="inline-flex items-center bg-red-100 text-red-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                                <?php if ($var['color_code']): ?>
                                                    <span class="w-3 h-3 rounded-full mr-1 border border-gray-300" style="background-color: <?php echo $var['color_code']; ?>;"></span>
                                                <?php endif; ?>
                                                Renk: <?php echo htmlspecialchars($var['color']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($var['size']): ?>
                                            <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                                Boyut: <?php echo htmlspecialchars($var['size']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($var['weight']): ?>
                                            <span class="editable-field inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded mr-1 mb-1 cursor-pointer hover:bg-purple-200" 
                                                  onclick="makeEditable(this, 'weight', <?php echo $var['id']; ?>, '<?php echo htmlspecialchars($var['weight']); ?>')"
                                                  title="Tıklayarak düzenle">
                                                <?php echo htmlspecialchars($var['weight']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="editable-field inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded mr-1 mb-1 cursor-pointer hover:bg-gray-200" 
                                                  onclick="makeEditable(this, 'weight', <?php echo $var['id']; ?>, '')"
                                                  title="Tıklayarak ağırlık/hacim ekle">
                                                + Ağırlık/Hacim
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($var['sku']): ?>
                                        <span class="editable-field text-sm font-mono text-black cursor-pointer hover:bg-gray-100 px-2 py-1 rounded" 
                                              onclick="makeEditable(this, 'sku', <?php echo $var['id']; ?>, '<?php echo htmlspecialchars($var['sku']); ?>')"
                                              title="Tıklayarak düzenle">
                                            <?php echo htmlspecialchars($var['sku']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="editable-field text-sm text-gray-400 cursor-pointer hover:bg-gray-100 px-2 py-1 rounded" 
                                              onclick="makeEditable(this, 'sku', <?php echo $var['id']; ?>, '')"
                                              title="Tıklayarak SKU ekle">
                                            + SKU Ekle
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($var['price']): ?>
                                        <span class="editable-field text-sm font-semibold text-black cursor-pointer hover:bg-gray-100 px-2 py-1 rounded" 
                                              onclick="makeEditable(this, 'price', <?php echo $var['id']; ?>, '<?php echo $var['price']; ?>')"
                                              title="Tıklayarak düzenle">
                                            ₺<?php echo number_format($var['price'], 2); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="editable-field text-sm text-gray-400 cursor-pointer hover:bg-gray-100 px-2 py-1 rounded" 
                                              onclick="makeEditable(this, 'price', <?php echo $var['id']; ?>, '0')"
                                              title="Tıklayarak fiyat ekle">
                                            + Fiyat Ekle
                                        </span>
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
                                        <a href="?action=edit&id=<?php echo $var['id']; ?>" class="text-red-600 hover:text-red-700" title="Düzenle">
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
            <h2 class="text-xl font-semibold text-black">
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
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
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           placeholder="0">
                </div>
            </div>
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Varyant Adı *</label>
                <input type="text" name="name" id="name" required
                       value="<?php echo $variant ? htmlspecialchars($variant['name']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                       placeholder="Varyant adını girin (örn: Doğal Renk, Meşe Rengi)">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-2">Renk</label>
                    <input type="text" name="color" id="color"
                           value="<?php echo $variant ? htmlspecialchars($variant['color']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           placeholder="Renk adını girin">
                </div>
                
                <div>
                    <label for="color_code" class="block text-sm font-medium text-gray-700 mb-2">Renk Kodu</label>
                    <div class="flex items-center space-x-2">
                        <input type="color" name="color_code" id="color_code"
                               value="<?php echo $variant ? htmlspecialchars($variant['color_code']) : '#ffffff'; ?>"
                               class="w-12 h-10 border border-gray-300 rounded cursor-pointer"
                               onchange="document.getElementById('color_code_text').value = this.value">
                        <input type="text" name="color_code_text" id="color_code_text"
                               value="<?php echo $variant ? htmlspecialchars($variant['color_code']) : ''; ?>"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="#ffffff"
                               onchange="document.getElementById('color_code').value = this.value">
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Renk seçici kullanın veya hex kod girin (örn: #ff0000)</p>
                </div>
                
                <div>
                    <label for="size" class="block text-sm font-medium text-gray-700 mb-2">Boyut</label>
                    <input type="text" name="size" id="size"
                           value="<?php echo $variant ? htmlspecialchars($variant['size']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           placeholder="Boyut bilgisini girin">
                </div>
                
                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Ağırlık/Hacim</label>
                    <input type="text" name="weight" id="weight"
                           value="<?php echo $variant ? htmlspecialchars($variant['weight']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           placeholder="Ağırlık veya hacim (örn: 200gr, 125ml)">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700 mb-2">SKU (Stok Kodu)</label>
                    <input type="text" name="sku" id="sku"
                           value="<?php echo $variant ? htmlspecialchars($variant['sku']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           placeholder="Benzersiz stok kodu">
                </div>
                
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Fiyat (₺)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0"
                           value="<?php echo $variant ? $variant['price'] : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                           placeholder="0.00">
                </div>
            </div>
            
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Varyant Görseli</label>
                <input type="file" name="image" id="image" accept="image/*"
                       onchange="previewImage(this, 'imagePreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
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
                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Aktif</label>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition duration-300">
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