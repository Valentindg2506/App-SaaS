CREATE TABLE cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    empresa VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE aviso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_aviso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE servicio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    descripcion TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);  

CREATE TABLE pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL UNIQUE,
    cliente_id INT NOT NULL,
    servicio_id INT NOT NULL,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

CREATE TABLE factura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    fecha_factura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuario_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL, -- admin, jefe, subjefe, supervisor, empleado
    nombre_completo VARCHAR(100),
    debe_cambiar_password BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pago (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    monto DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE personal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL, -- subjefe, supervisor, empleado
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    color_menu VARCHAR(50) DEFAULT '#1e293b',
    color_body VARCHAR(50) DEFAULT '#f1f5f9',
    color_texto_menu VARCHAR(50) DEFAULT '#ffffff',
    color_texto_body VARCHAR(50) DEFAULT '#1e293b',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar usuarios por defecto (Contraseña: 1234)
-- En producción usar password_hash(). Aquí simple texto o hash básico según setup.
-- Para simplificar el ejemplo usaré texto plano si el login lo permite, pero mejor hash.
-- Asumiremos que el login usará password_verify si usamos hash, o == si texto plano.
-- Voy a usar TEXTO PLANO por ahora para facilitar las pruebas del usuario según el nivel anterior.

INSERT INTO usuario_sistema (usuario, contrasena, rol, nombre_completo) VALUES 
('admin', '1234', 'admin', 'Administrador Sistema'),
('jefe', '1234', 'jefe', 'El Dueño'),
('subjefe', '1234', 'subjefe', 'Subjefe Operaciones'),
('supervisor', '1234', 'supervisor', 'Supervisor Turno'),
('empleado', '1234', 'empleado', 'Empleado General');

CREATE TABLE registro_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- error, info, warning
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- DATOS DE PRUEBA: AVISOS (Para Empleado)
INSERT INTO aviso (titulo, mensaje) VALUES 
('Reunión General', 'El viernes a las 15:00 todos en la sala de conferencias.'),
('Inventario', 'Recordar cerrar el inventario antes del 30 del mes.'),
('Mantenimiento', 'El sistema estará lento el domingo por la noche.');

-- DATOS DE PRUEBA: LOGS (Para Admin)
INSERT INTO registro_log (tipo, mensaje, fecha) VALUES 
('info', 'Inicio de sistema', NOW()),
('warning', 'Intento de acceso fallido admin', NOW() - INTERVAL 1 HOUR),
('error', 'Conexión BD timeout', NOW() - INTERVAL 2 DAY),
('info', 'Respaldo automático completado', NOW() - INTERVAL 1 DAY),
('error', 'Error 500 en modulo facturas', NOW() - INTERVAL 5 HOUR);

-- ACTUALIZACION AVISOS (Privado vs Global)
ALTER TABLE aviso ADD COLUMN usuario_id INT NULL;
ALTER TABLE aviso ADD COLUMN alcance ENUM('global', 'personal') DEFAULT 'global';

-- Update existing to global
UPDATE aviso SET alcance = 'global';

-- Insert Sample Personal Notice
INSERT INTO aviso (titulo, mensaje, alcance, usuario_id) VALUES 
('Nota Privada', 'Recordar revisar mi reporte de gastos.', 'personal', 1); -- Asuming ID 1 is default admin/jefe

ALTER TABLE usuario_sistema ADD COLUMN debe_cambiar_password BOOLEAN DEFAULT 1;

CREATE TABLE prospectos (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    nombre VARCHAR(100) NOT NULL, 
    apellido VARCHAR(100) NOT NULL, 
    email VARCHAR(100) NOT NULL, 
    telefono VARCHAR(20) NOT NULL, 
    direccion VARCHAR(255) NOT NULL, 
    empresa VARCHAR(100) NOT NULL, 
    comentarios TEXT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE cliente ADD COLUMN empleado_id INT DEFAULT NULL; 
ALTER TABLE prospectos ADD COLUMN empleado_id INT DEFAULT NULL;
ALTER TABLE clientes ADD COLUMN estado VARCHAR(50) DEFAULT 'Prospecto';
ALTER TABLE cliente ADD COLUMN fase VARCHAR(50) DEFAULT 'Nuevo';


ULTIMAS MODIFICACIONES
-- 1. Añadir las nuevas columnas solicitadas
ALTER TABLE cliente ADD COLUMN nombre_completo VARCHAR(255) NOT NULL AFTER id;
ALTER TABLE cliente ADD COLUMN ssn VARCHAR(50) DEFAULT NULL AFTER direccion;
ALTER TABLE cliente ADD COLUMN ein VARCHAR(50) DEFAULT NULL AFTER ssn;
ALTER TABLE cliente ADD COLUMN ultimos_digitos VARCHAR(20) DEFAULT NULL AFTER ein;
ALTER TABLE cliente ADD COLUMN cantidad_empleados INT DEFAULT 0 AFTER ultimos_digitos;
ALTER TABLE cliente ADD COLUMN requerimiento TEXT DEFAULT NULL AFTER cantidad_empleados;

-- 2. Si tienes datos viejos, migra los nombres
UPDATE cliente SET nombre_completo = CONCAT(nombre, ' ', apellido);

-- 3. Limpiar columnas viejas que ya no usarás
ALTER TABLE cliente DROP COLUMN nombre;
ALTER TABLE cliente DROP COLUMN apellido;

## Nueva tabla ##

CREATE TABLE historial_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    usuario_id INT NOT NULL, -- Quién escribió la nota
    tipo ENUM('nota', 'conversacion', 'sistema') DEFAULT 'nota',
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
