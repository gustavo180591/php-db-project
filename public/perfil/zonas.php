<?php
require_once '../config/database.php';
checkAuth();

// Manejar acciones CRUD
$action = $_GET['action'] ?? '';
$zona_id = $_GET['id'] ?? null;

// Mensajes de éxito/error
$message = '';
$error = '';

try {
    $pdo = getConnection();
    
    // Obtener todas las zonas
    $stmt = $pdo->query("SELECT * FROM zonas ORDER BY numero");
    $zonas = $stmt->fetchAll();
    
    // Manejar formularios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $numero = $_POST['numero'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $estado = isset($_POST['estado']) ? 1 : 0;
        
        if (empty($numero) || empty($nombre)) {
            $error = "Los campos número y nombre son requeridos";
        } else {
            if ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE zonas SET 
                                      numero = ?, 
                                      nombre = ?, 
                                      descripcion = ?, 
                                      estado = ? 
                                      WHERE id = ?");
                $stmt->execute([$numero, $nombre, $descripcion, $estado, $zona_id]);
                $message = "Zona actualizada exitosamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO zonas (numero, nombre, descripcion, estado) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([$numero, $nombre, $descripcion, $estado]);
                $message = "Zona registrada exitosamente";
            }
        }
    }
    
    // Manejar eliminación
    if ($action === 'delete' && $zona_id) {
        // Verificar si la zona tiene centros o personas asignados
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM centros WHERE zona_id = ?");
        $stmt->execute([$zona_id]);
        $centros_count = $stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM personas WHERE zona_id = ?");
        $stmt->execute([$zona_id]);
        $personas_count = $stmt->fetch()['count'];
        
        if ($centros_count > 0 || $personas_count > 0) {
            $error = "No se puede eliminar esta zona porque tiene centros o personas asignados";
        } else {
            $stmt = $pdo->prepare("DELETE FROM zonas WHERE id = ?");
            $stmt->execute([$zona_id]);
            $message = "Zona eliminada exitosamente";
        }
    }
    
    // Obtener zona para editar si existe
    if ($action === 'edit' && $zona_id) {
        $stmt = $pdo->prepare("SELECT * FROM zonas WHERE id = ?");
        $stmt->execute([$zona_id]);
        $zona = $stmt->fetch();
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
    <title>Gestión de Zonas - Sistema de Captación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/styles.css" rel="stylesheet">
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
                                <?php echo $action === 'add' ? 'Nueva Zona' : 'Editar Zona'; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="numero" class="form-label">Número de Zona</label>
                                            <input type="number" class="form-control" id="numero" name="numero" 
                                                   value="<?php echo isset($zona) ? htmlspecialchars($zona['numero']) : ''; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="nombre" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                                   value="<?php echo isset($zona) ? htmlspecialchars($zona['nombre']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3">
                                            <?php echo isset($zona) ? htmlspecialchars($zona['descripcion']) : ''; ?>
                                        </textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                                   <?php echo isset($zona) && $zona['estado'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="estado">
                                                Zona Activa
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="." class="btn btn-secondary">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Lista de Zonas</h5>
                                <a href="?action=add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Nueva Zona
                                </a>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Número</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($zonas as $zona): ?>
                                            <tr>
                                                <td><?php echo $zona['numero']; ?></td>
                                                <td><?php echo htmlspecialchars($zona['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($zona['descripcion']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $zona['estado'] ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $zona['estado'] ? 'Activa' : 'Inactiva'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?action=edit&id=<?php echo $zona['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?php echo $zona['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar esta zona?')">
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
