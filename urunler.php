<?php
require_once 'includes/config.php';

$pageTitle = 'Ürünlerimiz';
$pageDescription = 'Ahşap tamir macunları, zemin koruyucu keçeler ve yapışkanlı tapalar gibi kaliteli ürünlerimizi keşfedin.';

// Get filter parameters
$category_slug = $_GET['kategori'] ?? '';
$search = $_GET['arama'] ?? '';

// Build query conditions
$whereConditions = ['p.is_active = 1', 'c.is_active = 1'];
$params = [];

if ($category_slug) {
    $whereConditions[] = 'c.slug = ?';
    $params[] = $category_slug;
}

if ($search) {
    $whereConditions[] = '(p.name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

$whereClause = implode(' AND ', $whereConditions);

// Get products
$products = fetchAll("
    SELECT p.*, c.name as category_name, c.slug as category_slug,
           (SELECT pv.image FROM product_variants pv WHERE pv.product_id = p.id AND pv.image IS NOT NULL LIMIT 1) as variant_image
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE $whereClause
    ORDER BY p.sort_order, p.name
", $params);

// Get product variants with color codes for each product
$productVariants = [];
foreach ($products as $product) {
    $productVariants[$product['id']] = fetchAll("
        SELECT id, name, color, color_code FROM product_variants 
        WHERE product_id = ? AND is_active = 1 AND color_code IS NOT NULL 
        ORDER BY sort_order, name LIMIT 5
    ", [$product['id']]);
}

// Get categories for filter
$categories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");

// Get selected category info
$selectedCategory = null;
if ($category_slug) {
    $selectedCategory = fetchOne("SELECT * FROM categories WHERE slug = ? AND is_active = 1", [$category_slug]);
}

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-red-600 to-black py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">
            <?php echo $selectedCategory ? htmlspecialchars($selectedCategory['name']) : 'Ürünlerimiz'; ?>
        </h1>
        <p class="text-xl opacity-90 max-w-3xl mx-auto">
            <?php if ($selectedCategory): ?>
                <?php echo htmlspecialchars($selectedCategory['description']); ?>
            <?php else: ?>
                Ahşap tamir ve dolgu malzemelerinde <div class="text-3xl font-bold"><?php echo (int)date('Y') - (int)getSetting('company_founded', 1998); ?>+</div> yıllık deneyimimizle ürettiğimiz kaliteli ürünler
            <?php endif; ?>
        </p>
        
        <!-- Breadcrumb -->
        <nav class="mt-8">
            <ol class="flex items-center justify-center space-x-2 text-sm">
                <li><a href="<?php echo BASE_URL; ?>" class="hover:text-gray-200">Ana Sayfa</a></li>
                <li><i class="fas fa-chevron-right mx-2"></i></li>
                <?php if ($selectedCategory): ?>
                    <li><a href="<?php echo BASE_URL; ?>/urunler.php" class="hover:text-gray-200">Ürünler</a></li>
                    <li><i class="fas fa-chevron-right mx-2"></i></li>
                    <li class="text-gray-200"><?php echo htmlspecialchars($selectedCategory['name']); ?></li>
                <?php else: ?>
                    <li class="text-gray-200">Ürünler</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</section>

<!-- Filters and Search -->
<section class="py-8 bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <form method="GET" class="relative">
                    <?php if ($category_slug): ?>
                        <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($category_slug); ?>">
                    <?php endif; ?>
                    <input type="text" name="arama" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Ürün ara..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
            
            <!-- Category Filter -->
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo BASE_URL; ?>/urunler.php<?php echo $search ? '?arama=' . urlencode($search) : ''; ?>" 
                   class="px-4 py-2 rounded-full text-sm font-medium transition duration-300 <?php echo !$category_slug ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    Tümü
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="<?php echo BASE_URL; ?>/urunler.php?kategori=<?php echo $category['slug']; ?><?php echo $search ? '&arama=' . urlencode($search) : ''; ?>" 
                       class="px-4 py-2 rounded-full text-sm font-medium transition duration-300 <?php echo $category_slug === $category['slug'] ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Results count -->
        <div class="mt-4 text-sm text-gray-600">
            <span class="font-medium"><?php echo count($products); ?></span> ürün bulundu
            <?php if ($search): ?>
                "<span class="font-medium"><?php echo htmlspecialchars($search); ?></span>" araması için
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Products Grid -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (empty($products)): ?>
            <!-- No products found -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-search text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-2">Ürün bulunamadı</h3>
                <p class="text-gray-600 mb-6">Arama kriterlerinize uygun ürün bulunamadı. Farklı bir arama deneyin.</p>
                <a href="<?php echo BASE_URL; ?>/urunler.php" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition duration-300">
                    Tüm Ürünleri Görüntüle
                </a>
            </div>
        <?php else: ?>
            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition duration-300 card-shadow hover-scale animate-on-scroll">
                        <!-- Product Image -->
                        <div class="relative h-64 bg-gray-200">
                            <?php 
                            $productImage = $product['main_image'] ?: $product['variant_image'] ?: IMAGES_URL . '/product-placeholder.jpg';
                            ?>
                            <img src="<?php echo $productImage; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-full h-full object-cover">
                            
                            <!-- Category Badge -->
                            <div class="absolute top-4 left-4">
                                <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </span>
                            </div>
                            
                            <!-- Quick View Button -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-30 transition duration-300 flex items-center justify-center opacity-0 hover:opacity-100">
                                <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>" 
                                   class="bg-white text-black px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                                    <i class="fas fa-eye mr-2"></i>Detayları Gör
                                </a>
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-black mb-2 line-clamp-2">
                                <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>" class="hover:text-red-600 transition duration-300">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                <?php echo htmlspecialchars($product['short_description']); ?>
                            </p>
                            
                            <!-- Product Variants Colors -->
                            <?php if (!empty($productVariants[$product['id']])): ?>
                                <div class="mb-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-600">Renkler:</span>
                                        <div class="flex space-x-1">
                                            <?php foreach ($productVariants[$product['id']] as $variant): ?>
                                                <div class="w-4 h-4 rounded-full border border-gray-300" 
                                                     style="background-color: <?php echo $variant['color_code']; ?>;" 
                                                     title="<?php echo htmlspecialchars($variant['color']); ?>"></div>
                                            <?php endforeach; ?>
                                            <?php 
                                            $totalVariants = fetchOne("SELECT COUNT(*) as count FROM product_variants WHERE product_id = ? AND is_active = 1", [$product['id']]);
                                            if ($totalVariants['count'] > 5): 
                                            ?>
                                                <span class="text-xs text-gray-500">+<?php echo $totalVariants['count'] - 5; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Product Features -->
                            <?php if ($product['features']): ?>
                                <div class="mb-4">
                                    <div class="flex flex-wrap gap-1">
                                        <?php 
                                        $features = explode("\n", $product['features']);
                                        $displayFeatures = array_slice($features, 0, 2);
                                        foreach ($displayFeatures as $feature): 
                                            $feature = trim($feature);
                                            if ($feature):
                                        ?>
                                            <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                                                <?php echo htmlspecialchars($feature); ?>
                                            </span>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                        <?php if (count($features) > 2): ?>
                                            <span class="inline-block bg-red-100 text-red-700 text-xs px-2 py-1 rounded">
                                                +<?php echo count($features) - 2; ?> özellik
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Güvenlik Rozetleri -->
                                <div class="mb-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                            <i class="fas fa-shield-alt mr-1"></i>Çocuk Güvenli
                                        </span>
                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                            <i class="fas fa-paw mr-1"></i>Evcil Hayvan Dostu
                                        </span>
                                        <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">
                                            <i class="fas fa-leaf mr-1"></i>Alerjen İçermez
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center justify-between">
                                <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>" 
                                   class="flex items-center text-red-600 font-semibold hover:text-red-700 transition duration-300">
                                    Detayları Gör
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                                
                                <div class="flex space-x-2">
                                    <button class="text-gray-400 hover:text-red-500 transition duration-300" title="Favorilere Ekle">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <button class="text-gray-400 hover:text-red-500 transition duration-300" title="Paylaş">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-red-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl font-bold mb-4">Aradığınızı Bulamadınız mı?</h2>
        <p class="text-xl mb-8 opacity-90">
            Uzman ekibimiz size özel çözümler sunmaya hazır
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?php echo BASE_URL; ?>/iletisim.php" 
               class="bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                <i class="fas fa-envelope mr-2"></i>İletişime Geçin
            </a>
            <a href="tel:<?php echo getSetting('company_phone'); ?>" 
               class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-red-600 transition duration-300">
                <i class="fas fa-phone mr-2"></i>Hemen Arayın
            </a>
        </div>
    </div>
</section>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php include 'includes/footer.php'; ?>
