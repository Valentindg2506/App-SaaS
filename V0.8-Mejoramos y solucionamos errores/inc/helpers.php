<?php
/**
 * Convierte nombres de columnas de BD (ej: cliente_id) a etiquetas legibles (ej: Cliente)
 */
function format_column_name($name) {
    // 1. Mapeo directo de casos especiales
    $map = [
        'id' => 'ID',
        'created_at' => 'Fecha Registro',
        'updated_at' => 'Última Actualización',
        'usuario_id' => 'Usuario',
        'cliente_id' => 'Cliente',
        'pedido_id' => 'Pedido',
        'numero_pedido' => 'Número de Pedido',
        'servicio_id' => 'Servicio',
        'fecha_pedido' => 'Fecha del Pedido',
        'fecha_pago' => 'Fecha de Pago',
        'fecha_factura' => 'Fecha Facturación',
        'email' => 'Correo Electrónico',
        'telefono' => 'Teléfono',
        'direccion' => 'Dirección',
        'dni' => 'DNI / NIF',
        'alcance' => 'Visibilidad',
        'debe_cambiar_password' => 'Debe Cambiar Password',
        'rol' => 'Rol',
        'nombre_completo' => 'Nombre Completo',
        'contrasena' => 'Contraseña',
        'usuario' => 'Usuario',
        'tipo' => 'Tipo',
        'mensaje' => 'Mensaje',
        'fecha' => 'Fecha',
        'empleado_id' => 'Empleado Asignado',
    ];
    
    if(isset($map[$name])) {
        return $map[$name];
    }

    // 2. Lógica Genérica
    
    // Si termina en _id, quitarlo (ej: categoria_id -> categoria)
    if(substr($name, -3) == '_id') {
        $name = substr($name, 0, -3);
    }

    // Reemplazar guiones bajos por espacios
    $name = str_replace('_', ' ', $name);

    // Capitalizar palabras
    return ucwords($name);
}

/**
 * Calcula color de texto (blanco o negro) según fondo
 */
function get_contrast_color($hexColor) {
    // Normalizar a 6 chars
    $hex = str_replace('#', '', $hexColor);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    }
    // Si esta vacio o invalido return black default
    if(strlen($hex)!=6) return '#000000';

    // Obtener RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    // Fórmula de Luminosidad (YIQ)
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

    // Si es oscuro (<128), texto blanco. Si es claro, texto oscuro.
    return ($yiq >= 128) ? '#0f172a' : '#ffffff';
}
?>
