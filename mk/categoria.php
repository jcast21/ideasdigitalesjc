<?php
$page_title = "Categoría";
require_once __DIR__ . '/includes/header.php';

// Obtener el slug de la categoría desde la URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$categoria = null;
$productos = [];

if (!empty($slug)) {
    // Buscar la categoría
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE slug = ? AND activo = 1");
    $stmt->execute([$slug]);
    $categoria = $stmt->fetch();
    
    if ($categoria) {
        $page_title = $categoria['nombre'];
        
        // Obtener productos de esta categoría
        $stmt = $pdo->prepare("SELECT p.*, c.nombre as cat_nombre, c.slug as cat_slug 
                               FROM productos p 
                               LEFT JOIN categorias c ON p.categoria_id = c.id 
                               WHERE p.categoria_id = ? AND p.activo = 1 
                               ORDER BY p.destacado DESC, p.id DESC");
        $stmt->execute([$categoria['id']]);
        $productos = $stmt->fetchAll();
    }
}
?>

<div class="container mx-auto px-4 py-10">
    
    <!-- Header de la Categoría -->
    <?php if ($categoria): ?>
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-[#1A1A1A] brand-font mb-3"><?= htmlspecialchars($categoria['nombre']) ?></h1>
            <?php if (!empty($categoria['descripcion'])): ?>
                <p class="text-gray-600 max-w-2xl mx-auto"><?= htmlspecialchars($categoria['descripcion']) ?></p>
            <?php endif; ?>
            <div class="w-20 h-1 bg-[#C9A961] mx-auto mt-4"></div>
        </div>
    <?php else: ?>
        <div class="text-center py-20">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Categoría no encontrada</h1>
            <a href="<?= SITE_URL ?>" class="text-[#C9A961] hover:underline">← Volver al inicio</a>
        </div>
    <?php endif; ?>

    <!-- Grid de Productos -->
    <?php if ($categoria && !empty($productos)): ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($productos as $prod): 
                $imgs = explode(',', $prod['imagenes']);
                $img = $imgs[0] ?? '';
                $ruta_img = !empty($img) ? SITE_URL . '/uploads/productos/' . $img : 'https://via.placeholder.com/300x400?text=MK+Store';
                
                $precio_final = $prod['precio_oferta'] ? $prod['precio_oferta'] : $prod['precio'];
                $en_oferta = $prod['precio_oferta'] && $prod['precio_oferta'] < $prod['precio'];
            ?>
                <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-shadow duration-300 overflow-hidden group border border-gray-100 flex flex-col">
                    <div class="relative overflow-hidden aspect-[3/4] bg-gray-100">
                        <img src="<?= $ruta_img ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <?php if ($en_oferta): ?>
                            <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">OFERTA</span>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 flex flex-col flex-1">
                        <p class="text-xs text-[#C9A961] font-bold uppercase tracking-wide mb-1"><?= htmlspecialchars($prod['cat_nombre'] ?? '') ?></p>
                        <h3 class="font-bold text-gray-800 mb-2 line-clamp-2 flex-1"><?= htmlspecialchars($prod['nombre']) ?></h3>
                        <div class="flex items-center justify-between mt-auto">
                            <div>
                                <?php if ($en_oferta): ?>
                                    <span class="text-gray-400 text-xs line-through block"><?= formatoPrecio($prod['precio']) ?></span>
                                    <span class="text-[#1A1A1A] font-bold text-lg"><?= formatoPrecio($precio_final) ?></span>
                                <?php else: ?>
                                    <span class="text-[#1A1A1A] font-bold text-lg"><?= formatoPrecio($precio_final) ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="<?= SITE_URL ?>/producto.php?id=<?= $prod['id'] ?>" class="bg-[#1A1A1A] text-[#C9A961] p-2 rounded-full hover:bg-[#C9A961] hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($categoria): ?>
        <div class="text-center py-20 bg-white rounded-xl shadow-sm">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            <p class="text-gray-500 text-lg">Aún no hay productos en esta categoría.</p>
            <a href="<?= SITE_URL ?>" class="inline-block mt-4 text-[#C9A961] hover:underline">Ver todos los productos</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>