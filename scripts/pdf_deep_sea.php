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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>DEEP SEA FISHING</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/deep_sea.jpg" width="211" height="142"/></td>
            <td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
Be a pioneer of sport fishing in Riviera Maya. Fishing Charters offers one of the shortest running time out to the deep water beyond the Continental Shelf. With their expert English-speaking captains you are virtually guaranteed to catch fish. Most of the crew has over 10 years of experience fishing these grounds. Their boats are fully equipped with quality Penn and Shimano tackle, depth sounders and G.P.S. Comfortably seating 6 persons in the shade, they feature large cockpits with fighting chair. All safety gear is provided. Traditionally the seas are mostly calm from February through August and are excellent for sails, marlin, tuna and dolphin.</td>
</tr>
</table>
<table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:15%;text-align:left;"><u><strong>Includes:</strong></u></td>
    </tr>
    <tr>
        <td style="width:55%;"><li>CTA Cancun Coordination</li></td>
    </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Experienced bilingual Captain and mate</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Very comfortably yachts fully equipped with quality tournament gear</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Safety Gear<strong></li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Cooler with soft drinks, beer and bottled water</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Bait</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Fishing licenses and fees<strong></li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Insurance</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Cold bottled water and disposable moist towels on board ground transportation.</li></td>
        </tr>
    </table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Gratuities 15 % </li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Food & Snacks</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Transportation, quoted below</li></td>
        </tr>
         <tr>
            <td style="width:15%;text-align:left;"><li>Federal Reef Taxes US$10.00 per person</li></td>
        </tr>
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><strong>Price per Yacht</strong></td>
            </tr>
            <tr>
                <td style="width:20%;">&nbsp;</td>
                 <td style="width:20%;">4 Hours</td>
                  <td style="width:20%;">6 Hours</td>
                  <td style="width:20%;">8 Hours</td>
        </tr>
        <tr>
            <td style="width:20%;">33’ & 34’ yachts (max capacity 8 pax) Recommended only 6 pax</td>
            <td style="width:20%;">US $690.00</td>
            <td style="width:20%;">US $790.00</td>
            <td style="width:20%;">US $890.00</td>
        </tr>
        </table>
        <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong>Transportation Hotel –Marina - Hotel in Cancun hotel Zone</strong></td>
            </tr>
            <tr>
                <td style="width:20%;">Van for small groups up to 10 pax</td>
                 <td style="width:20%;">US $120.00</td>
                  <td style="width:20%;">Per Unit, Round Trip</td>
        </tr>
        <tr>
                <td style="width:20%;">Motor coach Up to 45 pax</td>
                 <td style="width:20%;">US $660.00</td>
                  <td style="width:20%;">Per Unit, Round Trip</td>
        </tr>
    </table>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>Deluxe Box lunches from US $ 10.00  to US$22.00 per person, different options available</strong></td>
            </tr>
        </table>

<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong>SUGGESTED ITINERARY FOR 6-HOUR CHARTER:</strong></td>
            </tr>
        <tr>
                <td style="width:20%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">06.30 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure to the Marina:</td>
                 <td style="width:20%;">06.35 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival to the Marina:</td>
                 <td style="width:20%;">07:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Boats departure:</td>
                 <td style="width:20%;">07.30 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Return to the Marina</td>
                 <td style="width:20%;">02:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Back at the Hotel:</td>
                 <td style="width:20%;">02:30 P.M.</td>
        </tr>
        </table>
         <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        	<tr>
        		<td style="width:100%;text-align:left;"><strong>Recommendations:</strong></td>
        	</tr>
        	<tr>
        		<td style="width:100%;"><li> Bring sunglasses, sun block, and camera.</li></td>
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