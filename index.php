<?php
// Inicia la sesión para poder utilizar variables de sesión.
session_start();

// Si el usuario ya ha iniciado sesión, redirige al panel de control.
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Incluye la cabecera de la página.
include __DIR__ . '/templates/header.php';
?>

<div class="container auth-container">
    <div id="login-form" class="auth-form">
        <?php include 'login.php'; ?>
    </div>
    <div id="register-form" class="auth-form" style="display: none;">
        <?php include 'register.php'; ?>
    </div>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
