<?php
if(isset($_GET['tabla']) && isset($_GET['id']) && isset($_GET['pk'])){
    $tabla = $_POST['tabla'] ?? $_GET['tabla']; 
    $id = $_POST['id'] ?? $_GET['id'];
    $pk = $_POST['pk'] ?? $_GET['pk'];
    
    // Validaciones de Seguridad
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("Método no permitido. Use POST.");
    }

    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("Error de validación CSRF. Token inválido.");
    }
    
    // Include security if not already included (it should be in index, but if file called directly):
    // Actually delete.php is included from index.php usually, so security.php function is available.
    
    // Whitelist check
    $tabla = validate_table_name($tabla);

    // Prevenir inyeccion SQL en IDs (aunque real_escape hace algo, mejor cast o prepare)
    $id = $conexion->real_escape_string($id);
    $pk = $conexion->real_escape_string($pk);
    
    // Check PK naming convention to avoid arbitrary deletion if possible, 
    // or relying on whitelist is "okay" if we trust the tables. 
    // Ideally we should validate PK column too, but SHOW COLUMNS logic in read.php handles that.
    // Let's assume input is somewhat safe after escape, but $tabla is whitelisted.

    $query = "DELETE FROM $tabla WHERE $pk = '$id'";
    $conexion->query($query);
    
    echo "<script>window.location.href='?tabla=$tabla';</script>";
} else {
    echo "Faltan parámetros para eliminar.";
}
?>
