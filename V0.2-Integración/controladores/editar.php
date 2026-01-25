<?php
$tabla = $_GET['tabla'];
$id = $_GET['id'];
$pk = $_GET['pk'];

// Obtener datos actuales
$query = "SELECT * FROM $tabla WHERE $pk = '$id'";
$resultado = $conexion->query($query);
$actual = $resultado->fetch_assoc();
?>
<div class="header-title">
    <h2>Editar Registro en: <span style="color:var(--primary)"><?= ucfirst($tabla) ?></span></h2>
    <a href="?tabla=<?= $tabla ?>" class="btn btn-secondary" style="background:var(--secondary); color:white;">Volver</a>
</div>

<div class="card" style="max-width: 600px;">
    <form action="?operacion=procesa_editar&tabla=<?= $tabla ?>&id=<?= $id ?>&pk=<?= $pk ?>" method="POST">
        <?php
        $resultado = $conexion->query("SHOW COLUMNS FROM " . $tabla);
        while ($col = $resultado->fetch_assoc()) {
            $campo = $col['Field'];
            // Si es PK, no dejar editar (o mostrar disabled)
            $is_pk = ($campo == $pk);
            $val = isset($actual[$campo]) ? $actual[$campo] : '';
            
            echo "
            <div class='form-group'>
                <label>".ucfirst($campo)."</label>
                <input type='text' name='".$campo."' value='".$val."' ".($is_pk ? 'readonly style="background:#f1f5f9; color:#94a3b8;"' : '').">
            </div>
            ";
        }
        ?>
        <button type="submit" class="btn btn-primary">Actualizar Registro</button>
    </form>
</div>
