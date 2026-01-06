<!doctype html>
<html>
	<head>
		<link rel="stylesheet" href="css/estilo.css">
	</head>
	<body>
		<?php include "inc/conexion_bd.php"; ?>
		<nav>
			<?php include "controladores/poblar_menu.php"; ?>
		</nav>
		<main>
			<?php
			// ENRUTADOR -> SE ENCARGA DE PROCESAR LAS OPERACIONES A MOSTRAR
				if(!isset($_GET['operacion'])){ // Si no hay operacion
					include "controladores/read.php";
				}else{
					if($_GET['operacion'] == "insertar"){
						include "controladores/insertar.php";
					}else if($_GET['operacion'] == "procesa_insertar"){
						include "controladores/procesa_insertar.php";
					}
				}		 
			?>
		</main>
	</body>
</html>
