<?php //script para eliminar articulos desde la tabla de articulos
include("datos.php");
header("Content-type: application/json");
if(isset($_POST["id_item"]))
	$id_item=$_POST["id_item"];
$cant=$_POST["cantidad"]; //cantidad
$precio=$_POST["precio"]; //cantidad
$total=$cant*$precio;
$cot=$_POST["id_cotizacion"]; //cotizacion
$art=$_POST["id_articulo"]; //articulo id
if(isset($_POST["id_paquete"])) //paquete id
	$paq=$_POST["id_paquete"]; //paquete id
$time1 = $_POST["time1"];
$time2 = $_POST["time2"];
$time3 = $_POST["time3"];
$time4 = $_POST["time4"];
$time5 = $_POST["time5"];
$time6 = $_POST["time6"];
$time7 = $_POST["time7"];
$time8 = $_POST["time8"];
$time9 = $_POST["time9"];
try{
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
	
	$sqlBuscar="";
	if($id_item!=""){//si ya está guardado previamente
		$sql="UPDATE cotizaciones_articulos SET id_cotizacion=$cot, id_articulo=$art, cantidad=$cant, precio=$precio, total=$total WHERE id_item=$id_item;";
		$bd->query($sql);
		$r["info"]="Modificacion al <strong>articulo</strong> realizada exitosamente";
	}else{//registro nuevo
		$sql="INSERT INTO 
			cotizaciones_articulos (id_cotizacion, id_articulo, cantidad, precio, total)
		VALUES ($cot, $art, '$cant', '$precio', '$total');";
		$bd->query($sql);
		$sqlBuscar="SELECT MAX(id_articulo) as id_articulo FROM cotizaciones_articulos WHERE id_cotizacion=$cot";
		$res = $bd->query($sqlBuscar);
		$res = $res->fetchAll(PDO::FETCH_ASSOC);
		try{
			$sqlHora = "insert into cotizaciones_hora(id_cotizacion, hora, id_art) values($cot, '$time1', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into cotizaciones_hora(id_cotizacion, hora, id_art) values($cot, '$time2', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into cotizaciones_hora(id_cotizacion, hora, id_art) values($cot, '$time3', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into cotizaciones_hora(id_cotizacion, hora, id_art) values($cot, '$time4', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into cotizaciones_hora(id_cotizacion, hora, id_art) values($cot, '$time5', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into cotizaciones_hora(id_cotizacion, hora, id_art) values($cot, '$time6', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into cotizaciones_hora(id_cotizacion, hora, id_art) values($cot, '$time7', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into cotizaciones_hora(id_cotizacion, hora, id_art) values($cot, '$time8', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into cotizaciones_hora(id_cotizacion, hora, id_art) values($cot, '$time9', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
		
			$r["info"]="<strong>Articulo</strong> guardado exitosamente";
			$r["id_item"]=$id_item;
			$r["continuar"]=true;
		}catch(PDOException $err){
			$r["continuar"]=false;
			$r["info"]="Error encontrado: ".$err->getMessage();
		}
	}
}catch(PDOException $err){
	$r["continuar"]=false;
	$r["info"]="Error encontrado: ".$err->getMessage();
}
//0084609

echo json_encode($r);
?>