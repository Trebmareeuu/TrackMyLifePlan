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
$sql = "SELECT name, email, avatar FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

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

    // Procesa la subida del avatar.
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_extensions)) {
            if ($_FILES['avatar']['size'] < 5000000) { // 5MB
                $new_avatar_name = uniqid('avatar_', true) . '.' . $file_extension;
                $upload_path = __DIR__ . '/img/avatars/' . $new_avatar_name;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                    // Elimina el avatar anterior si existe.
                    if ($user['avatar'] && file_exists(__DIR__ . '/img/avatars/' . $user['avatar'])) {
                        unlink(__DIR__ . '/img/avatars/' . $user['avatar']);
                    }
                    $user['avatar'] = $new_avatar_name;
                } else {
                    $errors[] = "Hubo un error al subir el avatar. Asegúrese de que el directorio 'img/avatars' tiene permisos de escritura.";
                }
            } else {
                $errors[] = "El archivo es demasiado grande. El tamaño máximo es de 5MB.";
            }
        } else {
            $errors[] = "El formato del archivo no es válido. Solo se permiten JPG, JPEG, PNG y GIF.";
        }
    }

    // Si no hay errores, procede a actualizar el perfil.
    if (empty($errors)) {
        $sql = "UPDATE users SET name = ?, email = ?, avatar = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$name, $email, $user['avatar'], $_SESSION['user_id']])) {
            $_SESSION['user_avatar'] = $user['avatar'];
            $success_message = "Perfil actualizado correctamente.";
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
    <form action="profile.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Nombre:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="avatar">Avatar:</label>
            <input type="file" name="avatar" id="avatar">
            <?php if ($user['avatar']): ?>
                <img src="img/avatars/<?php echo $user['avatar']; ?>" alt="Avatar" class="current-avatar">
            <?php endif; ?>
        </div>
        <button type="submit">Actualizar Perfil</button>
    </form>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
