<?php session_start();
setlocale(LC_ALL,"");
setlocale(LC_ALL,"es_MX");
include_once("datos.php");
require_once('../clases/html2pdf.class.php');
include_once("func_form.php");
$emp=$_SESSION["id_empresa"];

if (isset($_GET["id"])) {
    $id = $_GET["id"];
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
        date_format(t1.fecha, '%d/%m/%Y') As fecha,
        t1.fechaevento,
        date_format(t1.fechamontaje,'%d/%m/%Y') As fechamontaje,
        t1.id_cliente,
        t1.hotel,
        t1.participantes,
        t2.nombre,
        t2.compania,
        t3.direccion,
        t3.colonia,
        t3.ciudad,
        t3.estado,
        t3.cp,
        t3.telefono,
        t3.email,
        t4.razonf
    FROM cotizaciones t1
    LEFT JOIN clientes t2 ON t1.id_cliente=t2.id_cliente
    LEFT JOIN clientes_contacto t3 ON t1.id_cliente=t3.id_cliente
    LEFT JOIN clientes_fiscal t4 ON t1.id_cliente=t4.id_cliente
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
    $contactoCliente=$evento["razonf"];
    $hotel=$evento["hotel"];
    $fechaInicio=$evento["fecha"];
    $fechaFin=$evento["fechamontaje"];
    $participantes=$evento["participantes"];
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
    $sql = "SELECT
        path
    FROM cotizacion_imagen
    WHERE id_cotizacion=$id;";
    $img = $bd->query($sql);
	$img = $img->fetchAll(PDO::FETCH_ASSOC);
	if(isset($img[0]["path"]))
	{
		$img[0]["path"] ='<img src="' . $img[0]["path"] . '" style="width:150px;" />';
	}
	else{
		$img[0]["path"] ='';
	}
    $articulos = array();
    foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $d) {
        if ($d["id_articulo"] != "") {
            $art = $d["id_item"];
            unset($d["id_item"]);
            $articulos[$art] = $d;
        } /*else {
            $art = $d["id_item"];
            unset($d["id_item"]);
            $articulos[$art] = $d;
            $paq = $d["id_item"];

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
        }*/
    }
} catch (PDOException $err) {
    echo $err->getMessage();
}

 $portada ='
 <page>
 <body>
 <table style="width:100%;border-bottom:'.pxtomm(2).'solid #000;" cellpadding="0" cellspacing="0" >
    <tr>
         <td style="width:50%; text-align:left"><img src="../img/logo.png" style="width:150px;"  /></td>
         <td style="width:50%; text-align:left; padding-bottom:5px;">
            <p style="margin:0;text-align:right;font-size:16px;">' . $img[0]["path"] . '</p>
         </td>
    </tr>
</table>
<br/>
<br/>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:20px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>CTA Cancun DMC <br/>
Activities propositions for group: &nbsp;
</strong>'.$nombreEvento.'</td>
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
            Client: &nbsp;
</strong>'.$cliente.'</td>
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
.estilotextarea4 {
	background-color:#099;
	border:0;
	padding: 12px 20px 0px 20px;
	}
</style>
<page>
<table style="width:100%;border-bottom:'.pxtomm(2).'solid #000;" cellpadding="0" cellspacing="0" >
    <tr>
         <td style="width:50%; text-align:left"><img src="../img/logo.png" style="width:150px;" /></td>
         <td style="width:50%; text-align:left"></td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" style=" font-size:12px;width:100%; margin-top:10px; padding:0 20px;">
    <tr>
    	<td style="width:20%;"><strong>Attention:&nbsp;</strong></td>
        <td style="width:30%;">'.$cliente.'</td>
      <td style="width:20%;"><strong>Program:&nbsp;</strong></td>
      <td style="width:30%;">'. $nombreEvento.'</td>
    </tr>
    <tr>    	
        <td style="width:20%;"><strong>Date:&nbsp;</strong></td>
        <td style="width:30%;">'.$fechaInicio.'</td>
        <td style="width:20%;"><strong>Date:&nbsp;</strong></td>
        <td style="width:30%;">'.$fechaFin.'</td>
    </tr>
     <tr>
     <td style="width:20%;"><strong>Charge:&nbsp;</strong></td>
        <td style="width:30%;"> </td>
    	<td style="width:20%;"><strong>Company:&nbsp;</strong></td>
        <td style="width:30%;">'.$comCliente.' </td>        
    </tr>
     <tr>
     	<td style="width:20%;"><strong>Participants:&nbsp;</strong></td>
        <td style="width:30%;">'.$participantes.'</td>
    	<td style="width:20%;"><strong>E-mail:&nbsp;</strong></td>
        <td style="width:30%;">'.$emailCliente.'</td>        
    </tr>
    <tr>
    	<td style="width:20%;"><strong>Hotel:&nbsp;</strong></td>
        <td style="width:30%;">'.$hotel.'</td>
    	<td style="width:20%;"><strong>Phone:&nbsp;</strong></td>
        <td style="width:30%;">'. $telCliente.' </td>
    </tr>
</table>
<br>
<textarea class=estilotextarea4 cols="71" rows="5" style=" padding:0 20px; color:#FFF; text-align:justify">ALL RATES QUOTED THROUGHOUT THIS PROPOSAL ARE IN USD, NET PRICES NONE COMMISSIONABLE. TRANSPORTATION COST DOES NOT INCLUDE AIRPORT FEES AND TOLL ROADS, ALL PRICES ARE SUBJECT TO 16% FEDERAL TAX AND 5% GRATUITIES/SERVICE CHARGE FOR ALL CTA’S OPERATION STAFF.</textarea>
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

$path = '../docs/';
$filename = "generador.pdf";
$orientar="portrait";
$topdf = new HTML2PDF($orientar, array($mmCartaW, $mmCartaH), 'es');
$topdf->writeHTML($portada);
$topdf->writeHTML($html);
$html2 ='';
foreach($articulos as $paq)
{
switch ($paq['id_articulo'])
{
    case 15:
    {
        $html2 ='
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
       <!-- <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
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
    <br/>-->
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
$topdf->writeHTML($html2);
    }
    break;
    case 14:
    {
        $html2 ='
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
<<table style="width:100%;">
<tr>
<td style="text-align:center; width:15%;"><img src="../img/activities/xcaret.jpg" width="211" height="142"/></td>
<td style="text-align:justify;  font-size:12px; width:65%;">
“Nature’s sacred paradise”. It is the world’s most famous eco-archaeological park with over 53 activities to enjoy. Visit the Mayan Village, the aviary, the butterfly pavilion, bat caves, the botanical garden or the coral reef aquarium; snorkel through the underground rivers and, optionally, swim with the dolphins and do much more. What during the day is a jungle adventure park turns into mysticism, traditions, legends and history with the Xcaret Mexico Spectacular celebration show at night.</td>
</tr>
</table>

<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
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
            <td style="width:50%;text-align:left;">&nbsp; &nbsp;Cold bottled water and disposable moist towels on board the motor coach.</td>
            <td style="width:50%;text-align:left;">&nbsp; &nbsp;Snorkel equipment and showers </td>
        </tr>
        <tr>
            <td style="width:50%;text-align:left;">&nbsp; &nbsp;Entrance fee to Xcaret Park and parking.</td>
         <td style="width:50%;text-align:left;">&nbsp; &nbsp;Lockers</td>
        </tr>
        <tr>
            <td style="width:50%;text-align:left;">&nbsp; &nbsp;Prefer Service at Plus Area</td>
         <td style="width:50%;text-align:left;"><strong>&nbsp; &nbsp;Xcaret “Mexico Spectacular “ Show </strong></td>
        </tr>
        </table>
        <br/>
 <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp; &nbsp;Towels</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; &nbsp;Swimming with dolphins and other activities.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; &nbsp;Gratuities US $ 5.00 per person</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; &nbsp;Taxes</td>
        </tr>
        </table>
        <!--<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><strong>Cost</strong></td>
            </tr>
            <tr>
                <td style="width:25%; border:0.3px;">Plus program</td>
                 <td style="width:25%; border:0.3px;">$137.00 us per person</td>
                  <td style="width:25%; border:0.3px;">Minimum 35 pax</td>
              </tr>
        </table>-->
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><u><strong>SUGGESTED ITINERARY:</strong></u></td>
            </tr>
            <tr>
                <td style="width:25%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:25%;">08:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Departure from the hotel:</td>
                 <td style="width:25%;">08:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Arrival in Xcaret:</td>
                 <td style="width:25%;">10:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Tour in Xcaret:</td>
                 <td style="width:25%;">10:45 A.M.-12:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Lunch:</td>
                 <td style="width:25%;">12:30 P.M.-01:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Free time:</td>
                 <td style="width:25%;">01:30 P.M.-06:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Show:</td>
                 <td style="width:25%;">07:00 P.M.-09:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Departure from Xcaret:</td>
                 <td style="width:25%;">09:30 P.M.</td>
        </tr>
         <tr>
                <td style="width:25%;">Arrival at the hotel:</td>
                 <td style="width:25%;">11:00 P.M.</td>
        </tr>
        </table>
        <br/>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>Remarks:</strong></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp; &nbsp;Comfortable clothing is suggested: bermudas, T-shirts, Tennis Shoes, Sunglasses, swimming suit and Cameras, biodegradable Suntan lotion I </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; &nbsp;Local Arts & Crafts offered at the stores; bring cash </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; &nbsp;Schedule is suggested and will be adapted according to the group needs. </td>
        </tr>
    </table>
	</body>
    </page>';
$topdf->writeHTML($html2);
    }
    break;
case 16:
	{
		$html2 ='
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
            <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XPLOR FUEGO</strong></td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:90%;">
        <tr>
            <td width="15%" style="text-align:left;"><img src="../img/activities/xplor_fuego.jpg" width="234" height="142"/></td>
            <td style="width:65%;text-align:justify;  font-size:12px;">
                Journey deep into the darkness of the jungle and be part of a new adventure where the night and fire will be your best companions.<br/> Follow your instincts and ignite your life with Xplor Fuego; a challenge in the darkness like you never imagined.</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;">&nbsp;• Enjoy a 3.1-mile circuit to drive with Amphibious Vehicles (only people 18 or older may drive).</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• 580 yards of underground caves to paddle on a Raft.  A nine zip line circuit.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Swim along 430 yards of crystal-clear water in the Stalactite River Swim. Equipment: helmet and life jacket</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:45%;text-align:left;"><u><strong>included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:45%;">&nbsp;• CTA Staff to coordinate the service.</td>
            <td style="width:50%;text-align:left;">&nbsp;• Locker for two</td>
        </tr>
        <tr>
            <td style="width:45%;text-align:left;">&nbsp;• Round trip transportation</td>
            <td style="width:50%;text-align:left;">&nbsp;• Dressing rooms and bathrooms</td>
        </tr>
        <tr>
            <td style="width:45%;">&nbsp;• Admission to Xplor Park Monday through Saturday <br/>from 5:30 a 10:30 p. m.</td>
            <td style="width:50%;text-align:left;">&nbsp;• Resting areas</td>
        </tr>
        <tr>
            <td style="width:45%;text-align:left;">&nbsp;• Equipment (life jacket, helmet, harness, paddles, raft, amphibian vehicle for two).</td>
            <td style="width:50%;text-align:left;">&nbsp;• Buffet and unlimited beverages (coffee, hot chocolate and fresh fruit flavored water).</td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Not included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;">&nbsp;• $20.00 per Person as a deposit for the lockers.</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp;• Gratuities US $ 3.00 per person</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:20%;text-align:left;"><u><strong>Suggested itinerary:</strong></u></td>
        </tr>
        <tr>
            <td style="width:20%;">Participants ready at the hotel lobby:</td>
        </tr>
        <tr>
            <td style="width:20%;">Departure from the hotel:</td>
        </tr>
        <tr>
            <td style="width:20%;">Arrival at Xplor Fuego:</td>
        </tr>
        <tr>
            <td style="width:20%;">Departure from Xplor Fuego:</td>
        </tr>
        <tr>
            <td style="width:20%;">Arrival at the hotel:</td>
        </tr>
    </table>
    <!--<br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;text-align:left;"><u><strong>***Cost Xplor Fuego (fire)</strong></u></td>
        </tr>
    </table>
        <table border="0.3" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;">All Inclusive</td>
            <td style="width:25%;">US $110.00</td>
            <td style="width:25%;">Per person, plus taxes</td>
            <td style="width:25%;">Minimum 35</td>
        </tr>
    </table>-->
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:justify;"><u><strong>Recommendations:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">Wear comfortable beachwear, water shoes, and extra change of clothes, swimsuit and towel. Sunscreen should be free of chemicals to be used in the park. If it contains any of these ingredients, it can be used within the Park: Benzophenone, Etilhexila, Homosalate, Octyl methoxycinnamate, octyl salicylate, Octinoxate Oxybenzone Methoxydibenzoylmethane Butyl.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Notes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify">The minimum age to access Xplor is seven years. Children between 7 and 11 years are charged 50% of adult price.</td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify">You must present official ID at the box office of the Park.</td>
        </tr>
    </table>
</page>		';
$topdf->writeHTML($html2);
	}
	break;
case 17:
{
	$html2 ='
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
</style>
<table style="width:100%;" class="celda_color">
            <tr>
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>Xenotes Oasis Maya</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/xenotes_maya.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:12px;">
    Xenotes Oasis Maya Tour is a unique where you will live nature at its best and enjoy incredible adventures.
    <br/>
<br/>
The adventure takes place in four different kinds of cenotes protected by aluxes where you will be able to practice different activities such as Kayak, Zip-lines, Inner Tubes, Rappel and Snorkel. A personalized tour where our adventurers will be led by experts on this virgin territory and instructed on activities, history, anecdotes and legends of the region.<br/>
<br/>
Be amazed by the mysticism that surrounds the cenotes in Riviera Maya and learn about the legend of the Alux that guards each cenote (aluxes are small beings who must be asked permission before entering their domains).
</td>
</tr>
</table>

<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%; text-align:justify;">If you love nature then you cannot miss the most complete tour in Cancun and Riviera Maya, the Xenotes Oasis Maya Tour operated by Experiencias Xcaret.</td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">Due to its geological composition, the Yucatan Peninsula reacts as a type of sponge, when it rains, by absorbing all the moisture. The water that seeps through the soil begins to dissolve, giving way to caverns that can be partially or completely flooded, and when one of these caverns collapses due to erosion, the cenotes are born.</td>
        </tr>
        </table>
        <br/>
 <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>K’áak’</strong></td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">Káak ´ is an open Xenote that allows underground currents to communicate with the jungle and light. Among its great virtues are its vertical walls and exceptional landscapes. Large quantities of life can be found here and plants surround it; it is the perfect place to interact with nature and enjoy some healthy fun.</td>
                </tr>
                </table>
                <br/>
                <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
                <tr>
                <td style="width:25%;text-align:left;"><strong>Ha</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:30%; text-align:center;"><img src="../img/activities/ha.jpg" width="211" height="142"/></td>
            <td style="width:60%;text-align:justify;">Ha’ is a cavern cenote, home to beautiful aquatic fauna and where you will find beautiful rock formations. Here you will be able to enjoy the unique landscape of the underwater world, surrounded by jungle and peace.</td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px; width:100%;">
        <tr>
            <td style="width:20%;text-align:left;"><strong>Lu´um</strong></td>
        </tr>
        <tr>
            <td style="width:60%;text-align:justify;">Lu’um is a semi-open cenote. It connects to the aquifer through tunnels and caves. The flow of water is horizontal and the amount of time the water stays put is usually short. The Xenote is still semi-closed, thus it is considered young.</td>
            <td style="width:30%; text-align:center;"><img src="../img/activities/lum.jpg" width="211" height="142"/></td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:30%; text-align:center;"><img src="../img/activities/lik.jpg" width="211" height="142"/></td>
            <td style="width:60%;text-align:justify;">Iik’ is an advanced age cenote known as ancient cenote. This type of Xenote is blocked from the watertable due to the collapsed roof or walls and sediments, which make the exchange with underground currents restricted and the flow of water a lot slower.</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Transportation service from the comfort of your hotel with a specialized guide and tour operator</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• Welcome coffee, hot chocolate and sweet breads as well as at every Xenote exit, except the one where lunch is served</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• Non-alcoholic beverages (water and soft drinks) and seasonal fruits en route</td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify;">&nbsp;• Glam Picnic: energizing selection including fusilli-vegetable soup, fresh bar with premium quality cheeses and deli meats, a variety of rustic breads, dressings, fresh salads, water, coffee, wine and beer</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• Equipment: life jacket, snorkel equipment, rappel gear and/ or inner tube</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• Restrooms, dressing rooms and one towel</td>
        </tr>
        </table>
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:40%;text-align:justify;"><u><strong>Itinerary:</strong></u></td>
            </tr>
            <tr>
                <td style="width:40%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:25%;">09:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Departure from the hotel:</td>
                 <td style="width:25%;">09:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Arrival at Xenotes Oasis Maya:</td>
                 <td style="width:25%;">11:30 A.M.-12:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Lunch:</td>
                 <td style="width:25%;">12:30 P.M.-01:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Departure from Xenotes Oasis Maya</td>
                 <td style="width:25%;">06:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Arrival at the hotel:</td>
                 <td style="width:25%;">08:00 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><u><strong>Duration:</strong></u> 9 hours aproximately including transfers.</td><br/>
            </tr>
            <tr>
                <td style="width:100%;"><strong>Tour available Monday through Saturday.</strong></td>
        </tr>
        </table>
        <!--<br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:justify;"><u><strong>Cost</strong></u></td>
            </tr>
            <tr>
                <td style="width:25%;" border="0.5px">All Inclusive</td>
                 <td style="width:25%;" border="0.5px">US $119.00</td>
                  <td style="width:25%;" border="0.5px">Per person, plus taxes</td>
                  <td style="width:25%;" border="0.5px">Minimum 32</td>
        </tr>
        </table>-->
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:justify;"><u><strong>Recommendations:</strong></u></td>
        	</tr>
        	<tr>
                <td style="width:100%; text-align:justify;">&nbsp;• We recommend that you bring aqua socks or water shoes and a towel.</td>
        	</tr>
        	<tr>
                <td style="width:100%; text-align:justify;">&nbsp;• Take care of nature, enjoy it and learn from it.</td>
        	</tr>
        	<tr>
                <td style="width:100%; text-align:justify;">&nbsp;• Avoid using makeup or chemical repellents that affect the ecosystem of the cenotes, use only chemical-free sunblock.</td>
        	</tr>
        	<tr>
                <td style="width:100%; text-align:justify;">&nbsp;• Shower before entering the Xenote to protect the habitat.</td>
        	</tr>
        	<tr>
                <td style="width:100%;">&nbsp;• The use of life jackets is required for water activities.</td>
        	</tr>
        	<tr>
                <td style="width:100%; text-align:justify;">&nbsp;• Avoid leaving the beaten track to avoid an incident with the fauna or flora of the place.</td>
        	</tr>
        	<tr>
                <td style="width:100%; text-align:justify;">&nbsp;• If you see an animal avoid touching or feeding it, remember they are in their natural habitat.</td>
        	</tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:justify;"><u><strong>Notes:</strong></u></td>
        	</tr>
        	<tr>
                <td style="width:100%; text-align:justify;">&nbsp;• Children ages 6 and older.</td>
        	</tr>
        	<tr>
                <td style="width:100%; text-align:justify;">&nbsp;• If you have heart or lung ailments, asthma, back problems, diabetes, hypertension or you are pregnant, we do not recommend activities in this tour for you.</td>
        	</tr>
        	<tr>
                <td style="width:100%; text-align:justify;">&nbsp;• An adult must accompany children at all times.</td>
        	</tr>
        </table>
        </page>';
$topdf->writeHTML($html2);
}
break;
case 18:
	{
$html2 ='
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
            <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>CHICHEN ITZA</strong></td>
        </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/chichen_itza.jpg" width="211" height="142"/></td>
            <td style="width:65%;text-align:justify;  font-size:13px;">
                The capital of the Mayan Empire. Experience the fascinating and mystical world of the Mayas,
                considered to be one of the most advanced cultures in America. Your Tour Guide will escort
                you through the city that contains hundreds of structures such as the Pyramid of Kukulcan
                and the Ball Court, which is the largest and best preserved in Mexico; the Cenote of Sacrifice
                was reserved for rituals involving human sacrifice and the rain God.</td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:95%;text-align:left;"><u><strong>Tours Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:95%;">&nbsp;• CTA Cancun supervision</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp;• Private Round trip transportation</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp;• Cold bottled water, disposable moist towels on board the motor coach</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp;• Professional Bilingual Tour guide <strong>(for the first 20 pax)</strong></td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp;• Entrance fee to the archeological zone and guided tour</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp;• Lunch buffet with International and Regional dishes with one drink included (water, soft drink or beer)</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:95%;">
        <tr>
            <td style="width:95%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:95%;">&nbsp;• Beverages in the restaurant (can be added to the master account with
            15% for concept of CTA coordination)</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp;• atuities, recommended - $5.00 USD per person</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp;• Additional tour guide $150.00 USD</td>
        </tr>
    </table>
   <!-- <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:30%;text-align:left;"><u><strong>TOUR COST</strong></u></td>
        </tr>
        <tr>
            <td style="width:30%;" class="table_borde">Tour with shared buffet lunch</td>
            <td style="width:25%;" class="table_borde">US$85.00</td>
            <td style="width:25%;" class="table_borde">per person,</td>
            <td style="width:20%;" class="table_borde">Minimum 35 pax</td>
        </tr>
    </table>-->
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:40%;text-align:left;"><u><strong>ITINERARY:</strong></u></td>
        </tr>
        <tr>
            <td style="width:40%;">Participants ready at the Hotel lobby:</td>
            <td style="width:25%;">8:00 A.M.</td>
        </tr>
        <tr>
            <td style="width:40%;">Departure from the hotel:</td>
            <td style="width:25%;">8:15 A.M.</td>
        </tr>
        <tr>
            <td style="width:40%;">Arrival at Chichen-Itza a:</td>
            <td style="width:25%;">11:15 A.M.</td>
        </tr>
        <tr>
            <td style="width:40%;">Guided Tour</td>
            <td style="width:25%;">01:00 P.M.</td>
        </tr>
        <tr>
            <td style="width:40%;">Lunch at hotel Mayaland</td>
            <td style="width:25%;">01:15 P.M.</td>
        </tr>
        <tr>
            <td style="width:40%;">Free time</td>
            <td style="width:25%;">02:00 P.M.-03:30 P.M.</td>
        </tr>
        <tr>
            <td style="width:40%;">Departure from Chichen-Itza</td>
            <td style="width:25%;">03:45 P.M.</td>
        </tr>
        <tr>
            <td style="width:40%;">Arrival at the hotel</td>
            <td style="width:25%;">07:00 P.M.</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Remarks:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Comfortable clothing is suggested: Bermudas, T-shirts, Tennis Shoes, Sunglasses, and Cameras.</td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• If you want to have swim at the Cenote, bring your swimming suit and towel</td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Suntan lotion is suggested</td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Local Arts & Crafts offered at the stores; bring cash</td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Professional Cameras require a special permission from Federal Authorities; process will take at least 30 days.</td>
        </tr>
    </table>
    </page>';  
$topdf->writeHTML($html2);  
	}
break;
case 1:
	{
$html2 ='
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>PRIVATE CATAMARAN HALF DAY SNORKELING TOUR</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/private_catamaran.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:12px;">
Maybe the most fun and relaxing day you can have in Cancun. We have different private catamarans of different sizes available to charter for the whole group.) Departure from the bay side marina in the morning and relaxing sailing over the sparkling clear, turquoise sea to Isla Mujeres Bay with very friendly staff, open bar on board and music.<br/>
<br/>
We will make the first stop to do incredible guided snorkeling tour in one of the famous coral reefs. During the relaxing sailing, we will make a stop to do spinnaker (swing with the Catamaran front sail high to the sky and jump from there to the sparkling sea; this is made solely in good climate conditions, we do not want you to end-up in Cuba). On the way, personnel will feast you with the "Tequila Celebration" and other cool drinks.</td>
</tr>
</table>

         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• 4 Hour private sailing tour</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Guided snorkeling tour to “Manchones” reef or other reefs depending of the weather conditions.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Open Bar with national drinks, beer on board of the Trimaran.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Snorkel equipment, (new tube)</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Gratuities 15%</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Pier & Federal Reef access fee US $ 10.00 per pax</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Food and beverages in the Beach Club US$25.00 per person + 15% gratuity (basic buffet)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Taxes</td>
        </tr>
    </table>
    <!--<br/>
     <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:50%;text-align:left;"><strong>Price per hour on the Catamaran Minimum 4 hours</strong></td>
        </tr>
        <tr>
            <td style="width:50%;" border="0.5px">Catamarán 36” Capacity 20 pax</td>
            <td style="width:25%; text-align:center;" border="0.5px">US$ 220.00</td>
            <td style="width:25%; text-align:center;" border="0.5px">Per hour, Min 4 hrs.</td>
        </tr>
        <tr>
            <td style="width:50%;" border="0.5px">Catamarán 42” Capacity 40 pax</td>
            <td style="width:25%; text-align:center;" border="0.5px">US$ 340.00</td>
            <td style="width:25%; text-align:center;" border="0.5px">Per hour, Min 4 hrs.</td>
        </tr>
        <tr>
            <td style="width:50%;" border="0.5px">Catamarán 44” Capacity 45 pax</td>
            <td style="width:25%; text-align:center;" border="0.5px">US$ 400 00</td>
            <td style="width:25%; text-align:center;" border="0.5px">Per hour, Min 4 hrs.</td>
        </tr>
        <tr>
            <td style="width:50%;" border="0.5px">Catamarán 58” Capacity 60 pax</td>
            <td style="width:25%; text-align:center;" border="0.5px">US$ 480 00</td>
            <td style="width:25%; text-align:center;" border="0.5px">Per hour, Min 4 hrs.</td>
        </tr>
        <tr>
            <td style="width:50%;" border="0.5px">Catamaran 78” Capacity 100 pax</td>
            <td style="width:25%; text-align:center;" border="0.5px">US$ 570.00</td>
            <td style="width:25%; text-align:center;" border="0.5px">Per hour, Min 4 hrs.</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>Transportation Cancun Hotels - Marina – Cancun Hotels</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:40%;" border="0.5px">Motor coach</td>
            <td style="width:20%;" border="0.5px">US $ 600.00</td>
            <td style="width:20%;" border="0.5px">Per Unit, Round Trip</td>
            <td style="width:20%;" border="0.5px">Up to 45 pax</td>
        </tr>
        <tr>
            <td style="width:40%;" border="0.5px">Vans</td>
            <td style="width:20%;" border="0.5px">US $ 110.00</td>
            <td style="width:20%;" border="0.5px">Per Unit, Round Trip</td>
            <td style="width:20%;" border="0.5px">1-10 pax </td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>Transportation Hotels in Riviera Maya - Marina – Hotels in Riviera Maya</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:40%;" border="0.5px">Motor coach</td>
            <td style="width:20%;" border="0.5px">US $ 900.00</td>
            <td style="width:20%;" border="0.5px">Per Unit, Round Trip</td>
            <td style="width:20%;" border="0.5px">Up to 45 pax</td>
        </tr>
        <tr>
            <td style="width:40%;" border="0.5px">Vans</td>
            <td style="width:20%;" border="0.5px">US $ 110.00</td>
            <td style="width:20%;" border="0.5px">Per Unit, Round Trip</td>
            <td style="width:20%;" border="0.5px">1-10 pax </td>
        </tr>
        </table>-->
        <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px; width:95%;">
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;"><strong>SUGGESTED ITINERARY:</strong></td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Participants ready at the hotel lobby:</td>
                <td width="50%" style="text-align:justify; font-size:12px;">09:10 A.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:13px;">Departure to Marina:</td>
                <td width="50%" style="text-align:justify; font-size:13px;">09:20 A.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:13px;">Arrival at Marina:</td>
                <td width="50%" style="text-align:justify; font-size:13px;">09:45 A.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Catamaran’s departure:</td>
                <td width="50%" style="text-align:justify; font-size:12px;">10:00 A.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Snorkeling time:</td>
                <td width="50%" style="text-align:justify; font-size:12px;">11:00 A.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Return to Cancún, Marina:</td>
                <td width="50%" style="text-align:justify; font-size:12px;">02:00 P.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Arrival at Marina:</td>
                <td width="50%" style="text-align:justify; font-size:12px;">02:30 P.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Arrival at the hotel:</td>
                <td width="50%" style="text-align:justify; font-size:12px;">02:50 P.M.</td>
            </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><u><strong>Recommendations:</strong></u></td>
        	</tr>
        	<tr>
                <td style="width:100%;">&nbsp;• Have swimming suits already on and remember bring towel, comfortable shoes or sandals, sunglasses, camera and maybe extra t-shirt.</td>
        	</tr>
        	<tr>
                <td style="width:100%;">&nbsp;• Use only bio-degradable sun lotion if snorkeling in the coral reef</td>
        	</tr>
        </table>
</page>';  
$topdf->writeHTML($html2);  
	}
break;
case 2:
		{
			$html2 ='
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>JUNGLE TOUR</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/jungle.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:12px;">
Experience the excitement of driving your own two-person speedboat through lagoon and dense mangrove channels, entering to the second largest reef in the world for great snorkeling with multicolored fish and delicate coral formations.</td>
</tr>
</table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Two-persons speedboats.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Snorkeling equipment & life jackets.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Reef access.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Experienced bilingual tour guides</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Bottled water, soft drinks and disposable Moist Towels on board transportation</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Fee to protect the reefs – US $10.00</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Picture of the tour - $10.00 USD each.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Gratuities - $5.00 USD per person.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transportation Hotel – Marina - Hotel</td>
        </tr>
    </table>
    <!--<br/>
     <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;text-align:left;"><strong>Prices:</strong></td>
        </tr>
        <tr>
            <td style="width:33%;" border="0.5px">Per Person</td>
            <td style="width:33%;" border="0.5px">US $ 60.00</td>
            <td style="width:33%;" border="0.5px">2 persons-speed boat</td>
        </tr>
        <tr>
            <td style="width:33%;"border="0.5px">Per speed boat</td>
            <td style="width:33%;"border="0.5px">US $ 120.00</td>
            <td style="width:33%;"border="0.5px">If one person, full charge applies</td>
        </tr>
        </table>-->
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
             <tr>
            <td style="width:50%;text-align:left;"><strong>Transportation Hotel –Marina - Hotel</strong></td>
        </tr>
        <tr>
            <td style="width:50%;" border="0.5px">Van for small groups up to 10 pax </td>
            <td style="width:25%;" border="0.5px">US $ 120.00</td>
            <td style="width:25%;" border="0.5px">Per Unit, Round Trip</td>
        </tr>
    <tr>
            <td style="width:50%;" border="0.5px">Motor coach Up to 45 pax</td>
            <td style="width:25%;" border="0.5px">US $60.00</td>
            <td style="width:25%;" border="0.5px">Per Unit, Round Trip</td>
        </tr>
        </table>
        <br />
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td width="40%" style="text-align:justify;  font-size:12px;"><u><strong>Suggested Itinerary:</strong></u></td>
            </tr>
            <tr>
                <td width="40%" style="text-align:justify; font-size:12px;">Participants ready at the hotel lobby:</td>
                <td width="20%" style="text-align:justify; font-size:12px;">08.00 A.M..</td>
            </tr>
            <tr>
                <td width="40%" style="text-align:justify; font-size:12px;">Departure from the hotel:</td>
                <td width="20%" style="text-align:justify; font-size:12px;">08.10 A.M.</td>
            </tr>
            <tr>
                <td width="40%" style="text-align:justify; font-size:12px;">Estimated arrival time at the Marina:</td>
                <td width="20%" style="text-align:justify; font-size:12px;">08.30 A.M.</td>
            </tr>
            <tr>
                <td width="40%" style="text-align:justify; font-size:12px;">Guided tour:</td>
                <td width="20%" style="text-align:justify; font-size:12px;">09.00 A.M.</td>
            </tr>
            <tr>
                <td width="40%" style="text-align:justify; font-size:12px;">Departure to the Hotel:</td>
                <td width="20%" style="text-align:justify; font-size:12px;">12:00 P.M.</td>
            </tr>
            <tr>
                <td width="40%" style="text-align:justify; font-size:12px;">Arrival at the hotel:</td>
                <td width="20%" style="text-align:justify; font-size:12px;">12:20 P.M.</td>
            </tr>
        </table>
<br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%; text-align:justify;"><u><strong>Remarks:</strong></u></td>
            </tr>
            <tr>
                <td style="width:100%; text-align:justify;">&nbsp;• Schedules may vary according to the season.</td>
            </tr>
            <tr>
                <td style="width:100%; text-align:justify;">&nbsp;• Participants have to be at the Marina 1/2 hour before tour departure.</td>
            </tr>
            <tr>
                <td style="width:100%; text-align:justify;">&nbsp;• Speedboat for two, single person may pay full rate for the boat.</td>
            </tr>
             <tr>
                 <td style="width:100%; text-align:justify;">&nbsp;• Bathing suit, towels, hat, and sunglasses with string (to avoid them flying off the head), sandals, and biodegradable suntan lotion, waterproof or disposable photograph camera recommended.</td>
            </tr>
             <tr>
                 <td style="width:100%; text-align:justify;">&nbsp;• Locker compartments are available for towels or personal belongings.</td>
            </tr>
            <tr>
                <td style="width:100%; text-align:justify;">&nbsp;• Minors fewer than 5 years of age, pregnant women, and persons with back problems are not recommended to use these services.</td>
            </tr>
        </table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:justify;"><strong>*** For all activities Deluxe Box, lunch available/ different options from US$ 10.00 up tp US$ 22.00 per person Printed group’s logo in bag, optional US $2.00
Deluxe Nuts and dry fruit snack bag $10.00/ printed company logo in bag .Optional US $2.00
</strong></td>

        </tr>
        </table>
			</page>';
			$topdf->writeHTML($html2);
		}
break;
case 19:
	{
		$html2='
        <page backbottom="15px">
        <style>
        span{
        display:inline-block;
        padding:10px;
        }
        h1        {
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>TULUM / XEL-HA</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/tulum.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:13px;">A visit to the world’s biggest natural aquarium, the beautiful inlet of Xel-Ha for a great snorkeling time combined with the archaeological site of Tulum, the Mayan walled city by the sea. </td>
</tr>
</table>

        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Tours Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• CTA Cancun supervision</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Round trip transportation</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Cold bottled water, disposable moist towels on board the motor coach</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Professional Bilingual Tour guide for the first 20 pax</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Entrance fee to Xel-Ha park and parking lot.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Buffet lunch  with domestic open bar, ice cream, Snorkel equipment, Lockers,  towels, Showers, life vests, transportation to the river and bags to store your belongings</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Swimming with Dolphins and other activities with additional costs</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• $20.00 per person,  locker deposit (refundable at the end of the day)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Gratuities US $ 5.00 per person</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Additional Guide $100.00usd per tour guide</td>
        </tr>
    </table>
    <!--<br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><u><strong>Tour (4 HOURS)</strong></u></td>
            </tr>
            <tr>
                <td style="width:25%;" border="0.5px">Private Tour </td>
                <td style="width:25%;" border="0.5px">US $ 68.00</td>
                <td style="width:25%;" border="0.5px">Per person</td>
                <td style="width:25%;" border="0.5px">Minimum 35 pax</td>
        </tr>
        </table>-->
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Remarks:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Comfortable clothing is suggested: Bermudas, T-shirts, Tennis Shoes, Sunglasses, swimming suit and Cameras, biodegradable Suntan lotion I </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Local Arts & Crafts offered at the stores; bring cash</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Professional Cameras require a special permission from Federal Authorities; process will take at least 20 days</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Only biodegradable  sun block is allowed</td>
        </tr>
        </table>
    <!--<br/>
    <table border="0" cellpadding="0" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:33%;text-align:left;"><u><strong>Cost:</strong></u></td>
            </tr>
            <tr>
                <td style="width:33%;" border="0.5px">Tour Tulum Xel-Ha all inclusive</td>
                <td style="width:33%;" border="0.5px">US$ 127.00</td>
                <td style="width:33%;" border="0.5px">Per person, min 35</td>
        </tr>
        </table>-->
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:40%;text-align:left;"><u><strong>SUGGESTED ITINERARY:</strong></u></td>
            </tr>
            <tr>
                <td style="width:40%;">Participants ready at the Hotel lobby:</td>
                <td style="width:40%;">8:20 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Motor coaches depart from Hotel</td>
                <td style="width:40%;">8:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Estimated arrival time at Tulum</td>
                <td style="width:40%;">10.30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Explanation and Visit of the Site</td>
                <td style="width:40%;">10.30/11.30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Participants ready to board the coaches</td>
                <td style="width:40%;">11.45 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Departure time to Xel-Ha</td>
                <td style="width:40%;">12.00 noon</td>
        </tr>
        <tr>
                <td style="width:40%;">Estimated arrival time at Xel-Ha</td>
                <td style="width:40%;">12.15 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Free time at Xel-Ha</td>
                <td style="width:40%;">12.15 – 3.15 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Participants ready to board the coaches</td>
                <td style="width:40%;">3.30 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Departure to Cancun </td>
                <td style="width:40%;">3.45 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Estimated arrival time at the Hotel  </td>
                <td style="width:40%;">5.30 P.M.</td>
        </tr>
        </table>
            </page>';
			$topdf->writeHTML($html2);
	}
break;
case 3:
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>PRIVATE TULUM EXPRESS</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/private_tulum.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:12px;">The famous “walled city” is built under a cliff overlooking the turquoise waters of the Caribbean Sea next to white sandy beach, which makes it spectacular to see. Tulum was inhabited during the postclassical period 900-1521 A.C.; its main buildings are the Castle, the Temple of the Descending God, and Temple of the Initial Series. Pure Mayan Architecture and Pre-Hispanic paintings, impressive murals are well preserved in the sample of the Frescoes.
</td>
</tr>
</table>

         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Tours Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• CTA Cancun Coordination</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transportation to the Tulum ruins on deluxe air-conditioned motor coaches or deluxe vans depending of the number of the participants (minimum 8 pax)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Cold bottled water and disposable moist towels on transportation</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Guided Tour with professional bilingual guide</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• “Train” transportation at the Tulum Ruins</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Entrance fee</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Gratuities - $5.00 USD per person></td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Additional tour Guide US$150.00</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Taxes</td>
        </tr>
    </table>
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:40%;text-align:left;"><u><strong>SUGGESTED ITINERARY:</strong></u></td>
            </tr>
            <tr>
                <td style="width:40%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:40%;">8:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Departure to Tulum</td>
                 <td style="width:40%;">8:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Arrival at Tulum</td>
                 <td style="width:40%;"> 10:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Guided tour in Tulum</td>
                 <td style="width:40%;">10:30 A.M-12:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Free time in Tulum</td>
                 <td style="width:40%;">12:30 P.M.- 01:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Departure to Cancún</td>
                 <td style="width:40%;">01:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Arrival at the hotel</td>
                 <td style="width:40%;">02:30 P.M.</td>
        </tr>
        </table>
        <!--<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><u><strong>Tour (4 HOURS)</strong></u></td>
            </tr>
            <tr>
                <td style="width:25%;" border="0.5px">Private Tour </td>
                 <td style="width:25%;" border="0.5px">US $ 68.00</td>
                  <td style="width:25%;" border="0.5px">Per person</td>
                  <td style="width:25%;" border="0.5px">Minimum 35 pax</td>
        </tr>
        </table>-->
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Recommendations:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify;">&nbsp;• Use comfortable shoes, casual clothes, bring towel and swimming suit if you want to have dip in the ocean next to the Tulum ruins, hat, sun classes and camera./li></td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• Bring cash for local handicrafts.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• <u>Only biodegradable  sun block is allowed</u></td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• Professional Cameras require a special permission from Federal Authorities; process will take at least 30 days.</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:justify;"><u><strong>Remarks:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Schedule is suggested and will be adapted according to the group needs.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• Professional Cameras require a special permission from Federal Authorities; process will take at least 30 days.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• Only biodegradable  sun-block is allowed</td>
        </tr>
        </table>
</page>';
$topdf->writeHTML($html2);	
	}
break;
case 20:
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XCARET AT NIGHT /SHOW DINE</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/xcaret_night.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:13px;">“Nature’s sacred paradise”. It is the most famous echo-archaeological park of the world, with more than 53 activities to enjoy. Visit the Mayan Town, the aviary, the butterfly pavilion, bat caves, the botanical garden, the coral reef aquarium, snorkel through the underground rivers, optional swim with the dolphins and much more. What during the day is a jungle adventure park turns into mysticism, traditions, legends, and history with the Xcaret Spectacular Mexico celebration show at night.
</td>
</tr>
</table>

         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">CTA Cancun supervision</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">Round trip transportation</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">Cold bottled water, disposable moist towels (only transportation)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">Entrance fee to Xcaret eco-archaeological park</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">3 courses dinner with beverages, (menu to be set by the group prior the event)</td>
        </tr>        
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">Swimming with dolphins and activities with additional cost</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">Gratuities US $ 5.00 per person</td>
        </tr>        
    </table>   
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:33%;text-align:left;"><u><strong>*** Tour Cost</strong></u></td>
            </tr>
            <tr>
                <td style="width:33%; border:0.3px">Private Tour in motor coach minimum 35.</td>
                 <td style="width:33%; border:0.3px">US $ 135.00</td>
                  <td style="width:33%; border:0.3px">Per person</td>
        </tr>
        </table>
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><u><strong>SUGGESTED ITINERARY (SUMMER TIME SCHEDULE):</strong></u></td>
            </tr>
            <tr>
                <td style="width:35%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">01:00 PM</td>
        </tr>
        <tr>
                <td style="width:35%;">Departure from the hotel</td>
                 <td style="width:20%;">01:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Arrival at Xcaret </td>
                 <td style="width:20%;">3:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Welcome in Xcaret</td>
                 <td style="width:20%;">3:00 P.M. – 3:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Free time</td>
                 <td style="width:20%;">3:30 P.M. - 6:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Night Show</td>
                 <td style="width:20%;">7:00 P.M. – 9:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Departure to the hotel</td>
                 <td style="width:20%;">9:30 P.M.</td>
        </tr> 
        <tr>
                <td style="width:35%;">Arrival at the hotel</td>
                 <td style="width:20%;">: 11:00 P.M.</td>
        </tr>       
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><u><strong>SUGGESTED ITINERARY (WINTER TIME SCHEDULE):</strong></u></td>
            </tr>
            <tr>
                <td style="width:35%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">01:00 PM</td>
        </tr>
        <tr>
                <td style="width:35%;">Departure from the hotel</td>
                 <td style="width:20%;">01:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Arrival at Xcaret </td>
                 <td style="width:20%;">3:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Welcome in Xcaret</td>
                 <td style="width:20%;">3:00 P.M. – 3:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Free time</td>
                 <td style="width:20%;">03:30 P.M. – 05:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Night Show</td>
                 <td style="width:20%;">06:00 P.M. – 08:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Departure to the hotel</td>
                 <td style="width:20%;">08:30 P.M.</td>
        </tr> 
        <tr>
                <td style="width:35%;">Arrival at the hotel</td>
                 <td style="width:20%;">10:00 P.M.</td>
        </tr>       
        </table>
        <br/>         
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Recommendations:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">Use comfortable shoes, casual clothes, bring towel and swimming suit, hat, sunglasses, and camera.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">Local Arts & Crafts offered at the stores; bring cash </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;"><u>Only Bio-degradable sun lotion this authorized in Xcaret (also available in its stores)</u></td>
        </tr>    
        </table>
        </page>';
		$topdf->writeHTML($html2);
	}
break;
case 21:
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>PUNTA VENADO</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/punta_venado.jpg" width="211" height="142"/></td>
<td style="width:65%; text-align:justify;  font-size:12px;">Punta Venado is an ecological adventure that extends itself in 2060 hectares of forest and 3.72 miles of beach in the heart of the Riviera Maya in the Mexican Caribbean. It also offers a traditional Mexican Hacienda surrounded by unique Caribbean style stables, pets and traditional buildings as region styled palapas that figure a traditional Mayan village in the middle of the virgin tropical jungle. 
<br/>Explore and discover the natural flora as the characteristic ceiba trees, palm trees, sea grapes, poplar and zapotes as well as a big variety of colorful birds, small cats, mammals and reptiles, particularities of this beautiful region.</td>
</tr>
</table>
<table style="width:100%;">
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:12px;">
You will admire the beauty of its white sand beaches, surrounded by an amazing amount of coral reefs, the second of the world by its size, and practice kayaking in the protected reef in shape of a big tube. <br/>
Nothing is better than enjoy a horse ride in the jungle or at the beach, or the exciting ATV trails, as well as snorkeling in an amazing reef just by the beach. The children will also enjoy kayaking, swimming in beautiful cenotes and live a real adventure by exploring the caves or simply relaxing on the beach and enjoying the scenery that offers Punta Venado.
<br/><br/>
Start the ATV adventure expedition through the jungle with a walking tour into the exciting cave and visit the beautiful cenote. A couple of minutes away from the cenote continue through the jungle, and you will find the beach and go snorkeling in the reef barrier. Returning from the water, some snacks, soft drinks and a white sandy beach will be waiting for your rest and refreshment. The trip ends after a ride along the beach to the Rancho. The rest of the day can be spent at El Coprero Beach Club.</td>
</tr>
</table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Included: </strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Entrance fee, ATV, helmet, goggles, lanterns, snorkeling equipment, soft drinks and snacks. </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Shared Transportation</td>
        </tr>       
    </table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Gratuities $5.00 USD per pax</td>
        </tr>
    </table>
    <table style="width:100%;">
<tr>
<td style="width:65%;text-align:justify;  font-size:13px;"><strong>Horse ride in Punta Venado:</strong></td>
</tr>
</table>
<table style="width:100%;">
	<tr>
<td style="width:65%;text-align:justify;  font-size:13px;">
A horse will be attributed to you according to your age. This activity is suitable for the whole family.
El The program includes a tour along the coast, returning to the ranch through one side of the beach where you can find lush vegetation, and a free time enjoying a beautiful Cenote where you can swim or just relax.
<br/> Expeditions 09:00 A.M. / 12:00 A.M./ 03:00 P.M<br/>
Capacity:  Groups containing 14 participants maximum</td>
<td width="15%" style="text-align:center;"><img src="../img/activities/horse.jpg" width="211" height="142"/></td>
</tr>
<tr>
<td style="width:65%;text-align:justify;  font-size:13px;"><strong>Includes:</strong>entry price, horseback riding and 1 bottle of water</td>
</tr>
<tr>
<td style="width:65%;text-align:justify;  font-size:13px;"><strong>Duration:</strong>1 hour and 15 minutes</td>
</tr>
</table>
<br/>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:left;"><img src="../img/activities/horse1.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:12px;">
ATV Aventura:<br/>
Meeting in Punta Venado to start the ATV expedition through the jungle, until arriving at a completely dark and exciting cave. This cave is located 5 minutes away by a walk through the jungle track.
<br/>One inside the cave, you need to go and admire the ancient rock formations by using flashlights, as it is completely dark. The ATV expedition goes on until a beautiful cenote where you can capture the best photos, swim or just take a little break. </td>
</tr>
</table>
<table style="width:100%;">
<tr>
<td style="width:65%;text-align:justify;  font-size:13px;">Expeditions  09:00 A.M. / 12:00 A.M./ 03.00 P.M</td>
</tr>
<tr>
<td style="width:65%;text-align:justify;  font-size:13px;">Capacities: Groups of 15 persons maximum</td>
</tr>
</table>  
 <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">ATV for 1 or 2 people, helmet, goggles, flashlights and 1 bottle of water</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;"><strong>Duration:</strong>1 hour and 45 minutes
<br/>Minimal age to drive: 16 years
            </td>
        </tr>
    </table>
    <br/>
    <table style="width:100%;">
    <tr>
<td style="width:65%;text-align:justify;  font-size:13px;">Snorkeling and Cenote:</td>
 </tr>
    <tr>
<td style="width:65%;text-align:justify; font-size:13px;">
Discover the beauty of one of the most impressive barriers of coral reefs in the Riviera Maya. You will enjoy a fascinating travel by snorkeling in a few deep reefs with gentle currents, where you will have an amazing visibility under the water.  The rest of the day could be spent at the “Coprero Beach Club”.
<br/>Capacity: Groups of 50 people maximum</td>
<td width="15%" style="text-align:left;"><img src="../img/activities/horse2.jpg" width="211" height="142"/></td>
</tr>
<tr>
            <td style="width:35%;text-align:left;font-size:12px;"><u><strong>Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:12px;">Entry price, snorkeling o kayaking material, water bottles and</td>
        </tr>
         <tr>
            <td style="width:35%;text-align:left;font-size:12px;"><u><strong>Duration:  </strong>3 hours
</u></td>
        </tr>
         <tr>
            <td style="width:35%;text-align:left;font-size:12px;">Expeditions:  09:00 A.M. / 11:00 A.M.</td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:12px;">Schedule: From 9:00 A.M. to 05:00 P.M. From Monday to Sunday.</td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:12px;"><u><strong>Price: </strong></u></td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:12px;">$ 20 USD for the beach club’s entry. </td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:12px;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:12px;"><u><strong>Suggested itinerary:</strong></u></td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:12px;">Participants ready at the hotel lobby:</td>
             <td style="width:20%;text-align:left;font-size:12px;">07:30 A.M.</td>
        </tr>
         <tr>
            <td style="width:35%;text-align:left;font-size:12px;">Departure from the hotel:</td>
            <td style="width:20%;text-align:left;font-size:12px;">07:45 A.M.</td>
            
        </tr>
         <tr>
            <td style="width:35%;text-align:left;font-size:12px;">Arrival at Punta Venado:</td>
            <td style="width:20%;text-align:left;font-size:12px;">08:45 A.M.</td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:12px;">Departure from Punta Venado:</td>
             <td style="width:20%;text-align:left;font-size:12px;">03:00 P.M.</td>
        </tr>
         <tr>
            <td style="width:35%;text-align:left;font-size:12px;">Arrival at hotel:</td>
             <td style="width:20%;text-align:left;font-size:12px;">04:00 P.M.</td>
        </tr>
</table>
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">           
         <tr>
                <td style="width:33%;"><strong>Tour Price:</strong></td>
        </tr>
         <tr>
                <td style="width:33%; border:0.3px">Tour Horse riding</td>
                 <td style="width:33%; border:0.3px">US $ 73.00</td>
                  <td style="width:33%; border:0.3px">Pro person</td>
        </tr>
        <tr>
                <td style="width:33%; border:0.3px">Tour ATV </td>
                 <td style="width:33%; border:0.3px">US $ 72.00</td>
                  <td style="width:33%; border:0.3px">Pro person</td>
        </tr>
        <tr>
                <td style="width:33%; border:0.3px">Tour Snorkeling</td>
                 <td style="width:33%; border:0.3px">US $ 52.00</td>
                  <td style="width:33%; border:0.3px">Pro person</td>
        </tr>
        <tr>
                <td style="width:33%;">&nbsp;</td>
        </tr>
        <tr>
                <td style="width:33%;"><strong>Private transportation:</strong></td>
        </tr>
        <tr>
                <td style="width:33%; border:0.3px">Van Min. 8 pax Max. 10 pax</td>
                 <td style="width:33%; border:0.3px">US $ 200.00</td>
                  <td style="width:33%; border:0.3px">Per unit / Open Service (6 hours)</td>
        </tr>
         <tr>
                <td style="width:33%; border:0.3px">Motor coach up to 45pax</td>
                 <td style="width:33%; border:0.3px">US $900.00</td>
                  <td style="width:33%; border:0.3px">Per unit / Open Service (6 hours)</td>
        </tr>       
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Remarks:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Comfortable footwear to walk in the jungle</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Swimming gear</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Extra T-shirt, Towel</td>
        </tr> 
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Extra cash (tips, photos and souvenirs)</td>
        </tr> 
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Biodegradable Suntan lotion</td>
        </tr>     
        </table>
        </page>';
		$topdf->writeHTML($html2);
}
break;
case 22:
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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>DEEP SEA FISHING</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/deep_sea.jpg" width="211" height="142"/></td>
            <td style="width:65%;text-align:justify;  font-size:13px;">
Be a pioneer of sport fishing in Riviera Maya. Fishing Charters offers one of the shortest running time out to the deep water beyond the Continental Shelf. With their expert English-speaking captains you are virtually guaranteed to catch fish. Most of the crew has over 10 years of experience fishing these grounds. Their boats are fully equipped with quality Penn and Shimano tackle, depth sounders and G.P.S. Comfortably seating 6 persons in the shade, they feature large cockpits with fighting chair. All safety gear is provided. Traditionally the seas are mostly calm from February through August and are excellent for sails, marlin, tuna and dolphin.</td>
</tr>
</table>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:100%;text-align:left;"><u><strong>Includes:</strong></u></td>
    </tr>
    <tr>
        <td style="width:100%;">&nbsp;•  CTA Cancun Coordination</td>
    </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•  Experienced bilingual Captain and mate</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•  Very comfortably yachts fully equipped with quality tournament gear</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•  Safety Gear</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•  Cooler with soft drinks, beer and bottled water</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•  Bait</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•  Fishing licenses and fees</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•  Insurance</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•  Cold bottled water and disposable moist towels on board ground transportation.</td>
        </tr>
    </table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Gratuities 15 % </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Food & Snacks</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transportation, quoted below</td>
        </tr>
         <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Federal Reef Taxes US$10.00 per person</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><strong>Price per Yacht</strong></td>
            </tr>
            <tr>
                <td style="width:55%; border: 0.3px">&nbsp;</td>
                 <td style="width:15%; border: 0.3px">4 Hours</td>
                  <td style="width:15%; border: 0.3px">6 Hours</td>
                  <td style="width:15%; border: 0.3px">8 Hours</td>
        </tr>
        <tr>
            <td style="width:55%; border: 0.3px">33’ & 34’ yachts (max capacity 8 pax) Recommended only 6 pax</td>
            <td style="width:15%; border: 0.3px">US $690.00</td>
            <td style="width:15%; border: 0.3px">US $790.00</td>
            <td style="width:15%; border: 0.3px">US $890.00</td>
        </tr>
        </table>
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:33%;text-align:left;"><strong>Transportation Hotel –Marina - Hotel in Cancun hotel Zone</strong></td>
            </tr>
            <tr>
                <td style="width:34%; border: 0.3px">Van for small groups up to 10 pax</td>
                 <td style="width:33%; border: 0.3px">US $120.00</td>
                  <td style="width:33%; border: 0.3px">Per Unit, Round Trip</td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px">Motor coach Up to 45 pax</td>
                 <td style="width:33%; border: 0.3px">US $660.00</td>
                  <td style="width:33%; border: 0.3px">Per Unit, Round Trip</td>
        </tr>
    </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>Deluxe Box lunches from US $ 10.00  to US$22.00 per person, different options available</strong></td>
            </tr>
        </table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:40%;text-align:left;"><strong>SUGGESTED ITINERARY FOR 6-HOUR CHARTER:</strong></td>
            </tr>
        <tr>
                <td style="width:40%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">06.30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Departure to the Marina:</td>
                 <td style="width:20%;">06.35 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Arrival to the Marina:</td>
                 <td style="width:20%;">07:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Boats departure:</td>
                 <td style="width:20%;">07.30 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Return to the Marina</td>
                 <td style="width:20%;">02:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Back at the Hotel:</td>
                 <td style="width:20%;">02:30 P.M.</td>
        </tr>
        </table>
         <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><strong>Recommendations:</strong></td>
        	</tr>
        	<tr>
        		<td style="width:100%;">Bring sunglasses, sun block, and camera.</td>
        	</tr>
        </table>
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 23:
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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>DOLPHIN ENCOUNTER  AT  PUERTO AVENTURA OR  ISLA MUJERES</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/dolphin.jpg" width="211" height="142"/></td>
            <td style="width:65%;text-align:justify;  font-size:13px;">
                The first encounter: learn and have fun! Discover the dolphin’s best-kept secrets in a safe and fun environment. You will be surprised by the great intelligence and friendliness of these wonderful marine mammals. You will also have the opportunity to hug it, kiss it, let it kiss you on the cheek, and enjoy watching your new friend while it performs a series of amazing behaviors. They will make you tingle with excitement! This program is ideal for children of all ages.
            </td>
</tr>
</table>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:100%;text-align:left;"><u><strong>Tour Includes:</strong></u></td>
    </tr>
    <tr>
        <td style="width:100%;">Private round-trip transportation hotel-Delphinarium-hotel</td>
    </tr>
        <tr>
            <td style="width:100%;text-align:left;">Cruise transportation to the islands</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">Swimming with Dolphins</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">Bottled Water on transportation</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">Buffet Lunch</td>
        </tr>
    </table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">Photographs</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">Video</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">Gratuities – US $5.00 per person</td>
        </tr>
         <tr>
            <td style="width:100%;text-align:left;">Coral reef federal tax $8.00 per person.</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><strong>SUGGESTED ITINERARY:</strong></td>
            </tr>
            <tr>
                <td style="width:35%;">Participants ready at the hotel lobby:</td>
                 <td style="width:25%;">08:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Departure from the hotel:</td>
                 <td style="width:25%;">08:15 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Arrival at Marina:</td>
                 <td style="width:25%;">08:45 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Departure to Isla Mujeres:</td>
                 <td style="width:25%;">09:15 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Dolphins meeting:</td>
                 <td style="width:25%;">09:45 AM – 11:45 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Departure to the hotel:</td>
                 <td style="width:25%;">12:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Arrival at the hotel:</td>
                 <td style="width:25%;">12:45 P.M.</td>
        </tr>
        <br/>
        </table>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>Tours are available at 10:30, 11:00, 1:00, 3:30 pm</strong></td>
            </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><strong><u>Tour price:</u></strong></td>
            </tr>
            <tr>
            <td style="width:34%; border:0.3px">Private tour</td>
            <td style="width:33%; border:0.3px">$86.00</td>
            <td style="width:33%; border:0.3px">Minimum Groups of 16 pax.</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>In order to keep the activity as private, we need a minimum of 16 pax Maximum per shift 20 pax</strong></td>
            </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;"><strong>Transportation Hotel –Marina - Hotel  IN CANCUN</strong></td>
        </tr>
        <tr>
                <td style="width:34%; border:0.3px">Van for small groups up to 10 pax</td>
                 <td style="width:33%; border:0.3px">US $ 120.00</td>
                  <td style="width:33%; border:0.3px">Per Unit, Round Trip</td>
        </tr>
        <tr>
                <td style="width:34%; border:0.3px">Motor coach Up to 45 pax</td>
                 <td style="width:33%; border:0.3px">US $660.00</td>
                  <td style="width:33%; border:0.3px">Per Unit, Round Trip</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>** TRASPORTATION TO PUERTO AVENTURAS  </strong></td>
            </tr>
            </table>
            <table border="0.3px" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%; border:0.3px">Van for small groups up to 10 pax</td>
                 <td style="width:33%; border:0.3px">US $150.00</td>
                  <td style="width:33%; border:0.3px">Per Unit, Round Trip</td>
        </tr>
        <tr>
                <td style="width:34%; border:0.3px">Motor coach Up to 45 pax</td>
                 <td style="width:33%; border:0.3px">US $950.00</td>
                  <td style="width:33%; border:0.3px">Per Unit, Round Trip</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Remarks:</u></strong></td>
            </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Comfortable clothing is suggested: Bermudas, T-shirts, Tennis Shoes, Sunglasses, and Cameras swimming suit and towel</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Suntan lotion is suggested</td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:100%;"><strong>Important:</strong></td>
        </tr>
        <tr>
                <td style="width:100%;"> Pregnant women are not allowed to participate. If you have any physical or mental limitations, please contact us before booking. The use of safety vests is required in all Dolphin Discovery programs. </td>
        </tr>
        </table>    
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 24:
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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>ROYAL SWIM AT PUERTO AVENTURAS, COZUMEL & ISLA MUJERES</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/royal_swim.jpg" width="211" height="142"/></td>
            <td style="width:65%;text-align:justify;  font-size:12px;">
                Have you ever dreamed about swimming with dolphins in the beautiful waters of the Caribbean Sea? Today you can make your dreams come true! The Dolphin Royal Swim
                ® program is best defined with the words “action” and “speed”. It is the most dynamic of all the swim with dolphins programs, and this time, Dolphin Discovery Cancun
                will give you an experience to talk about for the rest of your life with family and friends.
                <br/> <br/>
                You will find interesting aquatic activities with these tender animals that seem to know you already. While you walk by the pier, you will notice them following you, waiting for their playtime. They will conquer your heart before you even get into the water. They will give you a handshake, kisses and will pull you with their dorsal tows to give you a speed ride! After that, the dolphins will push your feet to raise you up the water surface, you will feel like flying!
            </td>
</tr>
</table>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:100%;text-align:left;"><u><strong>Includes:</strong></u></td>
    </tr>
    <tr>
        <td style="width:100%;">&nbsp;• CTA Cancun supervision</td>
    </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Round trip transportation</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Island cruise</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Cold bottled water and disposable moist towels during the transportation</td>
        </tr>
    </table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Gratuities $5.00 USD per pax</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Photo and Video of your experience with our dolphins.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Other activities in Cancun or Isla Mujeres.</td>
        </tr>
         <tr>
            <td style="width:100%;text-align:left;">&nbsp;• $3.00 USD dock fee per person that must be paid at Marina Aquatours check in.</td>
        </tr>
         <tr>
            <td style="width:100%;text-align:left;">&nbsp;• US$8.00 coral reef federal tax, per person</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><strong>Suggested itinerary:</strong></td>
            </tr>
            <tr>
                <td style="width:35%;">Participants ready at the hotel lobby:</td>
                 <td style="width:25%;">09:15 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Departure from the hotel:</td>
                 <td style="width:25%;">09:30 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Arrival at Marina:</td>
                 <td style="width:25%;">10:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Departure from Marina:</td>
                 <td style="width:25%;">10:15 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Arrival at Isla Mujeres:</td>
                 <td style="width:25%;">10:30 AM – 12:30 P.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Dolphins meeting:</td>
                 <td style="width:25%;">10:30 AM – 12:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Departure to the hotel:</td>
                 <td style="width:25%;">12:45 P.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Arrival at the hotel:</td>
                 <td style="width:25%;">01:15 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><strong><u>Costs:</u></strong></td>
            </tr>
            <tr>
            <td style="width:25%; border: 0.3px">Private tour</td>
            <td style="width:25%; border: 0.3px">US $ 139.00</td>
            <td style="width:25%; border: 0.3px">Per person</td>
            <td style="width:25%; border: 0.3px">Isla Mujeres</td>
        </tr>
            <tr>
            <td style="width:25%; border: 0.3px">Private tour</td>
            <td style="width:25%; border: 0.3px">US $ 130.00</td>
            <td style="width:25%; border: 0.3px">Per person</td>
            <td style="width:25%; border: 0.3px">Puerto Aventuras</td>
        </tr>
            <tr>
            <td style="width:25%; border: 0.3px">Private tour</td>
            <td style="width:25%; border: 0.3px">US $ 130.00</td>
            <td style="width:25%; border: 0.3px">Per person</td>
            <td style="width:25%; border: 0.3px">Cozumel</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>In order to keep the activity as private, we need a minimum of 16 pax, maximum per shift 20 pax</strong></td>
            </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;"><strong>Transportation ROYAL SWIM ISLA MUJERES:</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">            
        <tr>
                <td style="width:34%; border: 0.3px">Motorcoach for 45 pax</td>
                 <td style="width:33%; border: 0.3px">US $ 660.00</td>
                  <td style="width:33%; border: 0.3px">Per Unit, Round Trip</td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px">Van max 10 pax</td>
                 <td style="width:33%; border: 0.3px">US $ 120.00</td>
                  <td style="width:33%; border: 0.3px">Per Unit, Round Trip</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;"><strong>Transportation ROYAL SWIM COZUMEL:</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:34%; border: 0.3px">Motorcoach for 45 pax</td>
                 <td style="width:33%; border: 0.3px">US $ 850.00</td>
                  <td style="width:33%; border: 0.3px">Per Unit, Round Trip</td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px">Van max 10 pax</td>
                 <td style="width:33%; border: 0.3px">US $ 130.00</td>
                  <td style="width:33%; border: 0.3px">Per Unit, Round Trip</td>
        </tr>
    </table>
<br/>
 <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;"><strong>Transportation ROYAL SWIM PUERTO AVENTURAS</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:34%; border: 0.3px">Motorcoach for 45 pax</td>
                 <td style="width:33%; border: 0.3px">US $ 950.00</td>
                  <td style="width:33%; border: 0.3px">Per Unit, Round Trip</td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px">Van max 10 pax</td>
                 <td style="width:33%; border: 0.3px">US $ 150.00</td>
                  <td style="width:33%; border: 0.3px">Per Unit, Round Trip</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Remarks:</u></strong></td>
            </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Comfortable clothing is suggested: Bermudas, T-shirts, Tennis Shoes, Sunglasses, swimming suit and Cameras, biodegradable Suntan lotion I</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Local Arts & Crafts offered at the stores; bring cash</td>
        </tr>
         <tr>
                <td style="width:100%;">&nbsp;• Professional Cameras require a special permission from Federal Authorities; process will take at least 20 days.</td>
        </tr>
    </table>    
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 25:
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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>NATURAL REEF AT ISLA MUJERES “GARRAFON”</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/natural_reef.jpg" width="480" height="165"/></td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;  font-size:12px;">
                “Wake Up your Senses with the magic of a Coral reef in a cliff, amazing water activities and archeological ruins in the middle of the sea, which get merged with the mysticism of the Mayan culture to offer you the spiritual tranquility in combination with exciting experiences that make you enjoy the nature that the Mexican Caribbean has.
            </td>
</tr>
<tr>
            <td  style="width:100%;text-align:justify;  font-size:12px;">
                Enjoy snorkeling in this unique coral reef or a panoramic walk between an incredible naturally sculptured cliff and the Caribbean, or just relax in the beautiful infinity swimming pool. After enjoying all the activities at Garrafon, discover your dolphin nature with our famous “Dolphin Encounter” at Dolphin Island.
            </td>
</tr>

</table>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:100%;text-align:left;"><u><strong>Includes:</strong></u></td>
    </tr>
    <tr>
        <td style="width:100%;">&nbsp;• Disposable Moist towels on ground transportation.</td>
    </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Round-trip transportation from the Marina to Isla Mujeres (Garrafon)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Beverages on board and at the park.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Entrance fee.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Continental Breakfast and Buffet Lunch</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Snorkeling equipment, Life vests, Sky Line & Swimming Pool</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Swimming with Dolphins</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• CTA Bilingual personnel.</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Gratuities – US $5.00 per person.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Photos and videos with dolphins</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• The $3.00 USD fee at the moment of getting registered in Aqua World</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:40%;text-align:left;"><strong>SUGGESTED ITINERARY:</strong></td>
            </tr>
            <tr>
                <td style="width:40%;">Participants ready at the hotel lobby:</td>
                 <td style="width:20%;">09.00 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Motor coaches depart from Hotel:</td>
                 <td style="width:20%;">09.10 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Estimated arrival time at the Marina:</td>
                 <td style="width:20%;">09.40 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Depart to Garrafon Park:</td>
                 <td style="width:20%;">10.00 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Arrival at Garrafon Park:</td>
                 <td style="width:20%;">10.45 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Free time at Garrafon Park:</td>
                 <td style="width:20%;">11.00- 2:300 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Participants ready to board the Boat:</td>
                 <td style="width:20%;">02.30 P.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Departure to Dolphin Island:</td>
                 <td style="width:20%;">02.35 P.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Swimming with the Dolphins:</td>
                 <td style="width:20%;">03.00-03.50 P.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Departure to Cancun:</td>
                 <td style="width:20%;">04.00 P.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Arrival at the Marina:</td>
                 <td style="width:20%;">04.40 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Arrival at the hotel:</td>
                 <td style="width:20%;">05.00 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;text-align:left;"><strong><u>Tour price:</u></strong></td>
            </tr>
            <tr>
            <td style="width:34%; border: 0.3px">Garrafon Discovery  and Royal Swim</td>
            <td style="width:33%; border: 0.3px">US $172.00</td>
            <td style="width:33%; border: 0.3px">Per person</td>
        </tr>
    </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;text-align:left;"><strong>Transportation from Cancun</strong></td>
            </tr>
            <tr>
                <td style="width:34%;text-align:left; border: 0.3px">Motor coach for 45 pax</td>
                <td style="width:33%;text-align:left; border: 0.3px">US $ 660.00</td>
                <td style="width:33%;text-align:left; border: 0.3px">Per unit, round trip</td>
            </tr>
            <tr>
                <td style="width:34%;text-align:left; border: 0.3px">Van max 10 pax</td>
                <td style="width:33%;text-align:left; border: 0.3px">US $ 120.00</td>
                <td style="width:33%;text-align:left; border: 0.3px">Per unit, round trip</td>
            </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;"><strong>Remarks:</strong></td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Schedule is suggested and will be adapted according to the group needs.</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Comfortable clothing is suggested: Bermudas, T-shirts, Swimsuit, and Tennis Shoes & Cameras.</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Bring Hotel Towels.</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Biodegradable Suntan lotion is allowed, others will be kept at the entrance.</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Food and beverages may not be brought into.</td>
        </tr>
    </table>   
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 4:
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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>SELVATICA CANOPY TOUR</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/selvatica.jpg" width="211" height="142"/></td>
            <td style="width:65%;text-align:justify;  font-size:13px;">
            Upon arrival, all guests are greeted at the main “palapa”, where we have lockers to put away personal belongings.
            Our first activity will be the “Canopy Tour”. In order to do this our staff will equip each guest with harnesses, security pulleys, helmets, and gloves; our staff will check that all the equipment is properly fitted.
            <br/>
            <br/>
            After the canopy tour and a quick change to swimming trousers, the next activity takes place: this is mountain biking
            towards the “cenote” (sinkhole) which is one mile away. Our bikes are all aluminum Trek mountain bikes, specifically designed for this path; in this route you will witness the beauty of the regional Mayan jungle.</td>
</tr>
</table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:100%;text-align:left;"><u><strong>Includes:</strong></u></td>
    </tr>
    <tr>
        <td style="width:100%;">&nbsp;• Round transportation</td>
    </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Canopy Tour</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Mountain bikes</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Swim in the cenote (sink hole)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Light lunch and purified water</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Gratuities $5.00 USD per pax</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><strong>SUGGESTED ITINERARY:</strong></td>
            </tr>
            <tr>
                <td style="width:35%;">Participants ready at the hotel lobby:</td>
                 <td style="width:20%;">08:45 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Departure to Selvatica:</td>
                 <td style="width:20%;">09:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Arrival to Selvatica:</td>
                 <td style="width:20%;">09:45 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Selvatica Canopy Tour beginning:</td>
                 <td style="width:20%;">10.00 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Swimming at Cenote Verde Lucero :</td>
                 <td style="width:20%;">11:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Return to the Hotel:</td>
                 <td style="width:20%;">01:00 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;text-align:left;"><strong><u>Cost of the Tour:</u></strong></td>
            </tr>
            <tr>
            <td style="width:34%; border: 0.3px">Canopy Tour</td>
            <td style="width:33%; border: 0.3px">US $86.00</td>
            <td style="width:33%; border: 0.3px">Per person</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Remarks:</u></strong></td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Comfortable footwear to walk in the jungle</td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Swimming gear </td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Extra T-shirt</td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Towel </td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Extra cash (tips, photos and souvenirs).</td>
            </tr>
        </table>  
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 26:
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>RIO SECRETO</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/rio_secreto.jpg" width="211" height="142"/></td>
<td style="width:62%;text-align:justify;  font-size:12px;">
    Dare to live a fantastic experience in one of the most incredible locations in the Riviera Maya.
Enter this natural museum filled with a kaleidoscope of speleothems and walk an easy 600m route amidst the thousands of stalactites and stalagmites found in this protected natural reserve.
Learn and be amazed in this ancient, magical underground world unexplored for millions of years.<br/>
<br/>Arrival at Rio Secreto. Here the guides will: meet visitors, brief visitors regarding site attractions, usage, and safety and provide visitors with all appropriate clothing and equipment.
Visitors will then be guided on a one and a half hour (or 600 meter) tour of Rio Secreto, walking and swimming along the route.
<br/>
A light lunch will be enjoyed after this unique and exciting experience.</td>
</tr>
</table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Van A/A round trip Hotel – Rio Secreto – Hotel</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transfer to the River entrance.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Lockers</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Equipment: Helmet with light, flash light, wetsuit, special shoes and life vest (optional)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• River entrance fee</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Light Lunch</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• CTA  staff to Coordinate activity</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;">&nbsp;• 16% tax</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp;• Gratuity $5.00 usd per person</td>
        </tr>
    </table>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><u><strong>Suggested itinerary:</strong></u></td>
            </tr>
            <tr>
                <td style="width:3520%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">09:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Departure from the hotel:</td>
                 <td style="width:20%;">09:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Arrival at Rio Secreto:</td>
                 <td style="width:20%;">10:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Guided Tour</td>
                 <td style="width:20%;">11:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Lunch:</td>
                 <td style="width:20%;">12:45 P.M.-01:45 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Departure from Rio Secreto:</td>
                 <td style="width:20%;">02:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Arrival at the hotel:</td>
                 <td style="width:20%;">03:00 P.M.</td>
        </tr>
        </table>
         <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><u><strong>Schedule: &nbsp; &nbsp; &nbsp;9:00 A.M. / &nbsp;11 A.M. &nbsp;/ 1:00 P.M.</strong></u></td>
        	</tr>
        	<tr>
        		<td style="width:100%;">* The order of these activities may vary depending on daily logistics</td>
        	</tr>
        </table>
       <!-- <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:34%;text-align:left;"><u><strong>Tour Cost:</strong></u></td>
        </tr>
        <tr>
            <td style="width:34%; border: 0.3px">2.30 hrs Tour</td>
            <td style="width:33%;text-align:left; border: 0.3px">US $ 91.00</td>
             <td style="width:33%;text-align:left; border: 0.3px">Per person</td>
        </tr>
    </table>
    <br/>
     <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:34%;text-align:left;"><strong>Transportation to Rio Secreto</strong></td>
        </tr>
        <tr>
            <td style="width:34%; border: 0.3px">Motorcoach 45 pax</td>
            <td style="width:33%;text-align:left; border: 0.3px">US $ 950.00</td>
             <td style="width:33%;text-align:left; border: 0.3px">Per unit, round trip</td>
        </tr>
        <tr>
            <td style="width:34%; border: 0.3px">Van max 10 pax</td>
            <td style="width:33%;text-align:left; border: 0.3px">US $ 150.00</td>
             <td style="width:33%;text-align:left; border: 0.3px">Per unit, round trip</td>
         </tr>
         <tr>
             <td style="width:34%;text-align:left;"><strong>Capacity: 48 people per turn</strong></td>
        </tr>
    </table>-->
    <br/>
    <table style="width:100%;">
        <tr>            
            <td style="width:100%;text-align:justify;  font-size:13px;"><strong><u>Recommendations:</u></strong><br/>
                &nbsp;• Comfortable clothing and walking shoes, (no flip-flops!), short-sleeved T-shirt is recommended<br/>
                &nbsp;• Bathing suit on, additional set of dry clothes<br/>
                &nbsp;• Credit card or cash for tips, photos, videos, crafts and souvenirs<br/>
                <br/><strong><u>Restrictions:</u></strong>
                <br/>
                &nbsp;• Maximum weight: 250lb /120 kg<br/>
                &nbsp;• Minimum age: 6 years<br/>
                &nbsp;• This tour is not available for people with severe physical handicaps, heart diseases, pregnant women or for people under the influence of alcohol or drugs.</td>
        </tr>
</table>
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 27:
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>TULUM XTREME</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
    <td width="15%" style="text-align:center;"><img src="../img/activities/tulum_xtreme.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:13px;">
    Enjoy the perfect mixture of culture and adventure, as you go through one of the most amazing archaeological sites, overlooking the Caribbean Sea of Tulum.
    <br/>
    <br/>
    Fly 21 meters (70 feet) above the jungle canopy on 765 meters (2,525 ft) zip line ride! Test your tenacity as you rappel 21 meters (70 ft) down into the Mayan jungle. Afterwards, wind down and enjoy snorkeling through an underground cavern. Delight yourself by sampling real Mexican cuisine as a perfect finish.
</td>
</tr>
</table>

         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Tour includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">A/C Transportation, professional guides, entrance fees, climbing and snorkeling equipment, lunch and beverages, insurance and taxes.</td>
        </tr>
    </table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>Technical Data. Three Zip Line circuit:  Zip line height:</strong> 21 mts. (70 ft.) &nbsp; &nbsp; <strong>Rappel height:</strong>21 mts. (70 ft.).</td>
        </tr>
        <tr>
            <td style="width:100%;">Guided tour at Tulum: 45 minutes plus 1 hour free time.</td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width:100%;"><strong>Duration:</strong> Approximately 7 hours from pickup to drop off.</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><u><strong>Suggested itinerary:</strong></u></td>
            </tr>
            <tr>
                <td style="width:35%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:25%;">08:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Departure from the hotel:</td>
                 <td style="width:25%;">09:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Arrival at Tulum:</td>
                 <td style="width:25%;">11:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Free time:</td>
                 <td style="width:25%;">11:00 A.M.-12:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Lunch:</td>
                 <td style="width:25%;">12:15 P.M.- 01:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Guided tour in Tulum:</td>
                 <td style="width:25%;">01:15 P.M-02:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Departure from Tulum:</td>
                 <td style="width:25%;">02:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Arrival at hotel:</td>
                 <td style="width:25%;">04:15 P.M.</td>
        </tr>
        </table>
         <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><strong>Operation Days:</strong></td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp;• <strong>English and Spanish:</strong> Daily</td>
        	</tr>
            <tr>
                <td style="width:100%;">&nbsp;• <strong>French:</strong>  Monday, Wednesday and Friday</td>
            </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:34%;text-align:left;"><strong>Rates Riviera Maya</strong></td>
        </tr>
        <tr>
            <td style="width:34%; border: 0.3px">Tulum Extreme</td>
            <td style="width:33%;text-align:left; border: 0.3px">US $ 100.00</td>
             <td style="width:33%;text-align:left; border: 0.3px">Per person</td>
        </tr>
    </table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:34%;text-align:left;"><strong>Rates Cancún</strong></td>
        </tr>
        <tr>
            <td style="width:34%; border: 0.3px">Tulum Extreme</td>
            <td style="width:33%;text-align:left; border: 0.3px">US $ 110.00</td>
             <td style="width:33%;text-align:left; border: 0.3px">Per person</td>
        </tr>
    </table>
    <br/>
     <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>Bring along:</strong> Comfortable clothes and footwear, water shoes, sunglasses and hat, bathing suit, extra T-shirt, towel, only<strong> BIODEGRADABLE</strong> sunscreen and mosquito repellent, cash (pictures, souvenirs and tips).</td>
        </tr>
        <tr>
            <td style="width:100%;"></td>
        </tr>
        <tr>
            <td style="width:100%;"><strong>Restrictions: Weight limit: </strong>(135 kg.) 300 lbs. <strong>Size: 44</strong></td>
        </tr>
    </table>
    <br/>
    <table style="width:100%;">
        <tr>
            <td  style="width:100%;text-align:justify;  font-size:13px;"><strong>Important recommendations:</strong>
                 Basic swimming skills required. Prescription goggles available under previous request. This tour is not suitable for people with severe physical or motor handicap, serious heart problems, <strong>pregnant women </strong>or people who are not able to handle moderate physical activity. People under the influence of alcohol or drugs will not be permitted to participate in this tour.
            </td>
        </tr>
</table>
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 29:
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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>SNORKEL XTREME</strong></td>
		</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<td width="15%" style="text-align:center;"><img src="../img/activities/snorkel.png" width="198" height="137"/></td>
				<td style="width:65%;text-align:justify;  font-size:13px;">
				 Experience the best mixture of ocean and jungle adventures as you snorkel the turquoise waters of the Mexican Caribbean surrounded by exotic marine life and fly above the exuberant Mayan jungle canopy.<br/>
                Surprise yourself with Mexico’s biodiversity and snorkel between marine turtles; coral and colorful fish while you enjoy the best sandy beach around.</td>
                </tr>
                 </table>
                  <br/>
                   <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
                <tr>
                	 <td style="width:100%;text-align:justify;  font-size:13px;">
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
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px; width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><strong>Suggested itinerary:</strong></td>
            </tr>
            <tr>
                <td style="width:35%;">Participants ready at the hotel lobby:</td>
        </tr>
         <tr>
                <td style="width:35%;"><p>Departure  from the hotel:</p></td>
        </tr>
         <tr>
                <td style="width:35%;">Arrival at Snorkel Xtreme:</td>
        </tr>
         <tr>
                <td style="width:35%;">Lunch:</td>
        </tr>
         <tr>
                <td style="width:35%;">Departure from Snorkel Xtreme:</td>
        </tr>         
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>Technical Data. Three Zip Line circuit:  Zip line height:</strong> 21 mts. (70 ft.) <strong>Rappel height:</strong>21 mts. (70 ft.)</td>
            </tr>
        </table>
            <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;"><strong>Rates Riviera Maya</strong></td>
        </tr>
        <tr>
                <td style="width:34; border: 0.3px">Mayan  Extreme</td>
                <td style="width:33%; border: 0.3px">US $ 110.00 </td>
                <td style="width:33%; border: 0.3px">Per person</td>
        </tr>        
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;"><strong>Rates Cancun</strong></td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px">Mayan Extreme</td>
                <td style="width:33%; border: 0.3px">US $ 120.00 </td>
                <td style="width:33%; border: 0.3px">Per person</td>
        </tr>        
    </table>
<br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">           
        <tr>
         <td style="width:100%;"><strong><u>Bring along:</u></strong> Comfortable clothes and footwear, sunglasses and hat, bathing suit, extra T-shirt, towel, only BIODEGRADABLE sunscreen and mosquito repellent, cash (pictures, souvenirs and tips).</td>
        </tr>
        <tr>
                <td style="width:100%;"><strong>Restrictions:  Weight limit:</strong> (135 kg.) 300 lbs. <strong>Size: 44</strong></td>
        </tr>
         <tr>         
                <td style="width:100%;"><strong>Important recommendations:</strong> Basic swimming skills required. Prescription goggles available under previous request. This tour is not suitable for people with severe physical or motor handicap, serious heart problems, <strong>pregnant women</strong> or people who are not able to handle moderate physical activity. People under the influence of alcohol or drugs will not be permitted to participate in this tour</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Frequency:</u></strong></td>
            </tr>
            <tr>
            <td style="width:100%;"><strong>&nbsp;• English and Spanish:</strong> Daily</td>
        </tr>
            <tr>
            <td style="width:100%;"><strong>&nbsp;• French: </strong>Monday, Friday and Sunday</td>
        </tr>
            <tr>
            <td style="width:100%;"><strong>&nbsp;• German:</strong> Tuesday, Thursday and Saturday</td>
        </tr>
        <tr>
            <td style="width:100%;"><strong>&nbsp;• Italian:</strong> Friday and Sunday.</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Remarks:</u></strong></td>
            </tr>
            <tr>
            <td style="width:100%;">All of these activities could be on private basis with a minimum of 12 people as a guarantee per schedule.</td>
        </tr>           
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>Gratuities suggested</strong> $5.00 USD per pax</td>
            </tr>   
    </table>
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 30:
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XEL-HA</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/xel-ha.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:13px;">Una visita al acuario natural más grande del mundo, Xel-Ha… un hermoso lugar para snorquelear.</td>
</tr>
</table>

        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Staff de CTA Cancun para supervisión</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transportación redonda en autobús para 34 pasajeros, con aire acondicionado</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Agua fría embotellada y toallitas húmedas durante la transportación</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Entrada al parque Xel-Ha y estacionamiento</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Lunch buffet y bar abierto nacional, helado, equipo de snorkel, toalla, lockers y regaderas </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Impuestos</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Nado con delfines</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Propinas, US $ 5.00 por persona</td>
        </tr>        
    </table>
    <br/>
    
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Recomendaciones: </strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Llevar ropa cómoda: bermudas, playera, tenis, traje de baño, cámara y bloqueador biodegradable</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Artesanías locales disponibles en las tiendas, traiga efectivo </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Las cámaras profesionales requieren un permiso federal especial y el trámite lleva al menos días</td>
        </tr>        
        </table>
    <br/>
    <table border="0" cellpadding="0" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><u><strong>Precio Tour Xel Ha: </strong></u></td>
            </tr>
            <tr>
                <td style="width:25%;" border="0.5px">Private Tour</td>
                <td style="width:25%;" border="0.5px">US $ 100.00</td>
                <td style="width:25%;" border="0.5px">Por Persona</td>
                <td style="width:25%;" border="0.5px">Mínimo 35 pax</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:40%;text-align:left;"><u><strong>ITINERARIO SUGERIDO:</strong></u></td>
            </tr>
            <tr>
                <td style="width:40%;">Participantes listos en el lobby del hotel</td>
                <td style="width:40%;">8:20 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Salida del autobús del hotel</td>
                <td style="width:40%;">8:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Llegada estimada a Tulum</td>
                <td style="width:40%;">10.30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Explicación y visita del lugar</td>
                <td style="width:40%;">10.30/11.30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Participantes listos para abordar autobús</td>
                <td style="width:40%;">11.45 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Salida a Xel-Ha</td>
                <td style="width:40%;">12.00 noon</td>
        </tr>
        <tr>
                <td style="width:40%;">Llegada estimada a Xel-Ha</td>
                <td style="width:40%;">12.15 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Tiempo libre en Xel-Ha</td>
                <td style="width:40%;">12.15 – 3.15 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Participantes listos para abordar autobús</td>
                <td style="width:40%;">3.30 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Salida a Cancún</td>
                <td style="width:40%;">3.45 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Llegada estimada al hotel</td>
                <td style="width:40%;">5.30 P.M.</td>
        </tr>
        </table>
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 31:
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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>TOUR DE COMPRAS</strong></td>
		</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<td style="width:100%;text-align:justify;  font-size:12px;">
                Nosotros ofrecemos dos opciones para un gran  tour de compras: Playa del Carmen con su famosa 5ª Avenida o Tour por la ciudad de Cancún combinado con compras,
                visitando el mercado de artesanía local en el centro de Cancún y dos de las mas importantes plazas comerciales, Plaza Kukulcan y La Isla en la Zona Hotelera. </td>
                </tr>
                 </table>
                  <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Incluye: </u></strong></td>
            </tr>
            <tr>
                <td style="width:100%;">&nbsp;•	Representante de CTA Cancún </td>                
        	</tr>
         	<tr>
                <td style="width:100%;">&nbsp;• Servicio abierto por 4 horas</td>
       		</tr>
        	<tr>
        	   <td style="width:100%;">&nbsp;• Transportación</td>
           </tr>
            <tr>
               <td style="width:100%;text-align:left;">• Botellas de agua fría y toallas húmedas desechables</td>
        	</tr>
        	<tr>
               <td style="width:100%;text-align:left;">• Conductor profesional con alta experiencia</td>
        	</tr>
        	<tr>
               <td style="width:100%;text-align:left;">• Guía bilingüe</td>
        	</tr>              
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>No incluye:</u> </strong></td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Propinas $65 por pax</td>
            </tr>            
        </table>
            <br/>
            <table style="width:100%;">
			<tr>
				<td style="width:65%;text-align:justify;  font-size:12px;"><strong>5ª Avenida en Playa del Carmen </strong><br/>
				Esta larga calle está llena de recuerdos Mexicanos, boutiques y tiendas de marcas famosas, restaurantes al aire libre y cafés
				estilo Caribeño y algunas nuevas plazas comerciales, todo a solo una calle de la playa llena de bares con música en vivo y excelentes restaurantes de mariscos. </td>
                <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours1.jpg" width="198" height="137"/></td>
                </tr>
                 </table>
                 <table style="width:100%;">
			<tr>
				<td style="width:65%;text-align:justify;  font-size:12px;"><strong>Mercado de Artesanías en el Centro </strong><br/>
				Este muy popular mercado está localizado en el centro de la ciudad de Cancún, lleno de pequeñas tiendas con los mejores ejemplares de la artesanía de todo México;
				plata, alfarería, objetos de madera tallada, máscaras, sarapes, sombreros, vestidos tradicionales, hamacas, cerámica pintada a mano, juguetes tradicionales en fin, la lista es interminable. </td>
                <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours2.jpg" width="198" height="137"/></td>
                </tr>
                 </table>
                 <table style="width:100%;">
			<tr>
				<td style="width:65%;text-align:justify;  font-size:12px;"><strong>Centro comercial la Isla</strong><br/>
				La Isla, el centro comercial número uno de México, localizada en la Laguna bajo un gigantesco pabellón. Series de canales y pequeños puentes le dan una encantadora vista tipo Venecia, además
				de 150 tiendas la plaza cuenta con una marina, el acuario interactivo, muchos cafés, bares, restaurantes y cinema. </td>
                <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours3.png" width="198" height="137"/></td>
                </tr>
                 </table>
                 <table style="width:100%;">
			<tr>
				<td style="width:65%;text-align:justify;  font-size:12px;"><strong>Plaza Kukulkan</strong><br/>
				La plaza más fina de Cancún con más de 250 tiendas y la Avenida de lujo más famosa; famosas boutiques de diseñadores internacionales, vestidos de las costuras de haute, fina joyería,
				perfumes, almacenes de las marcas, artes mexicanos del arte de la multa y la agradable opción de restaurantes y excelentes cafés, todos dentro de áreas con aire acondicionado modernas y espaciosas.</td>
                <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours4.png" width="198" height="137"/></td>
                </tr>
                 </table>
                 <!--<br/>
                 <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;"><strong><u>Costo:</u></strong></td>
        </tr>
        <tr>
                <td style="width:35%; border: 0.3px">Tour de Compras en Cancún</td>
                 <td style="width:30%; border: 0.3px">$ 560.00</td>
                  <td style="width:35%; border: 0.3px">Van por 4 horas servicio abierto (max 8 pax)</td>
        </tr>
        <tr>
                <td style="width:35%; border: 0.3px">Tour de Compras en Playa</td>
                 <td style="width:30%; border: 0.3px">$ 1,000.00</td>
                  <td style="width:35%; border: 0.3px">Van por 5 horas servicio abierto (máx. 8 pax)</td>
        </tr>        
    </table>-->
    <br/>
    <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours5.png" width="198" height="137"/></td>
				<td style="width:65%;text-align:justify;  font-size:12px;"><strong>City Tour</strong><br/>
				Disfruta de este agradable paseo por la ciudad de Cancún. Le llevaremos a los lugares más interesantes de la zona hotelera y el centro de Cancún; un viaje exclusivamente para usted<br/>
				<br/>
				Conozca los alrededores donde se encuentra la zona del hotel, en el centro, viajar a través de las principales calles y zonas residenciales, centros de artesanía, visitar el nuevo y maravilloso Museo Maya de Cancún.
				Conocerá y vera los alrededores de los Centros Comerciales de la zona hotelera en el centro de la ciudad.</td>                
                </tr>
                 </table>
                 <br/>
                 <table style="width:100%;">
			<tr>            
				<td  style="width:100%;text-align:justify;  font-size:12px;"><strong>Downtown / Zona Hotelera de Cancún:</strong><br/>
				Cancún, en Quintana Roo fue la sede de los Itzáes que llegaron desde el sur. Los mayas aprendieron a vivir con el bosque. Sólo hay vestigios de su extraordinaria grandeza como la fortaleza de Tulum, Coba City Kohunlich, entre otros.
				Hay restos de innumerables sitios conocidos, pero en gran parte inexplorado. No es exagerado decir que cada pedazo de selva tropical son las huellas de su espléndida cultura.<br/>
				<br/>
				Sobre la base de estas directrices, se encuentra el Banco de México Infratur, creado en 1969 para llevar a cabo un programa de centros turísticos integrados. De este modo, se iniciaron estudios para identificar
				las áreas favorables para la ejecución de proyectos de infraestructura turística y Cancún fue seleccionado como una prioridad para la inversión.
				<br/>
				<br/>
				Así surge la majestuosa ciudad de Cancún, número 1 de los destinos turísticos en todo el mundo. Hoy en día cuenta con impresionantes hoteles, villas y condominios. Grandes cadenas están presentes en
				Cancún y la ciudad cuenta con excelentes centros comerciales de calidad como las mejores tiendas del mundo con prestigiosa reputación. También el arte culinario es lo mejor para los paladares más exigentes se complacen en Cancún con tales opciones de alimentos variados de los establecimientos de comida rápida a los mejores restaurantes</td>
                
                </tr>
                 </table>
                 <br/>
    <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours6.jpg" width="198" height="137"/></td>
				<td  style="width:65%;text-align:justify;  font-size:12px;"><strong>Cancún Museo Maya:</strong><br/>
				Después de seis años de trabajo, el Museo Maya abrió en Cancún, diseñado como un gran reservorio de esta cultura antigua, una de las más reconocidas en el mundo. De 1964 a 1987 se inauguró el Museo Nacional de Antropología y el Templo Mayor, y el Instituto Nacional de Antropología e Historia (INAH).</td>                
                </tr>
                 </table>
                 <table style="width:100%;">
			<tr>
				<td style="width:65%;text-align:justify;  font-size:12px;">
                Cancún Museo Maya cuenta con tres salas de exposición de 1.350 m² cuadrados. Dos de ellos acogen exposiciones permanentes y temporales de nivel nacional e internacional. La visita al museo se inicia con los restos óseos de hasta 14.000 años de antigüedad, descubiertos en los últimos doce años en Tulum en cuevas submarinas, espacios que ofrecen grandes contribuciones a la investigación sobre la llegada del hombre en el continente americano.</td>
                <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours7.jpg" width="198" height="137"/></td>
                </tr>
                 </table> 
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Incluye:</u></strong></td>
            </tr>
            <tr>
                <td style="width:100%;">&nbsp;• Supervisión CTA Cancún</td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">• Servicio durante 4 horas</td>             
            </tr>
            <tr>
                <td style="width:100%;">&nbsp;• El transporte en autobús de lujo con aire acondicionado</td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">• Agua embotellada fría y toallas húmedas desechables.</td>
           </tr>
            <tr>
                <td style="width:100%;">&nbsp;• Conductor profesional</td>
           </tr>
           <tr>
                <td style="width:100%;text-align:left;">• Guía turístico bilingüe licenciado por el Gobierno</td>
          </tr>
          <tr>
                <td style="width:100%;text-align:left;">• Entrada al Museo Maya</td>
          </tr>              
</table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Duración: </u></strong>4 horas</td>
            </tr>                      
</table>
<br/>
   <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;"><strong>Transportación Hotel – City Tour – Restaurant – Hotel</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:34%; border: 0.3px">Van hasta 8 pax</td>
                 <td style="width:33%; border: 0.3px">$ 610.00</td>
                  <td style="width:33%; border: 0.3px">Por unidad </td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px">Motor coach Max. 53pax</td>
                 <td style="width:33%; border: 0.3px">$ 1,150.00</td>
                  <td style="width:33%; border: 0.3px">Por unidad</td>
        </tr>        
    </table>
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 34:
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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>EXOTIC RIDES</strong></td>
		</tr>
		</table>    
                 <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/exotic_rides.png" width="198" height="137"/></td>
				<td style="width:65%;text-align:justify;  font-size:13px;">
					Situado en el centro de Cancún, la pista privada expande en más de 10 hectáreas de terreno, con la hermosa Laguna Nichupté de Cancún como telón de fondo.<br/>
					Exotic Rides México diseñó y construyó un edificio de 15,000 pies cuadrados que encierra un área de usos múltiples,
					Lobby y recepción, Video-equipada aula, Lounge, Cafetería, Restaurante, Sala VIP para eventos privados, y una tienda de recuerdos. Junto a este edificio se
					encuentra la zona de Boxes y el taller con espacio suficiente para 20 de los Exotic Rides Mexico Super Cars y 30 emocionantes y rápidos Go Karts.</td>
			</tr>
		</table>                 
                 <br/>
                 <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Track day:</u></strong></td>
            </tr>
            <tr>
                <td  style="width:100%;">&nbsp;• Coches de marca con el logotipo de su empresa.</td>                             
            </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Clase teórica sobre la conducción deportiva.</td>                
        </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Dos vueltas de reconocimiento con un instructor. </td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Cinco vueltas para conducir uno de los coches (Ferrari 360, Ferrari F430, Ferrari 612, Audi R8, Mercedes-Benz SLS AMG y Lamborghini Gallardo)</td>
        </tr>              
</table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:34%; border: 0.3px">Track Day</td>
                 <td style="width:33%; border:0.3px">US $350.00</td>
                  <td style="width:33%; border:0.3px">Por persona</td>
        </tr>                        
    </table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:60%;"><strong>GO KARTS:</strong></td>
        </tr>
        <tr>
                <td style="width:65%;">Carrera de 10 personas durante 15 minutos en la pista de carreras; muestran sus habilidades como piloto profesional. Luchando para llegar a la final y convertirse en el equipo ganador.</td>
                 <td style="width:10%; border:0.3px"><strong>Go Karts</strong></td>
                  <td style="width:10%; border:0.3px">US $20.00</td>
                  <td style="width:15%; border:0.3px">Por persona</td>
        </tr>
                            
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:65%;"><strong>HOT LAPS:</strong></td>
        </tr>
        <tr>
                <td style="width:65%;">&nbsp;•Los clientes serán acompañados de uno de los conductores profesionales en un intento de romper el récord de la pista.<br/>
                &nbsp;• Elige entre uno de los coches increíbles (Ferrari 360, Ferrari 430, Audi R8, Mercedes-Benz SLS AMG o el Lamborghini Gallardo)<br/>
                &nbsp;• Tres vueltas como acompañante en uno de los coches.</td>
                 <td style="width:10%; border:0.3px"><strong>Hot Laps</strong></td>
                  <td style="width:10%; border:0.3px">US $85.00</td>
                  <td style="width:15%; border:0.3px">Por Persona</td>
        </tr>
                            
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;"><strong>Costo transportación Cancun Hoteles – Exotic Rides Locación – Cancun Hoteles:</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:25%; border:0.3px">Motor coach</td>
                 <td style="width:25%; border:0.3px">US $660.00</td>
                  <td style="width:25%; border:0.3px">Por unidad, viaje redondo</td>
                  <td style="width:25%; border:0.3px">Hasta 45 pax </td>
        </tr>
        <tr>
                <td style="width:25%; border:0.3px">Vans</td>
                 <td style="width:25%; border:0.3px">US $ 120.00</td>
                  <td style="width:25%; border:0.3px">Por unidad, viaje redondo</td>
                  <td style="width:25%; border:0.3px">Hasta 1-10 pax </td>
        </tr>                       
    </table>
    <table style="width:100%; text-align:center">
			<tr>
            <td width="100%" style="text-align:center;">*el tipo de cambio es la que aplica el proveedor el día de la reservación</td>
				
 </tr>
 </table>
    <br/>
    <table style="width:100%; text-align:center">
			<tr>
            <td width="100%" style="text-align:center;"><img src="../img/activities/exotic_rides2.png" width="450" height="200"/></td>
				
 </tr>
 </table>
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 35:
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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>THE LAST PIRATE NIGHT </strong></td>
		</tr>
		</table>    
                 <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/pirate_night.png" width="198" height="137"/></td>
				<td style="width:65%;text-align:justify;  font-size:13px;">
				“Disfruta de una espectacular batalla entre Piratas en el mar Caribe”. Esta aventura se lleva a cabo a bordo de nuestro galeón, donde un grupo de locos piratas brindarán el mejor entretenimiento, mucha diversión y un excelente servicio.<br/>
				<br/>Mientras navegamos a bordo de un impresionante Galeón por el Mar del Caribe Mexicano, los participantes podrán de una suculenta cena servida, acompañada de un buffet con diferentes guarniciones.</td>
                </tr>
                 </table>                 
                 <br/>
                 <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Incluye:</u></strong></td>
            </tr>
            <tr>
                <td style="width:100%;">&nbsp;•	Travesía por el mar Caribe, saliendo del embarcadero de Cancún</td>                              
            </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Show</td>                
        </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Cena</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Barra Libre Por 3 hrs.</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Música y mucha diversión</td>                
        </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Horarios: </td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Operación:  todas las noches de Lunes a Domingo</td>
        </tr>               
</table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>No incluye:</u></strong></td>
            </tr>
            <tr>
                <td  style="width:100%;">&nbsp;• Transportación al lugar (se cotiza aparte</td>                 
  </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Bebidas internacionales, estas son a consumo.</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	IVA y servicio.</td>
        </tr>                             
</table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:100%;">*Para grupos se puede reservar un área  en el Barco o chartear el Galeón, de acuerdo a las necesidades del grupo y  sujeto a disponibilidad.</td>
                 
        </tr>                        
    </table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:55%;"><strong><u>Precio:</u></strong></td>
        </tr>
        <tr>
                <td style="width:55%; border:0.3px">Menú Premium (Mar y Tierra, Langosta y Filete mignon)</td>
                 <td style="width:15%; border:0.3px">US$80.00 </td>
                  <td style="width:15%; border:0.3px">Por persona</td>
        </tr>
        <tr>
                <td style="width:55%; border:0.3px">Menú regular</td>
                 <td style="width:15%; border:0.3px">US$78.00 </td>
                  <td style="width:15%; border:0.3px">Por persona</td>
        </tr>
        <tr>
                <td style="width:55%; border:0.3px">Menu vegetariano</td>
                 <td style="width:15%; border:0.3px">US$39.00  </td>
                  <td style="width:15%; border:0.3px">Por persona</td>
        </tr>
        <tr>
                <td style="width:55%; border:0.3px">Menú para niños</td>
                 <td style="width:15%; border:0.3px">US$78.00 </td>
                  <td style="width:15%; border:0.3px">Por persona</td>
        </tr>
         <tr>
                <td style="width:55%; border:0.3px">Cuota por muellaje</td>
                 <td style="width:15%; border:0.3px">US$10.50 </td>
                  <td style="width:15%; border:0.3px">Por persona mayor a 5 años</td>
        </tr>                
    </table>
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 36:
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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XOXIMILCO</strong></td>
		</tr>
		</table>    
                 <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/xoximilco.png" width="198" height="137"/></td>
				<td style="width:65%;text-align:justify;  font-size:12px;">
					Tour Xoximilco te lleva a este mágico lugar lleno de fiesta, música y tradición en el ambiente, capta la
					imaginación de todo aquel que lo visita, al evocar el recuerdo vivo del México de la época de oro.
					En este lugar conviven el pasado y el presente entre festejos, amigos, romance, naturaleza y sabores que dan identidad a este país.
					Adornadas con motivos de los 32 Estados de México, las famosas trajineras que se utilizaron para transporte de flores,
					frutas y verduras cosechadas en las chinampas, hoy son transporte y gozo para propios y ajenos, quienes buscan en Xoximilco
					un momento único que solo se podrá vivir aquí en Cancún, en su versión moderna del legendario paseo por los canales,
					con el colorido y el encanto de esos momentos de magia en nuestros recuerdos. Una verdadera fiesta mexicana, flores,
					canciones de nuestra tierra, sabores que llegan al alma; un reencuentro con otro tiempo que se vive en este nuevo atractivo tan mexicano.</td>
                </tr>
                 </table>  
                 <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Incluye: </u></strong></td>
            </tr>
            <tr>
                <td style="width:100%;">&nbsp;•	Recorrido nocturno a bordo de una trajinera en Cancún </td>                             
            </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Cena degustación con lo mejor de la gastronomía mexicana </td>                
        </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Barra libre de tequila, cerveza, aguas frescas y refrescos Diferentes grupos de música típica mexicana en vivo como: mariachi, grupo bolero, grupo jarocho y marimba.</td>
        </tr>                               
</table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:100%;"><strong>Duración aproximada: </strong>3 horas</td>                 
        </tr>                        
</table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Itinerario:  </u></strong></td>
            </tr>
            <tr>
                <td style="width:50%;">Participantes listos en el lobby:</td>
                <td style="width:50%;">&nbsp;07:30 P.M.</td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">Salida del hotel:</td>
                <td style="width:50%;">&nbsp;07:45 P.M. </td>                
        </tr>
        <tr>
                <td style="width:50%;">Llegada a Xoximilco:</td>
                <td style="width:50%;">&nbsp;08:15 P.M.</td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">Recorrido en trajinera y cena:</td>
                <td style="width:50%;">&nbsp;8:30 P.M.-11:00 P.M.</td>                
        </tr>
        <tr>
                <td style="width:50%;">Salida de Xoximilco:</td>
                <td style="width:50%;">&nbsp;12:15 P.M.</td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">Llegada al hotel:</td>
                <td style="width:50%;">&nbsp;12:45 P.M.</td>                
        </tr>                       
</table>
<br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Entradas:</u></strong></td>
            </tr>
            <tr>
                <td style="width:100%; ">&nbsp;• Dips (flor de calabaza, huitlacoche guisado, axiote, sikil pac, guacamole)</td>
            </tr>
            <tr>
                <td style="width:100%; ">&nbsp;• totopos</td>                 
 			</tr>
         <tr>
              <td style="width:100%; ">&nbsp;•	chicharrón</td>
            </tr>
            <tr>
                <td style="width:100%; ">&nbsp;• chapulines</td>
        </tr>
        <tr>
                <td style="width:100%; ">&nbsp;• charales</td>
        </tr>                   
</table>
<br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Primer servicio:</u></strong></td>
                <td style="width:50%;text-align:left;"><strong><u>Segundo servicio:</u></strong></td>               
            </tr>
            <tr>
                <td style="width:50%;">• Bolita de quesillo de Oaxaca</td> 
                <td style="width:50%;">• Pollo en mole</td>               
        </tr>
         <tr>
                <td style="width:50%;">• Ensalada de nopal</td>
                <td style="width:50%;">• Tamal de Camarón</td>
         </tr>
         <tr>
                <td style="width:50%;">• Ensalada mexicana</td>
                <td style="width:50%;">• Cecina con chorizo</td>
                </tr>
                 <tr>
                <td style="width:50%;text-align:left;">• Rollo de chaya relleno de queso crema</td>
                <td style="width:50%;">• Camarones en salsa de tamarindo</td>              
            </tr>
            <tr>
                <td style="width:50%;">• Ceviche Vallarta</td>
                <td style="width:50%;">• Pescado a la Talla</td>                
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;</td>
                <td style="width:50%;">• Tortita de Cochinita Pibil</td>                
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;</td>
                <td style="width:50%;">• Arroz a la mexicana</td>                
        </tr>                              
</table>   
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Tercer servicio:</u></strong></td>                
            </tr>
            <tr>
                <td style="width:100%;">&nbsp;•	Flan de elote</td>
                                
  </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Dulce de leche</td>
                
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Cocada</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Jamoncillo de piñón</td>
                
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Alegrías</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Obleas</td>                
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Chocolate de metate de Oaxaca</td>
        </tr>                     
</table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Recomendaciones: </u></strong></td>
            </tr>
            <tr>
                <td style="width:100%;">&nbsp;•	Niños a partir de 5 años, Trae ropa cómoda, Reserva con anticipación</td>
            </tr>
            <tr>
                <td style="width:100%;">&nbsp;•	No olvides traer efectivo para llevarte a casa un souvenir y las fotos de tu visita</td>
            </tr>
            <tr>
                <td style="width:100%;">&nbsp;•	Trae repelente libre de químicos</td>
            </tr>          
</table>
<!--<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
          <tr>
            <td style="width:34%;"><strong><u>Costo:</u></strong></td>
          </tr>
          <tr>
            <td style="width:34%; border: 0.3px">Tour Xoximilco</td>
            <td style="width:33%; border: 0.3px">US$105.00</td>
            <td style="width:33%; border: 0.3px">Por persona  </td>
          </tr>          
</table>-->    
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 37:
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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>VIDA NOCTURNA EN CANCUN</strong></td>
		</tr>
		</table>  
        <table style="width:100%;">
			<tr>
				<td style="width:100%;text-align:justify;  font-size:13px;">
 La vida nocturna en Cancún es famosa por su intensidad y variedad de barras tranquilas, bus animados con pantallas para televisarlo,
 salones, clubs coloridos con el entretenimiento en vivo que pulsa en los ritmos de la música latino-del Caribe a las discotecas que
 ofrecen lo último en audio y video con las bandas en vivo y la música que está siempre a la vanguardia con los 40 mejores del mundo.</td>
                </tr>
                 </table>
                 <br/>  
                 <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/nightlife.png" width="198" height="137"/></td>
				<td style="width:65%;text-align:justify;  font-size:13px;">
				<strong>The City </strong>tiene tres áreas principales, incluyendo la disco, un salón y una terraza con barra. “El salón” está situado dentro del club, y provee de un atmosfera intima, la capacidad es para 200 personas. 
<br/>
<br/>
<strong>The City </strong>es el último complejo del entretenimiento en Cancún, fiestas privadas que van desde 5 hasta 5000. Disfruta de cualquier ocasión con excelente servicio en el área VIP, música vibrante y mucha diversión.
The city ofrece a los huéspedes un variedad de entretenimiento.</td>
                </tr>
                 </table>                 
                 <br/>
                 <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
          <tr>
            <td style="width:34%;"><strong><u>costo </u></strong></td>
          </tr>
          <tr>
            <td style="width:34%; border:0.3px">Barra libre nacional y cover</td>
            <td style="width:33%; border:0.3px">Lunes a Viernes</td>
            <td style="width:33%; border:0.3px">&nbsp;</td>
          </tr>
          <tr>
            <td style="width:34%; border:0.3px">Barra libre nacional y cover</td>
            <td style="width:33%; border:0.3px">Sábado</td>
            <td style="width:33%; border:0.3px">&nbsp;</td>
          </tr>
                    
</table>
<br/>
<table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/nightlife2.png" width="198" height="137"/></td>
				<td style="width:65%;text-align:justify;  font-size:13px;">
				<strong>Dady’O </strong>es sin duda el mejor club nocturno de México. Su arquitectura construye un misterio y una fascinante caverna
				en lo más profundo del mar Caribe que después de millones de años emerge al corazón de Cancún, México. Se caracteriza
				por ofrecer estándares de alta calidad y tiene una barra de alimentos y una terraza con barra.
				El antro tiene una capacidad de 2500 personas, ofreciendo la posibilidad de hacer eventos privados.</td>
                </tr>
                 </table>
                 <br/>
                 <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
          <tr>
            <td style="width:34%;"><strong><u>Costo:</u></strong></td>
          </tr>
          <tr>
            <td style="width:34%; border:0.3px">Barra libre nacional y cover</td>
            <td style="width:33%; border:0.3px">&nbsp; </td>
            <td style="width:33%; border:0.3px">Lunes, Jueves Viernes y sábado</td>
          </tr>                    
</table>
<br/>
<table style="width:100%;">
			<tr>            
				<td style="width:100%;text-align:justify;  font-size:13px;">
				<strong>COCO BONGO”</strong> Aquí en el Vegas Showtime comienza la fiesta. Tiene una capacidad de 1800 personas; el lugar es de múltiples niveles,
				la locación se encuentra en el corazón de la zona hotelera y una noche de rock & roll y bandas de salsa hacen de Coco Bongo Cancún
				el más emocionante y único club. Agrega también la extraordinaria mezcla de música de los años 70´s y 80´s, danza y hip hop; es fácil
				porque es el número 1, en la pista de baile siempre está lleno.</td>
                </tr>
                </table>
                <table style="width:100%;">
                <tr>
                <td width="15%" style="text-align:center;"><img src="../img/activities/nightlife3.png" width="198" height="137"/></td>
				<td style="width:65%;text-align:justify;  font-size:13px;">
                <strong>Coco Bongo</strong> ofrece el más nuevo y último entretenimiento en audio y video, incluyendo una pantalla de video enorme, las burbujas de jabón,
                los globos, el confeti, las flámulas y mucho, mucho más.
                </td>                
                </tr>
                 </table>
                 <br/>
                 <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
          <tr>
            <td style="width:34%;"><strong><u>Costo:  </u></strong></td>
          </tr>
          <tr>
            <td style="width:34%; border:0.3px">Barra libre nacional y cover</td>
            <td style="width:33%; border:0.3px">&nbsp; </td>
            <td style="width:33%; border:0.3px">Lunes a Miércoles</td>
          </tr> 
          <tr>
            <td style="width:34%; border:0.3px">Barra libre nacional y cover</td>
            <td style="width:33%; border:0.3px">&nbsp;</td>
            <td style="width:33%; border:0.3px">Jueves a Domingo</td>
          </tr>                 
</table>    
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
case 38:
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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>TEAM BUILDING</strong></td>
		</tr>
		</table>  
                 <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/team_building.png" width="198" height="137"/></td>
				<td style="width:65%;text-align:justify;  font-size:13px;">
					Our motivating games and interactive events will challenge the guest’s creativity and team work with friendly and competitive atmosphere. Here are just some of the options available.</td>
                </tr>
                 </table>                 
                 <br/>
                  <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">            
            <tr>
                <td style="width:100%;">&nbsp;•	Build your own boat </td>
                                 
  </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Build your own car</td>                
        </tr>
         <tr>
                <td style="width:100%;">&nbsp;•	Beach Olympics games </td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Amazing Race</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Scavenger Hunt</td>                
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;•	Enigmatic  Rally at Xcaret</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Naturally sustainable</td>                
        </tr>                       
</table><br/>
                 <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
          
          <tr>
            <td style="width:100%;">Customized options</td>
          </tr>          
</table>  
        </page>';
		$topdf->writeHTML($html2);
	}
	break;
	        }
}

$topdf->Output();

?>
