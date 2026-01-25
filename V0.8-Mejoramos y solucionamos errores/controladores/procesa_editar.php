<?php
// Recibir datos
$tabla = $_GET['tabla'];
$id = $_GET['id'];
$pk = $_GET['pk'];
$datos = $_POST;

$updates = "";

foreach($datos as $c => $v){
    // Skip PK in update SET clause just in case, though usually safe if matches WHERE
    if($c == $pk) continue;
    
    $updates .= "`$c` = '".$conexion->real_escape_string($v)."',";
}

// Quitamos ultima coma
$updates = substr($updates, 0, -1);

$sql = "UPDATE $tabla SET $updates WHERE $pk = '$id';";

$conexion->query($sql);

// Redireccion
echo "<script>window.location.href='?tabla=$tabla';</script>";
?>
