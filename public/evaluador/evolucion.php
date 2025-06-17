<?php
require_once '../config/database.php';
checkAuth();
checkRole(2); // Solo evaluadores pueden acceder

$test_id = $_GET['id'] ?? null;
$persona_id = $_GET['persona_id'] ?? null;
$error = '';
$message = '';

try {
    $pdo = getConnection();
    
    // Obtener información del test
    $stmt = $pdo->prepare("SELECT * FROM test_types WHERE id = ?");
    $stmt->execute([$test_id]);
    $test = $stmt->fetch();
    
    if (!$test) {
        throw new Exception("Test no encontrado");
    }
    
    // Obtener configuración
    $config = json_decode($test['configuracion'], true);
    
    // Obtener resultados históricos
    $stmt = $pdo->prepare("
        SELECT tr.*, p.nombre, p.apellido 
        FROM test_results tr 
        JOIN personas p ON tr.persona_id = p.id 
        WHERE tr.test_type_id = ? 
        ORDER BY tr.fecha DESC
    ");
    $stmt->execute([$test_id]);
    $results = $stmt->fetchAll();
    
    // Obtener datos para gráficos
    $chartData = [];
    foreach ($results as $result) {
        $date = date('Y-m', strtotime($result['fecha']));
        if (!isset($chartData[$date])) {
            $chartData[$date] = [];
        }
        $chartData[$date][] = $result['valor'];
    }
    
    // Preparar datos para el gráfico
    $labels = array_keys($chartData);
    $values = array_map(function($data) {
        return array_sum($data) / count($data); // Promedio mensual
    }, $chartData);
    
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evolución de <?php echo htmlspecialchars($test['nombre']); ?> - Sistema de Captación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar">
                <!-- El sidebar ya está en index.php -->
            </div>
            
            <div class="col-md-9 col-lg-10">
                <div class="container-fluid">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Evolución de <?php echo htmlspecialchars($test['nombre']); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <canvas id="evolutionChart"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Persona</th>
                                                    <th>Valor</th>
                                                    <th>Nivel</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($results as $result): ?>
                                                    <tr>
                                                        <td><?php echo date('d/m/Y', strtotime($result['fecha'])); ?></td>
                                                        <td><?php echo htmlspecialchars($result['nombre'] . ' ' . $result['apellido']); ?></td>
                                                        <td><?php echo $result['valor']; ?> <?php echo $config['unidad_principal']; ?></td>
                                                        <td>
                                                            <?php 
                                                            if (isset($config['rango']) && isset($config['rango'][$result['sexo']])) {
                                                                foreach ($config['rango'][$result['sexo']] as $rango) {
                                                                    if ($result['valor'] >= $rango['min'] && $result['valor'] <= $rango['max']) {
                                                                        echo '<span class="badge bg-info">' . $rango['nivel'] . '</span>';
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-code.js" crossorigin="anonymous"></script>
    <script>
        // Configurar gráfico de evolución
        const ctx = document.getElementById('evolutionChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Evolución Mensual',
                    data: <?php echo json_encode($values); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
