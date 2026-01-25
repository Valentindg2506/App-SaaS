<?php
/**
 * Matriz de Permisos por Rol
 * Define qué tablas puede ver cada rol.
 * '*' significa acceso total.
 */
$permisos_roles = [
    'admin'      => ['*'], // Acceso total
    'jefe'       => ['cliente', 'pedido', 'factura', 'personal', 'configuracion', 'servicio', 'aviso'], // Dueño ve casi todo
    'subjefe'    => ['cliente', 'pedido', 'servicio', 'personal', 'factura'],
    'supervisor' => ['cliente', 'pedido', 'aviso', 'servicio', 'personal'],
    'empleado'   => ['aviso', 'servicio', 'pedido'], // Acceso limitado
];

/**
 * Función helper para verificar acceso
 */
function tiene_acceso($rol, $tabla) {
    global $permisos_roles;
    
    // Si el rol no existe, denegar
    if (!isset($permisos_roles[$rol])) {
        return false;
    }
    
    // Si tiene comodín *, permitir
    if (in_array('*', $permisos_roles[$rol])) {
        return true;
    }
    
    // Verificar si la tabla está en su lista
    return in_array($tabla, $permisos_roles[$rol]);
}
?>
