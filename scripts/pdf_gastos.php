<?php session_start();
setlocale(LC_ALL,"");
setlocale(LC_ALL,"es_MX");
include_once("datos.php");
require_once('../clases/html2pdf.class.php');
include_once("func_form.php");
$emp=$_SESSION["id_empresa"];
$cheque = $_GET["cheque"];
$elaborado = $_GET["elaborado"];
$selec = $_GET["selec"];
$obs = $_GET["obs"];
//funciones para usarse dentro de los pdfs
function mmtopx($d){
	$fc=96/25.4;
	$n=$d*$fc;
	return $n."px";
}
function pxtomm($d){
	$fc=96/25.4;
	$n=$d/$fc;
	return $n."mm";
}
function checkmark(){
	$url="http://".$_SERVER["HTTP_HOST"]."/img/checkmark.png";
	$s='<img src="'.$url.'" style="height:10px;" />';
	return $s;
}
function folio($digitos,$folio){
	$usado=strlen($folio);
	$salida="";
	for($i=0;$i<($digitos-$usado);$i++){
		$salida.="0";
	}
	$salida.=$folio;
	return $salida;
}
//tamaño carta alto:279.4 ancho:215.9
$heightCarta=960;
$widthCarta=660;
$celdas=12;
$widthCell=$widthCarta/$celdas;
$mmCartaH=pxtomm($heightCarta);
$mmCartaW=pxtomm($widthCarta);
ob_start();

//sacar los datos del cliente
$error="";
if(isset($_GET["id_evento"])){
	$obs=$_GET["obs"];
	$eve=$_GET["id_evento"];
	try{
		$bd=new PDO($dsnw,$userw,$passw,$optPDO);
		// para saber los datos del cliente
			$sql = "SELECT cantidad, precio, total, nombre
					FROM gastos_art
					INNER JOIN gastos ON gastos.id_gasto = gastos_art.id_gasto
					WHERE id_gEve =1";
			$res = $bd->query($sql);
			$sql = "select * from gastos_eventos where id = 1";
			$res = $bd->query($sql);

	}catch(PDOException $err){
		$error= $err->getMessage();
	}
}

?>
<?php if($error==""){ ?>
<style>
span{
	display:inline-block;
	padding:10px;
}
h1{
	font-size:20px;
}
.spacer{
	display:inline-block;
	height:1px;
}
td{
	background-color:#FFF;
}
th{
	color:#FFF;
	text-align:center;
}
</style>
<table style="width:100%; border:0.5px solid #000;" cellpadding="0" cellspacing="0" >
	    <tr>
      <td valign="middle" style="width:25%; text-align:center; font-size:14px; background-color:#000000; color:#FFFFFF;">bariconcept</td>
      <td valign="middle" style="width:35%; text-align:center;border:0.3px">ORDEN DE PAGO</td>
      <td valign="middle" style="width:15%; text-align:right;border:0.3px">Folio</td>
      <td valign="middle" style="width:25%; text-align:left; color:#C00;border:0.3px">N&ordm; 1</td>
    </tr>
    </table >
    <table cellpadding="0" cellspacing="0" style=" font-size:10px;width:100%; border:0.5px solid #000;">
      <tr align="center">
    	<td style="width:25%;color:#000; font-size:10px;border:0.3px">Solicitado para el evento:</td>
        <td style="width:25%;color:#000;font-size:10px;border:0.3px">ZURITA JR</td>
        <td style="width:25%;color:#000;font-size:10px;text-align:left;border:0.3px">Fecha Solicitud:</td>
        <td style="width:25%;color:#000;font-size:10px;border:0.3px">2015-03-30 13:46:45</td>
    </tr>
    <tr align="center">
    	<td style="width:25%;color:#000; font-size:10px;border:0.3px">Solicitado por:</td>
        <td style="width:25%;color:#000;font-size:10px;border:0.3px">sergio</td>        
        <td style="width:25%;color:#000;font-size:10px;text-align:left;border:0.3px">Fecha Requerido:</td>
        <td style="width:25%;color:#000;font-size:10px;border:0.3px">2015-03-30 13:46:45</td>
    </tr>
    </table>
    <br/>
  <table cellpadding="0" cellspacing="0" style=" font-size:10px;width:100%; border:0.5px solid #000;">
      <tr align="center">
    	<th style="width:10%;color:#000;border:0.3px">PARTIDA</th>
        <th style="width:10%;color:#000;border:0.3px ">CANTIDAD</th>
        <th style="width:40%;color:#000;border:0.3px">DESCRIPCION</th>
        <th style="width:15%;color:#000;border:0.3px">PRECIO UNITARIO</th>
        <th style="width:15%;color:#000;border:0.3px">NOTAS</th>
        <th style="width:10%;color:#000;border:0.3px">TOTAL</th>        
    </tr>
      <tr align="center">
    	<td style="width:10%;color:#000; border:0.3px ">1</td>
        <td style="width:10%;color:#000; border:0.3px">3</td>
        <td style="width:40%;color:#000; border:0.3px">Gasolina</td>
        <td style="width:15%;color:#000;border:0.3px ">500</td>
        <td style="width:15%;color:#000;border:0.3px"></td>
        <td style="width:10%;color:#000;border:0.3px">1500</td>        
    </tr>
      <tr align="center">
    	<td style="width:10%;color:#000;border:0.3px">2</td>
        <td style="width:10%;color:#000;border:0.3px">1</td>
        <td style="width:40%;color:#000;border:0.3px">Comida</td>
        <td style="width:15%;color:#000;border:0.3px">100</td>
        <td style="width:15%;color:#000;border:0.3px"></td>
        <td style="width:10%;color:#000;border:0.3px">100</td>        
    </tr>
  </table>
  <br/>
 <table cellpadding="0" cellspacing="0" style=" font-size:10px;width:100%; border:0.5px solid #000;">
      <tr align="center">
    	<td style="width:20%;color:#000; font-size:10px;">Proveedor:</td>
        <td style="width:20%;color:#000;font-size:10px;"></td>
        <td style="width:15%;color:#000;font-size:10px;text-align:left;">Clave Proveedor:</td>
        <td style="width:10%;color:#000;font-size:10px;"></td>
        <td style="width:20%;color:#000; font-size:10px;">Total cotizado:</td>
        <td style="width:15%;color:#000;font-size:10px;"></td>        
    </tr>
    </table>
  <table cellpadding="0" cellspacing="0" style=" font-size:10px;width:100%; border:0.5px solid #000;">
    <tr align="center">
    	<td style="width:20%;color:#000; font-size:10px;">Observaciones</td>
        <td style="width:40%;color:#000;font-size:10px;"><?php echo $obs;?></td>        
        <td style="width:20%;color:#000;font-size:10px;text-align:left;">Cheque:</td>
        <td style="width:20%;color:#000;font-size:10px;"><?php echo $cheque;?></td>
    </tr>
    </table>
    <table cellpadding="0" cellspacing="0" style=" font-size:10px;width:100%; border:0.5px solid #000;">
    <tr align="center">
    	<td style="width:15%;color:#000; font-size:10px;">elaborado por:</td>
        <td style="width:15%;color:#000;font-size:10px;"><?php echo $elaborado;?></td>        
        <td style="width:15%;color:#000;font-size:10px;text-align:left;">Seleccionado por:</td>
        <td style="width:20%;color:#000;font-size:10px;"><?php echo $selec;?></td>
        <td style="width:15%;color:#000;font-size:10px;text-align:left;">Fecha entrega:</td>
        <td style="width:20%;color:#000;font-size:10px;">2015-03-30 13:46:45</td>
    </tr>
    </table>
    	  <?php
$html=ob_get_clean();
$path='../docs/';
$filename="generador.pdf";
$orientar="portrait";

$topdf=new HTML2PDF($orientar,array($mmCartaW,$mmCartaH),'es');
$topdf->writeHTML($html);
$topdf->Output();
?>
   	   
<?php }else{
	echo $error;
}?>