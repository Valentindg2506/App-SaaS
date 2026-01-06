<?php
/**
 * CONFIGURACIÓN DEL MENÚ LATERAL
 */
// Importar configuración de roles
if(!isset($permisos_roles)) include "inc/config_roles.php";

$rol_actual = isset($_SESSION['usuario_rol']) ? $_SESSION['usuario_rol'] : 'empleado';

$menu_items = [
    ["label" => "Errores", "tabla" => "registro_log", "icono" => "⚠️"],
    ["label" => "Usuarios Sistema", "tabla" => "usuario_sistema", "icono" => "🔐"],
    ["label" => "Clientes",     "tabla" => "cliente",     "icono" => "👥"],
    ["label" => "Avisos",       "tabla" => "aviso",       "icono" => "🔔"],
    ["label" => "Servicios",    "tabla" => "servicio",    "icono" => "🛠️"],
    ["label" => "Pedidos",      "tabla" => "pedido",      "icono" => "🛒"],
    ["label" => "Facturas",     "tabla" => "factura",     "icono" => "📄"],
    ["label" => "Personal",    "tabla" => "personal",    "icono" => "👤"],
    ["label" => "Pagos",        "tabla" => "pago",        "icono" => "💰"],

];

foreach ($menu_items as $item) {
    // FILTRO DE SEGURIDAD VISUAL
    // Si no tiene acceso, saltar este item
    if (!tiene_acceso($rol_actual, $item['tabla'])) {
        continue;
    }

    $nombre_tabla = $item['tabla'];
    $label = $item['label'];
    $icono = isset($item['icono']) ? $item['icono'] : '📂';
    
    $clase = "";
    if(isset($_GET['tabla'])){
        if($nombre_tabla == $_GET['tabla']){
            $clase = "activo";
        }
    }
    
    echo '
    <li>
        <a href="?tabla='.$nombre_tabla.'" class="'.$clase.'">
            <span style="font-size:1.2em">'.$icono.'</span>
            '.$label.'
        </a>
    </li>
    ';
}

// CONFIGURACIÓN (Solo Admin/Jefe)
if(in_array($rol_actual, ['admin', 'jefe'])){
    $active = (isset($_GET['operacion']) && $_GET['operacion'] == 'configuracion') ? 'activo' : '';
    echo '
    <li>
        <a href="?operacion=configuracion" class="'.$active.'">
            <span style="font-size:1.2em">⚙️</span>
            Configuración
        </a>
    </li>
    ';
}

// Botón cerrar sesión siempre visible
echo '
<li>
    <a href="logout.php" style="color:var(--danger)">
        <span style="font-size:1.2em">🚪</span>
        Cerrar Sesión
    </a>
</li>
';
?>
