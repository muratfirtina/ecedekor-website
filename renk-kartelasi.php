<?php
require_once 'includes/config.php';

// Renk kartelası ayarlarını çek
$settings = fetchOne("SELECT * FROM color_palette_settings WHERE id = 1 AND is_active = 1");

if (!$settings) {
    header('Location: ' . BASE_URL);
    exit;
}

// Seçili kategorileri çek
$selectedCategories = fetchAll("
    SELECT cpc.*, c.name, c.slug, c.image, c.description
    FROM color_palette_categories cpc
    JOIN categories c ON cpc.category_id = c.id
    WHERE cpc.is_active = 1
    ORDER BY cpc.sort_order, c.name
");

// Her kategori için ürünleri ve renkleri çek
$categoryData = [];
foreach ($selectedCategories as $category) {
    $categoryId = $category['category_id'];

    // Bu kategorideki ürünleri çek (resimli olanlar)
    $products = fetchAll("
        SELECT p.*,
               (SELECT pv.image FROM product_variants pv
                WHERE pv.product_id = p.id AND pv.image IS NOT NULL AND pv.image != ''
                ORDER BY pv.sort_order LIMIT 1) as variant_image
        FROM products p
        WHERE p.category_id = ? AND p.is_active = 1
          AND (p.image IS NOT NULL AND p.image != ''
               OR EXISTS(SELECT 1 FROM product_variants pv2
                        WHERE pv2.product_id = p.id
                        AND pv2.image IS NOT NULL AND pv2.image != ''))
        ORDER BY p.sort_order, p.name
        LIMIT 8
    ", [$categoryId]);

    // Bu kategorideki tüm benzersiz renkleri çek
    $colors = fetchAll("
        SELECT DISTINCT pv.color, pv.color_code, pv.name
        FROM product_variants pv
        JOIN products p ON pv.product_id = p.id
        WHERE p.category_id = ?
          AND pv.color_code IS NOT NULL
          AND pv.color_code != ''
          AND pv.is_active = 1
        ORDER BY pv.color, pv.name
    ", [$categoryId]);

    $categoryData[] = [
        'info' => $category,
        'products' => $products,
        'colors' => $colors
    ];
}

$pageTitle = $settings['title'] ?? 'Renk Kartelamız';
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-red-600 to-purple-700 text-white overflow-hidden">
    <?php if (!empty($settings['hero_image'])): ?>
        <div class="absolute inset-0">
            <img src="<?php echo BASE_URL . htmlspecialchars($settings['hero_image']); ?>"
                 alt="<?php echo htmlspecialchars($settings['title']); ?>"
                 class="w-full h-full object-cover opacity-30">
        </div>
    <?php endif; ?>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4 drop-shadow-lg">
            <?php echo htmlspecialchars($settings['title']); ?>
        </h1>
        <?php if (!empty($settings['subtitle'])): ?>
            <p class="text-xl md:text-2xl mb-6 drop-shadow">
                <?php echo htmlspecialchars($settings['subtitle']); ?>
            </p>
        <?php endif; ?>
        <?php if (!empty($settings['description'])): ?>
            <p class="text-lg max-w-3xl mx-auto opacity-90">
                <?php echo nl2br(htmlspecialchars($settings['description'])); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Decorative wave -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" class="w-full h-12 md:h-16">
            <path fill="#f9fafb" fill-opacity="1" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z"></path>
        </svg>
    </div>
</section>

<!-- Main Content -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (empty($categoryData)): ?>
            <div class="text-center py-16">
                <i class="fas fa-palette text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">Henüz Renk Kartelası Eklenmemiş</h3>
                <p class="text-gray-500">Yakında renk seçeneklerimizi burada görebileceksiniz.</p>
            </div>
        <?php else: ?>
            <?php foreach ($categoryData as $index => $data): ?>
                <div class="mb-16" id="category-<?php echo $data['info']['category_id']; ?>">
                    <!-- Kategori Başlığı -->
                    <div class="text-center mb-8">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3 flex items-center justify-center">
                            <i class="fas fa-tag text-red-600 mr-3"></i>
                            <?php echo htmlspecialchars($data['info']['name']); ?>
                        </h2>
                        <?php if (!empty($data['info']['description'])): ?>
                            <p class="text-gray-600 max-w-3xl mx-auto">
                                <?php echo htmlspecialchars($data['info']['description']); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Ürün Görselleri -->
                    <?php if (!empty($data['products'])): ?>
                        <div class="mb-10">
                            <h3 class="text-2xl font-semibold text-gray-800 mb-6 text-center">
                                <i class="fas fa-images text-purple-600 mr-2"></i>
                                Ürün Görselleri
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-6">
                                <?php foreach ($data['products'] as $product): ?>
                                    <a href="<?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?>"
                                       class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                                        <div class="aspect-square overflow-hidden bg-gray-100">
                                            <?php if (!empty($product['variant_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($product['variant_image']); ?>"
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                            <?php elseif (!empty($product['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <i class="fas fa-box text-4xl text-gray-300"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="p-4">
                                            <h4 class="font-semibold text-gray-900 text-sm line-clamp-2 group-hover:text-red-600 transition-colors">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </h4>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Renk Kartelası -->
                    <?php if (!empty($data['colors'])): ?>
                        <div class="bg-white rounded-2xl shadow-lg p-8">
                            <h3 class="text-2xl font-semibold text-gray-800 mb-6 text-center">
                                <i class="fas fa-palette text-red-600 mr-2"></i>
                                Renk Seçenekleri (<?php echo count($data['colors']); ?> Renk)
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                                <?php foreach ($data['colors'] as $color): ?>
                                    <div class="group relative bg-gray-50 rounded-xl p-4 hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-red-300">
                                        <!-- Renk Kutusu -->
                                        <?php
                                        $colorCode = strtolower(trim($color['color_code']));
                                        $isWhite = ($colorCode === '#ffffff' || $colorCode === '#fff' || $colorCode === 'white' || $colorCode === '#fefefe' || $colorCode === '#f9f9f9');
                                        ?>
                                        <div class="aspect-square rounded-lg mb-3 shadow-md border-2 <?php echo $isWhite ? 'border-gray-300' : 'border-gray-200'; ?> group-hover:scale-105 transition-transform relative overflow-hidden"
                                             style="background: <?php echo $isWhite ? 'repeating-conic-gradient(#f0f0f0 0% 25%, white 0% 50%) 50% / 20px 20px' : htmlspecialchars($color['color_code']); ?>;">

                                            <!-- Renk Kodu Badge -->
                                            <div class="absolute bottom-1 right-1 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded-md font-mono">
                                                <?php echo htmlspecialchars($color['color_code']); ?>
                                            </div>
                                        </div>

                                        <!-- Renk İsmi -->
                                        <div class="text-center">
                                            <p class="font-semibold text-gray-900 text-sm mb-1 line-clamp-2">
                                                <?php echo htmlspecialchars($color['color'] ?? $color['name']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-3xl mb-2"></i>
                            <p class="text-yellow-800">Bu kategoride henüz renk bilgisi bulunmamaktadır.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Ayırıcı (son kategori hariç) -->
                    <?php if ($index < count($categoryData) - 1): ?>
                        <div class="mt-12 mb-8 border-t-2 border-gray-200"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action -->
<section class="bg-gradient-to-r from-red-600 to-purple-700 text-white py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
            Size En Uygun Rengi Bulun
        </h2>
        <p class="text-xl mb-8 opacity-90">
            Geniş renk seçeneklerimiz ile projelerinize hayat verin
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="<?php echo BASE_URL; ?>/urunler.php"
               class="inline-flex items-center justify-center bg-white text-red-600 font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition duration-300 shadow-lg">
                <i class="fas fa-shopping-bag mr-2"></i>
                Ürünleri İncele
            </a>
            <a href="<?php echo BASE_URL; ?>/iletisim.php"
               class="inline-flex items-center justify-center bg-transparent border-2 border-white text-white font-semibold px-8 py-3 rounded-lg hover:bg-white hover:text-red-600 transition duration-300">
                <i class="fas fa-phone mr-2"></i>
                Bize Ulaşın
            </a>
        </div>
    </div>
</section>

<!-- Quick Navigation (Kategoriler arası hızlı geçiş) -->
<?php if (count($categoryData) > 1): ?>
<section class="bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">Kategorilere Hızlı Geçiş</h3>
        <div class="flex flex-wrap justify-center gap-3">
            <?php foreach ($categoryData as $data): ?>
                <a href="#category-<?php echo $data['info']['category_id']; ?>"
                   class="inline-flex items-center bg-white text-gray-700 hover:bg-red-600 hover:text-white font-medium px-4 py-2 rounded-lg shadow transition duration-200">
                    <i class="fas fa-tag mr-2"></i>
                    <?php echo htmlspecialchars($data['info']['name']); ?>
                    <span class="ml-2 bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">
                        <?php echo count($data['colors']); ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
