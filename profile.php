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

// Obtiene la información del usuario.
$sql = "SELECT name, email FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Inicializa un array para almacenar los errores.
$errors = [];
// Inicializa una variable para los mensajes de éxito.
$success_message = '';

// Comprueba si el formulario ha sido enviado.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpia y asigna las variables del formulario.
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Valida el nombre.
    if (empty($name)) {
        $errors[] = "El nombre es obligatorio.";
    }

    // Valida el correo electrónico.
    if (empty($email)) {
        $errors[] = "El correo electrónico es obligatorio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El formato del correo electrónico no es válido.";
    } else {
        // Comprueba si el correo electrónico ya está registrado por otro usuario.
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = "Este correo electrónico ya está registrado por otro usuario.";
        }
    }

    // Si no hay errores, procede a actualizar el perfil.
    if (empty($errors)) {
        $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$name, $email, $_SESSION['user_id']])) {
            $success_message = "Perfil actualizado correctamente.";
            // Actualiza la información del usuario para mostrarla en el formulario.
            $user['name'] = $name;
            $user['email'] = $email;
        } else {
            $errors[] = "Hubo un error al actualizar el perfil. Por favor, inténtelo de nuevo.";
        }
    }
}

// Incluye la cabecera de la página.
include __DIR__ . '/templates/header.php';
?>

<div class="container">
    <h2>Configurar Perfil</h2>
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
    <form action="profile.php" method="post">
        <div class="form-group">
            <label for="name">Nombre:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <button type="submit">Actualizar Perfil</button>
    </form>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
