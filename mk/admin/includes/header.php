<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MK Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { background-color: #F3F4F6; color: #1A1A1A; }
        .sidebar { background-color: #1A1A1A; color: #F9F9F9; }
        .gold-text { color: #C9A961; }
        .nav-item.active { background-color: rgba(201, 169, 97, 0.15); color: #C9A961; border-right: 3px solid #C9A961; }
    </style>
</head>
<body class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
    
    <!-- Overlay para móvil -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity 
         class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"></div>

    <!-- Barra Lateral (Sidebar) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="sidebar fixed lg:static inset-y-0 left-0 z-30 w-64 transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col shadow-xl">
        <div class="p-6 border-b border-gray-800 flex items-center justify-between">
            <h1 class="text-2xl font-bold gold-text tracking-wider">MK ADMIN</h1>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4">
            <a href="<?= SITE_URL ?>/admin/index.php" class="nav-item flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?= $currentPage == 'index.php' ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Dashboard
            </a>
            <a href="<?= SITE_URL ?>/admin/categorias.php" class="nav-item flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?= $currentPage == 'categorias.php' ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                Categorías
            </a>
            <a href="<?= SITE_URL ?>/admin/productos.php" class="nav-item flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?= $currentPage == 'productos.php' ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Productos
            </a>
            <a href="<?= SITE_URL ?>/admin/pedidos.php" class="nav-item flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors <?= $currentPage == 'pedidos.php' ? 'active' : '' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Pedidos
            </a>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <a href="<?= SITE_URL ?>/admin/logout.php" class="flex items-center text-red-400 hover:text-red-300 transition-colors text-sm font-medium">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Barra Superior -->
        <header class="bg-white shadow-sm z-10">
            <div class="flex items-center justify-between px-6 py-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="flex items-center space-x-4 ml-auto">
                    <span class="text-sm text-gray-600">Hola, <strong class="text-gray-900"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong></span>
                    <a href="<?= SITE_URL ?>" target="_blank" class="text-sm gold-text hover:underline font-medium flex items-center gap-1">
                        Ver Tienda 
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                </div>
            </div>
        </header>

        <!-- Área de Contenido Dinámico -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">