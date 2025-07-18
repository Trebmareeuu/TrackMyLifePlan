<?php
session_start();
require_once __DIR__ . '/../src/includes/db.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    if (empty($email)) {
        $errors[] = "El correo electrónico es obligatorio.";
    }

    if (empty($password)) {
        $errors[] = "La contraseña es obligatoria.";
    }

    if (empty($errors)) {
        $sql = "SELECT id, password FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];

            if ($remember) {
                $selector = bin2hex(random_bytes(16));
                $token = random_bytes(32);
                $token_hash = hash('sha256', $token);
                $expires = new DateTime('+1 month');

                $sql = "INSERT INTO remembered_users (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user['id'], $selector, $token_hash, $expires->format('Y-m-d H:i:s')]);

                setcookie('remember_me', $selector . ':' . bin2hex($token), $expires->getTimestamp(), '/');
            }

            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Las credenciales no son válidas.";
        }
    }
}

include __DIR__ . '/../templates/header.php';
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

<?php include __DIR__ . '/../templates/footer.php'; ?>
