<?php
$rol = $_SESSION['usuario_rol'];
?>

<div class="header-title">
    <div>
        <h1>Dashboard</h1>
        <p style="color:var(--text-muted)">Bienvenido, <b><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></b>. Panel de: <span style="text-transform:uppercase; font-weight:bold; color:var(--accent)"><?= $rol ?></span></p>
    </div>
    <span style="color:var(--text-muted)"><?= date("d M Y") ?></span>
</div>

<!-- ==============================================
     LOGICA DE DATOS 
     ============================================== -->
<?php
// Variables JS iniciales
$js_labels_finanzas = [];
$js_data_ingresos   = [];
$js_labels_servicios = [];
$js_data_servicios   = [];
$js_labels_pedidos   = [];
$js_data_pedidos     = [];
// Admin specific
$js_labels_db_tables = [];
$js_data_db_rows     = [];
// Calendar Events
$js_events_calendar  = [];

// ------------------------------------------------
// 1. DATOS PARA JEFE (Finanzas & Personal)
// ------------------------------------------------
if (in_array($rol, ['jefe'])) {
    // Finanzas
    $res_fin = $conexion->query("SELECT DATE_FORMAT(fecha_factura, '%Y-%m') as mes, SUM(total) as total FROM factura GROUP BY mes ORDER BY mes ASC LIMIT 6");
    while($row = $res_fin->fetch_assoc()){
        $js_labels_finanzas[] = $row['mes'];
        $js_data_ingresos[]   = $row['total'];
    }
    // Servicios
    $res_serv = $conexion->query("SELECT s.nombre, COUNT(p.id) as ventas FROM pedido p JOIN servicio s ON p.servicio_id = s.id GROUP BY s.id");
    while($row = $res_serv->fetch_assoc()){
        $js_labels_servicios[] = $row['nombre'];
        $js_data_servicios[]   = $row['ventas'];
    }
    // Personal Count (KPI)
    $cnt_personal = $conexion->query("SELECT COUNT(*) as c FROM personal")->fetch_assoc()['c'];
}

// ------------------------------------------------
// 2. DATOS PARA ADMIN (Sistema & Logs)
// ------------------------------------------------
if ($rol == 'admin') {
    // DB Table Row Counts
    $res_tables = $conexion->query("SHOW TABLES");
    while($row = $res_tables->fetch_array()){
        $table_name = $row[0];
        $cnt = $conexion->query("SELECT COUNT(*) as c FROM $table_name")->fetch_assoc()['c'];
        $js_labels_db_tables[] = $table_name;
        $js_data_db_rows[]     = $cnt;
    }

    // Chart Logs (Feed logic moved to view, only check if table exists here)
    $table_check = $conexion->query("SHOW TABLES LIKE 'registro_log'");
    $has_logs = $table_check->num_rows > 0;

    // --- MAX ADMIN DATA EXTRACTION ---
    // 1. DB Size
    $query_size = "SELECT Round(Sum(data_length + index_length) / 1024 / 1024, 2) as size_mb FROM information_schema.tables WHERE table_schema = '$db'";
    $db_size_mb = $conexion->query($query_size)->fetch_assoc()['size_mb'];

    // 2. Disk Space
    $disk_total = disk_total_space("/");
    $disk_free = disk_free_space("/");
    $disk_used = $disk_total - $disk_free;
    $disk_pct = round(($disk_used / $disk_total) * 100);
    $disk_info = round($disk_free / 1024 / 1024 / 1024, 1) . " GB Libres";

    // 3. PHP Config
    $php_cfg = [
        'Versión PHP' => phpversion(),
        'Memoria Límite' => ini_get('memory_limit'),
        'Max Execution Time' => ini_get('max_execution_time') . 's',
        'Upload Max Size' => ini_get('upload_max_filesize'),
        'Post Max Size' => ini_get('post_max_size')
    ];

    // 4. Critical Logs Feed
    $log_feed = [];
    if($has_logs){
        $res_feed = $conexion->query("SELECT * FROM registro_log ORDER BY fecha DESC LIMIT 5");
        while($r = $res_feed->fetch_assoc()){
            $log_feed[] = $r;
        }
    }
}

// ------------------------------------------------
// 3. DATOS PARA EMPLEADO
// ------------------------------------------------
$avisos_html = "";
$cnt_servicios_disponibles = 0;
$cnt_mis_pedidos_activos = 0;

if ($rol == 'empleado') {
    $uid = $_SESSION['usuario_id'];
    
    // A. LISTADO AVISOS (Ultimos 5)
    $sql_avisos_list = "
        SELECT * FROM aviso 
        WHERE alcance = 'global' 
           OR (alcance = 'personal' AND usuario_id = $uid)
        ORDER BY fecha_aviso DESC LIMIT 5
    ";
    $res_avisos = $conexion->query($sql_avisos_list);

    if($res_avisos && $res_avisos->num_rows > 0){
        while($row = $res_avisos->fetch_assoc()){
            $badge = ($row['alcance'] == 'global') 
                ? '<span style="background:var(--accent); color:white; padding:2px 6px; border-radius:4px; font-size:0.7em; margin-right:5px;">GLOBAL</span>' 
                : '<span style="background:#64748b; color:white; padding:2px 6px; border-radius:4px; font-size:0.7em; margin-right:5px;">PRIVADO</span>';
            
            $avisos_html .= "<li>$badge <b>".htmlspecialchars($row['titulo']).":</b> ".htmlspecialchars($row['mensaje'])."</li>";
        }
    } else {
        $avisos_html = "<li>No hay avisos recientes.</li>";
    }

    // B. CALENDAR DATA (TODOS)
    $sql_avisos_cal = "
        SELECT * FROM aviso 
        WHERE alcance = 'global' 
           OR (alcance = 'personal' AND usuario_id = $uid)
    ";
    $res_cal = $conexion->query($sql_avisos_cal);
    while($row = $res_cal->fetch_assoc()){
        // Formato FullCalendar
        $color = ($row['alcance'] == 'global') ? '#3b82f6' : '#64748b';
        $js_events_calendar[] = [
            'title' => $row['titulo'],
            'start' => substr($row['fecha_aviso'], 0, 10), // YYYY-MM-DD
            'color' => $color,
            'description' => $row['mensaje'] // Custom prop
        ];
    }

    // Servicios
    $cnt_servicios_disponibles = $conexion->query("SELECT COUNT(*) as c FROM servicio")->fetch_assoc()['c'];
    
    // Pedidos Activos (Globales)
    $cnt_mis_pedidos_activos = $conexion->query("SELECT COUNT(*) as c FROM pedido WHERE estado != 'completado'")->fetch_assoc()['c'];
}

// ------------------------------------------------
// 4. DATOS COMUN (Pedidos Status)
// ------------------------------------------------
if (in_array($rol, ['jefe', 'subjefe', 'supervisor'])) {
    $res_ped = $conexion->query("SELECT estado, COUNT(*) as c FROM pedido GROUP BY estado");
    while($row = $res_ped->fetch_assoc()){
        $js_labels_pedidos[] = ucfirst($row['estado']);
        $js_data_pedidos[]   = $row['c'];
    }
}
?>

<!-- ==============================================
     VISTA (WIDGETS & CHARTS)
     ============================================== -->

<div class="dashboard-grid">
    
    <!-- ADMIN WIDGETS -->
    <?php if($rol == 'admin'): ?>
        <div class="stat-card" style="border-left: 4px solid var(--accent);">
            <span class="stat-label">Tamaño BBDD</span>
            <div class="stat-value"><?= $db_size_mb ?> MB</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #8b5cf6;">
            <span class="stat-label">Espacio en Disco</span>
            <div class="stat-value"><?= $disk_pct ?>%</div>
            <div style="font-size:0.8em; color:var(--text-muted);"><?= $disk_info ?></div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--warning);">
            <span class="stat-label">PHP Max Upload</span>
            <div class="stat-value" style="font-size:1.5rem"><?= ini_get('upload_max_filesize') ?></div>
        </div>
    <?php endif; ?>

    <!-- JEFE WIDGETS -->
    <?php if($rol == 'jefe'): ?>
        <div class="stat-card" style="border-left: 4px solid var(--accent);">
            <span class="stat-label">Personal Total</span>
            <div class="stat-value"><?= $cnt_personal ?></div>
        </div>
        <?php
        $ing = $conexion->query("SELECT SUM(total) as t FROM factura")->fetch_assoc()['t'] ?? 0;
        ?>
        <div class="stat-card" style="border-left: 4px solid var(--success);">
            <span class="stat-label">Ingresos Totales</span>
            <div class="stat-value text-success">$<?= number_format($ing, 2) ?></div>
        </div>
    <?php endif; ?>

    <!-- EMPLEADO WIDGETS -->
    <?php if($rol == 'empleado'): ?>
        <div class="stat-card" style="border-left: 4px solid var(--success);">
            <span class="stat-label">Servicios Disponibles</span>
            <div class="stat-value"><?= $cnt_servicios_disponibles ?></div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--warning);">
            <span class="stat-label">Pedidos Activos (Global)</span>
            <div class="stat-value text-warning"><?= $cnt_mis_pedidos_activos ?></div>
        </div>
    <?php endif; ?>

</div>

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">

    <!-- EMPLEADO CALENDAR (Top Priority) -->
    <?php if($rol == 'empleado'): ?>
        <div class="card" style="grid-column: 1 / -1;">
            <h3>📅 Calendario de Avisos</h3>
            <div id='calendar' style="min-height: 500px;"></div>
        </div>
    <?php endif; ?>

    <!-- ADMIN MAX VIEW -->
    <?php if($rol == 'admin'): ?>
        <div class="card">
             <h3>🛠️ Configuración del Servidor</h3>
             <table style="width:100%; border-collapse:collapse; font-size:0.9em;">
                 <?php foreach($php_cfg as $k => $v): ?>
                 <tr style="border-bottom:1px solid var(--border);">
                     <td style="padding:8px; font-weight:bold; color:var(--text-muted);"><?= $k ?></td>
                     <td style="padding:8px; text-align:right; font-family:monospace;"><?= $v ?></td>
                 </tr>
                 <?php endforeach; ?>
             </table>
        </div>

        <div class="card" style="grid-column: span 1;">
            <h3>🚨 Feed de Errores (Últimos Eventos)</h3>
            <?php if(empty($log_feed)): ?>
                 <p style="color:var(--text-muted); padding:10px;">Sin logs recientes.</p>
            <?php else: ?>
                 <div style="display:flex; flex-direction:column; gap:10px;">
                     <?php foreach($log_feed as $log): ?>
                         <div style="padding:10px; background:var(--bg-body); border-left:3px solid <?= $log['tipo']=='error'?'var(--danger)':($log['tipo']=='warning'?'var(--warning)':'var(--success)') ?>; border-radius:4px; font-size:0.85em;">
                             <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                 <strong style="text-transform:uppercase; font-size:0.75em; color:var(--text-muted);"><?= $log['tipo'] ?></strong>
                                 <span style="color:var(--text-muted);"><?= $log['fecha'] ?></span>
                             </div>
                             <div><?= htmlspecialchars($log['mensaje']) ?></div>
                         </div>
                     <?php endforeach; ?>
                 </div>
            <?php endif; ?>
        </div>

        <div class="card" style="grid-column: span 2;">
            <h3>🗄️ Peso de Tablas (Nº Filas)</h3>
            <canvas id="chartDBRows" style="max-height:300px;"></canvas>
        </div>
    <?php endif; ?>

    <!-- JEFE CHARTS -->
    <?php if($rol == 'jefe'): ?>
        <div class="card">
            <h3>📈 Evolución Ingresos</h3>
            <canvas id="chartFinanzas"></canvas>
        </div>
        <div class="card">
            <h3>🥧 Top Servicios</h3>
            <canvas id="chartServicios"></canvas>
        </div>
    <?php endif; ?>

    <!-- EMPLEADO AVISOS LIST -->
    <?php if($rol == 'empleado'): ?>
        <div class="card">
            <h3>📢 Tablón de Avisos (Recientes)</h3>
            <ul style="padding-left: 20px; line-height: 1.8; color: var(--text-muted);">
                <?= $avisos_html ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- COMMON OPS CHARTS -->
    <?php if(in_array($rol, ['jefe', 'subjefe', 'supervisor'])): ?>
        <div class="card">
            <h3>📊 Pedidos por Estado</h3>
            <canvas id="chartPedidos"></canvas>
        </div>
    <?php endif; ?>

</div>

<!-- SCRIPT DE GRAFICOS & CALENDAR -->
<script>
// ADMIN
const labelsDB = <?= json_encode($js_labels_db_tables) ?>;
const dataDB = <?= json_encode($js_data_db_rows) ?>;

// JEFE
const labelsFin = <?= json_encode($js_labels_finanzas) ?>;
const dataFin = <?= json_encode($js_data_ingresos) ?>;
const labelsServ = <?= json_encode($js_labels_servicios) ?>;
const dataServ = <?= json_encode($js_data_servicios) ?>;

// OPS
const labelsPed = <?= json_encode($js_labels_pedidos) ?>;
const dataPed = <?= json_encode($js_data_pedidos) ?>;

// CALENDAR
const calendarEvents = <?= json_encode($js_events_calendar) ?>;

// --- RENDER EMPLEADO CALENDAR ---
if(document.getElementById('calendar')){
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          locale: 'es',
          height: 600,
          events: calendarEvents,
          eventClick: function(info) {
             alert('Aviso: ' + info.event.title + '\n' + (info.event.extendedProps.description || ''));
          }
        });
        calendar.render();
    });
}

// --- RENDER ADMIN ---
if(document.getElementById('chartDBRows')){
    new Chart(document.getElementById('chartDBRows'), {
        type: 'bar',
        data: {
            labels: labelsDB,
            datasets: [{ label: 'Filas', data: dataDB, backgroundColor: '#6366f1' }]
        },
        options: { indexAxis: 'y' }
    });
}

// --- RENDER JEFE ---
if(document.getElementById('chartFinanzas') && labelsFin.length > 0){
    new Chart(document.getElementById('chartFinanzas'), {
        type: 'line',
        data: {
            labels: labelsFin,
            datasets: [{ label: 'Ingresos ($)', data: dataFin, borderColor: '#10b981', fill:true, backgroundColor:'rgba(16, 185, 129, 0.1)' }]
        }
    });
}
if(document.getElementById('chartServicios') && labelsServ.length > 0){
    new Chart(document.getElementById('chartServicios'), {
        type: 'pie',
        data: {
            labels: labelsServ,
            datasets: [{ data: dataServ, backgroundColor: ['#3b82f6', '#f59e0b', '#ef4444', '#10b981'] }]
        }
    });
}

// --- RENDER OPS ---
if(document.getElementById('chartPedidos') && labelsPed.length > 0){
    new Chart(document.getElementById('chartPedidos'), {
        type: 'bar',
        data: {
            labels: labelsPed,
            datasets: [{ label: 'Pedidos', data: dataPed, backgroundColor: '#f59e0b' }]
        }
    });
}
</script>
