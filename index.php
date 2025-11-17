<?php
require_once 'includes/config.php';

$pageTitle = 'Ana Sayfa';
$pageDescription = 'Ahşap tamir macunları, zemin koruyucu keçeler ve yapışkanlı tapalar konusunda 27+ yıllık deneyimle hizmet veriyoruz.';

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

// Get testimonials
$testimonials = fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order LIMIT 3");

// Get categories
$categories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");

// Get color palette settings for homepage
$colorPaletteSettings = fetchOne("SELECT * FROM color_palette_settings WHERE id = 1 AND is_active = 1 AND show_on_homepage = 1");

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
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Ürün Kategorilerimiz</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Mobilya sektörünün ihtiyaçlarına yönelik kaliteli ürünler sunuyoruz
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($categories as $category): ?>
                <div class="bg-white rounded-2xl overflow-hidden card-shadow hover-scale animate-on-scroll">
                    <div class="h-48 bg-white relative">
                        <?php if ($category['image']): ?>
                            <img src="<?php echo $category['image']; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="w-full h-full object-cover">
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                        <div class="absolute bottom-4 left-4 text-white">
                            <i class="fas fa-tools text-3xl mb-2"></i>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3"><?php echo htmlspecialchars($category['name']); ?></h3>
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
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Öne Çıkan Ürünler</h2>
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
                        <h3 class="text-xl font-semibold text-gray-900 mb-3"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-600 mb-4 text-sm leading-relaxed">
                            <?php echo htmlspecialchars(substr($product['short_description'], 0, 120)) . '...'; ?>
                        </p>
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

<!-- Renk Kartelası Bölümü -->
<?php if ($colorPaletteSettings): ?>
<section class="py-20 bg-gradient-to-br from-red-50 via-purple-50 to-pink-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Sol Taraf - Resim -->
            <div class="animate-on-scroll order-2 lg:order-1">
                <a href="<?php echo BASE_URL; ?>/renk-kartelasi.php" class="block group">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl hover:shadow-3xl transition-all duration-500 transform group-hover:scale-105">
                        <?php if (!empty($colorPaletteSettings['homepage_image'])): ?>
                            <img src="<?php echo BASE_URL . htmlspecialchars($colorPaletteSettings['homepage_image']); ?>"
                                 alt="<?php echo htmlspecialchars($colorPaletteSettings['title']); ?>"
                                 class="w-full h-96 object-cover">
                        <?php else: ?>
                            <!-- Varsayılan renk kartelası görseli -->
                            <div class="w-full h-96 bg-gradient-to-br from-red-400 via-purple-500 to-pink-500 flex items-center justify-center">
                                <div class="text-center text-white">
                                    <i class="fas fa-palette text-8xl mb-4 opacity-80"></i>
                                    <h3 class="text-3xl font-bold">Renk Kartelamız</h3>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-70 transition-opacity duration-500 flex items-end justify-center pb-8">
                            <span class="text-white font-semibold text-xl transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                                <i class="fas fa-arrow-right mr-2"></i>Renk Kartelasını Görüntüle
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Sağ Taraf - İçerik -->
            <div class="animate-on-scroll order-1 lg:order-2">
                <div class="space-y-6">
                    <div class="inline-flex items-center bg-white px-4 py-2 rounded-full shadow-md">
                        <i class="fas fa-swatchbook text-red-600 mr-2"></i>
                        <span class="text-sm font-semibold text-gray-700">Renk Seçenekleri</span>
                    </div>

                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight">
                        <?php echo htmlspecialchars($colorPaletteSettings['title'] ?? 'Renk Kartelamız'); ?>
                    </h2>

                    <?php if (!empty($colorPaletteSettings['subtitle'])): ?>
                        <p class="text-2xl text-gray-700 font-medium">
                            <?php echo htmlspecialchars($colorPaletteSettings['subtitle']); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($colorPaletteSettings['description'])): ?>
                        <p class="text-lg text-gray-600 leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($colorPaletteSettings['description'])); ?>
                        </p>
                    <?php endif; ?>

                    <div class="flex flex-wrap gap-4 pt-4">
                        <a href="<?php echo BASE_URL; ?>/renk-kartelasi.php"
                           class="inline-flex items-center bg-gradient-to-r from-red-600 to-purple-600 text-white px-8 py-4 rounded-full font-semibold text-lg hover:from-red-700 hover:to-purple-700 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <i class="fas fa-palette mr-2"></i>
                            Renk Kartelasını İncele
                        </a>
                        <a href="<?php echo BASE_URL; ?>/urunler.php"
                           class="inline-flex items-center bg-white text-gray-800 px-8 py-4 rounded-full font-semibold text-lg border-2 border-gray-300 hover:border-red-600 hover:text-red-600 transition duration-300 shadow-md hover:shadow-lg">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Ürünleri Gör
                        </a>
                    </div>

                    <!-- Renk Örnekleri -->
                    <div class="pt-6">
                        <div class="flex items-center space-x-3">
                            <div class="flex -space-x-2">
                                <div class="w-12 h-12 rounded-full border-4 border-white shadow-lg" style="background-color: #8B4513;"></div>
                                <div class="w-12 h-12 rounded-full border-4 border-white shadow-lg" style="background-color: #654321;"></div>
                                <div class="w-12 h-12 rounded-full border-4 border-white shadow-lg" style="background-color: #D2691E;"></div>
                                <div class="w-12 h-12 rounded-full border-4 border-white shadow-lg" style="background-color: #F5DEB3;"></div>
                                <div class="w-12 h-12 rounded-full border-4 border-white shadow-lg bg-gradient-to-r from-red-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold">
                                    +
                                </div>
                            </div>
                            <span class="text-sm text-gray-600 font-medium">Ve daha fazlası...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Sertifikalar ve Güvenlik -->
<section class="pt-5 pb-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-green-50 rounded-2xl p-8 animate-on-scroll">
            <div class="text-center mb-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Kalite ve Güvenlik Sertifikalarımız</h3>
                <p class="text-gray-600">Ürünlerimiz uluslararası standartlarda üretilir ve aile dostu formülasyona sahiptir</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mb-8">
                <div class="text-center">
                    <img src="<?php echo IMAGES_URL; ?>/certificates/iso-9001.png" alt="ISO 9001" class="w-20 h-20 mx-auto mb-3">
                    <div class="font-semibold text-gray-900">ISO 9001</div>
                    <div class="text-sm text-gray-600">Kalite Yönetimi</div>
                </div>
                <div class="text-center">
                    <img src="<?php echo IMAGES_URL; ?>/certificates/iso-14001.png" alt="ISO 14001" class="w-20 h-20 mx-auto mb-3">
                    <div class="font-semibold text-gray-900">ISO 14001</div>
                    <div class="text-sm text-gray-600">Çevre Yönetimi</div>
                </div>
                <div class="text-center">
                    <img src="<?php echo IMAGES_URL; ?>/certificates/iso-45001.png" alt="ISO 45001" class="w-20 h-20 mx-auto mb-3">
                    <div class="font-semibold text-gray-900">ISO 45001</div>
                    <div class="text-sm text-gray-600">İş Sağlığı ve Güvenliği Yönetimi</div>
                </div>
                <div class="text-center">
                    <img src="<?php echo IMAGES_URL; ?>/certificates/vegan-friendly.png" alt="Vegan Friendly" class="w-20 h-20 mx-auto mb-3">
                    <div class="font-semibold text-gray-900">Vegan Friendly</div>
                    <div class="text-sm text-gray-600">Hayvan Dostu</div>
                </div>
                <div class="text-center">
                    <img src="<?php echo IMAGES_URL; ?>/certificates/ce-mark.png" alt="CE Belgeli" class="w-20 h-20 mx-auto mb-3">
                    <div class="font-semibold text-gray-900">CE Belgeli</div>
                    <div class="text-sm text-gray-600">Avrupa Uygunluk</div>
                </div>
            </div>

            <!-- Güvenlik Özellikleri -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-baby text-xl text-green-600"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">Çocuk Güvenli</h4>
                    <p class="text-sm text-gray-600">Çocuklar için tamamen güvenli formülasyon</p>
                </div>
                <div class="bg-white rounded-lg p-6 text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-paw text-xl text-blue-600"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">Evcil Hayvan Dostu</h4>
                    <p class="text-sm text-gray-600">Evcil hayvanlar için zararsız içerik</p>
                </div>
                <div class="bg-white rounded-lg p-6 text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-leaf text-xl text-purple-600"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">Alerjen İçermez</h4>
                    <p class="text-sm text-gray-600">Alerji yapıcı madde bulunmaz</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<?php if ($aboutSection): ?>
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="animate-on-scroll">
                    <h2 class="text-4xl font-bold text-gray-900 mb-6"><?php echo htmlspecialchars($aboutSection['title']); ?></h2>
                    <h3 class="text-2xl text-red-600 font-semibold mb-4"><?php echo htmlspecialchars($aboutSection['subtitle']); ?></h3>
                    <p class="text-gray-600 mb-8 leading-relaxed text-lg">
                        <?php echo nl2br(htmlspecialchars($aboutSection['content'])); ?>
                    </p>

                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div class="text-center p-4 bg-white rounded-lg card-shadow">
                            <i class="fas fa-award text-3xl text-red-600 mb-2"></i>
                            <div class="font-semibold text-gray-900">Kalite Garantisi</div>
                            <div class="text-sm text-gray-600">ISO sertifikalı</div>
                        </div>
                        <div class="text-center p-4 bg-white rounded-lg card-shadow">
                            <i class="fas fa-shipping-fast text-3xl text-red-600 mb-2"></i>
                            <div class="font-semibold text-gray-600">Hızlı Teslimat</div>
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
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Neden ECEDEKOR?</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Sektörde 25 yıllık deneyimimizle sizlere en iyi hizmeti sunuyoruz
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div class="text-center animate-on-scroll">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-medal text-3xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Kaliteli Ürünler</h3>
                <p class="text-gray-600 leading-relaxed">
                    ISO standartlarında üretilen ürünlerimiz ile en yüksek kaliteyi garanti ediyoruz.
                </p>
            </div>

            <div class="text-center animate-on-scroll">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-headset text-3xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Müşteri Desteği</h3>
                <p class="text-gray-600 leading-relaxed">
                    7/24 müşteri desteği ile tüm sorularınıza hızlı ve etkili çözümler sunuyoruz.
                </p>
            </div>

            <div class="text-center animate-on-scroll">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-truck text-3xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Hızlı Teslimat</h3>
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
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Müşterilerimiz Ne Diyor?</h2>
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
                                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($testimonial['name']); ?></div>
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