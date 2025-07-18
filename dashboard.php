<?php
// Inicia la sesión para poder utilizar variables de sesión.
session_start();

// Comprueba si el usuario ha iniciado sesión.
if (!isset($_SESSION['user_id'])) {
    // Si no ha iniciado sesión, comprueba si existe la cookie "Recordarme".
    if (isset($_COOKIE['remember_me'])) {
        // Divide el valor de la cookie en selector y token.
        list($selector, $token) = explode(':', $_COOKIE['remember_me']);
        $token = hex2bin($token);

        // Requiere el archivo de conexión a la base de datos.
        require_once __DIR__ . '/src/includes/db.php';

        // Prepara la consulta SQL para obtener el token de "Recordarme".
        $sql = "SELECT * FROM remembered_users WHERE selector = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$selector]);
        $remembered_user = $stmt->fetch();

        // Si se encuentra el token, comprueba si es válido.
        if ($remembered_user) {
            $token_hash = hash('sha256', $token);
            if (hash_equals($token_hash, $remembered_user['token_hash'])) {
                // Si el token es válido, inicia sesión para el usuario.
                $_SESSION['user_id'] = $remembered_user['user_id'];
            }
        }
    } else {
        // Si no hay sesión ni cookie, redirige al usuario a la página de inicio de sesión.
        header("Location: login.php");
        exit;
    }
}

// Requiere el archivo de conexión a la base de datos.
require_once __DIR__ . '/src/includes/db.php';

// Prepara la consulta SQL para obtener el nombre del usuario.
$sql = "SELECT name FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Incluye la cabecera de la página.
include __DIR__ . '/templates/header.php';
?>

<div class="container">
    <h2>Panel de Control</h2>
    <p>Bienvenido, <?php echo htmlspecialchars($user['name']); ?>!</p>
    <p>Aquí podrás ver tus hábitos y finanzas.</p>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
