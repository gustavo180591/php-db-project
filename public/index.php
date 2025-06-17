<?php
require_once 'config/database.php';
checkAuth();

// Obtener el nombre del rol del usuario
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT nombre FROM roles WHERE id = ?");
    $stmt->execute([$_SESSION['role_id']]);
    $role = $stmt->fetch()['nombre'];
} catch (PDOException $e) {
    $role = "Usuario";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Captación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Sistema de Captación</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> (<?php echo $role; ?>)</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <?php if ($_SESSION['role_id'] == 1): ?> <!-- Administrador -->
                        <li class="nav-item">
                            <a class="nav-link" href="admin/usuarios.php">
                                <i class="fas fa-users"></i>
                                <span>Usuarios</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/roles.php">
                                <i class="fas fa-user-shield"></i>
                                <span>Roles</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($_SESSION['role_id'] <= 2): ?> <!-- Administrador o Evaluador -->
                        <li class="nav-item">
                            <a class="nav-link" href="/evaluador/tests.php">
                                <i class="fas fa-file-alt"></i>
                                <span>Tests</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="evaluador/asignaciones.php">
                                <i class="fas fa-tasks"></i>
                                <span>Asignaciones</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="perfil/personas.php">
                            <i class="fas fa-user"></i>
                            <span>Personas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="perfil/centros.php">
                            <i class="fas fa-building"></i>
                            <span>Centros</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="perfil/zonas.php">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Zonas</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="container-fluid">
                    <h1 class="mt-4">Dashboard</h1>
                    
                    <!-- Tarjetas de estadísticas -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Personas Registradas</h5>
                                    <p class="card-text h2"><?php echo getStatistics('personas'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Tests Asignados</h5>
                                    <p class="card-text h2"><?php echo getStatistics('asignaciones_tests'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Centros Activos</h5>
                                    <p class="card-text h2"><?php echo getStatistics('centros', 'estado = 1'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Zonas</h5>
                                    <p class="card-text h2"><?php echo getStatistics('zonas'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
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

<?php
function getStatistics($table, $where = '') {
    try {
        $pdo = getConnection();
        $sql = "SELECT COUNT(*) as count FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $stmt = $pdo->query($sql);
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        return 'N/A';
    }
}
?>
