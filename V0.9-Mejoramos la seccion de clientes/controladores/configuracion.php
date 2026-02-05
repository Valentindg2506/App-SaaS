<?php

	// 1. AUTO-REPARACIÓN (Simplified)
	$conexion->query("CREATE TABLE IF NOT EXISTS configuracion (id INT PRIMARY KEY, color_menu VARCHAR(50), color_body VARCHAR(50), color_texto_menu VARCHAR(50), color_texto_body VARCHAR(50))");
	// Ensure row exists
	$conexion->query("INSERT IGNORE INTO configuracion (id, color_menu, color_body) VALUES (1, '#1e293b', '#f1f5f9')");

	// PROCESAR CAMBIOS DE TEMA
	if(isset($_POST['update_theme'])){
		// MENU: Color libre
		$c_menu = $_POST['color_menu'];
		
		// BODY: Modo Claro vs Oscuro (Preset)
		$modo = $_POST['modo_body']; // 'light' or 'dark'
		if($modo == 'dark'){
		    $c_body = '#0f172a'; // Dark Slate
		} else {
		    $c_body = '#f1f5f9'; // Ligh Gray
		}
		
		// GUARDAR (Ignoramos texto, se calcula al vuelo o se podria guardar calculado aqui, pero mejor al vuelo en index)
		$stmt = $conexion->prepare("UPDATE configuracion SET color_menu=?, color_body=? WHERE id=1");
		$stmt->bind_param("ss", $c_menu, $c_body);
		$stmt->execute();
		
		echo "<script>window.location.href='?operacion=configuracion';</script>";
		exit;
	}

	// OBTENER DATOS
	$dataset = $conexion->query("SELECT * FROM configuracion WHERE id=1")->fetch_assoc();
	// Detectar modo actual
	$is_dark = ($dataset['color_body'] == '#0f172a');
?>

<div class="header-title" style="border:none; margin-bottom:1rem;">
    <div>
        <h2 style="font-size:2rem; color:var(--text-main); font-weight:700; margin-bottom: 5px;">Centro de Control</h2>
        <p style="color:var(--text-muted);">Personaliza la experiencia y gestiona el equipo.</p>
    </div>
</div>

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">

    <!-- 1. TARJETA DE APARIENCIA -->
    <div class="card" style="padding:0; overflow:hidden; border:none; box-shadow:var(--shadow-lg);">
        <div style="background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 2rem; color: white;">
            <h3 style="margin:0; font-size:1.5rem;">🎨 Personalización Inteligente</h3>
            <p style="opacity:0.9; margin-top:5px;">Colores adaptables y modos de visualización.</p>
        </div>
        <div style="padding: 2rem;">
            <form method="POST" action="">
                <input type="hidden" name="update_theme" value="1">
                
                <!-- MENU SECTION -->
                <div style="margin-bottom:25px;">
                    <label style="display:block; margin-bottom:10px; font-weight:600; color:var(--text-main);">
                        Color Menú Lateral
                    </label>
                    <div style="display:flex; align-items:center; gap:15px; background:var(--bg-body); padding:10px; border-radius:10px; border:1px solid var(--border);">
                        <input type="color" name="color_menu" value="<?= $dataset['color_menu'] ?>" style="border:none; width:40px; height:40px; cursor:pointer; background:none; padding:0;">
                        <span style="color:var(--text-muted); font-size:0.9rem;">Elige tu color de marca. El texto se adaptará automáticamente.</span>
                    </div>
                </div>

                <!-- CAMBIAR DE MODO -->
                <div style="margin-bottom: 30px;">
                     <label style="display:block; margin-bottom:10px; font-weight:600; color:var(--text-main);">Tema Principal</label>
                     <div style="display:flex; gap:10px;">
                     	<!-- MODO CLARO -->
                        <label style="flex:1; cursor:pointer;">
                            <input type="radio" name="modo_body" value="light" <?= !$is_dark ? 'checked' : '' ?> style="display:none;" onchange="this.form.submit()">
                            <div style="padding:15px; border:2px solid <?= !$is_dark ? 'var(--accent)' : 'var(--border)' ?>; border-radius:10px; text-align:center; background:#fff; color:#1e293b; transition:all 0.2s;">
                                <div style="font-size:1.5rem;">☀️</div>
                                <div style="font-weight:600;">Claro</div>
                            </div>
                        </label>
                        <!-- MODO OSCURO -->
                        <label style="flex:1; cursor:pointer;"> 
                            <input type="radio" name="modo_body" value="dark" <?= $is_dark ? 'checked' : '' ?> style="display:none;" onchange="this.form.submit()">
                            <div style="padding:15px; border:2px solid <?= $is_dark ? 'var(--accent)' : 'var(--border)' ?>; border-radius:10px; text-align:center; background:#0f172a; color:#fff; transition:all 0.2s;">
                                <div style="font-size:1.5rem;">🌙</div>
                                <div style="font-weight:600;">Oscuro</div>
                            </div>
                        </label>
                     </div>
                </div>

                <div style="text-align:right;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem;">
                        Guardar Preferencias
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
