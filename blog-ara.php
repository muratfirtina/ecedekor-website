<?php
require_once 'includes/config.php';

$pageTitle = 'Blog Arama - ' . getSetting('company_name', 'ECEDEKOR');
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($searchQuery)) {
    header('Location: ' . BASE_URL . '/blog');
    exit;
}

$pageDescription = '"' . htmlspecialchars($searchQuery) . '" için blog arama sonuçları';

// Arama sorgusu
$searchTerm = '%' . $searchQuery . '%';
$blogs = fetchAll("
    SELECT * FROM blogs 
    WHERE status = 'published' 
    AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ? OR focus_keyword LIKE ?)
    ORDER BY published_at DESC
", [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);

$resultCount = count($blogs);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-red-600 to-purple-600 text-white py-16">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4 animate-fade-in-up">Arama Sonuçları</h1>
            <p class="text-xl text-white/90 animate-fade-in-up" style="animation-delay: 0.1s;">
                "<span class="font-semibold"><?php echo htmlspecialchars($searchQuery); ?></span>" için 
                <span class="font-bold"><?php echo $resultCount; ?></span> sonuç bulundu
            </p>
        </div>
    </div>
</section>

<div class="container mx-auto px-4 py-12">
    <!-- Arama Formu -->
    <div class="max-w-2xl mx-auto mb-12">
        <form method="GET" class="relative">
            <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                   placeholder="Blog yazılarında ara..." 
                   class="w-full px-6 py-4 pr-24 border-2 border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 focus:border-transparent text-lg">
            <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-red-600 text-white px-6 py-2 rounded-full hover:bg-red-700 transition duration-200">
                <i class="fas fa-search mr-2"></i>Ara
            </button>
        </form>
    </div>

    <?php if (empty($blogs)): ?>
        <!-- Sonuç Bulunamadı -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-600 mb-3">Sonuç Bulunamadı</h3>
                <p class="text-gray-500 mb-6">
                    "<span class="font-semibold"><?php echo htmlspecialchars($searchQuery); ?></span>" 
                    aramanız için hiçbir blog yazısı bulunamadı.
                </p>
                <div class="space-y-3">
                    <p class="text-sm text-gray-600">Öneriler:</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Farklı anahtar kelimeler deneyin</li>
                        <li>• Daha genel terimler kullanın</li>
                        <li>• Yazım hatalarını kontrol edin</li>
                    </ul>
                </div>
                <div class="mt-8">
                    <a href="<?php echo BASE_URL; ?>/blog" 
                       class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-full font-semibold hover:bg-red-700 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Tüm Blog Yazılarına Dön
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Sonuçlar -->
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($blogs as $blog): ?>
                    <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition duration-300 card-shadow">
                        <?php if ($blog['featured_image']): ?>
                            <div class="relative h-48 overflow-hidden">
                                <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $blog['slug']; ?>">
                                    <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                         class="w-full h-full object-cover hover:scale-110 transition duration-500">
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="h-48 bg-gradient-to-br from-red-100 to-purple-100 flex items-center justify-center">
                                <i class="fas fa-blog text-6xl text-red-300"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <div class="flex items-center text-xs text-gray-500 mb-3">
                                <i class="far fa-calendar-alt mr-1"></i>
                                <?php echo date('d.m.Y', strtotime($blog['published_at'])); ?>
                                <span class="mx-2">•</span>
                                <i class="far fa-eye mr-1"></i>
                                <?php echo number_format($blog['views']); ?>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-800 mb-3 hover:text-red-600 transition duration-200 line-clamp-2">
                                <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $blog['slug']; ?>">
                                    <?php 
                                    // Arama terimini vurgula
                                    $title = htmlspecialchars($blog['title']);
                                    $highlightedTitle = str_ireplace(
                                        htmlspecialchars($searchQuery), 
                                        '<mark class="bg-yellow-200 px-1 rounded">' . htmlspecialchars($searchQuery) . '</mark>', 
                                        $title
                                    );
                                    echo $highlightedTitle;
                                    ?>
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 mb-4 text-sm line-clamp-3">
                                <?php 
                                $excerpt = htmlspecialchars(substr(strip_tags($blog['excerpt'] ?: $blog['content']), 0, 120));
                                $highlightedExcerpt = str_ireplace(
                                    htmlspecialchars($searchQuery), 
                                    '<mark class="bg-yellow-200 px-1 rounded">' . htmlspecialchars($searchQuery) . '</mark>', 
                                    $excerpt
                                );
                                echo $highlightedExcerpt;
                                ?>...
                            </p>
                            
                            <?php if ($blog['focus_keyword'] && stripos($blog['focus_keyword'], $searchQuery) !== false): ?>
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-tag mr-1"></i>
                                        <?php echo htmlspecialchars($blog['focus_keyword']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $blog['slug']; ?>" 
                               class="inline-flex items-center text-red-600 font-medium hover:text-red-700 transition duration-200 text-sm">
                                Devamını Oku
                                <i class="fas fa-arrow-right ml-2 text-xs"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Geri Dön -->
            <div class="mt-12 text-center">
                <a href="<?php echo BASE_URL; ?>/blog" 
                   class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-full font-semibold hover:bg-gray-200 transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Tüm Blog Yazılarına Dön
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
