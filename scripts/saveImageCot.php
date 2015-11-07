<?php
	include_once("datos.php");
    $bd = new PDO($dsnw, $userw, $passw, $optPDO);
	$cot = $_POST["cotizacion"];
	$date = microtime(true);
	$date = explode(".", $date);
	$date1 = $date[0] . $date[1];
	$date2 = base64_encode($date1);
	$date3 = explode("=", $date2);
	$name = $date3[0] . $date[1];
	
	
	$fileType = $_FILES["file-0"]["type"];
	$fileType = explode("/", $fileType);
	
	
	try
	{
		$taco = move_uploaded_file($_FILES['file-0']['tmp_name'], "../logo/" . $name . "." . $fileType[1]);
			$sql = "insert into cotizacion_imagen (path, id_cotizacion) values('../logo/" . $name . "." . $fileType[1]."', $cot)";
			$bd->query($sql);
			$r["info"] = $_FILES['file-0']["tmp_name"]; 
			echo json_encode($taco);
	} catch (PDOException $err) {
		$r["info"] = $err->getMessage();
	}
	
?>