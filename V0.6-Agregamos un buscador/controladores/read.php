<?php
if(!isset($_GET['tabla'])){
    echo "No se ha especificado una tabla.";
    exit();
}
$tabla = $_GET['tabla'];

// Security: Whitelist validation
$tabla = validate_table_name($tabla);

// --- SEARCH LOGIC ---
// --- SEARCH & FILTER LOGIC ---
$conditions = [];

// A. Filtro de Búsqueda
$busqueda = "";
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $busqueda = trim($_GET['q']);
    $search = $conexion->real_escape_string($busqueda);
    $colsSearch = [];
    
    // Dynamically find searchable columns
    $resCols = $conexion->query("SHOW COLUMNS FROM $tabla");
    if($resCols){
        while($c = $resCols->fetch_assoc()){
            $type = strtolower($c['Type']);
            $field = $c['Field'];
            
            // Allow search in: char, text, varchar, int (ids), decimal (prices)
            if(
                strpos($type, 'char') !== false || 
                strpos($type, 'text') !== false || 
                strpos($type, 'int') !== false ||
                strpos($type, 'decimal') !== false
            ){
                $colsSearch[] = "`$field` LIKE '%$search%'";
            }
        }
    }
    
    if(count($colsSearch) > 0){
        $conditions[] = "(" . implode(" OR ", $colsSearch) . ")";
    }
}

// B. Filtro de Privacidad (Empleados)
if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'empleado'){
    // Tablas restringidas
    if(in_array($tabla, ['cliente', 'prospectos'])){
        $my_id = $_SESSION['usuario_id'];
        $conditions[] = "empleado_id = '$my_id'"; 
    }
}

// Construir WHERE final
$whereClause = "";
if(count($conditions) > 0){
    $whereClause = " WHERE " . implode(" AND ", $conditions);
}
?>

<div class="header-title" style="flex-wrap: wrap; gap: 1rem;">
    <div style="flex:1; min-width: 200px;">
        <h2>Gestionando: <span style="color:var(--accent)"><?= ucfirst($tabla) ?></span></h2>
    </div>
    
    <!-- PREMIUM SEARCH FORM -->
    <form method="GET" style="display:flex; align-items:center; width: 100%; max-width: 400px; position:relative;">
        <input type="hidden" name="tabla" value="<?= $tabla ?>">
        <span style="position:absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1em; pointer-events: none;">🔍</span>
        <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>" 
               placeholder="Buscar en <?= ucfirst($tabla) ?>..." 
               style="width:100%; padding: 0.8rem 1rem 0.8rem 2.8rem; border: 1px solid var(--border); border-radius: 50px; outline:none; background: var(--bg-surface); color: var(--text-main); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); transition: all 0.2s;">
        
        <?php if(!empty($busqueda)): ?>
            <a href="?tabla=<?= $tabla ?>" style="position:absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); text-decoration:none; font-weight:bold; cursor:pointer; font-size: 1.1em;" title="Limpiar">✕</a>
        <?php endif; ?>
    </form>

    <div style="margin-left:auto;">
        <a href="?operacion=insertar&tabla=<?= $tabla ?>" class="btn btn-primary">
            <span>+</span> Nuevo
        </a>
    </div>
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
                                        <form action="?operacion=eliminar" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar usuario?');">
                                            <input type="hidden" name="tabla" value="usuario_sistema">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="pk" value="id">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                        </form>
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
            
            // Determine Sort Column (default to created_at, fallback to fecha or id)
            $sortCol = 'id'; // Fallback
            // Check if created_at or fecha exists in the columns we just fetched
            // We need to re-fetch or store them? We didn't store all field names.
            // Let's simple-check specific tables or re-check columns.
            // Actually, we can just use a simple mapping or check.
            $res = $conexion->query("SHOW COLUMNS FROM $tabla LIKE 'created_at'");
            if($res && $res->num_rows > 0) $sortCol = 'created_at';
            else {
                $res2 = $conexion->query("SHOW COLUMNS FROM $tabla LIKE 'fecha'");
                if($res2 && $res2->num_rows > 0) $sortCol = 'fecha';
                else {
                    $res3 = $conexion->query("SHOW COLUMNS FROM $tabla LIKE 'fecha_pedido'");
                     if($res3 && $res3->num_rows > 0) $sortCol = 'fecha_pedido';
                }
            }
            ?>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Updated query with search filter using dynamic sort column
            $query = "SELECT * FROM " . $tabla . $whereClause . " ORDER BY $sortCol DESC";
            $resultado = $conexion->query($query);
            
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

                    // FORMATO CASO ESPECIAL: EMPLEADO_ID (Mostrar Nombre)
                    if($col_name == 'empleado_id'){
                        if(!empty($valor)){
                            // Fetch user name (Inefficient but works for read.php generic view)
                            $res_u = $conexion->query("SELECT nombre_completo, usuario FROM usuario_sistema WHERE id = '$valor'");
                            if($res_u && $u_row = $res_u->fetch_assoc()){
                                $valor = !empty($u_row['nombre_completo']) ? $u_row['nombre_completo'] : $u_row['usuario'];
                            } else {
                                $valor = "ID: $valor (N/A)";
                            }
                        } else {
                            $valor = "- No Asignado -";
                        }
                    }

                    // Limitar longitud
                    $texto = strlen($valor) > 50 ? substr($valor,0,50)."..." : $valor;
                    echo "<td>" . htmlspecialchars($texto) . "</td>";
                }
                
                // Botones de acción
                $id = $fila[$pk] ?? ''; // Use Null Coalesce in case PK is weird
                echo "<td>
                    <div style='display:flex; gap:5px; align-items:center;'>";
                        
                if ($tabla == 'prospectos') {
                    echo "
                        <form action='?operacion=convertir_prospecto' method='POST' style='margin:0;' onsubmit='return confirm(\"¿Convertir prospecto a cliente?\");'>
                            <input type='hidden' name='id' value='$id'>
                            <input type='hidden' name='csrf_token' value='".generate_csrf_token()."'>
                            <button type='submit' class='btn' style='background:var(--success); color:white; border:1px solid var(--success); padding:0.4rem 0.6rem; font-size:1rem; line-height:1;' title='Convertir a Cliente'>✅</button>
                        </form>
                    ";
                }
                
                echo "
                        <form action='?operacion=eliminar' method='POST' style='margin:0;' onsubmit='return confirm(\"¿Estás seguro de eliminar este registro?\");'>
                            <input type='hidden' name='tabla' value='$tabla'>
                            <input type='hidden' name='id' value='$id'>
                            <input type='hidden' name='pk' value='$pk'>
                            <input type='hidden' name='csrf_token' value='".generate_csrf_token()."'>
                            <button type='submit' class='btn btn-danger' style='padding:0.4rem 0.6rem; font-size:1rem; line-height:1;'>🗑️</button>
                        </form>
                        <a href='?operacion=editar&tabla=$tabla&id=$id&pk=$pk' 
                           class='btn btn-primary' style='padding:0.4rem 0.6rem; font-size:1rem; line-height:1;'>
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
