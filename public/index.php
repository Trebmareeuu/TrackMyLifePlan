<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

include __DIR__ . '/../templates/header.php';
?>

<div class="container">
    <h1>Bienvenido a TrackMyLifePlan</h1>
    <p>Gestiona tus hábitos y finanzas de forma sencilla y eficaz.</p>
    <p>
        <a href="register.php" class="btn">Regístrate ahora</a> o
        <a href="login.php" class="btn">Inicia Sesión</a>
    </p>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
