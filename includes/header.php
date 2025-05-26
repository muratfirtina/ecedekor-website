<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo getSetting('site_title', 'ECEDEKOR'); ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : getSetting('site_description'); ?>">
    <meta name="keywords" content="ahşap tamir macunu, zemin koruyucu keçe, yapışkanlı tapa, mobilya tamiri, ahşap dolgu">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s ease-out',
                        'fade-in-down': 'fadeInDown 0.6s ease-out',
                        'slide-down': 'slideDown 0.3s ease-out',
                        'scale-in': 'scaleIn 0.3s ease-out',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    backdropBlur: {
                        xs: '2px',
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-6px);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .parallax {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .gradient-overlay {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.5));
        }

        .text-shadow {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hover-scale {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        .card-shadow {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .card-shadow:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .nav-blur {
            backdrop-filter: blur(12px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.85);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-item {
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-item::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 50%;
            background: linear-gradient(90deg, #A30000, #FF0000);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-item:hover::after {
            width: 100%;
        }

        .dropdown-menu {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .dropdown-item {
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 2px 8px;
        }

        .dropdown-item:hover {
            background: linear-gradient(90deg, #EBF4FF, #F3E8FF);
            transform: translateX(4px);
        }

        .cta-button {
            background: linear-gradient(135deg, #A30000, #FF0000);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .cta-button:hover {
            background: linear-gradient(135deg, #FF0000, #A30000);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }

        .logo-hover {
            transition: all 0.3s ease;
        }

        .logo-hover:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }

        .mobile-menu {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .search-icon {
            transition: all 0.3s ease;
        }

        .search-icon:hover {
            color: #A30000;
            transform: scale(1.1);
        }
    </style>

    <!-- Alpine.js for interactive components -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-50 font-sans">

    <!-- Navigation -->
    <nav class="nav-blur fixed w-full z-50 transition-all duration-300" x-data="{ open: false, scrolled: false }"
        x-init="window.addEventListener('scroll', () => { scrolled = window.pageYOffset > 50 })"
        :class="{ 'shadow-2xl': scrolled }">

        <!-- Top Bar -->
        <div class="bg-gradient-to-r from-gray-700 to-slate-600 text-white py-2 text-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-6">
                        <a href="mailto:<?php echo getSetting('company_email'); ?>" class="hover:text-red-300 transition duration-300 flex items-center">
                            <i class="far fa-envelope mr-2"></i>
                            <?php echo getSetting('company_email'); ?>
                        </a>
                        <span class="hidden md:flex items-center">
                            <i class="far fa-clock mr-2"></i>
                            Pazartesi - Cuma: 08:00 - 18:00
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="#" class="hover:text-red-300 transition duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="hover:text-red-300 transition duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="hover:text-red-300 transition duration-300">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    <!-- Logo -->
                    <a href="<?php echo BASE_URL; ?>" class="flex-shrink-0 flex items-center">
                        <img class="h-10 w-auto" src="<?php echo getSetting('logo_path') ?: (IMAGES_URL . '/logo.png'); ?>" alt="<?php echo getSetting('company_name'); ?>" onerror="this.style.display='none'">
                        <!-- <span class="ml-2 text-2xl font-bold text-red-600"><?php echo getSetting('company_name', 'ECEDEKOR'); ?></span> -->
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="<?php echo BASE_URL; ?>" class="nav-item text-gray-700 hover:text-red-600 font-medium py-2 transition duration-300">
                        Ana Sayfa
                    </a>
                    <a href="<?php echo BASE_URL; ?>/hakkimizda.php" class="nav-item text-gray-700 hover:text-red-600 font-medium py-2 transition duration-300">
                        Hakkımızda
                    </a>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false"
                            class="nav-item text-gray-700 hover:text-red-600 font-medium py-2 transition duration-300 flex items-center group">
                            Ürünler
                            <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-300 group-hover:text-red-600"
                                :class="{'rotate-180': open}"></i>
                        </button>
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-95"
                            class="dropdown-menu absolute top-full left-0 mt-3 w-80 rounded-2xl py-4 z-50">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100 mb-2">
                                Ürün Kategorileri
                            </div>
                            <?php
                            $categories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
                            foreach ($categories as $category): ?>
                                <a href="<?php echo BASE_URL; ?>/kategori/<?php echo $category['slug']; ?>"
                                    class="dropdown-item flex items-center px-4 py-3 text-gray-700 hover:text-red-600 group">
                                    <div class="w-8 h-8 bg-gradient-to-br from-red-100 to-purple-100 rounded-lg flex items-center justify-center mr-3 group-hover:from-red-200 group-hover:to-purple-200 transition-all duration-200">
                                        <i class="fas fa-tools text-red-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium"><?php echo htmlspecialchars($category['name']); ?></div>
                                        <div class="text-xs text-gray-500 line-clamp-1"><?php echo htmlspecialchars(substr($category['description'] ?? '', 0, 40)); ?>...</div>
                                    </div>
                                    <i class="fas fa-arrow-right ml-auto text-xs opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                                </a>
                            <?php endforeach; ?>
                            <div class="border-t border-gray-100 mt-2 pt-2">
                                <a href="<?php echo BASE_URL; ?>/urunler.php" class="dropdown-item flex items-center px-4 py-2 text-red-600 hover:text-red-700 font-medium">
                                    <i class="fas fa-th-large mr-3"></i>
                                    Tüm Ürünleri Görüntüle
                                    <i class="fas fa-arrow-right ml-auto text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/iletisim.php" class="nav-item text-gray-700 hover:text-red-600 font-medium py-2 transition duration-300">
                        İletişim
                    </a>
                </div>

                <!-- Search & CTA -->
                <div class="hidden lg:flex items-center space-x-4">
                    <button class="search-icon text-gray-600 hover:text-red-600 p-2" title="Ara">
                        <i class="fas fa-search text-lg"></i>
                    </button>
                    <a href="tel:<?php echo getSetting('company_phone'); ?>"
                        class="cta-button text-white px-6 py-3 rounded-full font-semibold flex items-center space-x-2">
                        <i class="fas fa-phone"></i>
                        <span>Hemen Ara</span>
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="lg:hidden flex items-center">
                    <button @click="open = !open" class="text-gray-700 hover:text-red-600 focus:outline-none p-2 rounded-lg hover:bg-gray-100 transition duration-300">
                        <div class="w-6 h-6 relative">
                            <span class="absolute top-0 left-0 w-full h-0.5 bg-current transform transition-all duration-300" :class="{'rotate-45 top-3': open, 'top-1': !open}"></span>
                            <span class="absolute top-3 left-0 w-full h-0.5 bg-current transition-all duration-300" :class="{'opacity-0': open, 'opacity-100': !open}"></span>
                            <span class="absolute top-6 left-0 w-full h-0.5 bg-current transform transition-all duration-300" :class="{'-rotate-45 top-3': open, 'top-5': !open}"></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-4"
            class="lg:hidden mobile-menu border-t border-gray-200">
            <div class="px-4 pt-4 pb-6 space-y-3">
                <a href="<?php echo BASE_URL; ?>" class="block px-4 py-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-xl font-medium transition duration-300">
                    <i class="fas fa-home mr-3 w-5"></i>Ana Sayfa
                </a>
                <a href="<?php echo BASE_URL; ?>/hakkimizda.php" class="block px-4 py-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-xl font-medium transition duration-300">
                    <i class="fas fa-info-circle mr-3 w-5"></i>Hakkımızda
                </a>

                <!-- Mobile Categories -->
                <div x-data="{ categoryOpen: false }" class="space-y-2">
                    <button @click="categoryOpen = !categoryOpen" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-xl font-medium transition duration-300">
                        <span><i class="fas fa-box mr-3 w-5"></i>Ürünler</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'rotate-180': categoryOpen}"></i>
                    </button>
                    <div x-show="categoryOpen" x-collapse class="ml-8 space-y-2">
                        <?php foreach ($categories as $category): ?>
                            <a href="<?php echo BASE_URL; ?>/kategori/<?php echo $category['slug']; ?>"
                                class="block px-4 py-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg text-sm transition duration-300">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                        <a href="<?php echo BASE_URL; ?>/urunler.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition duration-300">
                            Tüm Ürünler
                        </a>
                    </div>
                </div>

                <a href="<?php echo BASE_URL; ?>/iletisim.php" class="block px-4 py-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-xl font-medium transition duration-300">
                    <i class="fas fa-envelope mr-3 w-5"></i>İletişim
                </a>

                <!-- Mobile CTA -->
                <div class="pt-4 border-t border-gray-200">
                    <a href="tel:<?php echo getSetting('company_phone'); ?>"
                        class="block w-full bg-gradient-to-r from-red-600 to-purple-600 text-white px-6 py-4 rounded-xl text-center font-semibold shadow-lg hover:shadow-xl transition duration-300">
                        <i class="fas fa-phone mr-2"></i>
                        <?php echo getSetting('company_phone'); ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="pt-16">