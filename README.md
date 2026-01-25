# ⚡ App-SaaS Project

Bienvenido al repositorio de **App-SaaS**, una aplicación web de gestión empresarial (CRM/ERP) desarrollada en **PHP nativo** y **MySQL**. Este proyecto simula un entorno SaaS (Software as a Service) escalable, diseñado para administrar clientes, pedidos, facturación y personal con un sistema robusto de roles y permisos.

## 🚀 Características Principales

El sistema ha evolucionado a través de varias versiones hasta la actual (**V0.6**), incorporando las siguientes funcionalidades:

### 👥 Gestión de Usuarios y Roles
- **Sistema de Roles:** Acceso diferenciado para Admin, Jefe, Subjefe, Supervisor y Empleado.
- **Autenticación:** Login seguro, logout y gestión de sesiones.
- **Personal:** Administración de empleados y asignación de tareas.

### 💼 CRM y Ventas
- **Clientes:** Gestión completa de base de datos de clientes.
- **Prospectos:** Módulo para clientes potenciales con opción de **conversión a cliente** en un clic.
- **Asignación:** Vinculación de empleados específicos a clientes y prospectos.

### 💰 Facturación y Operaciones
- **Servicios:** Catálogo de servicios ofrecidos con precios.
- **Pedidos:** Creación y seguimiento de pedidos por cliente.
- **Facturas:** Generación de facturas asociadas a pedidos.
- **Pagos:** Registro y control de pagos recibidos.

### 🛠️ Herramientas y Utilidades
- **Dashboard:** Panel principal con métricas visuales (integración con **Chart.js**).
- **Calendario:** Vista de eventos y avisos (integración con **FullCalendar**).
- **Avisos:** Sistema de notificaciones globales y privadas para usuarios.
- **Logs del Sistema:** Registro de auditoría para errores y actividades críticas.
- **Buscador:** Funcionalidad de búsqueda transverso (añadido en V0.6).

### 🎨 Personalización
- **Temas Dinámicos:** Configuración de colores para el menú y el cuerpo de la aplicación, con ajuste automático de contraste y persistencia en base de datos.
- **Diseño Responsivo:** Interfaz adaptada a diferentes dispositivos.

---

## 📂 Estructura del Proyecto

El proyecto está organizado en versiones incrementales. La versión más estable y completa es **`V0.6-Agregamos un buscador`**.

```text
App-SaaS/
├── V0.6-Agregamos un buscador/  <-- VERSIÓN RECOMENDADA
│   ├── BBDD.sql                 # Esquema de la Base de Datos
│   ├── index.php                # Punto de entrada y enrutador principal
│   ├── login.php                # Página de inicio de sesión
│   ├── inc/                     # Archivos de inclusión
│   │   ├── conexion_bd.php      # Conexión a MySQL
│   │   ├── config_roles.php     # Lógica de permisos
│   │   └── security.php         # Headers y funciones de seguridad
│   ├── controladores/           # Lógica de negocio (CRUDs)
│   ├── css/                     # Estilos (estilo.css)
│   └── img/                     # Recursos gráficos
└── README.md                    # Documentación del proyecto
```

---

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP (Sin frameworks, arquitectura MVC simplificada)
- **Base de Datos:** MySQL
- **Frontend:** HTML5, CSS3 (Variables CSS), JavaScript
- **Librerías JS:**
    - [Chart.js](https://www.chartjs.org/) (Gráficos)
    - [FullCalendar](https://fullcalendar.io/) (Calendario)
- **Fuentes:** Google Fonts (Inter)

---

## 📜 Historial de Versiones

- **V0.1 - Base:** Estructura inicial y conexión a BD.
- **V0.2 - Integración:** Primeros módulos funcionales.
- **V0.3 - Mejoramos:** Refactorización y mejoras visuales.
- **V0.4 - Hasheamos:** Seguridad mejorada en contraseñas.
- **V0.5 - Password:** Flujo de cambio de contraseña obligatorio.
- **V0.6 - Buscador:** Implementación de búsqueda global y mejoras en prospectos.