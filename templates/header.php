<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrackMyLifePlan</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="page-container">
        <header>
            <div class="logo">
                <a href="index.php">TrackMyLifePlan</a>
            </div>
            <nav>
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="#">Notificaciones</a></li>
                        <li>
                            <div class="user-menu">
                                <?php
                                $avatar_path = 'img/default-avatar.png';
                                if (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar'])) {
                                    $avatar_path = 'img/avatars/' . $_SESSION['user_avatar'];
                                }
                                ?>
                                <img src="<?php echo $avatar_path; ?>" alt="Avatar" class="avatar">
                                <div class="dropdown-content">
                                    <a href="profile.php">Configurar Perfil</a>
                                    <a href="change-password.php">Cambiar Contraseña</a>
                                    <a href="logout.php">Cerrar Sesión</a>
                                </div>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="index.php">Iniciar Sesión / Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>
        <div class="main-content">
            <?php if (isset($_SESSION['user_id'])): ?>
                <aside class="sidebar">
                    <ul>
                        <li><a href="dashboard.php">Panel Principal</a></li>
                        <li><a href="habits.php">Registrar Hábitos</a></li>
                        <li><a href="schedule.php">Ver Horario</a></li>
                    </ul>
                </aside>
            <?php endif; ?>
            <main>
