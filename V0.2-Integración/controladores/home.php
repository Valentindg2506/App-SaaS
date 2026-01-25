<div class="header-title">
    <h1>Dashboard</h1>
    <span style="color:var(--text-muted)"><?= date("d M Y") ?></span>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <span class="stat-label">Estado del Sistema</span>
        <div class="stat-value" style="color:var(--success)">En Línea</div>
    </div>
    
    <?php
    $tables = $conexion->query("SHOW TABLES");
    $num_tables = $tables->num_rows;
    ?>
    <div class="stat-card">
        <span class="stat-label">Tablas Gestionadas</span>
        <div class="stat-value"><?= $num_tables ?></div>
    </div>
    
    <div class="stat-card">
        <span class="stat-label">Versión</span>
        <div class="stat-value" style="font-size:1.5rem">v0.2 Beta</div>
    </div>
</div>

<div class="card">
    <h3>Bienvenido al Panel de Administración</h3>
    <p style="color:var(--text-muted); line-height: 1.6;">
        Selecciona una tabla del menú lateral para ver, editar o eliminar registros.
        Este panel ha sido actualizado con una nueva interfaz y sistema de seguridad.
    </p>
</div>
