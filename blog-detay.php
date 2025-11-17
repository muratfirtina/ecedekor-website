<?php
require_once 'includes/config.php';

// URL'den slug'ı al
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ' . BASE_URL . '/blog.php');
    exit;
}

// Blog'u getir
$blog = fetchOne("SELECT * FROM blogs WHERE slug = ? AND status = 'published'", [$slug]);

if (!$blog) {
    header('Location: ' . BASE_URL . '/404.php');
    exit;
}

// Görüntüleme sayısını artır
query("UPDATE blogs SET views = views + 1 WHERE id = ?", [$blog['id']]);

// İlgili blog yazıları
$relatedBlogs = fetchAll("
    SELECT * FROM blogs 
    WHERE status = 'published' AND id != ? 
    ORDER BY RAND() 
    LIMIT 3
", [$blog['id']]);

// SEO için meta bilgileri
$pageTitle = $blog['meta_title'] ?: $blog['title'];
$pageDescription = $blog['meta_description'] ?: substr(strip_tags($blog['excerpt'] ?: $blog['content']), 0, 160);

include 'includes/header.php';
?>

<!-- Schema.org Yapısal Veri -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "<?php echo htmlspecialchars($blog['title']); ?>",
  "image": "<?php echo htmlspecialchars($blog['featured_image'] ?: ''); ?>",
  "datePublished": "<?php echo date('c', strtotime($blog['published_at'])); ?>",
  "dateModified": "<?php echo date('c', strtotime($blog['updated_at'])); ?>",
  "author": {
    "@type": "Organization",
    "name": "<?php echo htmlspecialchars(getSetting('company_name', 'ECEDEKOR')); ?>"
  },
  "publisher": {
    "@type": "Organization",
    "name": "<?php echo htmlspecialchars(getSetting('company_name', 'ECEDEKOR')); ?>",
    "logo": {
      "@type": "ImageObject",
      "url": "<?php echo htmlspecialchars(getSetting('logo_path', IMAGES_URL . '/logo.png')); ?>"
    }
  },
  "description": "<?php echo htmlspecialchars($pageDescription); ?>"
}
</script>

<!-- Hero Section -->
<section class="relative py-20 bg-gradient-to-r from-gray-800 to-gray-900">
    <?php if ($blog['featured_image']): ?>
        <div class="absolute inset-0 opacity-20">
            <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                 alt="<?php echo htmlspecialchars($blog['title']); ?>"
                 class="w-full h-full object-cover">
        </div>
    <?php endif; ?>
    
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center text-white">
            <!-- Breadcrumb -->
            <nav class="flex justify-center mb-6 text-sm">
                <ol class="flex items-center space-x-2 text-gray-300">
                    <li><a href="<?php echo BASE_URL; ?>" class="hover:text-white transition">Ana Sayfa</a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li><a href="<?php echo BASE_URL; ?>/blog.php" class="hover:text-white transition">Blog</a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li class="text-white font-medium">Makale</li>
                </ol>
            </nav>
            
            <h1 class="text-3xl md:text-5xl font-bold mb-6 leading-tight animate-fade-in-up">
                <?php echo htmlspecialchars($blog['title']); ?>
            </h1>
            
            <div class="flex items-center justify-center space-x-6 text-gray-300 animate-fade-in-up" style="animation-delay: 0.1s;">
                <div class="flex items-center">
                    <i class="far fa-calendar-alt mr-2"></i>
                    <?php echo date('d F Y', strtotime($blog['published_at'])); ?>
                </div>
                <div class="flex items-center">
                    <i class="far fa-eye mr-2"></i>
                    <?php echo number_format($blog['views']); ?> görüntüleme
                </div>
                <div class="flex items-center">
                    <i class="far fa-clock mr-2"></i>
                    <?php 
                    $wordCount = str_word_count(strip_tags($blog['content']));
                    $readingTime = ceil($wordCount / 200);
                    echo $readingTime; 
                    ?> dk okuma
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog İçeriği -->
<article class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <!-- Öne Çıkan Görsel -->
        <?php if ($blog['featured_image']): ?>
            <div class="mb-12 rounded-2xl overflow-hidden shadow-2xl mx-auto max-w-[65%]">
                <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($blog['title']); ?>"
                     class="w-full max-h-[500px] object-cover">
            </div>
        <?php endif; ?>
        
        <!-- Blog İçeriği -->
        <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
            <!-- Sosyal Paylaşım -->
            <div class="flex items-center justify-between mb-8 pb-8 border-b border-gray-200">
                <div>
                    <?php if ($blog['focus_keyword']): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-tag mr-2"></i>
                            <?php echo htmlspecialchars($blog['focus_keyword']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-gray-500 text-sm mr-2">Paylaş:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BASE_URL . '/blog-detay/' . $blog['slug']); ?>" 
                       target="_blank"
                       class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-600 text-white hover:bg-blue-700 transition duration-200">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(BASE_URL . '/blog-detay/' . $blog['slug']); ?>&text=<?php echo urlencode($blog['title']); ?>" 
                       target="_blank"
                       class="w-10 h-10 flex items-center justify-center rounded-full bg-sky-500 text-white hover:bg-sky-600 transition duration-200">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(BASE_URL . '/blog-detay/' . $blog['slug']); ?>&title=<?php echo urlencode($blog['title']); ?>" 
                       target="_blank"
                       class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-700 text-white hover:bg-blue-800 transition duration-200">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($blog['title'] . ' - ' . BASE_URL . '/blog-detay/' . $blog['slug']); ?>" 
                       target="_blank"
                       class="w-10 h-10 flex items-center justify-center rounded-full bg-green-500 text-white hover:bg-green-600 transition duration-200">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
            
            <!-- İçerik -->
            <div class="prose prose-lg max-w-none blog-content">
                <?php echo $blog['content']; ?>
            </div>
        </div>
        
        <!-- İlgili Blog Yazıları -->
        <?php if (!empty($relatedBlogs)): ?>
            <div class="mt-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">İlgili Yazılar</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php foreach ($relatedBlogs as $related): ?>
                        <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                            <?php if ($related['featured_image']): ?>
                                <div class="relative h-48 overflow-hidden">
                                    <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $related['slug']; ?>">
                                        <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($related['title']); ?>"
                                             class="w-full h-full object-cover hover:scale-110 transition duration-500">
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-gray-800 mb-3 hover:text-red-600 transition duration-200 line-clamp-2">
                                    <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $related['slug']; ?>">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </a>
                                </h3>
                                
                                <p class="text-gray-600 mb-4 text-sm line-clamp-3">
                                    <?php echo htmlspecialchars(substr(strip_tags($related['excerpt'] ?: $related['content']), 0, 100)); ?>...
                                </p>
                                
                                <a href="<?php echo BASE_URL; ?>/blog-detay/<?php echo $related['slug']; ?>" 
                                   class="inline-flex items-center text-red-600 font-medium hover:text-red-700 transition duration-200 text-sm">
                                    Devamını Oku
                                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Geri Dön Butonu -->
        <div class="mt-12 text-center">
            <a href="<?php echo BASE_URL; ?>/blog.php" 
               class="inline-flex items-center px-8 py-3 bg-gray-100 text-gray-700 rounded-full font-semibold hover:bg-gray-200 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Tüm Blog Yazılarına Dön
            </a>
        </div>
    </div>
</article>

<!-- Blog İçeriği için Özel Stiller -->
<style>
.blog-content {
    color: #374151;
    line-height: 1.8;
}

.blog-content h1,
.blog-content h2,
.blog-content h3,
.blog-content h4,
.blog-content h5,
.blog-content h6 {
    color: #1f2937;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.blog-content h1 { font-size: 2.25rem; }
.blog-content h2 { font-size: 1.875rem; }
.blog-content h3 { font-size: 1.5rem; }
.blog-content h4 { font-size: 1.25rem; }

.blog-content p {
    margin-bottom: 1.5rem;
}

.blog-content a {
    color: #dc2626;
    text-decoration: underline;
    transition: color 0.2s;
}

.blog-content a:hover {
    color: #991b1b;
}

.blog-content img {
    border-radius: 0.75rem;
    margin: 2rem auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    max-width: 100%;
    height: auto;
    display: block;
}

.blog-content ul,
.blog-content ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.blog-content li {
    margin-bottom: 0.5rem;
}

.blog-content blockquote {
    border-left: 4px solid #dc2626;
    padding-left: 1.5rem;
    margin: 2rem 0;
    font-style: italic;
    color: #6b7280;
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 0.5rem;
}

.blog-content code {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
    color: #dc2626;
}

.blog-content pre {
    background: #1f2937;
    color: #f3f4f6;
    padding: 1.5rem;
    border-radius: 0.75rem;
    overflow-x: auto;
    margin: 2rem 0;
}

.blog-content pre code {
    background: transparent;
    color: inherit;
    padding: 0;
}

.blog-content table {
    width: 100%;
    margin: 2rem 0;
    border-collapse: collapse;
}

.blog-content th,
.blog-content td {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    text-align: left;
}

.blog-content th {
    background: #f9fafb;
    font-weight: 600;
}
</style>

<?php include 'includes/footer.php'; ?>
