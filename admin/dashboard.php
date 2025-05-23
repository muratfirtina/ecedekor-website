<?php
require_once '../includes/config.php';

$pageTitle = 'Dashboard';

// Get statistics
$stats = [
    'total_products' => fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1")['count'],
    'total_categories' => fetchOne("SELECT COUNT(*) as count FROM categories WHERE is_active = 1")['count'],
    'total_variants' => fetchOne("SELECT COUNT(*) as count FROM product_variants WHERE is_active = 1")['count'],
    'total_testimonials' => fetchOne("SELECT COUNT(*) as count FROM testimonials WHERE is_active = 1")['count']
];

// Get recent products
$recentProducts = fetchAll("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");

// Get recent testimonials
$recentTestimonials = fetchAll("
    SELECT * FROM testimonials 
    ORDER BY created_at DESC 
    LIMIT 3
");

include 'includes/header.php';
?>

<!-- Dashboard Content -->
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Products -->
        <div class="bg-white rounded-lg shadow-md p-6 card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-2xl text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Toplam Ürün</h3>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total_products']; ?></p>
                </div>
            </div>
            <div class="mt-4">
                <a href="<?php echo ADMIN_URL; ?>/products.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Ürünleri Yönet →
                </a>
            </div>
        </div>
        
        <!-- Total Categories -->
        <div class="bg-white rounded-lg shadow-md p-6 card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tags text-2xl text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Kategoriler</h3>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['total_categories']; ?></p>
                </div>
            </div>
            <div class="mt-4">
                <a href="<?php echo ADMIN_URL; ?>/categories.php" class="text-sm text-green-600 hover:text-green-700 font-medium">
                    Kategorileri Yönet →
                </a>
            </div>
        </div>
        
        <!-- Total Variants -->
        <div class="bg-white rounded-lg shadow-md p-6 card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-palette text-2xl text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Varyantlar</h3>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $stats['total_variants']; ?></p>
                </div>
            </div>
            <div class="mt-4">
                <a href="<?php echo ADMIN_URL; ?>/variants.php" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                    Varyantları Yönet →
                </a>
            </div>
        </div>
        
        <!-- Total Testimonials -->
        <div class="bg-white rounded-lg shadow-md p-6 card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-comments text-2xl text-orange-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Yorumlar</h3>
                    <p class="text-3xl font-bold text-orange-600"><?php echo $stats['total_testimonials']; ?></p>
                </div>
            </div>
            <div class="mt-4">
                <a href="<?php echo ADMIN_URL; ?>/testimonials.php" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                    Yorumları Yönet →
                </a>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Hızlı İşlemler</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="<?php echo ADMIN_URL; ?>/products.php?action=add" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                <i class="fas fa-plus-circle text-3xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Yeni Ürün</span>
            </a>
            
            <a href="<?php echo ADMIN_URL; ?>/categories.php?action=add" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                <i class="fas fa-tag text-3xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Yeni Kategori</span>
            </a>
            
            <a href="<?php echo ADMIN_URL; ?>/testimonials.php?action=add" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                <i class="fas fa-comment-plus text-3xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Yeni Yorum</span>
            </a>
            
            <a href="<?php echo ADMIN_URL; ?>/settings.php" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                <i class="fas fa-cog text-3xl text-gray-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Site Ayarları</span>
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Products -->
        <div class="bg-white rounded-lg shadow-md card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Son Eklenen Ürünler</h2>
                    <a href="<?php echo ADMIN_URL; ?>/products.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Tümünü Gör →
                    </a>
                </div>
            </div>
            <div class="p-6">
                <?php if (empty($recentProducts)): ?>
                    <p class="text-gray-500 text-center py-4">Henüz ürün eklenmemiş.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentProducts as $product): ?>
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                                <div class="flex-shrink-0">
                                    <?php if ($product['main_image']): ?>
                                        <img src="<?php echo $product['main_image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-12 h-12 object-cover rounded-lg">
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo date('d.m.Y', strtotime($product['created_at'])); ?></p>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="<?php echo ADMIN_URL; ?>/products.php?action=edit&id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <span class="text-<?php echo $product['is_active'] ? 'green' : 'red'; ?>-600">
                                        <i class="fas fa-circle text-xs"></i>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Testimonials -->
        <div class="bg-white rounded-lg shadow-md card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Son Müşteri Yorumları</h2>
                    <a href="<?php echo ADMIN_URL; ?>/testimonials.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Tümünü Gör →
                    </a>
                </div>
            </div>
            <div class="p-6">
                <?php if (empty($recentTestimonials)): ?>
                    <p class="text-gray-500 text-center py-4">Henüz yorum eklenmemiş.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentTestimonials as $testimonial): ?>
                            <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <?php if ($testimonial['image']): ?>
                                            <img src="<?php echo $testimonial['image']; ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="w-10 h-10 object-cover rounded-full">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center">
                                            <h3 class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($testimonial['name']); ?></h3>
                                            <div class="ml-2 flex text-yellow-400">
                                                <?php for ($i = 1; $i <= $testimonial['rating']; $i++): ?>
                                                    <i class="fas fa-star text-xs"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <?php if ($testimonial['company']): ?>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($testimonial['company']); ?></p>
                                        <?php endif; ?>
                                        <p class="text-sm text-gray-700 mt-1"><?php echo htmlspecialchars(substr($testimonial['content'], 0, 100)) . '...'; ?></p>
                                        <p class="text-xs text-gray-400 mt-1"><?php echo date('d.m.Y', strtotime($testimonial['created_at'])); ?></p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="<?php echo ADMIN_URL; ?>/testimonials.php?action=edit&id=<?php echo $testimonial['id']; ?>" class="text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <span class="text-<?php echo $testimonial['is_active'] ? 'green' : 'red'; ?>-600">
                                            <i class="fas fa-circle text-xs"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- System Info -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Sistem Bilgileri</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600"><?php echo PHP_VERSION; ?></div>
                <div class="text-sm text-gray-600">PHP Sürümü</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600"><?php echo date('d.m.Y H:i'); ?></div>
                <div class="text-sm text-gray-600">Sistem Saati</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600"><?php echo $_SESSION['admin_username']; ?></div>
                <div class="text-sm text-gray-600">Aktif Kullanıcı</div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
