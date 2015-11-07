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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>PRIVATE CATAMARAN HALF DAY SNORKELING TOUR</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/private_catamaran.jpg" width="211" height="142"/></td>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
Maybe the most fun and relaxing day you can have in Cancun. We have different private catamarans of different sizes available to charter for the whole group.) Departure from the bay side marina in the morning and relaxing sailing over the sparkling clear, turquoise sea to Isla Mujeres Bay with very friendly staff, open bar on board and music.
We will make the first stop to do incredible guided snorkeling tour in one of the famous coral reefs. During the relaxing sailing, we will make a stop to do spinnaker (swing with the Catamaran front sail high to the sky and jump from there to the sparkling sea; this is made solely in good climate conditions, we do not want you to end-up in Cuba). On the way, personnel will feast you with the "Tequila Celebration" and other cool drinks.</td>
</tr>
</table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>4 Hour private sailing tour</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Guided snorkeling tour to “Manchones” reef or other reefs depending of the weather conditions.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Open Bar with national drinks, beer on board of the Trimaran.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Snorkel equipment, (new tube)<strong></li></td>
        </tr>
    </table>
    <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Gratuities 15%</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Pier & Federal Reef access fee US $ 10.00 per pax</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Food and beverages in the Beach Club US$25.00 per person + 15% gratuity (basic buffet)</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Taxes</li></td>
        </tr>
    </table>
    <br/>
     <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:20%;text-align:left;"><strong>Price per hour on the Catamaran Minimum 4 hours</strong></td>
        </tr>
        <tr>
            <td style="width:20%;">Catamarán 36” Capacity 20 pax</td>
            <td style="width:20%;">US$ 220.00</td>
            <td style="width:20%;">Per hour, Min 4 hrs.</td>
        </tr>
        <tr>
            <td style="width:20%;">Catamarán 42” Capacity 40 pax</td>
            <td style="width:20%;">US$ 340.00</td>
            <td style="width:20%;">Per hour, Min 4 hrs.</td>
        </tr>
        <tr>
            <td style="width:20%;">Catamarán 44” Capacity 45 pax</td>
            <td style="width:20%;">US$ 400 00</td>
            <td style="width:20%;">Per hour, Min 4 hrs.</td>
        </tr>
        <tr>
            <td style="width:20%;">Catamarán 58” Capacity 60 pax</td>
            <td style="width:20%;">US$ 480 00</td>
            <td style="width:20%;">Per hour, Min 4 hrs.</td>
        </tr>
        <tr>
            <td style="width:20%;">Catamaran 78” Capacity 100 pax</td>
            <td style="width:20%;">US$ 570.00</td>
            <td style="width:20%;">Per hour, Min 4 hrs.</td>
        </tr>
        <tr>
            <td style="width:20%;text-align:left;"><strong>Transportation Cancun Hotels - Marina – Cancun Hotels</strong></td>
        </tr>
        <tr>
            <td style="width:20%;">Motor coach</td>
            <td style="width:20%;">US $ 600.00</td>
            <td style="width:20%;">Per Unit, Round Trip</td>
            <td style="width:20%;">Up to 45 pax</td>
        </tr>
        <tr>
            <td style="width:20%;">Vans</td>
            <td style="width:20%;">US $ 110.00</td>
            <td style="width:20%;">Per Unit, Round Trip</td>
            <td style="width:20%;">1-10 pax </td>
        </tr>
        <tr>
            <td style="width:20%;text-align:left;"><strong>Transportation Hotels in Riviera Maya - Marina – Hotels in Riviera Maya</strong></td>
        </tr>
        <tr>
            <td style="width:20%;">Motor coach</td>
            <td style="width:20%;">US $ 900.00</td>
            <td style="width:20%;">Per Unit, Round Trip</td>
            <td style="width:20%;">Up to 45 pax</td>
        </tr>
        <tr>
            <td style="width:20%;">Vans</td>
            <td style="width:20%;">US $ 110.00</td>
            <td style="width:20%;">Per Unit, Round Trip</td>
            <td style="width:20%;">1-10 pax </td>
        </tr>
        </table>
        <table style="width:100%;">
            <tr>
                <td width="15%" style="text-align:center;"><img src="../img/activities/private_catamaran1.jpg" width="211" height="142"/></td>
                <td width="76%" style="text-align:justify;  font-size:13px;">SUGGESTED ITINERARY:</td>
            </tr>
            <tr>
                <td width="15%" style="text-align:left;"></td>
                <td width="25%" style="text-align:justify; font-size:13px;">Participants ready at the hotel lobby:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">09:10 A.M.</td>
            </tr>
            <tr>
                <td width="15%" style="text-align:left;"></td>
                <td width="25%" style="text-align:justify; font-size:13px;">Departure to Marina:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">09:20 A.M.</td>
            </tr>
            <tr>
                <td width="15%" style="text-align:left;"></td>
                <td width="25%" style="text-align:justify; font-size:13px;">Arrival at Marina:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">09:45 A.M.</td>
            </tr>
            <tr>
                <td width="15%" style="text-align:left;"></td>
                <td width="25%" style="text-align:justify; font-size:13px;">Catamaran’s departure:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">10:00 A.M.</td>
            </tr>
            <tr>
                <td width="15%" style="text-align:left;"></td>
                <td width="25%" style="text-align:justify; font-size:13px;">Snorkeling time:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">11:00 A.M.</td>
            </tr>

            <tr>
                <td width="15%" style="text-align:left;"></td>
                <td width="25%" style="text-align:justify; font-size:13px;">Return to Cancún, Marina:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">02:00 P.M.</td>
            </tr>
            <tr>
                <td width="15%" style="text-align:left;"></td>
                <td width="25%" style="text-align:justify; font-size:13px;">Arrival at Marina:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">02:30 P.M.</td>
            </tr>
            <tr>
                <td width="15%" style="text-align:left;"></td>
                <td width="25%" style="text-align:justify; font-size:13px;">Arrival at the hotel:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">02:50 P.M.</td>
            </tr>
        </table>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><u><strong>Recommendations:</strong></u></td>
        	</tr>
        	<tr>
        		<td style="width:100%;"><li>Have swimming suits already on and remember bring towel, comfortable shoes or sandals, sunglasses, camera and maybe extra t-shirt.</li></td>
        	</tr>
        	<tr>
        		<td style="width:100%;"><li>Use only bio-degradable sun lotion if snorkeling in the coral reef</li></td>
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