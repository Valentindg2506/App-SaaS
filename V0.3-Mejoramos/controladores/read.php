<?php
if(!isset($_GET['tabla'])){
    echo "No se ha especificado una tabla.";
    exit();
}
$tabla = $_GET['tabla'];
?>

<div class="header-title">
    <h2>Gestionando: <span style="color:var(--accent)"><?= ucfirst($tabla) ?></span></h2>
    <a href="?operacion=insertar&tabla=<?= $tabla ?>" class="btn btn-primary">
        <span>+</span> Nuevo Registro
    </a>
</div>

<div class="card" style="overflow-x:auto;">
    <table>
        <thead>
            <?php
            // Definir columnas a ocultar
            $hidden_cols = ['id', 'contrasena']; // Global hidden
            if($tabla == 'aviso') {
                $hidden_cols[] = 'usuario_id';
            }

            // HEADER
            // Obtener columnas
            $columnas = [];
            $resultado = $conexion->query("SHOW COLUMNS FROM " . $tabla);
            $pk = "";
            while ($col = $resultado->fetch_assoc()) {
                $field = $col['Field'];
                // Guardar PK
                if($col['Key'] == 'PRI'){
                    $pk = $field;
                }
                
                // Si esta en lista oculta, saltar VISUALMENTE
                if(in_array($field, $hidden_cols)) continue;

                $columnas[] = $field; // Guardamos solo las visibles para el body? No, necesitamos saber cuales mostrar luego
                echo "<th>" . format_column_name($field) . "</th>";
            }
            // Si no encontramos PK explicita, usamos la primera columna (aunque este oculta, la necesitamos para acciones)
            if($pk == "" && $resultado->num_rows > 0){
                // Re-query or assume id logic handled elsewhere. 
                // In SHOW COLUMNS loop we usually find it.
                $pk = "id"; 
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
                foreach($fila as $col_name => $valor){
                    // FILTRO VISUAL
                    if(in_array($col_name, $hidden_cols)) continue;

                    // FORMATO FECHA (Solo para avisos o global si parece fecha)
                    // El usuario pidio especificamente para avisos, pero es mejor global si es timestamp
                    // Deteccion simple por nombre o contenido
                    if(strpos($col_name, 'fecha') !== false || $col_name == 'created_at'){
                        if($valor){
                            $valor = date("d/m/Y H:i", strtotime($valor));
                        }
                    }

                    // Limitar longitud
                    $texto = strlen($valor) > 50 ? substr($valor,0,50)."..." : $valor;
                    echo "<td>" . htmlspecialchars($texto) . "</td>";
                }
                
                // Botones de acción
                $id = $fila[$pk] ?? ''; // Use Null Coalesce in case PK is weird
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
