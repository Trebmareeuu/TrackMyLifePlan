<?php
// Inicia la sesión para poder utilizar variables de sesión.
session_start();

// Comprueba si el usuario ha iniciado sesión.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Requiere el archivo de conexión a la base de datos.
require_once __DIR__ . '/src/includes/db.php';

// Inicializa un array para almacenar los errores.
$errors = [];
// Inicializa una variable para los mensajes de éxito.
$success_message = '';

// Comprueba si el formulario ha sido enviado.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpia y asigna las variables del formulario.
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Obtiene la contraseña actual del usuario.
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Valida la contraseña actual.
    if (!password_verify($current_password, $user['password'])) {
        $errors[] = "La contraseña actual no es correcta.";
    }

    // Valida la nueva contraseña.
    if (empty($new_password)) {
        $errors[] = "La nueva contraseña es obligatoria.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "La nueva contraseña debe tener al menos 8 caracteres.";
    }

    // Comprueba si las nuevas contraseñas coinciden.
    if ($new_password !== $confirm_new_password) {
        $errors[] = "Las nuevas contraseñas no coinciden.";
    }

    // Si no hay errores, procede a cambiar la contraseña.
    if (empty($errors)) {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$password_hash, $_SESSION['user_id']])) {
            $success_message = "Contraseña cambiada correctamente.";
        } else {
            $errors[] = "Hubo un error al cambiar la contraseña. Por favor, inténtelo de nuevo.";
        }
    }
}

// Incluye la cabecera de la página.
include __DIR__ . '/templates/header.php';
?>

<div class="container">
    <h2>Cambiar Contraseña</h2>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="success">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>
    <form action="change-password.php" method="post">
        <div class="form-group">
            <label for="current_password">Contraseña Actual:</label>
            <input type="password" name="current_password" id="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">Nueva Contraseña:</label>
            <input type="password" name="new_password" id="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_new_password">Confirmar Nueva Contraseña:</label>
            <input type="password" name="confirm_new_password" id="confirm_new_password" required>
        </div>
        <button type="submit">Cambiar Contraseña</button>
    </form>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
