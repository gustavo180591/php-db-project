<?php
require_once '../../config/database.php';
checkAuth();

// Manejar acciones CRUD
$action = $_GET['action'] ?? '';
$centro_id = $_GET['id'] ?? null;

// Mensajes de éxito/error
$message = '';
$error = '';

try {
    $pdo = getConnection();
    
    // Obtener todas las zonas
    $stmt = $pdo->query("SELECT * FROM zonas WHERE estado = 1");
    $zonas = $stmt->fetchAll();
    
    // Obtener todos los centros
    $stmt = $pdo->query("SELECT c.*, z.nombre as zona_nombre 
                         FROM centros c 
                         JOIN zonas z ON c.zona_id = z.id");
    $centros = $stmt->fetchAll();
    
    // Manejar formularios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $zona_id = $_POST['zona_id'] ?? '';
        $estado = isset($_POST['estado']) ? 1 : 0;
        
        if (empty($nombre) || empty($direccion) || empty($zona_id)) {
            $error = "Los campos nombre, dirección y zona son requeridos";
        } else {
            if ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE centros SET 
                                      nombre = ?, 
                                      direccion = ?, 
                                      telefono = ?, 
                                      zona_id = ?, 
                                      estado = ? 
                                      WHERE id = ?");
                $stmt->execute([$nombre, $direccion, $telefono, $zona_id, $estado, $centro_id]);
                $message = "Centro actualizado exitosamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO centros (nombre, direccion, telefono, zona_id, estado) 
                                      VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $direccion, $telefono, $zona_id, $estado]);
                $message = "Centro registrado exitosamente";
            }
        }
    }
    
    // Manejar eliminación
    if ($action === 'delete' && $centro_id) {
        // Verificar si el centro tiene personas asignadas
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM personas WHERE centro_id = ?");
        $stmt->execute([$centro_id]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            $error = "No se puede eliminar este centro porque tiene personas asignadas";
        } else {
            $stmt = $pdo->prepare("DELETE FROM centros WHERE id = ?");
            $stmt->execute([$centro_id]);
            $message = "Centro eliminado exitosamente";
        }
    }
    
    // Obtener centro para editar si existe
    if ($action === 'edit' && $centro_id) {
        $stmt = $pdo->prepare("SELECT * FROM centros WHERE id = ?");
        $stmt->execute([$centro_id]);
        $centro = $stmt->fetch();
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
    <title>Gestión de Centros - Sistema de Captación</title>
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
                                <?php echo $action === 'add' ? 'Nuevo Centro' : 'Editar Centro'; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre del Centro</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo isset($centro) ? htmlspecialchars($centro['nombre']) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <textarea class="form-control" id="direccion" name="direccion" rows="3" required>
                                            <?php echo isset($centro) ? htmlspecialchars($centro['direccion']) : ''; ?>
                                        </textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="telefono" class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" 
                                               value="<?php echo isset($centro) ? htmlspecialchars($centro['telefono']) : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="zona_id" class="form-label">Zona</label>
                                        <select class="form-select" id="zona_id" name="zona_id" required>
                                            <option value="">Seleccione una zona</option>
                                            <?php foreach ($zonas as $zona): ?>
                                                <option value="<?php echo $zona['id']; ?>"
                                                        <?php echo isset($centro) && $centro['zona_id'] == $zona['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($zona['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                                   <?php echo isset($centro) && $centro['estado'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="estado">
                                                Centro Activo
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
                                <h5 class="mb-0">Lista de Centros</h5>
                                <a href="?action=add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Nuevo Centro
                                </a>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Dirección</th>
                                            <th>Teléfono</th>
                                            <th>Zona</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($centros as $centro): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($centro['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($centro['direccion']); ?></td>
                                                <td><?php echo htmlspecialchars($centro['telefono']); ?></td>
                                                <td><?php echo htmlspecialchars($centro['zona_nombre']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $centro['estado'] ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $centro['estado'] ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?action=edit&id=<?php echo $centro['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?php echo $centro['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar este centro?')">
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
