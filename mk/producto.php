<?php
$page_title = "Detalle del Producto";
require_once __DIR__ . '/includes/header.php';

// 1. Obtener el ID del producto desde la URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$producto = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT p.*, c.nombre as cat_nombre, c.slug as cat_slug 
                           FROM productos p 
                           LEFT JOIN categorias c ON p.categoria_id = c.id 
                           WHERE p.id = ? AND p.activo = 1");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
}

// Si no existe el producto, redirigir al inicio
if (!$producto) {
    header("Location: " . SITE_URL);
    exit;
}

// 2. Preparar datos (imágenes, tallas, colores) para usarlos en la vista
$imagenes = array_filter(explode(',', $producto['imagenes']));
$tallas = array_filter(array_map('trim', explode(',', $producto['tallas'])));
$colores = array_filter(array_map('trim', explode(',', $producto['colores'])));

// Calcular precio
$precio_final = $producto['precio_oferta'] ? $producto['precio_oferta'] : $producto['precio'];
$en_oferta = $producto['precio_oferta'] && $producto['precio_oferta'] < $producto['precio'];
?>

<!-- Contenedor Principal con Alpine.js para la interactividad -->
<div x-data="{ 
    currentImage: '<?= SITE_URL ?>/uploads/productos/<?= $imagenes[0] ?? '' ?>',
    selectedSize: '<?= $tallas[0] ?? '' ?>',
    selectedColor: '<?= $colores[0] ?? '' ?>',
    openWhatsApp() {
        let msg = `¡Hola MK Store! 👋 Me interesa este producto:%0A%0A`;
        msg += `🛍️ *Producto:* <?= addslashes($producto['nombre']) ?>%0A`;
        msg += `💰 *Precio:* <?= formatoPrecio($precio_final) ?>%0A`;
        if (this.selectedSize) msg += `📏 *Talla:* ${this.selectedSize}%0A`;
        if (this.selectedColor) msg += `🎨 *Color:* ${this.selectedColor}%0A%0A`;
        msg += `¿Está disponible?`;
        window.open(`https://wa.me/<?= WHATSAPP_NUMBER ?>?text=${msg}`, '_blank');
    }
}" class="container mx-auto px-4 py-10">

    <!-- Breadcrumb (Migas de pan) -->
    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2">
        <a href="<?= SITE_URL ?>" class="hover:text-[#C9A961]">Inicio</a>
        <span>/</span>
        <a href="<?= SITE_URL ?>/categoria.php?slug=<?= $producto['cat_slug'] ?>" class="hover:text-[#C9A961]"><?= htmlspecialchars($producto['cat_nombre']) ?></a>
        <span>/</span>
        <span class="text-gray-800 font-medium truncate"><?= htmlspecialchars($producto['nombre']) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">
        
        <!-- COLUMNA IZQUIERDA: Galería de Imágenes -->
        <div class="space-y-4">
            <!-- Imagen Principal -->
            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
                <img :src="currentImage" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
            </div>
            
            <!-- Miniaturas (Solo si hay más de 1 imagen) -->
            <?php if (count($imagenes) > 1): ?>
                <div class="flex gap-3 overflow-x-auto pb-2">
                    <?php foreach ($imagenes as $img): 
                        $ruta = SITE_URL . '/uploads/productos/' . $img;
                    ?>
                        <button @click="currentImage = '<?= $ruta ?>'" 
                                class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all hover:border-[#C9A961]"
                                :class="currentImage === '<?= $ruta ?>' ? 'border-[#C9A961] opacity-100' : 'border-transparent opacity-60'">
                            <img src="<?= $ruta ?>" class="w-full h-full object-cover">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- COLUMNA DERECHA: Detalles del Producto -->
        <div class="flex flex-col">
            <p class="text-[#C9A961] font-bold uppercase tracking-widest text-sm mb-2"><?= htmlspecialchars($producto['cat_nombre']) ?></p>
            <h1 class="text-3xl md:text-4xl font-bold text-[#1A1A1A] brand-font mb-4"><?= htmlspecialchars($producto['nombre']) ?></h1>
            
            <!-- Precio -->
            <div class="flex items-baseline gap-3 mb-6">
                <span class="text-3xl font-bold text-[#1A1A1A]"><?= formatoPrecio($precio_final) ?></span>
                <?php if ($en_oferta): ?>
                    <span class="text-xl text-gray-400 line-through"><?= formatoPrecio($producto['precio']) ?></span>
                    <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded">OFERTA</span>
                <?php endif; ?>
            </div>

            <!-- Descripción -->
            <p class="text-gray-600 leading-relaxed mb-8"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

            <!-- Selector de Tallas -->
            <?php if (!empty($tallas)): ?>
                <div class="mb-6">
                    <p class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                        Talla: <span x-text="selectedSize" class="text-[#C9A961]"></span>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($tallas as $talla): ?>
                            <button @click="selectedSize = '<?= $talla ?>'"
                                    class="px-4 py-2 border rounded-lg font-medium transition-all"
                                    :class="selectedSize === '<?= $talla ?>' ? 'bg-[#1A1A1A] text-[#C9A961] border-[#1A1A1A]' : 'bg-white text-gray-600 border-gray-300 hover:border-[#C9A961]'">
                                <?= $talla ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Selector de Colores -->
            <?php if (!empty($colores)): ?>
                <div class="mb-8">
                    <p class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                        Color: <span x-text="selectedColor" class="text-[#C9A961]"></span>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($colores as $color): ?>
                            <button @click="selectedColor = '<?= $color ?>'"
                                    class="px-4 py-2 border rounded-lg font-medium transition-all"
                                    :class="selectedColor === '<?= $color ?>' ? 'bg-[#1A1A1A] text-[#C9A961] border-[#1A1A1A]' : 'bg-white text-gray-600 border-gray-300 hover:border-[#C9A961]'">
                                <?= $color ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Botones de Acción -->
            <div class="flex flex-col sm:flex-row gap-4 mt-auto pt-6 border-t border-gray-100">
                <button @click="openWhatsApp()" 
                        class="flex-1 bg-[#25D366] text-white font-bold py-4 px-6 rounded-xl hover:bg-[#20bd5a] transition-colors flex items-center justify-center gap-2 shadow-lg transform hover:scale-[1.02]">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Pedir por WhatsApp
                </button>
            </div>
            
            <p class="text-xs text-gray-400 mt-4 text-center sm:text-left">
                * Al hacer clic, se abrirá WhatsApp con los detalles de tu pedido listos para enviar.
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>