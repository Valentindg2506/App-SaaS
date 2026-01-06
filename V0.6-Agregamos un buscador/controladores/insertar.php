<?php
$tabla = $_GET['tabla'];
?>
<div class="header-title">
    <h2>Nuevo Registro en: <span style="color:var(--primary)"><?= ucfirst($tabla) ?></span></h2>
    <a href="?tabla=<?= $tabla ?>" class="btn btn-secondary">Volver</a>
</div>

<div class="card" style="max-width: 800px;">
    <form action="?operacion=procesa_insertar&tabla=<?= $tabla ?>" method="POST" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <?php
        // CSS Styles using Variables for Light/Dark Compatibility
        $inputStyle = "width:100%; padding: 0.75rem 1rem; border:1px solid var(--border); border-radius: var(--radius); background-color: var(--bg-surface); color: var(--text-main); outline: none;";
        $labelStyle = "display:block; margin-bottom:0.5rem; font-weight:600; color: var(--text-main); font-size: 0.9rem;";

        $resultado = $conexion->query("SHOW COLUMNS FROM " . $tabla);
        while ($col = $resultado->fetch_assoc()) {
            $campo = $col['Field'];
            $tipo  = $col['Type'];
            
            // 1. OMITIR AUTO_INCREMENT Y CAMPOS DE SISTEMA
            if($col['Extra'] == 'auto_increment') continue;
            if($campo == 'created_at') continue; 
            
            // 2. OMITIR CAMPOS INTERNOS (Nuevo)
            if($campo == 'debe_cambiar_password') continue;

            // CASO ESPECIAL: usuario_id en avisos (Auto-fill)
            if($tabla == 'aviso' && $campo == 'usuario_id'){
                echo "<input type='hidden' name='usuario_id' value='".$_SESSION['usuario_id']."'>";
                continue;
            }

            // CASO ESPECIAL: Alcance para empleados (Solo Personal)
            if($tabla == 'aviso' && $campo == 'alcance' && $_SESSION['usuario_rol'] == 'empleado'){
                 echo "<input type='hidden' name='alcance' value='personal'>";
                 continue; // No mostrar el select
            }
            
            echo "<div class='form-group'>";
            echo "<label style='$labelStyle'>".format_column_name($campo)."</label>";

            // 3. DETECCION INTELIGENTE

            // CASO A: Contraseña (Nuevo)
            if ($campo == 'contrasena' || $campo == 'password') {
                echo "<input type='password' name='$campo' placeholder='Ingrese contraseña segura' required style='$inputStyle'>";
            }
            // CASO B: Foreign Key (termina en _id)
            elseif (substr($campo, -3) == '_id') {
                $tabla_ref = substr($campo, 0, -3);
                
                // Mapeo manual de excepciones comunes
                if($tabla_ref == 'usuario') $tabla_ref = 'usuario_sistema';
                if($tabla_ref == 'jefe') $tabla_ref = 'personal'; 
                
                echo "<select name='$campo' required style='$inputStyle'>";
                echo "<option value=''>-- Seleccionar $tabla_ref --</option>";
                
                // Intentar buscar datos referencia
                $sql_ref = "SELECT * FROM $tabla_ref LIMIT 100";
                $res_ref = $conexion->query($sql_ref);
                if($res_ref){
                   while($row_ref = $res_ref->fetch_assoc()){
                       // Intentar encontrar columna descriptiva
                       $display_text = $row_ref['id']; // Fallback
                       foreach(['nombre','titulo','usuario','apellido','razon_social'] as $candidate){
                           if(isset($row_ref[$candidate])){
                               $display_text = $row_ref[$candidate];
                               if(isset($row_ref['apellido'])) $display_text .= " " . $row_ref['apellido'];
                               break;
                           }
                       }
                       echo "<option value='".$row_ref['id']."'>$display_text</option>";
                   }
                }
                echo "</select>";
            }
            // CASO C: Enums
            elseif (strpos($tipo, 'enum') !== false) {
                // Parsear enum('val1','val2')
                $regex = "/'(.*?)'/";
                preg_match_all($regex, $tipo, $enum_opts);
                
                echo "<select name='$campo' style='$inputStyle'>";
                foreach($enum_opts[1] as $opt){
                    echo "<option value='$opt'>".ucfirst($opt)."</option>";
                }
                echo "</select>";
            }
            // CASO D: Fechas
            elseif (strpos($tipo, 'date') !== false || strpos($tipo, 'time') !== false) {
                 $inputType = (strpos($tipo, 'datetime') !== false) ? 'datetime-local' : 'date';
                 echo "<input type='$inputType' name='$campo' style='$inputStyle'>";
            }
            // CASO E: Texto Largo
            elseif (strpos($tipo, 'text') !== false) {
                 echo "<textarea name='$campo' rows='3' placeholder='Ingrese $campo' style='$inputStyle'></textarea>";
            }
            // CASO F: Rol (Dropdown Hardcoded) - Nuevo Requerimiento
            elseif ($campo == 'rol') {
                $roles = ['admin', 'jefe', 'subjefe', 'supervisor', 'empleado'];
                echo "<select name='$campo' required style='$inputStyle'>";
                echo "<option value=''>-- Seleccionar Rol --</option>";
                foreach($roles as $r){
                    echo "<option value='$r'>".ucfirst($r)."</option>";
                }
                echo "</select>";
            }
            // CASO G: Numeros / Decimales
            elseif (strpos($tipo, 'int') !== false || strpos($tipo, 'decimal') !== false) {
                 echo "<input type='number' step='any' name='$campo' placeholder='0.00' required style='$inputStyle'>";
            }
            // DEFAULT
            else {
                 echo "<input type='text' name='$campo' placeholder='Ingrese $campo' required style='$inputStyle'>";
            }
            
            echo "</div>";
        }
        ?>
        <div style="grid-column: 1 / -1; margin-top: 20px;">
            <button type="submit" class="btn btn-primary" style="width:100%; padding:15px; font-size:1.1em;">Guardar Registro</button>
        </div>
    </form>
</div>
