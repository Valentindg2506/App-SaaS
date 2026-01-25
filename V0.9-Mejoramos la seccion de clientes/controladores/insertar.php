<?php
$tabla = $_GET['tabla'];

// =================================================================================
// 1. FORMULARIO PERSONALIZADO EXCLUSIVO PARA: CLIENTES
// =================================================================================
if($tabla == 'cliente') {
?>
    <div class="header-title">
        <h2>Nuevo Cliente</h2>
        <a href="?tabla=cliente" class="btn btn-secondary">Volver</a>
    </div>

    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <form action="?operacion=procesa_insertar&tabla=cliente" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div style="background: var(--bg-body); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                <h3 style="margin-top:0; color:var(--primary); font-size:1.1rem; border-bottom:1px solid var(--border); padding-bottom:10px; margin-bottom:15px;">👤 Información de Contacto</h3>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label style="font-weight:600; margin-bottom:5px; display:block;">Nombre Completo</label>
                        <input type="text" name="nombre_completo" required placeholder="Ej: Juan Pérez" style="width:100%; padding: 10px; border:1px solid var(--border); border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600; margin-bottom:5px; display:block;">Teléfono</label>
                        <input type="text" name="telefono" required placeholder="(555) 000-0000" style="width:100%; padding: 10px; border:1px solid var(--border); border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600; margin-bottom:5px; display:block;">Email</label>
                        <input type="email" name="email" placeholder="cliente@email.com" style="width:100%; padding: 10px; border:1px solid var(--border); border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600; margin-bottom:5px; display:block;">Dirección</label>
                        <input type="text" name="direccion" placeholder="Calle, Ciudad, Zip Code" style="width:100%; padding: 10px; border:1px solid var(--border); border-radius: 6px;">
                    </div>
                </div>
            </div>

            <div style="background: var(--bg-body); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                <h3 style="margin-top:0; color:var(--primary); font-size:1.1rem; border-bottom:1px solid var(--border); padding-bottom:10px; margin-bottom:15px;">🏢 Datos Fiscales y Compañía</h3>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label style="font-weight:600; margin-bottom:5px; display:block;">Nombre de Compañía</label>
                        <input type="text" name="empresa" placeholder="Nombre Legal LLC/Inc" style="width:100%; padding: 10px; border:1px solid var(--border); border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600; margin-bottom:5px; display:block;">EIN (Employer ID)</label>
                        <input type="text" name="ein" placeholder="XX-XXXXXXX" style="width:100%; padding: 10px; border:1px solid var(--border); border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600; margin-bottom:5px; display:block;">Social Security</label>
                        <input type="text" name="ssn" placeholder="XXX-XX-XXXX" style="width:100%; padding: 10px; border:1px solid var(--border); border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600; margin-bottom:5px; display:block;">Últimos dígitos (SSN/ITIN)</label>
                        <input type="text" name="ultimos_digitos" placeholder="Ej: 1234" style="width:100%; padding: 10px; border:1px solid var(--border); border-radius: 6px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600; margin-bottom:5px; display:block;">Cantidad de Empleados</label>
                        <input type="number" name="cantidad_empleados" value="0" style="width:100%; padding: 10px; border:1px solid var(--border); border-radius: 6px;">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label style="font-size:1.1rem; color:var(--primary); font-weight:bold; margin-bottom:5px; display:block;">📝 Requerimiento / Notas</label>
                <textarea name="requerimiento" rows="4" placeholder="Describa el servicio o necesidad del cliente..." style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px; background:var(--bg-surface); color:var(--text-main);"></textarea>
            </div>

            <input type="hidden" name="fase" value="Nuevo">
            
            <div style="margin-top: 25px; text-align: right;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 1rem;">Guardar Cliente</button>
            </div>
        </form>
    </div>
<?php
    // IMPORTANTE: Detenemos la ejecución aquí para que NO cargue el formulario genérico de abajo
    return;
}

// =================================================================================
// 2. FORMULARIO GENÉRICO (PARA EL RESTO DE TABLAS: AVISOS, USUARIOS, ETC.)
// =================================================================================
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
        
        if($resultado) {
            while ($col = $resultado->fetch_assoc()) {
                $campo = $col['Field'];
                $tipo  = $col['Type'];
                
                // 1. OMITIR AUTO_INCREMENT Y CAMPOS DE SISTEMA
                if($col['Extra'] == 'auto_increment') continue;
                if($campo == 'created_at') continue; 
                
                // 2. OMITIR CAMPOS INTERNOS
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

                // CASO A: Contraseña
                if ($campo == 'contrasena' || $campo == 'password') {
                    echo "<input type='password' name='$campo' placeholder='Ingrese contraseña segura' required style='$inputStyle'>";
                }
                // CASO B: Foreign Key (termina en _id)
                elseif (substr($campo, -3) == '_id') {
                    $tabla_ref = substr($campo, 0, -3);
                    
                    // Mapeo manual de excepciones comunes
                    if($tabla_ref == 'usuario') $tabla_ref = 'usuario_sistema';
                    if($tabla_ref == 'jefe') $tabla_ref = 'personal'; 

                    // CASO ESPECIAL: empleado_id (Asignacion)
                    if($campo == 'empleado_id') {
                        // Si soy empleado, no puedo asignar.
                        if($_SESSION['usuario_rol'] == 'empleado'){
                            continue; 
                        }
                        $tabla_ref = 'usuario_sistema'; // Forzar fuente
                    }
                    
                    echo "<select name='$campo' style='$inputStyle'>";
                    echo "<option value=''>-- Seleccionar $tabla_ref --</option>";
                    
                    // Intentar buscar datos referencia
                    $sql_ref = "SELECT * FROM $tabla_ref LIMIT 100";
                    
                    // FILTRO SOLO EMPLEADOS
                    if($campo == 'empleado_id'){
                        $sql_ref = "SELECT * FROM usuario_sistema WHERE rol = 'empleado' ORDER BY nombre_completo ASC";
                    }
                    
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
                // CASO F: Rol (Dropdown Hardcoded)
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
        } else {
            echo "<p>Error al cargar columnas: " . $conexion->error . "</p>";
        }
        ?>
        <div style="grid-column: 1 / -1; margin-top: 20px;">
            <button type="submit" class="btn btn-primary" style="width:100%; padding:15px; font-size:1.1em;">Guardar Registro</button>
        </div>
    </form>
</div>
