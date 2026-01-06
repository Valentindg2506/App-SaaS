<?php
$tabla = $_GET['tabla'];
?>
<div class="header-title">
    <h2>Nuevo Registro en: <span style="color:var(--primary)"><?= ucfirst($tabla) ?></span></h2>
    <a href="?tabla=<?= $tabla ?>" class="btn btn-secondary" style="background:var(--secondary); color:white;">Volver</a>
</div>

<div class="card" style="max-width: 600px;">
    <form action="?operacion=procesa_insertar&tabla=<?= $tabla ?>" method="POST">
        <?php
        $resultado = $conexion->query("SHOW COLUMNS FROM " . $tabla);
        while ($col = $resultado->fetch_assoc()) {
            $campo = $col['Field'];
            // Si es auto_increment, saltar
            if($col['Extra'] == 'auto_increment') continue;
            
            echo "
            <div class='form-group'>
                <label>".ucfirst($campo)."</label>
                <input type='text' name='".$campo."' placeholder='Ingrese ".$campo."' required>
            </div>
            ";
        }
        ?>
        <button type="submit" class="btn btn-primary">Guardar Registro</button>
    </form>
</div>
