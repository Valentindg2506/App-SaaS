<?php
session_start();
include "inc/conexion_bd.php";

if (!isset($_SESSION['temp_usuario_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['temp_usuario_id'];
$p1 = $_POST['nueva_password'];
$p2 = $_POST['confirmar_password'];

// Validaciones basicas
if ($p1 !== $p2) {
    header("Location: cambiar_password.php?error=Las contraseñas no coinciden");
    exit();
}

if (strlen($p1) < 4) {
    header("Location: cambiar_password.php?error=La contraseña es muy corta");
    exit();
}

// Actualizar password
$hash = password_hash($p1, PASSWORD_DEFAULT);
$stmt = $conexion->prepare("UPDATE usuario_sistema SET contrasena = ?, debe_cambiar_password = 0 WHERE id = ?");
$stmt->bind_param("si", $hash, $uid);

if ($stmt->execute()) {
    // Vamos a buscar la info del usuario y loguearlo.
    
    $stmtUser = $conexion->prepare("SELECT * FROM usuario_sistema WHERE id = ?");
    $stmtUser->bind_param("i", $uid);
    $stmtUser->execute();
    $fila = $stmtUser->get_result()->fetch_assoc();

    $_SESSION['usuario_id'] = $fila['id'];
    $_SESSION['usuario_nombre'] = $fila['usuario'];
    $_SESSION['usuario_rol'] = $fila['rol'];
    
    // Limpiar temp
    unset($_SESSION['temp_usuario_id']);
    
    header("Location: index.php");
    exit();
} else {
    header("Location: cambiar_password.php?error=Error al actualizar la base de datos");
    exit();
}
