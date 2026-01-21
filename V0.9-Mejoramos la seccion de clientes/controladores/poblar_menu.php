<?php
/**
 * CONFIGURACIÓN DEL MENÚ LATERAL
 */
// Importar configuración de roles
if(!isset($permisos_roles)) include "inc/config_roles.php";

$rol_actual = isset($_SESSION['usuario_rol']) ? $_SESSION['usuario_rol'] : 'empleado';

$menu_items = [
    ["label" => "Errores",          "tabla" => "registro_log",    "icono" => "⚠️"],
    ["label" => "Usuarios Sistema", "tabla" => "usuario_sistema", "icono" => "🔐"],
    ["label" => "Clientes",         "tabla" => "cliente",         "icono" => "👥"],
    ["label" => "Prospectos",       "tabla" => "prospectos",      "icono" => "🎯"],
    
    // --- NUEVO ITEM KANBAN (Vinculado a permiso de cliente) ---
    ["label" => "Tablero Clientes", "operacion" => "kanban",      "icono" => "📋"],
    
    ["label" => "Avisos",           "tabla" => "aviso",           "icono" => "🔔"],
    ["label" => "Servicios",        "tabla" => "servicio",        "icono" => "🛠️"],
    ["label" => "Pedidos",          "tabla" => "pedido",          "icono" => "🛒"],
    ["label" => "Facturas",         "tabla" => "factura",         "icono" => "📄"],
    ["label" => "Personal",         "tabla" => "personal",        "icono" => "👤"],
    ["label" => "Pagos",            "tabla" => "pago",            "icono" => "💰"],
];

foreach ($menu_items as $item) {
    // 1. Definir variables visuales (para evitar el error "Undefined variable")
    $label = $item['label'];
    $icono = isset($item['icono']) ? $item['icono'] : '📂';
    
    // 2. Determinar permisos y URL según el tipo de ítem
    $tabla_para_permisos = '';
    $url = '';
    $esta_activo = false;

    if (isset($item['tabla'])) {
        // ES UNA TABLA ESTÁNDAR
        $tabla_para_permisos = $item['tabla'];
        $url = '?tabla=' . $item['tabla'];
        $esta_activo = (isset($_GET['tabla']) && $_GET['tabla'] == $item['tabla']);
    } 
    elseif (isset($item['operacion'])) {
        // ES UNA OPERACIÓN PERSONALIZADA (Como Kanban)
        // Si definimos un permiso explícito, úsalo. Si no, usa el nombre de la operación.
        $tabla_para_permisos = isset($item['permiso']) ? $item['permiso'] : $item['operacion'];
        $url = '?operacion=' . $item['operacion'];
        $esta_activo = (isset($_GET['operacion']) && $_GET['operacion'] == $item['operacion']);
    }

    // 3. FILTRO DE SEGURIDAD
    // Si no tiene acceso, saltar al siguiente (continue)
    if (!tiene_acceso($rol_actual, $tabla_para_permisos)) {
        continue;
    }

    // 4. Renderizar HTML
    $clase_css = $esta_activo ? "activo" : "";
    
    echo '
    <li>
        <a href="'.$url.'" class="'.$clase_css.'">
            <span style="font-size:1.2em">'.$icono.'</span>
            '.$label.'
        </a>
    </li>
    ';
}

// CONFIGURACIÓN (Solo Admin/Jefe) - Esto se mantiene igual
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

// Botón cerrar sesión
echo '
<li>
    <a href="logout.php" style="color:var(--danger)">
        <span style="font-size:1.2em">🚪</span>
        Cerrar Sesión
    </a>
</li>
';
?>
