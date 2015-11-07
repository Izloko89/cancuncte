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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>RIO SECRETO</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
    Dare to live a fantastic experience in one of the most incredible locations in the Riviera Maya.
Enter this natural museum filled with a kaleidoscope of speleothems and walk an easy 600m route amidst the thousands of stalactites and stalagmites found in this protected natural reserve.
Learn and be amazed in this ancient, magical underground world unexplored for millions of years.
</td>
</tr>
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
    Arrival at Rio Secreto. Here the guides will: meet visitors, brief visitors regarding site attractions, usage, and safety and provide visitors with all appropriate clothing and equipment.
Visitors will then be guided on a one and a half hour (or 600 meter) tour of Rio Secreto, walking and swimming along the route.
<br/>
A light lunch will be enjoyed after this unique and exciting experience.
</td>
</tr>
</table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Van A/A round trip Hotel – Rio Secreto – Hotel </li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Transfer to the River entrance. </li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Lockers</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Equipment: Helmet with light, flash light, wetsuit, special shoes and life vest (optional)</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>River entrance fee</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Light Lunch</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>CTA  staff to Coordinate activity</li></td>
        </tr>
    </table>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>16% tax</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Gratuity $5.00 usd per person</li></td>
        </tr>
    </table>
    <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><u><strong>Suggested itinerary:</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">09:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from the hotel:</td>
                 <td style="width:20%;">09:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at Rio Secreto:</td>
                 <td style="width:20%;">10:45 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Guided Tour</td>
                 <td style="width:20%;">11:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Lunch:</td>
                 <td style="width:20%;">12:45 P.M.-01:45 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure from Rio Secreto:</td>
                 <td style="width:20%;">02:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at the hotel:</td>
                 <td style="width:20%;">03:00 P.M.</td>
        </tr>
        </table>
         <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><u><strong>Schedule: &nbsp; &nbsp; &nbsp;9:00 A.M. / &nbsp;11 A.M. &nbsp;/ 1:00 P.M.</strong></u></td>
        	</tr>
        	<tr>
        		<td style="width:100%;"><li>* The order of these activities may vary depending on daily logistics</li></td>
        	</tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Tour Cost:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>2.30 hrs Tour</li></td>
            <td style="width:15%;text-align:left;"><li>US $ 91.00</li></td>
             <td style="width:15%;text-align:left;"><li>Per person</li></td>
        </tr>
    </table>
    <br/>
     <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><strong>Transportation to Rio Secreto</strong></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Motorcoach 45 pax</li></td>
            <td style="width:15%;text-align:left;"><li>US $ 950.00</li></td>
             <td style="width:15%;text-align:left;"><li>Per unit, round trip</li></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Van max 10 pax</li></td>
            <td style="width:15%;text-align:left;"><li>US $ 150.00</li></td>
             <td style="width:15%;text-align:left;"><li>Per unit, round trip</li></td>
         </tr>
         <tr>
             <td style="width:15%;text-align:left;"><strong>Capacity: 48 people per turn</strong></td>
        </tr>
    </table>
    <br/>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/rio_secreto.jpg" width="326" height="212"/></td>
            <td width="76%" style="width:65%;text-align:justify;  font-size:13px;"><strong><u>Recommendations:</u></strong>
                <li>  Comfortable clothing and walking shoes, (no flip-flops!), short-sleeved T-shirt is recommended</li>
                <li>Bathing suit on, additional set of dry clothes</li>
                <li>Credit card or cash for tips, photos, videos, crafts and souvenirs</li>
                <br/><strong><u>Restrictions:</u></strong>
                <br/>
                <li>Maximum weight: 250lb /120 kg</li>
                <li>Minimum age: 6 years</li>
                <li>This tour is not available for people with severe physical handicaps, heart diseases, pregnant women or for people under the influence of alcohol or drugs.</li>
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