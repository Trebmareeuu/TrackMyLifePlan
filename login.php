<?php
// Inicia la sesión para poder utilizar variables de sesión.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Requiere el archivo de conexión a la base de datos.
require_once __DIR__ . '/src/includes/db.php';

// Define el número máximo de intentos de inicio de sesión fallidos.
const MAX_LOGIN_ATTEMPTS = 5;
// Define el tiempo de bloqueo en segundos.
const LOCKOUT_TIME = 900; // 15 minutos

// Inicializa un array para almacenar los errores.
$errors = [];

// Comprueba si el formulario ha sido enviado.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Limita la frecuencia de las solicitudes de inicio de sesión.
    if (isset($_SESSION['last_login_attempt']) && (time() - $_SESSION['last_login_attempt'] < 2)) {
        $errors[] = "Por favor, espere un momento antes de volver a intentarlo.";
    } else {
        $_SESSION['last_login_attempt'] = time();

        // Limpia y asigna las variables del formulario.
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);

        // Obtiene la dirección IP del usuario.
        $ip_address = $_SERVER['REMOTE_ADDR'];

        // Prepara la consulta SQL para obtener los intentos de inicio de sesión fallidos.
        $sql = "SELECT COUNT(*) FROM failed_login_attempts WHERE ip_address = ? AND timestamp > (NOW() - INTERVAL ? SECOND)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ip_address, LOCKOUT_TIME]);
        $failed_attempts = $stmt->fetchColumn();

        // Si hay demasiados intentos de inicio de sesión fallidos, bloquea al usuario.
        if ($failed_attempts >= MAX_LOGIN_ATTEMPTS) {
            $errors[] = "Ha excedido el número de intentos de inicio de sesión. Por favor, inténtelo de nuevo más tarde.";
        } else {
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
                    // Si el inicio de sesión es exitoso, elimina los intentos de inicio de sesión fallidos.
                    $sql = "DELETE FROM failed_login_attempts WHERE ip_address = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$ip_address]);

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
                    // Si las credenciales no son válidas, añade un error y registra el intento de inicio de sesión fallido.
                    $errors[] = "Las credenciales no son válidas.";

                    $sql = "INSERT INTO failed_login_attempts (ip_address, timestamp) VALUES (?, NOW())";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$ip_address]);
                }
            }
        }
    }
}
?>

<div class="container">
    <h2>Iniciar Sesión</h2>
    <form action="index.php" method="post">
        <input type="hidden" name="login" value="1">
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
