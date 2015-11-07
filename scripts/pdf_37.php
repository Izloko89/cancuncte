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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XPLOR FUEGO</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="28%" style="text-align:center;"><img src="../img/activities/xplor_fuego.jpg" width="234" height="142"/></td>
<td width="72%" style="width:65%;text-align:justify;  font-size:13px;">
	Journey deep into the darkness of the jungle and be part of a new adventure where the night and fire will be your best companions. Follow your instincts and ignite your life with Xplor Fuego; a challenge in the darkness like you never imagined.</td>
</tr>
</table>


<table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:50%;"><li>Enjoy a 3.1-mile circuit to drive with Amphibious Vehicles (only people 18 or older may drive).</li></td>
        </tr>
        <tr>
            <td style="width:50%;text-align:left;"><li>580 yards of underground caves to paddle on a Raft.  A nine zip line circuit.</li></td>
        </tr>
        <tr>
            <td style="width:50%;text-align:left;"><li>Swim along 430 yards of crystal-clear water in the Stalactite River Swim. Equipment: helmet and life jacket</li></td>
        </tr>
        </table>
        <br/>
 <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>CTA Staff to coordinate the service.</li></td>
            <td style="width:15%;text-align:left;"><li>Locker for two</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Round trip transportation </li></td>
            <td style="width:15%;text-align:left;"><li>Dressing rooms and bathrooms</li></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Admission to Xplor Park Monday through Saturday from 5:30 a 10:30 p. m.</li></td>
            <td style="width:15%;text-align:left;"><li>Resting areas</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Equipment (life jacket, helmet, harness, paddles, raft, amphibian vehicle for two).</li></td>
            <td style="width:15%;text-align:left;"><li>Buffet and unlimited beverages (coffee, hot chocolate and fresh fruit flavored water).</li></td>
        </tr>
        </table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Not included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>$20.00 per Person as a deposit for the lockers.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Gratuities US $ 3.00 per person</li></td>
        </tr>
        </table>
        <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><u><strong>Suggested itinerary:</strong></u></td>
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

<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><u><strong><table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><u><strong>***Cost Xplor Fuego (fire)</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">All Inclusive</td>
                 <td style="width:20%;">US $110.00</td>
                  <td style="width:20%;">Per person, plus taxes</td>
                  <td style="width:20%;">Minimum 35</td>
        </tr>
        </table>

<br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><u><strong>Recommendations:</strong></u></td>
            </tr>
            <tr>
                <td style="width:100%;">Wear comfortable beachwear, water shoes, and extra change of clothes, swimsuit and towel. Sunscreen should be free of chemicals to be used in the park. If it contains any of these ingredients, it can be used within the Park: Benzophenone, Etilhexila, Homosalate, Octyl methoxycinnamate, octyl salicylate, Octinoxate Oxybenzone Methoxydibenzoylmethane Butyl.</td>
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
        <br/>


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