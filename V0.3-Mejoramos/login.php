<?php
session_start();
include "inc/conexion_bd.php"; // Need connection

if (isset($_SESSION['usuario_rol'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // Consulta segura
    $stmt = $conexion->prepare("SELECT * FROM usuario_sistema WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($fila = $resultado->fetch_assoc()) {
        // En producción: if (password_verify($contrasena, $fila['contrasena'])) {
        if ($contrasena == $fila['contrasena']) { // Plan text for now
            $_SESSION['usuario_id'] = $fila['id'];
            $_SESSION['usuario_nombre'] = $fila['usuario'];
            $_SESSION['usuario_rol'] = $fila['rol']; // Store Role
            header("Location: index.php");
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Admin Panel</title>
    <link rel="stylesheet" href="css/estilo.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo-container">
            🚀
        </div>
        <h1>Panel de Administración</h1>
        
        <?php if($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 1rem;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <p style="margin-top: 1rem; font-size: 0.8rem; color: #64748b;">
            Usuarios demo: admin, jefe, subjefe, supervisor, empleado<br>
            Contraseña: 1234
        </p>
    </div>
</body>
</html>
