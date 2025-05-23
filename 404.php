<?php
require_once 'includes/config.php';

http_response_code(404);

$pageTitle = '404 - Sayfa Bulunamadı';
$pageDescription = 'Aradığınız sayfa bulunamadı. Ana sayfaya dönebilir veya arama yapabilirsiniz.';

include 'includes/header.php';
?>

<!-- 404 Error Section -->
<section class="min-h-screen flex items-center justify-center bg-gray-50 py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Error Illustration -->
        <div class="mb-8 animate-on-scroll">
            <div class="relative">
                <!-- Large 404 Text -->
                <div class="text-9xl md:text-12xl font-bold text-gray-200 select-none">
                    404
                </div>
                
                <!-- Icon Overlay -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-32 h-32 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-search text-4xl text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Error Message -->
        <div class="animate-on-scroll">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Sayfa Bulunamadı
            </h1>
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                Üzgünüz, aradığınız sayfa bulunamadı. Sayfa taşınmış, silinmiş olabilir 
                veya yanlış bir adres girmiş olabilirsiniz.
            </p>
        </div>
        
        <!-- Search Box -->
        <div class="mb-8 animate-on-scroll">
            <div class="max-w-md mx-auto">
                <form action="<?php echo BASE_URL; ?>/urunler.php" method="GET" class="relative">
                    <input type="text" name="arama" 
                           placeholder="Ürün veya kategori ara..." 
                           class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center text-blue-600 hover:text-blue-700">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12 animate-on-scroll">
            <a href="<?php echo BASE_URL; ?>" 
               class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">
                <i class="fas fa-home mr-2"></i>Ana Sayfaya Dön
            </a>
            <a href="<?php echo BASE_URL; ?>/urunler.php" 
               class="border-2 border-blue-600 text-blue-600 px-8 py-3 rounded-lg hover:bg-blue-600 hover:text-white transition duration-300 font-semibold">
                <i class="fas fa-box mr-2"></i>Ürünleri İncele
            </a>
            <a href="<?php echo BASE_URL; ?>/iletisim.php" 
               class="border-2 border-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-300 transition duration-300 font-semibold">
                <i class="fas fa-envelope mr-2"></i>İletişime Geç
            </a>
        </div>
        
        <!-- Popular Categories -->
        <div class="animate-on-scroll">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Popüler Kategoriler</h2>
            
            <?php
            // Get active categories
            $categories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order LIMIT 4");
            ?>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($categories as $category): ?>
                    <a href="<?php echo BASE_URL; ?>/kategori/<?php echo $category['slug']; ?>" 
                       class="group bg-white rounded-lg p-4 shadow-md hover:shadow-lg transition duration-300 border border-gray-200 hover:border-blue-300">
                        <?php if ($category['image']): ?>
                            <img src="<?php echo $category['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                 class="w-12 h-12 object-cover rounded-lg mx-auto mb-3">
                        <?php else: ?>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 transition duration-300">
                                <i class="fas fa-tools text-blue-600"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="text-sm font-semibold text-gray-900 text-center group-hover:text-blue-600 transition duration-300">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </h3>
                        
                        <?php
                        $productCount = fetchOne("SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_active = 1", [$category['id']]);
                        ?>
                        <p class="text-xs text-gray-500 text-center mt-1">
                            <?php echo $productCount['count']; ?> ürün
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Help Text -->
        <div class="mt-12 animate-on-scroll">
            <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">Yardıma mı ihtiyacınız var?</h3>
                <p class="text-blue-700 mb-4">
                    Aradığınızı bulamıyor musunuz? Uzman ekibimiz size yardımcı olmaya hazır.
                </p>
                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                    <a href="tel:<?php echo getSetting('company_phone'); ?>" 
                       class="text-blue-600 hover:text-blue-700 font-medium">
                        <i class="fas fa-phone mr-2"></i><?php echo getSetting('company_phone'); ?>
                    </a>
                    <span class="hidden sm:inline text-blue-300">|</span>
                    <a href="mailto:<?php echo getSetting('company_email'); ?>" 
                       class="text-blue-600 hover:text-blue-700 font-medium">
                        <i class="fas fa-envelope mr-2"></i><?php echo getSetting('company_email'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Products -->
<?php
$recentProducts = fetchAll("
    SELECT p.*, c.name as category_name,
           (SELECT pv.image FROM product_variants pv WHERE pv.product_id = p.id AND pv.image IS NOT NULL LIMIT 1) as variant_image
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 AND c.is_active = 1 
    ORDER BY p.created_at DESC 
    LIMIT 4
");
?>

<?php if (!empty($recentProducts)): ?>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 animate-on-scroll">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Son Eklenen Ürünler</h2>
            <p class="text-gray-600">Belki aradığınız bunlardan biridir</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($recentProducts as $product): ?>
                <div class="bg-gray-50 rounded-lg overflow-hidden hover:shadow-lg transition duration-300 animate-on-scroll">
                    <div class="h-48 bg-gray-200">
                        <?php 
                        $productImage = $product['main_image'] ?: $product['variant_image'] ?: IMAGES_URL . '/product-placeholder.jpg';
                        ?>
                        <img src="<?php echo $productImage; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-full h-full object-cover">
                    </div>
                    <div class="p-4">
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full mb-2">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </span>
                        <h3 class="font-semibold text-gray-900 mb-2">
                            <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>" 
                               class="hover:text-blue-600 transition duration-300">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        <p class="text-sm text-gray-600 mb-3">
                            <?php echo htmlspecialchars(substr($product['short_description'], 0, 80)) . '...'; ?>
                        </p>
                        <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>" 
                           class="text-blue-600 font-medium text-sm hover:text-blue-700 transition duration-300">
                            İncele →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.text-12xl {
    font-size: 12rem;
    line-height: 1;
}

@media (max-width: 768px) {
    .text-12xl {
        font-size: 8rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
