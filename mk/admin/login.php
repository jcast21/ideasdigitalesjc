<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// 🎨 LISTA DE FONDOS (Se elige uno al azar cada vez que se carga la página)
$fondos = [
    'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1920&q=80', // Moda urbana/elegante
    'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&q=80', // Estilo fashion / shopping
    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1920&q=80', // Estilo juvenil moderno
    'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=1920&q=80', // Ropa moderna / streetwear
    'https://images.unsplash.com/photo-1496747611176-843222e1e57c?w=1920&q=80'  // Look elegante casual
];

// Seleccionar uno al azar
$fondo_actual = $fondos[array_rand($fondos)];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrador - MK Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .brand-font { font-family: 'Playfair Display', serif; }
        
        /* Fondo dinámico con overlay oscuro */
        .login-bg {
            background-image: url('<?= $fondo_actual ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        /* Efecto cristal oscuro (Glassmorphism) */
        .glass-panel {
            background: rgba(26, 26, 26, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(201, 169, 97, 0.2);
        }
        
        .gold-focus:focus {
            border-color: #C9A961;
            box-shadow: 0 0 0 3px rgba(201, 169, 97, 0.2);
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4 relative">
    
    <!-- Capa oscura profesional sobre el fondo -->
    <div class="absolute inset-0 bg-black/70"></div>

    <!-- Contenedor del Login -->
    <div class="glass-panel w-full max-w-md p-8 md:p-10 rounded-2xl shadow-2xl relative z-10">
        
        <!-- LOGO -->
        <div class="text-center mb-8">
            <img src="../assets/img/logo-preloader.webp" alt="MK Store Logo" class="h-24 w-auto mx-auto mb-4 drop-shadow-lg">
            <h2 class="text-white text-xl font-semibold tracking-wide">Panel de Administración</h2>
            <p class="text-gray-400 text-sm mt-1">Ingresa tus credenciales para continuar</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-900/40 border border-red-500/50 text-red-200 px-4 py-3 rounded-lg mb-6 text-sm text-center flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-300 mb-1.5">Usuario</label>
                <input type="text" id="username" name="username" required autocomplete="username"
                    class="w-full bg-[#1A1A1A]/50 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none gold-focus transition-all"
                    placeholder="Ej: admin">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password"
                    class="w-full bg-[#1A1A1A]/50 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none gold-focus transition-all"
                    placeholder="••••••••">
            </div>

            <button type="submit" 
                class="w-full bg-[#C9A961] hover:bg-[#b08d4b] text-[#1A1A1A] font-bold py-3.5 px-4 rounded-lg transition-all duration-300 transform hover:scale-[1.02] shadow-lg shadow-[#C9A961]/20 mt-2">
                Iniciar Sesión
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <a href="https://ideasdigitalesjc.com/mk" class="text-gray-500 hover:text-[#C9A961] text-sm transition-colors flex items-center justify-center gap-2 group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver a la tienda pública
            </a>
        </div>
    </div>
</body>
</html>