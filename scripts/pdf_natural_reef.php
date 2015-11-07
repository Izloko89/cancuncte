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
</style>
<table style="width:100%;" class="celda_color">
    <tr>
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>NATURAL REEF AT ISLA MUJERES “GARRAFON”</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/natural_reef.jpg" width="701" height="204"/></td>
        </tr>
        <tr>
            <td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
                “Wake Up your Senses with the magic of a Coral reef in a cliff, amazing water activities and archeological ruins in the middle of the sea, which get merged with the mysticism of the Mayan culture to offer you the spiritual tranquility in combination with exciting experiences that make you enjoy the nature that the Mexican Caribbean has.
            </td>
</tr>
<tr>
            <td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
                Enjoy snorkeling in this unique coral reef or a panoramic walk between an incredible naturally sculptured cliff and the Caribbean, or just relax in the beautiful infinity swimming pool. After enjoying all the activities at Garrafon, discover your dolphin nature with our famous “Dolphin Encounter” at Dolphin Island.
            </td>
</tr>

</table>
<table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:15%;text-align:left;"><u><strong>Includes:</strong></u></td>
    </tr>
    <tr>
        <td style="width:55%;"><li>Disposable Moist towels on ground transportation.</li></td>
    </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Round-trip transportation from the Marina to Isla Mujeres (Garrafon)</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Beverages on board and at the park.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Entrance fee.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Continental Breakfast and Buffet Lunch</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Snorkeling equipment, Life vests, Sky Line & Swimming Pool</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Swimming with Dolphins</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>CTA Bilingual personnel.</li></td>
        </tr>
    </table>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Gratuities – US $5.00 per person.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Photos and videos with dolphins</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>The $3.00 USD fee at the moment of getting registered in Aqua World</li></td>
        </tr>
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><strong>SUGGESTED ITINERARY:</strong></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the hotel lobby:</td>
                 <td style="width:20%;">09.00 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Motor coaches depart from Hotel:</td>
                 <td style="width:20%;">09.10 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Estimated arrival time at the Marina:</td>
                 <td style="width:20%;">09.40 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Depart to Garrafon Park:</td>
                 <td style="width:20%;">10.00 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Arrival at Garrafon Park:</td>
                 <td style="width:20%;">10.45 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Free time at Garrafon Park:</td>
                 <td style="width:20%;">11.00- 2:300 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Participants ready to board the Boat:</td>
                 <td style="width:20%;">02.30 P.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Departure to Dolphin Island:</td>
                 <td style="width:20%;">02.35 P.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Swimming with the Dolphins:</td>
                 <td style="width:20%;">03.00-03.50 P.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Departure to Cancun:</td>
                 <td style="width:20%;">04.00 P.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Arrival at the Marina:</td>
                 <td style="width:20%;">04.40 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at the hotel:</td>
                 <td style="width:20%;">05.00 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong><u>Tour price:</u></strong></td>
            </tr>
            <tr>
            <td style="width:20%;">Garrafon Discovery  and Royal Swim</td>
            <td style="width:20%;">US $172.00</td>
            <td style="width:20%;">Per person</td>
        </tr>
    </table>
    <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong>Transportation from Cancun</strong></td>
            </tr>
            <tr>
                <td style="width:15%;text-align:left;">Motor coach for 45 pax</td>
                <td style="width:15%;text-align:left;">US $ 660.00</td>
                <td style="width:15%;text-align:left;">Per unit, round trip</td>
            </tr>
            <tr>
                <td style="width:15%;text-align:left;">Van max 10 pax</td>
                <td style="width:15%;text-align:left;">US $ 120.00</td>
                <td style="width:15%;text-align:left;">Per unit, round trip</td>
            </tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Remarks:</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Schedule is suggested and will be adapted according to the group needs.</td>
        </tr>
        <tr>
                <td style="width:20%;">Comfortable clothing is suggested: Bermudas, T-shirts, Swimsuit, and Tennis Shoes & Cameras.</td>
        </tr>
        <tr>
                <td style="width:20%;">Bring Hotel Towels.</td>
        </tr>
        <tr>
                <td style="width:20%;">Biodegradable Suntan lotion is allowed, others will be kept at the entrance.</td>
        </tr>
        <tr>
                <td style="width:20%;">Food and beverages may not be brought into.</td>
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