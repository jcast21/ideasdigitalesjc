<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

try {
    $categorias_menu = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();
} catch (Exception $e) {
    $categorias_menu = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" type="image/png" sizes="32x32" href="<?= SITE_URL ?>/assets/img/logo-icon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= SITE_URL ?>/assets/img/logo-icon.png">
    <link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/img/logo-icon.png">

    <title><?= isset($page_title) ? $page_title . ' | ' : '' ?>MK Store - Moda Elegante y Juvenil</title>
    <meta name="description" content="<?= isset($seo_desc) ? $seo_desc : 'Descubre la nueva colección de MK Store.' ?>">
    <meta name="robots" content="index, follow">

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= isset($page_title) ? $page_title . ' | MK Store' : 'MK Store' ?>">
    <meta property="og:description" content="<?= isset($seo_desc) ? $seo_desc : 'MK Store - Moda Elegante' ?>">
    <meta property="og:image" content="<?= SITE_URL ?>/assets/img/logo-icon.png">
    <meta property="og:url" content="<?= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #FAFAFA; color: #1A1A1A; }
        h1, h2, h3, .brand-font { font-family: 'Playfair Display', serif; }
        
        .text-gold { color: #C9A961; }
        .bg-gold { background-color: #C9A961; }
        .bg-gold:hover { background-color: #b08d4b; }
        
        #preloader { position: fixed; inset: 0; background: #1A1A1A; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 0.8s ease, visibility 0.8s ease; }
        .preloader-container { position: relative; width: 280px; height: 280px; display: flex; justify-content: center; align-items: center; }
        .preloader-logo { position: relative; width: 100%; height: 100%; animation: logoShineZoom 3s ease-in-out infinite; filter: drop-shadow(0 0 20px rgba(201, 169, 97, 0.4)); }
        .preloader-logo img { width: 100%; height: 100%; object-fit: contain; }
        .preloader-shine { position: absolute; inset: 0; background: radial-gradient(circle at center, rgba(255,255,255,0.15) 0%, transparent 70%); animation: shinePulse 2s ease-in-out infinite; pointer-events: none; }
        .preloader-text { position: absolute; bottom: -40px; left: 50%; transform: translateX(-50%); color: #C9A961; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; font-weight: 600; letter-spacing: 0.4em; text-transform: uppercase; animation: textFade 3s ease-in-out infinite; }
        
        @keyframes logoShineZoom { 0%, 100% { transform: scale(1); filter: drop-shadow(0 0 20px rgba(201, 169, 97, 0.4)) brightness(1); } 50% { transform: scale(1.08); filter: drop-shadow(0 0 40px rgba(201, 169, 97, 0.8)) brightness(1.15); } }
        @keyframes shinePulse { 0%, 100% { opacity: 0.3; } 50% { opacity: 0.7; } }
        @keyframes textFade { 0%, 100% { opacity: 0.5; } 50% { opacity: 1; } }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #C9A961; border-radius: 4px; }
        
        /* Dropdown de Categorías */
        .dropdown-categorias {
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        .dropdown-trigger:hover .dropdown-categorias {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
    </style>
</head>
<body class="text-gray-800 antialiased" x-data="{ mobileMenuOpen: false, searchOpen: false }">

    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader-container">
            <div class="preloader-logo">
                <img src="<?= SITE_URL ?>/assets/img/logo-preloader.webp" alt="MK Store">
            </div>
            <div class="preloader-shine"></div>
            <div class="preloader-text">MK Store</div>
        </div>
    </div>

    <!-- Navbar Principal -->
    <nav class="bg-white shadow-md sticky top-0 z-40 border-b border-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-20">
                
                <!-- IZQUIERDA: Logo -->
                <div class="flex-shrink-0">
                    <a href="<?= SITE_URL ?>" class="flex items-center gap-3 group">
                        <img src="<?= SITE_URL ?>/assets/img/logo-preloader.webp" alt="MK Store" class="h-12 w-auto group-hover:scale-110 transition-transform duration-300">
                        <span class="text-xl font-bold tracking-wider text-[#1A1A1A] hidden md:block brand-font">MK STORE</span>
                    </a>
                </div>

                <!-- CENTRO: Buscador -->
                <div class="hidden md:flex flex-1 max-w-2xl mx-8">
                    <form action="<?= SITE_URL ?>/buscar.php" method="GET" class="w-full relative">
                        <input type="text" name="q" placeholder="Buscar productos..." class="w-full border-2 border-gray-200 rounded-full px-6 py-2.5 pr-12 focus:outline-none focus:border-[#C9A961] transition-colors">
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-[#C9A961] text-white p-2 rounded-full hover:bg-[#b08d4b] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </form>
                </div>

                <!-- DERECHA: Menú de Categorías (Dropdown en PC) -->
                <div class="hidden lg:block">
                    <div class="dropdown-trigger relative">
                        <button class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-[#C9A961] transition-colors py-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            Categorías
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <!-- Dropdown Panel -->
                        <div class="dropdown-categorias absolute right-0 top-full mt-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden">
                            <div class="p-4 bg-[#1A1A1A]">
                                <p class="text-[#C9A961] text-xs font-bold uppercase tracking-widest">Explora nuestra colección</p>
                            </div>
                            <div class="py-2 max-h-96 overflow-y-auto">
                                <?php if (empty($categorias_menu)): ?>
                                    <p class="px-4 py-3 text-gray-500 text-sm">No hay categorías disponibles</p>
                                <?php else: ?>
                                    <?php foreach ($categorias_menu as $cat): ?>
                                        <a href="<?= SITE_URL ?>/categoria.php?slug=<?= $cat['slug'] ?>" 
                                           class="flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-[#C9A961] transition-colors group">
                                            <span class="font-medium text-sm"><?= htmlspecialchars($cat['nombre']) ?></span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-[#C9A961] transform group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones Móviles -->
                <div class="flex items-center gap-3 lg:hidden">
                    <button @click="searchOpen = !searchOpen" class="text-gray-600 hover:text-[#C9A961] transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-800">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Buscador Desplegable Móvil -->
        <div x-show="searchOpen" @click.away="searchOpen = false" x-transition class="md:hidden absolute top-full left-0 w-full bg-white shadow-lg border-t border-gray-100 p-4 z-30">
            <form action="<?= SITE_URL ?>/buscar.php" method="GET" class="container mx-auto flex gap-2">
                <input type="text" name="q" placeholder="Buscar productos..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-[#C9A961]">
                <button type="submit" class="bg-[#C9A961] text-white px-4 py-2 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </form>
        </div>
    </nav>

    <!-- Menú Móvil (Drawer) -->
    <div x-show="mobileMenuOpen" class="fixed inset-0 z-50 lg:hidden" style="display: none;">
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="mobileMenuOpen = false"></div>
        <div class="absolute right-0 top-0 h-full w-72 bg-white shadow-xl p-6 transform transition-transform" x-transition:enter="translate-x-full" x-transition:enter-end="translate-x-0">
            <div class="flex justify-between items-center mb-8">
                <span class="font-bold text-lg brand-font">Menú</span>
                <button @click="mobileMenuOpen = false" class="text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <nav class="flex flex-col space-y-4">
                <a href="<?= SITE_URL ?>" class="text-gray-800 font-medium hover:text-[#C9A961]">Inicio</a>
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-3">Categorías</p>
                    <?php foreach ($categorias_menu as $cat): ?>
                        <a href="<?= SITE_URL ?>/categoria.php?slug=<?= $cat['slug'] ?>" class="block text-gray-600 hover:text-[#C9A961] py-2"><?= htmlspecialchars($cat['nombre']) ?></a>
                    <?php endforeach; ?>
                </div>
            </nav>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            const preloader = document.getElementById('preloader');
            setTimeout(() => {
                preloader.style.opacity = '0';
                preloader.style.visibility = 'hidden';
                setTimeout(() => preloader.remove(), 800);
            }, 2000);
        });
    </script>