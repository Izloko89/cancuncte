<?php session_start();
header("content-type: application/json");
include("datos.php");



$id_cot=$_POST["id_cotizacion"];


if($id_cot!=""){
	try{
		$bd=new PDO($dsnw,$userw,$passw,$optPDO);
			
		//para obtener el id de evento
		$sql="SELECT 
			id_evento,
			id_cliente
		FROM eventos
		WHERE id_cotizacion = $id_cot;";
		$res=$bd->query($sql);
		$res=$res->fetchAll(PDO::FETCH_ASSOC);
		
		//se conoce el id de cliente y del evento
		$id_eve=$res[0]["id_evento"];
		
	
			$r["id"] = $id_eve;
		$r["continuar"]=true;
	}catch(PDOException $err){
		$r["continuar"]=false;
		$r["info"]="Error: ".$err->getMessage()." <br />";
	}
}else{
	$r["continuar"]=false;
	$r["info"]="No ha seleccionado ninguna cotización";
}
		echo json_encode($r);

?>