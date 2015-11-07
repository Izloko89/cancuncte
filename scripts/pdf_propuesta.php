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
    // para saber los datos del cliente  """"""abajo venia el campo razonf""""""""
    $sql="SELECT
        t1.id_evento,
        t1.nombre AS nombreEvento,
        date_format(t1.fecha, '%d/%m/%Y') As fecha,
        t1.fechaevento,
        date_format(t1.fechamontaje,'%d/%m/%Y') As fechamontaje,
        t1.id_cliente,
        t1.participantes,
        t1.hotel,
        t2.nombre,
        t2.compania,
        t3.direccion,
        t3.colonia,
        t3.ciudad,
        t3.estado,
        t3.cp,
        t3.telefono,
        t3.email
       
    FROM eventos t1
    LEFT JOIN clientes t2 ON t1.id_cliente=t2.id_cliente
    LEFT JOIN clientes_contacto t3 ON t1.id_cliente=t3.id_cliente
    LEFT JOIN clientes_fiscal t4 ON t1.id_cliente=t4.id_cliente
    WHERE id_evento=$id;";
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
    $fechaInicio=$evento["fecha"];
    $fechaFin=$evento["fechamontaje"];
    $participantes=$evento["participantes"];
    $hotel=$evento["hotel"];
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
    FROM eventos_articulos t1
    LEFT JOIN articulos t2 ON t1.id_articulo=t2.id_articulo
    WHERE t1.id_evento=$id;";
    $res = $bd->query($sql);
    $sql = "SELECT
        path
    FROM evento_imagen
    WHERE id_evento=$id;";
    $img = $bd->query($sql);
	$img = $img->fetchAll(PDO::FETCH_ASSOC);
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
         <td style="width:55%; text-align:left"><img src="../img/logo.png" style="width:150px;" /></td>
         <td style="width:45%; text-align:left; padding-bottom:5px;">
            <p style="margin:0;text-align:right;font-size:16px;"><img src="' . $img[0]["path"] . '" style="width:150px;" /></p>
         </td>
    </tr>
</table>
<br/>
<br/>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:20px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>CTA Cancún & Riviera Maya DMC <br/>
Propuestas de actividades para: &nbsp;
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
</strong>Cliente:'.$cliente.'</td>
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
    	<td style="width:20%;"><strong>Atención:&nbsp;</strong></td>
        <td style="width:30%;">'.$contactoCliente.'</td>
      <td style="width:20%;"><strong>Programa:&nbsp;</strong></td>
      <td style="width:30%;">'. $nombreEvento.'</td>
    </tr>
    <tr>
    	<td style="width:20%;"><strong>Título:&nbsp;</strong></td>
        <td style="width:30%;"> </td>
        <td style="width:20%;"><strong>Compañía:&nbsp;</strong></td>
        <td style="width:30%;">'.$comCliente.' </td>
    </tr>
     <tr>
     	<td style="width:20%;"><strong>Fecha Inicio:&nbsp;</strong></td>
        <td style="width:30%;">'.$fechaInicio.'</td>
        <td style="width:20%;"><strong>Fecha Fin:&nbsp;</strong></td>
        <td style="width:30%;">'.$fechaFin.'</td>
        
    </tr>
     <tr>
     	<td style="width:20%;"><strong>Participantes:&nbsp;</strong></td>
        <td style="width:30%;">'.$participantes.'</td>
    	<td style="width:20%;"><strong>E-mail:&nbsp;</strong></td>
        <td style="width:30%;">'.$emailCliente.'</td>        
    </tr>
    <tr>
    	<td style="width:20%;"><strong>Hotel:&nbsp;</strong></td>
        <td style="width:30%;">'.$hotel.'</td>
    	<td style="width:20%;"><strong>Tel: &nbsp;</strong></td>
        <td style="width:30%;">'. $telCliente.' </td>
        
    </tr>
</table>
<br>
<textarea class=estilotextarea4 cols="71" rows="5" style=" padding:0 20px; color:#FFF; text-align:justify"><br/>TODAS LAS TARIFAS DE ESTA PROPUESTA ESTÁN EN DÓLARES AMERICANOS, PRECIOS NETOS NO COMISIONABLES. EL COSTO DE TRANSPORTACIÓN NO INCLUYE CUOTAS DE AEROPUERTO NI CASETAS DE AUTOPISTAS Y CARRETERAS.
 TODOS LOS PRECIOS ESTÁN SUJETOS AL 16% DE IVA Y 5% DE CARGO POR SERVICIO PARA EL STAFF OPERATIVO DE CTA.</textarea>
<br/>
<br/>
<div style="width:100%; padding:0 20px; font-size:12px; background-color:#099; color:#FFF; text-align:center"><strong>TOURS Y ACTIVIDADES</strong></div>
<div style="width:100%; padding:0 20px; font-size:12px; text-align:justify">Todas las actividades y eventos serán supervisados por tiempo completo por nuestro personal altamente capacitado y bilingüe (español-inglés) para garantizar que nuestros clientes reciban el mismo servicio y excelentes estándares de calidad que reciben de CTA Cancún & Riviera Maya DMC.</div><br/>
<div style="width:100%; padding:0 20px; font-size:12px; text-align:justify">Todos nuestros servicios utilizan vehículos nuevos y de lujo que están completamente equipados con aire acondicionado y comunicación por radio, agua embotellada fría y toallas húmedas disponibles; los conductores bien uniformados y capacitados, los autobuses están totalmente equipados con monitores de T, sistemas de micrófono, WC, asientos reclinables, con pasillos anchos y ventanas panorámicas, así como con buen personal bilingüe y guías turísticos autorizados por el gobierno.</div>
<br/>
<div style="width:100%; padding:0 20px; font-size:12px; text-align:justify">**** Todas las actividades se cotizan en dólares y se utiliza el tipo de cambio del día, que aplica cada proveedor.</div>
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XPLOR</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/xplor.jpg" width="162" height="133"/></td>
<td style="width:70%;text-align:justify;  font-size:12px;">
Una aventura que comenzó hace 65 millones de años. Xplor ... más allá de la superficie de 65 millones de años, cuando los dinosaurios habitaban el planeta, un asteroide golpeó la Península de Yucatán trayendo fin a una era.<br/>
¡Prepárate para explorar los 4 elementos a través de las actividades de aventura más intensas que se reunieron en el mismo lugar: </td>
        </tr>
        </table>
        <br/>
         <table style="width:100%;">
        <tr>
            <td style="width:90%; text-align:justify; font-size:12px;"> • Los vehículos con tracción en imparables anfibios y desalentar las fronteras entre la selva, el agua, las rocas y grutas. </td>
        </tr>
        <tr>
            <td style="width:90%; text-align:justify; font-size:12px;">• Nada a través de las rutas más espectaculares y misteriosas, rodeado de impresionantes estalactitas y estalagmitas.</td>
        </tr>
        <tr>
            <td style="width:90%; text-align:justify; font-size:12px;">• Embárcate en una balsa y rema con las manos sobre las aguas cristalinas entre las antiguas formaciones rocosas.</td>
        </tr>
        <tr>
            <td style="width:90%; text-align:justify; font-size:12px;">• Vuela a través de más de 2 millas de la libertad y la altura de nuestras 11 tirolesas.</td>
        </tr>
        <tr>
            <td style="width:90%; text-align:justify; font-size:12px;">• Mantén la hazaña en los niveles superiores, con un nutritivo y ligero buffet especialmente diseñado para recargar tu energía.</td>
        </tr>
    </table>
         <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">• Representante de CTA Cancún.  </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Transportación de ida y regreso del hotel en un autobús de lujo con aire acondicionado y sanitarios</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Agua fría embotellada y toallas húmedas desechables (únicamente en  la transportación)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Entradas al parque eco-arqueológico Xplor</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Comida buffet con aguas frescas, regaderas, baños.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Impuestos.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluido:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• US$20.00 por persona como depósito para lockers.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Nado con Delfines y actividades con costo adicional.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Propinas US$ 5.00 por persona</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:50%;text-align:left;"><u><strong>Itinerario sugerido:</strong></u></td>
        </tr>
        <tr>
            <td style="width:25%;">Participantes listos en el lobby:</td>
            <td style="width:25%;">08:15 A.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Salida del hotel:</td>
            <td style="width:25%;">08:30 A.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Llegada estimada al Parque Xplorr:</td>
            <td style="width:25%;text-align:left;">10:45 A.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Comida:</td>
            <td style="width:25%;text-align:left;">12:30 P.M.-01:30 P.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Tiempo libre:</td>
            <td style="width:25%;text-align:left;">01:30 P.M.-04:30 P.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Salida del Parque Xplor:</td>
            <td style="width:25%;text-align:left;">05:00 P.M.</td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;">Llegada al hotel:</td>
            <td style="width:25%;text-align:left;">06:30 P.M.</td>
        </tr>
        </table>
        <!--<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:20%;text-align:left;"><strong>***Costo temporada baja</strong></td>
        </tr>
            </table>
            <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
                <tr>
            <td style="width:25%;"border="0.5">Paquete todo incluido</td>
            <td style="width:25%;"border="0.5">US $128.00</td>
            <td style="width:25%;"border="0.5">Por  persona</td>
            <td style="width:25%;"border="0.5">Mínimo 35 pax</td>
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
            <td style="width:25%;text-align:left;"><strong>***Costo temporada alta</strong></td>
        </tr>
    </table>
        <table cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;" border="0.5">Paquete todo incluido</td>
            <td style="width:25%;"border="0.5">US $145.00</td>
            <td style="width:25%;"border="0.5">Por  persona</td>
            <td style="width:25%;"border="0.5">Mínimo 35 pax</td>
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
            <td style="width:25%;text-align:left;"><strong>Jul 01 -  Aug 16, 2015</strong></td>
        </tr>
        <tr>
            <td style="width:25%;text-align:left;"><strong>Dic 25  - Ene 03  2016</strong></td>
        </tr>
    </table>-->
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>Recomendaciones:</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;">• Usar zapatos cómodos y ropa casual, llevar traje de baño, toalla, sombrero, camera y lentes de sol </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Únicamente bloqueador biodegradable está permitido en Xplor</td>
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
<table style="width:100%;">
<tr>
<td width:"15%" style="text-align:center;"><img src="../img/activities/xcaret.jpg" width="200" height="120"/></td>
<td style="text-align:justify;  font-size:12px; width:66%;">“Paraíso sagrado de la Naturaleza”, es el parque eco-arqueológico más famoso del mundo, con más de 53 actividades para disfrutar. Visite el Pueblo Maya, el aviario, el mariposario, la cueva de los murciélagos, el jardín botánico, el acuario del arrecife, nado a través de los ríos subterráneos, nado con delfines (opcional) y mucho más. Lo que durante el día es un parque de aventura en la selva, por la noche se convierte en un lugar místico, tradicional, de leyendas e historia con el show “México Espectacular”.</td>
</tr>
</table>

<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:95%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:95%;">&nbsp; &nbsp;• Representante de CTA Cancún.</td>
            </tr>
            <tr>
            <td style="width:95%;text-align:left;">&nbsp; &nbsp; • Transportación de ida y regreso en  autobús de lujo con aire acondicionado y sanitarios.</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp; &nbsp;• Agua fría embotellada y toallas húmedas desechables (únicamente en la transportación)</td>
            </tr>
            <tr>
            <td style="width:95%;text-align:left;">&nbsp; &nbsp;• Entradas al parque eco-arqueológico Xcaret</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">&nbsp; &nbsp;• Comida buffet con aguas frescas y café  ilimitado, durante la comida. </td>
            </tr>
            <tr>
            <td style="width:95%;text-align:left;">&nbsp; &nbsp;• Regaderas, baños, llantas flotadores, chalecos salvavidas para los ríos y bolsos para guardar sus pertenencias durante el corrido, hamacas, sillas de playa reclinables.</td>
        </tr>
        </table>
        <br/>
 <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluido:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp; &nbsp;• Toalla</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; &nbsp;• US$20.00 por persona como depósito para lockers.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; &nbsp;• Nado con Delfines y actividades con costo adicional.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; &nbsp;• Propinas US$5.00 por persona</td>
        </tr>
        </table>
        <br/>
         <!--<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:32%;text-align:left;"><strong>*Costo </strong></td>
            </tr>
            <tr>
                <td style="width:32%; border:0.3px;">Paquete Plus</td>
                 <td style="width:32%; border:0.3px;">$US 137.00  Por persona</td>
                  <td style="width:32%; border:0.3px;">Mínimo 35 pax</td>
              </tr>
        </table>-->
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><u><strong>Itinerario sugerido:</strong></u></td>
            </tr>
            <tr>
                <td style="width:25%;">Participantes listos en el lobby:</td>
                 <td style="width:25%;">08:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Salida del hotel:</td>
                 <td style="width:25%;">08:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Llegada a Xcaret:</td>
                 <td style="width:25%;">10:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Tour guiado en Xcaret:</td>
                 <td style="width:25%;">10:45 A.M.-12:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Comida:</td>
                 <td style="width:25%;">12:30 P.M.-01:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Tiempo libre:</td>
                 <td style="width:25%;">01:30 P.M.-06:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Show:</td>
                 <td style="width:25%;">07:00 P.M.-09:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:25%;">Salida de Xcaret:</td>
                 <td style="width:25%;">09:30 P.M.</td>
        </tr>
         <tr>
                <td style="width:25%;">Llegada al hotel:</td>
                 <td style="width:25%;">11:00 P.M.</td>
        </tr>
        </table>
        <br/>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>Recomendaciones:</strong></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp; &nbsp;•	Usar zapatos cómodos y ropa casual, llevar traje de baño, toalla, sombrero, camera y lentes de sol  </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; &nbsp;• Traer efectivo para compras, fotos o extra actividades.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; &nbsp;• Únicamente bloqueador biodegradable está autorizado en Xcaret (también disponible en sus tiendas).  </td>
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
                Siente la intensidad de la noche deslizándote en increíbles tirolesas, maneja esta tierra en vehículos anfibios entre misteriosas cavernas y sumérgete en las refrescantes aguas de un río subterráneo.
                 Descubre milenarios paisajes y un mundo nuevo lleno de aventuras en el corazón de la Riviera Maya.
                  Xplor Fuego se convierte en una inigualable aventura cuando la selva cobra vida al caer la noche para atraparte en medio de la oscuridad.</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;">&nbsp;• 530 metros de cavernas subterráneas para recorrer en Balsas.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•	Un circuito de nueve Tirolesas</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•	5.5 km de recorrido por la selva en Vehículos Anfibios.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•	350 metros de Nado en Río de Estalactitas.</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:45%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:45%;">&nbsp;• CTA representante</td>
            <td style="width:50%;text-align:left;">&nbsp;• Equipo incluido (Tirolesas: casco y arnés, Vehículos Anfibios: casco y vehículo anfibio para 2 personas.</td>
        </tr>
        <tr>
            <td style="width:45%;text-align:left;">&nbsp;• Transportación y da y vuelta</td>
            <td style="width:50%;text-align:left;">&nbsp;• Casillero para 2 personas.</td>
        </tr>
        <tr>
            <td style="width:45%;">&nbsp;• Comida tipo buffet, pan dulce y galletas.</td>
            <td style="width:50%;text-align:left;">&nbsp;• Áreas de descanso, vestidores y baños.</td>
        </tr>
        <tr>
            <td style="width:45%;text-align:left;">&nbsp;• Bebidas ilimitadas (café, atole, champurrado y aguas frescas).</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;">&nbsp;• US$ 20.00 por Persona como depósito para los armarios.</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp;• Propinas US$ 3.00 por persona</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:60%;text-align:left;"><u><strong>Itinerario sugerido:</strong></u></td>
        </tr>
        <tr>
            <td style="width:60%;">Participantes listos en el lobby:</td>
        </tr>
        <tr>
            <td style="width:60%;">Salida a Xplor Fuego:</td>
        </tr>
        <tr>
            <td style="width:60%;">Llegada a Xplor Fuego:</td>
        </tr>
        <tr>
            <td style="width:60%;">Salida al hotel:</td>
        </tr>
        <tr>
            <td style="width:60%;">Llegada al hotel:</td>
        </tr>
    </table>
    <!--<br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;text-align:left;"><u><strong>Costo Xplor Fuego:</strong></u></td>
        </tr>
    </table>
        <table border="0.3" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;">Todo incluido </td>
            <td style="width:25%;">US $110.00</td>
            <td style="width:25%;">Por persona</td>
            <td style="width:25%;">Mínimo 35 pax</td>
        </tr>
    </table>-->
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:justify;"><u><strong>Recomendaciones:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">Usar ropa cómoda de playa, zapatos para agua, una muda de ropa extra, traje de baño y toalla. El protector solar deberá ser libre de químicos para poder ser utilizado en el Parque. Si éste contiene alguno de los siguientes ingredientes, no podrá ser utilizado dentro del Parque: Benzofenona, Etilhexila, Homosalato, Octil metoxicinamato, Octil salicilato, Octinoxato, Oxibenzona, Butil metoxidibenzoilmetano.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Notas:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify">La edad mínima para ingresar a Xplor es de siete años. Niños entre 7 y 11 años pagan 50% del precio de adulto. Es indispensable presentar identificación oficial en las taquillas del Parque.</td>
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
<td style="width:50%;text-align:justify;  font-size:13px;">
	Xenotes Oasis Maya es un viaje en el que descubrirá cuatro diferentes tipos de cenotes en un entorno natural,
	un oasis en medio de la selva donde se puede disfrutar la naturaleza al máximo, una actividad diferente en cada Xenote
	(Kayak, Escalada, Snorkel, Tirolesas e interior tubos) en un ambiente de diversión, donde los visitantes aprenden sobre
	el respeto a la tierra que alguna vez pisaron los antiguos mayas y las leyendas y el misticismo a través de los míticos
	guardianes de la selva.
</td>
</tr>
</table>

<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;">
            Los cenotes de la Península de Yucatán son formaciones geológicas que albergan los ríos subterráneos y piscinas cristalinas
de la selva cuyo origen tiene un largo proceso de miles de años. Pueden estar ubicados en su mayoría en esta región del mundo,
el sureste de México, por lo que son un tesoro natural en Cancún y Riviera Maya a los cuales tienes acceso a través de XENOTES OASIS MAYA TOUR.</td>
        </tr>
        </table>
        <br/>
 <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>K’áak’</strong></td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">K\'áak, es un cenote abierto, donde, ya sea en kayak o en cámaras de aire, se encuentra el atleta en su interior. Sólo necesitas mucha actitud y ganas de disfrutar de la naturaleza.
            <br/> Alux K\'áak se unirá a usted, sus reglas son fáciles de seguir: vive el momento y hacer las cosas con gran pasión.
Disfrute de la mezcla perfecta de la cultura y la aventura, a medida que avanza a través de uno de los sitios arqueológicos más impresionantes, con vistas al mar Caribe en Tulum.
            </td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    	<tr>
            <td style="width:15%;text-align:left;"><strong>Ha\'</strong></td>
        </tr>
        <tr>
            <td style="width:15%;"><img src="../img/activities/ha.jpg" width="211" height="142"/></td>
            <td style="width:65%;text-align:left;">Ha’ es un cenote caverna con un hermoso hábitat acuático.
             Disfrute de snorkeling en este Xenote y se sorprenderá por los jardines bajo el agua de lirios.
             El Alux Ha\'es tranquilo y sincero, le recomendamos que usted trate con cuidado las aguas, que
             es lo más sagrado para él, no tire basura y use protector solar adecuado </td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px; width:95%;">
        <tr>
            <td style="width:15%;text-align:left;"><strong>Lu´um</strong></td>
        </tr>
        <tr>
            <td style="width:65%;text-align:left;">Lu’um, es un cenote semi-abierto donde se puede practicar rappel,
             gritar, reír, disfrutar... déjate llevar y experimenta esta actividad inolvidable. El Alux Lu\'um gusta
             reírse y disfrutar de sus tierras, que son el punto de conexión entre él y la madre naturaleza, ayúdalo
             a cuidar de su casa y no tire cualquier tipo de basura, ya que esto lo molesta.</td>
            <td style="width:25%;"><img src="../img/activities/lum.jpg" width="211" height="142"/></td>
        </tr>
    </table>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;"><img src="../img/activities/lik.jpg" width="211" height="142"/></td>
            <td style="width:65%;text-align:justify;">Iik\', es un antiguo cenote donde se puede disfrutar de la tranquilidad de la selva y la adrenalina de volar alto. Oxigenar los pulmones y ser libre, ahora es cuándo. El Alux Iik \'está continuamente yendo y viniendo, observando la naturaleza desde la parte superior .</td>
        </tr>
    </table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>El Tour incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Transportación desde el Confort de su Hotel acompañado de un Guía especializado y un conductor. </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Café, chocolate y Pan Dulce de Bienvenida</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Aguas, Refrescos y Fruta de la estación. </td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp; •  Chocolate caliente, Café y Galletas de Avena en cada salida de Xenote excepto donde se sirva la comida. </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; • Picnic: Una selección energizante incluyendo Fussili,
             sopa de vegetales Barra fría con Carnes frías y quesos de marca Premium junto a Pan Rustico de granos
             y aderezos para preparar el platillo a su gusto acompañado de selección de ensaladas  Agua, Café y Vino o Cerveza</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; • Equipo: Chaleco salvavidas, equipo de snorkel, equipo para Rapelear, Kayak y Dona de Aire </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp; • Baños, vestidores y toallas.</td>
        </tr>
        </table>
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><u><strong>Itinerario:</strong></u></td>
            </tr>
            <tr>
                <td style="width:35%;">Participantes listos en el lobby:</td>
                 <td style="width:25%;">09:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Salida del hotel:</td>
                 <td style="width:25%;">09:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">LLegada a Xenotes Oasis Maya:</td>
                 <td style="width:25%;">11:30 A.M.-12:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:30%;">Comida:</td>
                 <td style="width:25%;">12:30 P.M.-01:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Salida de Xenotes Oasis Maya:</td>
                 <td style="width:25%;">06:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Llegada al hotel:</td>
                 <td style="width:25%;">08:00 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:90%;text-align:left;"><u><strong>Duración:</strong></u>Aproximadamente 9 horas desde su recolección hasta el regreso  al Lobby. </td><br/>
            </tr>
            <tr>
                <td style="width:25%;">Días de operación:<strong>Lunes a Sábado</strong></td>
        </tr>
        </table>
        <br/>
        <!--<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><u><strong>Costo:</strong></u></td>
            </tr>
            <tr>
                <td style="width:25%; border:0.3px">Todo  Incluido </td>
                 <td style="width:25%; border:0.3px">US $119.00</td>
                  <td style="width:25%; border:0.3px">Por persona, plus taxes</td>
                  <td style="width:25%; border:0.3px">Mínimo 32</td>
        </tr>
        </table>
        <br/>-->
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><u><strong>Favor de llevar:</strong></u></td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Zapatos acuáticos y toalla extra.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Solo usa Bronceador, protector solar y repelente Biodegradable. Evita usar Maquillaje o repelentes químicos que<br/> afectan el ecosistema se los cenotes. Usa solo bloqueadores libre de químicos .</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Ropa y Calzado Confortables..</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Traje de Baño .</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Playera extra.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • toalla.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Dinero para Fotos y recuerdos.</td>
        	</tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><u><strong>Recomendaciones importantes:</strong></u></td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Tener habilidades de natación básica. Tenga cuidado de la naturaleza, disfrute y aprenda de ella. Evite salir de los caminos para evitar un incidente con la fauna y la flora del lugar.
        		Si usted ve un animal evite tocar o alimentar, recuerda que están en su hábitat natural.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp; • Ducha antes de entrar al Xenote para proteger el hábitat.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">&nbsp;•	Actividades restringidas a las mujeres embarazadas, personas con problemas cardíacos, diabetes, epilepsia, asma, hipertensión y claustrofobia.</td>
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
            <td style="width:65%;text-align:justify;  font-size:12px;">
                La capital del Imperio Maya. Experimente lo fascinante y místico de la cultura Maya, considerada una de las culturas
                más avanzadas de América. Su guía lo llevará a través de esta ciudad que contiene cientos de estructuras tales como la
                “Pirámide de Kukulcan” y “El Juego de Pelota” más grande y preservado de México. El “Cenote del Sacrificio” fue reservado
                para rituales que involucraban a los humanos y al Dios de la Lluvia. </td>
        </tr>
    </table>  
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">• Representante de CTA Cancún</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Transportación en viaje redondo</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Agua fría embotellada y toallas húmedas desechables (únicamente en la transportación)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Guía de turistas profesional bilingüe (para los primeros 20 pasajeros</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Entradas al parque arqueológico y estacionamientos.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Comida buffet con platillos regionales e internacionales con una bebida incluida.</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">• Bebidas en el Restaurante (Pueden ser anexados a la Cuenta Maestra con 15 % por  Concepto de administración de CTA)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Propinas US $5.00 por persona</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Guía de turistas adicional US$150.00</td>
        </tr>
    </table>
    <br/>
    <!--<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:40%;text-align:left;"><u><strong>Costo</strong></u></td>
        </tr>
        <tr>
            <td style="width:40%; border:0.3px">Tour  con lunch buffet compartido </td>
            <td style="width:20%; border:0.3px"> US$85.00 </td>
            <td style="width:20%; border:0.3px">por persona </td>
            <td style="width:20%; border:0.3px">mínimo 35</td>
        </tr>
    </table>-->
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td width="50%" style="width:15%;text-align:left;"><u><strong>ITINERARIO:</strong></u></td>
        </tr>
        <tr>
            <td style="width:35%;">Listos en el lobby del hotel</td>
            <td style="width:25%;">8:00 A.M.</td>
        </tr>
        <tr>
            <td style="width:35%;">Salida del hotel</td>
            <td style="width:25%;">8:15 A.M.</td>
        </tr>
        <tr>
            <td style="width:35%;">LLegada estimada a Chichen-Itza</td>
            <td style="width:25%;">11:15 A.M.</td>
        </tr>
        <tr>
            <td style="width:35%;">Tour guiado</td>
            <td style="width:25%;">01:00 P.M.</td>
        </tr>
        <tr>
            <td style="width:35%;">Lunch en hotel Mayaland</td>
            <td style="width:25%;">01:15 P.M.</td>
        </tr>
        <tr>
            <td style="width:35%;">Tiempo libre</td>
            <td style="width:25%;">02:00 P.M.-03:30 P.M.</td>
        </tr>
        <tr>
            <td style="width:35%;">Salida de Chichen-Itza</td>
            <td style="width:25%;">03:45 P.M.</td>
        </tr>
        <tr>
            <td style="width:35%;">Llegada estimada al hotel </td>
            <td style="width:25%;">07:00 P.M.</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Recomendaciones:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">• Usar zapatos cómodos y ropa casual, llevar traje de baño, toalla, sombrero o gorra, cámara y lentes de sol.</td>
        </tr>
        <tr>
            <td style="width:100%;">• Traer efectivo para compras de artesanía local.</td>
        </tr>
        <tr>
            <td style="width:100%;">•<strong>Cámaras profesionales necesitan permiso de Autoridades Federales; procesó de este trámite lleva por lo menos 20 días</strong></td>
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF"><strong>CATAMARAN PRIVADO CON TOUR DE SNORKEL DE MEDIO DIA </strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/private_catamaran.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:12px;">
Disfrute de un excelente paseo por las cristalinas aguas del Mar Caribe a bordo de un moderno catamarán. Increíble snorkel en
uno de los arrecifes coralinos que ofrece el destino y para re-energizarse, nada mejor que un delicioso almuerzo con bebidas tropicales en un club de playa de Isla Mujeres.</td>
</tr>
</table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:95%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:95%;">• 4 horas  de  navegación y snorkel.</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">• Tour de snorkel dirigido al arrecife “los manchones” u otros arrecifes dependiendo de las condiciones climáticas</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">• Barra libre con bebidas nacionales, cerveza a bordo del barco.</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">• Equipo para snorkel, (tubo nuevo)</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:95%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:95%;">• Propinas, 15% del costo total.</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">• Impuesto federal para el acceso al arrecife y muelle,  $10.00 por persona</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">• Alimentos  y bebidas en Club de playa, US$25.00 por persona + 15% de servicio. (buffet básico).</td>
        </tr>
        <tr>
            <td style="width:95%;text-align:left;">• Impuestos</td>
        </tr>
    </table>
    <br/>
   <!-- <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>Costo:</strong></td>
        </tr>
        </table>
     <table border="0.3px" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">        
        <tr>
            <td style="width:33%;">Catamarán 36” Capacidad 20 pax</td>
            <td style="width:33%;">&nbsp;</td>
            <td style="width:33%;">Por hora, Min 4 hrs</td>
        </tr>
        <tr>
            <td style="width:33%;">Catamarán 42” Capacidad 40 pax</td>
            <td style="width:33%;"></td>
            <td style="width:33%;">Por hora, Min 4 hrs. </td>
        </tr>
        <tr>
            <td style="width:33%;">Catamarán 44” Capacidad 45pax</td>
            <td style="width:33%;"></td>
            <td style="width:33%;">Por hora, Min 4 hrs.</td>
        </tr>
        <tr>
            <td style="width:33%;">Catamarán 58” Capacidad 60 paxx</td>
            <td style="width:33%;"></td>
            <td style="width:33%;">Por hora, Min 4 hrs. </td>
        </tr>
        <tr>
            <td style="width:33%;">Catamaran 78” Capacidad 100 pax</td>
            <td style="width:33%;"></td>
            <td style="width:33%;">Por hora, Min 4 hrs.</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>Transportación Hoteles Cancún - Marina – Hoteles Cancún:</strong></td>
        </tr>
        </table>
        <table border="0.3px" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">        
        <tr>
            <td style="width:25%;">Motor coach</td>
            <td style="width:25%;">US $ 600.00</td>
            <td style="width:25%;">Por unidad, viaje redondo</td>
            <td style="width:25%;">Hasta 57 pax</td>
        </tr>
        <tr>
            <td style="width:25%;">Vans</td>
            <td style="width:25%;">US $ 110.00</td>
            <td style="width:25%;">Por unidad, viaje redondo</td>
            <td style="width:25%;">1-10 pax  </td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><strong>Transportación Hoteles en Riviera - Marina – Hoteles en Riviera Maya:</strong></td>
        </tr>
        </table>
        <table border="0.3" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;">Motor coach</td>
            <td style="width:25%;">US $ 900.00</td>
            <td style="width:25%;">Por unidad, viaje redondo</td>
            <td style="width:25%;">Up to 45 pax</td>
        </tr>
        <tr>
            <td style="width:25%;">Vans</td>
            <td style="width:25%;">US $ 110.00</td>
            <td style="width:25%;">Por unidad, viaje redondo</td>
            <td style="width:25%;">1-10 pax </td>
        </tr>
        </table>
        <br/>-->
        <table style="width:100%;">
            <tr>
                <!--<td width="15%" style="text-align:center;"><img src="../img/activities/private_catamaran1.jpg" width="211" height="142"/></td>-->
                <td width="50%" style="text-align:justify;  font-size:12px;"><strong>ITINERARIO SUGERIDO:</strong></td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Participantes listos en el lobby del hotel:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">09:10 A.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Salida a Marina:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">09:20 A.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Llegada a la Marina:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">09:45 A.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Salida del Catamarán:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">10:00 A.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Tiempo de Snorkeling:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">11:00 A.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Regreso a Cancún, Marina:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">02:00 P.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Llegada a la Marina:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">02:30 P.M.</td>
            </tr>
            <tr>
                <td width="50%" style="text-align:justify; font-size:12px;">Llegada al hotel:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">02:50 P.M.</td>
            </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><u><strong>Recomendaciones:</strong></u></td>
        	</tr>
        	<tr>
        		<td style="width:100%;">• Traer traje  de baño, toalla, zapatos o sandalias cómodas, lentes de sol, cámara y una playera adicional.</td>
        	</tr>
        	<tr>
        		<td style="width:100%;">• Use solamente bloqueador solar bio-degradable para snorkelear en el arrecife coralino.</td>
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>TRAVESIA POR LA SELVA</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/jungle.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:13px;">
Experimente lo divertido que es conducir un bote para dos personas a través en la laguna y los densos canales de manglares,
visitando el segundo arrecife más grande del mundo para snorquelear con peces multicolores y delicadas formaciones coralina.</td>
</tr>
</table>

         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">• Representante de CTA Cancun</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Agua fría embotellada.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Bote para dos personas.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Equipo de snorquel</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Chaleco salvavidas y acceso al arrecife</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Guías bilingües con experiencia </td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">• Impuesto federal de Arrecife US$10.00</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Transportación  a Marina</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Fotografía del tour US$10.00.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">• Propinas US $5.00 por pax</td>
        </tr>
    </table>
    <!--<br/>
     <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:25%;text-align:left;"><strong>Costo:</strong></td>
        </tr>
        <tr>
            <td style="width:25%;border: 0.3px">Travesía por la jungla </td>
            <td style="width:20%; border: 0.3px">US $ 60.00</td>
            <td style="width:55%; border: 0.3px">Por persona, 2 personas por  vehículo  </td>
        </tr>
        <tr>
            <td style="width:25%; border: 0.3px">Por bote rápido</td>
            <td style="width:20%; border: 0.3px">US $ 120.00</td>
            <td style="width:55%; border: 0.3px">Si es una sola persona, aplica cargo completo</td>
        </tr> 
        </table>-->
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
         <tr>
            <td style="width:55%;text-align:left;"><strong>Transportación Hotel –Marina - Hotel</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
         <tr>
            <td style="width:25%; border:0.3px">Autobús 45 pax</td>
            <td style="width:25%; border:0.3px">$ 660.00</td>
            <td style="width:25%; border:0.3px">Por unidad </td>
            <td style="width:25%; border:0.3px">Viaje ida y vuelta</td>
        </tr>
        <tr>
            <td style="width:25%; border:0.3px">Vans 10 pax</td>
            <td style="width:25%; border:0.3px">$ 120.00 </td>
            <td style="width:25%; border:0.3px">Por unidad </td>
            <td style="width:25%; border:0.3px">Viaje ida y vuelta</td>
        </tr>         
        </table>
        <br />
        <table style="width:100%;">
            <tr>               
                <td width="305%" style="text-align:justify;  font-size:12px;"><u><strong>Itinerario sugerido:</strong></u></td>
            </tr>
            <tr>
                <td width="30%" style="text-align:justify; font-size:12px;">Participantes listos en el lobby del hotel:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">08.00 A.M.</td>
            </tr>
            <tr>
                <td width="30%" style="text-align:justify; font-size:12px;">Salida del hotel:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">08.10 A.M.</td>
            </tr>
            <tr>
                <td width="30%" style="text-align:justify; font-size:12px;">Llegada estimada a la Marina:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">08.30 A.M.</td>
            </tr>
            <tr>
                <td width="30%" style="text-align:justify; font-size:12px;">Tour guiado:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">09.00 A.M.</td>
            </tr>
            <tr>
                <td width="30%" style="text-align:justify; font-size:12px;">Participantes listos para abordar autobús:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">11:30 P.M.</td>
            </tr>
            <tr>
                <td width="30%" style="text-align:justify; font-size:12px;">Salida hacia el hotel:</td>
                <td width="25%" style="text-align:justify; font-size:12px;">12:00 P.M.</td>
            </tr>            
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><u><strong>Recomendaciones:</strong></u></td>
            </tr>
            <tr>
                <td style="width:100%;">• Los horarios pueden variar de acuerdo a la estación.</td>
            </tr>
            <tr>
                <td style="width:100%;">• Los participantes deben estar media hora en la marina antes de la salida del tour.</td>
            </tr>
            <tr>
            <td style="width:100%;">• Una lancha es para dos personas.</td>
            </tr>
             <tr>
            <td style="width:100%;">• Trajes de baño, toallas, sombrero y lentes de sol con string (pueden volar sobre la cabeza), sandalias y loción protectora bio-degradable, cámara para fotografías es recomendada.</td>
            </tr>
             <tr>
            <td style="width:100%;">• Los lockers están disponibles para toallas o cosas personales.</td>
            </tr>
            <tr>
            <td style="width:100%;">• No se recomienda este servicio a niños menores de 5 años, mujeres embarazadas, y personas con problemas de la columna.</td>
            </tr>
        </table>
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:justify;"><strong>*** Para todas las actividades, Box Lunch Deluxe disponible con diferentes opciones por  desde US$10.00 a US $ 22.00 por persona, con el logo del grupo impreso en la bolsa por US $2.00
Bolsa Deluxe de snack con nueces y frutas secas por $10.00, con la opción del logo de la empresa impreso por US $2.00
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
<td style="width:65%;text-align:justify;  font-size:13px;">Una visita al Acuario Natural más grande del mundo, la
hermosa caleta de Xel-Ha perfecta para snorquelear, combinado con el sitio arqueológico de Tulum, la ciudad amurallada localizada a la orilla del mar.</td>
</tr>
</table>

        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Representante de CTA Cancún.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transportación viaje redondo.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Guía de turistas profesional bilingüe. (para la  primeras 20 personas)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Entradas al parque arqueológico, parque Xel-Ha y estacionamientos</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• En Xel-Ha: todas las comidas y bebidas (barra libre nacional), helado,
            equipo de snorquel, toallas, locker, regaderas, baños, llantas salvavidas, chalecos salvavidas, hamacas, sillas de playa
            reclinables, transportación en tren para llegar al Río y bolsos para guardar pertenencias.</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Nado con Delfines y actividades con costo adicional.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;•  Propinas $ 65.00 por persona</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Guía adicional $1300.00 por guía</td>
        </tr>        
    </table>
   <!-- <br/>
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
    <table border="0" cellpadding="0" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:33%;text-align:left;"><u><strong>Costo:</strong></u></td>
            </tr>
            <tr>
                <td style="width:33%;" border="0.5px">Tour Tulum Xel-Ha todo incluido</td>
                <td style="width:33%;" border="0.5px">US$ 127.00</td>
                <td style="width:33%;" border="0.5px">Por persona,  mínimo 35</td>
        </tr>
        </table>-->
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:40%;text-align:left;"><u><strong>ITINERARIO SUGERIDO:</strong></u></td>
            </tr>
            <tr>
                <td style="width:40%;">Participantes listos en el lobby del hotel:</td>
                <td style="width:40%;">8:20 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Salida del autobús del hotel:</td>
                <td style="width:40%;">8:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Llegada estimada a Tulum:</td>
                <td style="width:40%;">10.30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Tour guiado en Tulum:</td>
                <td style="width:40%;">10.30/11.30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Participantes listos para abordar autobús:</td>
                <td style="width:40%;">11.45 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Salida de Xel-Ha:</td>
                <td style="width:40%;">12.00 noon</td>
        </tr>
        <tr>
                <td style="width:40%;">Llegada estimada a Xel-Ha:</td>
                <td style="width:40%;">12.15 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Tiempo libre en Xel-Ha:</td>
                <td style="width:40%;">12.15 – 3.15 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Participantes listos para abordar autobús:</td>
                <td style="width:40%;">3.30 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Salida a Cancún:</td>
                <td style="width:40%;">3.45 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Llegada estimada al hotel:</td>
                <td style="width:40%;">5.30 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Recomendaciones:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Usar zapatos cómodos y ropa casual, llevar traje de baño, toalla, sombrero, cámara y lentes de sol </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Traer efectivo para compras de artesanía local.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• En Xel-ha se permite usar únicamente bloqueador solar biodegradable;
            si usted lleva consigo otro tipo de bloqueador o bronceador, lo podrá intercambiar por uno biodegradable en la entrada y a su salida le regresarán el suyo. </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• El uso de cámaras profesionales solo están permitidas en Tulum, por medio de un permiso federal, (considerar 1 ½ mes para el trámite)</td>
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>TULUM EXPRESS PRIVADO</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/private_tulum.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:12px;">La famosa “ciudad amurallada” se construye debajo de un acantilado que pasa por alto las aguas azul turquesa del mar caribe a lado de la blanca arena de la playa lo que es un espectáculo para ver.<br/>
Tulum fue habitado por la arquitectura maya durante el periodo posclásico. 900-1521 a.C. y las pinturas del prehispánico, los impresionantes murales son preservados en el “Templo de los Frescos”.</td>
</tr>
</table>

         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Representante de CTA Cancún</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transportación a las ruinas de Tulum</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Botellas de agua y toallas húmedas disponibles (únicamente en la transportación)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Guía profesional bilingüe (para las  primeras 20 personas)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transporte en tren en las ruinas de Tulum</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Propinas- US$ 5.00 por persona</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Alimentos</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Impuestos</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Guia adicional US$150.00</td>
        </tr>
    </table>
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:40%;text-align:left;"><u><strong>ITINERARIO SUGERIDO:</strong></u></td>
            </tr>
            <tr>
                <td style="width:40%;">Participantes listos en lobby del hotel:</td>
                 <td style="width:40%;">8:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Salida hacia Tulum</td>
                 <td style="width:40%;">8:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Llegada estimada a Tulum</td>
                 <td style="width:40%;"> 10:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Tour guiado en Tulum</td>
                 <td style="width:40%;">10:30 A.M-12:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Tiempo libre en Tulum</td>
                 <td style="width:40%;">12:30 P.M.- 01:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Salida hacia Cancún</td>
                 <td style="width:40%;">01:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Llegada estimada al hotel</td>
                 <td style="width:40%;">02:30 P.M.</td>
        </tr>
        </table>
        <!--<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><u><strong>Costo:</strong></u></td>
            </tr>
            <tr>
                <td style="width:25%;" border="0.3px">Tour Tulum Express (4 hrs.)</td>
                 <td style="width:25%;" border="0.3px">US$ 65.00</td>
                  <td style="width:25%;" border="0.3px">Por persona</td>
                  <td style="width:25%;" border="0.3px">mínimo 35</td>
        </tr>
        </table>-->
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Recomendaciones:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%; text-align:justify;">&nbsp;• Usar zapatos cómodos, ropa casual,
            traer traje de baño y toalla para que usted pueda nadar en la playa de las ruinas de Tulum, sombrero, lentes de sol y cámara.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• Dinero en efectivo para artesanía local.</td>
        </tr>        
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:justify;"><u><strong>Notas:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Los horarios varían de acuerdo a las necesidades del grupo.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• <strong>El uso de cámaras profesionales solo está permitido en Tulum por medio de un permiso federal, (considerar 1 mes para el trámite)</strong></td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;">&nbsp;• <strong>Solo se permite el uso de bloqueador biodegradable</strong></td>
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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XCARET DE NOCHE /SHOW</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/xcaret_night.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:13px;">“Paraíso sagrado de la Naturaleza”, es el parque eco-arqueológico más famoso del mundo
con más de 53 actividades para disfrutar. Visite el Pueblo Maya, el aviario, el mariposario, la cueva de los murciélagos, el jardín botánico, el
acuario del arrecife, nado a través de los ríos subterráneos, nado con delfines (opcional) y mucho más. Lo que durante el día es un parque de aventura
en la selva, por la noche se convierte en un lugar místico, tradicional, de leyendas e historia con el show “México Espectacular”. 
</td>
</tr>
</table>

         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Representante de CTA Cancún.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transportación en viaje redondo.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Agua fría embotellada y toallas húmedas desechables (únicamente en  la transportación)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Entradas al parque eco-arqueológico Xcaret a partir de la 4:00 pm.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Show  Mexico Espectacular (cena incluida)</td>
        </tr>  
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Cena y dos bebidas (Aga, Refesco o Vino)</td>
        </tr>      
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Nado con Delfines y actividades con costo adicional.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Propinas US$ 5.00 por persona</td>
        </tr>        
    </table>   
       <!-- <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:33%;text-align:left;"><u><strong>Costo:</strong></u></td>
            </tr>
            <tr>
                <td style="width:33%; border:0.3px">Tour privado en autobús, min 35 pax </td>
                 <td style="width:33%; border:0.3px">US $ 135.00</td>
                  <td style="width:33%; border:0.3px">Por persona</td>
        </tr>
        </table>-->
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><u><strong>ITINERARIO SUGERIDO (HORARIO VERANO):</strong></u></td>
            </tr>
            <tr>
                <td style="width:35%;">Participantes listos en el lobby:</td>
                 <td style="width:20%;">01:00 PM</td>
        </tr>
        <tr>
                <td style="width:35%;">Salida del hotel</td>
                 <td style="width:20%;">01:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Llegada estimada a Xcaret</td>
                 <td style="width:20%;">3:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Bienvenida en Xcaret</td>
                 <td style="width:20%;">3:00 P.M. – 3:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Tiempo libre</td>
                 <td style="width:20%;">3:30 P.M. - 6:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Show de noche</td>
                 <td style="width:20%;">7:00 P.M. – 9:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Salida hacia el hotel</td>
                 <td style="width:20%;">9:30 P.M.</td>
        </tr> 
        <tr>
                <td style="width:35%;">Llegada estimada al hotel</td>
                 <td style="width:20%;">: 11:00 P.M.</td>
        </tr>       
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><u><strong>ITINERARIO SUGERIDO (HORARIO INVIERNO):</strong></u></td>
            </tr>
            <tr>
                <td style="width:35%;">Participantes listos en el lobby:</td>
                 <td style="width:20%;">01:00 PM</td>
        </tr>
        <tr>
                <td style="width:35%;">Llegada estimada a Xcaret</td>
                 <td style="width:20%;">3:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Bienvenida en Xcaret</td>
                 <td style="width:20%;">3:00 P.M. – 3:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Tiempo libre</td>
                 <td style="width:20%;">03:30 P.M. – 05:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Show de noche</td>
                 <td style="width:20%;">06:00 P.M. – 08:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Salida hacia el hotel</td>
                 <td style="width:20%;">08:30 P.M.</td>
        </tr> 
        <tr>
                <td style="width:35%;">Llegada estimada al hotel</td>
                 <td style="width:20%;">10:00 P.M.</td>
        </tr>       
        </table>
        <br/>         
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Recomendaciones:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Usar zapatos cómodos y ropa casual, llevar traje de baño, toalla, sombrero, cámara y lentes de sol</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Traer efectivo para compras, fotos o extra actividades.</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Únicamente bloqueador bio-degradable esta autorizado en Xcaret (también disponible en sus tiendas).</td>
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
<td style="width:65%; text-align:justify;  font-size:12px;">Punta Venado es una aventura eco-destino, de 2060 hectáreas de selva y
<br/>3.72 kilómetros de playa en el corazón de la Riviera Maya en el Caribe Mexicano.
<br/>Con una Hacienda tradicional mexicana, rodeada de singulares caballerizas estilo caribeño, animales domésticos y construcciones típicas.
<br/>Tales como palapas al estilo de la región, que simulan un pueblo maya tradicional, en el medio de la selva virgen tropical.</td>
</tr>
</table>
<table style="width:100%;">
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:12px;">
Explora la flora natural y las características de la fauna de esta región, tales como: ceibas, palmeras, uvas de mar, los álamos y los zapotes.<br/>
Además de una gran variedad de coloridas aves, pequeños felinos, mamíferos y reptiles, característicos de la zona.<br/>
En sus hermosas playas de arena blanca, se pueden admirarlos arrecifes de coral, que representan  la segunda barrera arrecifal más grande del planeta, formando un tubo grande, en donde podrás practicar el kayak en el arrecife protegido<br/><br/>
Nada mejor como disfrutar de un paseo a caballo en la selva a lo largo de la playa, disfrutando de los emocionantes senderos en ATV, además de esnórquel en un arrecife increíble que se ubica a pocos metros de la playa, kayaks para niños,
nadar en cenotes y aventurarte explorando las  cavernas o simplemente relajándote en la playa disfrutando  así de los paisajes que te ofrece  Punta Venado.</td>
</tr>
</table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Representante de CTA Cancún </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Entrada a Punta Venado </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• 1 cuatrimoto por persona</td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Casco</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Goggles</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Linternas </td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;•Equipo de snorkel</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Refrescos, aguas y botanas secas</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transporte compartido</td>
        </tr>
    </table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Propinas US$5.00 por pax</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Impuestos</td>
        </tr>       
    </table>
    <table style="width:100%;">
<tr>
<td style="width:65%;text-align:justify;  font-size:13px;"><strong>Cabalgata en Punta Venado:</strong></td>
</tr>
</table>
<table style="width:100%;">
	<tr>
<td style="width:65%;text-align:justify;  font-size:13px;">
Un caballo se te asignará de acuerdo a tu edad, esta actividad es apta para toda la familia.<br/>
El paseo incluye un recorrido a lo largo de la costa, regresando al rancho a través de un lado de la playa
en donde se puede encontrar  una frondosa vegetación.
<br/>Después te llevaremos a un hermoso Cenote con agua cristalina donde te podrás bañar o simplemente relajar.
<br/>Expediciones  09:00 / 12:00 / 15:00 horas<br/>
Capacidades  Grupos de 14 personas máximo</td>
<td width="15%" style="text-align:center;"><img src="../img/activities/horse.jpg" width="211" height="142"/></td>
</tr>
<tr>
<td style="width:65%;text-align:justify;  font-size:13px;"><strong>Incluye:</strong>precio de entrada, paseo a caballo y 1 botella de agua</td>
</tr>
<tr>
<td style="width:65%;text-align:justify;  font-size:13px;"><strong>Duración:</strong>1 hora 15 minutos</td>
</tr>
</table>
<br/>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:left;"><img src="../img/activities/horse2.jpg" width="211" height="142"/></td>
<td style="width:65%;text-align:justify;  font-size:12px;">
ATV Aventura:<br/>
Encuentro en Punta Venado para iniciar la expedición de ATV a través de la selva, hasta llegar a una cueva completamente oscura y emocionante.
<br/>Esta cueva se encuentra a 5 minutos por medio de una caminata en la selva de la pista.
<br/>Una vez dentro, el uso de linternas, será necesario para recorrer y admirar las antiguas formaciones rocosas, ya que está completamente a obscura. 
<br/>La expedición continúa en vehículo todo terreno, hasta un hermoso cenote en donde podrás capturar las mejores fotos, nadar o simplemente tomar un pequeño descanso.</td>
</tr>
</table>
<table style="width:100%;">
<tr>
<td style="width:65%;text-align:justify;  font-size:13px;">Expediciones  09:00 / 12:00 / 15:00
</td>
</tr>
<tr>
<td style="width:65%;text-align:justify;  font-size:13px;">Capacidades: Grupos de 15 personas máximo</td>
</tr>
</table>  
 <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">ATV para 1 o 2 personas, casco, gafas, linternas y 1 botella de agua</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;"><strong>Duración:</strong>1 hora 45 minutos
<br/>Edad mínima para conducir 16 años
            </td>
        </tr>
    </table>
    <br/>
    <table style="width:100%;">
    <tr>
<td style="width:65%;text-align:justify;  font-size:13px;">Esnórquel y Cenote:</td>
 </tr>
    <tr>
<td style="width:65%;text-align:justify; font-size:13px;">
Descubre la belleza de una de las barreras de arrecifes de coral más impresionante en la Riviera Maya.
Resulta fascinante practicar esnórquel en los arrecifes pocos profundos a suaves corrientes, en donde tendrás  una increíble visibilidad bajo el agua.
Capacity: Groups of 50 people maximum 
<br/>El resto del día se puede pasar  en el “Coprero Beach Club”
<br/>Capacidades  Grupos de 50 personas máximo</td>
<td width="15%" style="text-align:left;"><img src="../img/activities/horse2.jpg" width="211" height="142"/></td>
</tr>
<tr>
            <td style="width:35%;text-align:left;font-size:13px;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:13px;">Precio de la entrada, equipo de esnórquel o kayak, botella de agua y snacks</td>
        </tr>
         <tr>
            <td style="width:35%;text-align:left;font-size:13px;"><u><strong>Duración: </strong>3 horas
</u></td>
        </tr>
         <tr>
            <td style="width:35%;text-align:left;font-size:13px;">Expediciones  09:00 / 11:00</td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:13px;">Horario: De  9:00 a.m. a 17:00 p.m. De lunes a domingo.</td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:13px;"><u><strong>Precio:</strong></u></td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:13px;">$ 260 por entrar al club de playa.</td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:13px;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:13px;"><u><strong>Itinerario sugerido:</strong></u></td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:13px;">Participantes listos en el lobby:</td>
             <td style="width:20%;text-align:left;font-size:13px;">07:30 A.M.</td>
        </tr>
         <tr>
            <td style="width:35%;text-align:left;font-size:13px;">Salida del hotel:</td>
            <td style="width:20%;text-align:left;font-size:13px;">07:45 A.M.</td>
            
        </tr>
         <tr>
            <td style="width:35%;text-align:left;font-size:13px;">Llegada a Punta Venado:</td>
            <td style="width:20%;text-align:left;font-size:13px;">08:45 A.M.</td>
        </tr>
        <tr>
            <td style="width:35%;text-align:left;font-size:13px;">Salida de Punta Venado:</td>
             <td style="width:20%;text-align:left;font-size:13px;">03:00 P.M.</td>
        </tr>
         <tr>
            <td style="width:35%;text-align:left;font-size:13px;">Llegada al hotel:</td>
             <td style="width:20%;text-align:left;font-size:13px;">04:00 P.M.</td>
        </tr>
</table>
        <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">           
         <tr>
                <td style="width:33%;">Precio del tour:</td>
                 <td style="width:20%;">8:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:33%;">Tour Cabalgata </td>
                 <td style="width:33%;">US $ 73.00</td>
                  <td style="width:33%;">Por persona</td>
        </tr>
        <tr>
                <td style="width:33%;">Tour ATV </td>
                 <td style="width:33%;">US $ 72.00</td>
                  <td style="width:33%;">Por persona</td>
        </tr>
        <tr>
                <td style="width:33%;">Tour Esnorquel</td>
                 <td style="width:33%;">US $ 52.00</td>
                  <td style="width:33%;">Por persona</td>
        </tr>
        <tr>
                <td style="width:33%;"><strong>Transportación privada:</strong></td>
        </tr>
        <tr>
                <td style="width:33%;">Van Min. 8 pax Max. 10 pax</td>
                 <td style="width:33%;">US $ 200.00</td>
                  <td style="width:33%;">Por unidad / Viaje redondo</td>
        </tr>
         <tr>
                <td style="width:33%;">Autobús hasta 45 pax</td>
                 <td style="width:33%;">$ 800.00</td>
                  <td style="width:33%;">Por unidad / Viaje redondo</td>
        </tr>       
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Recomendaciones:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Usar zapatos cómodos y ropa casual para caminar en la selva</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Llevar traje de baño</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Toalla, sombrero, cámara y lentes de sol </td>
        </tr> 
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Cambio de ropa seca</td>
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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>PESCA EN AGUAS PROFUNDAS</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/deep_sea.jpg" width="211" height="142"/></td>
            <td style="width:65%;text-align:justify;  font-size:13px;">
            Sé un pionero de la pesca deportiva en la Riviera Maya; la pesca ofrece el tiempo más corto para ponerse en marcha hacia
            afuera de las profundas aguas más allá de la plataforma Continental. Sus capitanes de habla inglesa expertos le garantizan
            virtualmente pescar peces. La mayor parte del equipo tiene más de 10 años de experiencia pescando así. Sus barcos están
            totalmente equipados con la calidad Penn y los trastos de Shimano, con receptores acústicos de la profundidad y GPS que
            asientan confortablemente a 6 personas en la cortina. Se proporciona todo el engranaje de la seguridad. Los mares son
            tradicionalmente tranquilos, sobre todo de febrero hasta agosto son excelentes para los pez vela, el marlín, el atún y el delfín.</td>
</tr>
</table>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:100%;text-align:left;"><u><strong>Incluye: </strong></u></td>
    </tr>
    <tr>
        <td style="width:100%;">&nbsp;• Representante de CTA Cancún</td>
    </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Capitán bilingüe con experiencia</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Yates muy cómodos completamente equipados con calidad en los engranes </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Engranes de seguridad</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Nevera con refrescos, cerveza y botellas de agua</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Cebos</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Permisos para pescar y honorarios</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Seguro </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Agua fría embotellada y toallas húmedas desechables (únicamente en la transportación)</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Propinas 15%</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Comida y botanas</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transportación hotel-marina-hotel </td>
        </tr>
         <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Impuesto ferderal de muyelle y arrecife US$10.00 por persona</td>
        </tr>
    </table>
    <br/>    
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:33%;text-align:left;"><strong>Transportación Hotel –Marina - Hotel en Zona hotelera de Cancún</strong></td>
            </tr>
            <tr>
                <td style="width:33%; border:0.3px">Autobús para 53 pax</td>
                 <td style="width:33%; border:0.3px">US $120.00</td>
                  <td style="width:33%; border:0.3px">Por vehiculo, Viaje redondo</td>
        </tr>
        <tr>
                <td style="width:33%; border:0.3px">Van para grupos de hasta 10 pax</td>
                 <td style="width:33%; border:0.3px">US $660.00</td>
                  <td style="width:33%; border:0.3px">Por vehiculo, Viaje redondo</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>Box lunch deluxe en diferentes opciones están disponibles desde $US10.00 ha $US22.00 cda uno</strong></td>
            </tr>
        </table>
        <br/>
<table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><strong>ITINERARIO SUGERIDO PARA CHARTER DE 6 HRS: </strong></td>
            </tr>
        <tr>
                <td style="width:35%;">Participantes listos en el lobby del hotel:</td>
                 <td style="width:20%;">06.30 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Salida a la Marina:</td>
                 <td style="width:20%;">06.35 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Llegada a la Marina :</td>
                 <td style="width:20%;">07:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Salida de los botes:</td>
                 <td style="width:20%;">07.30 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Regreso a la Marina:</td>
                 <td style="width:20%;">02:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Regreso al hotel:</td>
                 <td style="width:20%;">02:30 P.M.</td>
        </tr>
        </table>
         <!--<br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><strong>Precio por yate:</strong></td>
        	</tr>        	
        </table>
        <table border="0.3" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">            
        <tr>
                <td style="width:40%;">&nbsp;</td>
                 <td style="width:20%;">4 Horas</td>
                 <td style="width:20%;">6 Horas</td>
                 <td style="width:20%;">8 Horas</td>
        </tr>
        <tr>
                <td style="width:40%;">Yates de 33’ a 34’ (capacidad máx. 8 pax) Recomendado maximo. 6 pax</td>
                 <td style="width:20%;">US $690.00</td>
                 <td style="width:20%;">US $790.00</td>
                 <td style="width:20%;">US $890.00</td>
        </tr>
        </table>-->
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
            El primer encuentro: Aprenda y diviértase! Descubra los secretos más guardados de los delfines en un ambiente seguro y lleno de diversión.
            Usted se sorprenderá de lo inteligentes y amigables que son estos maravillosos mamíferos marinos. Al igual tendrá la oportunidad de abrazarlos,
            besarlos, dejarlo que lo besen en la mejilla y disfrutar viendo a sus nuevos amigos mientras realizan una serie de asombrosos actos de conducta.
            Lo harán sentir cosquilleo de la emoción. Este programa es ideal para niños de todas las edades.</td>
</tr>
</table>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
    </tr>
    <tr>
        <td style="width:100%;">&nbsp;• Representante de CTA Cancun</td>
    </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Desayuno ligero durante el check-in (Isla Mujeres)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Traslado redondo Marina-Isla Mujeres-Marina  </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Aguas y refrescos a bordo de la embarcación </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Buffet lunch en el club de playa (Isla Mujeres)</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Propinas US$5.00 por pax</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Comidas y bebidas en Puerto Aventuras</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Foto y video de la experiencia con los delfines</td>
        </tr>
         <tr>
            <td style="width:100%;text-align:left;">&nbsp;• US$ 10.00  Impuesto de uso de arrecife y muelle, por persona</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><strong>ITINERARIO SUGERIDO:</strong></td>
            </tr>
            <tr>
                <td style="width:35%;">Participantes listos en el lobby:</td>
                 <td style="width:25%;">08:00 A.M.</td>
        </tr>        
         <tr>
                <td style="width:35%;">Salida del hotel:</td>
                 <td style="width:25%;">08:15 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Llegada estimada a la Marina	:</td>
                 <td style="width:25%;">08:45 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Salida a Isla Mujeres:</td>
                 <td style="width:25%;">09:15 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Introducción y nado con delfines:</td>
                 <td style="width:25%;">09:45 AM – 11:45 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Salida hacia el hotel:</td>
                 <td style="width:25%;">12:15 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Llegada al hotel:</td>
                 <td style="width:25%;">12:45 P.M.</td>
        </tr>
        <br/>
        </table>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>Los tours están disponibles a las  10:30, 11:00, 1:00 y 3:30 pm</strong></td>
            </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:25%;text-align:left;"><strong><u>Costo:</u></strong></td>
            </tr>
            <tr>
            <td style="width:34%; border:0.3px">Tour privado</td>
            <td style="width:33%; border:0.3px">Adultos</td>
            <td style="width:33%; border:0.3px">Mín. grupos 16 pax</td>
        </tr>
        <tr>
            <td style="width:34%; border:0.3px">&nbsp;</td>
            <td style="width:33%; border:0.3px">US $86.00</td>
            <td style="width:33%; border:0.3px">Mín. grupos 16 pax</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>Para mantener la actividad privada se requieren mín. 16 PAX y máx. 20 por turno.</strong></td>
            </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;"><strong>Transportación Hotel en Cancun  –Marina – Hoteles en Cancun</strong></td>
        </tr>
        <tr>
                <td style="width:34%; border:0.3px">Autobús para 45 pax</td>
                 <td style="width:33%; border:0.3px">US 450.00</td>
                  <td style="width:33%; border:0.3px">Por vehiculo, Viaje redondo</td>
        </tr>
        <tr>
                <td style="width:34%; border:0.3px">Van para grupos de hasta 10 pax </td>
                 <td style="width:33%; border:0.3px">US 120. 00</td>
                  <td style="width:33%; border:0.3px">Por vehículo, Viaje redondo</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>**TRANSPORTACION PARA HOTELES EN RIVIERA MAYA Y PUERTO AVENTURAS</strong></td>
            </tr>
            </table>
            <table border="0.3px" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;">Autobús para 45 pax</td>
                 <td style="width:33%;">US 950.00</td>
                  <td style="width:33%;">Por vehiculo, Viaje redondo</td>
        </tr>
        <tr>
                <td style="width:34%;">Van para grupos de hasta 10 pax </td>
                 <td style="width:33%;">US 145.00</td>
                  <td style="width:33%;">Por vehículo, Viaje redondo</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Recomendaciones:</u></strong></td>
            </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Se sugiere llevar ropa cómoda: bermudas, playera, tenis, lentes de sol, traje de baño, cámara, protector biodegradable.</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Traer efectivo para las artesanías locales y tiendas de recuerdos.</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• El uso de cámaras profesionales requiere un permiso especial por parte de las autoridades federales; con un proceso de 20 días antes.</td>
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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>NADO ROYAL EN PUERTO AVENTURAS, COZUMEL E ISLA MUJERES</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/royal_swim.jpg" width="211" height="142"/></td>
            <td style="width:65%;text-align:justify;  font-size:12px;">
                Lleno de emoción, acción y velocidad. Este es, indudablemente, el programa más famoso de Dolphin Discovery y puede resumirse en dos palabras:
                acción y velocidad. Dos delfines dándole la bienvenida con un afectuoso saludo de mano, un beso en la mejilla y dejando que usted lo bese también.
                Más tarde lo llevan a un original y veloz paseo sujetándose de sus aletas. El momento más espeluznante de este programa es el “foot-push”, cuando
                usted siente todo el vigor de sus nuevos amigos mientras lo empujan a través de la piscina de la planta de sus pies. Es verdaderamente “una experiencia para toda la vida”.</td>
</tr>
</table>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:100%;text-align:left;"><u><strong>Incluye: </strong></u></td>
    </tr>
    <tr>
        <td style="width:100%;">&nbsp;• Representante de CTA Cancun</td>
    </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Desayuno ligero durante el check-in (Isla Mujeres)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Traslado redondo Marina-Isla Mujeres-Marina  </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Aguas y refrescos a bordo de la embarcación </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Buffet lunch en el club de playa  Isla Mujeres)</td>
        </tr>
    </table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Propinas $65.00 por pax</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Comidas y bebidas en Puerto Aventuras y Cozumel</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Foto y video de la experiencia con los delfines</td>
        </tr>
         <tr>
            <td style="width:100%;text-align:left;">&nbsp;• $39.00 para el honorario por persona que debe ser pagado en la Marina de Aquatours al registrarse</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><strong>Itinerario sugerido:</strong></td>
            </tr>
            <tr>
                <td style="width:35%;">Participantes listos en el lobby:</td>
                 <td style="width:25%;">09:15 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Salida del hotel:</td>
                 <td style="width:25%;">09:30 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Llegada estimada a la Marina:</td>
                 <td style="width:25%;">10:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Salida de la Marina:</td>
                 <td style="width:25%;">10:15 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Llegada a Isla Mujeres:</td>
                 <td style="width:25%;">10:30 AM – 12:30 P.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Introducción y nado con delfines:</td>
                 <td style="width:25%;">10:30 AM – 12:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Salida hacia el hotel:</td>
                 <td style="width:25%;">12:45 P.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Llegada al hotel:</td>
                 <td style="width:25%;">01:15 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;text-align:left;"><strong><u>Costo Tour Privado</u></strong></td>
            </tr>
            <tr>
            <td style="width:34%; border: 0.3px">Royal Swim </td>
            <td style="width:33%; border: 0.3px">$ 1,782.00</td>
            <td style="width:33%; border: 0.3px">Isla Mujeres </td>
        </tr>
            <tr>
            <td style="width:34%; border: 0.3px">Royal Swim </td>
            <td style="width:33%; border: 0.3px">$ 1,670.00</td>
            <td style="width:33%; border: 0.3px">Cozumel</td>
        </tr>
            <tr>
            <td style="width:34%; border: 0.3px">Royal Swim </td>
            <td style="width:33%; border: 0.3px">$ 1,670.00</td>
            <td style="width:33%; border: 0.3px">Puerto Aventuras</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>Para mantener la actividad privada se requieren mín. 16 PAX y máx. 20 por turno.</strong></td>
            </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;"><strong>Transportación ROYAL SWIM ISLA MUJERES:</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">            
        <tr>
                <td style="width:34%; border: 0.3px">Autobús para 45 pax</td>
                 <td style="width:33%; border: 0.3px">US $ 660.00</td>
                  <td style="width:33%; border: 0.3px">Por vehículo, Viaje redondo</td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px">Van para grupos de hasta 10 pax </td>
                 <td style="width:33%; border: 0.3px">US $ 120.00</td>
                  <td style="width:33%; border: 0.3px">Por vehículo, Viaje redondo</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;"><strong>Transportación ROYAL SWIM COZUMEL:</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:34%; border: 0.3px">Autobús para 45 pax</td>
                 <td style="width:33%; border: 0.3px">US $ 850.00</td>
                  <td style="width:33%; border: 0.3px">Por vehículo, Viaje redondo   </td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px">Van para grupos de hasta 10 pax</td>
                 <td style="width:33%; border: 0.3px">US $ 130.00</td>
                  <td style="width:33%; border: 0.3px">Por vehículo, Viaje redondo</td>
        </tr>
    </table>
<br/>
 <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;"><strong>Transportación ROYAL SWIM PUERTO AVENTURAS</strong></td>
        </tr>
        </table>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:34%; border: 0.3px">Autobús para 45 pax</td>
                 <td style="width:33%; border: 0.3px">US $ 950.00</td>
                  <td style="width:33%; border: 0.3px">Por vehículo, Viaje redondo</td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px">Van para grupos de hasta 10 pax </td>
                 <td style="width:33%; border: 0.3px">US $ 150.00</td>
                  <td style="width:33%; border: 0.3px">Por vehículo, Viaje redondo</td>
        </tr>
    </table>
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Recomendaciones:</u></strong></td>
            </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Se sugiere llevar ropa cómoda: bermudas, playera, tenis, lentes de sol, traje de baño, cámara, protector biodegradable.</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Traer efectivo para las artesanías locales y tiendas de recuerdos.</td>
        </tr>
         <tr>
                <td style="width:100%;">&nbsp;• El uso de cámaras profesionales requiere un permiso especial por parte de las autoridades federales; con un proceso de 20 días antes.</td>
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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>ARRECIFE NATURAL EN ISLA MUJERES “GARRAFON”</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/natural_reef.jpg" width="500" height="150"/></td>
        </tr>
        <tr>
            <td style="width:100%;text-align:justify;  font-size:13px;">
                “Despierte sus sentidos con la magia de un arrecife coralino de un acantilado, actividades de agua asombrosas y las ruinas arqueológicas
                en medio del mar que se combinan con el misticismo de la cultura maya para ofrecerle la tranquilidad espiritual con la combinación de experiencias
                emocionantes que hace que usted disfrute de la naturaleza que tiene el Caribe Mexicano. Goce el bucear en un arrecife coralino único o una caminata
                panorámica entre un acantilado naturalmente esculpido y el increíble Caribe o relájese en la hermosa piscina del infinito. Después de disfrutar de
                todas las actividades en Garrafón, descubra la naturaleza del delfín con nuestro “famoso encuentro del delfín” en la isla del Delfín.
            </td>
</tr>
</table>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
    </tr>
    <tr>
        <td style="width:100%;">&nbsp;• Representante de CTA Cancun</td>
    </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Desayuno ligero durante el check-in ( Isla Mujeres)</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Traslado redondo Marina-Isla Mujeres-Marina  </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Aguas y refrescos a bordo de la embarcación </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Alimentos y bebidas en parque Garrafón</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Equipo de snorkel, chaleco salvavidas, tirolesa, y alberca</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Nado con delfines</td>
        </tr>        
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Propinas US$5.00 por pax</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Foto y video de la experiencia con los delfines</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• US$3.00 para el honorario por persona que debe ser pagado en la Marina de Aquatours al registrarse</td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:40%;text-align:left;"><strong>ITINERARIO SUGERIDO</strong></td>
            </tr>
            <tr>
                <td style="width:40%;">Participantes listos en el lobby del hotel</td>
                 <td style="width:20%;">09.00 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Salida del Hotel</td>
                 <td style="width:20%;">09.10 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Hora estimada en llegar a la Marina</td>
                 <td style="width:20%;">09.40 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Salida al Parque de Garrafón</td>
                 <td style="width:20%;">10.00 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Llegada al Parque de Garrafón</td>
                 <td style="width:20%;">10.45 A.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Tiempo libre en el Parque garrafón</td>
                 <td style="width:20%;">11.00- 2:300 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Participantes listos para abordar el bote</td>
                 <td style="width:20%;">02.30 P.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Salida a la Isla del Delfín</td>
                 <td style="width:20%;">02.35 P.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Nado con los delfines</td>
                 <td style="width:20%;">03.00-03.50 P.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Salida hacia Cancun</td>
                 <td style="width:20%;">04.00 P.M.</td>
        </tr>
         <tr>
                <td style="width:40%;">Llegada a la Marina</td>
                 <td style="width:20%;">04.40 P.M.</td>
        </tr>
        <tr>
                <td style="width:40%;">Llegada al hotel</td>
                 <td style="width:20%;">05.00 P.M.</td>
        </tr>
        </table>
        <!--<br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;text-align:left;"><strong><u>Tour price:</u></strong></td>
            </tr>
            <tr>
            <td style="width:34%; border: 0.3px">Garrafon Discovery  and Royal Swim</td>
            <td style="width:33%; border: 0.3px">US $172.00</td>
            <td style="width:33%; border: 0.3px">Per person</td>
        </tr>
    </table>-->
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;text-align:left;"><strong>Transportación de Cancún:</strong></td>
            </tr>
            <tr>
                <td style="width:34%;text-align:left; border: 0.3px">Autobús para45 pax</td>
                <td style="width:33%;text-align:left; border: 0.3px">US $ 660.00</td>
                <td style="width:33%;text-align:left; border: 0.3px">Por vehiculo, Viaje redondo</td>
            </tr>
            <tr>
                <td style="width:34%;text-align:left; border: 0.3px">Van para grupos de hasta 10 pax </td>
                <td style="width:33%;text-align:left; border: 0.3px">US $ 120.00</td>
                <td style="width:33%;text-align:left; border: 0.3px">Por vehículo, Viaje redondo</td>
            </tr>
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;"><strong>Recomendaciones:</strong></td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• El horario varía de acuerdo a las necesidades del grupo</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Se sugiere llevar ropa cómoda: bermudas, playera, tenis, traje de baño, cámara</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Llevar toallas del hotel</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Protector biodegradable es necesario o serán guardados en la entrada.</td>
        </tr>
        <tr>
                <td style="width:100%;">&nbsp;• Los alimentos y bebidas no pueden ser introducidos.</td>
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
             A la llegada se saluda a los huéspedes en la “Palapa principal”, donde tenemos armarios para poner las pertenencias personales.
             Nuestra primera actividad será el “Canopy tour”. Para hacer esto, nuestro personal equipara a cada huésped con los arneses, poleas
             de seguridad, cascos y guantes y revisará que todo el equipo esté puesto correctamente. Después de que termine el viaje del pabellón,
             la actividad siguiente será en bici de montaña hacia el cenote que está situado a una milla; nuestras bicis son de aluminio, diseñadas para esta trayectoria. 
            <br/>
            En esta ruta usted será testigo de la belleza de la selva maya regional.</td>
</tr>
</table>
<br/>
<table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
    </tr>
    <tr>
        <td style="width:100%;">Representante de CTA Cancún</td>
    </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transportación en viaje redondo</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Canopy tour</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Nado en el cenote </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Comida ligera y agua purificada</td>
        </tr>
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>No incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Propinas $5.00 USD por pax</td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;</td>
        </tr>
         <tr>
            <td style="width:100%;"><strong>Duración:</strong>aprox. 03:30 hrs.</td>
        </tr>
         <tr>
            <td style="width:100%;"><strong>Lunes a Sábado.</strong></td>
        </tr>
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><strong>ITINERARIO SUGERIDO:</strong></td>
            </tr>
            <tr>
                <td style="width:35%;">Participantes listos en el lobby del hotel</td>
                 <td style="width:20%;">08:45 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Salida a Selvática</td>
                 <td style="width:20%;">09:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Llegada a Selvática</td>
                 <td style="width:20%;">09:45 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Comienzo del Canopy Tour en Selvática</td>
                 <td style="width:20%;">10.00 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Nado en el cenote Verde Lucero</td>
                 <td style="width:20%;">11:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:35%;">Regreso al Hotel</td>
                 <td style="width:20%;">01:00 P.M.</td>
        </tr>
        </table>
       <!-- <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;text-align:left;"><strong><u>Costo:</u></strong></td>
            </tr>
            <tr>
            <td style="width:34%; border: 0.3px">Selvatica Canopy Tour</td>
            <td style="width:33%; border: 0.3px">US$86.00</td>
            <td style="width:33%; border: 0.3px">Adulto</td>
        </tr>
    </table>-->
    <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Recomendaciones:</u></strong></td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Calzado cómodo para caminar en la selva</td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Engranaje de natación</td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Playera extra</td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Toalla</td>
            </tr>
            <tr>
                <td style="width:100%;text-align:left;">&nbsp;• Dinero extra (propinas, fotografías y recuerdos</td>
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
<table style="width:100%">
<tr>
<td width="15%" style="text-align:left;"><img src="../img/activities/rio_secreto.jpg" width="200" height="135"/></td>
            <td style="width:65%;text-align:justify;  font-size:12px;">
    Atrévete a vivir una experiencia fantástica en uno de los lugares más increíbles de la Riviera Maya.<br/>
    Introdúcete a este museo natural lleno de un caleidoscopio de espeleotemas y camina una ruta fácil de 600m en medio de las miles de estalactitas y estalagmitas que se encuentran en esta reserva natural protegida.<br/>
    Aprende y admira en este mundo antiguo, mágico y subterráneo inexplorado por millones de años.</td>
</tr>
</table>
<br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:100%;text-align:left;"><u><strong>Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:100%;">&nbsp;• Van A/A Viaje Redondo Hotel – Rio Secreto – Hotel </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Transportación hacia el Rio. </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Lockers</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Equipo: Casco con linterna, flash , traje de neopreno, zapatos especiales y chaleco (opcional) </td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Entrada al Rio</td>
        </tr>
        <tr>
            <td style="width:100%;text-align:left;">&nbsp;• Lunch Ligero</td>
        </tr>        
    </table>
    <br/>
         <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>No Incluye:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;">&nbsp;• El 16% de impuestos</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;">&nbsp;• Propina Sugerida $65.00 por persona</td>
        </tr>
    </table>
         <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><u><strong>Itinerario:</strong></u></td>
            </tr>
            <tr>
                <td style="width:35%;">Participantes listos en el lobby:</td>
                 <td style="width:25%;">09:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Salida del hotel:</td>
                 <td style="width:25%;">09:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Llegada a Rio Secreto:</td>
                 <td style="width:25%;">10:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Tour guiado:</td>
                 <td style="width:25%;">11:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Comida:</td>
                 <td style="width:25%;">12:45 P.M.-01:45 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Salida de Rio Secreto:</td>
                 <td style="width:25%;">02:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:35%;">Llegada al hotel:</td>
                 <td style="width:25%;">03:00 P.M.</td>
        </tr>
        </table>
         <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><strong><u>Horarios: </u>&nbsp; &nbsp; &nbsp;9:00 A.M. / &nbsp;11 A.M. &nbsp;/ 1:00 P.M.</strong></td>
        	</tr>
        	<tr>
        		<td style="width:100%;">* El orden de estas actividades puede variar dependiendo de la logística del día</td>
        	</tr>
        </table>
        <!--<br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:34%;text-align:left;"><u><strong>Costo:</strong></u></td>
        </tr>
        <tr>
            <td style="width:34%; border: 0.3px">Río Secreto Tour </td>
            <td style="width:33%;text-align:left; border: 0.3px">$ 1,177.00</td>
             <td style="width:33%;text-align:left; border: 0.3px">Por persona</td>
        </tr>
    </table>-->
    <br/>
     <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:34%;text-align:left;"><strong>Transportacion a  Rio secreto</strong></td>
        </tr>
        <tr>
            <td style="width:34%; border: 0.3px">Motorcoach for 45 pax</td>
            <td style="width:33%;text-align:left; border: 0.3px">US $ 950.00</td>
             <td style="width:33%;text-align:left; border: 0.3px">Per unit, round trip</td>
        </tr>
        <tr>
            <td style="width:34%; border: 0.3px">Van max 10 pax</td>
            <td style="width:33%;text-align:left; border: 0.3px">US $ 150.00</td>
             <td style="width:33%;text-align:left; border: 0.3px">Per unit, round trip</td>
         </tr>
         <tr>
             <td style="width:34%;text-align:left;"><strong>Capacidad para 48 personas por horario máximo</strong></td>
        </tr>
    </table>
    <br/>
    <table style="width:100%;">
        <tr>
            <td style="width:100%;text-align:justify;  font-size:13px;"><strong><u>Recomendaciones:</u></strong><br/>
                &nbsp;• Ropa Confortable y zapatos-tenis cómodos, (no sandalias!), Bermudas , traje de Baño y camiseta se recomiendan<br/>
                &nbsp;• Cambio de ropa seca extras<br/>
                &nbsp;• Tarjeta de Crédito & Efectivo para Videos, fotos y souvenirs a su disponibilidad<br/>
                <br/><strong><u>Notas:</u></strong>
                <br/>
                &nbsp;• Peso máximo por persona: 250lb /120 kg<br/>
                &nbsp;• Edad Mínima: 6 años<br/>
                &nbsp;• Este tour no está disponible para personas con capacidades diferentes, problemas del corazón, problemas de espalda, mujeres embarazadas o bajo influencia de alcohol o drogas.</td>
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
				 Vive una maravillosa experiencia en la selva y el mar Caribe sumergiéndote en sus aguas turquesas rodeadas de una
				 exótica vida marina y vuela por encima de la copa de los árboles. Déjate sorprender por la biodiversidad de México y practica snorkel rodeado de tortugas marinas, corales y peces multicolores mientras disfrutas de una playa de arena blanca.</td>
                </tr>
                 </table>
                  <br/>
                   <table border="0" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
                <tr>
                	 <td style="width:100%;text-align:justify;  font-size:13px;">
                Haz realidad tu sueño de volar sobre la selva mientras recorres el circuito de tirolesas más rápido y emocionante de la Riviera Maya. Después te prepararemos con todo el equipo necesario para descender
                a rappel dentro de la selva y exploraremos un río subterráneo en medio de la selva disfrutando al máximo la belleza de los cenotes mayas. Al terminar el excitante recorrido de aventura, te esperará un delicioso buffet con comida de la región, bajo una palapa típica y rodeado por los sonidos de la naturaleza.
                <br/>
                Nuestros guías te acompañarán en todo momento, compartirán contigo las mejores técnicas de snorkeling, así como información acerca del tipo de flora y fauna que habita en los ecosistemas y la formación de los cenotes.
                <br/>
                <br/>
                <strong><u>Incluye:</u></strong>Transportación en van con aire acondicionado, guía bilingüe, entradas, equipo de snorkeling y montañismo, comida, agua y refrescos, seguros e impuestos.
                <br/>
                <br/>
                <strong><u>Duración:</u></strong>Aproximadamente 7 horas desde el momento de ser recogido en el hotel hasta regresar al mismo.
            </td>
</tr>
</table>
<br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px; width:100%;">
            <tr>
                <td style="width:35%;text-align:left;"><strong>Itinerario:</strong></td>
            </tr>
            <tr>
                <td style="width:35%;">Participantes listos en el lobby:</td>
        </tr>
         <tr>
                <td style="width:35%;"><p>Salida del hotel:</p></td>
        </tr>
         <tr>
                <td style="width:35%;">Llegada a Snorkel Xtreme:</td>
        </tr>
         <tr>
                <td style="width:35%;">Comida:</td>
        </tr>
         <tr>
                <td style="width:35%;">Salida de Snorkel Xtreme:</td>
        </tr>  
        <tr>
                <td style="width:35%;">Llegada al hotel:</td>
        </tr>        
        </table>
        <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>Datos técnicos aprox.:</strong> Circuito de Tres Tirolesas / Altura de las tirolesas: 21 metros (70 pies). / Altura del descenso en rappel: 21 metros (70 pies).</td>
            </tr>
        </table>
            <br/>
        <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;"><strong>Días de operación:</strong></td>
        </tr>
        <tr>
                <td style="width:34;"> • <strong>Español e Inglés: </strong>Todos los días</td>
        </tr>
        <tr>
                <td style="width:34;">• <strong>Francés:</strong> Lunes, Viernes y Domingo.</td>
        </tr>
        <tr>
                <td style="width:34;">•	<strong>Alemán:</strong> Martes, Jueves y Sábado.</td>
        </tr>
        <tr>
                <td style="width:34;">•	<strong>Italiano:</strong> Viernes y Domingos.</td>
        </tr>        
    </table>
    <!--<br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;"><strong>Precios Riviera Maya </strong></td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px"Snorkel Extreme</td>
                <td style="width:33%; border: 0.3px">US $ 110.00</td>
                <td style="width:33%; border: 0.3px">Por persona</td>
        </tr>        
    </table>
    <br/>
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:34%;"><strong>Precios Riviera Maya </strong></td>
        </tr>
        <tr>
                <td style="width:34%; border: 0.3px"Snorkel Extreme</td>
                <td style="width:33%; border: 0.3px">US $ 120.00</td>
                <td style="width:33%; border: 0.3px">Por persona</td>
        </tr>        
    </table>-->
<br/>        
    <table border="0" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Recomendaciones Importantes:</u></strong></td>
            </tr>
            <tr>
            <td style="width:100%;">Se requieren habilidades básicas de nado. Máscaras de prescripción médica disponibles. Este programa no es recomendable para personas que sufran de severos problemas físicos o motrices, con severos problemas del corazón o <strong>mujeres embarazadas.</strong>
            Personas bajo la influencia de alcohol o algún estupefaciente no podrán participar en este tour.</td>
        </tr>
        <tr>
            <td style="width:100%;"><strong>Qué llevar:</strong> Ropa y calzado confortable, lentes de sol y sombrero, traje de baño, camiseta adicional, toalla, sólo bronceadores y repelentes <strong>BIODEGRADABLES</strong>, dinero en efectivo (fotos, souvenirs y propinas). </td>
        </tr>           
    </table>
    <br>
    <table style="width:100%;">
        <tr>
            <td  style="width:100%;text-align:justify;  font-size:13px;"><strong>Notas: </strong>
                 Peso máximo permitido por participante: 135 kilos/ 300 libras aprox. &nbsp; &nbsp;  Talla: 44</td>
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
