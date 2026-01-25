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
    <a href="?tabla=<?= $tabla ?>" class="btn btn-secondary">Volver</a>
</div>

<div class="card" style="max-width: 800px;">
    <form action="?operacion=procesa_editar&tabla=<?= $tabla ?>&id=<?= $id ?>&pk=<?= $pk ?>" method="POST" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <?php
        $resultado = $conexion->query("SHOW COLUMNS FROM " . $tabla);
        while ($col = $resultado->fetch_assoc()) {
            $campo = $col['Field'];
            $tipo  = $col['Type'];
            $val   = isset($actual[$campo]) ? $actual[$campo] : '';
            
            // Si es PK, readonly
            $is_pk = ($campo == $pk);
            $readonly = $is_pk ? 'readonly style="background:#f1f5f9; color:#94a3b8;"' : '';
            
            // Si es created_at (y no es editable), saltar o readonly
            if($campo == 'created_at') continue;

            echo "<div class='form-group'>";
            echo "<label>".format_column_name($campo)."</label>";

            // 1. PK Logic
            if($is_pk){
                echo "<input type='text' name='$campo' value='$val' $readonly>";
            }
            // 2. FOREIGN KEYS
            elseif (substr($campo, -3) == '_id') {
                $tabla_ref = substr($campo, 0, -3);
                if($tabla_ref == 'usuario') $tabla_ref = 'usuario_sistema';
               
                $sql_ref = "SELECT * FROM $tabla_ref LIMIT 100";
                $res_ref = $conexion->query($sql_ref);
                
                if($res_ref){
                   echo "<select name='$campo' style='width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;'>";
                   while($row_ref = $res_ref->fetch_assoc()){
                       $display_text = $row_ref['id']; 
                       foreach(['nombre','titulo','usuario','apellido','razon_social'] as $candidate){
                           if(isset($row_ref[$candidate])){
                               $display_text = $row_ref[$candidate];
                               if(isset($row_ref['apellido'])) $display_text .= " " . $row_ref['apellido'];
                               break;
                           }
                       }
                       $selected = ($row_ref['id'] == $val) ? "selected" : "";
                       echo "<option value='".$row_ref['id']."' $selected>$display_text</option>";
                   }
                   echo "</select>";
                } else {
                   echo "<input type='number' name='$campo' value='$val' required>";
                }
            }
            // 3. ENUMS
            elseif (strpos($tipo, 'enum') !== false) {
                $regex = "/'(.*?)'/";
                preg_match_all($regex, $tipo, $enum_opts);
                echo "<select name='$campo' style='width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;'>";
                foreach($enum_opts[1] as $opt){
                    $selected = ($opt == $val) ? "selected" : "";
                    echo "<option value='$opt' $selected>".ucfirst($opt)."</option>";
                }
                echo "</select>";
            }
            // 4. Fechas
            elseif (strpos($tipo, 'date') !== false || strpos($tipo, 'time') !== false) {
                 $inputType = (strpos($tipo, 'datetime') !== false) ? 'datetime-local' : 'date';
                 // Formato value para input date: Y-m-d (eliminar H:i:s si es date puro)
                 if($inputType == 'date' && strlen($val) > 10) $val = substr($val, 0, 10);
                 if($inputType == 'datetime-local') $val = str_replace(' ', 'T', $val); // ISO format required
                 
                 echo "<input type='$inputType' name='$campo' value='$val'>";
            }
            // 5. Textarea
            elseif (strpos($tipo, 'text') !== false || strpos($tipo, 'varchar(255)') !== false) {
                 // CASO ESPECIAL: Rol en Editar
                 if($campo == 'rol'){
                    $roles = ['admin', 'jefe', 'subjefe', 'supervisor', 'empleado'];
                    echo "<select name='$campo' style='width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;'>";
                    foreach($roles as $r){
                        $selected = ($r == $val) ? 'selected' : '';
                        echo "<option value='$r' $selected>".ucfirst($r)."</option>";
                    }
                    echo "</select>";
                 } else {
                     echo "<textarea name='$campo' rows='3'>$val</textarea>";
                 }
            }
            // 6. Numeros
            elseif (strpos($tipo, 'int') !== false || strpos($tipo, 'decimal') !== false) {
                 echo "<input type='number' step='any' name='$campo' value='$val' required>";
            }
            // Default
            else {
                 echo "<input type='text' name='$campo' value='$val' required>";
            }
            echo "</div>";
        }
        ?>
        <div style="grid-column: 1 / -1; margin-top: 20px;">
            <button type="submit" class="btn btn-primary" style="width:100%; padding:15px;">Actualizar Registro</button>
        </div>
    </form>
</div>
