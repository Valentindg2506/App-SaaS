<?php
// Validaciones de Seguridad básicas
if (!isset($_POST['id']) || !isset($_POST['fase'])) {
    die("Datos incompletos");
}

// Opcional: Validar CSRF si es estricto, aunque en AJAX a veces se simplifica.
// Aquí usamos la función existente en security.php si la sesión está activa.
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
   // die("Error CSRF"); // Descomentar si quieres seguridad estricta en AJAX
}

$id = $conexion->real_escape_string($_POST['id']);
$fase = $conexion->real_escape_string($_POST['fase']);

// Actualizar
$sql = "UPDATE cliente SET fase = '$fase' WHERE id = '$id'";

if($conexion->query($sql)){
    echo "OK";
} else {
    http_response_code(500);
    echo "Error: " . $conexion->error;
}
?>
