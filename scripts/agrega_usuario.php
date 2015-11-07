<?php session_start();
header("Content-type: application/json");
$empresaid=$_SESSION["id_empresa"];
$term=$_GET["term"];
include("datos.php");

$usuario = $_POST['usuario'];
$nombre = $_POST['nombre'];
$password = $_POST['password'];
$cotizacion = 0;
$evento = 0;
$almacen = 0;
$compras = 0;
$bancos = 0;
$modulos = 0;


$cotizacion = $_POST['coti'];
$evento = $_POST['even'];
$almacen = $_POST['alma'];
$compras = $_POST['compr'];
$bancos = $_POST['banc'];
$modulos = $_POST['modu'];

	$bd=new PDO($dsnw, $userw, $passw, $optPDO);


try{	
	$bd->query("insert into usuarios (usuario,password,nombre,categoria) values ('$usuario','$password','$nombre','administrador')");
	
	$res = $bd->query("SELECT MAX( id_usuario ) as id FROM usuarios");
	$adidi = $res->fetchAll(PDO::FETCH_ASSOC);
	
	$id = $adidi[0]["id"];

	$sql = "insert into usuario_permisos (id_usuario,cotizacion,evento,almacen,compras,bancos,modulos) values
	($id,$cotizacion,$evento,$almacen,$compras,$bancos,$modulos)";
	$bd->query($sql);
	echo $sql;
	echo true;
	
}catch(PDOException $err){
	echo $err->getMessage();
	echo false;
}
?>