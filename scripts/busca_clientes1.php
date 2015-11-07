<?php session_start();
header("Content-type: application/json");
$empresaid=$_SESSION["id_empresa"];
$term=$_GET["term"];
include("datos.php");

try{
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
	//sacar los campos para acerlo más autoámtico
	$campos=array();
	$res=$bd->query("DESCRIBE clientes;");
	foreach($res->fetchAll(PDO::FETCH_ASSOC) as $a=>$c){
		$campos[$a]=$c["Field"];
	}
	$campos1=array();
	$res=$bd->query("DESCRIBE clientes_contacto;");
	foreach($res->fetchAll(PDO::FETCH_ASSOC) as $a=>$c){
		$campos1[$a]=$c["Field"];
	}
	$campos2=array();
	$res=$bd->query("DESCRIBE clientes_fiscal;");
	foreach($res->fetchAll(PDO::FETCH_ASSOC) as $a=>$c){
		$campos2[$a]=$c["Field"];
	}
	
	$res=$bd->query("SELECT * FROM clientes
					INNER JOIN clientes_contacto ON clientes.id_cliente = clientes_contacto.id_cliente
					INNER JOIN clientes_fiscal ON clientes.id_cliente = clientes_fiscal.id_cliente
					WHERE clientes.id_empresa=$empresaid AND nombre LIKE '$term%' OR clientes.clave = '$term';");
	foreach($res->fetchAll(PDO::FETCH_ASSOC) as $i=>$v){
		$r[$i]["label"]=$v["nombre"];
		$r[$i]["form"]="#f_clientes";
		foreach($campos as $campo){
			$r[$i][$campo]=$v[$campo];
		}
		foreach($campos1 as $campo){
			$r[$i][$campo]=$v[$campo];
		}
		foreach($campos2 as $campo){
			$r[$i][$campo]=$v[$campo];
		}
	}
	
}catch(PDOException $err){
	echo $err->getMessage();
}

echo json_encode($r);
?>