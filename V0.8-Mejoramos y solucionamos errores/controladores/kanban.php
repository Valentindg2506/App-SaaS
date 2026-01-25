<?php
// Configuración de las 7 Fases del Kanban
$fases = [
    'Nuevo', 
    'Contactado', 
    'No respondió',
    'Interesado', 
    'Presupuesto', 
    'Compró', 
    'Descartado'
];

// Mapeo de colores visuales
$colores_fase = [
    'Nuevo'          => 'border-top: 3px solid #64748b;', // Slate
    'Contactado'     => 'border-top: 3px solid #3b82f6;', // Blue
    'No respondió'   => 'border-top: 3px solid #a855f7;', // Purple (NUEVO)
    'Interesado'     => 'border-top: 3px solid #0ea5e9;', // Sky
    'Presupuesto'    => 'border-top: 3px solid #f97316;', // Orange
    'Compró'         => 'border-top: 3px solid #22c55e;', // Green
    'Descartado'     => 'border-top: 3px solid #ef4444;'  // Red
];

// Obtener todos los clientes
$sql = "SELECT id, nombre_completo, empresa, fase, telefono FROM cliente ORDER BY created_at DESC";
$res = $conexion->query($sql);

$clientes_por_fase = [];
// Inicializar arrays vacíos para evitar errores si no hay clientes en una fase
foreach($fases as $f) { $clientes_por_fase[$f] = []; }

while($row = $res->fetch_assoc()){
    // Si la fase en BD no coincide con las definidas, va a 'Nuevo' por defecto
    $fase_actual = in_array($row['fase'], $fases) ? $row['fase'] : 'Nuevo';
    $clientes_por_fase[$fase_actual][] = $row;
}
?>

<div class="header-title">
	<h2><span style="color:var(--accent)">Estado de clientes</span></h2>
    <a href="?tabla=cliente" class="btn btn-secondary">Ver Lista</a>
</div>

<style>
    .kanban-board {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        padding-bottom: 2rem;
        min-height: 600px;
        align-items: stretch;
    }
    .kanban-column {
        min-width: 260px;
        width: 260px;
        background: #f1f5f9; /* Lighter background for column */
        border-radius: 8px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--border);
    }
    .kanban-header {
        font-weight: bold;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 10px;
        text-align: center;
        padding: 5px;
        font-size: 0.85rem;
        letter-spacing: 0.05em;
    }
    .kanban-card {
        background: var(--bg-surface);
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 6px;
        box-shadow: var(--shadow-sm);
        cursor: grab;
        transition: transform 0.2s, box-shadow 0.2s;
        font-size: 0.9rem;
    }
    .kanban-card:active {
        cursor: grabbing;
        transform: rotate(2deg);
    }
    /* Estilo visual cuando se arrastra sobre una columna */
    .kanban-column.drag-over {
        background: #e2e8f0;
        border: 2px dashed var(--accent);
    }
</style>

<div class="kanban-board">
    <?php foreach($fases as $fase): ?>
        <div class="kanban-column" data-fase="<?= $fase ?>" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="leaveDrop(event)">
            <div class="kanban-header">
                <?= $fase ?> <small>(<?= count($clientes_por_fase[$fase]) ?>)</small>
            </div>
            
            <?php foreach($clientes_por_fase[$fase] as $c): ?>
                <div class="kanban-card" 
                     draggable="true" 
                     ondragstart="drag(event)" 
                     id="card-<?= $c['id'] ?>" 
                     data-id="<?= $c['id'] ?>"
                     style="<?= $colores_fase[$fase] ?? '' ?>">
                    
                    <div style="font-weight:bold; color:var(--text-main);">
                        <?= htmlspecialchars($c['nombre_completo']) ?>
                    </div>
                    
                    <?php if(!empty($c['empresa'])): ?>
                        <div style="color:var(--text-muted); font-size:0.85em; margin-bottom:5px;">
                            🏢 <?= htmlspecialchars($c['empresa']) ?>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.8em; color:var(--text-muted);">ID: <?= $c['id'] ?></span>
                        <a href="?operacion=editar&tabla=cliente&id=<?= $c['id'] ?>&pk=id" title="Editar" style="text-decoration:none;">✏️</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function allowDrop(ev) {
        ev.preventDefault();
        ev.currentTarget.classList.add('drag-over');
    }

    function leaveDrop(ev) {
        ev.currentTarget.classList.remove('drag-over');
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
        ev.dataTransfer.setData("client_id", ev.target.dataset.id);
    }

    function drop(ev) {
        ev.preventDefault();
        // Quitar estilo visual
        ev.currentTarget.classList.remove('drag-over');

        var data = ev.dataTransfer.getData("text");
        var clientId = ev.dataTransfer.getData("client_id");
        
        // La columna destino (el div con clase kanban-column)
        var column = ev.currentTarget.closest('.kanban-column');
        var nuevaFase = column.getAttribute('data-fase');
        
        // Mover visualmente la tarjeta
        var card = document.getElementById(data);
        column.appendChild(card);

        // Actualizar en BBDD via Fetch (AJAX)
        updatePhase(clientId, nuevaFase);
    }

    function updatePhase(id, fase) {
        // Crear FormData
        let formData = new FormData();
        formData.append('id', id);
        formData.append('fase', fase);
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>'); // Usar token de sesión

        fetch('?operacion=ajax_update_fase', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log("Actualizado:", data);
        })
        .catch(error => {
            alert("Error al guardar el cambio. Recarga la página.");
            console.error('Error:', error);
        });
    }
</script>
