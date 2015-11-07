<?php
	session_start(); 
include("datos.php");
$emp=$_SESSION["id_empresa"];

$nombre = $_POST["nombre"];
$limitecredito = $_POST["limitecredito"];
$clave = $_POST["clave"];
$direccion = $_POST["direccion"];
$colonia = $_POST["colonia"];
$ciudad = $_POST["ciudad"];
$estado = $_POST["estado"];
$cp = $_POST["cp"];
$telefono = $_POST["telefono"];
$celular = $_POST["celular"];
$email = $_POST["email"];
$rfcf = $_POST["rfcf"];
$razonf = $_POST["razonf"];
$nombrecomercialf = $_POST["nombrecomercialf"];
$direccionf = $_POST["direccionf"];
$coloniaf = $_POST["coloniaf"];
$ciudadf = $_POST["ciudadf"];
$estadof = $_POST["estadof"];

try{
	$bd=new PDO($dsnw,$userw,$passw,$optPDO);
	$sql="insert into clientes(id_empresa, clave, nombre, compania, ncom)values(1, '$clave', '$nombre', 'CTA', '$limitecredito')";
	$bd->query($sql);
	$sql="select MAX(id_cliente) as cliente from clientes";
	$res=$bd->query($sql);
	$res = $res->fetchAll(PDO::FETCH_ASSOC);
	$id_cliente = $res[0]["cliente"];
	$sql="insert into clientes_contacto(id_empresa, id_cliente, clave, direccion, colonia, ciudad, estado, cp, telefono, celular, email)
	values(1, $id_cliente, '$clave', '$direccion', '$colonia', '$ciudad', '$estado', '$cp', '$telefono', '$celular', '$email')";
	$bd->query($sql);
	$sql="insert into clientes_fiscal(id_empresa, id_cliente, rfcf, razonf, nombrecomercialf, direccionf, coloniaf, estadof, cpf, ciudadf)
	values(1, $id_cliente, '$rfcf', '$razonf', '$nombrecomercialf', '$direccionf', '$coloniaf', '$estadof', '$rfcf', '$ciudadf')";
	$bd->query($sql);
	$r["continuar"] = true;
}catch(PDOException $err){
	$r = false;
}
echo json_encode($r);
?>