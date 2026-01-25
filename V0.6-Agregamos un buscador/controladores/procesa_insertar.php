<?php
// Recibir datos
$tabla = $_GET['tabla']; // validated below
$datos = $_POST;

// Security Checks
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    die("Error de validación CSRF.");
}
// Whitelist Table
$tabla = validate_table_name($tabla);

// Remove token from data to be inserted
unset($datos['csrf_token']);

$campos = "";
$valores = "";

// LOGICA ESPECIFICA: HASHEO DE CONTRASEÑA
if ($tabla == 'usuario_sistema' && isset($datos['contrasena'])) {
    $datos['contrasena'] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
    $datos['debe_cambiar_password'] = 1; 
}

// Prepare columns and values safely
foreach($datos as $c => $v){
    // Validate column names roughly (alphanumeric + underscore) to prevent weird injection if not checking schema
    if(!preg_match('/^[a-zA-Z0-9_]+$/', $c)) continue;

    $campos .= "`$c`,";
    $valores .= "'".$conexion->real_escape_string($v)."',";
}

// Quitamos ultima coma
$campos = substr($campos, 0, -1);
$valores = substr($valores, 0, -1);

$sql = "INSERT INTO $tabla ($campos) VALUES ($valores);";

$conexion->query($sql);

// Redireccion
echo "<script>window.location.href='?tabla=$tabla';</script>";
?>
