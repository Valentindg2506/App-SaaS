<?php
// Recibir datos
$tabla = $_GET['tabla'];
$datos = $_POST;

$campos = "";
$valores = "";

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
