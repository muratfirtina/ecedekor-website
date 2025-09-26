<?php
require_once 'includes/config.php';

$pageTitle = 'İletişim';
$pageDescription = getSetting('site_description_contact', 'ECEDEKOR ile iletişime geçin. Uzman ekibimiz sizin için burada. Telefon, e-posta veya iletişim formumuzu kullanarak bize ulaşabilirsiniz.'); // Ayarlardan çekilebilir

$success = '';
$error = '';
$form_data = []; // Form tekrar doldurulduğunda verileri tutmak için
$selectedProduct = sanitizeInput($_GET['urun'] ?? '');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $form_data = $_POST; // Hata durumunda formu tekrar doldurmak için

    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? null);
        $company = sanitizeInput($_POST['company'] ?? null);
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $message_content = sanitizeInput($_POST['message'] ?? ''); // message'ı message_content olarak değiştirdim, DB'deki sütunla karışmasın diye
        $product_info = sanitizeInput($_POST['product'] ?? $selectedProduct); // hidden input veya GET'ten gelen
        $kvkk_consent = isset($_POST['kvkk_consent']);

        // Temel Doğrulamalar
        if (empty($name)) {
            $error .= 'Ad Soyad alanı zorunludur.<br>';
        }
        if (empty($email)) {
            $error .= 'E-posta alanı zorunludur.<br>';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error .= 'Geçerli bir e-posta adresi giriniz.<br>';
        }
        if (empty($message_content)) {
            $error .= 'Mesaj alanı zorunludur.<br>';
        }
        if (!$kvkk_consent) {
            $error .= 'KVKK Aydınlatma Metni\'ni onaylamanız gerekmektedir.<br>';
        }

        if (empty($error)) {
            try {
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];

                $sql = "INSERT INTO contact_messages (name, email, phone, company, subject, message, product_info, ip_address, user_agent, created_at) 
                        VALUES (:name, :email, :phone, :company, :subject, :message, :product_info, :ip_address, :user_agent, NOW())";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
                $stmt->bindParam(':company', $company, PDO::PARAM_STR);
                $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
                $stmt->bindParam(':message', $message_content, PDO::PARAM_STR);
                $stmt->bindParam(':product_info', $product_info, PDO::PARAM_STR);
                $stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
                $stmt->bindParam(':user_agent', $user_agent, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $success = 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.';
                    $form_data = []; // Başarılı gönderim sonrası formu temizle

                    // İsteğe bağlı: Admin'e e-posta bildirimi
                    $admin_email = getSetting('company_email'); // Veya farklı bir admin e-postası
                    if ($admin_email) {
                        $email_subject = "Yeni İletişim Formu Mesajı: " . ($subject ?: 'Belirtilmemiş Konu');
                        $email_body = "Web sitenizden yeni bir iletişim formu mesajı aldınız:\n\n";
                        $email_body .= "Ad Soyad: $name\n";
                        $email_body .= "E-posta: $email\n";
                        if ($phone) $email_body .= "Telefon: $phone\n";
                        if ($company) $email_body .= "Şirket: $company\n";
                        if ($product_info) $email_body .= "İlgili Ürün: $product_info\n";
                        $email_body .= "Konu: $subject\n\n";
                        $email_body .= "Mesaj:\n$message_content\n\n";
                        $email_body .= "IP Adresi: $ip_address\n";
                        $email_body .= "Gönderim Tarihi: " . date('d.m.Y H:i:s') . "\n\n";
                        $email_body .= "Mesajı görüntülemek için admin panelini ziyaret edin.";

                        $headers = "From: " . getSetting('site_title', 'Web Siteniz') . " <noreply@" . ($_SERVER['SERVER_NAME'] ?? 'example.com') . ">\r\n";
                        $headers .= "Reply-To: $email\r\n";
                        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                        
                        // mail($admin_email, $email_subject, $email_body, $headers);
                        // mail() fonksiyonu sunucu yapılandırmasına bağlıdır. PHPMailer gibi bir kütüphane daha güvenilirdir.
                    }

                } else {
                    $error = 'Mesajınız gönderilirken bir sorun oluştu. Lütfen daha sonra tekrar deneyin.';
                    error_log("İletişim formu kaydetme hatası: " . implode(", ", $stmt->errorInfo()));
                }

            } catch (PDOException $e) {
                $error = 'Veritabanı hatası. Lütfen daha sonra tekrar deneyin.';
                error_log("PDOException - İletişim Formu: " . $e->getMessage());
            }
        }
    }
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative py-24 bg-gradient-to-r from-red-600 to-black overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <div class="absolute inset-0 z-0">
        <?php
        $contactHeroImage = getSetting('contact_image');
        $defaultContactHeroImage = IMAGES_URL  ;
        $heroImageSrc = $contactHeroImage ?: $defaultContactHeroImage;
        ?>
        <img src="<?php echo htmlspecialchars($heroImageSrc); ?>" class="w-full h-full object-cover" alt="İletişim ECEDEKOR" onerror="this.src='<?php echo htmlspecialchars($defaultContactHeroImage); ?>'; this.classList.add('bg-gradient-to-r', 'from-red-600', 'to-black');">
        <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-black opacity-80"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="animate-on-scroll">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 text-shadow">İletişim</h1>
            <p class="text-xl md:text-2xl mb-8 max-w-4xl mx-auto text-shadow opacity-90">
                Uzman ekibimiz sizin için burada. Her türlü soru ve talebiniz için bize ulaşın.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                <?php if (getSetting('company_phone')): ?>
                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', getSetting('company_phone')); ?>"
                    class="bg-white text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                    <i class="fas fa-phone mr-2"></i><?php echo htmlspecialchars(getSetting('company_phone')); ?>
                </a>
                <?php endif; ?>
                <?php if (getSetting('company_email')): ?>
                <a href="mailto:<?php echo htmlspecialchars(getSetting('company_email')); ?>"
                    class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-red-600 transition duration-300">
                    <i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars(getSetting('company_email')); ?>
                </a>
                <?php endif; ?>
            </div>
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
<section class="py-20 bg-gray-50" x-data="{ kvkkModalOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Contact Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-8 shadow-lg h-full">
                    <h2 class="text-2xl font-bold text-black mb-8">İletişim Bilgileri</h2>
                    <div class="space-y-6">
                        <?php if (getSetting('company_address')): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-map-marker-alt text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-black mb-1">Adresimiz</h3>
                                <p class="text-gray-600 text-sm"><?php echo nl2br(htmlspecialchars(getSetting('company_address'))); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (getSetting('company_phone') || getSetting('company_mobile')): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-phone-alt text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-black mb-1">Telefon</h3>
                                <?php if (getSetting('company_phone')): ?>
                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', getSetting('company_phone')); ?>" class="text-gray-600 hover:text-green-600 transition duration-300 block text-sm">
                                    <?php echo htmlspecialchars(getSetting('company_phone')); ?> (Sabit)
                                </a>
                                <?php endif; ?>
                                <?php if (getSetting('company_mobile')): ?>
                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', getSetting('company_mobile')); ?>" class="text-gray-600 hover:text-green-600 transition duration-300 block text-sm <?php if(getSetting('company_phone')) echo 'mt-1'; ?>">
                                    <i class="fas fa-mobile-alt mr-1"></i><?php echo htmlspecialchars(getSetting('company_mobile')); ?> (Mobil)
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (getSetting('company_email')): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-envelope text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-black mb-1">E-posta</h3>
                                <a href="mailto:<?php echo htmlspecialchars(getSetting('company_email')); ?>" class="text-gray-600 hover:text-purple-600 transition duration-300 text-sm">
                                    <?php echo htmlspecialchars(getSetting('company_email')); ?>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-clock text-orange-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-black mb-1">Çalışma Saatleri</h3>
                                <div class="text-gray-600 text-sm">
                                    <div>Pazartesi - Cuma: 08:00 - 18:00</div>
                                    <div>Cumartesi: 09:00 - 14:00</div>
                                    <div>Pazar: Kapalı</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h3 class="font-semibold text-black mb-4">Bizi Takip Edin</h3>
                        <div class="flex space-x-3">
                            <?php if(getSetting('facebook_url')): ?><a href="<?php echo htmlspecialchars(getSetting('facebook_url')); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 hover:bg-blue-200 transition duration-300" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                            <?php if(getSetting('instagram_url')): ?><a href="<?php echo htmlspecialchars(getSetting('instagram_url')); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center text-pink-600 hover:bg-pink-200 transition duration-300" aria-label="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
                            <?php if(getSetting('linkedin_url')): ?><a href="<?php echo htmlspecialchars(getSetting('linkedin_url')); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-700 hover:bg-blue-200 transition duration-300" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                            <?php if(getSetting('whatsapp_number')): ?><a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', getSetting('whatsapp_number')); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center text-green-600 hover:bg-green-200 transition duration-300" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <h2 class="text-2xl font-bold text-black mb-8">Bize Mesaj Gönderin</h2>
                    <?php if ($success): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
                            <div class="flex">
                                <div class="py-1"><i class="fas fa-check-circle fa-lg mr-3"></i></div>
                                <div>
                                    <p class="font-bold">Başarılı!</p>
                                    <p class="text-sm"><?php echo htmlspecialchars($success); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                            <div class="flex">
                                <div class="py-1"><i class="fas fa-exclamation-triangle fa-lg mr-3"></i></div>
                                <div>
                                    <p class="font-bold">Hata!</p>
                                    <div class="text-sm"><?php echo $error; /* HTML içerdiği için htmlspecialchars yok */ ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php if($selectedProduct) echo '?urun=' . urlencode($selectedProduct); ?>" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ad Soyad <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" required
                                    value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                    placeholder="Adınızı ve soyadınızı girin">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta <span class="text-red-500">*</span></label>
                                <input type="email" name="email" id="email" required
                                    value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                    placeholder="E-posta adresinizi girin">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                                <input type="tel" name="phone" id="phone"
                                    value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                    placeholder="Telefon numaranızı girin">
                            </div>
                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Şirket</label>
                                <input type="text" name="company" id="company"
                                    value="<?php echo htmlspecialchars($form_data['company'] ?? ''); ?>"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                    placeholder="Şirket adınızı girin">
                            </div>
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Konu</label>
                            <select name="subject" id="subject"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300 bg-white">
                                <option value="">Konu seçin...</option>
                                <option value="Ürün Bilgisi" <?php echo (($form_data['subject'] ?? '') === 'Ürün Bilgisi') ? 'selected' : ''; ?>>Ürün Bilgisi</option>
                                <option value="Fiyat Teklifi" <?php echo (($form_data['subject'] ?? '') === 'Fiyat Teklifi') ? 'selected' : ''; ?>>Fiyat Teklifi</option>
                                <option value="Teknik Destek" <?php echo (($form_data['subject'] ?? '') === 'Teknik Destek') ? 'selected' : ''; ?>>Teknik Destek</option>
                                <option value="İş Birliği" <?php echo (($form_data['subject'] ?? '') === 'İş Birliği') ? 'selected' : ''; ?>>İş Birliği</option>
                                <option value="Diğer" <?php echo (($form_data['subject'] ?? '') === 'Diğer') ? 'selected' : ''; ?>>Diğer</option>
                            </select>
                        </div>
                        <?php if ($selectedProduct): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">İlgilendiğiniz Ürün</label>
                                <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-lg">
                                    <span class="text-red-800 font-medium"><?php echo htmlspecialchars($selectedProduct); ?></span>
                                </div>
                                <input type="hidden" name="product" value="<?php echo htmlspecialchars($selectedProduct); ?>">
                            </div>
                        <?php endif; ?>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Mesajınız <span class="text-red-500">*</span></label>
                            <textarea name="message" id="message" rows="6" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                placeholder="Mesajınızı buraya yazın..."><?php echo htmlspecialchars($form_data['message'] ?? ''); ?></textarea>
                        </div>
                        <div class="flex items-start">
                            <input type="checkbox" name="kvkk_consent" id="kvkk_consent" required
                                class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1"
                                <?php echo isset($form_data['kvkk_consent']) ? 'checked' : ''; ?>>
                            <label for="kvkk_consent" class="ml-3 text-sm text-gray-600">
                                <button type="button" @click="kvkkModalOpen = true" class="text-red-600 hover:text-red-700 underline focus:outline-none">KVKK Aydınlatma Metni</button>'ni okudum, anladım ve kişisel verilerimin işlenmesini kabul ediyorum. <span class="text-red-500">*</span>
                            </label>
                        </div>
                        <button type="submit"
                            class="w-full bg-red-600 text-white py-3 px-6 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300 transition duration-300 font-semibold">
                            <i class="fas fa-paper-plane mr-2"></i>Mesajı Gönder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- KVKK Modal -->
    <div x-show="kvkkModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center"
        aria-labelledby="kvkk-modal-title" role="dialog" aria-modal="true">
        <div x-show="kvkkModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <div x-show="kvkkModalOpen"
            @click.away="kvkkModalOpen = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-3xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-shield-alt text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="kvkk-modal-title">
                            Kişisel Verilerin Korunması Kanunu (KVKK) Aydınlatma Metni
                        </h3>
                        <div class="mt-4 text-sm text-gray-600 max-h-96 overflow-y-auto space-y-3 pr-2">
                            <p><strong>Veri Sorumlusu:</strong> <?php echo htmlspecialchars(getSetting('company_name', 'ECEDEKOR')); ?> ("Şirket")</p>
                            <p>Bu aydınlatma metni, 6698 sayılı Kişisel Verilerin Korunması Kanunu (“KVKK”)’nun 10. maddesi ile Aydınlatma Yükümlülüğünün Yerine Getirilmesinde Uyulacak Usul ve Esaslar Hakkında Tebliğ kapsamında veri sorumlusu sıfatıyla Şirketimiz tarafından hazırlanmıştır.</p>
                            
                            <p><strong>Kişisel Verilerin İşlenme Amaçları:</strong></p>
                            <p>İletişim formu aracılığıyla topladığımız adınız, soyadınız, e-posta adresiniz, telefon numaranız (isteğe bağlı), şirket bilgileriniz (isteğe bağlı), konu ve mesaj içeriğiniz aşağıdaki amaçlarla işlenecektir:</p>
                            <ul class="list-disc list-inside ml-4">
                                <li>Talep, soru ve şikayetlerinizin alınması, değerlendirilmesi ve yanıtlanması,</li>
                                <li>Sizinle iletişim kurulması,</li>
                                <li>Ürün ve hizmetlerimiz hakkında bilgi verilmesi (talep etmeniz halinde),</li>
                                <li>Fiyat teklifi sunulması (talep etmeniz halinde),</li>
                                <li>Teknik destek sağlanması (talep etmeniz halinde),</li>
                                <li>İş birliği olanaklarının değerlendirilmesi,</li>
                                <li>Hizmet kalitemizin artırılması ve müşteri memnuniyetinin sağlanması,</li>
                                <li>Yasal yükümlülüklerimizin yerine getirilmesi.</li>
                            </ul>

                            <p><strong>Kişisel Verilerin Toplanma Yöntemi ve Hukuki Sebebi:</strong></p>
                            <p>Kişisel verileriniz, web sitemizde yer alan iletişim formunu doldurmanız suretiyle elektronik ortamda otomatik olarak toplanmaktadır. Bu kişisel veriler, KVKK’nın 5. maddesinde belirtilen “ilgili kişinin temel hak ve özgürlüklerine zarar vermemek kaydıyla, veri sorumlusunun meşru menfaatleri için veri işlenmesinin zorunlu olması” ve “bir sözleşmenin kurulması veya ifasıyla doğrudan doğruya ilgili olması kaydıyla, sözleşmenin taraflarına ait kişisel verilerin işlenmesinin gerekli olması” hukuki sebeplerine dayanılarak işlenmektedir.</p>

                            <p><strong>Kişisel Verilerin Aktarılması:</strong></p>
                            <p>Kişisel verileriniz, yukarıda belirtilen amaçlar doğrultusunda ve KVKK’nın 8. ve 9. maddelerinde belirtilen kişisel veri işleme şartları ve amaçları çerçevesinde, yasal zorunluluklar gereği yetkili kamu kurum ve kuruluşlarına, faaliyetlerimizi yürütmek üzere hizmet aldığımız, iş birliği yaptığımız yurt içi ve/veya yurt dışındaki iş ortaklarımıza ve tedarikçilerimize (örneğin, e-posta gönderim hizmeti sağlayıcıları, sunucu/hosting hizmeti sağlayıcıları) aktarılabilecektir.</p>

                            <p><strong>Kişisel Veri Sahibinin KVKK Kapsamındaki Hakları:</strong></p>
                            <p>KVKK’nın 11. maddesi uyarınca aşağıdaki haklara sahipsiniz:</p>
                            <ul class="list-disc list-inside ml-4">
                                <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme,</li>
                                <li>Kişisel verileriniz işlenmişse buna ilişkin bilgi talep etme,</li>
                                <li>Kişisel verilerinizin işlenme amacını ve bunların amacına uygun kullanılıp kullanılmadığını öğrenme,</li>
                                <li>Yurt içinde veya yurt dışında kişisel verilerinizin aktarıldığı üçüncü kişileri bilme,</li>
                                <li>Kişisel verilerinizin eksik veya yanlış işlenmiş olması hâlinde bunların düzeltilmesini isteme,</li>
                                <li>KVKK’nın 7. maddesinde öngörülen şartlar çerçevesinde kişisel verilerinizin silinmesini veya yok edilmesini isteme,</li>
                                <li>(d) ve (e) bentleri uyarınca yapılan işlemlerin, kişisel verilerinizin aktarıldığı üçüncü kişilere bildirilmesini isteme,</li>
                                <li>İşlenen verilerinizin münhasıran otomatik sistemler vasıtasıyla analiz edilmesi suretiyle aleyhinize bir sonucun ortaya çıkmasına itiraz etme,</li>
                                <li>Kişisel verilerinizin kanuna aykırı olarak işlenmesi sebebiyle zarara uğramanız hâlinde zararınızın giderilmesini talep etme.</li>
                            </ul>
                            <p>Yukarıda belirtilen haklarınızı kullanmak için taleplerinizi, yazılı olarak veya Kişisel Verileri Koruma Kurulu’nun belirlediği diğer yöntemlerle Şirketimizin <?php echo htmlspecialchars(getSetting('company_address')); ?> adresine veya <?php echo htmlspecialchars(getSetting('company_email')); ?> e-posta adresine iletebilirsiniz.</p>
                            <p>Şirketimiz, talebin niteliğine göre talebi en kısa sürede ve en geç otuz (30) gün içinde ücretsiz olarak sonuçlandıracaktır. Ancak, işlemin ayrıca bir maliyeti gerektirmesi hâlinde, Kişisel Verileri Koruma Kurulu tarafından belirlenen tarifedeki ücret alınabilir.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" @click="kvkkModalOpen = false"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Anladım, Kapat
                </button>
            </div>
        </div>
    </div>
    <!-- End KVKK Modal -->
</section>

<!-- Map Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-black mb-4">Bizi Ziyaret Edin</h2>
            <p class="text-gray-600">Ofisimize gelerek ürünlerimizi yakından inceleyebilirsiniz.</p>
        </div>
        <div class="bg-gray-200 rounded-2xl overflow-hidden shadow-lg">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3014.811249990341!2d29.15755877669217!3d40.91988427136317!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cac56b492b8369%3A0xba0c02063ba5ef01!2zRWNlZGVrb3IgLSBIYXphciBEZcSfZXJsaSBUYcWfbGFyIHZlIMSwbsWfLiBMdGQuIMWfdGku!5e0!3m2!1str!2str!4v1748906296994!5m2!1str!2str" 
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<!-- FAQ Section (Alpine.js ile) -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-black mb-4">Sıkça Sorulan Sorular</h2>
            <p class="text-gray-600">En çok merak edilen sorulara yanıt bulun.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8" x-data="{ openAccordion: null }">
            <?php
            $faqs = [
                1 => ["title" => "Ürünlerinizin kalite garantisi var mı?", "answer" => "Evet, tüm ürünlerimiz kalite garantisi altındadır. ISO standartlarında üretim yapıyoruz ve ürünlerimiz çeşitli kalite testlerinden geçmektedir."],
                2 => ["title" => "Minimum sipariş miktarı var mı?", "answer" => "Minimum sipariş miktarı ürüne göre değişiklik göstermektedir. Detaylı bilgi için bizimle iletişime geçebilirsiniz."],
                3 => ["title" => "Kargo süresi ne kadar?", "answer" => "Türkiye genelinde 1-3 iş günü içerisinde kargo teslimatı yapılmaktadır. Stokta bulunan ürünler aynı gün kargoya verilir."],
                4 => ["title" => "İhracat yapıyor musunuz?", "answer" => "Evet, 20'den fazla ülkeye ihracat yapmaktayız. İhracat konusunda detaylı bilgi için satış ekibimizle iletişime geçebilirsiniz."],
                5 => ["title" => "Teknik destek sağlıyor musunuz?", "answer" => "Evet, uzman teknik ekibimiz ürün kullanımı konusunda destek sağlamaktadır. Teknik sorularınız için bize ulaşabilirsiniz."],
                6 => ["title" => "Özel renk üretimi yapıyor musunuz?", "answer" => "Evet, müşterilerimizin talepleri doğrultusunda özel renk ve formül üretimi yapabilmekteyiz. Minimum sipariş miktarları geçerlidir."]
            ];
            $column1_faqs = array_slice($faqs, 0, 3, true);
            $column2_faqs = array_slice($faqs, 3, 3, true);
            ?>
            <div class="space-y-4">
                <?php foreach ($column1_faqs as $id => $faq): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="openAccordion = (openAccordion === <?php echo $id; ?> ? null : <?php echo $id; ?>)" 
                            class="w-full flex justify-between items-center px-6 py-4 text-left font-semibold text-black hover:bg-gray-50 transition duration-150 focus:outline-none">
                        <span><?php echo htmlspecialchars($faq['title']); ?></span>
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="{ 'rotate-180': openAccordion === <?php echo $id; ?> }"></i>
                    </button>
                    <div x-show="openAccordion === <?php echo $id; ?>" x-collapse x-cloak>
                        <div class="px-6 pb-4 pt-2 text-gray-600 text-sm">
                            <?php echo htmlspecialchars($faq['answer']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="space-y-4">
                <?php foreach ($column2_faqs as $id => $faq): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="openAccordion = (openAccordion === <?php echo $id; ?> ? null : <?php echo $id; ?>)" 
                            class="w-full flex justify-between items-center px-6 py-4 text-left font-semibold text-black hover:bg-gray-50 transition duration-150 focus:outline-none">
                        <span><?php echo htmlspecialchars($faq['title']); ?></span>
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="{ 'rotate-180': openAccordion === <?php echo $id; ?> }"></i>
                    </button>
                    <div x-show="openAccordion === <?php echo $id; ?>" x-collapse x-cloak>
                        <div class="px-6 pb-4 pt-2 text-gray-600 text-sm">
                            <?php echo htmlspecialchars($faq['answer']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>