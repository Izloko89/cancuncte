<?php
session_start();
setlocale(LC_ALL, "");
setlocale(LC_ALL, "es_MX");
include_once("datos.php");
require_once('../clases/html2pdf.class.php');
include_once("func_form.php");
$emp = $_SESSION["id_empresa"];

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
    $sql = "SELECT
        t1.id_cotizacion,
        t1.fecha,
        t1.fechaevento,
        t1.fechamontaje,
        t1.fechadesmont,
        t1.id_cliente,
        t1.id_tipo,
        t2.nombre,
        t3.direccion,
        t3.colonia,
        t3.ciudad,
        t3.estado,
        t3.cp,
        t3.telefono
    FROM cotizaciones t1
    LEFT JOIN clientes t2 ON t1.id_cliente=t2.id_cliente
    LEFT JOIN clientes_contacto t3 ON t1.id_cliente=t3.id_cliente
    WHERE id_cotizacion=$id;";
    $res = $bd->query($sql);
    $res = $res->fetchAll(PDO::FETCH_ASSOC);
    $evento = $res[0];
    $cliente = $evento["nombre"];
    $idTipo = $evento["id_tipo"];
    $telCliente = $evento["telefono"];
    $domicilio = $evento["direccion"] . " " . $evento["colonia"] . " " . $evento["ciudad"] . " " . $evento["estado"] . " " . $evento["cp"];
    $fecha = $evento["fecha"];
    $fechaEve = $evento["fechaevento"];
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

//var_dump($articulos);

switch ($idTipo)
{
    case 1:
    {
        $html='
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
    <br/>
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
        </table>
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
</page>
';
    }
    break;
    case 52:
    {
        $html='
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
    <br/>
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
        </table>
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
</page>
';
    }
    break;
    case 36:
    {
$html='
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
    case 37:
    {
        $html='
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
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td width="25%" style="text-align:center;"><img src="../img/activities/xplor_fuego.jpg" width="234" height="142"/></td>
            <td style="width:60%;text-align:justify;  font-size:12px;">
                Journey deep into the darkness of the jungle and be part of a new adventure where the night and fire will be your best companions. Follow your instincts and ignite your life with Xplor Fuego; a challenge in the darkness like you never imagined.</td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:95%;">&nbsp;• Enjoy a 3.1-mile circuit to drive with Amphibious Vehicles (only people 18 or older may drive).</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp;• 580 yards of underground caves to paddle on a Raft.  A nine zip line circuit.</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp;• Swim along 430 yards of crystal-clear water in the Stalactite River Swim. Equipment: helmet and life jacket</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:45%;text-align:left;"><u><strong>included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:45%;">&nbsp;• CTA Staff to coordinate the service.</td>
            <td style="width:45%;text-align:left;">&nbsp;• Locker for two</td>
        </tr>
        <tr>
            <td style="width:45%;text-align:left;">&nbsp;• Round trip transportation</td>
            <td style="width:45%;text-align:left;">&nbsp;• Dressing rooms and bathrooms</td>
        </tr>
        <tr>
            <td style="width:45%;">&nbsp;• Admission to Xplor Park Monday through Saturday from 5:30 a 10:30 p. m.</td>
            <td style="width:45%;text-align:left;">&nbsp;• Resting areas</td>
        </tr>
        <tr>
            <td style="width:45%;text-align:left;">&nbsp;• Equipment (life jacket, helmet, harness, paddles, raft, amphibian vehicle for two).</td>
            <td style="width:45%;text-align:left;">&nbsp;• Buffet and unlimited beverages (coffee, hot chocolate and fresh fruit flavored water).</td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:50%;text-align:left;"><u><strong>Not included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:50%;">&nbsp;• $20.00 per Person as a deposit for the lockers.</td>
        </tr>
        <tr>
            <td style="width:50%;text-align:left;">&nbsp;• Gratuities US $ 3.00 per person</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:95%;text-align:left;"><u><strong>Suggested itinerary:</strong></u></td>
        </tr>
        <tr>
            <td style="width:95%;">Participants ready at the hotel lobby:</td>
        </tr>
        <tr>
            <td style="width:95%;">Departure from the hotel:</td>
        </tr>
        <tr>
            <td style="width:95%;">Arrival at Xplor Fuego:</td>
        </tr>
        <tr>
            <td style="width:95%;">Departure from Xplor Fuego:</td>
        </tr>
        <tr>
            <td style="width:95%;">Arrival at the hotel:</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:95%;text-align:left;"><u><strong>***Cost Xplor Fuego (fire)</strong></u></td>
        </tr>
    </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;" border="0.5px">All Inclusive</td>
            <td style="width:25%;"border="0.5px">US $110.00</td>
            <td style="width:25%;"border="0.5px">Per person, plus taxes</td>
            <td style="width:25%;"border="0.5px">Minimum 35</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Recommendations:</strong></u></td>
        </tr>
        <tr>
            <td style="width:95%; text-align:justify;">Wear comfortable beachwear, water shoes, and extra change of clothes, swimsuit and towel. Sunscreen should be free of chemicals to be used in the park. If it contains any of these ingredients, it can be used within the Park: Benzophenone, Etilhexila, Homosalate, Octyl methoxycinnamate, octyl salicylate, Octinoxate Oxybenzone Methoxydibenzoylmethane Butyl.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Notes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">The minimum age to access Xplor is seven years. Children between 7 and 11 years are charged 50% of adult price.</td>
        </tr>
        <tr>
            <td style="width:100%;">You must present official ID at the box office of the Park.</td>
        </tr>
    </table>
</page>';
    }
    break;
    case 38:
    {
        $html='
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
        <br/>
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
        </table>
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
    }
    break;
    case 39:
    {
$html='
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
    <br/>
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
    </table>
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
    }
    break;
    case 3:
    {
        $html='
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
        <br/>
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
        </table>
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
    }
    break;
    case 40:
    {
        $html='
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
    <br/>
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
        </table>
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
    <br/>
    <table border="0" cellpadding="0" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:33%;text-align:left;"><u><strong>Cost:</strong></u></td>
            </tr>
            <tr>
                <td style="width:33%;" border="0.5px">Tour Tulum Xel-Ha all inclusive</td>
                <td style="width:33%;" border="0.5px">US$ 127.00</td>
                <td style="width:33%;" border="0.5px">Per person, min 35</td>
        </tr>
        </table>
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
$topdf->writeHTML($html);
$topdf->Output();
//$path.$filename,'F'
//echo "http://".$_SERVER['HTTP_HOST']."/docs/".$filename;
?>
