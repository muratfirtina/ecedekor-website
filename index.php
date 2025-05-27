<?php
require_once 'includes/config.php';

$pageTitle = 'Ana Sayfa';
$pageDescription = 'Ahşap tamir macunları, zemin koruyucu keçeler ve yapışkanlı tapalar konusunda 25 yıllık deneyimle hizmet veriyoruz.';

// Get homepage sections
$heroSection = fetchOne("SELECT * FROM homepage_sections WHERE section_type = 'hero' AND is_active = 1 ORDER BY sort_order LIMIT 1");
$aboutSection = fetchOne("SELECT * FROM homepage_sections WHERE section_type = 'about' AND is_active = 1 ORDER BY sort_order LIMIT 1");

// Get featured products
$featuredProducts = fetchAll("
    SELECT p.*, c.name as category_name, c.slug as category_slug,
           (SELECT pv.image FROM product_variants pv WHERE pv.product_id = p.id AND pv.image IS NOT NULL LIMIT 1) as variant_image
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 AND c.is_active = 1 
    ORDER BY p.sort_order 
    LIMIT 6
");

// Get product variants with color codes for featured products
$featuredProductVariants = [];
foreach ($featuredProducts as $product) {
    $featuredProductVariants[$product['id']] = fetchAll("
        SELECT id, name, color, color_code FROM product_variants 
        WHERE product_id = ? AND is_active = 1 AND color_code IS NOT NULL 
        ORDER BY sort_order, name LIMIT 4
    ", [$product['id']]);
}

// Get testimonials
$testimonials = fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order LIMIT 3");

// Get categories
$categories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center">
    <!-- Hero Background Image -->
    <div class="absolute inset-0 z-0">
        <?php 
        // Önce homepage_sections tablosundan hero bölümünü alalım
        $heroSection = fetchOne("SELECT * FROM homepage_sections WHERE section_type = 'hero' AND is_active = 1 ORDER BY sort_order LIMIT 1");
        
        // Hero resmi varsa onu gösterelim
        if ($heroSection && !empty($heroSection['image'])): 
        ?>
            <img src="<?php echo $heroSection['image']; ?>" class="w-full h-full object-cover" alt="Hero Background">
        <?php 
        // Eğer hero ayarı (site settings) varsa onu gösterelim
        elseif (getSetting('hero_image') && !empty(getSetting('hero_image'))): 
        ?>
            <img src="<?php echo getSetting('hero_image'); ?>" class="w-full h-full object-cover" alt="Hero Background">
        <?php 
        // Hiçbiri yoksa varsayılan gradient gösterelim
        else: 
        ?>
            <div class="w-full h-full bg-gradient-to-r from-red-600 to-black"></div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-black bg-opacity-70"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white z-10">
        <div class="animate-on-scroll">
            <h1 class="text-5xl md:text-7xl font-bold mb-6 text-shadow">
                <?php echo $heroSection ? htmlspecialchars($heroSection['title']) : 'Ahşap Tamir ve Dolgu Malzemelerinde Uzman'; ?>
            </h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto text-shadow">
                <?php echo $heroSection ? htmlspecialchars($heroSection['subtitle']) : 'ECEDEKOR ile Kaliteli Çözümler'; ?>
            </p>
            <p class="text-lg mb-10 max-w-4xl mx-auto opacity-90">
                <?php echo $heroSection ? htmlspecialchars($heroSection['content']) : '1998 yılından bu yana mobilya sektöründe kullanılmak üzere dolgu macunu, pvc tapa ve keçe üretimi yapmaktayız.'; ?>
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo BASE_URL; ?>/urunler.php" class="bg-white text-red-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-gray-100 transition duration-300 hover-scale">
                    <i class="fas fa-box mr-2"></i>Ürünlerimizi İnceleyin
                </a>
                <a href="<?php echo BASE_URL; ?>/iletisim.php" class="border-2 border-white text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-white hover:text-red-600 transition duration-300 hover-scale">
                    <i class="fas fa-phone mr-2"></i>İletişime Geçin
                </a>
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white animate-bounce">
        <i class="fas fa-chevron-down text-2xl"></i>
    </div>
</section>

<!-- Stats Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="animate-on-scroll">
                <div class="text-4xl font-bold text-red-600 mb-2"><?php echo getSetting('company_founded', '1998'); ?></div>
                <div class="text-gray-600">Kuruluş Yılı</div>
            </div>
            <div class="animate-on-scroll">
                <div class="text-4xl font-bold text-red-600 mb-2"><?php echo (int)date('Y') - (int)getSetting('company_founded', 1998); ?>+</div>
                <div class="text-gray-600">Yıllık Deneyim</div>
            </div>
            <div class="animate-on-scroll">
                <div class="text-4xl font-bold text-red-600 mb-2">1000+</div>
                <div class="text-gray-600">Mutlu Müşteri</div>
            </div>
            <div class="animate-on-scroll">
                <div class="text-4xl font-bold text-red-600 mb-2">50+</div>
                <div class="text-gray-600">Ürün Çeşidi</div>
            </div>
        </div>
    </div>
</section>

<!-- Product Categories -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 animate-on-scroll">
            <h2 class="text-4xl font-bold text-black mb-4">Ürün Kategorilerimiz</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Mobilya sektörünün ihtiyaçlarına yönelik kaliteli ürünler sunuyoruz
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($categories as $category): ?>
                <div class="bg-white rounded-2xl overflow-hidden card-shadow hover-scale animate-on-scroll">
                    <div class="h-48 bg-gradient-to-br from-red-500 to-purple-600 relative">
                        <?php if ($category['image']): ?>
                            <img src="<?php echo $category['image']; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="w-full h-full object-cover">
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                        <div class="absolute bottom-4 left-4 text-white">
                            <i class="fas fa-tools text-3xl mb-2"></i>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-black mb-3"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="text-gray-600 mb-4 text-sm leading-relaxed">
                            <?php echo htmlspecialchars($category['description']); ?>
                        </p>
                        <a href="<?php echo BASE_URL; ?>/kategori/<?php echo $category['slug']; ?>" class="inline-flex items-center text-red-600 font-semibold hover:text-red-700 transition duration-300">
                            Ürünleri Görüntüle
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 animate-on-scroll">
            <h2 class="text-4xl font-bold text-black mb-4">Öne Çıkan Ürünler</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                En çok tercih edilen kaliteli ürünlerimizi keşfedin
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="bg-white rounded-2xl overflow-hidden card-shadow hover-scale animate-on-scroll">
                    <div class="h-64 bg-gray-200 relative">
                        <?php 
                        $productImage = $product['main_image'] ?: $product['variant_image'] ?: IMAGES_URL . '/product-placeholder.jpg';
                        ?>
                        <img src="<?php echo $productImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-black mb-3"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-600 mb-4 text-sm leading-relaxed">
                        <?php echo htmlspecialchars(substr($product['short_description'], 0, 120)) . '...'; ?>
                        </p>
                            
                            <!-- Product Variants Colors -->
                            <?php if (!empty($featuredProductVariants[$product['id']])): ?>
                                <div class="mb-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-600">Renkler:</span>
                                        <div class="flex space-x-1">
                                            <?php foreach ($featuredProductVariants[$product['id']] as $variant): ?>
                                                <div class="w-3 h-3 rounded-full border border-gray-300" 
                                                     style="background-color: <?php echo $variant['color_code']; ?>;" 
                                                     title="<?php echo htmlspecialchars($variant['color']); ?>"></div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <div class="flex justify-between items-center">
                            <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>" class="inline-flex items-center text-red-600 font-semibold hover:text-red-700 transition duration-300">
                                Detayları Görüntüle
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                            <div class="flex space-x-2">
                                <button class="text-gray-400 hover:text-red-500 transition duration-300">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <button class="text-gray-400 hover:text-red-500 transition duration-300">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="<?php echo BASE_URL; ?>/urunler.php" class="bg-red-600 text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-red-700 transition duration-300 hover-scale">
                <i class="fas fa-box mr-2"></i>Tüm Ürünleri Görüntüle
            </a>
        </div>
    </div>
</section>

<!-- About Section -->
<?php if ($aboutSection): ?>
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="animate-on-scroll">
                <h2 class="text-4xl font-bold text-black mb-6"><?php echo htmlspecialchars($aboutSection['title']); ?></h2>
                <h3 class="text-2xl text-red-600 font-semibold mb-4"><?php echo htmlspecialchars($aboutSection['subtitle']); ?></h3>
                <p class="text-gray-600 mb-8 leading-relaxed text-lg">
                    <?php echo nl2br(htmlspecialchars($aboutSection['content'])); ?>
                </p>
                
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="text-center p-4 bg-white rounded-lg card-shadow">
                        <i class="fas fa-award text-3xl text-red-600 mb-2"></i>
                        <div class="font-semibold text-black">Kalite Garantisi</div>
                        <div class="text-sm text-gray-600">ISO sertifikalı</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-lg card-shadow">
                        <i class="fas fa-shipping-fast text-3xl text-red-600 mb-2"></i>
                        <div class="font-semibold text-black">Hızlı Teslimat</div>
                        <div class="text-sm text-gray-600">Türkiye geneli</div>
                    </div>
                </div>
                
                <?php if ($aboutSection['button_text']): ?>
                    <a href="<?php echo $aboutSection['button_link']; ?>" class="bg-red-600 text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-red-700 transition duration-300 hover-scale inline-block">
                        <?php echo htmlspecialchars($aboutSection['button_text']); ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="animate-on-scroll">
                <div class="relative">
                    <img src="<?php echo $aboutSection['image'] ? $aboutSection['image'] : (getSetting('about_image') ? getSetting('about_image') : IMAGES_URL . '/about-us.jpg'); ?>" alt="Hakkımızda" class="rounded-2xl w-full h-96 object-cover card-shadow">
                    <div class="absolute -bottom-6 -right-6 bg-red-600 text-white p-6 rounded-2xl card-shadow">
                        <div class="text-center">
                            <div class="text-3xl font-bold"><?php echo (int)date('Y') - (int)getSetting('company_founded', 1998); ?>+</div>
                            <div class="text-sm">Yıllık Deneyim</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 animate-on-scroll">
            <h2 class="text-4xl font-bold text-black mb-4">Neden ECEDEKOR?</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Sektörde 25 yıllık deneyimimizle sizlere en iyi hizmeti sunuyoruz
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center animate-on-scroll">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-medal text-3xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-4">Kaliteli Ürünler</h3>
                <p class="text-gray-600 leading-relaxed">
                    ISO standartlarında üretilen ürünlerimiz ile en yüksek kaliteyi garanti ediyoruz.
                </p>
            </div>
            
            <div class="text-center animate-on-scroll">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-headset text-3xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-4">Müşteri Desteği</h3>
                <p class="text-gray-600 leading-relaxed">
                    7/24 müşteri desteği ile tüm sorularınıza hızlı ve etkili çözümler sunuyoruz.
                </p>
            </div>
            
            <div class="text-center animate-on-scroll">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-truck text-3xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-4">Hızlı Teslimat</h3>
                <p class="text-gray-600 leading-relaxed">
                    Türkiye genelinde hızlı ve güvenli teslimat ağımız ile siparişlerinizi ulaştırıyoruz.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 animate-on-scroll">
            <h2 class="text-4xl font-bold text-black mb-4">Müşterilerimiz Ne Diyor?</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Memnun müşterilerimizin deneyimlerini okuyun
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="bg-white rounded-2xl p-8 card-shadow animate-on-scroll">
                    <div class="flex items-center mb-4">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    
                    <blockquote class="text-gray-700 mb-6 leading-relaxed">
                        "<?php echo htmlspecialchars($testimonial['content']); ?>"
                    </blockquote>
                    
                    <div class="flex items-center">
                        <?php if ($testimonial['image']): ?>
                            <img src="<?php echo $testimonial['image']; ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="w-12 h-12 rounded-full object-cover mr-4">
                        <?php else: ?>
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user text-red-600"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="font-semibold text-black"><?php echo htmlspecialchars($testimonial['name']); ?></div>
                            <?php if ($testimonial['company']): ?>
                                <div class="text-sm text-gray-600"><?php echo htmlspecialchars($testimonial['company']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-20 bg-red-600 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-black"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="animate-on-scroll">
            <h2 class="text-4xl font-bold mb-6">Projeleriniz İçin Hemen İletişime Geçin</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto opacity-90">
                Uzman ekibimiz ile ihtiyaçlarınıza özel çözümler geliştirelim
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo BASE_URL; ?>/iletisim.php" class="bg-white text-red-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-gray-100 transition duration-300 hover-scale">
                    <i class="fas fa-envelope mr-2"></i>İletişim Formu
                </a>
                <a href="tel:<?php echo getSetting('company_phone'); ?>" class="border-2 border-white text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-white hover:text-red-600 transition duration-300 hover-scale">
                    <i class="fas fa-phone mr-2"></i><?php echo getSetting('company_phone'); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
