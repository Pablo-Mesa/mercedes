<?php
/**
 * views/layouts/public.php
 * Layout para pantallas públicas:
 * - Login
 * - Activación Rider
 * - Recuperación de contraseña
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solver App</title>

    <link rel="icon" type="image/svg+xml" href="/mercedes/public/images/icono_solver_nobg.png">
    <link rel="stylesheet" href="/mercedes/public/css/style.css">
    <link rel="stylesheet" href="/mercedes/public/css/css_cubo.css">
    <script src="/mercedes/public/js/tool-kit-v002.js"></script>
    
</head>

<body class="auth-layout">

    <main class="auth-container">

        <?php
        if (isset($data) && is_array($data)) {
            extract($data, EXTR_SKIP);
        }

        include __DIR__ . '/../' . $view . '.php';
        ?>

    </main>

</body>

</html>