<?php
require_once 'includes/config.php';

// URL'den kategori slug'ını al
// (Bir önceki yanıttaki daha sağlamlaştırılmış slug alma mantığını kullanıyoruz)
$requestUriPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$basePathConfig = trim(parse_url(BASE_URL, PHP_URL_PATH), '/');

$relevantPath = $requestUriPath;
// BASE_URL'in path kısmını request URI'dan çıkararak göreceli yolu elde et
if (!empty($basePathConfig) && strpos($requestUriPath, $basePathConfig) === 0) {
    $pathAfterBase = substr($requestUriPath, strlen($basePathConfig));
    $relevantPath = trim($pathAfterBase, '/'); // örn: kategori/tp-ahap-tamir-macunlar
}

$categorySlug = '';
// Desen: kategori/SLUG
if (preg_match('/^kategori\/([a-zA-Z0-9-]+)\/?$/', $relevantPath, $matches)) {
    $categorySlug = $matches[1];
} elseif (isset($_GET['slug'])) { // Fallback: GET parametresi olarak slug (örn: kategori.php?slug=...)
    $categorySlug = $_GET['slug'];
}

if (empty($categorySlug)) {
    // error_log("Kategori slug'ı alınamadı. Request URI: {$_SERVER['REQUEST_URI']}, Relevant Path: {$relevantPath}");
    header('Location: ' . BASE_URL . '/urunler.php');
    exit;
}

// Kategori bilgilerini veritabanından çek
$category = fetchOne("SELECT * FROM categories WHERE slug = ? AND is_active = 1", [$categorySlug]);

if (!$category) {
    // error_log("Kategori bulunamadı. Slug: {$categorySlug}");
    header('Location: ' . BASE_URL . '/404.php');
    exit;
}

// SAYFA BAŞLIĞI VE AÇIKLAMASI DOĞRU $category DEĞİŞKENİ İLE AYARLANIYOR
$pageTitle = $category['name'];
$pageDescription = $category['description'] ?: 'Kaliteli ' . $category['name'] . ' ürünlerini keşfedin.';

// Bu kategorideki ürünleri çek (ana ürün kartları için)
$products = fetchAll("
    SELECT p.*,
           (SELECT pv.image FROM product_variants pv WHERE pv.product_id = p.id AND pv.image IS NOT NULL AND pv.image != '' ORDER BY pv.sort_order LIMIT 1) as variant_image,
           (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.id AND pv.is_active = 1) as variant_count
    FROM products p
    WHERE p.category_id = ? AND p.is_active = 1
    ORDER BY p.sort_order, p.name
", [$category['id']]);

// Ürün varyantları için bilgi çek
$productVariants = [];
foreach ($products as $product) {
    $productVariants[$product['id']] = fetchAll("
        SELECT id, name, color, color_code, size, weight, sku, price FROM product_variants 
        WHERE product_id = ? AND is_active = 1 
        ORDER BY sort_order, name
    ", [$product['id']]);
}

// Bu kategorideki tüm varyantları çek (varyant kartları için)
$allVariants = fetchAll("
    SELECT 
        p.id as product_id,
        p.name as product_name,
        p.slug as product_slug,
        p.short_description as product_description,
        p.features as product_features,
        p.main_image as product_main_image,
        pv.id as variant_id,
        pv.name as variant_name,
        pv.color,
        pv.color_code,
        pv.size,
        pv.weight,
        pv.sku,
        pv.price,
        pv.image as variant_image,
        pv.sort_order as variant_sort_order
    FROM products p
    INNER JOIN product_variants pv ON p.id = pv.product_id AND pv.is_active = 1
    WHERE p.category_id = ? AND p.is_active = 1
    ORDER BY p.sort_order, p.name, pv.sort_order, pv.name
", [$category['id']]);

// Toplam öğe sayısı
$totalVariants = 0;
foreach ($products as $p) {
    $totalVariants += $p['variant_count'];
}
$totalItems = count($products) + $totalVariants;

$otherCategories = fetchAll("SELECT * FROM categories WHERE id != ? AND is_active = 1 ORDER BY sort_order", [$category['id']]);

// ---- ÖNEMLİ DÜZELTME ----
// Mevcut kategori bilgilerini, includes/header.php tarafından olası bir üzerine yazılma ihtimaline karşı
// farklı bir değişkende saklayalım. Sayfanın gövdesinde bu değişkeni kullanacağız.
$displayCategory = $category;
// ---- ÖNEMLİ DÜZELTME SONU ----

include 'includes/header.php'; // Bu dosya global $category değişkenini değiştirebilir
?>

<!-- Category Header -->
<section class="relative py-24 bg-gradient-to-r from-red-600 to-black overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <?php if (!empty($displayCategory['image'])): // $displayCategory kullanılıyor 
    ?>
        <div class="absolute inset-0 parallax" style="background-image: url('<?php echo htmlspecialchars($displayCategory['image']); ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-black opacity-80"></div>
    <?php endif; ?>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="animate-on-scroll">
            <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-<?php echo htmlspecialchars($displayCategory['icon'] ?? 'tools'); ?> text-3xl"></i>
            </div>

            <h1 class="text-4xl md:text-6xl font-bold mb-6 text-shadow">
                <?php echo htmlspecialchars($displayCategory['name']); // $displayCategory kullanılıyor 
                ?>
            </h1>

            <?php if (!empty($displayCategory['description'])): // $displayCategory kullanılıyor 
            ?>
                <p class="text-xl md:text-2xl mb-8 max-w-4xl mx-auto text-shadow opacity-90">
                    <?php echo htmlspecialchars($displayCategory['description']); ?>
                </p>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-8 mb-8">
                <div class="flex items-center">
                    <i class="fas fa-box mr-2"></i>
                    <span class="font-semibold"><?php echo count($products); ?> Ürün</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-palette mr-2"></i>
                    <span class="font-semibold"><?php echo count($allVariants); ?> Varyant</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-award mr-2"></i>
                    <span class="font-semibold">Kalite Garantili</span>
                </div>
            </div>

            <nav class="mt-8">
                <ol class="flex items-center justify-center space-x-2 text-sm opacity-90">
                    <li><a href="<?php echo BASE_URL; ?>" class="hover:text-gray-200">Ana Sayfa</a></li>
                    <li><i class="fas fa-chevron-right mx-2 text-xs"></i></li>
                    <li><a href="<?php echo BASE_URL; ?>/urunler.php" class="hover:text-gray-200">Ürünler</a></li>
                    <li><i class="fas fa-chevron-right mx-2 text-xs"></i></li>
                    <li class="text-gray-200"><?php echo htmlspecialchars($displayCategory['name']); // $displayCategory kullanılıyor 
                                                ?></li>
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
                <h3 class="text-xl font-semibold text-black mb-2">Bu kategoride henüz ürün bulunmuyor</h3>
                <p class="text-gray-600 mb-6">Yakında yeni ürünler eklenecek. Diğer kategorileri incelemeyi unutmayın.</p>
                <a href="<?php echo BASE_URL; ?>/urunler.php"
                    class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition duration-300">
                    Tüm Ürünleri Görüntüle
                </a>
            </div>
        <?php else: ?>
            <!-- Category Products Header -->
            <div class="text-center mb-12 animate-on-scroll">
                <h2 class="text-3xl font-bold text-black mb-4">
                    <?php echo htmlspecialchars($displayCategory['name']); // $displayCategory kullanılıyor 
                    ?> Ürünleri
                </h2>
                <p class="text-gray-600 max-w-3xl mx-auto">
                    Bu kategoride <?php echo count($products); ?> ürün ve <?php echo count($allVariants); ?> varyant bulundu. İhtiyaçlarınıza en uygun olanı seçin.
                </p>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">

                <!-- Ana Ürünleri Göster -->
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

                            <!-- Variant Count Badge -->
                            <?php if ($product['variant_count'] > 0): ?>
                                <div class="absolute top-4 right-4">
                                    <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                        <?php echo $product['variant_count']; ?> Varyant
                                    </span>
                                </div>
                            <?php endif; ?>

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
                                <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>"
                                    class="hover:text-red-600 transition duration-300">
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
                                            if ($totalVariants['count'] > 4):
                                            ?>
                                                <span class="text-xs text-gray-500">+<?php echo $totalVariants['count'] - 4; ?></span>
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

                <!-- Varyantları Ayrı Kartlar Olarak Göster -->
                <?php foreach ($allVariants as $variant): ?>
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition duration-300 hover-scale animate-on-scroll border-l-4 border-red-500">
                        <!-- Variant Color/Image -->
                        <div class="relative h-64 bg-gray-200 group">
                            <?php if (!empty($variant['color_code'])): ?>
                                <!-- Renk göstergesi -->
                                <div class="w-full h-full flex items-center justify-center" style="background: <?php echo htmlspecialchars($variant['color_code']); ?>;">
                                    <div class="text-center text-white drop-shadow-lg">
                                        <div class="font-semibold text-lg"><?php echo htmlspecialchars($variant['color']); ?></div>
                                    </div>
                                </div>
                            <?php elseif (!empty($variant['variant_image'])): ?>
                                <!-- Varyant görseli -->
                                <img src="<?php echo htmlspecialchars($variant['variant_image']); ?>"
                                    alt="<?php echo htmlspecialchars($variant['variant_name']); ?>"
                                    class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                            <?php else: ?>
                                <!-- Varsayılan görsel -->
                                <div class="w-full h-full bg-gradient-to-br from-red-300 to-red-400 flex items-center justify-center">
                                    <div class="text-center text-white">
                                        <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-cube text-3xl"></i>
                                        </div>
                                        <div class="font-semibold text-lg"><?php echo htmlspecialchars($variant['variant_name']); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- SKU Badge -->
                            <?php if (!empty($variant['sku'])): ?>
                                <div class="absolute top-4 right-4">
                                    <span class="bg-black bg-opacity-70 text-white px-2 py-1 rounded-full text-xs font-mono">
                                        <?php echo htmlspecialchars($variant['sku']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- Price Badge -->
                            <!-- <?php if (!empty($variant['price'])): ?>
                                <div class="absolute top-4 left-4">
                                    <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                        ₺<?php echo number_format($variant['price'], 2); ?>
                                    </span>
                                </div>
                            <?php endif; ?> -->

                            <!-- Overlay on hover -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <a href="<?php echo BASE_URL; ?>/urun-varyant/<?php echo htmlspecialchars($variant['product_slug']); ?>/<?php echo $variant['variant_id']; ?>"
                                    class="bg-white text-black px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition duration-300 transform translate-y-4 group-hover:translate-y-0">
                                    <i class="fas fa-eye mr-2"></i>İncele
                                </a>
                            </div>
                        </div>

                        <!-- Variant Info -->
                        <div class="p-6">
                            <div class="mb-2 flex items-center">
                                <i class="fas fa-palette text-red-500 mr-2 text-sm"></i>
                                <span class="text-xs text-red-600 uppercase tracking-wide font-semibold">
                                    <?php echo htmlspecialchars($variant['product_name']); ?> Varyantı
                                </span>
                            </div>

                            <h3 class="text-lg font-semibold text-black mb-2">
                                <a href="<?php echo BASE_URL; ?>/urun-varyant/<?php echo htmlspecialchars($variant['product_slug']); ?>/<?php echo $variant['variant_id']; ?>"
                                    class="hover:text-red-600 transition duration-300">
                                    <?php echo htmlspecialchars($variant['variant_name']); ?>
                                </a>
                            </h3>

                            <!-- Variant Properties -->
                            <div class="mb-4 space-y-1">
                                <?php if (!empty($variant['size'])): ?>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-ruler-horizontal mr-2 text-xs"></i>
                                        <span>Boyut: <?php echo htmlspecialchars($variant['size']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($variant['weight'])): ?>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-weight-hanging mr-2 text-xs"></i>
                                        <span><?php echo htmlspecialchars($variant['weight']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($variant['product_description'])): ?>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                    <?php echo htmlspecialchars($variant['product_description']); ?>
                                </p>
                            <?php endif; ?>

                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <a href="<?php echo BASE_URL; ?>/urun-varyant/<?php echo htmlspecialchars($variant['product_slug']); ?>/<?php echo $variant['variant_id']; ?>"
                                    class="bg-red-600 text-white px-5 py-2.5 rounded-lg hover:bg-red-700 transition duration-300 text-sm font-medium shadow hover:shadow-md">
                                    Varyant Detayı
                                </a>
                                <div class="flex space-x-2">
                                    <button class="text-gray-400 hover:text-red-500 transition duration-300 p-2 rounded-full hover:bg-red-50" title="Favorilere Ekle">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <button class="text-gray-400 hover:text-red-500 transition duration-300 p-2 rounded-full hover:bg-red-50" title="Paylaş">
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
                <h2 class="text-3xl font-bold text-black mb-4">Diğer Kategoriler</h2>
                <p class="text-gray-600">Kaliteli ürün gamımızın diğer kategorilerini keşfedin</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach (array_slice($otherCategories, 0, 6) as $otherCategory): ?>
                    <a href="<?php echo BASE_URL; ?>/kategori/<?php echo htmlspecialchars($otherCategory['slug']); ?>"
                        class="group bg-gray-50 rounded-2xl p-6 hover:bg-red-50 transition-all duration-300 ease-in-out shadow-md hover:shadow-lg transform hover:-translate-y-1 animate-on-scroll">
                        <div class="flex items-center mb-4">
                            <?php if (!empty($otherCategory['image'])): ?>
                                <img src="<?php echo htmlspecialchars($otherCategory['image']); ?>"
                                    alt="<?php echo htmlspecialchars($otherCategory['name']); ?>"
                                    class="w-14 h-14 object-cover rounded-lg mr-4 shadow-sm">
                            <?php else: ?>
                                <div class="w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-red-200 transition duration-300">
                                    <i class="fas fa-<?php echo htmlspecialchars($otherCategory['icon'] ?? 'tags'); ?> text-red-600 text-xl"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <h3 class="font-semibold text-black group-hover:text-red-600 transition duration-300">
                                    <?php echo htmlspecialchars($otherCategory['name']); ?>
                                </h3>
                                <?php
                                $categoryProductCountResult = fetchOne("SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_active = 1", [$otherCategory['id']]);
                                $categoryProductCount = $categoryProductCountResult ? $categoryProductCountResult['count'] : 0;
                                ?>
                                <p class="text-sm text-gray-600"><?php echo $categoryProductCount; ?> ürün</p>
                            </div>
                            <i class="fas fa-arrow-right text-gray-400 group-hover:text-red-600 group-hover:translate-x-1 transition-transform duration-300"></i>
                        </div>
                        <?php if (!empty($otherCategory['description'])): ?>
                            <p class="text-sm text-gray-600 line-clamp-2">
                                <?php echo htmlspecialchars($otherCategory['description']); ?>
                            </p>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (count($otherCategories) > 6): ?>
                <div class="text-center mt-12">
                    <a href="<?php echo BASE_URL; ?>/urunler.php"
                        class="bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition duration-300 font-semibold shadow hover:shadow-md">
                        <i class="fas fa-th-large mr-2"></i>Tüm Kategorileri Görüntüle
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-20 bg-gradient-to-r from-red-600 to-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="animate-on-scroll">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                <?php echo htmlspecialchars($displayCategory['name']); // $displayCategory kullanılıyor 
                ?> Hakkında Sorularınız mı Var?
            </h2>
            <p class="text-xl md:text-2xl mb-10 opacity-90 max-w-3xl mx-auto">
                Ürünlerimiz veya hizmetlerimiz hakkında daha fazla bilgi almak, özel bir proje için teklif istemek ya da sadece merhaba demek için bize ulaşın. Uzman ekibimiz size yardımcı olmaktan mutluluk duyacaktır.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo BASE_URL; ?>/iletisim.php"
                    class="bg-white text-red-700 px-8 py-3.5 rounded-lg font-semibold hover:bg-gray-100 transition duration-300 shadow-md text-lg">
                    <i class="fas fa-envelope mr-2"></i>İletişime Geçin
                </a>
                <?php $companyPhone = getSetting('company_phone'); ?>
                <?php if (!empty($companyPhone)): ?>
                    <a href="tel:<?php echo htmlspecialchars(str_replace(' ', '', $companyPhone)); ?>"
                        class="border-2 border-white text-white px-8 py-3.5 rounded-lg font-semibold hover:bg-white hover:text-red-700 transition duration-300 shadow-md text-lg">
                        <i class="fas fa-phone-alt mr-2"></i>Hemen Arayın
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

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

    .parallax {
        background-attachment: fixed;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }

    .text-shadow {
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    }

    .card-shadow {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .animate-on-scroll {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .animate-on-scroll.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const animatedElements = document.querySelectorAll('.animate-on-scroll');
        if (typeof IntersectionObserver === 'function') {
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });
            animatedElements.forEach(el => observer.observe(el));
        } else { // Fallback for older browsers
            animatedElements.forEach(el => el.classList.add('is-visible'));
        }
    });
</script>

<?php include 'includes/footer.php'; ?>