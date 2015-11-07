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

//Agrega la portada
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

switch ($paq)
{
    case 1:
    {
        $actividad= '
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
$topdf->AddPage();
$topdf->writeHTML($html);
$topdf->AddPage();
$topdf->writeHTML($html);
//$topdf->writeHTML($actividad);
$topdf->Output();
//$path.$filename,'F'
//echo "http://".$_SERVER['HTTP_HOST']."/docs/".$filename;
?>
