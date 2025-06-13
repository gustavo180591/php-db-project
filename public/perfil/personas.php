<?php
require_once '../config/database.php';
checkAuth();

// Manejar acciones CRUD
$action = $_GET['action'] ?? '';
$persona_id = $_GET['id'] ?? null;

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
                         JOIN zonas z ON c.zona_id = z.id 
                         WHERE c.estado = 1");
    $centros = $stmt->fetchAll();
    
    // Obtener todas las personas
    $stmt = $pdo->query("SELECT p.*, z.nombre as zona_nombre, c.nombre as centro_nombre 
                         FROM personas p 
                         LEFT JOIN zonas z ON p.zona_id = z.id 
                         LEFT JOIN centros c ON p.centro_id = c.id");
    $personas = $stmt->fetchAll();
    
    // Manejar formularios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $dni = $_POST['dni'] ?? '';
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
        $sexo = $_POST['sexo'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $email = $_POST['email'] ?? '';
        $zona_id = $_POST['zona_id'] ?? '';
        $centro_id = $_POST['centro_id'] ?? null;
        $estado = isset($_POST['estado']) ? 1 : 0;
        
        if (empty($nombre) || empty($apellido) || empty($dni) || empty($zona_id)) {
            $error = "Los campos nombre, apellido, DNI y zona son requeridos";
        } else {
            if ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE personas SET 
                                      nombre = ?, 
                                      apellido = ?, 
                                      dni = ?, 
                                      fecha_nacimiento = ?,
                                      sexo = ?,
                                      direccion = ?, 
                                      telefono = ?, 
                                      email = ?, 
                                      zona_id = ?, 
                                      centro_id = ?, 
                                      estado = ? 
                                      WHERE id = ?");
                $stmt->execute([$nombre, $apellido, $dni, $fecha_nacimiento, $sexo, $direccion, $telefono, $email, $zona_id, $centro_id, $estado, $persona_id]);
                $message = "Persona actualizada exitosamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO personas (nombre, apellido, dni, fecha_nacimiento, sexo, direccion, telefono, email, zona_id, centro_id, estado) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $apellido, $dni, $fecha_nacimiento, $sexo, $direccion, $telefono, $email, $zona_id, $centro_id, $estado]);
                $message = "Persona registrada exitosamente";
            }
        }
    }
    
    // Manejar eliminación
    if ($action === 'delete' && $persona_id) {
        // Verificar si la persona tiene asignaciones
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM asignaciones_tests WHERE persona_id = ?");
        $stmt->execute([$persona_id]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            $error = "No se puede eliminar esta persona porque tiene asignaciones de tests";
        } else {
            $stmt = $pdo->prepare("DELETE FROM personas WHERE id = ?");
            $stmt->execute([$persona_id]);
            $message = "Persona eliminada exitosamente";
        }
    }
    
    // Obtener persona para editar si existe
    if ($action === 'edit' && $persona_id) {
        $stmt = $pdo->prepare("SELECT * FROM personas WHERE id = ?");
        $stmt->execute([$persona_id]);
        $persona = $stmt->fetch();
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
    <title>Personas - Sistema de Captación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../css/styles.css" rel="stylesheet">
    <link href="../../assets/style.css" rel="stylesheet">
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
                                <?php echo $action === 'add' ? 'Nueva Persona' : 'Editar Persona'; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nombre" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                                   value="<?php echo isset($persona) ? htmlspecialchars($persona['nombre']) : ''; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="apellido" class="form-label">Apellido</label>
                                            <input type="text" class="form-control" id="apellido" name="apellido" 
                                                   value="<?php echo isset($persona) ? htmlspecialchars($persona['apellido']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="dni" class="form-label">DNI</label>
                                            <input type="text" class="form-control" id="dni" name="dni" 
                                                   value="<?php echo isset($persona) ? htmlspecialchars($persona['dni']) : ''; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                                   value="<?php echo isset($persona) ? htmlspecialchars($persona['fecha_nacimiento']) : ''; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="sexo" class="form-label">Sexo</label>
                                            <select class="form-select" id="sexo" name="sexo" required>
                                                <option value="">Seleccione...</option>
                                                <option value="M" <?php echo isset($persona) && $persona['sexo'] === 'M' ? 'selected' : ''; ?>>Masculino</option>
                                                <option value="F" <?php echo isset($persona) && $persona['sexo'] === 'F' ? 'selected' : ''; ?>>Femenino</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="zona_id" class="form-label">Zona</label>
                                            <select class="form-select" id="zona_id" name="zona_id" required>
                                                <option value="">Seleccione una zona</option>
                                                <?php foreach ($zonas as $zona): ?>
                                                    <option value="<?php echo $zona['id']; ?>"
                                                            <?php echo isset($persona) && $persona['zona_id'] == $zona['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($zona['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="centro_id" class="form-label">Centro</label>
                                            <select class="form-select" id="centro_id" name="centro_id">
                                                <option value="">Seleccione un centro</option>
                                                <?php foreach ($centros as $centro): ?>
                                                    <option value="<?php echo $centro['id']; ?>"
                                                            <?php echo isset($persona) && $persona['centro_id'] == $centro['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($centro['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="direccion" class="form-label">Dirección</label>
                                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                                   value="<?php echo isset($persona) ? htmlspecialchars($persona['direccion']) : ''; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="telefono" class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                                   value="<?php echo isset($persona) ? htmlspecialchars($persona['telefono']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo isset($persona) ? htmlspecialchars($persona['email']) : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                                   <?php echo isset($persona) && $persona['estado'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="estado">
                                                Persona Activa
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
                                <h5 class="mb-0">Lista de Personas</h5>
                                <a href="?action=add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Nueva Persona
                                </a>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Apellido</th>
                                            <th>DNI</th>
                                            <th>Fecha Nac.</th>
                                            <th>Sexo</th>
                                            <th>Zona</th>
                                            <th>Centro</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($personas as $persona): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($persona['id']); ?></td>
                                                <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($persona['apellido']); ?></td>
                                                <td><?php echo htmlspecialchars($persona['dni']); ?></td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($persona['fecha_nacimiento']))); ?></td>
                                                <td><?php echo $persona['sexo'] === 'M' ? 'Masculino' : 'Femenino'; ?></td>
                                                <td><?php echo htmlspecialchars($persona['zona_nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($persona['centro_nombre']); ?></td>
                                                <td><?php echo $persona['estado'] ? 'Activo' : 'Inactivo'; ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="?action=edit&id=<?php echo $persona['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?action=delete&id=<?php echo $persona['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta persona?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/62c2ef2c47.js" crossorigin="anonymous"></script>
    <script>
        // Asegurar que los estilos se carguen correctamente
        window.addEventListener('load', function() {
            console.log('Estilos cargados');
        });
    </script>
</body>
</html>
