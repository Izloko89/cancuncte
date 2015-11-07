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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XCARET AT NIGHT /SHOW DINE</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/xcaret_night.jpg" width="211" height="142"/></td>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">“Nature’s sacred paradise”. It is the most famous echo-archaeological park of the world, with more than 53 activities to enjoy. Visit the Mayan Town, the aviary, the butterfly pavilion, bat caves, the botanical garden, the coral reef aquarium, snorkel through the underground rivers, optional swim with the dolphins and much more. What during the day is a jungle adventure park turns into mysticism, traditions, legends, and history with the Xcaret Spectacular Mexico celebration show at night.
</td>
</tr>
</table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>CTA Cancun supervision </li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Round trip transportation</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Cold bottled water, disposable moist towels (only transportation)</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Entrance fee to Xcaret eco-archaeological park</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>3 courses dinner with beverages, (menu to be set by the group prior the event)</li></td>
        </tr>        
    </table>
    <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:55%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Swimming with dolphins and activities with additional cost</li></td>
        </tr>
        <tr>
            <td style="width:55%;text-align:left;"><li>Gratuities US $ 5.00 per person</li></td>
        </tr>        
    </table>   
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><u><strong>*** Tour Cost</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">Private Tour in motor coach minimum 35.</td>
                 <td style="width:20%;">US $ 135.00</td>
                  <td style="width:20%;">Per person</td>
        </tr>
        </table>
        <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><u><strong>SUGGESTED ITINERARY (SUMMER TIME SCHEDULE):</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">01:00 PM</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from the hotel</td>
                 <td style="width:20%;">01:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at Xcaret </td>
                 <td style="width:20%;">3:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Welcome in Xcaret</td>
                 <td style="width:20%;">3:00 P.M. – 3:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Free time</td>
                 <td style="width:20%;">3:30 P.M. - 6:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Night Show</td>
                 <td style="width:20%;">7:00 P.M. – 9:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure to the hotel</td>
                 <td style="width:20%;">9:30 P.M.</td>
        </tr> 
        <tr>
                <td style="width:20%;">Arrival at the hotel</td>
                 <td style="width:20%;">: 11:00 P.M.</td>
        </tr>       
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><u><strong>SUGGESTED ITINERARY (WINTER TIME SCHEDULE):</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">01:00 PM</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from the hotel</td>
                 <td style="width:20%;">01:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at Xcaret </td>
                 <td style="width:20%;">3:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Welcome in Xcaret</td>
                 <td style="width:20%;">3:00 P.M. – 3:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Free time</td>
                 <td style="width:20%;">03:30 P.M. – 05:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Night Show</td>
                 <td style="width:20%;">06:00 P.M. – 08:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure to the hotel</td>
                 <td style="width:20%;">08:30 P.M.</td>
        </tr> 
        <tr>
                <td style="width:20%;">Arrival at the hotel</td>
                 <td style="width:20%;">10:00 P.M.</td>
        </tr>       
        </table>
        <br/>         
        <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Recommendations:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Use comfortable shoes, casual clothes, bring towel and swimming suit, hat, sunglasses, and camera.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Local Arts & Crafts offered at the stores; bring cash </li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li><u>Only Bio-degradable sun lotion this authorized in Xcaret (also available in its stores)</u></li></td>
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