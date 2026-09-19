<?php
$page_title = "Resultados de Búsqueda";
require_once __DIR__ . '/includes/header.php';

// Obtener el término de búsqueda
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$productos = [];

if (!empty($query)) {
    $page_title = "Búsqueda: " . $query;
    
    // Buscar productos por nombre o descripción
    $searchTerm = '%' . $query . '%';
    $stmt = $pdo->prepare("SELECT p.*, c.nombre as cat_nombre, c.slug as cat_slug 
                           FROM productos p 
                           LEFT JOIN categorias c ON p.categoria_id = c.id 
                           WHERE p.activo = 1 
                           AND (p.nombre LIKE ? OR p.descripcion LIKE ?)
                           ORDER BY p.id DESC");
    $stmt->execute([$searchTerm, $searchTerm]);
    $productos = $stmt->fetchAll();
}
?>

<div class="container mx-auto px-4 py-10">
    
    <!-- Header de Búsqueda -->
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-[#1A1A1A] brand-font mb-3">
            <?php if (!empty($query)): ?>
                Resultados para: <span class="text-[#C9A961]">"<?= htmlspecialchars($query) ?>"</span>
            <?php else: ?>
                Búsqueda
            <?php endif; ?>
        </h1>
        <p class="text-gray-600">
            <?php if (!empty($query)): ?>
                Se encontraron <?= count($productos) ?> producto(s)
            <?php else: ?>
                Ingresa un término para buscar productos
            <?php endif; ?>
        </p>
        <div class="w-20 h-1 bg-[#C9A961] mx-auto mt-4"></div>
    </div>

    <!-- Formulario de Búsqueda (para refinar) -->
    <div class="max-w-2xl mx-auto mb-10">
        <form action="<?= SITE_URL ?>/buscar.php" method="GET" class="flex gap-2">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" 
                   placeholder="Buscar productos..." 
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-[#C9A961] focus:ring-2 focus:ring-[#C9A961]/20">
            <button type="submit" class="bg-[#1A1A1A] text-[#C9A961] px-6 py-3 rounded-lg font-bold hover:bg-gray-800 transition-colors">
                Buscar
            </button>
        </form>
    </div>

    <!-- Grid de Resultados -->
    <?php if (!empty($query) && !empty($productos)): ?>
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
    <?php elseif (!empty($query)): ?>
        <div class="text-center py-20 bg-white rounded-xl shadow-sm">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <p class="text-gray-500 text-lg mb-2">No se encontraron productos para "<strong><?= htmlspecialchars($query) ?></strong>"</p>
            <p class="text-gray-400 text-sm mb-6">Intenta con otros términos o revisa nuestras categorías</p>
            <a href="<?= SITE_URL ?>" class="inline-block bg-[#C9A961] text-[#1A1A1A] font-bold py-2 px-6 rounded-lg hover:bg-[#F4E8C1] transition-colors">
                Ver todos los productos
            </a>
        </div>
    <?php else: ?>
        <div class="text-center py-20 bg-white rounded-xl shadow-sm">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <p class="text-gray-500 text-lg">Usa el buscador para encontrar productos</p>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>