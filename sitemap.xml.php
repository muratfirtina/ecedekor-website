<?php
require_once 'includes/config.php';

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Homepage -->
    <url>
        <loc><?php echo BASE_URL; ?>/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- Static Pages -->
    <url>
        <loc><?php echo BASE_URL; ?>/hakkimizda</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    
    <url>
        <loc><?php echo BASE_URL; ?>/urunler</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    
    <url>
        <loc><?php echo BASE_URL; ?>/iletisim</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    
    <!-- Categories -->
    <?php
    $categories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
    foreach ($categories as $category):
    ?>
    <url>
        <loc><?php echo BASE_URL; ?>/kategori/<?php echo $category['slug']; ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($category['updated_at'])); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- Products -->
    <?php
    $products = fetchAll("
        SELECT p.*, c.slug as category_slug 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1 AND c.is_active = 1 
        ORDER BY p.updated_at DESC
    ");
    foreach ($products as $product):
    ?>
    <url>
        <loc><?php echo BASE_URL; ?>/urun/<?php echo $product['slug']; ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($product['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>
</urlset>
