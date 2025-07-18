<?php
// Inicia la sesión para poder utilizar variables de sesión.
session_start();

// Requiere el archivo de conexión a la base de datos.
require_once __DIR__ . '/src/includes/db.php';

// Inicializa un array para almacenar los errores.
$errors = [];

// Comprueba si el formulario ha sido enviado.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpia y asigna las variables del formulario.
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    // Valida el correo electrónico.
    if (empty($email)) {
        $errors[] = "El correo electrónico es obligatorio.";
    }

    // Valida la contraseña.
    if (empty($password)) {
        $errors[] = "La contraseña es obligatoria.";
    }

    // Si no hay errores, procede a iniciar sesión.
    if (empty($errors)) {
        // Prepara la consulta SQL para obtener el usuario por su correo electrónico.
        $sql = "SELECT id, password FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Comprueba si el usuario existe y si la contraseña es correcta.
        if ($user && password_verify($password, $user['password'])) {
            // Almacena el ID del usuario en la sesión.
            $_SESSION['user_id'] = $user['id'];

            // Si el usuario ha marcado "Recordarme", crea una cookie segura.
            if ($remember) {
                // Genera un selector y un token aleatorios.
                $selector = bin2hex(random_bytes(16));
                $token = random_bytes(32);
                // Hashea el token para almacenarlo en la base de datos.
                $token_hash = hash('sha256', $token);
                // Establece la fecha de caducidad de la cookie para dentro de un mes.
                $expires = new DateTime('+1 month');

                // Prepara la consulta SQL para insertar el token de "Recordarme".
                $sql = "INSERT INTO remembered_users (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user['id'], $selector, $token_hash, $expires->format('Y-m-d H:i:s')]);

                // Establece la cookie en el navegador del usuario.
                setcookie('remember_me', $selector . ':' . bin2hex($token), $expires->getTimestamp(), '/');
            }

            // Redirige al usuario al panel de control.
            header("Location: dashboard.php");
            exit;
        } else {
            // Si las credenciales no son válidas, añade un error.
            $errors[] = "Las credenciales no son válidas.";
        }
    }
}

// Incluye la cabecera de la página.
include __DIR__ . '/templates/header.php';
?>

<div class="container">
    <h2>Iniciar Sesión</h2>
    <form action="login.php" method="post">
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Recordarme</label>
        </div>
        <button type="submit">Iniciar Sesión</button>
    </form>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
