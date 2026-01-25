<?php
if(isset($_GET['tabla']) && isset($_GET['id']) && isset($_GET['pk'])){
    $tabla = $_GET['tabla'];
    $id = $_GET['id'];
    $pk = $_GET['pk'];
    
    // Prevenir inyeccion SQL basica (en un entorno real usar Prepared Statements siempre)
    $tabla = $conexion->real_escape_string($tabla);
    $id = $conexion->real_escape_string($id);
    $pk = $conexion->real_escape_string($pk);
    
    $query = "DELETE FROM $tabla WHERE $pk = '$id'";
    $conexion->query($query);
    
    echo "<script>window.location.href='?tabla=$tabla';</script>";
} else {
    echo "Faltan parámetros para eliminar.";
}
?>
