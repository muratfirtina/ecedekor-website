<?php
require_once 'includes/config.php';

$categorySlug = $_GET['slug'] ?? '';

if (!$categorySlug) {
    header('Location: ' . BASE_URL . '/urunler.php');
    exit;
}

// Get category
$category = fetchOne("SELECT * FROM categories WHERE slug = ? AND is_active = 1", [$categorySlug]);

if (!$category) {
    header('Location: ' . BASE_URL . '/404.php');
    exit;
}

$pageTitle = $category['name'];
$pageDescription = $category['description'] ?: 'Kaliteli ' . $category['name'] . ' ürünlerini keşfedin.';

// Get products in this category
$products = fetchAll("
    SELECT p.*, 
           (SELECT pv.image FROM product_variants pv WHERE pv.product_id = p.id AND pv.image IS NOT NULL LIMIT 1) as variant_image,
           (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.id AND pv.is_active = 1) as variant_count
    FROM products p 
    WHERE p.category_id = ? AND p.is_active = 1 
    ORDER BY p.sort_order, p.name
", [$category['id']]);

// Get other categories
$otherCategories = fetchAll("SELECT * FROM categories WHERE id != ? AND is_active = 1 ORDER BY sort_order", [$category['id']]);

include 'includes/header.php';
?>

<!-- Category Header -->
<section class="relative py-24 bg-gradient-to-r from-blue-600 to-purple-700 overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <?php if ($category['image']): ?>
        <div class="absolute inset-0 parallax" style="background-image: url('<?php echo $category['image']; ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-700 opacity-80"></div>
    <?php endif; ?>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="animate-on-scroll">
            <!-- Category Icon -->
            <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-tools text-3xl"></i>
            </div>
            
            <h1 class="text-4xl md:text-6xl font-bold mb-6 text-shadow">
                <?php echo htmlspecialchars($category['name']); ?>
            </h1>
            
            <?php if ($category['description']): ?>
                <p class="text-xl md:text-2xl mb-8 max-w-4xl mx-auto text-shadow opacity-90">
                    <?php echo htmlspecialchars($category['description']); ?>
                </p>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-8 mb-8">
                <div class="flex items-center">
                    <i class="fas fa-box mr-2"></i>
                    <span class="font-semibold"><?php echo count($products); ?> Ürün</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-palette mr-2"></i>
                    <span class="font-semibold">
                        <?php echo array_sum(array_column($products, 'variant_count')); ?> Varyant
                    </span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-award mr-2"></i>
                    <span class="font-semibold">Kalite Garantili</span>
                </div>
            </div>
            
            <!-- Breadcrumb -->
            <nav class="mt-8">
                <ol class="flex items-center justify-center space-x-2 text-sm opacity-90">
                    <li><a href="<?php echo BASE_URL; ?>" class="hover:text-gray-200">Ana Sayfa</a></li>
                    <li><i class="fas fa-chevron-right mx-2"></i></li>
                    <li><a href="<?php echo BASE_URL; ?>/urunler.php" class="hover:text-gray-200">Ürünler</a></li>
                    <li><i class="fas fa-chevron-right mx-2"></i></li>
                    <li class="text-gray-200"><?php echo htmlspecialchars($category['name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<!-- Products Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (empty($products)): ?>
            <!-- No products in category -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-box text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Bu kategoride henüz ürün bulunmuyor</h3>
                <p class="text-gray-600 mb-6">Yakında yeni ürünler eklenecek. Diğer kategorileri incelemeyi unutmayın.</p>
                <a href="<?php echo BASE_URL; ?>/urunler.php" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                    Tüm Ürünleri Görüntüle
                </a>
            </div>
        <?php else: ?>
            <!-- Category Products Header -->
            <div class="text-center mb-12 animate-on-scroll">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    <?php echo htmlspecialchars($category['name']); ?> Ürünleri
                </h2>
                <p class="text-gray-600 max-w-3xl mx-auto">
                    <?php echo count($products); ?> adet kaliteli ürün bulundu
                </p>
            </div>
            
            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition duration-300 hover-scale animate-on-scroll">
                        <!-- Product Image -->
                        <div class="relative h-64 bg-gray-200 group">
                            <?php 
                            $productImage = $product['main_image'] ?: $product['variant_image'] ?: IMAGES_URL . '/product-placeholder.jpg';
                            ?>
                            <img src="<?php echo $productImage; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                            
                            <!-- Variant Count Badge -->
                            <?php if ($product['variant_count'] > 0): ?>
                                <div class="absolute top-4 right-4">
                                    <span class="bg-purple-600 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                        <?php echo $product['variant_count']; ?> Varyant
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Overlay on hover -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>" 
                                   class="bg-white text-gray-900 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition duration-300 transform translate-y-4 group-hover:translate-y-0">
                                    <i class="fas fa-eye mr-2"></i>İncele
                                </a>
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>" 
                                   class="hover:text-blue-600 transition duration-300">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                <?php echo htmlspecialchars($product['short_description']); ?>
                            </p>
                            
                            <!-- Features Preview -->
                            <?php if ($product['features']): ?>
                                <div class="mb-4">
                                    <?php 
                                    $features = explode("\n", $product['features']);
                                    $feature = trim($features[0]);
                                    if ($feature):
                                    ?>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            <?php echo htmlspecialchars($feature); ?>
                                        </div>
                                        <?php if (count($features) > 1): ?>
                                            <div class="text-xs text-gray-500 mt-1">
                                                +<?php echo count($features) - 1; ?> özellik daha
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Action -->
                            <div class="flex items-center justify-between">
                                <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>" 
                                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 text-sm font-medium">
                                    Detayları Gör
                                </a>
                                
                                <div class="flex space-x-2">
                                    <button class="text-gray-400 hover:text-red-500 transition duration-300" title="Favorilere Ekle">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <button class="text-gray-400 hover:text-blue-500 transition duration-300" title="Paylaş">
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

<!-- Related Categories -->
<?php if (!empty($otherCategories)): ?>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 animate-on-scroll">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Diğer Kategoriler</h2>
            <p class="text-gray-600">Kaliteli ürün gamımızın diğer kategorilerini keşfedin</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach (array_slice($otherCategories, 0, 6) as $otherCategory): ?>
                <a href="<?php echo BASE_URL; ?>/kategori/<?php echo $otherCategory['slug']; ?>" 
                   class="group bg-gray-50 rounded-2xl p-6 hover:bg-blue-50 transition duration-300 card-shadow hover-scale animate-on-scroll">
                    <div class="flex items-center mb-4">
                        <?php if ($otherCategory['image']): ?>
                            <img src="<?php echo $otherCategory['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($otherCategory['name']); ?>" 
                                 class="w-12 h-12 object-cover rounded-lg mr-4">
                        <?php else: ?>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-blue-200 transition duration-300">
                                <i class="fas fa-tools text-blue-600"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition duration-300">
                                <?php echo htmlspecialchars($otherCategory['name']); ?>
                            </h3>
                            <?php
                            $categoryProductCount = fetchOne("SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_active = 1", [$otherCategory['id']]);
                            ?>
                            <p class="text-sm text-gray-600"><?php echo $categoryProductCount['count']; ?> ürün</p>
                        </div>
                        
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition duration-300"></i>
                    </div>
                    
                    <?php if ($otherCategory['description']): ?>
                        <p class="text-sm text-gray-600 line-clamp-2">
                            <?php echo htmlspecialchars($otherCategory['description']); ?>
                        </p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="<?php echo BASE_URL; ?>/urunler.php" 
               class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">
                <i class="fas fa-th-large mr-2"></i>Tüm Kategorileri Görüntüle
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-r from-blue-600 to-purple-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="animate-on-scroll">
            <h2 class="text-3xl font-bold mb-4">
                <?php echo htmlspecialchars($category['name']); ?> Hakkında Sorularınız mı Var?
            </h2>
            <p class="text-xl mb-8 opacity-90">
                Uzman ekibimiz size en uygun ürünü bulmak için burada
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo BASE_URL; ?>/iletisim.php" 
                   class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                    <i class="fas fa-envelope mr-2"></i>İletişime Geçin
                </a>
                <a href="tel:<?php echo getSetting('company_phone'); ?>" 
                   class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">
                    <i class="fas fa-phone mr-2"></i>Hemen Arayın
                </a>
            </div>
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
