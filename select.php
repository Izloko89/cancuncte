<html>
<head>


</head>
<body>


<?php

//$conexion=mysql_connect("localhost","root","") 
$conexion=mysql_connect("localhost","desarrollo_write","Writer1") 
  or die("Problemas en la conexion");
//mysql_select_db("eventos",$conexion) or
mysql_select_db("desarrollo_prueba1",$conexion) or
  die("Problemas en la seleccion de la base de datos");

  


  

  /*
$registros=mysql_query("Select clientes.nombre,clientes_fiscal.nombrecomercial,
clientes_fiscal.ciudad, clientes_fiscal.colonia,clientes_fiscal.cp,clientes_fiscal.direccion,
 clientes_fiscal.estado,clientes_fiscal.razon,clientes_fiscal.rfc, clientes_contacto.email,
 clientes_contacto.celular,clientes_contacto.telefono,clientes.fecha
 From clientes LEFT JOIN clientes_fiscal ON clientes.id_cliente=clientes_fiscal.id_cliente
 LEFT JOIN clientes_contacto ON clientes.id_cliente=clientes_contacto.id_cliente 
 WHERE clientes.id_cliente = $aidi",$conexion)

 or die("Problemas en el select:".mysql_error());
  
  echo "
  <table align=\"center\"  rules=\"groups\" border=\"0\">
  <tr>
  <th height="70px;" bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; margin-right=10px; font-size: 14px;\">Fecha de Alta</th>
     <th height="70px; bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; margin-right=10px; font-size: 14px;\">Nombre</th>
     <th height="70px; bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; margin-right=10px; font-size: 14px;\">Nombre Comercial</th>
     <th height="70px; bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF;  margin-right=10px;font-size: 14px;\">RFC</th>
    <th height="70px; bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; margin-right=10px; font-size: 14px;\">Razon social</th>
     <th height="70px; bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; margin-right=10px; font-size: 14px;\">Direccion</th>
     <th height="70px; bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; margin-right=10px; font-size: 14px;\">Estado</th>
     <th height="70px; bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; margin-right=10px; font-size: 14px;\">Codigo Postal</th>
     <th bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; font-size: 14px;  margin-right=10px;\">Ciudad</th>
     <th height="70px; bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF;  margin-right=10px; font-size: 14px;\">Colonia</th>
     <th height="70px; bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF;  margin-right=10px; font-size: 14px;\">Email</th>
     <th height="70px; bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold;color: #FFF; margin-right=10px; font-size: 14px;\">Telefono</th>
  
  </tr>
 
  ";
  
/*while ($reg=mysql_fetch_array($registros))
{
	echo "<tr>";
  echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\">".$reg['fecha']."</td>";
     echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['nombre']."</td>";
	  echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['nombrecomercial']."</td>";
	     echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['rfc']."</td>";
		   echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['razon']."</td>";
		     echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['direccion']."</td>";
			   echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['estado']."</td>";
			     echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['cp']."</td>";
				   echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['colonia']."</td>";
				     echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['email']."</td>";
					   echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['telefono']."</td>";
					     echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['celular']."</td>";

  echo "</tr>";
  
  
}*/

echo "</table>";


$checa = $_REQUEST['aidi'];

$registros_adeudos= mysql_query("select eventos_pagos.id_pago,eventos_pagos.plazo,eventos_pagos.fecha,eventos_pagos.cantidad,eventos_total.total From
eventos_pagos LEFT JOIN eventos_total ON eventos_pagos.id_evento=eventos_total.id_evento
 WHERE eventos_pagos.id_cliente = $checa",$conexion)
or die("Problemas en el select:".mysql_error());

echo "<table border=1 align=\"center\" rules=\"groups\" border=\"1\">
<tr>
  <th width=\"100px\" bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; font-size: 14px;\">Numero de pago</th>
  <th width=\"150px\" bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; font-size: 14px;\">Fecha</th>
  <th width=\"150px\" bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; font-size: 14px;\">Plazo</th>
  <th width=\"100px\" bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; font-size: 14px;\">Abono</th>
  <th width=\"100px\" bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; font-size: 14px;\">Total</th>
  <th width=\"100px\" bgcolor=\"#506e94\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; color: #FFF; font-size: 14px;\">Restante</th>
</tr>";


$control=0;
while ($reg=mysql_fetch_array($registros_adeudos))
{
	echo "<tr>";
	    echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['id_pago']."</td>";
    echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['fecha']."</td>";
	    echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['plazo']."</td>";
    echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: center; font-weight: bold; font-size: 14px;\" >".$reg['cantidad']."</td>";
	echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: left; font-weight: bold; font-size: 14px;\" >".$reg['total']."</td>";
	 
	 if($control==0)
	 {
	 $restante = $reg['total'] - $reg['cantidad'];
	 $control = 1;
	 }
	 
	 else
	 {
		$restante = $restante - $reg['cantidad'];
		 
	 }
	  echo "<td bgcolor=\"#f2f2f2\" style=\"font-family: Tahoma, Geneva, sans-serif; text-align: left; font-weight: bold; font-size: 14px;\" > $restante </td>";
	
	  echo "</tr>";
	
}
echo "</table>";


mysql_close($conexion);
?>


</body>
<html>