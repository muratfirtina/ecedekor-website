<?php
require_once 'includes/config.php';

$productSlug = $_GET['slug'] ?? '';

if (!$productSlug) {
    header('Location: ' . BASE_URL . '/urunler.php');
    exit;
}

// Get product with category info
$product = fetchOne("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.slug = ? AND p.is_active = 1 AND c.is_active = 1
", [$productSlug]);

if (!$product) {
    header('Location: ' . BASE_URL . '/404.php');
    exit;
}

$pageTitle = $product['name'];
$pageDescription = $product['short_description'] ?: 'Kaliteli ' . $product['name'] . ' ürününü keşfedin.';

// Get product variants
$variants = fetchAll("
    SELECT * FROM product_variants 
    WHERE product_id = ? AND is_active = 1 
    ORDER BY sort_order, name
", [$product['id']]);

// Get product images
$productImages = fetchAll("
    SELECT * FROM product_images 
    WHERE product_id = ? 
    ORDER BY sort_order
", [$product['id']]);

// Get related products from same category
$relatedProducts = fetchAll("
    SELECT p.*, 
           (SELECT pv.image FROM product_variants pv WHERE pv.product_id = p.id AND pv.image IS NOT NULL LIMIT 1) as variant_image
    FROM products p 
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 
    ORDER BY p.sort_order 
    LIMIT 4
", [$product['category_id'], $product['id']]);

// Process features
$features = [];
if ($product['features']) {
    $features = array_filter(array_map('trim', explode("\n", $product['features'])));
}

// Process usage instructions
$usageSteps = [];
if ($product['usage_instructions']) {
    $usageSteps = array_filter(array_map('trim', explode("\n", $product['usage_instructions'])));
}

include 'includes/header.php';
?>

<!-- Product Detail Section -->
<section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li><a href="<?php echo BASE_URL; ?>" class="hover:text-blue-600">Ana Sayfa</a></li>
                <li><i class="fas fa-chevron-right mx-2"></i></li>
                <li><a href="<?php echo BASE_URL; ?>/urunler.php" class="hover:text-blue-600">Ürünler</a></li>
                <li><i class="fas fa-chevron-right mx-2"></i></li>
                <li><a href="<?php echo BASE_URL; ?>/kategori/<?php echo $product['category_slug']; ?>" class="hover:text-blue-600"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <li><i class="fas fa-chevron-right mx-2"></i></li>
                <li class="text-gray-900 font-medium"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Product Images -->
            <div class="space-y-4">
                <!-- Main Image -->
                <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden">
                    <?php 
                    $mainImage = $product['main_image'] ?: (isset($variants[0]) ? $variants[0]['image'] : IMAGES_URL . '/product-placeholder.jpg');
                    ?>
                    <img id="mainProductImage" src="<?php echo $mainImage; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="w-full h-full object-cover">
                </div>
                
                <!-- Thumbnail Images -->
                <?php if (!empty($productImages) || !empty($variants)): ?>
                    <div class="flex space-x-2 overflow-x-auto pb-2">
                        <?php if ($product['main_image']): ?>
                            <button onclick="changeMainImage('<?php echo $product['main_image']; ?>')" 
                                    class="flex-shrink-0 w-20 h-20 bg-gray-100 rounded-lg overflow-hidden border-2 border-blue-600">
                                <img src="<?php echo $product['main_image']; ?>" 
                                     alt="Ana görsel" 
                                     class="w-full h-full object-cover">
                            </button>
                        <?php endif; ?>
                        
                        <?php foreach ($productImages as $image): ?>
                            <button onclick="changeMainImage('<?php echo $image['image_path']; ?>')" 
                                    class="flex-shrink-0 w-20 h-20 bg-gray-100 rounded-lg overflow-hidden border-2 border-transparent hover:border-blue-600 transition duration-300">
                                <img src="<?php echo $image['image_path']; ?>" 
                                     alt="<?php echo htmlspecialchars($image['alt_text']); ?>" 
                                     class="w-full h-full object-cover">
                            </button>
                        <?php endforeach; ?>
                        
                        <?php foreach ($variants as $variant): ?>
                            <?php if ($variant['image']): ?>
                                <button onclick="changeMainImage('<?php echo $variant['image']; ?>')" 
                                        class="flex-shrink-0 w-20 h-20 bg-gray-100 rounded-lg overflow-hidden border-2 border-transparent hover:border-blue-600 transition duration-300">
                                    <img src="<?php echo $variant['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($variant['name']); ?>" 
                                         class="w-full h-full object-cover">
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Info -->
            <div class="space-y-6">
                <!-- Category Badge -->
                <div>
                    <a href="<?php echo BASE_URL; ?>/kategori/<?php echo $product['category_slug']; ?>" 
                       class="inline-block bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full hover:bg-blue-200 transition duration-300">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                </div>
                
                <!-- Product Title -->
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
                
                <!-- Short Description -->
                <?php if ($product['short_description']): ?>
                    <p class="text-xl text-gray-600 leading-relaxed">
                        <?php echo htmlspecialchars($product['short_description']); ?>
                    </p>
                <?php endif; ?>
                
                <!-- Variants -->
                <?php if (!empty($variants)): ?>
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">Ürün Varyantları</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($variants as $variant): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:bg-blue-50 transition duration-300 cursor-pointer"
                                     onclick="selectVariant('<?php echo htmlspecialchars($variant['name']); ?>', '<?php echo $variant['image']; ?>')">
                                    <div class="flex items-center">
                                        <?php if ($variant['image']): ?>
                                            <img src="<?php echo $variant['image']; ?>" 
                                                 alt="<?php echo htmlspecialchars($variant['name']); ?>" 
                                                 class="w-12 h-12 object-cover rounded-lg mr-3">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-palette text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($variant['name']); ?></div>
                                            <div class="text-sm text-gray-600">
                                                <?php if ($variant['color']): ?>
                                                    Renk: <?php echo htmlspecialchars($variant['color']); ?>
                                                <?php endif; ?>
                                                <?php if ($variant['size']): ?>
                                                    <span class="<?php echo $variant['color'] ? 'ml-2' : ''; ?>">
                                                        Boyut: <?php echo htmlspecialchars($variant['size']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($variant['weight']): ?>
                                                    <span class="<?php echo ($variant['color'] || $variant['size']) ? 'ml-2' : ''; ?>">
                                                        <?php echo htmlspecialchars($variant['weight']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($variant['sku']): ?>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    SKU: <?php echo htmlspecialchars($variant['sku']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($variant['price']): ?>
                                            <div class="text-lg font-bold text-blue-600">
                                                ₺<?php echo number_format($variant['price'], 2); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?php echo BASE_URL; ?>/iletisim.php?urun=<?php echo urlencode($product['name']); ?>" 
                       class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold text-center">
                        <i class="fas fa-envelope mr-2"></i>Fiyat Teklifi Al
                    </a>
                    <a href="tel:<?php echo getSetting('company_phone'); ?>" 
                       class="border-2 border-blue-600 text-blue-600 px-8 py-3 rounded-lg hover:bg-blue-600 hover:text-white transition duration-300 font-semibold text-center">
                        <i class="fas fa-phone mr-2"></i>Hemen Ara
                    </a>
                </div>
                
                <!-- Share Buttons -->
                <div class="flex items-center space-x-4 pt-4 border-t border-gray-200">
                    <span class="text-sm font-medium text-gray-700">Paylaş:</span>
                    <button onclick="shareProduct('facebook')" class="text-blue-600 hover:text-blue-700 transition duration-300">
                        <i class="fab fa-facebook-f text-lg"></i>
                    </button>
                    <button onclick="shareProduct('twitter')" class="text-blue-400 hover:text-blue-500 transition duration-300">
                        <i class="fab fa-twitter text-lg"></i>
                    </button>
                    <button onclick="shareProduct('linkedin')" class="text-blue-700 hover:text-blue-800 transition duration-300">
                        <i class="fab fa-linkedin-in text-lg"></i>
                    </button>
                    <button onclick="shareProduct('whatsapp')" class="text-green-600 hover:text-green-700 transition duration-300">
                        <i class="fab fa-whatsapp text-lg"></i>
                    </button>
                    <button onclick="copyLink()" class="text-gray-600 hover:text-gray-700 transition duration-300" id="copyButton">
                        <i class="fas fa-link text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Details Tabs -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div x-data="{ activeTab: 'description' }" class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6">
                    <button @click="activeTab = 'description'" 
                            :class="{ 'border-blue-600 text-blue-600': activeTab === 'description' }"
                            class="py-4 px-1 border-b-2 border-transparent font-medium text-sm hover:text-blue-600 transition duration-300">
                        Açıklama
                    </button>
                    <?php if (!empty($features)): ?>
                        <button @click="activeTab = 'features'" 
                                :class="{ 'border-blue-600 text-blue-600': activeTab === 'features' }"
                                class="py-4 px-1 border-b-2 border-transparent font-medium text-sm hover:text-blue-600 transition duration-300">
                            Özellikler
                        </button>
                    <?php endif; ?>
                    <?php if (!empty($usageSteps)): ?>
                        <button @click="activeTab = 'usage'" 
                                :class="{ 'border-blue-600 text-blue-600': activeTab === 'usage' }"
                                class="py-4 px-1 border-b-2 border-transparent font-medium text-sm hover:text-blue-600 transition duration-300">
                            Kullanım
                        </button>
                    <?php endif; ?>
                </nav>
            </div>
            
            <!-- Tab Content -->
            <div class="p-6">
                <!-- Description Tab -->
                <div x-show="activeTab === 'description'" class="prose max-w-none">
                    <?php if ($product['description']): ?>
                        <div class="text-gray-700 leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600">Bu ürün için detaylı açıklama henüz eklenmemiş.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Features Tab -->
                <?php if (!empty($features)): ?>
                    <div x-show="activeTab === 'features'" class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Ürün Özellikleri</h3>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php foreach ($features as $feature): ?>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                                    <span class="text-gray-700"><?php echo htmlspecialchars($feature); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Usage Tab -->
                <?php if (!empty($usageSteps)): ?>
                    <div x-show="activeTab === 'usage'" class="space-y-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Kullanım Talimatları</h3>
                        <div class="space-y-4">
                            <?php foreach ($usageSteps as $index => $step): ?>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold text-sm mr-4">
                                        <?php echo $index + 1; ?>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-700"><?php echo htmlspecialchars($step); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">İlgili Ürünler</h2>
            <p class="text-gray-600">Aynı kategorideki diğer kaliteli ürünlerimiz</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="bg-gray-50 rounded-2xl overflow-hidden hover:shadow-lg transition duration-300">
                    <div class="h-48 bg-gray-200">
                        <?php 
                        $relatedImage = $relatedProduct['main_image'] ?: $relatedProduct['variant_image'] ?: IMAGES_URL . '/product-placeholder.jpg';
                        ?>
                        <img src="<?php echo $relatedImage; ?>" 
                             alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>" 
                             class="w-full h-full object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">
                            <a href="<?php echo BASE_URL; ?>/urun/<?php echo $relatedProduct['slug']; ?>" 
                               class="hover:text-blue-600 transition duration-300">
                                <?php echo htmlspecialchars($relatedProduct['name']); ?>
                            </a>
                        </h3>
                        <p class="text-sm text-gray-600 mb-3">
                            <?php echo htmlspecialchars(substr($relatedProduct['short_description'], 0, 80)) . '...'; ?>
                        </p>
                        <a href="<?php echo BASE_URL; ?>/urun/<?php echo $relatedProduct['slug']; ?>" 
                           class="text-blue-600 font-medium text-sm hover:text-blue-700 transition duration-300">
                            Detayları Gör →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-16 bg-blue-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl font-bold mb-4">Bu Ürünle İlgili Sorularınız mı Var?</h2>
        <p class="text-xl mb-8 opacity-90">
            Uzman ekibimiz size yardımcı olmaya hazır
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?php echo BASE_URL; ?>/iletisim.php?urun=<?php echo urlencode($product['name']); ?>" 
               class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                <i class="fas fa-envelope mr-2"></i>Teknik Destek Al
            </a>
            <a href="tel:<?php echo getSetting('company_phone'); ?>" 
               class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">
                <i class="fas fa-phone mr-2"></i>Hemen Arayın
            </a>
        </div>
    </div>
</section>

<script>
function changeMainImage(imageSrc) {
    document.getElementById('mainProductImage').src = imageSrc;
    
    // Update active thumbnail
    document.querySelectorAll('[onclick*="changeMainImage"]').forEach(btn => {
        btn.classList.remove('border-blue-600');
        btn.classList.add('border-transparent');
    });
    event.target.closest('button').classList.add('border-blue-600');
    event.target.closest('button').classList.remove('border-transparent');
}

function selectVariant(variantName, variantImage) {
    if (variantImage) {
        changeMainImage(variantImage);
    }
    showMessage('Varyant seçildi: ' + variantName, 'success');
}

function shareProduct(platform) {
    const url = window.location.href;
    const title = '<?php echo addslashes($product['name']); ?>';
    const text = '<?php echo addslashes($product['short_description']); ?>';
    
    let shareUrl;
    
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' - ' + url)}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        showMessage('Link panoya kopyalandı!', 'success');
        document.getElementById('copyButton').innerHTML = '<i class="fas fa-check text-lg"></i>';
        setTimeout(() => {
            document.getElementById('copyButton').innerHTML = '<i class="fas fa-link text-lg"></i>';
        }, 2000);
    });
}
</script>

<?php include 'includes/footer.php'; ?>
