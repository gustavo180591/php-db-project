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
    
    // Obtener categorías
    $stmt = $pdo->query("SELECT * FROM test_categories WHERE estado = 1 ORDER BY nombre");
    $categories = $stmt->fetchAll();
    
    // Obtener tipos de tests con sus categorías
    $stmt = $pdo->query("
        SELECT tt.*, 
               GROUP_CONCAT(tc.nombre SEPARATOR ', ') as categorias,
               GROUP_CONCAT(tc.icono SEPARATOR ', ') as iconos
        FROM test_types tt
        LEFT JOIN test_type_categories ttc ON tt.id = ttc.test_type_id
        LEFT JOIN test_categories tc ON ttc.category_id = tc.id
        WHERE tt.estado = 1
        GROUP BY tt.id
        ORDER BY tt.nombre
    ");
    $test_types = $stmt->fetchAll();
    
    // Verificar permisos para cada test
    $stmt = $pdo->prepare("
        SELECT tp.permiso 
        FROM test_permissions tp 
        WHERE tp.rol_id = ? AND tp.test_type_id = ?
    ");
    
    foreach ($test_types as &$test) {
        $stmt->execute([$_SESSION['rol_id'], $test['id']]);
        $permiso = $stmt->fetch()['permiso'] ?? 'ver';
        $test['permiso'] = $permiso;
    }
    
    // Obtener configuración del test si existe
    $test_config = null;
    if ($test_id) {
        $stmt = $pdo->prepare("SELECT * FROM test_types WHERE id = ?");
        $stmt->execute([$test_id]);
        $test = $stmt->fetch();
        if ($test) {
            $test_config = json_decode($test['configuracion'], true);
        }
    }
    
    // Obtener rangos de Cooper si es necesario
    if (isset($test) && $test['nombre'] === 'Cooper') {
        $stmt = $pdo->query("SELECT * FROM cooper_ranges ORDER BY sexo, edad_min");
        $cooper_ranges = $stmt->fetchAll();
    }
    
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
                                    
                                    <?php if ($action === 'edit' && isset($test_config)): ?>
                                    <div class="alert alert-info">
                                        <h5>Información del Test</h5>
                                        <p><?php echo htmlspecialchars($test_config['descripcion']); ?></p>
                                        <?php if (isset($test_config['unidad'])): ?>
                                            <p><strong>Unidad de medida:</strong> <?php echo $test_config['unidad']; ?></p>
                                        <?php endif; ?>
                                        <?php if (isset($test_config['tiempo'])): ?>
                                            <p><strong>Tiempo:</strong> <?php echo $test_config['tiempo']; ?></p>
                                        <?php endif; ?>
                                        <?php if (isset($test_config['edad_min'])): ?>
                                            <p><strong>Edad mínima:</strong> <?php echo $test_config['edad_min']; ?> años</p>
                                        <?php endif; ?>
                                        <?php if (isset($test_config['edad_max'])): ?>
                                            <p><strong>Edad máxima:</strong> <?php echo $test_config['edad_max']; ?> años</p>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="/evaluador/tests.php" class="btn btn-secondary">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($test_id): ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($test['nombre']); ?></h5>
                                    <small class="text-muted">
                                        <?php 
                                        if (!empty($test['categorias'])) {
                                            $icons = explode(', ', $test['iconos']);
                                            $categories = explode(', ', $test['categorias']);
                                            foreach ($categories as $i => $category) {
                                                echo '<i class="' . $icons[$i] . '"></i> ' . $category . ' ';
                                            }
                                        }
                                        ?>
                                    </small>
                                </div>
                                <div>
                                    <?php if ($test['permiso'] === 'administrar'): ?>
                                        <a href="/evaluador/tests.php?action=edit&id=<?php echo $test_id; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Editar Test
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($test['permiso'] !== 'ver'): ?>
                                        <a href="/evaluador/evaluar.php?id=<?php echo $test_id; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-running"></i> Evaluar Test
                                        </a>
                                    <?php endif; ?>
                                    <a href="/evaluador/evolucion.php?id=<?php echo $test_id; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-chart-line"></i> Evolución
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
                                                <?php if ($test['nombre'] === 'Cooper'): ?>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php 
                                                    // Buscar el nivel correspondiente
                                                    $nivel = 'No evaluado';
                                                    foreach ($cooper_ranges as $rango) {
                                                        if ($rango['distancia_min'] <= $pregunta['puntos'] && $pregunta['puntos'] <= $rango['distancia_max']) {
                                                            $nivel = $rango['nivel'];
                                                            break;
                                                        }
                                                    }
                                                    echo $nivel;
                                                    ?>
                                                </span>
                                            </td>
                                        <?php else: ?>
                                            <td><?php echo $pregunta['puntos']; ?></td>
                                        <?php endif; ?>
                                                <td>
                                                    <a href="/evaluador/preguntas.php?action=edit&id=<?php echo $pregunta['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="/evaluador/preguntas.php?action=delete&id=<?php echo $pregunta['id']; ?>" 
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
                                <a href="/evaluador/tests.php?action=add" class="btn btn-primary">
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
                                                    <a href="/evaluador/tests.php?id=<?php echo $test['id']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                    <a href="/evaluador/tests.php?action=edit&id=<?php echo $test['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="/evaluador/tests.php?action=delete&id=<?php echo $test['id']; ?>" 
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