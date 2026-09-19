<?php
// 1. Mostrar errores para diagnosticar (LO QUITAREMOS DESPUÉS)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Verificar autenticación
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] <= 0) {
    header("Location: login.php");
    exit;
}

// 4. Incluir configuración y funciones
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// 5. Obtener estadísticas de forma segura (con try-catch por si las tablas aún no existen)
$totalProductos = 0;
$totalCategorias = 0;
$pedidosPendientes = 0;
$ventasTotales = 0;

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1");
    $totalProductos = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM categorias WHERE activo = 1");
    $totalCategorias = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'");
    $pedidosPendientes = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT SUM(total) FROM pedidos WHERE estado != 'cancelado'");
    $ventasTotales = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    // Si hay error de tablas, lo mostramos en pantalla en lugar de Error 500
    $db_error = "⚠️ Error de Base de Datos: " . $e->getMessage();
}
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
    
    <!-- Overlay móvil -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity 
         class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"></div>

    <!-- Barra Lateral -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="sidebar fixed lg:static inset-y-0 left-0 z-30 w-64 transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col shadow-xl">
        <div class="p-6 border-b border-gray-800 flex items-center justify-between">
            <img src="../assets/img/logo-preloader.webp" alt="MK Store" class="h-10 w-auto drop-shadow-md">
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4">
            <a href="index.php" class="nav-item active flex items-center px-6 py-3 transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Dashboard
            </a>
            <a href="categorias.php" class="nav-item flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                Categorías
            </a>
            <a href="productos.php" class="nav-item flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Productos
            </a>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <a href="logout.php" class="flex items-center text-red-400 hover:text-red-300 transition-colors text-sm font-medium">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm z-10">
            <div class="flex items-center justify-between px-6 py-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="flex items-center space-x-4 ml-auto">
                    <span class="text-sm text-gray-600">Hola, <strong class="text-gray-900"><?= htmlspecialchars($_SESSION['admin_username']) ?></strong></span>
                    <a href="https://ideasdigitalesjc.com/mk" target="_blank" class="text-sm gold-text hover:underline font-medium">Ver Tienda →</a>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <?php if (isset($db_error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <p class="font-bold">Error de Base de Datos</p>
                    <p><?= htmlspecialchars($db_error) ?></p>
                    <p class="text-sm mt-2">💡 <strong>Solución:</strong> Ve a phpMyAdmin, selecciona tu base de datos y ejecuta el script SQL de creación de tablas que te proporcioné anteriormente.</p>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
                <p class="text-gray-500 text-sm">Resumen general de la actividad de la tienda</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#C9A961]">
                    <p class="text-sm font-medium text-gray-500">Productos Activos</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1"><?= $totalProductos ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <p class="text-sm font-medium text-gray-500">Categorías</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1"><?= $totalCategorias ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                    <p class="text-sm font-medium text-gray-500">Pedidos Pendientes</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1"><?= $pedidosPendientes ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <p class="text-sm font-medium text-gray-500">Ventas Totales</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">$<?= number_format($ventasTotales, 0, ',', '.') ?></p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Acciones Rápidas</h3>
                <div class="flex flex-wrap gap-4">
                    <a href="productos.php?action=add" class="inline-flex items-center px-4 py-2 bg-[#C9A961] text-black font-medium rounded-lg hover:bg-[#F4E8C1] transition-colors shadow-sm">
                        + Nuevo Producto
                    </a>
                    <a href="categorias.php?action=add" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white font-medium rounded-lg hover:bg-gray-700 transition-colors shadow-sm">
                        + Nueva Categoría
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>