<?php session_start();
setlocale(LC_ALL,"");
setlocale(LC_ALL,"es_MX");
include_once("datos.php");
require_once('../clases/html2pdf.class.php');
include_once("func_form.php");
$emp=$_SESSION["id_empresa"];

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
		$sql="SELECT
			t1.id_evento,
			t1.fechaevento,
			t1.fechamontaje,
			t1.fechadesmont,
			t1.id_cliente,
			t2.nombre,
			t3.direccion,
			t3.colonia,
			t3.ciudad,
			t3.estado,
			t3.cp,
			t3.telefono
		FROM eventos t1
		LEFT JOIN clientes t2 ON t1.id_cliente=t2.id_cliente
		LEFT JOIN clientes_contacto t3 ON t1.id_cliente=t3.id_cliente
		WHERE id_evento=$eve;";
		$res=$bd->query($sql);
		$res=$res->fetchAll(PDO::FETCH_ASSOC);
		$evento=$res[0];
		$cliente=$evento["nombre"];
		$telCliente=$evento["telefono"];
		$domicilio=$evento["direccion"]." ".$evento["colonia"]." ".$evento["ciudad"]." ".$evento["estado"]." ".$evento["cp"];
		$fechaEve=$evento["fechaevento"];

		//para saber los articulos y paquetes
		$sql="SELECT
			t1.*,
			t2.nombre
		FROM eventos_articulos t1
		LEFT JOIN articulos t2 ON t1.id_articulo=t2.id_articulo
		WHERE t1.id_evento=$eve;";
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
		//para saber el anticipo
		$emp_eve=$emp."_".$eve;
		$sql="SELECT SUM(cantidad) as pagado FROM eventos_pagos WHERE id_evento='$emp_eve';";
		$res=$bd->query($sql);
		$res=$res->fetchAll(PDO::FETCH_ASSOC);
		$pagado=$res[0]["pagado"];
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
<table style="width:100%;" class="celda_color">
	<tr>
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>SNORKEL XTREME</strong></td>
		</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<td width="15%" style="text-align:center;"><img src="../img/activities/snorkel.png" width="198" height="137"/></td>
				<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
				 Experience the best mixture of ocean and jungle adventures as you snorkel the turquoise waters of the Mexican Caribbean surrounded by exotic marine life and fly above the exuberant Mayan jungle canopy.<br/>
                Surprise yourself with Mexico’s biodiversity and snorkel between marine turtles; coral and colorful fish while you enjoy the best sandy beach around.</td>
                </tr>
                 </table>
                  <br/>
                   <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
                <tr>
                	 <td width="100%" style="width:95%;text-align:justify;  font-size:13px;">
                Fly 21 meters (70 feet) above the jungle canopy on a thrilling zip line ride! Test your tenacity as you rappel 21 meters (70 ft) down into the Mayan jungle and explore an underground river in the middle of the jungle.
                <br/>
                Delight yourself by sampling real Mexican cuisine as a perfect finish.
                <br/>
                <br/>
                You will be accompanied at all times by our professional guides, who will share with you the best snorkeling techniques and information regarding the flora and fauna that inhabits these unique ecosystems.
                <br/>
                <br/>
                <strong><u>Tour includes:</u></strong> A/C Transportation, professional bilingual guide, entrance fees, climbing and snorkeling equipment, lunch and beverages, insurance and taxes.
                <br/>
                <br/>
                <strong><u>Duration:</u></strong> Approximately 7 hours from pickup to drop off
            </td>
</tr>
</table>
<br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><strong>Suggested itinerary:</strong></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the hotel lobby:</td>
                 <td style="width:20%;"></td>
        </tr>
         <tr>
                <td style="width:20%;"><p>Departure  from the hotel:</p></td>
                 <td style="width:20%;"></td>
        </tr>
         <tr>
                <td style="width:20%;">Arrival at Snorkel Xtreme:</td>
                 <td style="width:20%;"></td>
        </tr>
         <tr>
                <td style="width:20%;">Lunch:</td>
                 <td style="width:20%;"></td>
        </tr>
         <tr>
                <td style="width:20%;">Departure from Snorkel Xtreme:</td>
                 <td style="width:20%;"></td>
        </tr>         
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong>Technical Data. Three Zip Line circuit:  Zip line height:</strong> 21 mts. (70 ft.) <strong>Rappel height:</strong>21 mts. (70 ft.)</td>
            </tr>
        </table>
            <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Rates Riviera Maya </strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Mayan  Extreme</td>
                 <td style="width:20%;">US $ 110.00 </td>
                  <td style="width:20%;">Per person</td>
        </tr>        
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Rates Cancun  </strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Mayan Extreme </td>
                 <td style="width:20%;">US $ 120.00 </td>
                  <td style="width:20%;">Per person</td>
        </tr>        
    </table>
<br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">           
        <tr>
         <td style="width:100%;"><strong><u>Bring along:</u></strong> Comfortable clothes and footwear, sunglasses and hat, bathing suit, extra T-shirt, towel, only BIODEGRADABLE sunscreen and mosquito repellent, cash (pictures, souvenirs and tips).</td>
        </tr>
        <tr>
                <td style="width:100%;"><strong>Restrictions:  Weight limit:</strong> (135 kg.) 300 lbs. <strong>Size: 44</strong></td>
        </tr>
         <tr>
                <td style="width:20%;"><strong>Important recommendations:</strong> Basic swimming skills required. Prescription goggles available under previous request. This tour is not suitable for people with severe physical or motor handicap, serious heart problems, <strong>pregnant women</strong> or people who are not able to handle moderate physical activity. People under the influence of alcohol or drugs will not be permitted to participate in this tour</td>
        </tr>
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong><u>Frequency:</u></strong></td>
            </tr>
            <tr>
            <td style="width:20%;"><strong>&nbsp;English and Spanish:</strong> Daily</td>
        </tr>
            <tr>
            <td style="width:20%;"><strong>&nbsp;French: </strong>Monday, Friday and Sunday</td>
        </tr>
            <tr>
            <td style="width:20%;"><strong>&nbsp;German:</strong> Tuesday, Thursday and Saturday</td>
        </tr>
        <tr>
            <td style="width:20%;"><strong>&nbsp;Italian:</strong> Friday and Sunday.</td>
        </tr>
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong><u>Remarks:</u></strong></td>
            </tr>
            <tr>
            <td style="width:20%;">All of these activities could be on private basis with a minimum of 12 people as a guarantee per schedule.</td>
        </tr>           
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong>Gratuities suggested</strong> $5.00 USD per pax</td>
            </tr>   
    </table>

<?php }else{
	echo $error;
}?>
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