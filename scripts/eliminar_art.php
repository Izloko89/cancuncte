<?php session_start();
include("datos.php");
include("func_form.php");
$aidi = $POST["aidi"];

try{
	$sql="delete FROM gastos_art WHERE id='$aidi';";
	$bd=new PDO($dsnw,$userw,$passw,$optPDO);
	$bd->query($sql);
	

	
	r["success"] = true;
	
}catch(PDOException $err){
	r["success"] = false;
	echo "Error: ".$err->getMessage();
	
}
echo json_encode($r);
?>