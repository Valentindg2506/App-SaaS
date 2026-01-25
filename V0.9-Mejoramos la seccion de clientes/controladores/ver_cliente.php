<?php
// Validar ID
if (!isset($_GET['id'])) die("Cliente no especificado");
$id = (int)$_GET['id'];
$mensaje_feedback = "";

// ---------------------------------------------------------
// A. PROCESAR GUARDADO DE CAMPO (EDICIÓN RÁPIDA)
// ---------------------------------------------------------
if(isset($_POST['campo_editar']) && isset($_POST['valor_nuevo'])){
    $campo = $conexion->real_escape_string($_POST['campo_editar']);
    $valor = $conexion->real_escape_string($_POST['valor_nuevo']);
    
    // Lista blanca de campos permitidos
    $permitidos = ['nombre_completo', 'email', 'telefono', 'direccion', 'empresa', 'ein', 'ssn', 'ultimos_digitos', 'cantidad_empleados', 'fase'];
    
    if(in_array($campo, $permitidos)){
        $sql = "UPDATE cliente SET $campo = '$valor' WHERE id = $id";
        if($conexion->query($sql)){
            $mensaje_feedback = "✅ Actualizado.";
        }
    }
}

// ---------------------------------------------------------
// B. PROCESAR NUEVA NOTA / CONVERSACIÓN
// ---------------------------------------------------------
if(isset($_POST['nueva_nota'])){
    $nota = $conexion->real_escape_string($_POST['contenido_nota']);
    $tipo = $conexion->real_escape_string($_POST['tipo_nota']); // nota o conversacion
    $uid  = $_SESSION['usuario_id'];
    
    if(!empty($nota)){
        $sql = "INSERT INTO historial_cliente (cliente_id, usuario_id, tipo, mensaje) VALUES ('$id', '$uid', '$tipo', '$nota')";
        $conexion->query($sql);
    }
}

// ---------------------------------------------------------
// C. OBTENER DATOS
// ---------------------------------------------------------
$cliente = $conexion->query("SELECT * FROM cliente WHERE id = $id")->fetch_assoc();
if(!$cliente) die("Cliente no encontrado");

// Historial
$historial = $conexion->query("
    SELECT h.*, u.usuario 
    FROM historial_cliente h 
    JOIN usuario_sistema u ON h.usuario_id = u.id 
    WHERE h.cliente_id = $id 
    ORDER BY h.fecha DESC
");
?>

<style>
    /* Layout */
    .crm-container {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 25px;
        align-items: start;
    }
    
    /* Ficha Izquierda */
    .profile-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        padding: 0;
        overflow: hidden;
    }
    .profile-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 20px;
        border-bottom: 1px solid var(--border);
        text-align: center;
    }
    .profile-avatar {
        width: 60px; height: 60px;
        background: var(--accent);
        color: white;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; font-weight: bold;
        margin: 0 auto 10px auto;
    }
    .profile-body {
        padding: 20px;
    }

    /* Lista de Datos */
    .data-item {
        margin-bottom: 15px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 10px;
    }
    .data-item:last-child { border-bottom: none; }
    
    .data-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 4px;
        display: flex; justify-content: space-between;
    }
    .data-value {
        font-size: 0.95rem;
        color: var(--text-main);
        font-weight: 500;
        min-height: 20px;
    }
    
    /* Boton editar invisible hasta hover */
    .edit-trigger {
        cursor: pointer;
        opacity: 0.3;
        transition: 0.2s;
        font-size: 1rem;
    }
    .data-item:hover .edit-trigger { opacity: 1; color: var(--accent); }

    /* Inputs de Edición */
    .edit-form { display: none; margin-top: 5px; }
    .edit-form.active { display: flex; gap: 5px; }
    .edit-input {
        width: 100%;
        padding: 6px;
        border: 1px solid var(--accent);
        border-radius: 4px;
        font-size: 0.9rem;
    }

    /* --- DERECHA: TIMELINE & CHAT --- */
    .feed-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Caja de Entrada */
    .input-box {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 15px;
        box-shadow: var(--shadow-sm);
    }
    .input-tabs {
        display: flex; gap: 15px; margin-bottom: 10px; border-bottom: 1px solid var(--border); padding-bottom: 10px;
    }
    .tab-label {
        cursor: pointer; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 5px;
    }
    .tab-label input { margin-right: 5px; }

    /* Feed Items */
    .timeline-feed {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .feed-card {
        display: flex;
        gap: 15px;
        animation: fadeIn 0.3s ease;
    }

    .feed-icon {
        width: 40px; height: 40px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        box-shadow: var(--shadow-sm);
        background: white;
        border: 2px solid transparent;
    }

    .feed-content {
        flex: 1;
        background: var(--bg-surface);
        padding: 15px;
        border-radius: 0 12px 12px 12px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        position: relative;
    }
    
    /* Estilos específicos por tipo */
    
    /* 1. NOTA INTERNA (Estilo Post-it moderno) */
    .type-nota .feed-icon {
        background: #fef9c3; /* Yellow light */
        border-color: #eab308;
        color: #ca8a04;
    }
    .type-nota .feed-content {
        background: #fffbeb; /* Very light yellow */
        border-left: 3px solid #eab308;
    }

    /* 2. CONVERSACIÓN (Estilo Chat Azul) */
    .type-conversacion .feed-icon {
        background: #eff6ff; /* Blue light */
        border-color: #3b82f6;
        color: #2563eb;
    }
    .type-conversacion .feed-content {
        background: #ffffff;
        border-left: 3px solid #3b82f6;
    }

    .feed-meta {
        display: flex; justify-content: space-between;
        font-size: 0.75rem; color: var(--text-muted);
        margin-bottom: 8px; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 5px;
    }
    .feed-body {
        font-size: 0.95rem;
        line-height: 1.5;
        color: var(--text-main);
        white-space: pre-wrap;
    }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @media(max-width: 900px) { .crm-container { grid-template-columns: 1fr; } }
</style>

<div class="header-title" style="margin-bottom: 1.5rem;">
    <div>
        <a href="?tabla=cliente" class="btn btn-secondary btn-sm" style="margin-bottom:10px;">⬅ Volver</a>
        <h2 style="margin:0;">Ficha de Cliente</h2>
    </div>
    <?php if($mensaje_feedback): ?>
        <div style="background:#dcfce7; color:#166534; padding:8px 15px; border-radius:6px; font-weight:600;">
            <?= $mensaje_feedback ?>
        </div>
    <?php endif; ?>
</div>

<div class="crm-container">

    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($cliente['nombre_completo'], 0, 1)) ?>
            </div>
            <h3 style="margin:0; font-size:1.2rem;"><?= htmlspecialchars($cliente['nombre_completo']) ?></h3>
            <div style="color:var(--text-muted); font-size:0.9rem; margin-top:5px;"><?= htmlspecialchars($cliente['empresa']) ?></div>
            
            <div style="margin-top:10px;">
                <?php
                $color = $cliente['fase']=='Nuevo' ? '#3b82f6' : ($cliente['fase']=='Compró' ? '#22c55e' : '#64748b');
                ?>
                <span style="background:<?= $color ?>; color:white; padding:4px 12px; border-radius:20px; font-size:0.8rem; text-transform:uppercase; font-weight:bold;">
                    <?= htmlspecialchars($cliente['fase']) ?>
                </span>
            </div>
        </div>
        
        <div class="profile-body">
            <?php
            $campos = [
                'nombre_completo' => 'Nombre Completo',
                'telefono' => 'Teléfono',
                'email' => 'Email',
                'empresa' => 'Empresa',
                'ssn' => 'SSN',
                'ein' => 'EIN',
                'ultimos_digitos' => 'Digitos SSN/ITIN',
                'cantidad_empleados' => 'Empleados',
                'direccion' => 'Dirección',
                'fase' => 'Fase'
            ];

            foreach($campos as $k => $label):
                $val = htmlspecialchars($cliente[$k] ?? '-');
            ?>
            <div class="data-item">
                <div class="data-label">
                    <?= $label ?>
                    <span class="edit-trigger" onclick="activarEdicion('<?= $k ?>')">✏️</span>
                </div>
                
                <div id="view_<?= $k ?>" class="data-value"><?= $val ?></div>
                
                <form method="POST" id="form_<?= $k ?>" class="edit-form">
                    <input type="hidden" name="campo_editar" value="<?= $k ?>">
                    <?php if($k == 'fase'): ?>
                        <select name="valor_nuevo" class="edit-input">
                             <?php 
                            $fs = ['Nuevo','Contactado','No respondió','Interesado','En Negociación','Presupuesto','Compró','Descartado'];
                            foreach($fs as $f) echo "<option ".($f==$cliente[$k]?'selected':'').">$f</option>";
                            ?>
                        </select>
                    <?php else: ?>
                        <input type="text" name="valor_nuevo" value="<?= $cliente[$k] ?>" class="edit-input">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary btn-sm">💾</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="cancelarEdicion('<?= $k ?>')">✖</button>
                </form>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:20px; padding:15px; background:#f8fafc; border-radius:8px; border:1px dashed var(--border);">
                <small style="color:var(--text-muted); font-weight:bold;">REQUERIMIENTO INICIAL</small>
                <div style="margin-top:5px; font-size:0.9rem; color:var(--text-main);">
                    <?= nl2br(htmlspecialchars($cliente['requerimiento'] ?? 'Sin datos')) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="feed-container">
        
        <div class="input-box">
            <form method="POST">
                <input type="hidden" name="nueva_nota" value="1">
                <div class="input-tabs">
                    <label class="tab-label">
                        <input type="radio" name="tipo_nota" value="nota" checked> 
                        <span style="color:#ca8a04">📝 Nota Interna</span>
                    </label>
                    <label class="tab-label">
                        <input type="radio" name="tipo_nota" value="conversacion"> 
                        <span style="color:#2563eb">🗣️ Conversación</span>
                    </label>
                </div>
                <div style="display:flex; gap:10px;">
                    <textarea name="contenido_nota" rows="2" placeholder="Escribe aquí..." style="flex:1; padding:12px; border:1px solid var(--border); border-radius:8px; outline:none; resize:none;" required></textarea>
                    <button type="submit" class="btn btn-primary" style="padding:0 20px; font-size:1.2rem;">➤</button>
                </div>
            </form>
        </div>

        <div class="timeline-feed">
            <?php while($h = $historial->fetch_assoc()): 
                $tipoClass = ($h['tipo'] == 'nota') ? 'type-nota' : 'type-conversacion';
            ?>
                <div class="feed-card <?= $tipoClass ?>">
                    <div class="feed-icon">
                        <?= ($h['tipo'] == 'nota') ? '📌' : '💬' ?>
                    </div>
                    <div class="feed-content">
                        <div class="feed-meta">
                            <strong><?= htmlspecialchars($h['usuario']) ?></strong>
                            <span><?= date("d M Y - H:i", strtotime($h['fecha'])) ?></span>
                        </div>
                        <div class="feed-body"><?= nl2br(htmlspecialchars($h['mensaje'])) ?></div>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if($historial->num_rows == 0): ?>
                <div style="text-align:center; padding:40px; color:var(--text-muted); opacity:0.7;">
                    <div style="font-size:3rem; margin-bottom:10px;">📭</div>
                    <p>No hay historial todavía.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
function activarEdicion(campo) {
    // 1. Ocultar el texto normal
    document.getElementById('view_' + campo).style.display = 'none';
    // 2. Mostrar el formulario
    document.getElementById('form_' + campo).classList.add('active');
    // 3. Poner foco en el input
    document.querySelector('#form_' + campo + ' .edit-input').focus();
}

function cancelarEdicion(campo) {
    // 1. Mostrar texto normal
    document.getElementById('view_' + campo).style.display = 'block';
    // 2. Ocultar formulario
    document.getElementById('form_' + campo).classList.remove('active');
}
</script>
