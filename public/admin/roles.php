<?php
require_once '../config/database.php';
checkAuth();
checkRole(1); // Solo administradores pueden acceder

// Manejar acciones CRUD
$action = $_GET['action'] ?? '';
$role_id = $_GET['id'] ?? null;

// Mensajes de éxito/error
$message = '';
$error = '';

try {
    $pdo = getConnection();
    
    // Obtener todos los roles
    $stmt = $pdo->query("SELECT * FROM roles");
    $roles = $stmt->fetchAll();
    
    // Manejar formularios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        
        if (empty($nombre)) {
            $error = "El nombre es requerido";
        } else {
            if ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE roles SET nombre = ?, descripcion = ? WHERE id = ?");
                $stmt->execute([$nombre, $descripcion, $role_id]);
                $message = "Rol actualizado exitosamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO roles (nombre, descripcion) VALUES (?, ?)");
                $stmt->execute([$nombre, $descripcion]);
                $message = "Rol creado exitosamente";
            }
        }
    }
    
    // Manejar eliminación
    if ($action === 'delete' && $role_id) {
        // Verificar si el rol está siendo usado
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM usuarios WHERE rol_id = ?");
        $stmt->execute([$role_id]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            $error = "No se puede eliminar este rol porque está siendo usado por usuarios";
        } else {
            $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->execute([$role_id]);
            $message = "Rol eliminado exitosamente";
        }
    }
    
    // Obtener rol para editar si existe
    if ($action === 'edit' && $role_id) {
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$role_id]);
        $role = $stmt->fetch();
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
    <title>Gestión de Roles - Sistema de Captación</title>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>Gestión de Roles</h1>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Rol
                        </a>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($action === 'add' || $action === 'edit'): ?>
                        <div class="card">
                            <div class="card-header">
                                <?php echo $action === 'add' ? 'Nuevo Rol' : 'Editar Rol'; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo isset($role) ? htmlspecialchars($role['nombre']) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3">
                                            <?php echo isset($role) ? htmlspecialchars($role['descripcion']) : ''; ?>
                                        </textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="." class="btn btn-secondary">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-header">Lista de Roles</div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($roles as $role): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($role['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($role['descripcion']); ?></td>
                                                <td>
                                                    <a href="?action=edit&id=<?php echo $role['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?php echo $role['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar este rol?')">
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
