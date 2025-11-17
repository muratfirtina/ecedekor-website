<?php
require_once 'includes/config.php';

$pageTitle = 'Blog - ' . getSetting('company_name', 'ECEDEKOR');
$pageDescription = 'Ahşap ürünler, mobilya bakımı ve dekorasyon hakkında faydalı blog yazılarımızı okuyun.';

// Sayfalama
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Toplam blog sayısı
$totalBlogs = fetchOne("SELECT COUNT(*) as count FROM blogs WHERE status = 'published'")['count'];
$totalPages = ceil($totalBlogs / $perPage);

// Öne çıkan blog
$featuredBlog = fetchOne("
    SELECT * FROM blogs 
    WHERE status = 'published' AND is_featured = 1 
    ORDER BY published_at DESC 
    LIMIT 1
");

// Blogları getir (öne çıkan hariç)
$excludeFeatured = $featuredBlog ? "AND id != " . $featuredBlog['id'] : "";
$blogs = fetchAll("
    SELECT * FROM blogs 
    WHERE status = 'published' $excludeFeatured
    ORDER BY published_at DESC 
    LIMIT $perPage OFFSET $offset
");

// Popüler bloglar (sidebar için)
$popularBlogs = fetchAll("
    SELECT id, title, slug, featured_image, views, published_at 
    FROM blogs 
    WHERE status = 'published' 
    ORDER BY views DESC 
    LIMIT 5
");

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-red-600 to-purple-600 text-white py-20">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 animate-fade-in-up">Blog</h1>
            <p class="text-xl text-white/90 animate-fade-in-up" style="animation-delay: 0.1s;">
                Ahşap ürünler, bakım teknikleri ve dekorasyon önerileri
            </p>
        </div>
    </div>
</section>

<div class="container mx-auto px-4 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Ana İçerik -->
        <div class="lg:col-span-2">
            <?php if ($featuredBlog): ?>
                <!-- Öne Çıkan Blog -->
                <div class="mb-12">
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg p-4 mb-4 inline-block">
                        <span class="text-yellow-700 font-semibold flex items-center">
                            <i class="fas fa-star mr-2"></i>
                            Öne Çıkan Yazı
                        </span>
                    </div>
                    
                    <article class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition duration-300 card-shadow">
                        <?php if ($featuredBlog['featured_image']): ?>
                            <div class="relative h-48 overflow-hidden">
                                <img src="<?php echo htmlspecialchars($featuredBlog['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($featuredBlog['title']); ?>"
                                     class="w-full h-full object-cover hover:scale-105 transition duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-8">
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <i class="far fa-calendar-alt mr-2"></i>
                                <?php echo date('d F Y', strtotime($featuredBlog['published_at'])); ?>
                                <span class="mx-3">•</span>
                                <i class="far fa-eye mr-2"></i>
                                <?php echo number_format($featuredBlog['views']); ?> görüntüleme
                            </div>
                            
                            <h2 class="text-3xl font-bold text-gray-800 mb-4 hover:text-red-600 transition duration-200">
                                <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $featuredBlog['slug']; ?>">
                                    <?php echo htmlspecialchars($featuredBlog['title']); ?>
                                </a>
                            </h2>
                            
                            <p class="text-gray-600 mb-6 text-lg leading-relaxed">
                                <?php echo htmlspecialchars(substr(strip_tags($featuredBlog['excerpt'] ?: $featuredBlog['content']), 0, 200)); ?>...
                            </p>
                            
                            <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $featuredBlog['slug']; ?>" 
                               class="inline-flex items-center text-red-600 font-semibold hover:text-red-700 transition duration-200">
                                Devamını Oku
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </article>
                </div>
            <?php endif; ?>

            <!-- Blog Listesi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
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
                                    <?php echo htmlspecialchars($blog['title']); ?>
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 mb-4 text-sm line-clamp-3">
                                <?php echo htmlspecialchars(substr(strip_tags($blog['excerpt'] ?: $blog['content']), 0, 120)); ?>...
                            </p>
                            
                            <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $blog['slug']; ?>" 
                               class="inline-flex items-center text-red-600 font-medium hover:text-red-700 transition duration-200 text-sm">
                                Devamını Oku
                                <i class="fas fa-arrow-right ml-2 text-xs"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if (empty($blogs) && !$featuredBlog): ?>
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <i class="fas fa-blog text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Henüz Blog Yazısı Yok</h3>
                    <p class="text-gray-500">Yakında ilginizi çekecek içerikler yayınlayacağız.</p>
                </div>
            <?php endif; ?>

            <!-- Sayfalama -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-12 flex justify-center">
                    <nav class="flex items-center space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" 
                               class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="px-4 py-2 rounded-lg transition duration-200 <?php echo $i === $page ? 'bg-red-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" 
                               class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Arama -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Blog Ara</h3>
                <form method="GET" action="<?php echo BASE_URL; ?>/blog-ara.php" class="relative">
                    <input type="text" name="q" placeholder="Arama yapın..." 
                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-600">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <!-- Popüler Yazılar -->
            <?php if (!empty($popularBlogs)): ?>
                <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-fire text-orange-500 mr-2"></i>
                        Popüler Yazılar
                    </h3>
                    <div class="space-y-4">
                        <?php foreach ($popularBlogs as $popular): ?>
                            <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $popular['slug']; ?>" 
                               class="flex items-start space-x-3 group">
                                <?php if ($popular['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($popular['featured_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($popular['title']); ?>"
                                         class="w-20 h-20 rounded-lg object-cover">
                                <?php else: ?>
                                    <div class="w-20 h-20 rounded-lg bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-800 group-hover:text-red-600 transition duration-200 line-clamp-2">
                                        <?php echo htmlspecialchars($popular['title']); ?>
                                    </h4>
                                    <div class="flex items-center text-xs text-gray-500 mt-1">
                                        <i class="far fa-eye mr-1"></i>
                                        <?php echo number_format($popular['views']); ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
