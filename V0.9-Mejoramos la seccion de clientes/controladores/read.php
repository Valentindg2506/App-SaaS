<?php
if(!isset($_GET['tabla'])){
    echo "No se ha especificado una tabla.";
    exit();
}
$tabla = $_GET['tabla'];

// Security: Whitelist validation
$tabla = validate_table_name($tabla);

// --- SEARCH LOGIC ---
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

// NOTA: Se eliminó el filtro de privacidad de empleados para mostrar todos los clientes.

// Construir WHERE final
$whereClause = "";
if(count($conditions) > 0){
    $whereClause = " WHERE " . implode(" AND ", $conditions);
}
?>

<div class="header-title" style="flex-wrap: wrap; gap: 1rem;">
    <div style="flex:1; min-width: 200px;">
        <h2 style="color:var(--accent); margin:0;"><?= ucfirst($tabla) ?></h2>
	</div>
    
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
                                        <a href='?operacion=editar&tabla=usuario_sistema&id=<?= $u['id'] ?>&pk=id' class='btn btn-primary btn-sm'>✏️</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </details>
        <?php endforeach; ?>

    <?php else: ?>
    <table style="width:100%; border-collapse: separate; border-spacing:0;">
        <thead>
            <tr>
            <?php
            // A. HEADERS PERSONALIZADOS PARA CLIENTE
            if($tabla == 'cliente'){
                $headers_mostrar = ['Cliente', 'Empresa', 'Teléfono', 'Fase', 'Agente'];
                foreach($headers_mostrar as $h) echo "<th>$h</th>";
                echo "<th>Acciones</th>";
            } 
            // B. HEADERS AUTOMÁTICOS PARA EL RESTO
            else {
                $hidden_cols = ['id', 'contrasena', 'password', 'debe_cambiar_password'];
                if($tabla == 'aviso') $hidden_cols[] = 'usuario_id';

                $resultado = $conexion->query("SHOW COLUMNS FROM $tabla");
                $pk = "id"; 
                while ($col = $resultado->fetch_assoc()) {
                    $field = $col['Field'];
                    if($col['Key'] == 'PRI') $pk = $field;
                    if(in_array($field, $hidden_cols)) continue;
                    echo "<th>" . format_column_name($field) . "</th>";
                }
                echo "<th>Acciones</th>";
            }
            ?>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consulta General
            $query = "SELECT * FROM " . $tabla . $whereClause . " ORDER BY id DESC";
            $resultado = $conexion->query($query);
            
            if($resultado->num_rows == 0):
            ?>
                <tr><td colspan="10" style="padding:2rem; text-align:center; color:var(--text-muted);">No hay datos en esta tabla.</td></tr>
            <?php
            else:
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<tr style='border-bottom:1px solid var(--border);'>";
                    
                    // --- 1. FILA DE CLIENTE (DISEÑO ESPECIAL) ---
                    if($tabla == 'cliente'){
                        $id = $fila['id'];
                        
                        // Nombre (Link al perfil)
                        echo "<td><a href='?operacion=ver_cliente&id=$id' style='font-weight:bold; color:var(--accent); text-decoration:none;'>" . htmlspecialchars($fila['nombre_completo']) . "</a></td>";
                        
                        // Empresa
                        echo "<td>" . htmlspecialchars($fila['empresa']) . "</td>";
                        
                        // Telefono
                        echo "<td>" . htmlspecialchars($fila['telefono']) . "</td>";
                        
                        // Fase (Badge)
                        $color_fase = '#64748b'; // Default
                        $f = $fila['fase'];
                        if($f == 'Nuevo') $color_fase = '#3b82f6';
                        if($f == 'Compró') $color_fase = '#22c55e';
                        if($f == 'Interesado') $color_fase = '#0ea5e9';
                        
                        echo "<td><span style='background:$color_fase; color:white; padding:2px 8px; border-radius:10px; font-size:0.8em;'>".htmlspecialchars($f)."</span></td>";

                        // Agente (Nombre en lugar de ID)
                        $agente = "-";
                        if(!empty($fila['empleado_id'])){
                            $res_u = $conexion->query("SELECT usuario FROM usuario_sistema WHERE id='".$fila['empleado_id']."'");
                            if($r_u = $res_u->fetch_assoc()) $agente = $r_u['usuario'];
                        }
                        echo "<td><small style='color:var(--text-muted)'>$agente</small></td>";

                        // ACCIONES (VER FICHA)
                        echo "<td>
                            <a href='?operacion=ver_cliente&id=$id' class='btn btn-primary btn-sm'>👁️ Ver Ficha</a>
                        </td>";
                    } 
                    // --- 2. FILA ESTÁNDAR (OTROS) ---
                    else {
                        foreach($fila as $col_name => $valor){
                            if(in_array($col_name, $hidden_cols)) continue;
                            
                            // Formatos fecha
                            if((strpos($col_name, 'fecha') !== false || $col_name == 'created_at') && $valor){
                                $valor = date("d/m/Y", strtotime($valor));
                            }
                            // Formato empleado
                            if($col_name == 'empleado_id' && !empty($valor)){
                                 $res_u = $conexion->query("SELECT usuario FROM usuario_sistema WHERE id = '$valor'");
                                 $valor = ($res_u && $u = $res_u->fetch_assoc()) ? $u['usuario'] : $valor;
                            }

                            $texto = strlen($valor) > 50 ? substr($valor,0,50)."..." : $valor;
                            echo "<td>" . htmlspecialchars($texto) . "</td>";
                        }
                        
                        $id = $fila[$pk] ?? $fila['id'];
                        echo "<td>
                            <div style='display:flex; gap:5px;'>
                                <form action='?operacion=eliminar' method='POST' onsubmit='return confirm(\"¿Eliminar?\");' style='margin:0;'>
                                    <input type='hidden' name='tabla' value='$tabla'>
                                    <input type='hidden' name='id' value='$id'>
                                    <input type='hidden' name='pk' value='$pk'>
                                    <input type='hidden' name='csrf_token' value='".generate_csrf_token()."'>
                                    <button class='btn btn-danger btn-sm'>🗑️</button>
                                </form>
                                <a href='?operacion=editar&tabla=$tabla&id=$id&pk=$pk' class='btn btn-secondary btn-sm'>✏️</a>
                                ";
                                // Botón extra para convertir prospectos
                                if($tabla == 'prospectos'){
                                    echo "<form action='?operacion=convertir_prospecto' method='POST' style='margin:0;' onsubmit='return confirm(\"¿Convertir?\");'>
                                        <input type='hidden' name='id' value='$id'>
                                        <input type='hidden' name='csrf_token' value='".generate_csrf_token()."'>
                                        <button class='btn btn-sm' style='background:var(--success); color:white;'>✅</button>
                                    </form>";
                                }
                        echo "</div>
                        </td>";
                    }
                    echo "</tr>";
                }
            endif; 
            ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
