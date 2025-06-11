<?php
require_once '../config/database.php';
checkAuth();
checkRole(2); // Solo evaluadores pueden acceder

// Manejar acciones CRUD
$action = $_GET['action'] ?? '';
$test_id = $_GET['id'] ?? null;

// Mensajes de éxito/error
$message = '';
$error = '';

try {
    $pdo = getConnection();
    
    // Obtener todos los tests
    $stmt = $pdo->query("SELECT * FROM tests");
    $tests = $stmt->fetchAll();
    
    // Manejar formularios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $estado = isset($_POST['estado']) ? 1 : 0;
        
        if (empty($nombre)) {
            $error = "El nombre es requerido";
        } else {
            if ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE tests SET nombre = ?, descripcion = ?, estado = ? WHERE id = ?");
                $stmt->execute([$nombre, $descripcion, $estado, $test_id]);
                $message = "Test actualizado exitosamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO tests (nombre, descripcion, estado) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $estado]);
                $test_id = $pdo->lastInsertId();
                $message = "Test creado exitosamente";
            }
        }
    }
    
    // Manejar eliminación
    if ($action === 'delete' && $test_id) {
        // Verificar si el test tiene preguntas o asignaciones
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM preguntas WHERE test_id = ?");
        $stmt->execute([$test_id]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            $error = "No se puede eliminar este test porque tiene preguntas asociadas";
        } else {
            $stmt = $pdo->prepare("DELETE FROM tests WHERE id = ?");
            $stmt->execute([$test_id]);
            $message = "Test eliminado exitosamente";
        }
    }
    
    // Obtener test para editar si existe
    if ($action === 'edit' && $test_id) {
        $stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
        $stmt->execute([$test_id]);
        $test = $stmt->fetch();
    }
    
    // Obtener preguntas si estamos viendo un test específico
    if ($test_id && $action !== 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM preguntas WHERE test_id = ? ORDER BY numero");
        $stmt->execute([$test_id]);
        $preguntas = $stmt->fetchAll();
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
    <title>Gestión de Tests - Sistema de Captación</title>
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
                                <?php echo $action === 'add' ? 'Nuevo Test' : 'Editar Test'; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo isset($test) ? htmlspecialchars($test['nombre']) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3">
                                            <?php echo isset($test) ? htmlspecialchars($test['descripcion']) : ''; ?>
                                        </textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                                   <?php echo isset($test) && $test['estado'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="estado">
                                                Test Activo
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="." class="btn btn-secondary">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($test_id): ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($test['nombre']); ?></h5>
                                <div>
                                    <a href="?action=edit&id=<?php echo $test_id; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Editar Test
                                    </a>
                                    <a href="preguntas.php?id=<?php echo $test_id; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-plus"></i> Agregar Pregunta
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Número</th>
                                            <th>Pregunta</th>
                                            <th>Tipo</th>
                                            <th>Puntos</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($preguntas as $pregunta): ?>
                                            <tr>
                                                <td><?php echo $pregunta['numero']; ?></td>
                                                <td><?php echo htmlspecialchars($pregunta['pregunta']); ?></td>
                                                <td><?php echo ucfirst($pregunta['tipo']); ?></td>
                                                <td><?php echo $pregunta['puntos']; ?></td>
                                                <td>
                                                    <a href="preguntas.php?action=edit&id=<?php echo $pregunta['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="preguntas.php?action=delete&id=<?php echo $pregunta['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar esta pregunta?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Lista de Tests</h5>
                                <a href="?action=add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Nuevo Test
                                </a>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tests as $test): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($test['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($test['descripcion']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $test['estado'] ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $test['estado'] ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?id=<?php echo $test['id']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                    <a href="?action=edit&id=<?php echo $test['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?php echo $test['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar este test?')">
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
