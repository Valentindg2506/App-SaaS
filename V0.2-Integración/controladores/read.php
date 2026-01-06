<?php
if(!isset($_GET['tabla'])){
    echo "No se ha especificado una tabla.";
    exit();
}
$tabla = $_GET['tabla'];
?>

<div class="header-title">
    <h2>Gestionando: <span style="color:var(--primary)"><?= ucfirst($tabla) ?></span></h2>
    <a href="?operacion=insertar&tabla=<?= $tabla ?>" class="btn btn-primary">
        <span>+</span> Nuevo Registro
    </a>
</div>

<div class="card" style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <?php
                // Obtener columnas
                $columnas = [];
                $resultado = $conexion->query("SHOW COLUMNS FROM " . $tabla);
                $pk = "";
                while ($col = $resultado->fetch_assoc()) {
                    $columnas[] = $col['Field'];
                    if($col['Key'] == 'PRI'){
                        $pk = $col['Field'];
                    }
                    echo "<th>" . $col['Field'] . "</th>";
                }
                // Si no encontramos PK explicita, usamos la primera columna
                if($pk == "" && count($columnas) > 0){
                    $pk = $columnas[0];
                }
                ?>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $resultado = $conexion->query("SELECT * FROM " . $tabla);
            while ($fila = $resultado->fetch_assoc()) {
                echo "<tr>";
                foreach($fila as $valor){
                    // Limitar longitudes de texto para no romper la tabla
                    $texto = strlen($valor) > 50 ? substr($valor,0,50)."..." : $valor;
                    echo "<td>" . htmlspecialchars($texto) . "</td>";
                }
                
                // Botones de acción
                $id = $fila[$pk];
                echo "<td>
                    <div style='display:flex; gap:5px;'>
                        <a href='?operacion=eliminar&tabla=$tabla&id=$id&pk=$pk' 
                           class='btn btn-danger btn-sm'
                           onclick='return confirm(\"¿Estás seguro de eliminar este registro?\");'>
                           🗑️
                        </a>
                        <a href='?operacion=editar&tabla=$tabla&id=$id&pk=$pk' 
                           class='btn btn-primary btn-sm' style='background:var(--secondary);'>
                           ✏️
                        </a>
                    </div>
                </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    
    <?php if($resultado->num_rows == 0): ?>
        <div style="padding:2rem; text-align:center; color:var(--text-muted);">
            No hay datos en esta tabla.
        </div>
    <?php endif; ?>
</div>
