<?php
$tabla = $_GET['tabla'];
?>
<div class="header-title">
    <h2>Nuevo Registro en: <span style="color:var(--primary)"><?= ucfirst($tabla) ?></span></h2>
    <a href="?tabla=<?= $tabla ?>" class="btn btn-secondary" style="background:var(--secondary); color:white;">Volver</a>
</div>

<div class="card" style="max-width: 800px;">
    <form action="?operacion=procesa_insertar&tabla=<?= $tabla ?>" method="POST" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <?php
        $resultado = $conexion->query("SHOW COLUMNS FROM " . $tabla);
        while ($col = $resultado->fetch_assoc()) {
            $campo = $col['Field'];
            $tipo  = $col['Type'];
            
            // 1. OMITIR AUTO_INCREMENT Y CAMPOS DE SISTEMA
            if($col['Extra'] == 'auto_increment') continue;
            if($campo == 'created_at') continue; 
            
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
            echo "<label>".format_column_name($campo)."</label>";

            // 2. DETECCION INTELIGENTE

            // CASO A: Foreign Key (termina en _id)
            if (substr($campo, -3) == '_id') {
                $tabla_ref = substr($campo, 0, -3);
                
                // Mapeo manual de excepciones comunes
                if($tabla_ref == 'usuario') $tabla_ref = 'usuario_sistema';
                if($tabla_ref == 'jefe') $tabla_ref = 'personal'; // Ejemplo hipotetico

                // Intentar buscar datos referencia
                $sql_ref = "SELECT * FROM $tabla_ref LIMIT 100";
                $res_ref = $conexion->query($sql_ref);
                
                if($res_ref){
                   echo "<select name='$campo' required style='width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;'>";
                   echo "<option value=''>-- Seleccionar $tabla_ref --</option>";
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
                   echo "</select>";
                } else {
                   // Fallback si la tabla no existe o query falla
                   echo "<input type='number' name='$campo' placeholder='ID de $tabla_ref' required>";
                }

            }
            // CASO B: Enums
            elseif (strpos($tipo, 'enum') !== false) {
                // Parsear enum('val1','val2')
                $regex = "/'(.*?)'/";
                preg_match_all($regex, $tipo, $enum_opts);
                
                echo "<select name='$campo' style='width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;'>";
                foreach($enum_opts[1] as $opt){
                    echo "<option value='$opt'>".ucfirst($opt)."</option>";
                }
                echo "</select>";
            }
            // CASO C: Fechas
            elseif (strpos($tipo, 'date') !== false || strpos($tipo, 'time') !== false) {
                 $inputType = (strpos($tipo, 'datetime') !== false) ? 'datetime-local' : 'date';
                 echo "<input type='$inputType' name='$campo'>";
            }
            // CASO D: Texto Largo
            elseif (strpos($tipo, 'text') !== false || strpos($tipo, 'varchar(255)') !== false) {
                 echo "<textarea name='$campo' rows='3' placeholder='Ingrese $campo'></textarea>";
            }
            // CASO E: Numeros / Decimales
            elseif (strpos($tipo, 'int') !== false || strpos($tipo, 'decimal') !== false) {
                 echo "<input type='number' step='any' name='$campo' placeholder='0.00' required>";
            }
            // DEFAULT
            else {
                 echo "<input type='text' name='$campo' placeholder='Ingrese $campo' required>";
            }
            
            echo "</div>";
        }
        ?>
        <div style="grid-column: 1 / -1; margin-top: 20px;">
            <button type="submit" class="btn btn-primary" style="width:100%; padding:15px; font-size:1.1em;">Guardar Registro</button>
        </div>
    </form>
</div>
