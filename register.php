<?php
// Inicia la sesión para poder utilizar variables de sesión.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Requiere el archivo de conexión a la base de datos.
require_once __DIR__ . '/src/includes/db.php';

// Inicializa un array para almacenar los errores.
$errors = [];

// Comprueba si el formulario ha sido enviado.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Limpia y asigna las variables del formulario.
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

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
        // Comprueba si el correo electrónico ya está registrado.
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Este correo electrónico ya está registrado.";
        }
    }

    // Valida la contraseña.
    if (empty($password)) {
        $errors[] = "La contraseña es obligatoria.";
    } elseif (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    }

    // Comprueba si las contraseñas coinciden.
    if ($password !== $confirm_password) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    // Si no hay errores, procede a registrar al usuario.
    if (empty($errors)) {
        // Hashea la contraseña para mayor seguridad.
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Prepara la consulta SQL para insertar al nuevo usuario.
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // Ejecuta la consulta y redirige al usuario al panel de control si tiene éxito.
        if ($stmt->execute([$name, $email, $password_hash])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Hubo un error al registrar el usuario. Por favor, inténtelo de nuevo.";
        }
    }
}
?>

<div class="container">
    <h2>Registrarse</h2>
    <form action="index.php" method="post">
        <input type="hidden" name="register" value="1">
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="register_name">Nombre:</label>
            <input type="text" name="name" id="register_name" required>
        </div>
        <div class="form-group">
            <label for="register_email">Correo Electrónico:</label>
            <input type="email" name="email" id="register_email" required>
        </div>
        <div class="form-group">
            <label for="register_password">Contraseña:</label>
            <input type="password" name="password" id="register_password" required>
        </div>
        <div class="form-group">
            <label for="register_confirm_password">Confirmar Contraseña:</label>
            <input type="password" name="confirm_password" id="register_confirm_password" required>
        </div>
        <button type="submit">Registrarse</button>
    </form>
</div>
