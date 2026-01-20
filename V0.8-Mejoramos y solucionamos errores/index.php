<?php
session_start();

// Updated session check
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

include "inc/config_roles.php"; // Load permission logic
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App SaaS</title>
    <link rel="stylesheet" href="css/estilo.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
</head>
    <?php 
    include "inc/conexion_bd.php";
    include "inc/security.php"; // Load Security Module
    secure_headers(); // Apply headers
    include "inc/helpers.php";

    // Cargar Configuración Visual
    $res_cfg = $conexion->query("SELECT * FROM configuracion WHERE id=1");
    $app_cfg = ($res_cfg && $res_cfg->num_rows > 0) ? $res_cfg->fetch_assoc() : ['color_menu'=>'#1e293b', 'color_body'=>'#f1f5f9'];
    
    // Calcular contraste automático
    $text_menu = get_contrast_color($app_cfg['color_menu']);
    $text_body = get_contrast_color($app_cfg['color_body']);
    
    // Calcular color de superficie (Blanco para claro, Oscuro para oscuro)
    // Si el body es 'oscuro' (#0f172a), la superficie es un poco más clara (#1e293b)
    // Si el body es 'claro' (#f1f5f9), la superficie es blanca (#ffffff)
    $bg_surface = ($app_cfg['color_body'] == '#0f172a') ? '#1e293b' : '#ffffff';
    ?>
    <style>
        :root {
            --bg-sidebar:   <?= $app_cfg['color_menu'] ?>;
            --text-sidebar: <?= $text_menu ?>;
            
            --bg-body:      <?= $app_cfg['color_body'] ?>;
            --text-main:    <?= $text_body ?>;
            --bg-surface:   <?= $bg_surface ?>;
        }
    </style>
    
    <nav>
        <div class="nav-header">
            <span>⚡ App SaaS</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php" class="<?= !isset($_GET['operacion']) && !isset($_GET['tabla']) ? 'activo' : '' ?>">🏠 Dashboard</a></li>
            <?php include "controladores/poblar_menu.php"; ?>
        </ul>
        
        <div class="user-info">
            <div>
                <div style="font-weight:bold; color:white;"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></div>
                <div style="font-size:0.75rem; color:#94a3b8; text-transform:uppercase;"><?= htmlspecialchars($_SESSION['usuario_rol']) ?></div>
            </div>
            <a href="logout.php" title="Cerrar Sesión">⏻</a>
        </div>
    </nav>
    
    <main>
        <?php
        
        // SEGURIDAD: Verificar acceso antes de router
        if (isset($_GET['tabla'])) {
            if (!tiene_acceso($_SESSION['usuario_rol'], $_GET['tabla'])) {
                echo "
                <div class='card' style='border-left: 4px solid var(--danger);'>
                    <h2 class='text-danger'>Acceso Denegado</h2>
                    <p>No tienes permisos para ver el módulo de <b>".htmlspecialchars($_GET['tabla'])."</b>.</p>
                    <p>Rol actual: <code>".$_SESSION['usuario_rol']."</code></p>
                </div>
                ";
                exit(); // Stop execution
            }
        }

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
                include "controladores/configuracion.php";
            } else if($_GET['operacion'] == "convertir_prospecto"){
                include "controladores/convertir_prospecto.php";
            } else if($_GET['operacion'] == "kanban"){
                include "controladores/kanban.php";
            } else if($_GET['operacion'] == "ajax_update_fase"){
                include "controladores/ajax_update_fase.php";
                exit; // Importante salir para no renderizar HTML extra
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
