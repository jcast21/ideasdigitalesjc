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

// 1. PROCESAR ELIMINAR
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Obtener imágenes para eliminarlas del servidor
        $stmt = $pdo->prepare("SELECT imagenes FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch();
        
        if ($producto && !empty($producto['imagenes'])) {
            $imgs = explode(',', $producto['imagenes']);
            foreach ($imgs as $img) {
                $ruta = UPLOAD_DIR . 'productos/' . trim($img);
                if (file_exists($ruta)) {
                    unlink($ruta);
                }
            }
        }
        
        $pdo->prepare("DELETE FROM productos WHERE id = ?")->execute([$id]);
        $mensaje = "Producto eliminado exitosamente.";
        $tipo_mensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar: " . $e->getMessage();
        $tipo_mensaje = 'error';
    }
    header("Location: productos.php");
    exit;
}

// 2. PROCESAR FORMULARIO DE NUEVO PRODUCTO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    $nombre = sanitize($_POST['nombre']);
    $slug = !empty($_POST['slug']) ? crearSlug($_POST['slug']) : crearSlug($nombre);
    $categoria_id = (int)$_POST['categoria_id'];
    $descripcion = sanitize($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $precio_oferta = !empty($_POST['precio_oferta']) ? (float)$_POST['precio_oferta'] : null;
    $stock = (int)$_POST['stock'];
    $tallas = sanitize($_POST['tallas']);
    $colores = sanitize($_POST['colores']);
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $activo = isset($_POST['activo']) ? 1 : 0;

    $nombres_imagenes = [];
    if (isset($_FILES['imagenes']) && $_FILES['imagenes']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        $carpeta_destino = UPLOAD_DIR . 'productos/';
        if (!is_dir($carpeta_destino)) mkdir($carpeta_destino, 0755, true);

        $archivos = $_FILES['imagenes'];
        $total_archivos = count($archivos['name']);

        for ($i = 0; $i < $total_archivos; $i++) {
            if ($archivos['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $archivos['tmp_name'][$i];
                $nombre_original = $archivos['name'][$i];
                $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $nombre_final = uniqid('prod_') . '.' . $extension;
                    $ruta_final = $carpeta_destino . $nombre_final;

                    if (redimensionarImagen($tmp_name, $ruta_final, 800, 800)) {
                        $nombres_imagenes[] = $nombre_final;
                    }
                }
            }
        }
    }

    if (empty($nombres_imagenes)) {
        $mensaje = "Error: Debes subir al menos una imagen válida.";
        $tipo_mensaje = 'error';
    } else {
        $imagenes_str = implode(',', $nombres_imagenes);

        try {
            $stmt = $pdo->prepare("INSERT INTO productos (categoria_id, nombre, slug, descripcion, precio, precio_oferta, stock, tallas, colores, imagenes, destacado, activo) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$categoria_id, $nombre, $slug, $descripcion, $precio, $precio_oferta, $stock, $tallas, $colores, $imagenes_str, $destacado, $activo]);
            $mensaje = "Producto '$nombre' creado exitosamente con " . count($nombres_imagenes) . " imagen(es).";
            $tipo_mensaje = 'exito';
        } catch (PDOException $e) {
            $mensaje = "Error al guardar en BD: " . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}

// 3. OBTENER DATOS
$categorias = $pdo->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();
$productos = $pdo->query("SELECT p.*, c.nombre as cat_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC LIMIT 20")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - MK Store</title>
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
            <a href="categorias.php" class="nav-item flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg> Categorías
            </a>
            <a href="productos.php" class="nav-item active flex items-center px-6 py-3 transition-colors">
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
                <h2 class="text-xl font-semibold text-gray-800">Gestión de Productos</h2>
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
                            Nuevo Producto
                        </h3>
                        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="accion" value="guardar">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Producto *</label>
                                <input type="text" name="nombre" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C9A961] outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                                <select name="categoria_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C9A961] outline-none bg-white">
                                    <option value="">Selecciona una categoría...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Precio *</label>
                                    <input type="number" name="precio" step="0.01" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C9A961] outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Precio Oferta</label>
                                    <input type="number" name="precio_oferta" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C9A961] outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                                    <input type="number" name="stock" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C9A961] outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tallas</label>
                                    <input type="text" name="tallas" placeholder="S, M, L" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C9A961] outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Colores</label>
                                <input type="text" name="colores" placeholder="Negro, Blanco, Gris" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C9A961] outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Imágenes * (Múltiple)</label>
                                <input type="file" name="imagenes[]" multiple accept="image/*" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                                <textarea name="descripcion" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#C9A961] outline-none"></textarea>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="destacado" id="destacado" class="w-4 h-4 text-[#C9A961] border-gray-300 rounded focus:ring-[#C9A961]">
                                    <label for="destacado" class="ml-2 text-sm text-gray-700">Destacado</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="activo" id="activo" checked class="w-4 h-4 text-[#C9A961] border-gray-300 rounded focus:ring-[#C9A961]">
                                    <label for="activo" class="ml-2 text-sm text-gray-700">Activo</label>
                                </div>
                            </div>

                            <button type="submit" class="w-full gold-bg text-black font-bold py-2 px-4 rounded-lg hover:bg-[#F4E8C1] transition-colors mt-2">
                                Guardar Producto
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Últimos Productos Agregados</h3>
                            <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded-full"><?= count($productos) ?> mostrados</span>
                        </div>
                        
                        <?php if (empty($productos)): ?>
                            <div class="p-8 text-center text-gray-500">
                                <p>Aún no hay productos. ¡Agrega el primero!</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                                        <tr>
                                            <th class="px-4 py-3">Imagen</th>
                                            <th class="px-4 py-3">Nombre</th>
                                            <th class="px-4 py-3">Categoría</th>
                                            <th class="px-4 py-3">Precio</th>
                                            <th class="px-4 py-3 text-center">Estado</th>
                                            <th class="px-4 py-3 text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <?php foreach ($productos as $prod): 
                                            $imgs = explode(',', $prod['imagenes']);
                                            $img_principal = $imgs[0] ?? '';
                                            $ruta_img = !empty($img_principal) ? '../uploads/productos/' . $img_principal : '';
                                        ?>
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-4 py-3">
                                                    <?php if (!empty($img_principal) && file_exists(__DIR__ . '/../uploads/productos/' . $img_principal)): ?>
                                                        <img src="<?= $ruta_img ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                                                    <?php else: ?>
                                                        <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3 font-medium text-gray-900"><?= htmlspecialchars($prod['nombre']) ?></td>
                                                <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars($prod['cat_nombre'] ?? 'Sin cat.') ?></td>
                                                <td class="px-4 py-3">
                                                    <span class="font-bold text-gray-800"><?= formatoPrecio($prod['precio']) ?></span>
                                                    <?php if ($prod['precio_oferta']): ?>
                                                        <br><span class="text-xs text-green-600 font-medium">Oferta: <?= formatoPrecio($prod['precio_oferta']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <?php if ($prod['activo']): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Activo</span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <a href="?accion=eliminar&id=<?= $prod['id'] ?>" class="text-red-600 hover:text-red-800 font-medium text-xs transition-colors inline-flex items-center gap-1" onclick="return confirm('¿Estás seguro de eliminar este producto? Esta acción no se puede deshacer y eliminará las imágenes del servidor.')">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        Eliminar
                                                    </a>
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