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
<page>
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>Xenotes Oasis Maya</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/xenotes_maya.jpg" width="211" height="142"/></td>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
	Xenotes Oasis Maya Tour is a unique where you will live nature at its best and enjoy incredible adventures.
<br/>
The adventure takes place in four different kinds of cenotes protected by aluxes where you will be able to practice different activities such as Kayak, Zip-lines, Inner Tubes, Rappel and Snorkel. A personalized tour where our adventurers will be led by experts on this virgin territory and instructed on activities, history, anecdotes and legends of the region.<br/>
<br/>
Be amazed by the mysticism that surrounds the cenotes in Riviera Maya and learn about the legend of the Alux that guards each cenote (aluxes are small beings who must be asked permission before entering their domains).
</td>
</tr>
</table>

<table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:50%;">If you love nature then you cannot miss the most complete tour in Cancun and Riviera Maya, the Xenotes Oasis Maya Tour operated by Experiencias Xcaret.</td>
        </tr>
        <tr>
            <td style="width:50%;text-align:left;">Due to its geological composition, the Yucatan Peninsula reacts as a type of sponge, when it rains, by absorbing all the moisture. The water that seeps through the soil begins to dissolve, giving way to caverns that can be partially or completely flooded, and when one of these caverns collapses due to erosion, the cenotes are born.</td>
        </tr>
        </table>
        <br/>
 <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><strong>K’áak’</strong></td>
        </tr>
        <tr>
            <td style="width:75%;text-align:left;">K'áak ´ is an open Xenote that allows underground currents to communicate with the jungle and light. Among its great virtues are its vertical walls and exceptional landscapes. Large quantities of life can be found here and plants surround it; it is the perfect place to interact with nature and enjoy some healthy fun.</td>
        </tr>
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    	<tr>
            <td style="width:15%;text-align:left;"><strong>Ha'</strong></td>
        </tr>
        <tr>
            <td style="width:25%;"><img src="../img/activities/ha.jpg" width="211" height="142"/></td>
            <td style="width:75%;text-align:left;">Ha’ is a cavern cenote, home to beautiful aquatic fauna and where you will find beautiful rock formations. Here you will be able to enjoy the unique landscape of the underwater world, surrounded by jungle and peace.</td>
        </tr>
    </table>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px; width:95%;">
        <tr>
            <td style="width:15%;text-align:left;"><strong>Lu´um</strong></td>
        </tr>
        <tr>
            <td style="width:75%;text-align:left;">Lu’um is a semi-open cenote. It connects to the aquifer through tunnels and caves. The flow of water is horizontal and the amount of time the water stays put is usually short. The Xenote is still semi-closed, thus it is considered young.</td>
            <td style="width:25%;"><img src="../img/activities/lum.jpg" width="211" height="142"/></td>
        </tr>
    </table>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;"><img src="../img/activities/lik.jpg" width="211" height="142"/></td>
            <td style="width:75%;text-align:left;">Iik’ is an advanced age cenote known as ancient cenote. This type of Xenote is blocked from the watertable due to the collapsed roof or walls and sediments, which make the exchange with underground currents restricted and the flow of water a lot slower.</td>
        </tr>
    </table>


         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;">&nbsp;• Transportation service from the comfort of your hotel with a specialized guide and tour operator</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp;• Welcome coffee, hot chocolate and sweet breads as well as at every Xenote exit, except the one where lunch is served</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp;• Non-alcoholic beverages (water and soft drinks) and seasonal fruits en route</td>
        </tr>
        <tr>
            <td style="width:55%;">&nbsp; • Glam Picnic: energizing selection including fusilli-vegetable soup, fresh bar with premium quality cheeses and deli meats, a variety of rustic breads, dressings, fresh salads, water, coffee, wine and beer </td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp; • Equipment: life jacket, snorkel equipment, rappel gear and/ or inner tube</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp; • Restrooms, dressing rooms and one towel</td>
        </tr>
        </table>
        <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><u><strong>Itinerary:</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">09:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from the hotel:</td>
                 <td style="width:20%;">09:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at Xenotes Oasis Maya:</td>
                 <td style="width:20%;">11:30 A.M.-12:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Lunch:</td>
                 <td style="width:20%;">12:30 P.M.-01:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from Xenotes Oasis Maya</td>
                 <td style="width:20%;">06:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at the hotel:</td>
                 <td style="width:20%;">08:00 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><u><strong>Duration:</strong></u> 9 hours aproximately including transfers.</td><br/>
            </tr>
            <tr>
                <td style="width:20%;"><strong>Tour available Monday through Saturday.</strong></td>
        </tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><u><strong>Cost</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">All Inclusive</td>
                 <td style="width:20%;">US $119.00</td>
                  <td style="width:20%;">Per person, plus taxes</td>
                  <td style="width:20%;">Minimum 32</td>
        </tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><u><strong>Recommendations:</strong></u></td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • We recommend that you bring aqua socks or water shoes and a towel.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Take care of nature, enjoy it and learn from it.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Avoid using makeup or chemical repellents that affect the ecosystem of the cenotes, use only chemical-free sunblock.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Shower before entering the Xenote to protect the habitat.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • The use of life jackets is required for water activities.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Avoid leaving the beaten track to avoid an incident with the fauna or flora of the place.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • If you see an animal avoid touching or feeding it, remember they are in their natural habitat.</td>
        	</tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><u><strong>Notes:</strong></u></td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Children ages 6 and older.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • If you have heart or lung ailments, asthma, back problems, diabetes, hypertension or you are pregnant, we do not recommend activities in this tour for you.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • An adult must accompany children at all times.</td>
        	</tr>
        </table>
        </page>

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