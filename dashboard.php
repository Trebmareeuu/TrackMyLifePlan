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

// Obtiene el nombre del usuario.
$sql = "SELECT name FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Obtiene las estadísticas de cumplimiento de hábitos.
$sql = "
    SELECT
        SUM(CASE WHEN status = 'Realizado' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'No Realizado' THEN 1 ELSE 0 END) as not_completed
    FROM habit_tracking ht
    JOIN habit_schedules hs ON ht.schedule_id = hs.id
    JOIN habits h ON hs.habit_id = h.id
    WHERE h.user_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$habit_stats = $stmt->fetch(PDO::FETCH_ASSOC);

$completed = $habit_stats['completed'] ?? 0;
$not_completed = $habit_stats['not_completed'] ?? 0;

// Incluye la cabecera de la página.
include __DIR__ . '/templates/header.php';
?>

<div class="container">
    <h2>Panel de Control</h2>
    <p>Bienvenido, <?php echo htmlspecialchars($user['name']); ?>!</p>

    <div class="stats-container">
        <div class="chart-container">
            <h3>Cumplimiento de Hábitos</h3>
            <canvas id="habit-chart"></canvas>
        </div>
    </div>
</div>

<script src="js/vendor/chart.js"></script>
<script>
    const habitChartCtx = document.getElementById('habit-chart').getContext('2d');
    new Chart(habitChartCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completados', 'No Completados'],
            datasets: [{
                label: 'Cumplimiento de Hábitos',
                data: [<?php echo $completed; ?>, <?php echo $not_completed; ?>],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(220, 53, 69, 0.7)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>

<?php
// Incluye el pie de página.
include __DIR__ . '/templates/footer.php';
?>
