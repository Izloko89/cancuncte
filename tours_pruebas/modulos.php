<head>
<link rel="shortcut icon" href="img/favicon.ico">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Sistema | CTA Cancún & Riviera Maya DMC</title>
</head>
<?php
include("partes/header.php");
include("scripts/botones_form.php");

//permisos
$seccion="modu";
include("scripts/permisos.php");

?>
<script src="js/modulos.js"></script>
<style>
#botones_modulo *{
	-webkit-user-select: none; /* webkit (safari, chrome) browsers */
	-moz-user-select: none; /* mozilla browsers */
	-khtml-user-select: none; /* webkit (konqueror) browsers */
	-ms-user-select: none; /* IE10+ */
	border-radius:10px 10px 10px 10px;
}
.boton_abrir_form, .boton_abrir_form_dos{
	display:inline-block;
	background-color:#399;
	color:#FFF;
	margin:10px;
	width:130px !important;
	height:100px !important;
	cursor:pointer;
	font-weight:bold;
	font-size:1.3em;
}
.boton_abrir_form1
{
	display:inline-block;
	background-image:url(img/cta/propuesta.png);
	color:#FFF;
	margin:10px;
	width:130px !important;
	height:100px !important;
	cursor:pointer;
	font-weight:bold;
	font-size:1.3em;
}

.boton_abrir_form:hover, .boton_abrir_form_dos:hover{
	background-color:#C90;
}
.loading{
	margin-top:5px;
	display:none;
}
.boton_abrir_clientes
{
	display:inline-block;
	background-image:url('img/clientes.png');
	background-size:contain;
	color:#000;
	text-align:end;
	margin:10px;
	width:130px !important;
	height:100px !important;
	cursor:pointer;
	font-weight:bold;
	font-size:1.3em;
}
.boton_abrir_clientes:hover
{
 border:2px solid #1E23A8;
 background-color: #C0FABB; background-image: -webkit-gradient(linear, left top, left bottom, from(#C0FABB), to(#CCEBDF));
 background-image: -webkit-linear-gradient(top, #C0FABB, #CCEBDF);
 background-image: -moz-linear-gradient(top, #C0FABB, #CCEBDF);
 background-image: -ms-linear-gradient(top, #C0FABB, #CCEBDF);
 background-image: -o-linear-gradient(top, #C0FABB, #CCEBDF);
 background-image: linear-gradient(to bottom, #C0FABB, #CCEBDF);filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#C0FABB, endColorstr=#CCEBDF);
}
.boton_abrir_proveedores
{
	display:inline-block;
	background-image:url('img/proveedores.png');
	color:#FFF;
	margin:10px;
	width:130px !important;
	height:100px !important;
	cursor:pointer;
	font-weight:bold;
	font-size:1.3em;
}
.boton_abrir_proveedores:hover
{
background-color:#C90;
}

.boton_abrir_eventos
{
	display:inline-block;
	background-image:url('img/propuesta.png');
	background-size:contain;
	color:#FFF;
	margin:10px;
	width:130px !important;
	height:100px !important;
	cursor:pointer;
	font-weight:bold;
	font-size:1.3em;
}
.boton_abrir_eventos:hover
{
background-color:#C90;
}

.boton_abrir_Bancos
{
	display:inline-block;
	background-image:url('img/banks.png');
	background-size:contain;
	text-align:end;
	color:#FFF;
	margin:10px;
	width:130px !important;
	height:100px !important;
	cursor:pointer;
	font-weight:bold;
	font-size:1.3em;
}
.boton_abrir_Bancos:hover
{
background-color:#C90;
}
</style>
<div id="contenido">
<div id="tabs">
<ul>
  <li><a href="#acciones">Acciones</a></li>
</ul>
<div id="acciones">
	<div id="botones_modulo" style="" align="center">
    <?php //se muestran los botones de la categoria del usuario
		foreach($botones[$categoria] as $boton){ ?>
    	<div align="center" class="<?php echo $boton["accion"]; ?>" data-m="<?php echo $boton["metodo"]; ?>" data-form="<?php echo $boton["tabla"]; ?>">
          <div class="tabla" style="height:100%; text-align:center;"><div class="celda centrado_v">
        	<span><?php echo $boton["nombre"]; ?></span><br />
            <img class="loading" src="img/loading.gif" />
          </div></div>
        </div>
	<?php } ?>
    </div>
    <div id="formularios_modulo" style="padding-top:10px;"></div>
</div>
</div>
</div>
<?php include("partes/footer.php"); ?>