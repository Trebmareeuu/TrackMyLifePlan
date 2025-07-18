<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Check for "remember me" cookie
    if (isset($_COOKIE['remember_me'])) {
        list($selector, $token) = explode(':', $_COOKIE['remember_me']);
        $token = hex2bin($token);

        require_once __DIR__ . '/../src/includes/db.php';

        $sql = "SELECT * FROM remembered_users WHERE selector = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$selector]);
        $remembered_user = $stmt->fetch();

        if ($remembered_user) {
            $token_hash = hash('sha256', $token);
            if (hash_equals($token_hash, $remembered_user['token_hash'])) {
                $_SESSION['user_id'] = $remembered_user['user_id'];
            }
        }
    } else {
        header("Location: login.php");
        exit;
    }
}

require_once __DIR__ . '/../src/includes/db.php';

$sql = "SELECT name FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include __DIR__ . '/../templates/header.php';
?>

<div class="container">
    <h2>Panel de Control</h2>
    <p>Bienvenido, <?php echo htmlspecialchars($user['name']); ?>!</p>
    <p>Aquí podrás ver tus hábitos y finanzas.</p>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
