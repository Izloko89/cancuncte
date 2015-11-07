<?php
	$nombre = $_POST["nombre"];
	$precio = $_POST["precio"];
	include_once("datos.php");
    $bd = new PDO($dsnw, $userw, $passw, $optPDO);
	
	try{
		$sql = "insert into articulos (id_empresa, nombre)values(1, '$nombre')";
		$bd->query($sql);
		$sql = "select MAX(id_articulo) as articulo from articulos";
		$res = $bd->query($sql);
		$res = $res->fetchAll(PDO::FETCH_ASSOC);
		$item = $res[0]["articulo"];
		$sql = "insert into listado_precios (id_empresa, id_articulo, precio1) values(1, $item, '$precio')";
		$bd->query($sql);
			$r=true;
	}catch(PDOException $err){
			$r=false;
	}
	echo json_encode($r);
	
?>