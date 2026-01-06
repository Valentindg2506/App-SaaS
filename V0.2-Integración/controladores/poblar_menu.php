<?php
/**
 * CONFIGURACIÓN DEL MENÚ LATERAL
 * 
 * Define aquí tus secciones manualmente.
 * Formato: ["label" => "Texto a mostrar", "tabla" => "nombre_real_tabla_bd", "icono" => "Emoji o clase"]
 */
$menu_items = [
    // Ejemplo:
    // ["label" => "Mis Productos", "tabla" => "productos", "icono" => "📦"],
    
    // Cambia 'nombre_de_tabla_x' por los nombres reales de tus tablas en la base de datos
    ["label" => "Clientes",     "tabla" => "clientes",     "icono" => "👥"],
    ["label" => "Servicios",    "tabla" => "servicios",    "icono" => "🛠️"],
    ["label" => "Pedidos",      "tabla" => "pedidos",      "icono" => "🛒"],
    ["label" => "Configuración","tabla" => "configuracion","icono" => "⚙️"],
];

foreach ($menu_items as $item) {
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

/*
// CÓDIGO ANTERIOR (Automático)
// Descomentar si se quiere volver al modo automático

$resultado = $conexion->query("SHOW TABLES;");
while ($fila = $resultado->fetch_assoc()) {
    $nombre_tabla = $fila['Tables_in_'.$db];
    // ...
}
*/
?>
