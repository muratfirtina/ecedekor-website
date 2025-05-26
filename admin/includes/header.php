<?php
requireAdminLogin();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin Panel - ECEDEKOR</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }
        
        .content-transition {
            transition: margin-left 0.3s ease-in-out;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
        
        .menu-item {
            transition: all 0.3s ease;
        }
        
        .menu-item:hover {
            background-color: rgba(59, 130, 246, 0.1);
            border-left: 4px solid #3b82f6;
        }
        
        .menu-item.active {
            background-color: rgba(59, 130, 246, 0.15);
            border-left: 4px solid #3b82f6;
            color: #3b82f6;
        }
        
        .card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100" x-data="{ sidebarOpen: false }">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg sidebar sidebar-transition" :class="{ 'open': sidebarOpen }">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 px-4 bg-red-600 text-white">
            <i class="fas fa-cogs text-2xl mr-2"></i>
            <span class="text-xl font-bold">ECEDEKOR</span>
        </div>
        
        <!-- Navigation -->
        <nav class="mt-8">
            <div class="px-4 space-y-2">
                <!-- Dashboard -->
                <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="menu-item flex items-center px-4 py-3 text-gray-700 rounded-lg <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                    Dashboard
                </a>
                
                <!-- Categories -->
                <a href="<?php echo ADMIN_URL; ?>/categories.php" class="menu-item flex items-center px-4 py-3 text-gray-700 rounded-lg <?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-tags w-5 mr-3"></i>
                    Kategoriler
                </a>
                
                <!-- Products -->
                <a href="<?php echo ADMIN_URL; ?>/products.php" class="menu-item flex items-center px-4 py-3 text-gray-700 rounded-lg <?php echo $currentPage === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box w-5 mr-3"></i>
                    Ürünler
                </a>
                
                <!-- Product Variants -->
                <a href="<?php echo ADMIN_URL; ?>/variants.php" class="menu-item flex items-center px-4 py-3 text-gray-700 rounded-lg <?php echo $currentPage === 'variants' ? 'active' : ''; ?>">
                    <i class="fas fa-palette w-5 mr-3"></i>
                    Ürün Varyantları
                </a>
                
                <!-- Homepage Sections -->
                <a href="<?php echo ADMIN_URL; ?>/homepage.php" class="menu-item flex items-center px-4 py-3 text-gray-700 rounded-lg <?php echo $currentPage === 'homepage' ? 'active' : ''; ?>">
                    <i class="fas fa-home w-5 mr-3"></i>
                    Ana Sayfa Yönetimi
                </a>
                
                <!-- Testimonials -->
                <a href="<?php echo ADMIN_URL; ?>/testimonials.php" class="menu-item flex items-center px-4 py-3 text-gray-700 rounded-lg <?php echo $currentPage === 'testimonials' ? 'active' : ''; ?>">
                    <i class="fas fa-comments w-5 mr-3"></i>
                    Müşteri Yorumları
                </a>
                
                <!-- Settings -->
                <a href="<?php echo ADMIN_URL; ?>/settings.php" class="menu-item flex items-center px-4 py-3 text-gray-700 rounded-lg <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog w-5 mr-3"></i>
                    Site Ayarları
                </a>
                
                <!-- File Manager -->
                <a href="<?php echo ADMIN_URL; ?>/files.php" class="menu-item flex items-center px-4 py-3 text-gray-700 rounded-lg <?php echo $currentPage === 'files' ? 'active' : ''; ?>">
                    <i class="fas fa-folder w-5 mr-3"></i>
                    Dosya Yönetici
                </a>
            </div>
            
            <!-- User Section -->
            <div class="absolute bottom-0 w-full p-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-red-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-700"><?php echo $_SESSION['admin_username']; ?></p>
                            <p class="text-xs text-gray-500">Admin</p>
                        </div>
                    </div>
                    <a href="<?php echo ADMIN_URL; ?>/logout.php" class="text-red-600 hover:text-red-700" title="Çıkış Yap">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="content-transition" :class="{ 'ml-64': !sidebarOpen }">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-600 hover:text-black mr-4">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <h1 class="text-2xl font-semibold text-black">
                        <?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?>
                    </h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- View Site -->
                    <a href="<?php echo BASE_URL; ?>" target="_blank" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:text-black border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-300">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Siteyi Görüntüle
                    </a>
                    
                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-black focus:outline-none">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        
                        <div x-show="open" x-transition @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-black">Bildirimler</h3>
                            </div>
                            <div class="p-4">
                                <p class="text-gray-600 text-sm">Henüz bildirim bulunmuyor.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center p-2 text-gray-600 hover:text-black focus:outline-none">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-2">
                                <i class="fas fa-user text-red-600"></i>
                            </div>
                            <span class="hidden md:block text-sm font-medium"><?php echo $_SESSION['admin_username']; ?></span>
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        
                        <div x-show="open" x-transition @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="py-2">
                                <a href="<?php echo ADMIN_URL; ?>/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-circle mr-2"></i>Profil
                                </a>
                                <a href="<?php echo ADMIN_URL; ?>/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Ayarlar
                                </a>
                                <hr class="my-1">
                                <a href="<?php echo ADMIN_URL; ?>/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Çıkış Yap
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-6">
