<?php
$page_title = "Inicio";
require_once __DIR__ . '/includes/header.php';

// Obtener productos destacados o los últimos activos
try {
    $stmt = $pdo->query("SELECT p.*, c.nombre as cat_nombre, c.slug as cat_slug 
                         FROM productos p 
                         LEFT JOIN categorias c ON p.categoria_id = c.id 
                         WHERE p.activo = 1 
                         ORDER BY p.destacado DESC, p.id DESC 
                         LIMIT 12");
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    $productos = [];
}
?>

<!-- Hero Section (Banner Principal) -->
<section class="relative bg-[#1A1A1A] text-white py-20 md:py-32 overflow-hidden">
    <div class="absolute inset-0 opacity-20 bg-[url('https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80')] bg-cover bg-center"></div>
    <div class="container mx-auto px-4 relative z-10 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-4 brand-font text-[#C9A961]">Nueva Colección</h1>
        <p class="text-lg md:text-xl text-gray-300 mb-8 max-w-2xl mx-auto">Descubre el estilo que define tu personalidad. Elegancia, confort y diseño en cada prenda.</p>
        <a href="#productos" class="inline-block bg-[#C9A961] text-[#1A1A1A] font-bold py-3 px-8 rounded-full hover:bg-white transition-colors duration-300 transform hover:scale-105 shadow-lg">
            Ver Productos
        </a>
    </div>
</section>

<!-- Sección de Productos -->
<section id="productos" class="container mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-[#1A1A1A] brand-font mb-2">Productos Destacados</h2>
        <div class="w-20 h-1 bg-[#C9A961] mx-auto"></div>
    </div>

    <?php if (empty($productos)): ?>
        <div class="text-center py-20 bg-white rounded-xl shadow-sm">
            <p class="text-gray-500 text-lg">Aún no hay productos disponibles. ¡Vuelve pronto!</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($productos as $prod): 
                $imgs = explode(',', $prod['imagenes']);
                $img = $imgs[0] ?? '';
                $ruta_img = !empty($img) ? SITE_URL . '/uploads/productos/' . $img : 'https://via.placeholder.com/300x400?text=MK+Store';
                
                // Calcular precio final
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
                        <p class="text-xs text-[#C9A961] font-bold uppercase tracking-wide mb-1"><?= htmlspecialchars($prod['cat_nombre'] ?? 'General') ?></p>
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
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>