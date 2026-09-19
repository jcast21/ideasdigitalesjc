<?php
// Función para crear slugs amigables
function crearSlug($texto) {
    $texto = trim(mb_strtolower($texto));
    $texto = preg_replace('/[^a-z0-9\-]/', '-', $texto);
    $texto = preg_replace('/-+/', '-', $texto);
    return trim($texto, '-');
}

// Función para formatear precio
function formatoPrecio($precio) {
    return '$' . number_format($precio, 0, ',', '.');
}

// Función para redimensionar imagen
function redimensionarImagen($src, $dest, $ancho_max, $alto_max) {
    $info = getimagesize($src);
    $ancho = $info[0];
    $alto = $info[1];
    $tipo = $info[2];
    
    // Calcular proporciones
    $ratio = min($ancho_max / $ancho, $alto_max / $alto);
    $nuevo_ancho = (int)($ancho * $ratio);
    $nuevo_alto = (int)($alto * $ratio);
    
    // Crear imagen según el tipo
    switch($tipo) {
        case IMAGETYPE_JPEG:
            $imagen = imagecreatefromjpeg($src);
            break;
        case IMAGETYPE_PNG:
            $imagen = imagecreatefrompng($src);
            break;
        case IMAGETYPE_GIF:
            $imagen = imagecreatefromgif($src);
            break;
        default:
            return false;
    }
    
    // Crear nueva imagen redimensionada
    $nueva_imagen = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
    
    // Mantener transparencia para PNG
    if($tipo == IMAGETYPE_PNG) {
        imagealphablending($nueva_imagen, false);
        imagesavealpha($nueva_imagen, true);
    }
    
    imagecopyresampled($nueva_imagen, $imagen, 0, 0, 0, 0, 
                      $nuevo_ancho, $nuevo_alto, $ancho, $alto);
    
    // Guardar imagen
    switch($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($nueva_imagen, $dest, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($nueva_imagen, $dest, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($nueva_imagen, $dest);
            break;
    }
    
    imagedestroy($imagen);
    imagedestroy($nueva_imagen);
    
    return true;
}

// Función para generar número de pedido único
function generarNumeroPedido() {
    return 'MK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Verificar si el usuario es admin
function isAdmin() {
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

// Función de redirección segura (Reemplaza la que tenías)
function redirect($url) {
    // Elimina cualquier barra inicial que venga en la variable para evitar dobles barras
    $url = ltrim($url, '/');
    header("Location: " . SITE_URL . "/" . $url);
    exit;
}

// Sanitizar input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>