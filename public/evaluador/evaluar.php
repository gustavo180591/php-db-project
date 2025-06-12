<?php
require_once '../config/database.php';
checkAuth();
checkRole(2); // Solo evaluadores pueden acceder

$test_id = $_GET['id'] ?? null;
$error = '';
$message = '';

try {
    $pdo = getConnection();
    
    // Obtener información del test
    $stmt = $pdo->prepare("SELECT * FROM test_types WHERE id = ?");
    $stmt->execute([$test_id]);
    $test = $stmt->fetch();
    
    if (!$test) {
        throw new Exception("Test no encontrado");
    }
    
    // Obtener configuración
    $config = json_decode($test['configuracion'], true);
    
    // Obtener personas
    $stmt = $pdo->query("SELECT * FROM personas WHERE estado = 1 ORDER BY nombre");
    $personas = $stmt->fetchAll();
    
    // Manejar el formulario de evaluación
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $persona_id = $_POST['persona_id'] ?? null;
        $valor = $_POST['valor'] ?? null;
        $edad = $_POST['edad'] ?? null;
        $sexo = $_POST['sexo'] ?? null;
        
        if (!$persona_id || !$valor || !$edad || !$sexo) {
            $error = "Todos los campos son requeridos";
        } else {
            // Validar valor según tipo de test
            if (!is_numeric($valor)) {
                $error = "El valor debe ser un número válido";
            } else {
                // Insertar el resultado
                $stmt = $pdo->prepare("INSERT INTO test_results (persona_id, test_type_id, valor, edad, sexo) 
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$persona_id, $test_id, $valor, $edad, $sexo]);
                
                $message = "Resultado del test registrado exitosamente";
            }
        }
    }
    
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación de <?php echo htmlspecialchars($test['nombre']); ?> - Sistema de Captación</title>
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

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Evaluación de <?php echo htmlspecialchars($test['nombre']); ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="persona_id" class="form-label">Persona</label>
                                        <select class="form-select" id="persona_id" name="persona_id" required>
                                            <option value="">Seleccione una persona</option>
                                            <?php foreach ($personas as $persona): ?>
                                                <option value="<?php echo $persona['id']; ?>">
                                                    <?php echo htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label for="edad" class="form-label">Edad</label>
                                        <input type="number" class="form-control" id="edad" name="edad" 
                                               min="<?php echo isset($config['edad_min']) ? $config['edad_min'] : 18; ?>" 
                                               max="<?php echo isset($config['edad_max']) ? $config['edad_max'] : 60; ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label for="sexo" class="form-label">Sexo</label>
                                        <select class="form-select" id="sexo" name="sexo" required>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="valor" class="form-label"><?php echo isset($config['unidad']) ? "Valor ($config[unidad])" : 'Valor'; ?></label>
                                    <input type="number" class="form-control" id="valor" name="valor" 
                                           step="<?php echo $test['tipo'] === 'cooper' ? '0.1' : '1'; ?>" required>
                                    <?php if (isset($config['tiempo'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($config['tiempo']); ?></small>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (isset($config['rango'])): ?>
                                <div class="mb-3">
                                    <h6>Rangos de Evaluación</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Sexo</th>
                                                    <th>Edad</th>
                                                    <th>Nivel</th>
                                                    <th>Valor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($config['rango'] as $sexo => $rangos): ?>
                                                    <?php foreach ($rangos as $rango): ?>
                                                        <tr>
                                                            <td><?php echo $sexo === 'M' ? 'Masculino' : 'Femenino'; ?></td>
                                                            <td><?php echo isset($config['edad_min']) ? $config['edad_min'] . '-' . $config['edad_max'] : 'Todos'; ?></td>
                                                            <td><?php echo $rango['nivel']; ?></td>
                                                            <td><?php echo $rango['min'] . ' - ' . $rango['max']; ?> <?php echo isset($config['unidad']) ? $config['unidad'] : ''; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <button type="submit" class="btn btn-primary">Guardar Resultado</button>
                                <a href="../tests.php?id=<?php echo $test_id; ?>" class="btn btn-secondary">Cancelar</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-code.js" crossorigin="anonymous"></script>
</body>
</html>
