<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · SOLVER-APP</title>
    <link rel="icon" type="image/svg+xml" href="/mercedes/public/images/icono_solver_nobg.png">
    <link rel="stylesheet" href="/mercedes/public/css/style.css">
</head>
<body class="login-body">

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-brand">
                <img src="/mercedes/public/images/icono_solver_nobg.png" alt="Logo" class="login-logo">
                <h1>SOLVER-APP</h1>
                <p>Ingresa con tus credenciales para continuar</p>
            </div>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="/mercedes/login" method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" placeholder="admin@correo.com" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </form>

            <div class="login-hint">
                <strong>Credenciales por defecto</strong>
                <span>admin@correo.com &nbsp;/&nbsp; admin123</span>
            </div>
        </div>
    </div>

    <script src="/mercedes/public/js/main.js"></script>
</body>
</html>
