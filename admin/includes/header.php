<?php
requireAdminLogin(); // Bu dosyanın başında config.php'nin zaten include edildiğini varsayıyorum

$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Okunmamış mesaj sayısını al
$unreadMessagesCount = fetchOne("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0")['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Admin Panel - <?php echo htmlspecialchars(getSetting('company_name', 'ECEDEKOR')); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> {/* Versiyonu güncel tutun */}
    
    <style>
        /* ... (mevcut stil tanımlamalarınız) ... */
        .menu-item {
            transition: all 0.2s ease-in-out;
            position: relative;
        }
        .menu-item .badge {
            position: absolute;
            top: 50%;
            right: 0.75rem; /* 12px */
            transform: translateY(-50%);
            min-width: 1.25rem; /* 20px */
            height: 1.25rem; /* 20px */
            padding: 0 0.375rem; /* 6px */
        }
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        @media (min-width: 768px) {
            .sidebar { transform: translateX(0); }
            .content-transition { margin-left: 16rem; /* w-64 */ }
            .content-transition.sidebar-closed { margin-left: 0; }
            .sidebar.md\:translate-x-0 { transform: translateX(0) !important; }
        }
         [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100" x-data="{ sidebarOpen: window.innerWidth >= 768 ? true : false }" @resize.window="sidebarOpen = window.innerWidth >= 768 ? true : false">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg sidebar sidebar-transition" 
         :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
         x-show="sidebarOpen"
         x-transition:enter="transition ease-in-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         @click.away="if (window.innerWidth < 768) sidebarOpen = false">
        
        <div class="flex items-center justify-center h-16 px-4 bg-red-600 text-white">
            <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="flex items-center">
                <i class="fas fa-cogs text-2xl mr-2"></i>
                <span class="text-xl font-bold"><?php echo htmlspecialchars(getSetting('company_name_short', 'ECEDEKOR')); ?></span>
            </a>
        </div>
        
        <nav class="mt-6 flex-1">
            <div class="px-3 space-y-1">
                <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="menu-item flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 rounded-md hover:bg-red-50 hover:text-red-700 <?php echo $currentPage === 'dashboard' ? 'active bg-red-50 text-red-700 border-l-4 border-red-600' : ''; ?>">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>Dashboard
                </a>
                
                <!-- YENİ: Mesajlar Linki -->
                <a href="<?php echo ADMIN_URL; ?>/messages.php" class="menu-item flex items-center justify-between px-3 py-2.5 text-sm font-medium text-gray-700 rounded-md hover:bg-red-50 hover:text-red-700 <?php echo $currentPage === 'messages' ? 'active bg-red-50 text-red-700 border-l-4 border-red-600' : ''; ?>">
                    <div class="flex items-center">
                        <i class="fas fa-envelope-open-text w-5 mr-3"></i>Mesajlar
                    </div>
                    <?php if ($unreadMessagesCount > 0): ?>
                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full badge">
                            <?php echo $unreadMessagesCount; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="<?php echo ADMIN_URL; ?>/categories.php" class="menu-item flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 rounded-md hover:bg-red-50 hover:text-red-700 <?php echo $currentPage === 'categories' ? 'active bg-red-50 text-red-700 border-l-4 border-red-600' : ''; ?>">
                    <i class="fas fa-tags w-5 mr-3"></i>Kategoriler
                </a>
                <a href="<?php echo ADMIN_URL; ?>/products.php" class="menu-item flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 rounded-md hover:bg-red-50 hover:text-red-700 <?php echo $currentPage === 'products' ? 'active bg-red-50 text-red-700 border-l-4 border-red-600' : ''; ?>">
                    <i class="fas fa-box w-5 mr-3"></i>Ürünler
                </a>
                 <a href="<?php echo ADMIN_URL; ?>/variants.php" class="menu-item flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 rounded-md hover:bg-red-50 hover:text-red-700 <?php echo $currentPage === 'variants' ? 'active bg-red-50 text-red-700 border-l-4 border-red-600' : ''; ?>">
                    <i class="fas fa-palette w-5 mr-3"></i>Ürün Varyantları
                </a>
                <a href="<?php echo ADMIN_URL; ?>/homepage.php" class="menu-item flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 rounded-md hover:bg-red-50 hover:text-red-700 <?php echo $currentPage === 'homepage' ? 'active bg-red-50 text-red-700 border-l-4 border-red-600' : ''; ?>">
                    <i class="fas fa-home w-5 mr-3"></i>Ana Sayfa Yönetimi
                </a>
                <a href="<?php echo ADMIN_URL; ?>/testimonials.php" class="menu-item flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 rounded-md hover:bg-red-50 hover:text-red-700 <?php echo $currentPage === 'testimonials' ? 'active bg-red-50 text-red-700 border-l-4 border-red-600' : ''; ?>">
                    <i class="fas fa-comments w-5 mr-3"></i>Müşteri Yorumları
                </a>
                <a href="<?php echo ADMIN_URL; ?>/settings.php" class="menu-item flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 rounded-md hover:bg-red-50 hover:text-red-700 <?php echo $currentPage === 'settings' ? 'active bg-red-50 text-red-700 border-l-4 border-red-600' : ''; ?>">
                    <i class="fas fa-cog w-5 mr-3"></i>Site Ayarları
                </a>
                <a href="<?php echo ADMIN_URL; ?>/files.php" class="menu-item flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 rounded-md hover:bg-red-50 hover:text-red-700 <?php echo $currentPage === 'files' ? 'active bg-red-50 text-red-700 border-l-4 border-red-600' : ''; ?>">
                    <i class="fas fa-folder-open w-5 mr-3"></i>Dosya Yönetici
                </a>
                <?php if (hasRole('admin')): ?>
                <a href="<?php echo ADMIN_URL; ?>/users.php" class="menu-item flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 rounded-md hover:bg-red-50 hover:text-red-700 <?php echo $currentPage === 'users' ? 'active bg-red-50 text-red-700 border-l-4 border-red-600' : ''; ?>">
                    <i class="fas fa-users-cog w-5 mr-3"></i>Kullanıcı Yönetimi
                </a>
                <?php endif; ?>
            </div>
        </nav>
        <div class="absolute bottom-0 w-full p-3 border-t border-gray-200 bg-white">
            <div class="flex items-center justify-between">
                <?php 
                $currentUser = getUserInfo($_SESSION['admin_id']);
                $userAvatar = $currentUser['avatar'] ?? ''; // config.php'de getSetting ile çekilebilir veya direkt session'dan
                $userName = htmlspecialchars(!empty($_SESSION['admin_full_name']) ? $_SESSION['admin_full_name'] : ($_SESSION['admin_username'] ?? 'Kullanıcı'));
                $userRole = htmlspecialchars(ucfirst($_SESSION['admin_role'] ?? 'user'));
                ?>
                <div class="flex items-center">
                    <?php if ($userAvatar && filter_var($userAvatar, FILTER_VALIDATE_URL)): ?>
                        <img src="<?php echo $userAvatar; ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                    <?php else: ?>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-red-100">
                            <span class="text-sm font-medium leading-none text-red-700"><?php echo mb_substr($userName, 0, 1); ?></span>
                        </span>
                    <?php endif; ?>
                    <div class="ml-2">
                        <p class="text-xs font-medium text-gray-700 truncate max-w-28"><?php echo $userName; ?></p>
                        <p class="text-xs text-gray-500"><?php echo $userRole; ?></p>
                    </div>
                </div>
                <a href="<?php echo ADMIN_URL; ?>/logout.php" class="text-red-600 hover:text-red-800 p-1 rounded-md hover:bg-red-100" title="Çıkış Yap">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="flex flex-col flex-1 md:ml-64 content-transition" :class="{ 'md:ml-64': sidebarOpen, 'md:ml-0': !sidebarOpen }">
        <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700 mr-3">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-xl font-semibold text-gray-800">
                        <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?>
                    </h1>
                </div>
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <a href="<?php echo BASE_URL; ?>" target="_blank" class="hidden sm:flex items-center px-3 py-2 text-xs sm:text-sm text-gray-600 hover:text-red-700 border border-gray-300 rounded-md hover:bg-red-50 transition duration-150">
                        <i class="fas fa-external-link-alt mr-1.5"></i>Siteyi Gör
                    </a>
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 focus:outline-none">
                            <i class="fas fa-bell text-lg"></i>
                            <?php if ($unreadMessagesCount > 0): ?>
                            <span class="absolute top-0 right-0 block h-2 w-2 transform -translate-y-1/2 translate-x-1/2 rounded-full text-white shadow-solid bg-red-500"></span>
                            <?php endif; ?>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-md shadow-lg border border-gray-200 z-40">
                            <div class="p-3 border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-700">Bildirimler</h3>
                            </div>
                            <div class="py-1 max-h-80 overflow-y-auto">
                                <?php if ($unreadMessagesCount > 0): ?>
                                    <a href="<?php echo ADMIN_URL; ?>/messages.php" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-envelope mr-2 text-red-500"></i>
                                        <?php echo $unreadMessagesCount; ?> yeni mesajınız var.
                                    </a>
                                <?php else: ?>
                                    <p class="px-3 py-4 text-sm text-gray-500 text-center">Yeni bildirim yok.</p>
                                <?php endif; ?>
                                <!-- Diğer bildirim türleri eklenebilir -->
                            </div>
                        </div>
                    </div>
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                             <?php if ($userAvatar && filter_var($userAvatar, FILTER_VALIDATE_URL)): ?>
                                <img src="<?php echo $userAvatar; ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover mr-2">
                            <?php else: ?>
                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-red-100 mr-2">
                                    <span class="text-sm font-medium leading-none text-red-700"><?php echo mb_substr($userName, 0, 1); ?></span>
                                </span>
                            <?php endif; ?>
                            <span class="hidden md:inline"><?php echo $userName; ?></span>
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-40">
                            <a href="<?php echo ADMIN_URL; ?>/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user-edit w-4 mr-2"></i>Profilim</a>
                            <a href="<?php echo ADMIN_URL; ?>/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog w-4 mr-2"></i>Ayarlar</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="<?php echo ADMIN_URL; ?>/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt w-4 mr-2"></i>Çıkış Yap</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <main class="flex-1 p-4 sm:p-6">
            <!-- Sayfa içeriği buraya gelecek -->