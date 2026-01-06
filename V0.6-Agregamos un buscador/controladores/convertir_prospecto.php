<?php
// Validar entrada
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("ID Invalido");
}

// Check CSRF
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    die("Error de validación CSRF.");
}

$id = (int)$_POST['id'];

// 1. Fetch Prospecto
$sql = "SELECT * FROM prospectos WHERE id = $id";
$res = $conexion->query($sql);

if ($res && $res->num_rows > 0) {
    $p = $res->fetch_assoc();
    
    // 2. Insert into Cliente
    // Map fields: nombre, apellido, email, telefono, direccion, empresa
    // Ignore: id, comentarios, created_at (auto)
    
    $nombre = $conexion->real_escape_string($p['nombre']);
    $apellido = $conexion->real_escape_string($p['apellido']);
    $email = $conexion->real_escape_string($p['email']);
    $telefono = $conexion->real_escape_string($p['telefono']);
    $direccion = $conexion->real_escape_string($p['direccion']);
    $empresa = $conexion->real_escape_string($p['empresa']);
    
    $insertSql = "INSERT INTO cliente (nombre, apellido, email, telefono, direccion, empresa) VALUES ('$nombre', '$apellido', '$email', '$telefono', '$direccion', '$empresa')";
    
    if ($conexion->query($insertSql)) {
        // 3. Delete from Prospectos
        $conexion->query("DELETE FROM prospectos WHERE id = $id");
        
        // Success
        echo "<script>
            // alert('Prospecto convertido a cliente correctamente.'); // Opcional, mejor directo
            window.location.href='?tabla=cliente';
        </script>";
    } else {
        echo "<script>alert('Error al convertir: ".$conexion->error."'); window.location.href='?tabla=prospectos';</script>";
    }
} else {
    echo "Prospecto no encontrado.";
}
?>
