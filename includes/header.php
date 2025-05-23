<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo getSetting('site_title', 'ECEDEKOR'); ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : getSetting('site_description'); ?>">
    <meta name="keywords" content="ahşap tamir macunu, zemin koruyucu keçe, yapışkanlı tapa, mobilya tamiri, ahşap dolgu">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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
            transition: transform 0.3s ease;
        }
        
        .hover-scale:hover {
            transform: scale(1.05);
        }
        
        .card-shadow {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }
        
        .card-shadow:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
    </style>
    
    <!-- Alpine.js for interactive components -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <a href="<?php echo BASE_URL; ?>" class="flex-shrink-0 flex items-center">
                        <img class="h-10 w-auto" src="<?php echo getSetting('logo_path') ?: (IMAGES_URL . '/logo.png'); ?>" alt="<?php echo getSetting('company_name'); ?>" onerror="this.style.display='none'">
                        <!-- <span class="ml-2 text-2xl font-bold text-blue-600"><?php echo getSetting('company_name', 'ECEDEKOR'); ?></span> -->
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="<?php echo BASE_URL; ?>" class="text-gray-700 hover:text-blue-600 transition duration-300 font-medium">Ana Sayfa</a>
                    <a href="<?php echo BASE_URL; ?>/hakkimizda.php" class="text-gray-700 hover:text-blue-600 transition duration-300 font-medium">Hakkımızda</a>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-gray-700 hover:text-blue-600 transition duration-300 font-medium flex items-center">
                            Ürünler
                            <i class="fas fa-chevron-down ml-1 text-xs" :class="{'rotate-180': open}"></i>
                        </button>
                        <div x-show="open" x-transition @click.away="open = false" class="absolute top-full left-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 z-50">
                            <?php
                            $categories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
                            foreach ($categories as $category): ?>
                                <a href="<?php echo BASE_URL; ?>/kategori/<?php echo $category['slug']; ?>" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition duration-300">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/iletisim.php" class="text-gray-700 hover:text-blue-600 transition duration-300 font-medium">İletişim</a>
                    <a href="tel:<?php echo getSetting('company_phone'); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 font-medium">
                        <i class="fas fa-phone mr-2"></i>Hemen Ara
                    </a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button @click="open = !open" class="text-gray-700 hover:text-blue-600 focus:outline-none">
                        <i class="fas fa-bars text-xl" x-show="!open"></i>
                        <i class="fas fa-times text-xl" x-show="open"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="open" x-transition class="md:hidden bg-white border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="<?php echo BASE_URL; ?>" class="block px-3 py-2 text-gray-700 hover:text-blue-600 font-medium">Ana Sayfa</a>
                <a href="<?php echo BASE_URL; ?>/hakkimizda.php" class="block px-3 py-2 text-gray-700 hover:text-blue-600 font-medium">Hakkımızda</a>
                <div class="px-3 py-2">
                    <span class="text-gray-700 font-medium">Ürünler</span>
                    <div class="ml-4 mt-2 space-y-1">
                        <?php foreach ($categories as $category): ?>
                            <a href="<?php echo BASE_URL; ?>/kategori/<?php echo $category['slug']; ?>" class="block py-1 text-gray-600 hover:text-blue-600 text-sm">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/iletisim.php" class="block px-3 py-2 text-gray-700 hover:text-blue-600 font-medium">İletişim</a>
                <a href="tel:<?php echo getSetting('company_phone'); ?>" class="block mx-3 mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg text-center font-medium">
                    <i class="fas fa-phone mr-2"></i>Hemen Ara
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Area -->
    <main class="pt-16">
