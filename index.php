<?php session_start(); 
include("scripts/datos.php");
if(isset($_SESSION["id_usuario"])){
	if($_SESSION["id_usuario"]!=""){
		header('Location: '.LIGA.'home.php');
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" media="all" href="css/index.css" />
	<link rel="stylesheet" media="all" href="css/home.css" />
<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="js/index.js"></script>
	<title>Sistema | CTA Cancún & Riviera Maya DMCs</title>
</head>

<body>
	<div class="app_name">sistema | *CTA Cancún & Riviera Maya DMC*</div>
	<div class="vendor_name">&copy; 2015 - Americanet - Todos los derechos reservados.</div>
<div class="logo_amer"></div>
<table cellpadding="0" cellspacing="0" border="0">
<tr valign="middle">
<td align="center">
	<form id="login">
	    <div class="titulo">Iniciar Sesión</div>
    	<input type="text" name="usuario" placeholder="Usuario" /><br />
        <input type="password" name="pass" placeholder="Contraseña" /><br />
    	<input class="login" type="button" value="Ingresar" />
        <div class="respuesta"></div>
    </form>
</td>
</tr>
</table>
</body>
</html>