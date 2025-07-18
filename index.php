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

<div class="container">
    <h1>Bienvenido a TrackMyLifePlan</h1>
    <p>Gestiona tus hábitos y finanzas de forma sencilla y eficaz.</p>
    <p>
        <a href="register.php" class="btn">Regístrate ahora</a> o
        <a href="login.php" class="btn">Inicia Sesión</a>
    </p>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
