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

// Comprueba si el formulario ha sido enviado.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpia y asigna las variables del formulario.
    $habit_name = trim($_POST['habit_name']);
    $days_of_week = $_POST['days_of_week'];
    $time = $_POST['time'];

    // Valida el nombre del hábito.
    if (empty($habit_name)) {
        $errors[] = "El nombre del hábito es obligatorio.";
    }

    // Valida los días de la semana.
    if (empty($days_of_week)) {
        $errors[] = "Debe seleccionar al menos un día de la semana.";
    }

    // Valida la hora.
    if (empty($time)) {
        $errors[] = "La hora es obligatoria.";
    }

    // Si no hay errores, procede a registrar el hábito.
    if (empty($errors)) {
        // Inserta el hábito en la tabla `habits`.
        $sql = "INSERT INTO habits (user_id, name) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$_SESSION['user_id'], $habit_name])) {
            $habit_id = $pdo->lastInsertId();

            // Inserta los horarios del hábito en la tabla `habit_schedules`.
            foreach ($days_of_week as $day) {
                $sql = "INSERT INTO habit_schedules (habit_id, day_of_week, time) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$habit_id, $day, $time]);
            }

            // Redirige al usuario al panel de control.
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Hubo un error al registrar el hábito. Por favor, inténtelo de nuevo.";
        }
    }
}

// Incluye la cabecera de la página.
include __DIR__ . '/templates/header.php';
?>

<div class="container">
    <h2>Registrar Nuevo Hábito</h2>
    <form action="habits.php" method="post">
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="habit_name">Nombre del Hábito:</label>
            <input type="text" name="habit_name" id="habit_name" required>
        </div>
        <div class="form-group">
            <label>Días de la Semana:</label>
            <div>
                <input type="checkbox" name="days_of_week[]" value="Lunes" id="day-mon"> <label for="day-mon">Lunes</label>
                <input type="checkbox" name="days_of_week[]" value="Martes" id="day-tue"> <label for="day-tue">Martes</label>
                <input type="checkbox" name="days_of_week[]" value="Miércoles" id="day-wed"> <label for="day-wed">Miércoles</label>
                <input type="checkbox" name="days_of_week[]" value="Jueves" id="day-thu"> <label for="day-thu">Jueves</label>
                <input type="checkbox" name="days_of_week[]" value="Viernes" id="day-fri"> <label for="day-fri">Viernes</label>
                <input type="checkbox" name="days_of_week[]" value="Sábado" id="day-sat"> <label for="day-sat">Sábado</label>
                <input type="checkbox" name="days_of_week[]" value="Domingo" id="day-sun"> <label for="day-sun">Domingo</label>
            </div>
        </div>
        <div class="form-group">
            <label for="time">Hora:</label>
            <input type="time" name="time" id="time" required>
        </div>
        <button type="submit">Registrar Hábito</button>
    </form>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
