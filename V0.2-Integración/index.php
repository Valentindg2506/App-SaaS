<?php
session_start();
if (!isset($_SESSION['usuario_admin'])) {
    header("Location: login.php");
    exit();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/estilo.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include "inc/conexion_bd.php"; ?>
    
    <nav>
        <div class="nav-header">
            <span>⚡ AdminPanel</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php" class="<?= !isset($_GET['operacion']) && !isset($_GET['tabla']) ? 'activo' : '' ?>">🏠 Dashboard</a></li>
            <?php include "controladores/poblar_menu.php"; ?>
        </ul>
        
        <div class="user-info">
            <div>
                <div style="font-weight:bold; color:white;"><?= htmlspecialchars($_SESSION['usuario_admin']) ?></div>
                <div style="font-size:0.75rem; color:#94a3b8;">Administrador</div>
            </div>
            <a href="logout.php" title="Cerrar Sesión">⏻</a>
        </div>
    </nav>
    
    <main>
        <?php
        // ENRUTADOR
        if(isset($_GET['operacion'])){
            if($_GET['operacion'] == "insertar"){
                include "controladores/insertar.php";
            } else if($_GET['operacion'] == "procesa_insertar"){
                include "controladores/procesa_insertar.php";
            } else if($_GET['operacion'] == "eliminar"){
                include "controladores/delete.php";
            } else if($_GET['operacion'] == "editar"){
                include "controladores/editar.php";
            } else if($_GET['operacion'] == "procesa_editar"){
                include "controladores/procesa_editar.php";
            }
        } else if (isset($_GET['tabla'])) {
            include "controladores/read.php";
        } else {
            include "controladores/home.php";
        }
        ?>
    </main>
</body>
</html>
