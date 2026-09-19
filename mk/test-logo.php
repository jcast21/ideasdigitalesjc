<?php
$ruta = __DIR__ . '/assets/img/';
echo "<h3>Archivos en assets/img/:</h3><ul>";
if (is_dir($ruta)) {
    foreach (scandir($ruta) as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "<li>$file - " . filesize($ruta . $file) . " bytes</li>";
        }
    }
} else {
    echo "<li> La carpeta assets/img/ NO existe</li>";
}
echo "</ul>";
?>