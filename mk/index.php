<?php
$page_title = "Inicio";
$seo_desc = "Bienvenido a MK Store. Encuentra la mejor selección de ropa, zapatos y accesorios con un estilo elegante, moderno y juvenil.";
require_once __DIR__ . '/includes/header.php';

// Obtener productos
try {
    $stmt = $pdo->query("SELECT p.*, c.nombre as cat_nombre, c.slug as cat_slug 
                         FROM productos p 
                         LEFT JOIN categorias c ON p.categoria_id = c.id 
                         WHERE p.activo = 1 
                         ORDER BY p.destacado DESC, p.id DESC 
                         LIMIT 12");
    $productos = $stmt->fetchAll();
} catch (Exception $e) { $productos = []; }
?>

<!-- HERO SECTION con Carrusel de Fondo -->
<section class="relative min-h-[500px] md:h-[700px] overflow-hidden py-12 md:py-0" x-data="{ 
    currentSlide: 0,
    slides: [
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1920&q=80',
        'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=1920&q=80',
        'https://images.unsplash.com/photo-1496747611176-843222e1e57c?w=1920&q=80',
        'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=1920&q=80',
        'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&q=80'
    ],
    init() {
        setInterval(() => {
            this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        }, 5000);
    }
}">
    
    <!-- Carrusel de Imágenes -->
    <template x-for="(slide, index) in slides" :key="index">
        <div class="absolute inset-0 transition-opacity duration-1000"
             :class="currentSlide === index ? 'opacity-100' : 'opacity-0'">
            <img :src="slide" class="w-full h-full object-cover" :alt="'Slide ' + (index + 1)">
        </div>
    </template>

    <!-- Capa Transparente Oscura -->
    <div class="absolute inset-0 bg-gradient-to-b from-black/75 via-black/50 to-black/75"></div>

    <!-- Contenido del Hero -->
    <div class="container mx-auto px-4 h-full relative z-10 flex flex-col items-center justify-center gap-8 md:gap-12 md:flex-row">
        
        <!-- Texto (Centrado en su mitad izquierda) -->
        <div class="md:w-1/2 w-full flex flex-col items-center md:items-center justify-center text-center space-y-4 md:space-y-6 pt-8 md:pt-0">
            <span class="text-[#C9A961] uppercase tracking-[0.2em] md:tracking-[0.3em] text-xs md:text-sm font-semibold">Nueva Colección 2024</span>
            <h1 class="text-3xl md:text-7xl font-bold brand-font leading-tight text-white px-2">
                Define tu <span class="text-[#C9A961] italic">Estilo</span>
            </h1>
            <p class="text-gray-200 text-sm md:text-lg max-w-md md:max-w-lg mx-auto leading-relaxed px-4">
                Moda que combina la elegancia clásica con la energía juvenil. Descubre prendas diseñadas para destacar.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-center pt-2 md:pt-4 px-4">
                <a href="#productos" class="bg-[#C9A961] text-[#1A1A1A] font-bold py-2.5 px-6 md:py-3 md:px-8 rounded-full hover:bg-white transition-all duration-300 transform hover:scale-105 shadow-lg text-sm md:text-base">
                    Ver Colección
                </a>
                <a href="<?= SITE_URL ?>/categoria.php?slug=ofertas" class="border-2 border-white text-white font-medium py-2.5 px-6 md:py-3 md:px-8 rounded-full hover:border-[#C9A961] hover:text-[#C9A961] transition-all duration-300 text-sm md:text-base">
                    Ver Ofertas
                </a>
            </div>
        </div>

        <!-- Logo MK (Centrado en su mitad derecha) -->
        <div class="md:w-1/2 w-full flex justify-center pt-4 md:pt-0">
            <div class="relative w-48 h-48 md:w-80 md:h-80">
                <img src="<?= SITE_URL ?>/assets/img/logo-preloader.webp" 
                     alt="MK Store" 
                     class="w-full h-full object-contain drop-shadow-2xl">
            </div>
        </div>
    </div>

    <!-- Indicadores del Carrusel -->
    <div class="absolute bottom-6 md:bottom-8 left-1/2 -translate-x-1/2 flex gap-2 z-20">
        <template x-for="(slide, index) in slides" :key="index">
            <button @click="currentSlide = index" 
                    class="w-2 h-2 md:w-3 md:h-3 rounded-full transition-all duration-300"
                    :class="currentSlide === index ? 'bg-[#C9A961] w-6 md:w-8' : 'bg-white/50 hover:bg-white/80'">
            </button>
        </template>
    </div>
</section>

<!-- SECCIÓN DE PRODUCTOS -->
<section id="productos" class="container mx-auto px-4 py-20">
    <div class="text-center mb-16">
        <span class="text-[#C9A961] uppercase tracking-widest text-xs font-bold">Exclusivos para ti</span>
        <h2 class="text-4xl font-bold text-[#1A1A1A] brand-font mt-2 mb-4">Productos Destacados</h2>
        <div class="w-16 h-1 bg-[#C9A961] mx-auto rounded-full"></div>
    </div>

    <?php if (empty($productos)): ?>
        <div class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100">
            <p class="text-gray-500 text-lg">Próximamente más productos. ¡Vuelve pronto!</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8">
            <?php foreach ($productos as $prod): 
                $imgs = explode(',', $prod['imagenes']);
                $img = $imgs[0] ?? '';
                $ruta_img = !empty($img) ? SITE_URL . '/uploads/productos/' . $img : 'https://via.placeholder.com/400x500?text=MK';
                
                $precio_final = $prod['precio_oferta'] ? $prod['precio_oferta'] : $prod['precio'];
                $en_oferta = $prod['precio_oferta'] && $prod['precio_oferta'] < $prod['precio'];
            ?>
                <div class="group bg-white rounded-2xl shadow-sm hover:shadow-2xl transition-all duration-500 overflow-hidden border border-gray-100 flex flex-col transform hover:-translate-y-2">
                    <div class="relative overflow-hidden aspect-[4/5] bg-gray-100">
                        <img src="<?= $ruta_img ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        
                        <?php if ($en_oferta): ?>
                            <span class="absolute top-3 left-3 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider shadow-lg">Oferta</span>
                        <?php endif; ?>
                        <?php if ($prod['destacado']): ?>
                            <span class="absolute top-3 right-3 bg-[#1A1A1A] text-[#C9A961] text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider shadow-lg">Top</span>
                        <?php endif; ?>

                        <a href="<?= SITE_URL ?>/producto.php?id=<?= $prod['id'] ?>" class="absolute bottom-0 left-0 right-0 bg-[#1A1A1A]/90 text-white text-center py-3 font-medium text-sm translate-y-full group-hover:translate-y-0 transition-transform duration-300 backdrop-blur-sm">
                            Ver Detalles
                        </a>
                    </div>
                    
                    <div class="p-5 flex flex-col flex-1">
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-1"><?= htmlspecialchars($prod['cat_nombre'] ?? 'General') ?></p>
                        <h3 class="font-semibold text-gray-800 mb-3 line-clamp-2 flex-1 text-sm leading-snug group-hover:text-[#C9A961] transition-colors"><?= htmlspecialchars($prod['nombre']) ?></h3>
                        
                        <div class="flex items-center justify-between mt-auto pt-3 border-t border-gray-50">
                            <div class="flex flex-col">
                                <?php if ($en_oferta): ?>
                                    <span class="text-gray-400 text-[10px] line-through"><?= formatoPrecio($prod['precio']) ?></span>
                                    <span class="text-[#1A1A1A] font-bold text-base"><?= formatoPrecio($precio_final) ?></span>
                                <?php else: ?>
                                    <span class="text-[#1A1A1A] font-bold text-base"><?= formatoPrecio($precio_final) ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="<?= SITE_URL ?>/producto.php?id=<?= $prod['id'] ?>" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-[#C9A961] hover:text-white transition-all duration-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>