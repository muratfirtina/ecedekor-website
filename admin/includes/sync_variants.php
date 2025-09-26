<?php
// includes/sync_variants.php - Varyant senkronizasyon sınıfı

class VariantSyncManager 
{
    private $adminDb;
    private $teklifDb;
    
    public function __construct() 
    {
        // Admin DB bağlantısı (mevcut config'ten al)
        global $pdo;
        $this->adminDb = $pdo;
        
        // Teklif DB bağlantısı
        try {
            $this->teklifDb = new PDO(
                "mysql:host=" . (defined('TEKLIF_DB_HOST') ? TEKLIF_DB_HOST : 'localhost') . 
                ";dbname=" . (defined('TEKLIF_DB_NAME') ? TEKLIF_DB_NAME : 'ecedekor_teklif') . 
                ";charset=utf8",
                defined('TEKLIF_DB_USERNAME') ? TEKLIF_DB_USERNAME : DB_USERNAME,
                defined('TEKLIF_DB_PASSWORD') ? TEKLIF_DB_PASSWORD : DB_PASSWORD
            );
            $this->teklifDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            throw new Exception("Teklif DB bağlantı hatası: " . $e->getMessage());
        }
    }
    
    /**
     * Tüm aktif varyantları teklif sistemine senkronize et
     */
    public function syncAllVariants() 
    {
        try {
            // Admin sisteminden aktif varyantları al
            $sql = "SELECT 
                        pv.*, 
                        p.name as product_name, 
                        p.description as product_description,
                        p.features as product_features,
                        c.name as category_name
                    FROM product_variants pv
                    JOIN products p ON pv.product_id = p.id
                    JOIN categories c ON p.category_id = c.id
                    WHERE pv.is_active = 1 AND p.is_active = 1
                    ORDER BY c.name, p.name, pv.sort_order, pv.name";
            
            $stmt = $this->adminDb->prepare($sql);
            $stmt->execute();
            $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $syncedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            
            foreach ($variants as $variant) {
                try {
                    if ($this->syncSingleVariant($variant)) {
                        $syncedCount++;
                    } else {
                        $skippedCount++;
                    }
                } catch (Exception $e) {
                    $errorCount++;
                    error_log("Varyant senkronizasyon hatası (ID: {$variant['id']}): " . $e->getMessage());
                }
            }
            
            return [
                'success' => true,
                'synced' => $syncedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
                'total' => count($variants)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Tek bir varyantı senkronize et
     */
    public function syncSingleVariant($variant) 
    {
        // Varyant için benzersiz kod oluştur
        $code = $this->generateProductCode($variant);
        
        // Ürün adı oluştur (Ana ürün adı + Varyant adı)
        $productName = $variant['product_name'];
        if (!empty($variant['name']) && $variant['name'] !== $variant['product_name']) {
            $productName .= ' - ' . $variant['name'];
        }
        
        // Açıklama oluştur
        $description = $variant['product_description'] ?? '';
        $descriptionParts = [];
        
        if (!empty($variant['color'])) {
            $descriptionParts[] = "Renk: " . $variant['color'];
        }
        if (!empty($variant['size'])) {
            $descriptionParts[] = "Boyut: " . $variant['size'];
        }
        if (!empty($variant['weight'])) {
            $descriptionParts[] = "Ağırlık/Hacim: " . $variant['weight'];
        }
        
        if (!empty($descriptionParts)) {
            $description .= "\n\nÖzellikler:\n" . implode("\n", $descriptionParts);
        }
        
        // Teklif sisteminde bu ürün var mı kontrol et
        $existingProduct = $this->findExistingProduct($code, $variant['id']);
        
        if ($existingProduct) {
            // Güncelle
            return $this->updateProductInTeklif($existingProduct['id'], $code, $productName, $description, $variant);
        } else {
            // Yeni ekle
            return $this->insertProductToTeklif($code, $productName, $description, $variant);
        }
    }
    
    /**
     * Varyant için benzersiz ürün kodu oluştur
     */
    private function generateProductCode($variant) 
    {
        // Önce SKU varsa onu kullan
        if (!empty($variant['sku'])) {
            return $variant['sku'];
        }
        
        // SKU yoksa kategori + ürün adı + varyant özelliklerinden kod oluştur
        $codeParts = [];
        
        // Kategori kısaltması
        $categoryCode = $this->generateCategoryCode($variant['category_name']);
        if ($categoryCode) {
            $codeParts[] = $categoryCode;
        }
        
        // Ürün kısaltması
        $productCode = $this->generateNameCode($variant['product_name']);
        if ($productCode) {
            $codeParts[] = $productCode;
        }
        
        // Varyant özellikleri
        if (!empty($variant['color'])) {
            $codeParts[] = $this->generateNameCode($variant['color']);
        }
        if (!empty($variant['size'])) {
            $codeParts[] = $this->generateNameCode($variant['size']);
        }
        
        $baseCode = implode('-', $codeParts);
        
        // Benzersizlik için son ek ekle
        $finalCode = $baseCode . '-V' . $variant['id'];
        
        return strtoupper($finalCode);
    }
    
    /**
     * Kategori adından kod oluştur
     */
    private function generateCategoryCode($categoryName) 
    {
        $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
        $english = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'I', 'O', 'S', 'U'];
        
        $code = str_replace($turkish, $english, $categoryName);
        $code = preg_replace('/[^a-zA-Z0-9]/', '', $code);
        
        return substr($code, 0, 3);
    }
    
    /**
     * İsimden kod oluştur
     */
    private function generateNameCode($name) 
    {
        $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
        $english = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'I', 'O', 'S', 'U'];
        
        $code = str_replace($turkish, $english, $name);
        $code = preg_replace('/[^a-zA-Z0-9]/', '', $code);
        
        return substr($code, 0, 4);
    }
    
    /**
     * Teklif sisteminde mevcut ürünü bul
     */
    private function findExistingProduct($code, $variantId) 
    {
        // Önce kod ile ara
        $stmt = $this->teklifDb->prepare("SELECT * FROM products WHERE code = :code LIMIT 1");
        $stmt->bindValue(':code', $code);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            return $product;
        }
        
        // Kod bulunamazsa varyant ID'si ile ara (admin_variant_id alanı varsa)
        try {
            $stmt = $this->teklifDb->prepare("SELECT * FROM products WHERE admin_variant_id = :variant_id LIMIT 1");
            $stmt->bindValue(':variant_id', $variantId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // admin_variant_id kolonu yoksa null döner
            return null;
        }
    }
    
    /**
     * Teklif sistemindeki ürünü güncelle
     */
    private function updateProductInTeklif($productId, $code, $name, $description, $variant) 
    {
        try {
            $sql = "UPDATE products SET 
                        code = :code,
                        name = :name,
                        description = :description,
                        price = :price,
                        color_hex = :color_hex,
                        admin_variant_id = :admin_variant_id,
                        admin_sync_date = NOW()
                    WHERE id = :id";
            
            $stmt = $this->teklifDb->prepare($sql);
            
            // bindValue kullan (reference gerektirmez)
            $stmt->bindValue(':code', $code);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':description', $description);
            $stmt->bindValue(':price', $variant['price'] ?: 0);
            $stmt->bindValue(':color_hex', $variant['color_code']);
            $stmt->bindValue(':admin_variant_id', $variant['id']);
            $stmt->bindValue(':id', $productId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Ürün güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Teklif sistemine yeni ürün ekle
     */
    private function insertProductToTeklif($code, $name, $description, $variant) 
    {
        try {
            $sql = "INSERT INTO products 
                        (code, name, description, price, tax_rate, stock_quantity, color_hex, admin_variant_id, admin_sync_date) 
                    VALUES 
                        (:code, :name, :description, :price, :tax_rate, :stock_quantity, :color_hex, :admin_variant_id, NOW())";
            
            $stmt = $this->teklifDb->prepare($sql);
            
            // bindValue kullan (reference gerektirmez)
            $stmt->bindValue(':code', $code);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':description', $description);
            $stmt->bindValue(':price', $variant['price'] ?: 16);
            $stmt->bindValue(':tax_rate', 20); // Varsayılan KDV oranı
            $stmt->bindValue(':stock_quantity', 0); // Başlangıç stok
            $stmt->bindValue(':color_hex', $variant['color_code']);
            $stmt->bindValue(':admin_variant_id', $variant['id']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Ürün ekleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Belirli bir varyantı ID ile senkronize et
     */
    public function syncVariantById($variantId) 
    {
        try {
            // Varyant bilgisini al
            $sql = "SELECT 
                        pv.*, 
                        p.name as product_name, 
                        p.description as product_description,
                        p.features as product_features,
                        c.name as category_name
                    FROM product_variants pv
                    JOIN products p ON pv.product_id = p.id
                    JOIN categories c ON p.category_id = c.id
                    WHERE pv.id = :variant_id";
            
            $stmt = $this->adminDb->prepare($sql);
            $stmt->bindValue(':variant_id', $variantId);
            $stmt->execute();
            $variant = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$variant) {
                return ['success' => false, 'error' => 'Varyant bulunamadı'];
            }
            
            if ($this->syncSingleVariant($variant)) {
                return ['success' => true, 'message' => 'Varyant başarıyla senkronize edildi'];
            } else {
                return ['success' => false, 'error' => 'Senkronizasyon başarısız'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Silinen varyantı teklif sisteminden de sil
     */
    public function deleteVariantFromTeklif($variantId) 
    {
        try {
            $stmt = $this->teklifDb->prepare("DELETE FROM products WHERE admin_variant_id = :variant_id");
            $stmt->bindValue(':variant_id', $variantId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Ürün silme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Senkronizasyon istatistiklerini al
     */
    public function getStats() 
    {
        try {
            // Toplam aktif varyant sayısı
            $stmt = $this->adminDb->prepare("
                SELECT COUNT(*) as total_variants
                FROM product_variants pv 
                JOIN products p ON pv.product_id = p.id 
                WHERE pv.is_active = 1 AND p.is_active = 1
            ");
            $stmt->execute();
            $totalVariants = $stmt->fetchColumn();
            
            // Senkronize edilmiş varyant sayısı
            $stmt = $this->teklifDb->prepare("SELECT COUNT(*) as synced_variants FROM products WHERE admin_variant_id IS NOT NULL");
            $stmt->execute();
            $syncedVariants = $stmt->fetchColumn();
            
            return [
                'total_variants' => $totalVariants,
                'synced_variants' => $syncedVariants,
                'unsynced_variants' => $totalVariants - $syncedVariants
            ];
            
        } catch (Exception $e) {
            error_log("İstatistik alma hatası: " . $e->getMessage());
            return [
                'total_variants' => 0,
                'synced_variants' => 0,
                'unsynced_variants' => 0
            ];
        }
    }
}
?>