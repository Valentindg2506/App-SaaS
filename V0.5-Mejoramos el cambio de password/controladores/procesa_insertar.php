<?php
// Recibir datos
$tabla = $_GET['tabla'];
$datos = $_POST;

$campos = "";
$valores = "";

// LOGICA ESPECIFICA: HASHEO DE CONTRASEÑA
if ($tabla == 'usuario_sistema' && isset($datos['contrasena'])) {
    $datos['contrasena'] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
    $datos['debe_cambiar_password'] = 1; 
}

foreach($datos as $c => $v){
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
