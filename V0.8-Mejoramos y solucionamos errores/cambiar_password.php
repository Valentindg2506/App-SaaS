<?php
session_start();
if (!isset($_SESSION['temp_usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña | App SaaS</title>
    <link rel="stylesheet" href="css/estilo.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo-container">
            🚀
        </div>
        <h1>Cambiar Contraseña</h1>
        
        <p>
            Por seguridad, debes cambiar tu contraseña antes de continuar.
        </p>

        <?php if(isset($_GET['error'])): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 1rem;">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="procesa_cambiar_password.php">
            <input type="password" name="nueva_password" placeholder="Nueva Contraseña" required minlength="4">
            <input type="password" name="confirmar_password" placeholder="Confirmar Contraseña" required minlength="4">
            <button type="submit">Actualizar Contraseña</button>
        </form>
    </div>
</body>
</html>
