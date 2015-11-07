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

<!--
<ul>
	<li>CTA Cancun supervision (1 staff)</li>	<li>Round trip transportation on a deluxe motor coach, with AC, and toilettes. </li>
	<li>Cold bottled water and disposable moist towels on board the motor coach.</li>
	<li>Entrance fee to Xcaret Park and parking.</li>
	<li>More than 20 different activities</li>
	<li>Buffet lunch, unlimited; water, fresh fruit flavored water & coffee during lunch.</li>
	<li>Snorkel equipment and showers </li>
	<li>Lockers</li>
	<li>Prefer Service at Plus Area</li>
	<li><strong>Xcaret “Mexico Spectacular “ Show</strong></li>
</ul>-->

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