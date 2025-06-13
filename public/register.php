<?php
require_once '../config/database.php';

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? '';
$is_evaluador = $action === 'evaluador';
$is_atleta = $action === 'atleta';

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
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate inputs
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
        $sexo = $_POST['sexo'] ?? '';
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $zona_id = $_POST['zona_id'] ?? '';
        $centro_id = $_POST['centro_id'] ?? '';
        
        // Validaciones específicas para evaluador
        if ($is_evaluador) {
            $especialidad = trim($_POST['especialidad'] ?? '');
            $experiencia = trim($_POST['experiencia'] ?? '');
            if (empty($especialidad)) {
                $error = "La especialidad es requerida para evaluadores";
            } elseif (empty($experiencia)) {
                $error = "La experiencia es requerida para evaluadores";
            }
        }
        
        // Validaciones específicas para atleta
        if ($is_atleta) {
            $deporte = trim($_POST['deporte'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            if (empty($deporte)) {
                $error = "El deporte es requerido para atletas";
            } elseif (empty($categoria)) {
                $error = "La categoría es requerida para atletas";
            }
        }
        
        if (empty($nombre)) {
            $error = "El nombre es requerido";
        } elseif (empty($apellido)) {
            $error = "El apellido es requerido";
        } elseif (empty($dni)) {
            $error = "El DNI es requerido";
        } elseif (empty($fecha_nacimiento)) {
            $error = "La fecha de nacimiento es requerida";
        } elseif (empty($sexo)) {
            $error = "El sexo es requerido";
        } elseif (empty($zona_id)) {
            $error = "La zona es requerida";
        } elseif (empty($email)) {
            $error = "El email es requerido";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Formato de email inválido";
        } elseif (empty($password)) {
            $error = "La contraseña es requerida";
        } elseif (strlen($password) < 6) {
            $error = "La contraseña debe tener al menos 6 caracteres";
        } elseif ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "El email ya está registrado";
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar persona
                $stmt = $pdo->prepare("INSERT INTO personas (nombre, apellido, dni, fecha_nacimiento, sexo, direccion, telefono, email, zona_id, centro_id, estado) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $apellido, $dni, $fecha_nacimiento, $sexo, $direccion, $telefono, $email, $zona_id, $centro_id, 1]);
                $persona_id = $pdo->lastInsertId();
                
                // Insertar usuario con rol según tipo de registro
                $rol_id = $is_evaluador ? 2 : 3; // 2 para evaluador, 3 para atleta
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol_id, persona_id) VALUES (?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$nombre . ' ' . $apellido, $email, $hashed_password, $rol_id, $persona_id])) {
                    // Insertar datos específicos según tipo de registro
                    if ($is_evaluador) {
                        $stmt = $pdo->prepare("INSERT INTO evaluadores (persona_id, especialidad, experiencia) VALUES (?, ?, ?)");
                        $stmt->execute([$persona_id, $especialidad, $experiencia]);
                    } elseif ($is_atleta) {
                        $stmt = $pdo->prepare("INSERT INTO atletas (persona_id, deporte, categoria) VALUES (?, ?, ?)");
                        $stmt->execute([$persona_id, $deporte, $categoria]);
                    }
                    
                    // Redirect to login page with success message
                    $_SESSION['success'] = "Registro exitoso. Por favor, inicia sesión.";
                    header('Location: login.php');
                    exit;
                } else {
                    throw new PDOException("Error al insertar el usuario en la base de datos");
                }
            }
        }
    }
} catch (PDOException $e) {
    $error = "Error al registrar el usuario: " . $e->getMessage();
    error_log("Error de registro: " . $e->getMessage());
}
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Captación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center mb-0">Registro</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dni" class="form-label">DNI</label>
                                    <input type="text" class="form-control" id="dni" name="dni" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sexo" class="form-label">Sexo</label>
                                    <select class="form-select" id="sexo" name="sexo" required>
                                        <option value="">Seleccione...</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="zona_id" class="form-label">Zona</label>
                                    <select class="form-select" id="zona_id" name="zona_id" required>
                                        <option value="">Seleccione una zona...</option>
                                        <?php foreach ($zonas as $zona): ?>
                                            <option value="<?php echo $zona['id']; ?>"><?php echo $zona['nombre']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="centro_id" class="form-label">Centro</label>
                                    <select class="form-select" id="centro_id" name="centro_id" required>
                                        <option value="">Seleccione un centro...</option>
                                        <?php foreach ($centros as $centro): ?>
                                            <option value="<?php echo $centro['id']; ?>"><?php echo $centro['nombre']; ?> (<?php echo $centro['zona_nombre']; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Campos específicos para evaluador -->
                            <?php if ($is_evaluador): ?>
                            <div class="mb-3">
                                <label for="especialidad" class="form-label">Especialidad</label>
                                <input type="text" class="form-control" id="especialidad" name="especialidad" required>
                            </div>
                            <div class="mb-3">
                                <label for="experiencia" class="form-label">Años de Experiencia</label>
                                <input type="number" class="form-control" id="experiencia" name="experiencia" min="0" required>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Campos específicos para atleta -->
                            <?php if ($is_atleta): ?>
                            <div class="mb-3">
                                <label for="deporte" class="form-label">Deporte</label>
                                <input type="text" class="form-control" id="deporte" name="deporte" required>
                            </div>
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="categoria" name="categoria" required>
                            </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="text-decoration-none">¿Ya tienes cuenta? Inicia sesión</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
