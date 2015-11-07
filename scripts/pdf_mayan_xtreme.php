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
li{
	/*list-style: none;*/
	background-image: url("img/imprimir.png");
	background-position: left bottom;
	background-repeat: no-repeat;
	padding-left: 15px;
}
ul{
	list-style-image: url('img/BD21304_.GIF');
}
</style>
<table style="width:100%;" class="celda_color">
            <tr>
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>TULUM XTREME</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
    <td width="15%" style="text-align:center;"><img src="../img/activities/mayan_xtreme.jpg" width="211" height="142"/></td>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
    Be part of the nature as you experience the most thrilling adventure ride. Blend yourself in a perfect mixture of adventure & nature. Fly 21 meters (70 feet) above the jungle canopy on a 765 meters (2,525 ft) zip line ride! Test your tenacity as you rappel 21 meters (70 ft) down into the Mayan jungle. Afterwards, enjoy snorkeling through an underground cavern with astounding rock formations. Delight yourself by sampling real Mexican cuisine as a perfect finish.
</td>
</tr>
</table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;">A/C Transportation, professional bilingual guide, entrance fees, climbing and snorkeling equipment, lunch and beverages, insurance and taxes.</td>
        </tr>
    </table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><strong><u>Duration:</u></strong>Approximately 5 hours from pickup to drop off.</td>
        </tr>
    </table>
    <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><u><strong>Suggested itinerary:</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">08:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from the hotel:</td>
                 <td style="width:20%;">09:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at Mayan Xtreme:</td>
                 <td style="width:20%;">10:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Guided tour at Mayan Xtreme:</td>
                 <td style="width:20%;">10:45 A.M.-11:45 A:M</td>
        </tr>
        <tr>
                <td style="width:20%;">Lunch:</td>
                 <td style="width:20%;">11:45 A:M.-12:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from Mayan Xtreme:</td>
                 <td style="width:20%;">12:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from Tulum:</td>
                 <td style="width:20%;">02:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at hotel:</td>
                 <td style="width:20%;">04:15 P.M.</td>
        </tr>
        </table>
         <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><strong>Operation Days:</strong></td>
        	</tr>
        	<tr>
        		<td style="width:100%;"><li><strong>English and Spanish:</strong> Daily</li></td>
        	</tr>
            <tr>
                <td style="width:100%;"><li><strong>French:</strong>  Monday, Wednesday and Friday</li></td>
            </tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><strong>Rates Riviera Maya</strong></td>
        </tr>
        <tr>
            <td style="width:55%;">Tulum Extreme</td>
            <td style="width:15%;text-align:left;">US $ 100.00</td>
             <td style="width:15%;text-align:left;">Per person</td>
        </tr>
    </table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><strong>Rates Cancún</strong></td>
        </tr>
        <tr>
            <td style="width:55%;">Tulum Extreme</td>
            <td style="width:15%;text-align:left;">US $ 110.00</td>
             <td style="width:15%;text-align:left;">Per person</td>
        </tr>
    </table>
    <br/>
     <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><strong>Bring along:</strong> Comfortable clothes and footwear, water shoes, sunglasses and hat, bathing suit, extra T-shirt, towel, only<strong> BIODEGRADABLE</strong> sunscreen and mosquito repellent, cash (pictures, souvenirs and tips).</td>
        </tr>
        <tr>
            <td style="width:55%;"></td>
        </tr>
        <tr>
            <td style="width:55%;"><strong>Restrictions: Weight limit: </strong>(135 kg.) 300 lbs. <strong>Size: 44</strong></td>
        </tr>
    </table>
    <br/>
    <table style="width:100%;">
        <tr>
            <td width="76%" style="width:65%;text-align:justify;  font-size:13px;"><strong>Important recommendations:</strong>
                 Basic swimming skills required. Prescription goggles available under previous request. This tour is not suitable for people with severe physical or motor handicap, serious heart problems, <strong>pregnant women </strong>or people who are not able to handle moderate physical activity. People under the influence of alcohol or drugs will not be permitted to participate in this tour.
            </td>
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