<?php session_start();
setlocale(LC_ALL,"");
setlocale(LC_ALL,"es_MX");
include_once("datos.php");
require_once('../clases/html2pdf.class.php');
include_once("func_form.php");
$emp=$_SESSION["id_empresa"];

if (isset($_GET["cot"])) {
    $id = $_GET["cot"];
}
//funciones para convertir px->mm
function mmtopx($d) {
    $fc = 96 / 25.4;
    $n = $d * $fc;
    return $n . "px";
}

function pxtomm($d) {
    $fc = 96 / 25.4;
    $n = $d / $fc;
    return $n . "mm";
}

function checkmark() {
    $url = "http://" . $_SERVER["HTTP_HOST"] . "/img/checkmark.png";
    $s = '<img src="' . $url . '" style="height:10px;" />';
    return $s;
}

function folio($digitos, $folio) {
    $usado = strlen($folio);
    $salida = "";
    for ($i = 0; $i < ($digitos - $usado); $i++) {
        $salida.="0";
    }
    $salida.=$folio;
    return $salida;
}

//tamaño carta alto:279.4 ancho:215.9
$heightCarta = 960;
$widthCarta = 660;
$celdas = 12;
$widthCell = $widthCarta / $celdas;
$mmCartaH = pxtomm($heightCarta);
$mmCartaW = pxtomm($widthCarta);
ob_start();

try {
    $bd = new PDO($dsnw, $userw, $passw, $optPDO);
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
} catch (PDOException $err) {
    echo $err->getMessage();
}
$bd = NULL;

//para saber los articulos y paquetes
try {
    $bd = new PDO($dsnw, $userw, $passw, $optPDO);
    $sql = "SELECT
        t1.*,
        t2.nombre
    FROM cotizaciones_articulos t1
    LEFT JOIN articulos t2 ON t1.id_articulo=t2.id_articulo
    WHERE t1.id_cotizacion=$id;";
    $res = $bd->query($sql);
    $articulos = array();
    foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $d) {
        if ($d["id_articulo"] != "") {
            $art = $d["id_item"];
            unset($d["id_item"]);
            $articulos[$art] = $d;
        } else {
            $art = $d["id_item"];
            unset($d["id_item"]);
            $articulos[$art] = $d;
            $paq = $d["id_paquete"];

            //nombre del paquete
            $sql = "SELECT nombre FROM paquetes WHERE id_paquete=$paq;";
            $res3 = $bd->query($sql);
            $res3 = $res3->fetchAll(PDO::FETCH_ASSOC);
            $articulos[$art]["nombre"] = "PAQ. " . $res3[0]["nombre"];

            $sql = "SELECT
                t1.cantidad,
                t2.nombre
            FROM paquetes_articulos t1
            INNER JOIN articulos t2 ON t1.id_articulo=t2.id_articulo
            WHERE id_paquete=$paq AND t2.perece=0;";
            $res2 = $bd->query($sql);

            foreach ($res2->fetchAll(PDO::FETCH_ASSOC) as $dd) {
                $dd["precio"] = "";
                $dd["total"] = "";
                $dd["nombre"] = $dd["cantidad"] . " " . $dd["nombre"];
                $dd["cantidad"] = "";
                $articulos[] = $dd;
            }
        }
    }
} catch (PDOException $err) {
    echo $err->getMessage();
}

 $portada ='
 <page>
 <body>
 <table style="width:100%;border-bottom:'.pxtomm(2).'solid #000;" cellpadding="0" cellspacing="0" >
    <tr>
		 <td style="width:55%; text-align:left"><img src="../img/logo.png" width="100" height="70" style="width:200px;" /></td>
         <td style="width:30%; text-align:left; padding-bottom:5px;">         	
            <p style="margin:0;text-align:center;font-size:16px;">"Logo of Group"</p>
         </td>
    </tr>
</table>
<br/>
<br/>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:20px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>CTA Cancun DMC <br/>
Activities propositions for groups <br/>
“Name of the group”
</strong></td>
        </tr>       
    </table> 
<table style="width:100%;">
<tr>
<td width="100%" style="text-align:center;"><img src="../img/CTA.jpg" width="600" height="400"/></td>
</tr>
</table>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:18px;width:100%;">
        <tr>
            <td style="width:100%;text-align:right;"><strong>
            “Name of the client”    
</strong></td>
        </tr>       
    </table>
    <br/>
    <br/>
    <br/>
    </body>
    </page>';
    

$html = '
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
<page>
<table style="width:100%;border-bottom:'.pxtomm(2).'solid #000;" cellpadding="0" cellspacing="0" >
    <tr>
		 <td style="width:55%; text-align:left"><img src="../img/logo.png" style="width:200px;" /></td>
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
    	<td style="width:20%;"><strong>Attention:</strong></td>
        <td style="width:30%;">'.$cliente.'</td>
      <td style="width:20%;"><strong>Program:</strong></td>
      <td style="width:30%;">'. $nombreEvento.'</td>
    </tr>
    <tr>
    	<td style="width:20%;"><strong>Charge:</strong></td>
        <td style="width:30%;"> </td>
        <td style="width:20%;"><strong>Date:</strong></td>
        <td style="width:30%;">'.$fechaEve.'</td>
    </tr>
     <tr>
    	<td style="width:20%;"><strong>Company:</strong></td>
        <td style="width:30%;">'.$comCliente.' </td>
        <td style="width:20%;"><strong>Participants:</strong></td>
        <td style="width:30%;"></td>
    </tr>
     <tr>
    	<td style="width:20%;"><strong>E-mail:</strong></td>
        <td style="width:30%;">'.$emailCliente.'</td>
        <td style="width:20%;"><strong>Hotel:</strong></td>
        <td style="width:30%;">'.$domicilio.'</td>
    </tr>
    <tr>
    	<td style="width:20%;"><strong>Ph:</strong></td>
        <td style="width:30%;">'. $telCliente.' </td>
        <td style="width:20%;"><strong>FAX:</strong></td>
        <td style="width:30%;">'.$telCliente.'</td>
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
    </tr>';
    
	$total=0;
	foreach($articulos as $id=>$d){ 
	$total+=$d["total"];

   $html.=' <tr>
        <td style="width:15%;text-align:center;">'. $d["cantidad"] .'</td>
        <td style="width:55%;">'.$d["nombre"] .'</td>
        <td style="width:15%;text-align:center;">'. $d["precio"] .'</td>
        <td style="width:15%;text-align:center;">'. $d["total"] .'</td>
    </tr>';
} 
$html.='
	<tr>
        <td style="width:15%;text-align:center;"> </td>
        <td style="width:55%;"> </td>
        <td style="width:15%;text-align:right;">Total:</td>
        <td style="width:15%;text-align:center;">'. $total.'</td>
    </tr>
</table>
</page>';


switch ($paq)
{
	case 1:
	{
		$html2='
        <page>
        <body>
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XCARET BY THE DAY</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/xcaret.jpg" width="229" height="142"/></td>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
“Nature’s sacred paradise”. It is the world’s most famous eco-archaeological park with over 53 activities to enjoy. Visit the Mayan Village, the aviary, the butterfly pavilion, bat caves, the botanical garden or the coral reef aquarium; snorkel through the underground rivers and, optionally, swim with the dolphins and do much more. What during the day is a jungle adventure park turns into mysticism, traditions, legends and history with the Xcaret Mexico Spectacular celebration show at night.</td>
</tr>
</table>

<table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:50%;text-align:left;"><u><strong>Tour Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:50%;">&nbsp; &nbsp;CTA Cancun supervision (1 staff)</td>
            <td style="width:50%;text-align:left;">&nbsp; &nbsp;More than 20 different activities</td>
        </tr>
        <tr>
            <td style="width:50%;text-align:left;">&nbsp; &nbsp;Round trip transportation on a deluxe motor coach, with AC, and toilettes.</td>
            <td style="width:50%;text-align:left;">&nbsp; &nbsp;Buffet lunch, unlimited; water, fresh fruit flavored water & coffee during lunch.</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp; &nbsp;Cold bottled water and disposable moist towels on board the motor coach.</td>
            <td style="width:15%;text-align:left;">&nbsp; &nbsp;Snorkel equipment and showers </td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp; &nbsp;Entrance fee to Xcaret Park and parking.</td>
         <td style="width:15%;text-align:left;">&nbsp; &nbsp;Lockers</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp; &nbsp;Prefer Service at Plus Area</td>
         <td style="width:15%;text-align:left;"><strong>&nbsp; &nbsp;Xcaret “Mexico Spectacular “ Show </strong></td>
        </tr>
        </table>
        <br/>
 <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Not included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;">&nbsp; &nbsp;Towels</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp; &nbsp;Swimming with dolphins and other activities.</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp; &nbsp;Gratuities US $ 5.00 per person</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp; &nbsp;Taxes</td>
        </tr>
        </table>
        <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><strong>Cost</strong></td>
            </tr>
            <tr>
                <td style="width:20%;">Plus program</td>
                 <td style="width:20%;">$137.00 us per person</td>
                  <td style="width:20%;">Minimum 35 pax</td>
              </tr>
        </table>
<br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><u><strong>SUGGESTED ITINERARY:</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">08:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from the hotel:</td>
                 <td style="width:20%;">08:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival in Xcaret:</td>
                 <td style="width:20%;">10:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Tour in Xcaret:</td>
                 <td style="width:20%;">10:45 A.M.-12:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Lunch:</td>
                 <td style="width:20%;">12:30 P.M.-01:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Free time:</td>
                 <td style="width:20%;">01:30 P.M.-06:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Show:</td>
                 <td style="width:20%;">07:00 P.M.-09:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from Xcaret:</td>
                 <td style="width:20%;">09:30 P.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Arrival at the hotel:</td>
                 <td style="width:20%;">11:00 P.M.</td>
        </tr>
        </table>
        <br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:50%;text-align:left;"><strong>Remarks:</strong></td>
        </tr>
        <tr>
            <td style="width:50%;">&nbsp; &nbsp;Comfortable clothing is suggested: bermudas, T-shirts, Tennis Shoes, Sunglasses, swimming suit and Cameras, biodegradable Suntan lotion I </td>
        </tr>
        <tr>
            <td style="width:50%;text-align:left;">&nbsp; &nbsp;Local Arts & Crafts offered at the stores; bring cash </td>
        </tr>
        <tr>
            <td style="width:50%;text-align:left;">&nbsp; &nbsp;Schedule is suggested and will be adapted according to the group needs. </td>
        </tr>
    </table>
    </body>
    </page>';
	}
	break;	
	case 2:
	{
		$html2='
<page backbottom="15px">
<style>
    span
    {
        display:inline-block;
        padding:10px;
    }
    h1
    {
        font-size:20px;
    }
    .spacer
    {
        display:inline-block;
        height:1px;
    }
    .celda_color
    {
        background-color:#066;
        color:#FFF;
    }
    .celda_negro
    {
        width:100%;
        height:10%;
        background-color:#000;
    }
    .td_tabla
    {
        border:hidden;
    }
    .table_borde
    {
        border:1px solid #000;
    }
</style>
<table style="width:100%;" class="celda_color">
            <tr>
                <td style="width:100%; text-align:center"><strong>XPLOR</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/xplor.jpg" width="162" height="133"/></td>
<td style="width:70%;text-align:justify;  font-size:12px;">
            An adventure that began 65 million years ago. Xplor... Beyond the surface 65 million years ago, when dinosaurs dwelled the planet, a massive asteroid struck the Yucatan peninsula, bringing end to an era; dinosaurs perished and gave rise to new forms of life. Nonetheless, beneath the surface, even more amazing changes were taking place. Rain filled the basin formed by the crater, and found its way to the subsurface, where it gave life to an endless adventure scenario, and capriciously sculpted a world of caverns that today open up at last and ask to be discovered. Get ready to explore the 4 elements through the most intense adventure activities gathered in the same place:
        </td>
        </tr>
        </table>
        <br/>
         <table style="width:100%;">
        <tr>
            <td style="width:100%; text-align:justify; font-size:12px;"> • Drive unstoppable amphibious vehicles and daunt the borders between jungle, water, rocks and grottos.
            </td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify; font-size:12px;">• Swim through the most spectacular mysterious routes; surrounded by amazing stalactites and stalagmites.
            </td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify; font-size:12px;">• Set sail on a raft, and paddle with your hands over crystal-clear waters among age-old rocky formations.
            </td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify; font-size:12px;">• Fly through over 2 miles of freedom, and height on our 11 zip-lines.
            </td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify; font-size:12px;">• Keep the feat at top levels, with a nutritious and light buffet specially designed the recharge your energy.
            </td>
        </tr>
    </table>
         <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:70%;text-align:left;"><u><strong>Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:70%;">• Supervision by CTA Cancún attendant.</td>
        </tr>
        <tr>
            <td style="width:70%;text-align:left;">• Round Trip Transportation in a luxury bus with air conditioned</td>
        </tr>
        <tr>
            <td style="width:70%;text-align:left;">• Cold bottled water and disposable moist towels (transportation only)</td>
        </tr>
        <tr>
            <td style="width:70%;text-align:left;">• Buffet with drinks (no alcohol) showers and bathrooms</td>
        </tr>
        <tr>
            <td style="width:70%;text-align:left;">• Locker deposit $20.00usd per person (refundable at the end of the day)</td>
        </tr>
        <tr>
            <td style="width:70%;text-align:left;">• All equipment to do the activities.</td>
        </tr>
        <tr>
            <td style="width:70%;text-align:left;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width:70%;text-align:left;"><u><strong>Not included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:70%;text-align:left;">• Swimming with Dolphins and other activities.</td>
        </tr>
        <tr>
            <td style="width:70%;text-align:left;">• Gratuities US $ 5.00 per person</td>
        </tr>
        <tr>
            <td style="width:70%;text-align:left;">• Taxes</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:50%;text-align:left;"><u><strong>Suggested itinerary:</strong></u></td>
        </tr>
        <tr>
            <td style="width:25%;">Participants ready at the Hotel lobby:</td>
            <td style="width:25%;">08:15 A.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Departure from the hotel</td>
            <td style="width:25%;">08:30 A.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Arrival at Xplor:</td>
            <td style="width:25%;text-align:left;">10:45 A.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Lunch:</td>
            <td style="width:25%;text-align:left;">12:30 P.M.-01:30 P.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Free time:</td>
            <td style="width:25%;text-align:left;">01:30 P.M.-04:30 P.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Departure from Xplor:</td>
            <td style="width:25%;text-align:left;">05:00 P.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Arrival at the hotel:</td>
            <td style="width:25%;text-align:left;">06:30 P.M.</td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:20%;text-align:left;"><strong>***Cost low season</strong></td>
        </tr>
            </table>
            <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
                <tr>
            <td style="width:25%;"border="0.5">All Inclusive package</td>
            <td style="width:25%;"border="0.5">US $128.00</td>
            <td style="width:25%;"border="0.5">Per person</td>
            <td style="width:25%;"border="0.5">Minimum 35 pax</td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;text-align:left;"><strong>Jan 04 -  Apr 13</strong></td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;"><strong>Apr 27- Jun 31</strong></td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;"><strong>Aug 17-Dec 24</strong></td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;text-align:left;"><strong>***Cost High season</strong></td>
        </tr>
    </table>
        <table cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;" border="0.5">All Inclusive package</td>
            <td style="width:25%;"border="0.5">US $145.00</td>
            <td style="width:25%;"border="0.5">Per person</td>
            <td style="width:25%;"border="0.5">Minimum 35 pax</td>
        </tr>
    </table>
            <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;text-align:left;"><strong>Jul. 01 - Aug 16. 2015</strong></td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;"><strong>Apr 14 - Apr 26, 2015</strong></td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;"><strong>Jul 01 - Aug 16, 2015</strong></td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;"><strong>Dec 25 - Jan 03 2016</strong></td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;text-align:left;"><strong>Remarks:</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:95%;text-align:left;">• Comfortable clothing is suggested: Bermudas, T-shirts, Tennis Shoes, Sunglasses, swimming suit, and Cameras.</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">• Local Arts & Crafts offered at the stores; bring cash</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">• Schedule is suggested and will be adapted according to the group needs.</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;"><u>• Mandatory to use only biodegradable sun/tan lotion.</u></td>
        </tr>
    </table>
            </page>';
	}
	break;	
}


	
$path = '../docs/';
$filename = "generador.pdf";
//$filename=$_POST["nombre"].".pdf";
//
//configurar la pagina
//$orientar=$_POST["orientar"];
$orientar="portrait";
$topdf = new HTML2PDF($orientar, array($mmCartaW, $mmCartaH), 'es');
$topdf->writeHTML($portada);
$topdf->writeHTML($html);
$topdf->writeHTML($html2);
//$topdf->writeHTML($html2);
//$topdf->writeHTML($actividad);
$topdf->Output();
//$path.$filename,'F'
//echo "http://".$_SERVER['HTTP_HOST']."/docs/".$filename;

?>