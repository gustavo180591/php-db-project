<?php
require_once '../config/database.php';
checkAuth();
checkRole(2); // Solo evaluadores pueden acceder

// Manejar acciones CRUD
$action = $_GET['action'] ?? '';
$asignacion_id = $_GET['id'] ?? null;

// Mensajes de éxito/error
$message = '';
$error = '';

try {
    $pdo = getConnection();
    
    // Obtener todos los tests activos
    $stmt = $pdo->query("SELECT * FROM tests WHERE estado = 1");
    $tests = $stmt->fetchAll();
    
    // Obtener todas las personas
    $stmt = $pdo->query("SELECT * FROM personas WHERE estado = 1");
    $personas = $stmt->fetchAll();
    
    // Obtener todas las asignaciones
    $stmt = $pdo->query("SELECT a.*, p.nombre as persona_nombre, t.nombre as test_nombre 
                         FROM asignaciones_tests a 
                         JOIN personas p ON a.persona_id = p.id 
                         JOIN tests t ON a.test_id = t.id");
    $asignaciones = $stmt->fetchAll();
    
    // Manejar formularios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $persona_id = $_POST['persona_id'] ?? '';
        $test_id = $_POST['test_id'] ?? '';
        $estado = $_POST['estado'] ?? 'pendiente';
        
        if (empty($persona_id) || empty($test_id)) {
            $error = "Todos los campos son requeridos";
        } else {
            if ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE asignaciones_tests SET persona_id = ?, test_id = ?, estado = ? WHERE id = ?");
                $stmt->execute([$persona_id, $test_id, $estado, $asignacion_id]);
                $message = "Asignación actualizada exitosamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO asignaciones_tests (persona_id, test_id, estado, evaluador_id) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([$persona_id, $test_id, $estado, $_SESSION['user_id']]);
                $message = "Asignación creada exitosamente";
            }
        }
    }
    
    // Manejar eliminación
    if ($action === 'delete' && $asignacion_id) {
        $stmt = $pdo->prepare("DELETE FROM asignaciones_tests WHERE id = ?");
        $stmt->execute([$asignacion_id]);
        $message = "Asignación eliminada exitosamente";
    }
    
    // Obtener asignación para editar si existe
    if ($action === 'edit' && $asignacion_id) {
        $stmt = $pdo->prepare("SELECT * FROM asignaciones_tests WHERE id = ?");
        $stmt->execute([$asignacion_id]);
        $asignacion = $stmt->fetch();
    }
    
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignación de Tests - Sistema de Captación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar">
                <!-- El sidebar ya está en index.php -->
            </div>
            
            <div class="col-md-9 col-lg-10">
                <div class="container-fluid">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($action === 'add' || $action === 'edit'): ?>
                        <div class="card">
                            <div class="card-header">
                                <?php echo $action === 'add' ? 'Nueva Asignación' : 'Editar Asignación'; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="persona_id" class="form-label">Persona</label>
                                        <select class="form-select" id="persona_id" name="persona_id" required>
                                            <option value="">Seleccione una persona</option>
                                            <?php foreach ($personas as $persona): ?>
                                                <option value="<?php echo $persona['id']; ?>"
                                                        <?php echo isset($asignacion) && $asignacion['persona_id'] == $persona['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="test_id" class="form-label">Test</label>
                                        <select class="form-select" id="test_id" name="test_id" required>
                                            <option value="">Seleccione un test</option>
                                            <?php foreach ($tests as $test): ?>
                                                <option value="<?php echo $test['id']; ?>"
                                                        <?php echo isset($asignacion) && $asignacion['test_id'] == $test['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($test['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select class="form-select" id="estado" name="estado" required>
                                            <option value="pendiente" <?php echo isset($asignacion) && $asignacion['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="en_progreso" <?php echo isset($asignacion) && $asignacion['estado'] == 'en_progreso' ? 'selected' : ''; ?>>En Progreso</option>
                                            <option value="completado" <?php echo isset($asignacion) && $asignacion['estado'] == 'completado' ? 'selected' : ''; ?>>Completado</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="." class="btn btn-secondary">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Asignaciones de Tests</h5>
                                <a href="?action=add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Nueva Asignación
                                </a>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Persona</th>
                                            <th>Test</th>
                                            <th>Estado</th>
                                            <th>Fecha de Asignación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($asignaciones as $asignacion): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($asignacion['persona_nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($asignacion['test_nombre']); ?></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        echo ($asignacion['estado'] == 'pendiente' ? 'bg-warning' : 
                                                             ($asignacion['estado'] == 'en_progreso' ? 'bg-info' : 
                                                             'bg-success')); ?>">
                                                        <?php echo ucfirst($asignacion['estado']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($asignacion['fecha_asignacion'])); ?></td>
                                                <td>
                                                    <a href="?action=edit&id=<?php echo $asignacion['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?php echo $asignacion['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar esta asignación?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-code.js" crossorigin="anonymous"></script>
</body>
</html>
