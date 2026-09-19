<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Verificar si existe la sesión de administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] <= 0) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}
?>