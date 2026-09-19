<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] <= 0) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$mensaje = '';
$tipo_mensaje = '';
$categoria_editar = null;

// 1. PROCESAR ELIMINAR
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Verificar si tiene productos asociados
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = ?");
        $stmt->execute([$id]);
        $total_productos = $stmt->fetchColumn();
        
        if ($total_productos > 0) {
            $mensaje = "No se puede eliminar: hay $total_productos producto(s) asociados. Desactívala en su lugar.";
            $tipo_mensaje = 'error';
        } else {
            $pdo->prepare("DELETE FROM categorias WHERE id = ?")->execute([$id]);
            $mensaje = "Categoría eliminada exitosamente.";
            $tipo_mensaje = 'exito';
        }
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar: " . $e->getMessage();
        $tipo_mensaje = 'error';
    }
    header("Location: categorias.php");
    exit;
}

// 2. CARGAR DATOS PARA EDITAR
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    $categoria_editar = $stmt->fetch();
}

// 3. PROCESAR FORMULARIO (Crear o Actualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'guardar') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $nombre = sanitize($_POST['nombre']);
        $slug = !empty($_POST['slug']) ? crearSlug($_POST['slug']) : crearSlug($nombre);
        $descripcion = sanitize($_POST['descripcion']);
        $activo = isset($_POST['activo']) ? 1 : 0;

        try {
            if ($id) {
                // Actualizar
                $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, slug = ?, descripcion = ?, activo = ? WHERE id = ?");
                $stmt->execute([$nombre, $slug, $descripcion, $activo, $id]);
                $mensaje = "Categoría '$nombre' actualizada exitosamente.";
            } else {
                // Crear nueva
                $stmt = $pdo->prepare("INSERT INTO categorias (nombre, slug, descripcion, activo) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $slug, $descripcion, $activo]);
                $mensaje = "Categoría '$nombre' creada exitosamente.";
            }
            $tipo_mensaje = 'exito';
            $categoria_editar = null;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensaje = "Error: Ya existe una categoría con ese nombre o URL (slug).";
            } else {
                $mensaje = "Error al guardar: " . $e->getMessage();
            }
            $tipo_mensaje = 'error';
        }
    }
}

// 4. OBTENER LISTA DE CATEGORÍAS
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY orden ASC, id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - MK Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { background-color: #F3F4F6; color: #1A1A1A; }
        .sidebar { background-color: #1A1A1A; color: #F9F9F9; }
        .gold-text { color: #C9A961; }
        .gold-bg { background-color: #C9A961; }
        .gold-bg:hover { background-color: #F4E8C1; }
        .nav-item.active { background-color: rgba(201, 169, 97, 0.15); color: #C9A961; border-right: 3px solid #C9A961; }
    </style>
</head>
<body class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
    
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="sidebar fixed lg:static inset-y-0 left-0 z-30 w-64 transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col shadow-xl">
        <div class="p-6 border-b border-gray-800 flex items-center justify-between">
            <img src="../assets/img/logo-preloader.webp" alt="MK Store" class="h-10 w-auto drop-shadow-md">
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <a href="index.php" class="nav-item flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg> Dashboard
            </a>
            <a href="categorias.php" class="nav-item active flex items-center px-6 py-3 transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg> Categorías
            </a>
            <a href="productos.php" class="nav-item flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg> Productos
            </a>
        </nav>
        <div class="p-4 border-t border-gray-800">
            <a href="logout.php" class="flex items-center text-red-400 hover:text-red-300 transition-colors text-sm font-medium">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg> Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Contenido -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm z-10">
            <div class="flex items-center justify-between px-6 py-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-gray-900"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></button>
                <h2 class="text-xl font-semibold text-gray-800">Gestión de Categorías</h2>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <?php if ($mensaje): ?>
                <div class="mb-6 p-4 rounded-lg border <?= $tipo_mensaje === 'exito' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Formulario -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 sticky top-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#C9A961]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <?= $categoria_editar ? 'Editar Categoría' : 'Nueva Categoría' ?>
                        </h3>
                        <form method="POST" action="" class="space-y-4">
                            <input type="hidden" name="accion" value="guardar">
                            <?php if ($categoria_editar): ?>
                                <input type="hidden" name="id" value="<?= $categoria_editar['id'] ?>">
                            <?php endif; ?>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                                <input type="text" name="nombre" required value="<?= $categoria_editar ? htmlspecialchars($categoria_editar['nombre']) : '' ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C9A961] focus:border-[#C9A961] outline-none transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                                <input type="text" name="slug" placeholder="Se genera automático si se deja vacío" value="<?= $categoria_editar ? htmlspecialchars($categoria_editar['slug']) : '' ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-600 focus:ring-2 focus:ring-[#C9A961] outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                                <textarea name="descripcion" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C9A961] outline-none"><?= $categoria_editar ? htmlspecialchars($categoria_editar['descripcion']) : '' ?></textarea>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="activo" id="activo" <?= (!$categoria_editar || $categoria_editar['activo']) ? 'checked' : '' ?> class="w-4 h-4 text-[#C9A961] border-gray-300 rounded focus:ring-[#C9A961]">
                                <label for="activo" class="ml-2 text-sm text-gray-700">Categoría activa</label>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 gold-bg text-black font-bold py-2 px-4 rounded-lg hover:bg-[#F4E8C1] transition-colors mt-2">
                                    <?= $categoria_editar ? 'Actualizar' : 'Guardar' ?>
                                </button>
                                <?php if ($categoria_editar): ?>
                                    <a href="categorias.php" class="flex-1 bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors mt-2 text-center">
                                        Cancelar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Categorías Existentes</h3>
                            <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded-full"><?= count($categorias) ?> totales</span>
                        </div>
                        
                        <?php if (empty($categorias)): ?>
                            <div class="p-8 text-center text-gray-500">
                                <p>Aún no hay categorías. ¡Crea la primera!</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                                        <tr>
                                            <th class="px-6 py-3">Nombre</th>
                                            <th class="px-6 py-3">Slug</th>
                                            <th class="px-6 py-3 text-center">Estado</th>
                                            <th class="px-6 py-3 text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <?php foreach ($categorias as $cat): ?>
                                            <tr class="hover:bg-gray-50 transition-colors <?= $categoria_editar && $categoria_editar['id'] == $cat['id'] ? 'bg-yellow-50' : '' ?>">
                                                <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($cat['nombre']) ?></td>
                                                <td class="px-6 py-4 text-gray-500 font-mono text-xs"><?= htmlspecialchars($cat['slug']) ?></td>
                                                <td class="px-6 py-4 text-center">
                                                    <?php if ($cat['activo']): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activa</span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactiva</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <div class="flex items-center justify-center gap-2">
                                                        <a href="?accion=editar&id=<?= $cat['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium text-xs transition-colors" title="Editar">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        </a>
                                                        <a href="?accion=eliminar&id=<?= $cat['id'] ?>" class="text-red-600 hover:text-red-800 font-medium text-xs transition-colors" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta categoría? Esta acción no se puede deshacer.')">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </main>
    </div>
</body>
</html>