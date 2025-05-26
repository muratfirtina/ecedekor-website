<?php
require_once 'includes/config.php';

$pageTitle = 'İletişim';
$pageDescription = 'ECEDEKOR ile iletişime geçin. Uzman ekibimiz sizin için burada. Telefon, e-posta veya iletişim formumuzu kullanarak bize ulaşabilirsiniz.';

$success = '';
$error = '';
$selectedProduct = $_GET['urun'] ?? '';

// Handle form submission
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $company = sanitizeInput($_POST['company']);
        $subject = sanitizeInput($_POST['subject']);
        $message = sanitizeInput($_POST['message']);
        
        if ($name && $email && $message) {
            // Here you would typically send an email or save to database
            // For now, we'll just show a success message
            $success = 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.';
            
            // You can add email sending logic here
            /*
            $to = getSetting('company_email');
            $email_subject = "İletişim Formu: " . $subject;
            $email_body = "
                Ad Soyad: $name
                E-posta: $email
                Telefon: $phone
                Şirket: $company
                Konu: $subject
                
                Mesaj:
                $message
            ";
            
            mail($to, $email_subject, $email_body);
            */
            
        } else {
            $error = 'Lütfen gerekli alanları doldurun.';
        }
    }
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative py-24 bg-gradient-to-r from-red-600 to-black overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <!-- Hero Background Image -->
    <div class="absolute inset-0 z-0">
        <?php if (getSetting('contact_image')): ?>
            <img src="<?php echo getSetting('contact_image'); ?>" class="w-full h-full object-cover" alt="Hero Background">
            <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-black opacity-80"></div>
        <?php else: ?>
            <div class="w-full h-full bg-gradient-to-r from-red-600 to-black"></div>
        <?php endif; ?>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="animate-on-scroll">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 text-shadow">İletişim</h1>
            <p class="text-xl md:text-2xl mb-8 max-w-4xl mx-auto text-shadow opacity-90">
                Uzman ekibimiz sizin için burada. Her türlü soru ve talebiniz için bize ulaşın.
            </p>
            
            <!-- Quick Contact -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                <a href="tel:<?php echo getSetting('company_phone'); ?>" 
                   class="bg-white text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                    <i class="fas fa-phone mr-2"></i><?php echo getSetting('company_phone'); ?>
                </a>
                <a href="mailto:<?php echo getSetting('company_email'); ?>" 
                   class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-red-600 transition duration-300">
                    <i class="fas fa-envelope mr-2"></i><?php echo getSetting('company_email'); ?>
                </a>
            </div>
            
            <!-- Breadcrumb -->
            <nav class="mt-8">
                <ol class="flex items-center justify-center space-x-2 text-sm opacity-90">
                    <li><a href="<?php echo BASE_URL; ?>" class="hover:text-gray-200">Ana Sayfa</a></li>
                    <li><i class="fas fa-chevron-right mx-2"></i></li>
                    <li class="text-gray-200">İletişim</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Contact Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-8 shadow-lg h-full">
                    <h2 class="text-2xl font-bold text-black mb-8">İletişim Bilgileri</h2>
                    
                    <div class="space-y-6">
                        <!-- Address -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-map-marker-alt text-red-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-black mb-2">Adresimiz</h3>
                                <p class="text-gray-600"><?php echo getSetting('company_address'); ?></p>
                            </div>
                        </div>
                        
                        <!-- Phone -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-phone text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-black mb-2">Telefon</h3>
                                <a href="tel:<?php echo getSetting('company_phone'); ?>" 
                                   class="text-gray-600 hover:text-green-600 transition duration-300">
                                    <?php echo getSetting('company_phone'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-envelope text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-black mb-2">E-posta</h3>
                                <a href="mailto:<?php echo getSetting('company_email'); ?>" 
                                   class="text-gray-600 hover:text-purple-600 transition duration-300">
                                    <?php echo getSetting('company_email'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Working Hours -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-clock text-orange-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-black mb-2">Çalışma Saatleri</h3>
                                <div class="text-gray-600">
                                    <div>Pazartesi - Cuma: 08:00 - 18:00</div>
                                    <div>Cumartesi: 09:00 - 14:00</div>
                                    <div>Pazar: Kapalı</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Media -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h3 class="font-semibold text-black mb-4">Sosyal Medya</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center text-red-600 hover:bg-red-200 transition duration-300">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center text-pink-600 hover:bg-pink-200 transition duration-300">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center text-red-700 hover:bg-red-200 transition duration-300">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://wa.me/905551234567" class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center text-green-600 hover:bg-green-200 transition duration-300">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <h2 class="text-2xl font-bold text-black mb-8">Bize Mesaj Gönderin</h2>
                    
                    <?php if ($success): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                <?php echo $success; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <?php echo $error; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ad Soyad *</label>
                                <input type="text" name="name" id="name" required
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                       placeholder="Adınızı ve soyadınızı girin">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta *</label>
                                <input type="email" name="email" id="email" required
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                       placeholder="E-posta adresinizi girin">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                                <input type="tel" name="phone" id="phone"
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                       placeholder="Telefon numaranızı girin">
                            </div>
                            
                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Şirket</label>
                                <input type="text" name="company" id="company"
                                       value="<?php echo htmlspecialchars($_POST['company'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                       placeholder="Şirket adınızı girin">
                            </div>
                        </div>
                        
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Konu</label>
                            <select name="subject" id="subject"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300">
                                <option value="">Konu seçin</option>
                                <option value="Ürün Bilgisi" <?php echo ($_POST['subject'] ?? '') === 'Ürün Bilgisi' ? 'selected' : ''; ?>>Ürün Bilgisi</option>
                                <option value="Fiyat Teklifi" <?php echo ($_POST['subject'] ?? '') === 'Fiyat Teklifi' ? 'selected' : ''; ?>>Fiyat Teklifi</option>
                                <option value="Teknik Destek" <?php echo ($_POST['subject'] ?? '') === 'Teknik Destek' ? 'selected' : ''; ?>>Teknik Destek</option>
                                <option value="İş Birliği" <?php echo ($_POST['subject'] ?? '') === 'İş Birliği' ? 'selected' : ''; ?>>İş Birliği</option>
                                <option value="Diğer" <?php echo ($_POST['subject'] ?? '') === 'Diğer' ? 'selected' : ''; ?>>Diğer</option>
                            </select>
                        </div>
                        
                        <?php if ($selectedProduct): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">İlgili Ürün</label>
                                <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-lg">
                                    <span class="text-red-800 font-medium"><?php echo htmlspecialchars($selectedProduct); ?></span>
                                </div>
                                <input type="hidden" name="product" value="<?php echo htmlspecialchars($selectedProduct); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Mesajınız *</label>
                            <textarea name="message" id="message" rows="6" required
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                      placeholder="Mesajınızı buraya yazın..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- KVKK Consent -->
                        <div class="flex items-start">
                            <input type="checkbox" name="kvkk_consent" id="kvkk_consent" required
                                   class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1">
                            <label for="kvkk_consent" class="ml-3 text-sm text-gray-600">
                                <a href="#" class="text-red-600 hover:text-red-700">KVKK Aydınlatma Metni</a>'ni okudum ve 
                                kişisel verilerimin işlenmesini kabul ediyorum.
                            </label>
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-red-600 text-white py-3 px-6 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition duration-300 font-semibold">
                            <i class="fas fa-paper-plane mr-2"></i>Mesaj Gönder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-black mb-4">Bizi Ziyaret Edin</h2>
            <p class="text-gray-600">Ofisimize gelerek ürünlerimizi yakından inceleyebilirsiniz</p>
        </div>
        
        <div class="bg-gray-200 rounded-2xl overflow-hidden shadow-lg">
            <!-- Google Maps Embed -->
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3013.0234567890123!2d29.1234567!3d40.9876543!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDHCsDU5JzE1LjYiTiAyOcKwMDcnMjQuNCJF!5e0!3m2!1str!2str!4v1234567890123!5m2!1str!2str"
                width="100%" 
                height="400" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade"
                class="w-full">
            </iframe>
            
            <!-- Overlay with address info -->
            <div class="absolute bottom-4 left-4 bg-white rounded-lg p-4 shadow-lg max-w-sm">
                <h3 class="font-semibold text-black mb-2">ECEDEKOR</h3>
                <p class="text-sm text-gray-600"><?php echo getSetting('company_address'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-black mb-4">Sıkça Sorulan Sorular</h2>
            <p class="text-gray-600">En çok merak edilen sorulara yanıt bulun</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" x-data="{ activeAccordion: null }">
            <div class="space-y-4">
                <!-- FAQ 1 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 1 ? null : 1" 
                            class="w-full px-6 py-4 text-left font-semibold text-black hover:bg-gray-50 transition duration-300 flex items-center justify-between">
                        <span>Ürünlerinizin kalite garantisi var mı?</span>
                        <i class="fas fa-chevron-down transition-transform duration-300" 
                           :class="{ 'rotate-180': activeAccordion === 1 }"></i>
                    </button>
                    <div x-show="activeAccordion === 1" x-transition class="px-6 pb-4">
                        <p class="text-gray-600">Evet, tüm ürünlerimiz kalite garantisi altındadır. ISO standartlarında üretim yapıyoruz ve ürünlerimiz çeşitli kalite testlerinden geçmektedir.</p>
                    </div>
                </div>
                
                <!-- FAQ 2 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 2 ? null : 2" 
                            class="w-full px-6 py-4 text-left font-semibold text-black hover:bg-gray-50 transition duration-300 flex items-center justify-between">
                        <span>Minimum sipariş miktarı var mı?</span>
                        <i class="fas fa-chevron-down transition-transform duration-300" 
                           :class="{ 'rotate-180': activeAccordion === 2 }"></i>
                    </button>
                    <div x-show="activeAccordion === 2" x-transition class="px-6 pb-4">
                        <p class="text-gray-600">Minimum sipariş miktarı ürüne göre değişiklik göstermektedir. Detaylı bilgi için bizimle iletişime geçebilirsiniz.</p>
                    </div>
                </div>
                
                <!-- FAQ 3 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 3 ? null : 3" 
                            class="w-full px-6 py-4 text-left font-semibold text-black hover:bg-gray-50 transition duration-300 flex items-center justify-between">
                        <span>Kargo süresi ne kadar?</span>
                        <i class="fas fa-chevron-down transition-transform duration-300" 
                           :class="{ 'rotate-180': activeAccordion === 3 }"></i>
                    </button>
                    <div x-show="activeAccordion === 3" x-transition class="px-6 pb-4">
                        <p class="text-gray-600">Türkiye genelinde 1-3 iş günü içerisinde kargo teslimatı yapılmaktadır. Stokta bulunan ürünler aynı gün kargoya verilir.</p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4">
                <!-- FAQ 4 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 4 ? null : 4" 
                            class="w-full px-6 py-4 text-left font-semibold text-black hover:bg-gray-50 transition duration-300 flex items-center justify-between">
                        <span>İhracat yapıyor musunuz?</span>
                        <i class="fas fa-chevron-down transition-transform duration-300" 
                           :class="{ 'rotate-180': activeAccordion === 4 }"></i>
                    </button>
                    <div x-show="activeAccordion === 4" x-transition class="px-6 pb-4">
                        <p class="text-gray-600">Evet, 20'den fazla ülkeye ihracat yapmaktayız. İhracat konusunda detaylı bilgi için satış ekibimizle iletişime geçebilirsiniz.</p>
                    </div>
                </div>
                
                <!-- FAQ 5 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 5 ? null : 5" 
                            class="w-full px-6 py-4 text-left font-semibold text-black hover:bg-gray-50 transition duration-300 flex items-center justify-between">
                        <span>Teknik destek sağlıyor musunuz?</span>
                        <i class="fas fa-chevron-down transition-transform duration-300" 
                           :class="{ 'rotate-180': activeAccordion === 5 }"></i>
                    </button>
                    <div x-show="activeAccordion === 5" x-transition class="px-6 pb-4">
                        <p class="text-gray-600">Evet, uzman teknik ekibimiz ürün kullanımı konusunda 7/24 destek sağlamaktadır. Teknik sorularınız için bize ulaşabilirsiniz.</p>
                    </div>
                </div>
                
                <!-- FAQ 6 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="activeAccordion = activeAccordion === 6 ? null : 6" 
                            class="w-full px-6 py-4 text-left font-semibold text-black hover:bg-gray-50 transition duration-300 flex items-center justify-between">
                        <span>Özel renk üretimi yapıyor musunuz?</span>
                        <i class="fas fa-chevron-down transition-transform duration-300" 
                           :class="{ 'rotate-180': activeAccordion === 6 }"></i>
                    </button>
                    <div x-show="activeAccordion === 6" x-transition class="px-6 pb-4">
                        <p class="text-gray-600">Evet, müşterilerimizin talepleri doğrultusunda özel renk ve formül üretimi yapabilmekteyiz. Minimum sipariş miktarları geçerlidir.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
