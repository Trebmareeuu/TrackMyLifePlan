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

// Define la vista actual (día, semana, mes, año).
$view = isset($_GET['view']) ? $_GET['view'] : 'week';
// Define la fecha actual.
$date = isset($_GET['date']) ? new DateTime($_GET['date']) : new DateTime();

// Construye la consulta SQL base.
$sql = "
    SELECT h.id, h.name, hs.day_of_week, hs.start_time
    FROM habits h
    JOIN habit_schedules hs ON h.id = hs.habit_id
    WHERE h.user_id = ?
";
$params = [$_SESSION['user_id']];

// Añade las condiciones de fecha según la vista.
switch ($view) {
    case 'day':
        $sql .= " AND hs.start_date <= ? AND (hs.end_date IS NULL OR hs.end_date >= ?)";
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
    case 'month':
        $start_of_month = (clone $date)->modify('first day of this month');
        $end_of_month = (clone $date)->modify('last day of this month');
        $sql .= " AND hs.start_date <= ? AND (hs.end_date IS NULL OR hs.end_date >= ?)";
        $params[] = $end_of_month->format('Y-m-d');
        $params[] = $start_of_month->format('Y-m-d');
        break;
    case 'year':
        $start_of_year = (clone $date)->modify('first day of january this year');
        $end_of_year = (clone $date)->modify('last day of december this year');
        $sql .= " AND hs.start_date <= ? AND (hs.end_date IS NULL OR hs.end_date >= ?)";
        $params[] = $end_of_year->format('Y-m-d');
        $params[] = $start_of_year->format('Y-m-d');
        break;
}

$sql .= " ORDER BY hs.start_time, hs.day_of_week";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$habits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiza los hábitos por día y hora.
$schedule = [];
foreach ($habits as $habit) {
    $time = date('H:00', strtotime($habit['start_time']));
    $schedule[$time][$habit['day_of_week']] = [
        'id' => $habit['id'],
        'name' => $habit['name']
    ];
}

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
            <a href="?view=<?php echo $view; ?>&date=<?php echo (clone $date)->modify('-1 ' . $view)->format('Y-m-d'); ?>">&lt;</a>
            <span><?php echo $date->format('d M Y'); ?></span>
            <a href="?view=<?php echo $view; ?>&date=<?php echo (clone $date)->modify('+1 ' . $view)->format('Y-m-d'); ?>">&gt;</a>
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
            <?php for ($hour = 7; $hour <= 23; $hour++): ?>
                <?php $time_key = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00'; ?>
                <tr>
                    <td><?php echo $time_key; ?></td>
                    <?php if ($view === 'day'): ?>
                        <td>
                            <?php if (isset($schedule[$time_key][$date->format('l')])): ?>
                                <div class="habit-item">
                                    <?php echo htmlspecialchars($schedule[$time_key][$date->format('l')]['name']); ?>
                                    <a href="edit-habit.php?id=<?php echo $schedule[$time_key][$date->format('l')]['id']; ?>" class="edit-btn">Editar</a>
                                </div>
                            <?php endif; ?>
                        </td>
                    <?php else: ?>
                        <?php foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day): ?>
                            <td>
                                <?php if (isset($schedule[$time_key][$day])): ?>
                                    <div class="habit-item">
                                        <?php echo htmlspecialchars($schedule[$time_key][$day]['name']); ?>
                                        <a href="edit-habit.php?id=<?php echo $schedule[$time_key][$day]['id']; ?>" class="edit-btn">Editar</a>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
