<?php
// Inicia la sesión para poder utilizar variables de sesión.
session_start();

// Si el usuario ya ha iniciado sesión, redirige al panel de control.
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión / Registrarse - TrackMyLifePlan</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-header">
            <button id="show-login-btn" class="auth-toggle-btn active">Iniciar Sesión</button>
            <button id="show-register-btn" class="auth-toggle-btn">Registrarse</button>
        </div>
        <div id="login-form" class="auth-form">
            <?php include 'login.php'; ?>
        </div>
        <div id="register-form" class="auth-form" style="display: none;">
            <?php include 'register.php'; ?>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>
