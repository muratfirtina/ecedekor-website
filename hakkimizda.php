<?php
require_once 'includes/config.php'; // Bu yolun doğru olduğundan emin olun

$pageTitle = 'Hakkımızda';
$pageDescription = getSetting('site_description', 'ECEDEKOR olarak 1998 yılından bu yana mobilya sektöründe kaliteli ürünler üretiyoruz. 25 yıllık deneyimimiz ve müşteri memnuniyeti odaklı hizmet anlayışımız.');

include 'includes/header.php'; // Bu yolun doğru olduğundan emin olun
?>

<!-- Hero Section -->
<section class="relative py-24 bg-gradient-to-r from-red-600 to-black overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <!-- Hero Background Image -->
    <div class="absolute inset-0 z-0">
        <?php
        $aboutHeroImage = getSetting('about_image');
        $defaultAboutHeroImage = IMAGES_URL . '/default-hero-about.jpg'; // Varsayılan bir görsel belirleyin
        $heroImageSrc = $aboutHeroImage ?: $defaultAboutHeroImage;
        ?>
        <img src="<?php echo htmlspecialchars($heroImageSrc); ?>" class="w-full h-full object-cover" alt="Hakkımızda ECEDEKOR" onerror="this.src='<?php echo htmlspecialchars($defaultAboutHeroImage); ?>'; this.classList.add('bg-gradient-to-r', 'from-red-600', 'to-black');">
        <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-black opacity-80"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="animate-on-scroll">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 text-shadow">Hakkımızda</h1>
            <p class="text-xl md:text-2xl mb-8 max-w-4xl mx-auto text-shadow opacity-90">
                <?php echo (int)date('Y') - (int)getSetting('company_founded', 1998); ?> yıllık deneyimimizle ahşap tamir ve dolgu malzemelerinde güvenilir çözüm ortağınız.
            </p>

            <!-- Breadcrumb -->
            <nav class="mt-8">
                <ol class="flex items-center justify-center space-x-2 text-sm opacity-90">
                    <li><a href="<?php echo BASE_URL; ?>" class="hover:text-gray-200">Ana Sayfa</a></li>
                    <li><i class="fas fa-chevron-right mx-2"></i></li>
                    <li class="text-gray-200">Hakkımızda</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<!-- Company Story -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="animate-on-scroll">
                <div class="mb-6">
                    <span class="bg-red-100 text-red-800 text-sm font-semibold px-3 py-1 rounded-full">Hikayemiz</span>
                </div>
                <h2 class="text-4xl font-bold text-black mb-6">
                    <?php echo getSetting('company_founded', '1998'); ?> Yılından Bu Yana Kalite ve Güven
                </h2>
                <div class="space-y-4 text-gray-600 leading-relaxed">
                    <p>
                        ECEDEKOR, <?php echo getSetting('company_founded', '1998'); ?> yılından bu yana mobilya sektöründe kullanılmak üzere dolgu macunu,
                        PVC tapa ve keçe üretimi yapmaktadır. Kuruluşundan itibaren sadece yurt içi değil
                        yurt dışındaki müşterilerine de hizmet vermektedir.
                    </p>
                    <p>
                        2004 yılı itibarı ile yapmış olduğu ihracat ve ithalatlar neticesinde sektöre
                        kaliteli ve uygun fiyatlı ürünler sunmuştur. Mobilya sektöründe kullanılan
                        çeşitli teknik malzeme ve hammaddelerin tedariğini yapmaktadır.
                    </p>
                    <p>
                        Müşteri memnuniyeti ve kalite odaklı hizmet anlayışımızla, sektöründe öncü
                        firmalardan biri olmayı başarmış ve bu konumunu sürdürmeye devam etmektedir.
                    </p>
                </div>

                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <a href="<?php echo BASE_URL; ?>/urunler.php"
                       class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition duration-300 font-semibold text-center">
                        <i class="fas fa-box mr-2"></i>Ürünlerimizi İnceleyin
                    </a>
                    <a href="<?php echo BASE_URL; ?>/iletisim.php"
                       class="border-2 border-red-600 text-red-600 px-6 py-3 rounded-lg hover:bg-red-600 hover:text-white transition duration-300 font-semibold text-center">
                        <i class="fas fa-envelope mr-2"></i>İletişime Geçin
                    </a>
                </div>
            </div>

            <div class="animate-on-scroll">
                <div class="relative">
                    <?php
                    // Veritabanından şirket hikayesi görselini çek
                    $companyStoryImage = getSetting('company_story_image');
                    // Eğer ayar boşsa veya görsel bulunamazsa kullanılacak varsayılan görsel
                    // (IMAGES_URL . '/company-history.jpg' veya IMAGES_URL . '/placeholder-about.jpg' gibi)
                    $defaultCompanyStoryImage = IMAGES_URL . '/placeholder-about-story.jpg'; // Yeni bir placeholder belirleyebilirsiniz.
                    
                    // Kullanılacak görselin kaynağını belirle
                    $imageSrc = $companyStoryImage ?: $defaultCompanyStoryImage;
                    ?>
                    <img src="<?php echo htmlspecialchars($imageSrc); ?>"
                         alt="<?php echo htmlspecialchars(getSetting('company_name', 'ECEDEKOR')); ?> Tarihçesi ve Hikayesi"
                         class="rounded-2xl w-full h-96 object-cover shadow-2xl"
                         onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($defaultCompanyStoryImage); ?>';">

                    <!-- Timeline Badge -->
                    <div class="absolute -bottom-6 -right-6 bg-red-600 text-white p-6 rounded-2xl shadow-xl">
                        <div class="text-center">
                            <div class="text-3xl font-bold"><?php echo (int)date('Y') - (int)getSetting('company_founded', 1998); ?>+</div>
                            <div class="text-sm">Yıllık Deneyim</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 animate-on-scroll">
            <h2 class="text-4xl font-bold text-black mb-4">Değerlerimiz</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Başarımızın temelini oluşturan, bizi biz yapan değerlerimiz
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white rounded-2xl p-8 card-shadow hover-scale animate-on-scroll text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-award text-2xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-4">Kalite</h3>
                <p class="text-gray-600">
                    ISO standartlarında üretim yaparak en yüksek kalite standartlarını garanti ediyoruz.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 card-shadow hover-scale animate-on-scroll text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-leaf text-2xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-4">Çevre Dostu</h3>
                <p class="text-gray-600">
                    Sürdürülebilir üretim anlayışıyla çevre dostu ürünler geliştiriyoruz.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 card-shadow hover-scale animate-on-scroll text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-lightbulb text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-4">İnovasyon</h3>
                <p class="text-gray-600">
                    Sürekli araştırma geliştirme ile sektöre yenilikçi çözümler sunuyoruz.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-8 card-shadow hover-scale animate-on-scroll text-center">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-handshake text-2xl text-orange-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-4">Güven</h3>
                <p class="text-gray-600">
                    <?php echo (int)date('Y') - (int)getSetting('company_founded', 1998); ?> yıllık deneyimimizle müşterilerimizin güvenini kazandık ve koruyoruz.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-20 bg-red-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-white mb-4">Rakamlarla <?php echo htmlspecialchars(getSetting('company_name', 'ECEDEKOR')); ?></h2>
            <p class="text-xl text-red-100">Başarımızı gösteren ve gurur duyduğumuz rakamlar</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="text-center text-white animate-on-scroll">
                <div class="text-4xl lg:text-5xl font-bold mb-2"><?php echo (int)date('Y') - (int)getSetting('company_founded', 1998); ?>+</div>
                <div class="text-red-200">Yıllık Deneyim</div>
            </div>
            <div class="text-center text-white animate-on-scroll">
                <div class="text-4xl lg:text-5xl font-bold mb-2">1000+</div>
                <div class="text-red-200">Mutlu Müşteri</div>
            </div>
            <div class="text-center text-white animate-on-scroll">
                <div class="text-4xl lg:text-5xl font-bold mb-2">50+</div>
                <div class="text-red-200">Ürün Çeşidi</div>
            </div>
            <div class="text-center text-white animate-on-scroll">
                <div class="text-4xl lg:text-5xl font-bold mb-2">20+</div>
                <div class="text-red-200">İhracat Ülkesi</div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
            <!-- Mission -->
            <div class="animate-on-scroll">
                <div class="mb-6">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-bullseye text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-black">Misyonumuz</h3>
                </div>
                <p class="text-gray-600 leading-relaxed mb-6">
                    Mobilya sektöründe kullanılan ahşap tamir ve dolgu malzemelerinde en kaliteli
                    ürünleri üretmek, müşterilerimizin ihtiyaçlarını en iyi şekilde karşılamak ve
                    sektöre değer katacak yenilikçi çözümler sunmaktır.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Sürdürülebilir üretim anlayışı ile çevre dostu ürünler geliştirerek, gelecek
                    nesillere daha yaşanabilir bir dünya bırakmayı hedefliyoruz.
                </p>
            </div>

            <!-- Vision -->
            <div class="animate-on-scroll">
                <div class="mb-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-eye text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-black">Vizyonumuz</h3>
                </div>
                <p class="text-gray-600 leading-relaxed mb-6">
                    Ahşap tamir ve dolgu malzemeleri sektöründe Türkiye'nin öncü firması olmak ve
                    uluslararası pazarlarda tanınan bir marka haline gelmektir.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Sürekli araştırma geliştirme faaliyetleri ile sektöre yön veren, kalite ve
                    güvenilirlik konusunda referans alınan bir şirket olmayı amaçlıyoruz.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Quality & Certificates -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 animate-on-scroll">
            <h2 class="text-4xl font-bold text-black mb-4">Kalite ve Sertifikalar</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Kalite standartlarımızı ve sertifikalarımızı sürekli geliştiriyoruz
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="animate-on-scroll">
                <h3 class="text-2xl font-bold text-black mb-6">Kalite Kontrol Sürecimiz</h3>

                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center font-semibold text-sm mr-4">
                            1
                        </div>
                        <div>
                            <h4 class="font-semibold text-black mb-2">Hammadde Kontrolü</h4>
                            <p class="text-gray-600">Tüm hammaddeler laboratuvar ortamında test edilerek kalite standartları doğrulanır.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center font-semibold text-sm mr-4">
                            2
                        </div>
                        <div>
                            <h4 class="font-semibold text-black mb-2">Üretim Süreci</h4>
                            <p class="text-gray-600">Modern makine parkuru ve deneyimli personelimizle kontrollü üretim gerçekleştiriyoruz.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center font-semibold text-sm mr-4">
                            3
                        </div>
                        <div>
                            <h4 class="font-semibold text-black mb-2">Son Ürün Testi</h4>
                            <p class="text-gray-600">Üretilen her parti ürün kalite testlerinden geçirilerek onaylanır.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center font-semibold text-sm mr-4">
                            4
                        </div>
                        <div>
                            <h4 class="font-semibold text-black mb-2">Müşteri Geri Bildirimi</h4>
                            <p class="text-gray-600">Müşteri memnuniyeti anketleri ile sürekli iyileştirme sağlıyoruz.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="animate-on-scroll">
                <div class="bg-white rounded-2xl p-8 card-shadow">
                    <h3 class="text-xl font-bold text-black mb-6 text-center">Sertifikalarımız</h3>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <i class="fas fa-certificate text-3xl text-red-600 mb-3"></i>
                            <div class="font-semibold text-black">ISO 9001</div>
                            <div class="text-sm text-gray-600">Kalite Yönetimi</div>
                        </div>

                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <i class="fas fa-leaf text-3xl text-green-600 mb-3"></i>
                            <div class="font-semibold text-black">ISO 14001</div>
                            <div class="text-sm text-gray-600">Çevre Yönetimi</div>
                        </div>

                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <i class="fas fa-shield-alt text-3xl text-purple-600 mb-3"></i>
                            <div class="font-semibold text-black">CE Belgesi</div>
                            <div class="text-sm text-gray-600">Avrupa Uygunluk</div>
                        </div>

                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <i class="fas fa-check-circle text-3xl text-orange-600 mb-3"></i>
                            <div class="font-semibold text-black">TSE Belgesi</div>
                            <div class="text-sm text-gray-600">Türk Standartları</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 animate-on-scroll">
            <h2 class="text-4xl font-bold text-black mb-4">Ekibimiz</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Deneyimli ve uzman kadromuzla size en iyi hizmeti sunuyoruz
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="text-center animate-on-scroll">
                <div class="w-32 h-32 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-user-tie text-4xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-2">Yönetim Ekibi</h3>
                <p class="text-gray-600 mb-4">
                    Sektörde <?php echo (int)date('Y') - (int)getSetting('company_founded', 1998); ?>+ yıl deneyime sahip yönetim kadromuz ile şirketi geleceğe taşıyoruz.
                </p>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-users mr-2"></i>5 Kişi
                </div>
            </div>

            <div class="text-center animate-on-scroll">
                <div class="w-32 h-32 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-cogs text-4xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-2">Üretim Ekibi</h3>
                <p class="text-gray-600 mb-4">
                    Kalifiye üretim personelimiz ile kaliteli ürünleri zamanında üretiyoruz.
                </p>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-users mr-2"></i>15 Kişi
                </div>
            </div>

            <div class="text-center animate-on-scroll">
                <div class="w-32 h-32 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-flask text-4xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-black mb-2">Ar-Ge Ekibi</h3>
                <p class="text-gray-600 mb-4">
                    Araştırma geliştirme ekibimiz ile sürekli yenilik ve iyileştirme yapıyoruz.
                </p>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-users mr-2"></i>8 Kişi
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-red-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="animate-on-scroll">
            <h2 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars(getSetting('company_name', 'ECEDEKOR')); ?> Farkını Keşfedin</h2>
            <p class="text-xl mb-8 opacity-90">
                <?php echo (int)date('Y') - (int)getSetting('company_founded', 1998); ?> yıllık deneyimimizle size en iyi hizmeti sunmaya hazırız
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo BASE_URL; ?>/urunler.php"
                   class="bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                    <i class="fas fa-box mr-2"></i>Ürünlerimizi İnceleyin
                </a>
                <a href="<?php echo BASE_URL; ?>/iletisim.php"
                   class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-red-600 transition duration-300">
                    <i class="fas fa-envelope mr-2"></i>İletişime Geçin
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; // Bu yolun doğru olduğundan emin olun ?>