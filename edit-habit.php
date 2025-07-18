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

// Comprueba si se ha proporcionado un ID de hábito.
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$habit_id = $_GET['id'];

// Obtiene la información del hábito.
$sql = "
    SELECT h.name, GROUP_CONCAT(hs.day_of_week) as days, hs.start_time, hs.end_time, hs.start_date
    FROM habits h
    JOIN habit_schedules hs ON h.id = hs.habit_id
    WHERE h.id = ? AND h.user_id = ?
    GROUP BY h.id
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$habit_id, $_SESSION['user_id']]);
$habit = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el hábito no existe, redirige al panel de control.
if (!$habit) {
    header("Location: dashboard.php");
    exit;
}

$selected_days = explode(',', $habit['days']);

// Inicializa un array para almacenar los errores.
$errors = [];

// Comprueba si el formulario ha sido enviado.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpia y asigna las variables del formulario.
    $habit_name = trim($_POST['habit_name']);
    $days_of_week = isset($_POST['days_of_week']) ? $_POST['days_of_week'] : [];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $start_date = $_POST['start_date'];

    // Valida el nombre del hábito.
    if (empty($habit_name)) {
        $errors[] = "El nombre del hábito es obligatorio.";
    }

    // Valida los días de la semana.
    if (empty($days_of_week)) {
        $errors[] = "Debe seleccionar al menos un día de la semana.";
    }

    // Valida la hora de inicio.
    if (empty($start_time)) {
        $errors[] = "La hora de inicio es obligatoria.";
    }

    // Valida la hora de fin.
    if (empty($end_time)) {
        $errors[] = "La hora de fin es obligatoria.";
    }

    // Valida la fecha de inicio.
    if (empty($start_date)) {
        $errors[] = "La fecha de inicio es obligatoria.";
    }

    // Si no hay errores, procede a actualizar el hábito.
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Desactiva el horario anterior.
            $sql = "UPDATE habit_schedules SET end_date = ? WHERE habit_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([date('Y-m-d'), $habit_id]);

            // Actualiza el nombre del hábito.
            $sql = "UPDATE habits SET name = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$habit_name, $habit_id]);

            // Inserta los nuevos horarios.
            foreach ($days_of_week as $day) {
                $sql = "INSERT INTO habit_schedules (habit_id, day_of_week, start_time, end_time, start_date) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$habit_id, $day, $start_time, $end_time, $start_date]);
            }

            $pdo->commit();
            header("Location: dashboard.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Hubo un error al actualizar el hábito: " . $e->getMessage();
        }
    }
}

// Incluye la cabecera de la página.
include __DIR__ . '/templates/header.php';
?>

<div class="container">
    <h2>Editar Hábito</h2>
    <form action="edit-habit.php?id=<?php echo $habit_id; ?>" method="post" class="modern-form">
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="habit_name">Nombre del Hábito:</label>
            <input type="text" name="habit_name" id="habit_name" value="<?php echo htmlspecialchars($habit['name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Días de la Semana:</label>
            <div class="days-of-week">
                <?php foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day): ?>
                    <input type="checkbox" name="days_of_week[]" value="<?php echo $day; ?>" id="day-<?php echo strtolower(substr($day, 0, 3)); ?>" <?php echo in_array($day, $selected_days) ? 'checked' : ''; ?>>
                    <label for="day-<?php echo strtolower(substr($day, 0, 3)); ?>"><?php echo substr($day, 0, 1); ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group">
            <label for="start_time">Hora de Inicio:</label>
            <input type="time" name="start_time" id="start_time" value="<?php echo $habit['start_time']; ?>" required>
        </div>
        <div class="form-group">
            <label for="end_time">Hora de Fin:</label>
            <input type="time" name="end_time" id="end_time" value="<?php echo $habit['end_time']; ?>" required>
        </div>
        <div class="form-group">
            <label for="start_date">Fecha de Inicio:</label>
            <input type="date" name="start_date" id="start_date" value="<?php echo $habit['start_date']; ?>" required>
        </div>
        <button type="submit">Actualizar Hábito</button>
    </form>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
