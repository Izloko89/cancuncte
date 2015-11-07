<?php session_start();
//script para guardar articulos desde la tabla de articulos en eventos_articulos
include("datos.php");
include("funciones.php");
//include("s_check_inv_compra.php");
header("Content-type: application/json");

$emp=$_SESSION["id_empresa"];
$id_item=$_GET["id_item"];
$cant=$_GET["cantidad"]; //cantidad
$precio=$_GET["precio"]; //precio
$total=$cant*$precio; //total
$eve=$_GET["id_evento"]; //evento
$art=$_GET["id_articulo"]; //articulo id
$paq=$_GET["id_paquete"]; //paquete id
$time1 = $_GET["time1"];
$time2 = $_GET["time2"];
$time3 = $_GET["time3"];
$time4 = $_GET["time4"];
$time5 = $_GET["time5"];
$time6 = $_GET["time6"];
$time7 = $_GET["time7"];
$time8 = $_GET["time8"];
$time9 = $_GET["time9"];

//boolean para modificar el total
$boolTotal=false;
if($_GET["boolTotal"]=="true"){
	$boolTotal=true;
}
try{
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
	
	$sql="SELECT fechamontaje, fechadesmont FROM eventos WHERE id_evento=$eve;";
	$res=$bd->query($sql);
	$res=$res->fetchAll(PDO::FETCH_ASSOC);
	$montaje=$res[0]["fechamontaje"];
	$desmontaje=$res[0]["fechadesmont"];
	
	$sqlBuscar="";
	if($art!=""){//si es articulo
		//buscar el evento y el perecedero del articulo
		$sql="SELECT perece FROM articulos WHERE id_articulo=$art;";
		$res=$bd->query($sql);
		$res=$res->fetchAll(PDO::FETCH_ASSOC);
		$perece=$res[0]["perece"];
	
		if($id_item!=""){//si ya está guardado previamente hay que restar de salidas y entradas para volverlos a escribir
			//saber la cantidad original del item y luego restarlo de las entradas y salidas
			$sql="SELECT cantidad FROM eventos_articulos WHERE id_item=$id_item;";
			$res=$bd->query($sql);
			$res=$res->fetchAll(PDO::FETCH_ASSOC);
			$cantPrevia=$res[0]["cantidad"];
			
			//termina los que estaban antes
			$sql="UPDATE almacen_entradas SET termino=1, entro=1 WHERE id_evento=$eve AND id_articulo=$art;";
			$bd->query($sql);
			$sql="UPDATE almacen_salidas SET termino=1, salio=1 WHERE id_evento=$eve AND id_articulo=$art;";
			$bd->query($sql);
			
			//modificar las entradas y salidas con el negativo de cantPrevia
			$sql="INSERT INTO almacen_entradas (id_empresa,id_evento,id_articulo,cantidad,fechadesmont,entro,termino) VALUES ($emp,$eve,$art,'-$cantPrevia','$desmontaje',1,1);";
			$bd->query($sql);
			$sql="INSERT INTO almacen_salidas (id_empresa,id_evento,id_articulo,cantidad,fechamontaje,salio,termino) VALUES ($emp,$eve,$art,'-$cantPrevia','$montaje',1,1);";
			$bd->query($sql);
			
			//modificar el articulo del evento
			$sql="UPDATE eventos_articulos SET id_evento=$eve, id_articulo=$art, cantidad=$cant, precio=$precio, total=$total WHERE id_item=$id_item;";
			$bd->query($sql);
			
			$r["info"]="Modificacion al <strong>articulo</strong> realizada exitosamente";
		
		}else{//registro nuevo con modificación al inventario
		
			$sql="INSERT INTO 
				eventos_articulos (id_evento, id_articulo, cantidad, precio, total)
			VALUES ($eve, $art, $cant, $precio, $total);";
			$bd->query($sql);
			$sql = "select MAX(id_item) as id_item from eventos_articulos";
			$res = $bd->query($sql);
			$res = $res->fetchAll(PDO::FETCH_ASSOC);
			$id_item= $res[0]["id_item"];
			//$id_item=$bd->lastInsertId();
			
		$sqlBuscar="SELECT MAX(id_articulo) as id_articulo FROM eventos_articulos WHERE id_evento=$eve";
		$res = $bd->query($sqlBuscar);
		$res = $res->fetchAll(PDO::FETCH_ASSOC);
		
			$sqlHora = "insert into eventos_hora(id_evento, hora, id_art) values($eve, '$time1', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into eventos_hora(id_evento, hora, id_art) values($eve, '$time2', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into eventos_hora(id_evento, hora, id_art) values($eve, '$time3', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into eventos_hora(id_evento, hora, id_art) values($eve, '$time4', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into eventos_hora(id_evento, hora, id_art) values($eve, '$time5', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into eventos_hora(id_evento, hora, id_art) values($eve, '$time6', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into eventos_hora(id_evento, hora, id_art) values($eve, '$time7', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into eventos_hora(id_evento, hora, id_art) values($eve, '$time8', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
			$sqlHora = "insert into eventos_hora(id_evento, hora, id_art) values($eve, '$time9', " . $res[0]["id_articulo"] ." );";
			echo $sqlHora;
			$bd->query($sqlHora);
						
			if($perece == 0)
			{
				$sql = "select cantidad from almacen_inventario where id_articulo = $art;";
				$res = $bd->query($sql);
				$res = $res->fetchAll(PDO::FETCH_ASSOC);
				if($res[0]["cantidad"] < $cant)
				{
					$sql1 = "select MAX(folio) as folio from compras;";
					$res1 = $bd->query($sql1);
					$res1 = $res1->fetchAll(PDO::FETCH_ASSOC);
					$fol = 1;
					if(isset($res1[0]["folio"]))
					{
						$fol = $res1[0]["folio"] + 1;
					}
					$sql="INSERT INTO  compras (id_empresa, id_evento, folio) VALUES (1, $eve, $fol);";
					$bd->query($sql);
					$sql1 = "select MAX(id_compra) as id_compra from compras;";
					$res1 = $bd->query($sql1);
					$res1 = $res1->fetchAll(PDO::FETCH_ASSOC);
					$id_compra= $res1[0]["id_compra"];
					
					$cantCompra = $cant - $res[0]["cantidad"];
					$sql="INSERT INTO  compras_articulos (id_compra, id_empresa, id_articulo, cantidad) VALUES ($id_compra, 1, $art, $cantCompra);";
					$bd->query($sql);
				}
					
					$cantFinal = $res[0]["cantidad"] - $cant;
					if($cantFinal < 0)
					{
						$cantFinal = 0;
					}
					$sql = "update almacen_inventario set cantidad = $cantFinal where id_articulo = $art;";
					$bd->query($sql);
			}
			$r["info"]="<strong>Articulo</strong> guardado exitosamente";
		}
		//se debe añadir los elementos recién ingresados a la lista de salidas y entradas
		//si perece entonces no deben tener entrada de vuelta solamente salida
		/*
		if($perece==0){//no perece, da la entrada y salida
			//salida
			$sql="INSERT INTO almacen_salidas (id_empresa,id_evento,id_articulo,cantidad,fechamontaje,id_item) VALUES ($emp,$eve,$art,$cant,'$montaje',$id_item);";
			$bd->exec($sql);
			
			//entrada
			$sql="INSERT INTO almacen_entradas (id_empresa,id_evento,id_articulo,cantidad,fechadesmont,id_item) VALUES ($emp,$eve,$art,$cant,'$desmontaje',$id_item);";
			$bd->exec($sql);
		}else{
			//sí perece, da la salida solamente
			$sql="INSERT INTO almacen_salidas (id_empresa,id_evento,id_articulo,cantidad,fechamontaje,id_item) VALUES ($emp,$eve,$art,$cant,'$montaje',$id_item);";
			$bd->exec($sql);
		
		}*/
	}else if($paq!=""){//si es paquete
		if($id_item!=""){//si ya está guardado previamente
				//se restan las salidas del paq
				$sql="INSERT INTO 
					almacen_salidas (id_empresa,id_evento,id_articulo,cantidad,fechamontaje,salio,termino) 
				SELECT 1,1,articulos.id_articulo,(SELECT cantidad FROM eventos.almacen_salidas WHERE id_articulo=articulos.id_articulo ORDER BY id_salida DESC LIMIT 1)*-1 as cantidad,'$montaje',1,1
				FROM paquetes_articulos
				INNER JOIN articulos ON paquetes_articulos.id_articulo=articulos.id_articulo
				WHERE id_paquete=$paq;";
				$bd->query($sql);
				
				//actualiza los estatus del item
				$sql="UPDATE almacen_salidas SET termino=1, salio=1 WHERE id_evento=$eve AND id_articulo IN (SELECT id_articulo FROM paquetes_articulos WHERE id_paquete=$paq);";
				$bd->query($sql);
				$sql="UPDATE almacen_entradas SET termino=1, entro=1 WHERE id_evento=$eve AND id_articulo IN (SELECT id_articulo FROM paquetes_articulos WHERE id_paquete=$paq);";
				$bd->query($sql);
				
				//se restan las entradas del paq cuyo articulo no sea perecedero
				$sql="INSERT INTO 
					almacen_entradas (id_empresa,id_evento,id_articulo,regresaron,fechadesmont, entro, termino) 
				SELECT 1,1,articulos.id_articulo,(SELECT cantidad FROM eventos.almacen_salidas WHERE id_articulo=articulos.id_articulo ORDER BY id_salida DESC LIMIT 1)*-1 as cantidad,'$desmontaje',1,1
				FROM paquetes_articulos
				INNER JOIN articulos ON paquetes_articulos.id_articulo=articulos.id_articulo
				WHERE id_paquete=$paq AND articulos.perece=0;";
				$bd->query($sql);
			
			//se actualizan las cantidades del paquete en eventos_articulos
			$sql="UPDATE eventos_articulos SET id_evento=$eve, id_paquete=$paq, cantidad=$cant, precio=$precio, total=$total WHERE id_item=$id_item;";
			$bd->query($sql);
			
				//se escriben las salidas del paq
				$sql="INSERT INTO 
					almacen_salidas (id_empresa,id_evento,id_articulo,cantidad,fechamontaje) 
				SELECT $emp,$eve,articulos.id_articulo,paquetes_articulos.cantidad*$cant as cantidad,'$montaje' 
				FROM paquetes_articulos
				INNER JOIN articulos ON paquetes_articulos.id_articulo=articulos.id_articulo
				WHERE id_paquete=$paq;";
				$bd->query($sql);
				
				//se escriben las entradas del paq cuyo articulo no sea perecedero
				$sql="INSERT INTO 
					almacen_entradas (id_empresa,id_evento,id_articulo,cantidad,fechadesmont) 
				SELECT $emp,$eve,articulos.id_articulo,paquetes_articulos.cantidad*$cant as cantidad,'$desmontaje' 
				FROM paquetes_articulos
				INNER JOIN articulos ON paquetes_articulos.id_articulo=articulos.id_articulo
				WHERE id_paquete=$paq AND articulos.perece=0;";
				$bd->query($sql);
			
			$r["info"]="Modificación al <strong>paquete</strong> realizada exitosamente";
		}else{//registro nuevo
			$sql="INSERT INTO
				eventos_articulos (id_evento, id_paquete, cantidad, precio, total)
			VALUES ($eve, $paq, $cant, $precio, $total);";
			$bd->query($sql);
			$sql = "select MAX(id_item) as id_item from eventos_articulos";
			$res = $bd->query($sql);
			$res = $res->fetchAll(PDO::FETCH_ASSOC);
			$id_item= $res[0]["id_item"];
			
			//se escriben las salidas de los ariculos del paquete
			$sql="INSERT INTO 
				almacen_salidas (id_empresa,id_evento,id_articulo,cantidad,fechamontaje,id_item) 
			SELECT $emp,$eve,articulos.id_articulo,cantidad*$cant as cantidad,'$montaje',$id_item 
			FROM paquetes_articulos
			INNER JOIN articulos ON paquetes_articulos.id_articulo=articulos.id_articulo
			WHERE id_paquete=$paq;";
			$bd->query($sql);
			
			//se restan las entradas del item cuyo articulo no sea perecedero
			$sql="INSERT INTO 
				almacen_entradas (id_empresa,id_evento,id_articulo,cantidad,fechadesmont,id_item) 
			SELECT $emp,$eve,articulos.id_articulo,paquetes_articulos.cantidad*$cant as cantidad,'$desmontaje',$id_item
			FROM paquetes_articulos
			INNER JOIN articulos ON paquetes_articulos.id_articulo=articulos.id_articulo
			WHERE id_paquete=$paq AND articulos.perece=0;";
			$bd->query($sql);
			
			//
			$r["info"]="<strong>Paquete</strong> guardado exitosamente";
		}
	}
	
	//se actualiza el inventario y se genera l orden de compra
	//ordenCompra($eve);
	
	//modificar el total +=$total
	if($boolTotal){
		$total;
		$id_emp_eve=$emp."_".$eve;
		$sql="UPDATE eventos_total SET total=total+$total WHERE id_evento='$id_emp_eve';";
		//$bd->exec($sql);
	}
	
	//$bd->commit();
	$r["id_item"]=$id_item;
	$r["continuar"]=true;
}catch(PDOException $err){
	$r["continuar"]=false;
	$r["info"]="Error encontrado: ".$err->getMessage()." $sql";
}
//0084609

echo json_encode($r);
?>