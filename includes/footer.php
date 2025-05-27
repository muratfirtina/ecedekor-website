<?php
// Bu dosyanın başında config.php'nin include edildiğini varsayıyorum, 
// böylece getSetting() fonksiyonu kullanılabilir.
// require_once __DIR__ . '/config.php'; // Eğer henüz include edilmediyse
?>
    </main> <!-- main content kapanışı -->

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-700 to-slate-600 text-white">
        <!-- Main Footer -->
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="lg:col-span-2">
                    <div class="flex items-center mb-4">
                        <?php
                        $footerLogo = getSetting('footer_logo_path');
                        $mainLogo = getSetting('logo_path'); // Ana logo, footer logosu yoksa kullanılabilir
                        $logoSrc = $footerLogo ?: ($mainLogo ?: (IMAGES_URL . '/logo-white-placeholder.png')); // Varsayılan placeholder
                        $companyName = getSetting('company_name', 'ECEDEKOR');
                        ?>
                        <img class="h-10 w-auto" src="<?php echo htmlspecialchars($logoSrc); ?>" alt="<?php echo htmlspecialchars($companyName); ?> Logosu" onerror="this.style.display='none'">
                    </div>
                    <p class="text-gray-300 mb-4 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars(getSetting('site_description', '1998 yılından bu yana mobilya sektöründe kaliteli ürünler üretiyoruz.'))); ?>
                    </p>
                    <div class="flex space-x-4">
                        <?php if (getSetting('facebook_url')): ?>
                        <a href="<?php echo htmlspecialchars(getSetting('facebook_url')); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-facebook-f text-xl"></i><span class="sr-only">Facebook</span>
                        </a>
                        <?php endif; ?>
                        <?php if (getSetting('instagram_url')): ?>
                        <a href="<?php echo htmlspecialchars(getSetting('instagram_url')); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-instagram text-xl"></i><span class="sr-only">Instagram</span>
                        </a>
                        <?php endif; ?>
                        <?php if (getSetting('linkedin_url')): ?>
                        <a href="<?php echo htmlspecialchars(getSetting('linkedin_url')); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-linkedin-in text-xl"></i><span class="sr-only">LinkedIn</span>
                        </a>
                        <?php endif; ?>
                        <!-- Diğer sosyal medya ikonları buraya eklenebilir -->
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Hızlı Linkler</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo BASE_URL; ?>" class="text-gray-300 hover:text-white transition duration-300">Ana Sayfa</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/hakkimizda.php" class="text-gray-300 hover:text-white transition duration-300">Hakkımızda</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/urunler.php" class="text-gray-300 hover:text-white transition duration-300">Ürünler</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/iletisim.php" class="text-gray-300 hover:text-white transition duration-300">İletişim</a></li>
                        <!-- İhtiyaç duyulursa daha fazla link eklenebilir -->
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">İletişim</h3>
                    <div class="space-y-3">
                        <?php if (getSetting('company_address')): ?>
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-red-400 mt-1 mr-3 shrink-0"></i>
                            <span class="text-gray-300 text-sm"><?php echo nl2br(htmlspecialchars(getSetting('company_address'))); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (getSetting('company_phone')): ?>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-red-400 mr-3 shrink-0"></i>
                            <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', getSetting('company_phone')); ?>" class="text-gray-300 hover:text-white transition duration-300"><?php echo htmlspecialchars(getSetting('company_phone')); ?></a>
                        </div>
                        <?php endif; ?>
                        <?php if (getSetting('company_mobile')): ?>
                        <div class="flex items-center">
                            <i class="fas fa-mobile-alt text-red-400 mr-3 shrink-0"></i>
                            <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', getSetting('company_mobile')); ?>" class="text-gray-300 hover:text-white transition duration-300"><?php echo htmlspecialchars(getSetting('company_mobile')); ?></a>
                        </div>
                        <?php endif; ?>
                        <?php if (getSetting('company_email')): ?>
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-red-400 mr-3 shrink-0"></i>
                            <a href="mailto:<?php echo htmlspecialchars(getSetting('company_email')); ?>" class="text-gray-300 hover:text-white transition duration-300"><?php echo htmlspecialchars(getSetting('company_email')); ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Product Categories -->
            <div class="mt-8 pt-8 border-t border-gray-800">
                <h3 class="text-lg font-semibold mb-4">Ürün Kategorilerimiz</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php
                    $footerCategories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order LIMIT 4");
                    foreach ($footerCategories as $category): ?>
                        <a href="<?php echo BASE_URL; ?>/kategori/<?php echo $category['slug']; ?>" class="text-gray-300 hover:text-white transition duration-300 text-sm">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-400 text-sm text-center md:text-left flex items-center">
                        
                        <p class="mb-0"> <!-- mb-1'i kaldırdım veya mb-0 yaptım -->
                            © <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>. Tüm hakları saklıdır.
                        </p>
                    </div>
                    <?php $parentCompany = getSetting('parent_company'); ?>
                    <?php if ($parentCompany): ?>
                        <p class="text-xs text-gray-500 mt-1 md:mt-0 text-center md:text-left">
                            
                            <?php echo htmlspecialchars($companyName); ?> bir  
                            <?php
                        $footerBottomLogo = getSetting('footer_bottom_logo_path');
                        if ($footerBottomLogo):
                        ?>
                            <img src="<?php echo htmlspecialchars($footerBottomLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?> Alt Logo" class="h-12 w-auto mr-2 inline-block">
                        <?php endif; ?>
                             <?php echo htmlspecialchars($parentCompany); ?> iştirakidir.
                        </p>
                    <?php endif; ?>
                    <div class="flex space-x-6 mt-2 md:mt-0">
                        <a href="<?php echo BASE_URL . '/gizlilik-politikasi.php'; // Gerçek linkleri ekleyin ?>" class="text-gray-400 hover:text-white text-sm transition duration-300">Gizlilik Politikası</a>
                        <a href="<?php echo BASE_URL . '/kullanim-sartlari.php'; // Gerçek linkleri ekleyin ?>" class="text-gray-400 hover:text-white text-sm transition duration-300">Kullanım Şartları</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <?php $whatsappNumber = getSetting('whatsapp_number'); ?>
    <?php if ($whatsappNumber): ?>
        <div class="fixed bottom-6 right-6 z-50">
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsappNumber); ?>" target="_blank" rel="noopener noreferrer" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition duration-300 flex items-center justify-center group">
                <i class="fab fa-whatsapp text-2xl"></i>
                <span class="absolute right-full mr-3 bg-black text-white px-3 py-1 rounded text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-300 pointer-events-none">
                    WhatsApp ile İletişim
                </span>
            </a>
        </div>
    <?php endif; ?>

    <!-- Scroll to Top Button -->
    <div x-data="{ showScrollTop: false }" @scroll.window="showScrollTop = window.pageYOffset > 300" x-cloak>
        <button
            x-show="showScrollTop"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            @click="window.scrollTo({top: 0, behavior: 'smooth'})"
            class="fixed bottom-6 left-6 bg-red-600 hover:bg-red-700 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition duration-300 z-50"
            aria-label="Sayfa Başına Git">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>

    <!-- JavaScripts -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <?php
    $customJS = getSetting('custom_js');
    if ($customJS) {
        echo "<!-- Custom JS -->\n<script>\n" . $customJS . "\n</script>\n<!-- End Custom JS -->\n";
    }
    ?>
    <!-- Görsel düzeltme ve genel scriptler -->
    <!-- <script src="<?php //echo ASSETS_URL; ?>/js/image-fix.js"></script> -->
    <!-- <script src="<?php //echo ASSETS_URL; ?>/js/main.js"></script> -->
    </body>
</html>