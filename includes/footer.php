    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <!-- Main Footer -->
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="lg:col-span-2">
                    <div class="flex items-center mb-4">
                        <img class="h-8 w-auto mr-3" src="<?php echo getSetting('logo_path') ?: (IMAGES_URL . '/logo-white.png'); ?>" alt="<?php echo getSetting('company_name'); ?>" onerror="this.style.display='none'">
                        <!-- <span class="text-2xl font-bold"><?php echo getSetting('company_name', 'ECEDEKOR'); ?></span> -->
                    </div>
                    <p class="text-gray-300 mb-4 leading-relaxed">
                        <?php echo getSetting('site_description', '1998 yılından bu yana mobilya sektöründe kaliteli ürünler üretiyoruz.'); ?>
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-linkedin-in text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
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
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">İletişim</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-blue-400 mt-1 mr-3"></i>
                            <span class="text-gray-300 text-sm"><?php echo getSetting('company_address'); ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-blue-400 mr-3"></i>
                            <a href="tel:<?php echo getSetting('company_phone'); ?>" class="text-gray-300 hover:text-white transition duration-300"><?php echo getSetting('company_phone'); ?></a>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-blue-400 mr-3"></i>
                            <a href="mailto:<?php echo getSetting('company_email'); ?>" class="text-gray-300 hover:text-white transition duration-300"><?php echo getSetting('company_email'); ?></a>
                        </div>
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
                    <p class="text-gray-400 text-sm">
                        © <?php echo date('Y'); ?> <?php echo getSetting('company_name', 'ECEDEKOR'); ?>. Tüm hakları saklıdır.
                    </p>
                    <div class="flex space-x-6 mt-2 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-white text-sm transition duration-300">Gizlilik Politikası</a>
                        <a href="#" class="text-gray-400 hover:text-white text-sm transition duration-300">Kullanım Şartları</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- WhatsApp Floating Button -->
    <div class="fixed bottom-6 right-6 z-50">
        <a href="https://wa.me/905551234567" target="_blank" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition duration-300 flex items-center justify-center group">
            <i class="fab fa-whatsapp text-2xl"></i>
            <span class="absolute right-full mr-3 bg-gray-900 text-white px-3 py-1 rounded text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-300">
                WhatsApp ile iletişim
            </span>
        </a>
    </div>
    
    <!-- Scroll to Top Button -->
    <div x-data="{ showScrollTop: false }" @scroll.window="showScrollTop = window.pageYOffset > 300">
        <button 
            x-show="showScrollTop" 
            x-transition
            @click="window.scrollTo({top: 0, behavior: 'smooth'})"
            class="fixed bottom-6 left-6 bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition duration-300 z-50">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>
    
    <!-- JavaScript -->
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Add animation to elements when they come into view
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe elements with animate class
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
        
        // Lazy loading for images
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('opacity-0');
                    img.classList.add('opacity-100');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    </script>
    
    <!-- Görsel düzeltme scripti -->
    <script src="<?php echo BASE_URL; ?>/assets/js/image-fix.js"></script>
</body>
</html>
