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

// Procesa el seguimiento de hábitos.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['track_habit'])) {
    $schedule_id = $_POST['schedule_id'];
    $status = $_POST['status'];
    $tracking_date = $_POST['tracking_date'];

    // Comprueba si ya existe un registro de seguimiento para este hábito en esta fecha.
    $sql = "SELECT id FROM habit_tracking WHERE schedule_id = ? AND tracking_date = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$schedule_id, $tracking_date]);
    $existing_tracking = $stmt->fetch();

    if ($existing_tracking) {
        // Actualiza el registro existente.
        $sql = "UPDATE habit_tracking SET status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $existing_tracking['id']]);
    } else {
        // Inserta un nuevo registro.
        $sql = "INSERT INTO habit_tracking (schedule_id, tracking_date, status) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$schedule_id, $tracking_date, $status]);
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Define la vista actual (día, semana, mes, año).
$view = isset($_GET['view']) ? $_GET['view'] : 'week';
// Define la fecha actual.
$date = isset($_GET['date']) ? new DateTime($_GET['date']) : new DateTime();

// Construye la consulta SQL base.
$sql = "
    SELECT h.id, h.name, hs.id as schedule_id, hs.day_of_week, hs.start_time, hs.end_time
    FROM habits h
    JOIN habit_schedules hs ON h.id = hs.habit_id
    WHERE h.user_id = ?
";
$params = [$_SESSION['user_id']];

// Añade las condiciones de fecha según la vista.
switch ($view) {
    case 'day':
        $sql .= " AND hs.day_of_week = ? AND hs.start_date <= ? AND (hs.end_date IS NULL OR hs.end_date >= ?)";
        $params[] = $date->format('l');
        $params[] = $date->format('Y-m-d');
        $params[] = $date->format('Y-m-d');
        break;
    case 'week':
        $start_of_week = (clone $date)->modify('monday this week');
        $end_of_week = (clone $date)->modify('sunday this week');
        $sql .= " AND hs.start_date <= ? AND (hs.end_date IS NULL OR hs.end_date >= ?)";
        $params[] = $end_of_week->format('Y-m-d');
        $params[] = $start_of_week->format('Y-m-d');
        break;
    // ... (otros casos para mes y año)
}

$sql .= " ORDER BY hs.start_time, hs.day_of_week";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$habits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiza los hábitos por día y hora.
$schedule = [];
$hours = [];
foreach ($habits as $habit) {
    $start_hour = date('H', strtotime($habit['start_time']));
    $end_hour = date('H', strtotime($habit['end_time']));
    for ($h = $start_hour; $h <= $end_hour; $h++) {
        $time_key = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
        if (!in_array($time_key, $hours)) {
            $hours[] = $time_key;
        }
        $schedule[$time_key][$habit['day_of_week']] = [
            'id' => $habit['id'],
            'schedule_id' => $habit['schedule_id'],
            'name' => $habit['name']
        ];
    }
}
sort($hours);

// Incluye la cabecera de la página.
include __DIR__ . '/templates/header.php';
?>

<div class="container">
    <div class="schedule-header">
        <h2>Horario</h2>
        <div class="schedule-nav">
            <a href="?view=day&date=<?php echo $date->format('Y-m-d'); ?>" class="<?php echo $view === 'day' ? 'active' : ''; ?>">Día</a>
            <a href="?view=week&date=<?php echo $date->format('Y-m-d'); ?>" class="<?php echo $view === 'week' ? 'active' : ''; ?>">Semana</a>
            <a href="?view=month&date=<?php echo $date->format('Y-m-d'); ?>" class="<?php echo $view === 'month' ? 'active' : ''; ?>">Mes</a>
            <a href="?view=year&date=<?php echo $date->format('Y-m-d'); ?>" class="<?php echo $view === 'year' ? 'active' : ''; ?>">Año</a>
        </div>
        <div class="date-nav">
            <a href="?view=<?php echo $view; ?>&date=<?php echo (clone $date)->modify('-1 ' . ($view == 'day' ? 'day' : $view) )->format('Y-m-d'); ?>">&lt;</a>
            <span><?php echo $date->format('d M Y'); ?></span>
            <a href="?view=<?php echo $view; ?>&date=<?php echo (clone $date)->modify('+1 ' . ($view == 'day' ? 'day' : $view) )->format('Y-m-d'); ?>">&gt;</a>
        </div>
    </div>
    <table class="schedule-table">
        <thead>
            <tr>
                <th>Hora</th>
                <?php if ($view === 'day'): ?>
                    <th><?php echo $date->format('l'); ?></th>
                <?php else: ?>
                    <th>Lunes</th>
                    <th>Martes</th>
                    <th>Miércoles</th>
                    <th>Jueves</th>
                    <th>Viernes</th>
                    <th>Sábado</th>
                    <th>Domingo</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hours as $hour): ?>
                <tr>
                    <td><?php echo $hour; ?></td>
                    <?php
                    $days_to_display = ($view === 'day') ? [$date->format('l')] : ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                    foreach ($days_to_display as $day_index => $day):
                        $current_date = ($view === 'week') ? (clone $start_of_week)->modify("+$day_index days") : $date;
                    ?>
                        <td>
                            <?php if (isset($schedule[$hour][$day])):
                                $habit_data = $schedule[$hour][$day];
                            ?>
                                <div class="habit-item">
                                    <?php echo htmlspecialchars($habit_data['name']); ?>
                                    <a href="edit-habit.php?id=<?php echo $habit_data['id']; ?>" class="edit-btn">Editar</a>
                                    <div class="tracking-buttons">
                                        <form action="schedule.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
                                            <input type="hidden" name="track_habit" value="1">
                                            <input type="hidden" name="schedule_id" value="<?php echo $habit_data['schedule_id']; ?>">
                                            <input type="hidden" name="tracking_date" value="<?php echo $current_date->format('Y-m-d'); ?>">
                                            <button type="submit" name="status" value="Realizado" class="track-btn complete-btn">✓</button>
                                            <button type="submit" name="status" value="No Realizado" class="track-btn incomplete-btn">✗</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
