<?php
// Recibir datos
$tabla = $_GET['tabla']; 
$datos = $_POST;

// Security Checks
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    die("Error de validación CSRF.");
}

// Whitelist Table
$tabla = validate_table_name($tabla);

// Remove token from data
unset($datos['csrf_token']);

// ============================================================================
// 1. AUTO-ASIGNACIÓN DE PROPIETARIO (SOLUCIÓN A TU PROBLEMA)
// ============================================================================
// Si el formulario no envió un 'empleado_id' (como el nuevo de clientes)
// asignamos automáticamente el usuario actual. Así el Empleado podrá ver lo que creó.
if (in_array($tabla, ['cliente', 'prospectos']) && empty($datos['empleado_id'])) {
    if (isset($_SESSION['usuario_id'])) {
        $datos['empleado_id'] = $_SESSION['usuario_id'];
    }
}
// ============================================================================

$campos = "";
$valores = "";

// LOGICA ESPECIFICA: HASHEO DE CONTRASEÑA
if ($tabla == 'usuario_sistema' && isset($datos['contrasena'])) {
    $datos['contrasena'] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
    $datos['debe_cambiar_password'] = 1; 
}

// Prepare columns and values safely
foreach($datos as $c => $v){
    // Validación básica de nombre de columna para evitar inyecciones raras
    if(!preg_match('/^[a-zA-Z0-9_]+$/', $c)) continue;

    $campos .= "`$c`,";
    $valores .= "'".$conexion->real_escape_string($v)."',";
}

// Quitamos ultima coma
$campos = substr($campos, 0, -1);
$valores = substr($valores, 0, -1);

$sql = "INSERT INTO $tabla ($campos) VALUES ($valores);";

// EJECUTAR CON REPORTE DE ERRORES (Para que sepas si algo falla)
if($conexion->query($sql)){
    // Éxito
    echo "<script>window.location.href='?tabla=$tabla';</script>";
} else {
    // Error Visual detallado
    echo "<div style='font-family:sans-serif; padding:40px; text-align:center; background:#fee2e2; color:#991b1b; height:100vh;'>";
    echo "<h1 style='font-size:2rem;'>⚠️ Error al Guardar</h1>";
    echo "<p style='font-size:1.2rem;'>La base de datos rechazó la operación.</p>";
    
    echo "<div style='background:#fff; padding:20px; border-radius:8px; max-width:600px; margin:20px auto; text-align:left; box-shadow:0 4px 6px rgba(0,0,0,0.1);'>";
        echo "<p><strong>Mensaje SQL:</strong><br><code style='color:#dc2626'> " . $conexion->error . "</code></p>";
        echo "<hr style='border:0; border-top:1px solid #eee; margin:15px 0;'>";
        echo "<p style='color:#666; font-size:0.9em;'><strong>Consulta intentada:</strong><br>" . htmlspecialchars($sql) . "</p>";
    echo "</div>";

    echo "<button onclick='history.back()' style='cursor:pointer; padding:12px 24px; background:#1e293b; color:white; border:none; border-radius:6px; font-size:1rem;'>⬅️ Volver al Formulario</button>";
    echo "</div>";
}
?>
