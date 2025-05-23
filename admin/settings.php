<?php
require_once '../includes/config.php';

$pageTitle = 'Site Ayarları';

$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        $updates = 0;
        
        // Update each setting
        foreach ($_POST as $key => $value) {
            if ($key !== 'csrf_token' && !str_starts_with($key, 'file_')) {
                $value = sanitizeInput($value);
                if (updateSetting($key, $value)) {
                    $updates++;
                }
            }
        }
        
        // Handle file uploads
        $fileSettings = [
            'logo_path' => 'logo',
            'hero_image' => 'hero',
            'about_image' => 'about',
            'contact_image' => 'contact'
        ];
        
        foreach ($fileSettings as $setting => $folder) {
            $fileKey = 'file_' . $setting;
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $uploadedFile = uploadFile($_FILES[$fileKey], $folder);
                if ($uploadedFile) {
                    updateSetting($setting, $uploadedFile);
                    $updates++;
                }
            }
        }
        
        if ($updates > 0) {
            $success = 'Ayarlar başarıyla güncellendi.';
        } else {
            $error = 'Herhangi bir değişiklik yapılmadı.';
        }
    }
}

// Get all settings
$settings = fetchAll("SELECT * FROM site_settings ORDER BY setting_key");
$settingsArray = [];
foreach ($settings as $setting) {
    $settingsArray[$setting['setting_key']] = $setting['setting_value'];
}

include 'includes/header.php';
?>

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

<form method="POST" enctype="multipart/form-data" class="space-y-8">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <!-- General Settings -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-cog mr-3 text-blue-600"></i>
            Genel Ayarlar
        </h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="site_title" class="block text-sm font-medium text-gray-700 mb-2">Site Başlığı</label>
                <input type="text" name="site_title" id="site_title"
                       value="<?php echo htmlspecialchars($settingsArray['site_title'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Site başlığını girin">
            </div>
            
            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">Şirket Adı</label>
                <input type="text" name="company_name" id="company_name"
                       value="<?php echo htmlspecialchars($settingsArray['company_name'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Şirket adını girin">
            </div>
        </div>
        
        <div class="mt-6">
            <label for="site_description" class="block text-sm font-medium text-gray-700 mb-2">Site Açıklaması</label>
            <textarea name="site_description" id="site_description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Site açıklamasını girin"><?php echo htmlspecialchars($settingsArray['site_description'] ?? ''); ?></textarea>
        </div>
        
        <div class="mt-6">
            <label for="company_founded" class="block text-sm font-medium text-gray-700 mb-2">Kuruluş Yılı</label>
            <input type="number" name="company_founded" id="company_founded" min="1900" max="<?php echo date('Y'); ?>"
                   value="<?php echo htmlspecialchars($settingsArray['company_founded'] ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Kuruluş yılını girin">
        </div>
    </div>
    
    <!-- Contact Information -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-address-book mr-3 text-green-600"></i>
            İletişim Bilgileri
        </h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="company_phone" class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                <input type="tel" name="company_phone" id="company_phone"
                       value="<?php echo htmlspecialchars($settingsArray['company_phone'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Telefon numarasını girin">
            </div>
            
            <div>
                <label for="company_email" class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                <input type="email" name="company_email" id="company_email"
                       value="<?php echo htmlspecialchars($settingsArray['company_email'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="E-posta adresini girin">
            </div>
        </div>
        
        <div class="mt-6">
            <label for="company_address" class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
            <textarea name="company_address" id="company_address" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Şirket adresini girin"><?php echo htmlspecialchars($settingsArray['company_address'] ?? ''); ?></textarea>
        </div>
    </div>
    
    <!-- Visual Settings -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-image mr-3 text-purple-600"></i>
            Görsel Ayarları
        </h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Logo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Site Logosu</label>
                <?php if (isset($settingsArray['logo_path']) && $settingsArray['logo_path']): ?>
                    <div class="mb-4">
                        <img src="<?php echo $settingsArray['logo_path']; ?>" alt="Mevcut logo" class="w-32 h-20 object-contain border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-500 mt-1">Mevcut logo</p>
                        <div class="mt-2">
                            <div class="text-xs text-gray-500">Tam URL: <?php echo $settingsArray['logo_path']; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="file_logo_path" accept="image/*"
                       onchange="previewImage(this, 'logoPreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <img id="logoPreview" src="#" alt="Logo önizleme" class="hidden w-32 h-20 object-contain border border-gray-200 rounded-lg mt-2">
                <p class="text-sm text-gray-500 mt-1">PNG, JPG formatları. Önerilen boyut: 200x80px</p>
            </div>
            
            <!-- Hero Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ana Sayfa Hero Görseli</label>
                <?php if (isset($settingsArray['hero_image']) && $settingsArray['hero_image']): ?>
                    <div class="mb-4">
                        <img src="<?php echo $settingsArray['hero_image']; ?>" alt="Mevcut hero görseli" class="w-32 h-20 object-cover border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-500 mt-1">Mevcut hero görseli</p>
                        <div class="mt-2">
                            <div class="text-xs text-gray-500">Tam URL: <?php echo $settingsArray['hero_image']; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="file_hero_image" accept="image/*"
                       onchange="previewImage(this, 'heroPreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <img id="heroPreview" src="#" alt="Hero önizleme" class="hidden w-32 h-20 object-cover border border-gray-200 rounded-lg mt-2">
                <p class="text-sm text-gray-500 mt-1">JPG, PNG formatları. Önerilen boyut: 1920x1080px</p>
            </div>
            
            <!-- About Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hakkımızda Görseli</label>
                <?php if (isset($settingsArray['about_image']) && $settingsArray['about_image']): ?>
                    <div class="mb-4">
                        <img src="<?php echo $settingsArray['about_image']; ?>" alt="Mevcut hakkımızda görseli" class="w-32 h-20 object-cover border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-500 mt-1">Mevcut hakkımızda görseli</p>
                        <div class="mt-2">
                            <div class="text-xs text-gray-500">Tam URL: <?php echo $settingsArray['about_image']; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="file_about_image" accept="image/*"
                       onchange="previewImage(this, 'aboutPreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <img id="aboutPreview" src="#" alt="Hakkımızda önizleme" class="hidden w-32 h-20 object-cover border border-gray-200 rounded-lg mt-2">
                <p class="text-sm text-gray-500 mt-1">JPG, PNG formatları. Önerilen boyut: 800x600px</p>
            </div>
            
            <!-- Contact Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">İletişim Görseli</label>
                <?php if (isset($settingsArray['contact_image']) && $settingsArray['contact_image']): ?>
                    <div class="mb-4">
                        <img src="<?php echo $settingsArray['contact_image']; ?>" alt="Mevcut iletişim görseli" class="w-32 h-20 object-cover border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-500 mt-1">Mevcut iletişim görseli</p>
                        <div class="mt-2">
                            <div class="text-xs text-gray-500">Tam URL: <?php echo $settingsArray['contact_image']; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="file_contact_image" accept="image/*"
                       onchange="previewImage(this, 'contactPreview')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <img id="contactPreview" src="#" alt="İletişim önizleme" class="hidden w-32 h-20 object-cover border border-gray-200 rounded-lg mt-2">
                <p class="text-sm text-gray-500 mt-1">JPG, PNG formatları. Önerilen boyut: 1920x1080px</p>
            </div>
        </div>
    </div>
    
    <!-- SEO Settings -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-search mr-3 text-orange-600"></i>
            SEO Ayarları
        </h2>
        
        <div class="space-y-6">
            <div>
                <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">Meta Anahtar Kelimeler</label>
                <textarea name="meta_keywords" id="meta_keywords" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Anahtar kelimeleri virgülle ayırın"><?php echo htmlspecialchars($settingsArray['meta_keywords'] ?? ''); ?></textarea>
                <p class="text-sm text-gray-500 mt-1">Örnek: ahşap tamir macunu, dolgu macunu, zemin koruyucu keçe</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="google_analytics" class="block text-sm font-medium text-gray-700 mb-2">Google Analytics ID</label>
                    <input type="text" name="google_analytics" id="google_analytics"
                           value="<?php echo htmlspecialchars($settingsArray['google_analytics'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="GA-XXXXXXXXX-X">
                </div>
                
                <div>
                    <label for="google_search_console" class="block text-sm font-medium text-gray-700 mb-2">Google Search Console</label>
                    <input type="text" name="google_search_console" id="google_search_console"
                           value="<?php echo htmlspecialchars($settingsArray['google_search_console'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Doğrulama kodu">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Social Media -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-share-alt mr-3 text-blue-600"></i>
            Sosyal Medya
        </h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="facebook_url" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fab fa-facebook-f mr-2 text-blue-600"></i>Facebook URL
                </label>
                <input type="url" name="facebook_url" id="facebook_url"
                       value="<?php echo htmlspecialchars($settingsArray['facebook_url'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="https://facebook.com/yourpage">
            </div>
            
            <div>
                <label for="instagram_url" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fab fa-instagram mr-2 text-pink-600"></i>Instagram URL
                </label>
                <input type="url" name="instagram_url" id="instagram_url"
                       value="<?php echo htmlspecialchars($settingsArray['instagram_url'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="https://instagram.com/yourprofile">
            </div>
            
            <div>
                <label for="linkedin_url" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fab fa-linkedin-in mr-2 text-blue-700"></i>LinkedIn URL
                </label>
                <input type="url" name="linkedin_url" id="linkedin_url"
                       value="<?php echo htmlspecialchars($settingsArray['linkedin_url'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="https://linkedin.com/company/yourcompany">
            </div>
            
            <div>
                <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fab fa-whatsapp mr-2 text-green-600"></i>WhatsApp Numarası
                </label>
                <input type="tel" name="whatsapp_number" id="whatsapp_number"
                       value="<?php echo htmlspecialchars($settingsArray['whatsapp_number'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="905551234567">
                <p class="text-sm text-gray-500 mt-1">Ülke kodu ile birlikte, boşluksuz (örn: 905551234567)</p>
            </div>
        </div>
    </div>
    
    <!-- Technical Settings -->
    <div class="bg-white rounded-lg shadow-md p-6 card">
        <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-server mr-3 text-gray-600"></i>
            Teknik Ayarlar
        </h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="maintenance_mode" class="block text-sm font-medium text-gray-700 mb-2">Bakım Modu</label>
                <select name="maintenance_mode" id="maintenance_mode"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="0" <?php echo (($settingsArray['maintenance_mode'] ?? '0') == '0') ? 'selected' : ''; ?>>Kapalı</option>
                    <option value="1" <?php echo (($settingsArray['maintenance_mode'] ?? '0') == '1') ? 'selected' : ''; ?>>Açık</option>
                </select>
                <p class="text-sm text-gray-500 mt-1">Bakım modu açıldığında site ziyaretçilere kapatılır</p>
            </div>
            
            <div>
                <label for="cache_enabled" class="block text-sm font-medium text-gray-700 mb-2">Önbellek</label>
                <select name="cache_enabled" id="cache_enabled"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="0" <?php echo (($settingsArray['cache_enabled'] ?? '1') == '0') ? 'selected' : ''; ?>>Kapalı</option>
                    <option value="1" <?php echo (($settingsArray['cache_enabled'] ?? '1') == '1') ? 'selected' : ''; ?>>Açık</option>
                </select>
                <p class="text-sm text-gray-500 mt-1">Önbellek performansı artırır</p>
            </div>
        </div>
        
        <div class="mt-6">
            <label for="custom_css" class="block text-sm font-medium text-gray-700 mb-2">Özel CSS</label>
            <textarea name="custom_css" id="custom_css" rows="6"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                      placeholder="/* Özel CSS kodlarınızı buraya yazın */"><?php echo htmlspecialchars($settingsArray['custom_css'] ?? ''); ?></textarea>
        </div>
        
        <div class="mt-6">
            <label for="custom_js" class="block text-sm font-medium text-gray-700 mb-2">Özel JavaScript</label>
            <textarea name="custom_js" id="custom_js" rows="6"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                      placeholder="// Özel JavaScript kodlarınızı buraya yazın"><?php echo htmlspecialchars($settingsArray['custom_js'] ?? ''); ?></textarea>
        </div>
    </div>
    
    <!-- Save Button -->
    <div class="flex justify-end">
        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">
            <i class="fas fa-save mr-2"></i>Ayarları Kaydet
        </button>
    </div>
</form>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
