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
    <?php if($tabla == 'usuario_sistema'): ?>
        <!-- LOGICA DE MENU DESPLEGABLE POR ROL -->
        <?php
        // 1. Fetch Users
        $res = $conexion->query("SELECT * FROM usuario_sistema ORDER BY rol, usuario");
        $usersByRole = [];
        $rolesOrder = ['admin', 'jefe', 'subjefe', 'supervisor', 'empleado']; // Orden deseado

        while($r = $res->fetch_assoc()){
            $usersByRole[$r['rol']][] = $r;
        }

        // 2. Render Accordions
        foreach($rolesOrder as $rolName):
            if(empty($usersByRole[$rolName])) continue;
            $users = $usersByRole[$rolName];
        ?>
            <details style="margin-bottom: 1rem; background: var(--bg-card); border-radius: 8px; overflow: hidden; border: 1px solid var(--border);">
                <summary style="padding: 1rem; cursor: pointer; background: var(--primary-light); color: white; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
                   <span style="text-transform: uppercase;">🔹 <?= $rolName ?> <small style="opacity:0.8; font-weight:normal;">(<?= count($users) ?>)</small></span>
                   <span>▼</span>
                </summary>
                <div style="padding: 1rem; overflow-x: auto;">
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                                <th style="padding:10px; color:var(--text-muted);">Usuario</th>
                                <th style="padding:10px; color:var(--text-muted);">Nombre Completo</th>
                                <th style="padding:10px; color:var(--text-muted);">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding:10px;"><?= htmlspecialchars($u['usuario']) ?></td>
                                <td style="padding:10px;"><?= htmlspecialchars($u['nombre_completo']) ?></td>
                                <td style="padding:10px;">
                                    <div style="display:flex; gap:5px;">
                                        <a href='?operacion=eliminar&tabla=usuario_sistema&id=<?= $u['id'] ?>&pk=id' 
                                           class='btn btn-danger btn-sm'
                                           onclick='return confirm("¿Eliminar usuario?");'>🗑️</a>
                        <a href='?operacion=editar&tabla=usuario_sistema&id=<?= $u['id'] ?>&pk=id' 
                                           class='btn btn-primary btn-sm'>✏️</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </details>
        <?php endforeach; ?>

        <!-- Mostrar otros roles no definidos en el orden -->
        <?php foreach($usersByRole as $rolName => $users): 
             if(in_array($rolName, $rolesOrder)) continue; 
        ?>
            <details style="margin-bottom: 1rem; background: var(--bg-card); border-radius: 8px; overflow: hidden; border: 1px solid var(--border);">
                <summary style="padding: 1rem; cursor: pointer; background: #64748b; color: white; font-weight: bold;">
                   <span style="text-transform: uppercase;">🔸 <?= $rolName ?></span>
                </summary>
                <div style="padding: 1rem;">
                     <!-- Misma tabla de arriba, simplificada -->
                     <table style="width:100%">
                        <?php foreach($users as $u): ?>
                            <tr>
                                <td style="padding:5px;"><?= $u['usuario'] ?></td>
                                <td style="padding:5px; text-align:right;">
                                    <a href='?operacion=editar&tabla=usuario_sistema&id=<?= $u['id'] ?>&pk=id'>✏️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                     </table>
                </div>
            </details>
        <?php endforeach; ?>

    <?php else: ?>
    <!-- TABLA STANDARD PARA OTROS -->
    <table>
        <thead>
            <tr>
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
                           class='btn btn-primary btn-sm'>
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
    <?php endif; ?>
</div>
