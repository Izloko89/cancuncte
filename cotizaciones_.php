<head>
	<link rel="shortcut icon" href="img/favicon.ico">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Sistema | CTA Cancún & Riviera Maya DMC</title>
</head>
<?php include("partes/header.php");
setlocale(LC_ALL,"");
setlocale(LC_TIME,"es_MX");
include("scripts/func_form.php");

//pendientes
//- Añadir
//- cuando se añade un nuevo articulo pasarlo al almacen

//modificación para otro cliente
//- tapar el movimiento del evento si el salón ya estpa ocupado en la nueva fecha


?>
<link rel="stylesheet" media="all" href="css/eventos.css" />
<script src="js/cotizaciones.js"></script>
<script src="js/formularios.js"></script>
<style>
/* estilos para formularios */
.alejar_izq{
	margin-left:10px;
}
.clave{
	text-align:right;
}
.campo_form{
	margin:4px 0;
	text-align:center;
}
.text_corto{
	width:80px;
}
.text_mediano{
	width:150px;
}
.text_largo{
	width:400px;
}
.text_full_width{
	width:100%;
}
.text_half_width{
	width:50%;
}
.label_width{
	width:175px;
}
.borrar_fecha{
	cursor:pointer;
	display:none;
}
.input
{
	background-color:#B7FFB6;
	border-bottom-style:none;
}
	.input_sin_color
	{
		background-color: transparent;
		font-size: 50px;
		font-weight: bold;
		color: #063;
		border-width:0;
	}
table{
	margin:0 auto;
}
	#imp_cot
	{
		background: transparent url('img/Mexico.png') left center no-repeat;
		background-size: contain;
		width: 60px;
		height: 30px;
		display: inline-block;
		text-decoration: none;
		text-align: right;

	}	
	#imp_cot1
	{
		background: transparent url('img/United States of America (USA).png') left center no-repeat;
		background-size: contain;
		width: 60px;
		height: 30px;
		display: inline-block;
		text-decoration: none;
		text-align: right;

	}
.guardar_articulo{
	background: white url('img/check.png') left center no-repeat;
	background-size:contain;
	cursor:pointer;
	width:20px;
	height:20px;
	display:inline-block;
	margin-right:10px;
}
.eliminar_articulo{
	background: white url('img/cruz.png') left center no-repeat;
	background-size:contain;
	cursor:pointer;
	width:20px;
	height:20px;
	display:inline-block;
	margin-right:10px;
}
.crear_evento
	{
		border-top-color: #34740e;border-right-color: #34740e;border-bottom-color: #34740e;border-left-color: #34740e;border-width: 0px;border-style: solid;-webkit-box-shadow: #737373 0px 0px 5px ;-moz-box-shadow: #737373 0px 0px 5px ; box-shadow: #737373 0px 0px 5px ; -webkit-border-radius: 6px; -moz-border-radius: 6px;border-radius: 6px;font-size:18px;font-family:arial, helvetica, sans-serif; padding: 5px 10px 5px 10px; text-decoration:none; display:inline-block;text-shadow: 0px 0px 0 rgba(4,2,10,0);font-weight:bold; color: #FFFFFF;
		background-color: #3F8C11; background-image: -webkit-gradient(linear, left top, left bottom, from(#3F8C11), to(#008c00));
		background-image: -webkit-linear-gradient(top, #3F8C11, #008c00);
		background-image: -moz-linear-gradient(top, #3F8C11, #008c00);
		background-image: -ms-linear-gradient(top, #3F8C11, #008c00);
		background-image: -o-linear-gradient(top, #3F8C11, #008c00);
		background-image: linear-gradient(to bottom, #3F8C11, #008c00);filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#3F8C11, endColorstr=#008c00);
	}

	.crear_evento:hover{
		border:0px solid #224b09;
		background-color: #2b5f0b; background-image: -webkit-gradient(linear, left top, left bottom, from(#2b5f0b), to(#006600));
		background-image: -webkit-linear-gradient(top, #2b5f0b, #006600);
		background-image: -moz-linear-gradient(top, #2b5f0b, #006600);
		background-image: -ms-linear-gradient(top, #2b5f0b, #006600);
		background-image: -o-linear-gradient(top, #2b5f0b, #006600);
		background-image: linear-gradient(to bottom, #2b5f0b, #006600);filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#2b5f0b, endColorstr=#006600);
	}
#hacer .precio{
	/*display:none;*/
	width:60px;
}

.divplazos, .divbancos{
	display:inline-block;
}
	.input
	{
		background-color:#B7FFB6;
	}
	.tamaño
	{
		width:250px;
		height:1500px;
	}
#cuenta .campo_form{
	text-align:left;
}
#cuenta label{
	display:inline-block;
	width:100px;
	margin-right:5px;
}
</style>
<div id="contenido">
<div id="tabs">
	<ul>
	<li class="hacer"><a href="#hacer">Cotizar</a></li>
	<li class="mias"><a href="#mias">Mis cotizaciones</a></li>
	</ul>
	<div id="hacer">
	<form id='cotizaciones' class='formularios'>
	<h3 class='titulo_form'>Datos del evento</h3>
		<div class="tabla">

	<?php //si viene con una clave dedde otra pagina
	  if(isset($_GET["cve"])){?>
		<input type="hidden" name="id_cotizacion" class="id_cotizacion" value="<?php echo $_GET["cve"]; ?>" />
	<?php }else{ ?>
		<input type="hidden" name="id_cotizacion" class="id_cotizacion" value="" />
	<?php } ?>

		<input type="text" name="id_usuario" class="id_usuario" value="<?php echo $_SESSION["id_usuario"]; ?>" style="display:none;" />
		<input type="hidden" name="id_cliente" class="id_cliente requerido" value="" />

		<div class="campo_form celda">
			<label class="">CLAVE</label>
			<?php //si viene con una clave dedde otra pagina
				if(isset($_GET["cve"])){?>
				<script>
				$(document).ready(function(e){
					buscarClaveGet();
				});
				</script>
					<input type="text" name="clave" id="clave" class="clave label clave_cotizacion requerido mayuscula text_corto" data-nueva="<?php nuevaClaveCotizar(); ?>" value="<?php echo $_GET["cve"]; ?>" />
			<?php }else{ ?>
				 <input type="text" name="clave" id="clave" class="clave label clave_cotizacion requerido mayuscula text_corto" data-nueva="<?php nuevaClaveCotizar(); ?>" value="<?php nuevaClaveCotizar(); ?>" />
			<?php } ?>
			</div>
			<!--
			<div class="campo_form salones celda" style="width:292px;">
				<label>Hoteles</label>
				<select name="salon" class="salon">
					<option selected disabled>Elige un hotel</option>
					<?php salonesOpt();	?>
				</select>
			</div>
		<div class="campo_form celda" style="margin-left:auto; margin-right:0;">
			<label>Actividades</label>
			<select name="id_tipo" class="id_tipo">
				<option selected disabled value="">Elige una actividad</option>
				<?php tipoEventosOpt();	?>
			</select>
		</div>-->
		</div>
		<div class="tabla">
		<div class="celda" style=" width:600px;">
			<div class="campo_form">
				<label>Nombre del cliente</label>
			<input class="cliente_cotizacion text_largo" type="text" />
			</div>
			<div class="campo_form">
			<label class="">Nombre del Grupo</label>
			<input type="text" name="nombre" class="nombre text_largo requerido" />
			</div>
		</div>
		<div class="celda" style="">
			<div class="campo_form">
			<label class="align_right" style="width:120px;">Fecha de inicio</label>
			<abbr title=""><input placeholder="Click para elegir" class="fecha alejar_izq requerido fechaevento" type="text" name="fechaevento" readonly/></abbr><!--
			--><img class="borrar_fecha" data-class="fechaevento" src="img/cruz.png" width="15" />
			</div>
			<div class="campo_form">
			<label class="align_right" style="width:120px;">Fecha de terminaciòn</label>
			<abbr title=""><input placeholder="Click para elegir" class="fecha alejar_izq requerido fechamontaje" type="text" name="fechamontaje" readonly/></abbr><!--
			--><img class="borrar_fecha" data-class="fechamontaje" src="img/cruz.png" width="15" />
			</div>
		</div>
		</div>
		<div align="right">
			<input type="button" class="modificar" value="MODIFICAR" data-wrap="#hacer" style="display:none;" />
			<input type="button" class="guardar" value="CREAR" data-wrap="#hacer" data-accion="guardar" data-m="pivote" />
			<input type="button" class="nueva" value="NUEVA" data-s="s_nueva_cot" />
		</div>
	</form>
	<div class='formularios'>
		<h3 class='titulo_form'>Actividades y Articulos</h3>
	<table id="articulos">
		<tr>
			<th class="agregar_articulo"><img src="img/mas.png" height="25" /></th>
		<th width="100">Cant.</th>
		<th width="250">Concepto</th>
		<th width="100">Precio</th>
		<th width="100">Importe</th>
		<th width="150">Acciones</th>
		</tr>
	</table>
	</div>		
	<div id="nuevopago" class="formularios" style="display:none;">
	<h3 class='titulo_form'>Intinerario</h3>
		<table>
			<tr>
				<td>
					Participantes listos en el lobby
				</td>
				<td>
					<input type="time" id="time1" value="08:00" required = "required" />
				</td>
			</tr>
			<tr>
				<td>
					Salida del hotel
				</td>
				<td>
					<input type="time" id="time2" value="08:00" required = "required" />
				</td>
			</tr>
			<tr>
				<td>
					Llegada Estimada 
				</td>
				<td>
					<input type="time" id="time3" value="08:00" required = "required" />
				</td>
			</tr>
			<tr>
				<td>
					Tour Guiado 
				</td>
				<td>
					<input type="time" id="time4" value="08:00" required = "required" />
				</td>
			</tr>
			<tr>
				<td>
					Lunch: 
				</td>
				<td>
					<input type="time" id="time5" value="08:00" required = "required" />
				</td>
			</tr>
			<tr>
				<td>
					Tiempo Libre 
				</td>
				<td>
					<input type="time" id="time6" value="08:00" required = "required" />
				</td>
			</tr>
			<tr>
				<td>
					Show
				</td>
				<td>
					<input type="time" id="time7" value="08:00" required = "required" />
				</td>
			</tr>
			<tr>
				<td>
					Regreso a Hotel
				</td>
				<td>
					<input type="time" id="time8" value="08:00" required = "required" />
				</td>
			</tr>
			<tr>
				<td>
					Llegada a Hotel
				</td>
				<td>
					<input type="time" id="time9" value="08:00" required = "required" />
				</td>
			</tr>
		</table>
		</div>
	<div id="cuenta" class="formularios" align="left">
	<h3 class='titulo_form'>Cuenta</h3>
		<div class="campo_form">
			<label class="">Total del Evento</label>
			<input type="text" class="totalevento numerico input_sin_color" readonly="readonly"/>
		</div>
		<div class="campo_form">
			<label class="">Metodo de pago</label>
			<select class="metodo">
				<option value="contado">De contado</option>
				<option value="credito">A crédito</option>
				<option value="cheque">Cheque</option>
				<option value="transferencia">Transferencia</option>
			</select>
			<div class="divplazos" style="display:none;">
				<label class="">Plazos:</label>
				<input type="text" class="plazos numerico" size="4" value="1" />
			</div>
			<div class="divbancos" style="display:none;">
				<label class="">Bancos:</label>
				<select class="bancos"><option>Elige un banco</option></select>
			</div>
		</div>
		<div class="campo_form">
			<label class="">Anticipo:</label>
			<input type="text" class="anticipo numerico" />
			<label class="">Restante:</label>
			<input type="text" class="restante numerico" readonly="readonly" />
		</div>
		<div align="right">
			<a id="imp_cot" href="scripts/pdf_cotizacion.php" target="_blank" class="tamaño"></a>
			<a id="imp_cot1" href="scripts/pdf_cotizacion2.php" target="_blank" class="tamaño"></a>
			<input type="button" class="crear_evento" value="Pasar a evento" onclick="pasarevento();" />
		</div>
	</div>
	</div>
	<!-- //sección de las cotizaciones por empresa y or usuario -->
	<div id="mias">
	<style>
		#mias table{
		font-size:0.85em;
	}
	#mias th{
		font-size:1.05em;
		margin:2px;
	}
	#mias td{
		margin:2px;
		padding:5px 2px;
	}
	#mias .filtro{
		width:100%;
	}
	.accion{
		margin:0 5px;
		cursor:pointer;
	}
	</style>
	<table cellpadding="0" cellspacing="2" border="0" width="100%" class="listado">
	<tr>
		<th>Clave<br />Folio</th>
	<th style="width:200px;">Nombre del evento</th>
	<th style="width:200px;">Cliente</th>
	<th>Estatus</th>
	<th>Fecha<br />Inicio</th>
	<th>Fecha<br/>Terminación</th>
	<th>acciones</th>
	</tr>
	<tr class="barra_accion">
	<td style="width:34px;"><input class="filtro" data-c="bfolio" /></td>
	<td><input class="filtro" data-c="bnombre" /></td>
	<td><input class="filtro" data-c="btipo_evento" /></td>
	<td><input class="filtro" data-c="bcliente" /></td>
	<td style="width:34px;"><input class="filtro" data-c="bestatus" /></td>
	<td><input class="filtro filtrofecha" data-c="bfechaevento" /></td>
	<td><input class="filtro filtrofecha" data-c="bfechamontaje" /></td>
	<td><a href="#" class="pdf" onclick="return false;" data-nombre="evento" data-orientar="L">generar pdf</a></td>
	</tr>
		<?php
	try{
		$bd=new PDO($dsnw,$userw, $passw, $optPDO);
		$sqlCot="SELECT
			id_cotizacion,
			cotizaciones.clave,
			cotizaciones.nombre,
			estatus,
			fechaevento,
			fechamontaje
		FROM cotizaciones
		WHERE cotizaciones.id_empresa=$empresaid AND id_usuario=$userid;";
		$sqlClie="SELECT
			id_cotizacion,
			clientes.id_cliente,
			clientes.nombre,
			clientes.compania
		FROM clientes
		INNER JOIN cotizaciones ON clientes.id_cliente = cotizaciones.id_cliente
		WHERE clientes.id_empresa=$empresaid;";

		$cot=array();
		$res=$bd->query($sqlCot);
		foreach($res->fetchAll(PDO::FETCH_ASSOC) as $v){
			$ind=$v["id_cotizacion"];
			unset($v["id_cotizacion"]);
			$cot[$ind]=$v;
		}

		$cli=array();
		$res=$bd->query($sqlClie);
		foreach($res->fetchAll(PDO::FETCH_ASSOC) as $v){
			$ind=$v["id_cotizacion"];
			unset($v["id_cotizacion"]);
			$cli[$ind]=$v;
		}


		//correlacionar los subarrays al array principal de cotizacion
		foreach($cot as $ind=>$val){
			$cot[$ind]["cliente"]=$cli[$ind]["nombre"];
		}

		//escribimos la tabla
		foreach($cot as $folio=>$d){
			echo '<tr class="cot'.$d["clave"].'">';
			echo '<td class="bfolio">'.$d["clave"].'</td>';
			echo '<td class="bnombre">'.$d["nombre"].'</td>';
			echo '<td class="bcliente">'.$d["cliente"].'</td>';
			echo '<td class="bestatus">'.$d["estatus"].'</td>';
			echo '<td class="bfechaevento">'.varFechaAbrNorm($d["fechaevento"]).'</td>';
			echo '<td class="bfechamontaje">'.varFechaAbrNorm($d["fechamontaje"]).'</td>';
			echo '<td><img class="accion" src="img/edit.png" data-cve="'.$d["clave"].'" data-id="'.$folio.'" onclick="editar(this);" height="20" /><img class="accion eliminar" src="img/cruz.png" data-cve="'.$folio.'" height="20" /></td>';
			echo '</tr>';
		}
		$bd=NULL;
	}catch(PDOException $err){
		echo "Error encontrado: ".$err->getMessage();
	}
	?>
		</table>
	</div>
</div>
</div>
<?php include("partes/footer.php"); ?>
