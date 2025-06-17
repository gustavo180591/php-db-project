<?php
require_once '../config/database.php';
checkAuth();
checkRole(1); // Solo administradores pueden acceder

// Manejar acciones CRUD
$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? null;

// Mensajes de éxito/error
$message = '';
$error = '';

try {
    $pdo = getConnection();
    
    // Obtener todos los usuarios
    $stmt = $pdo->query("SELECT u.*, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id");
    $usuarios = $stmt->fetchAll();
    
    // Obtener todos los roles
    $stmt = $pdo->query("SELECT * FROM roles");
    $roles = $stmt->fetchAll();
    
    // Manejar formularios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $rol_id = $_POST['rol_id'] ?? '';
        $estado = isset($_POST['estado']) ? 1 : 0;
        
        if (empty($nombre) || empty($email) || empty($password) || empty($rol_id)) {
            $error = "Todos los campos son requeridos";
        } else {
            if ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ?, rol_id = ?, estado = ? WHERE id = ?");
                $stmt->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT), $rol_id, $estado, $user_id]);
                $message = "Usuario actualizado exitosamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol_id, estado) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT), $rol_id, $estado]);
                $message = "Usuario creado exitosamente";
            }
        }
    }
    
    // Manejar eliminación
    if ($action === 'delete' && $user_id) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = "Usuario eliminado exitosamente";
    }
    
    // Obtener usuario para editar si existe
    if ($action === 'edit' && $user_id) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);
        $usuario = $stmt->fetch();
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
    <title>Gestión de Usuarios - Sistema de Captación</title>
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
                        <h1>Gestión de Usuarios</h1>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Usuario
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
                                <?php echo $action === 'add' ? 'Nuevo Usuario' : 'Editar Usuario'; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo isset($usuario) ? htmlspecialchars($usuario['nombre']) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo isset($usuario) ? htmlspecialchars($usuario['email']) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="rol_id" class="form-label">Rol</label>
                                        <select class="form-select" id="rol_id" name="rol_id" required>
                                            <?php foreach ($roles as $rol): ?>
                                                <option value="<?php echo $rol['id']; ?>"
                                                        <?php echo isset($usuario) && $usuario['rol_id'] == $rol['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($rol['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                                   <?php echo isset($usuario) && $usuario['estado'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="estado">
                                                Usuario Activo
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
                            <div class="card-header">Lista de Usuarios</div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['rol_nombre']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $usuario['estado'] ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $usuario['estado'] ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?action=edit&id=<?php echo $usuario['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?php echo $usuario['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
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
