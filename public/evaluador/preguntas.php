<?php
require_once '../config/database.php';
checkAuth();
checkRole(2); // Solo evaluadores pueden acceder

// Manejar acciones CRUD
$action = $_GET['action'] ?? '';
$pregunta_id = $_GET['id'] ?? null;
$test_id = $_GET['test_id'] ?? null;

// Mensajes de éxito/error
$message = '';
$error = '';

try {
    $pdo = getConnection();
    
    // Obtener test actual
    if ($test_id) {
        $stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
        $stmt->execute([$test_id]);
        $test = $stmt->fetch();
        
        if (!$test) {
            throw new Exception("Test no encontrado");
        }
    }
    
    // Obtener todas las preguntas del test
    if ($test_id) {
        $stmt = $pdo->prepare("SELECT * FROM preguntas WHERE test_id = ? ORDER BY numero");
        $stmt->execute([$test_id]);
        $preguntas = $stmt->fetchAll();
    }
    
    // Manejar formularios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $numero = $_POST['numero'] ?? '';
        $pregunta = $_POST['pregunta'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $opciones = $_POST['opciones'] ?? '';
        $puntos = $_POST['puntos'] ?? '';
        $estado = isset($_POST['estado']) ? 1 : 0;
        
        if (empty($numero) || empty($pregunta) || empty($tipo) || empty($puntos)) {
            $error = "Todos los campos requeridos son obligatorios";
        } else {
            if ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE preguntas SET numero = ?, pregunta = ?, tipo = ?, opciones = ?, puntos = ?, estado = ? WHERE id = ?");
                $stmt->execute([$numero, $pregunta, $tipo, $opciones, $puntos, $estado, $pregunta_id]);
                $message = "Pregunta actualizada exitosamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO preguntas (test_id, numero, pregunta, tipo, opciones, puntos, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$test_id, $numero, $pregunta, $tipo, $opciones, $puntos, $estado]);
                $message = "Pregunta creada exitosamente";
            }
        }
    }
    
    // Manejar eliminación
    if ($action === 'delete' && $pregunta_id) {
        $stmt = $pdo->prepare("DELETE FROM preguntas WHERE id = ?");
        $stmt->execute([$pregunta_id]);
        $message = "Pregunta eliminada exitosamente";
    }
    
    // Obtener pregunta para editar si existe
    if ($action === 'edit' && $pregunta_id) {
        $stmt = $pdo->prepare("SELECT * FROM preguntas WHERE id = ?");
        $stmt->execute([$pregunta_id]);
        $pregunta = $stmt->fetch();
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
    <title>Gestión de Preguntas - Sistema de Captación</title>
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
                                <?php echo $action === 'add' ? 'Nueva Pregunta' : 'Editar Pregunta'; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="numero" class="form-label">Número de Pregunta</label>
                                        <input type="number" class="form-control" id="numero" name="numero" 
                                               value="<?php echo isset($pregunta) ? htmlspecialchars($pregunta['numero']) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="pregunta" class="form-label">Pregunta</label>
                                        <textarea class="form-control" id="pregunta" name="pregunta" rows="3" required>
                                            <?php echo isset($pregunta) ? htmlspecialchars($pregunta['pregunta']) : ''; ?>
                                        </textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tipo" class="form-label">Tipo de Pregunta</label>
                                        <select class="form-select" id="tipo" name="tipo" required>
                                            <option value="texto" <?php echo isset($pregunta) && $pregunta['tipo'] == 'texto' ? 'selected' : ''; ?>>Texto</option>
                                            <option value="multiple" <?php echo isset($pregunta) && $pregunta['tipo'] == 'multiple' ? 'selected' : ''; ?>>Opción Múltiple</option>
                                            <option value="verdadero_falso" <?php echo isset($pregunta) && $pregunta['tipo'] == 'verdadero_falso' ? 'selected' : ''; ?>>Verdadero/Falso</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="opciones" class="form-label">Opciones (una por línea, solo para opción múltiple)</label>
                                        <textarea class="form-control" id="opciones" name="opciones" rows="3">
                                            <?php echo isset($pregunta) ? htmlspecialchars($pregunta['opciones']) : ''; ?>
                                        </textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="puntos" class="form-label">Puntos</label>
                                        <input type="number" class="form-control" id="puntos" name="puntos" 
                                               value="<?php echo isset($pregunta) ? htmlspecialchars($pregunta['puntos']) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                                   <?php echo isset($pregunta) && $pregunta['estado'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="estado">
                                                Pregunta Activa
                                            </label>
                                        </div>
                                    </div>
                                    <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="?test_id=<?php echo $test_id; ?>" class="btn btn-secondary">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Preguntas del Test: <?php echo htmlspecialchars($test['nombre']); ?></h5>
                                <a href="?action=add&test_id=<?php echo $test_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Nueva Pregunta
                                </a>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Número</th>
                                            <th>Pregunta</th>
                                            <th>Tipo</th>
                                            <th>Puntos</th>
                                            <th>Estado</th>
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
                                                    <span class="badge <?php echo $pregunta['estado'] ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $pregunta['estado'] ? 'Activa' : 'Inactiva'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?action=edit&id=<?php echo $pregunta['id']; ?>&test_id=<?php echo $test_id; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?php echo $pregunta['id']; ?>&test_id=<?php echo $test_id; ?>" 
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-code.js" crossorigin="anonymous"></script>
</body>
</html>
