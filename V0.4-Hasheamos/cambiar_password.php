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
    <title>Cambiar Contraseña | Admin Panel</title>
    <link rel="stylesheet" href="css/estilo.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 5% auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background-color: #4338ca;
        }
        .error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 style="text-align:center; margin-bottom: 1.5rem; color:#1e293b;">Cambiar Contraseña</h2>
        
        <p style="text-align:center; color:#64748b; margin-bottom: 2rem; font-size:0.95rem;">
            Por seguridad, debes cambiar tu contraseña antes de continuar.
        </p>

        <?php if(isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form method="POST" action="procesa_cambiar_password.php">
            <input type="password" name="nueva_password" placeholder="Nueva Contraseña" required minlength="4">
            <input type="password" name="confirmar_password" placeholder="Confirmar Contraseña" required minlength="4">
            <button type="submit">Actualizar Contraseña</button>
        </form>
    </div>
</body>
</html>
