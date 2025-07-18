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

// Obtiene los hábitos del usuario.
$sql = "
    SELECT h.name, hs.day_of_week, hs.time
    FROM habits h
    JOIN habit_schedules hs ON h.id = hs.habit_id
    WHERE h.user_id = ?
    ORDER BY hs.time, hs.day_of_week
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$habits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiza los hábitos por día y hora.
$schedule = [];
foreach ($habits as $habit) {
    $time = date('H:00', strtotime($habit['time']));
    $schedule[$time][$habit['day_of_week']] = $habit['name'];
}

// Incluye la cabecera de la página.
include __DIR__ . '/templates/header.php';
?>

<div class="container">
    <h2>Horario Semanal</h2>
    <table class="schedule-table">
        <thead>
            <tr>
                <th>Hora</th>
                <th>Lunes</th>
                <th>Martes</th>
                <th>Miércoles</th>
                <th>Jueves</th>
                <th>Viernes</th>
                <th>Sábado</th>
                <th>Domingo</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($hour = 7; $hour <= 23; $hour++): ?>
                <?php $time_key = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00'; ?>
                <tr>
                    <td><?php echo $time_key; ?></td>
                    <?php foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day): ?>
                        <td>
                            <?php if (isset($schedule[$time_key][$day])): ?>
                                <div class="habit-item">
                                    <?php echo htmlspecialchars($schedule[$time_key][$day]); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
