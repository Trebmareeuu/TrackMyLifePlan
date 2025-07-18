<?php
// Inicia la sesión para poder utilizar variables de sesión.
session_start();

// Comprueba si existe la cookie "Recordarme".
if (isset($_COOKIE['remember_me'])) {
    // Divide el valor de la cookie en selector y token.
    list($selector, $token) = explode(':', $_COOKIE['remember_me']);

    // Requiere el archivo de conexión a la base de datos.
    require_once __DIR__ . '/src/includes/db.php';
    // Prepara la consulta SQL para eliminar el token de "Recordarme".
    $sql = "DELETE FROM remembered_users WHERE selector = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selector]);

    // Elimina la cookie del navegador del usuario.
    setcookie('remember_me', '', time() - 3600, '/');
}

// Elimina todas las variables de sesión.
$_SESSION = [];
// Destruye la sesión.
session_destroy();

// Redirige al usuario a la página de inicio de sesión.
header("Location: login.php");
exit;
?>
