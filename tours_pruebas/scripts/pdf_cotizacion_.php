<?php session_start();
setlocale(LC_ALL,"");
setlocale(LC_ALL,"es_MX");
include_once("datos.php");
require_once('../clases/html2pdf.class.php');
include_once("func_form.php");
$emp=$_SESSION["id_empresa"];

if(isset($_GET["cot"])){
	$id=$_GET["cot"];
}

//funciones para convertir px->mm
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

try{
	$bd=new PDO($dsnw,$userw,$passw,$optPDO);
	// para saber los datos del cliente
	$sql="SELECT 
		t1.id_cotizacion,
		t1.nombre AS nombreEvento,
		t1.fecha,
		t1.fechaevento,
		t1.fechamontaje,
		t1.fechadesmont,
		t1.id_cliente,
		t2.nombre,
		t2.compania,
		t3.direccion,
		t3.colonia,
		t3.ciudad,
		t3.estado,
		t3.cp,
		t3.telefono,
		t3.email
	FROM cotizaciones t1
	LEFT JOIN clientes t2 ON t1.id_cliente=t2.id_cliente
	LEFT JOIN clientes_contacto t3 ON t1.id_cliente=t3.id_cliente
	WHERE id_cotizacion=$id;";
	$res=$bd->query($sql);
	$res=$res->fetchAll(PDO::FETCH_ASSOC);
	$evento=$res[0];
	$cliente=$evento["nombre"];
	$nombreEvento=$evento["nombreEvento"];
	$comCliente=$evento["compania"];
	$telCliente=$evento["telefono"];
	$domicilio=$evento["direccion"]." ".$evento["colonia"]." ".$evento["ciudad"]." ".$evento["estado"]." ".$evento["cp"];
	$fecha=$evento["fecha"];
	$fechaEve=$evento["fechaevento"];
	$emailCliente=$evento["email"];
}catch(PDOException $err){
	echo $err->getMessage();
}
$bd=NULL;

//para saber los articulos y paquetes
try{
	$bd=new PDO($dsnw,$userw,$passw,$optPDO);
	$sql="SELECT
		t1.*,
		t2.nombre
	FROM cotizaciones_articulos t1
	LEFT JOIN articulos t2 ON t1.id_articulo=t2.id_articulo
	WHERE t1.id_cotizacion=$id;";
	$res=$bd->query($sql);
	$articulos=array();
	foreach($res->fetchAll(PDO::FETCH_ASSOC) as $d){
		if($d["id_articulo"]!=""){
			$art=$d["id_item"];
			unset($d["id_item"]);
			$articulos[$art]=$d;
		}else{
			$art=$d["id_item"];
			unset($d["id_item"]);
			$articulos[$art]=$d;
			$paq=$d["id_paquete"];
			
			//nombre del paquete
			$sql="SELECT nombre FROM paquetes WHERE id_paquete=$paq;";
			$res3=$bd->query($sql);
			$res3=$res3->fetchAll(PDO::FETCH_ASSOC);
			$articulos[$art]["nombre"]="PAQ. ".$res3[0]["nombre"];
			
			$sql="SELECT 
				t1.cantidad,
				t2.nombre
			FROM paquetes_articulos t1
			INNER JOIN articulos t2 ON t1.id_articulo=t2.id_articulo
			WHERE id_paquete=$paq AND t2.perece=0;";
			$res2=$bd->query($sql);
			
			foreach($res2->fetchAll(PDO::FETCH_ASSOC) as $dd){
				$dd["precio"]="";
				$dd["total"]="";
				$dd["nombre"]=$dd["cantidad"]." ".$dd["nombre"];
				$dd["cantidad"]="";
				$articulos[]=$dd;
			}
		}
	}
}catch(PDOException $err){
	echo $err->getMessage();
}

//var_dump($articulos);
?>
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
</style>
<table style="width:100%;border-bottom:<?php echo pxtomm(2); ?> solid #000;" cellpadding="0" cellspacing="0" >
    <tr>
		 <td style="width:55%; text-align:left"><img src="../<?php echo $_SESSION["logo"]; ?>" style="width:200px;" /></td>
         <td style="width:45%; text-align:left; padding-bottom:5px;">
         	<div style="width:100%; text-align:right;font-size:18px;"></div>
            <p style="margin:0;text-align:justify;font-size:16px;">CTA Cancun DMC
Activities propositions for groups
</p>
         </td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" style=" font-size:12px;width:100%; margin-top:10px; padding:0 20px;">
	<tr>
    	<td style="width:20%;">Attention:</td>
        <td style="width:30%;"><?php echo $cliente; ?> </td>
      <td style="width:20%;">Program:</td>
      <td style="width:30%;"><?php echo $nombreEvento;  ?></td>
    </tr>
    <tr>
    	<td style="width:20%;">Charge:</td>
        <td style="width:30%;"><?php  ?> </td>
        <td style="width:20%;">Date:</td>
        <td style="width:30%;"><?php echo $fechaEve; ?></td>
    </tr>
     <tr>
    	<td style="width:20%;">Company:</td>
        <td style="width:30%;"><?php echo $comCliente; ?> </td>
        <td style="width:20%;">Participants:</td>
        <td style="width:30%;"><?php  ?></td>
    </tr>
     <tr>
    	<td style="width:20%;">E-mail:</td>
        <td style="width:30%;"><?php echo $emailCliente; ?> </td>
        <td style="width:20%;">Hotel:</td>
        <td style="width:30%;"><?php echo $domicilio; ?></td>
    </tr>
    <tr>
    	<td style="width:20%;">Ph:</td>
        <td style="width:30%;"><?php echo $telCliente; ?> </td>
        <td style="width:20%;">FAX:</td>
        <td style="width:30%;"><?php echo $telCliente; ?></td>
    </tr>    
</table>
<br>
<div style="width:100%; padding:0 20px; font-size:12px; background-color:#099; color:#FFF; text-align:justify">ALL RATES QUOTED THROUGHOUT THIS PROPOSAL ARE IN USD, NET PRICES NONE COMMISSIONABLE. TRANSPORTATION COST DOES NOT INCLUDE AIRPORT FEES AND TOLL ROADS, ALL PRICES ARE SUBJECT TO 16% FEDERAL TAX AND 5% GRATUITIES/SERVICE CHARGE FOR ALL CTA’S OPERATION STAFF.</div>
<br/>
<br/>
<div style="width:100%; padding:0 20px; font-size:12px; background-color:#099; color:#FFF; text-align:center"><strong>GROUP ACTIVITIES</strong></div>
<div style="width:100%; padding:0 20px; font-size:12px; text-align:justify">All activities and events will be supervised full time by our professionally trained and bilingual (Spanish-English) personnel to guarantee that our clients receive the same quality service and standards they are used to receive from us.</div>
<br />
<div style="width:100%; padding:0 20px; font-size:12px; text-align:justify">All our services use brand new, deluxe vehicles, all of them are equipped with air conditioned, radio communication, cold bottled water and disposable moist towels. In addition, the staff is experienced and well uniformed and the motor coaches are fully equipped with T.V. monitors, microphone systems, W.C., declinable seats, wide aisles and panoramic windows; finally, they are all operated by bilingual staff and/or government licensed tour guides.</div>
<br/>
<div style="width:100%; padding:0 20px; font-size:12px; text-align:justify">**** All activities are quoted in American dollars and the Exchange rate that applies is the rate of the day used by each supplier.</div>
<table border="1" cellspacing="-0.5" cellpadding="1" style="width:100%;font-size:10px;margin-top:5px;">
	<tr align="center">
    	<th style="width:15%;">CANT.</th>
        <th style="width:55%;">CONCEPTO</th>
        <th style="width:15%;">P.U.</th>
        <th style="width:15%;">IMPORTE</th>
    </tr>
<?php 
	$total=0;
	foreach($articulos as $id=>$d){ 
	$total+=$d["total"];
?>
    <tr>
        <td style="width:15%;text-align:center;"><?php echo $d["cantidad"] ?></td>
        <td style="width:55%;"><?php echo $d["nombre"] ?></td>
        <td style="width:15%;text-align:center;"><?php echo $d["precio"] ?></td>
        <td style="width:15%;text-align:center;"><?php echo $d["total"] ?></td>
    </tr>
<?php } ?>
	<tr>
        <td style="width:15%;text-align:center;"> </td>
        <td style="width:55%;"> </td>
        <td style="width:15%;text-align:right;">Total:</td>
        <td style="width:15%;text-align:center;"><?php echo $total; ?></td>
    </tr>
</table>

<?php
$html=ob_get_clean();
$path='../docs/';
$filename="generador.pdf";
//$filename=$_POST["nombre"].".pdf";

//configurar la pagina
//$orientar=$_POST["orientar"];
$orientar="portrait";

$topdf=new HTML2PDF($orientar,array($mmCartaW,$mmCartaH),'es');
$topdf->writeHTML($html);
$topdf->Output();
//$path.$filename,'F'

//echo "http://".$_SERVER['HTTP_HOST']."/docs/".$filename;

?>