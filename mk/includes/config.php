<?php
// Configuración de la base de datos
define('DB_HOST', 'ideass-mk.db.tb-hosting.com');
define('DB_NAME', 'ideass_mk');
define('DB_USER', 'ideass_jc');
define('DB_PASS', '$eyAnn.0733');
define('DB_CHARSET', 'utf8mb4');

// Configuración del sitio
define('SITE_URL', 'https://ideasdigitalesjc.com/mk/');
define('SITE_NAME', 'MK Store');
define('WHATSAPP_NUMBER', '584242890999'); // Cambia por tu número

// Configuración de uploads
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Conexión PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>