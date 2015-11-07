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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>SELVATICA CANOPY TOUR</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/selvatica.jpg" width="701" height="204"/></td>
        </tr>
        <tr>
            <td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
            Upon arrival, all guests are greeted at the main “palapa”, where we have lockers to put away personal belongings. Our first activity will be the “Canopy Tour”. In order to do this our staff will equip each guest with harnesses, security pulleys, helmets, and gloves; our staff will check that all the equipment is properly fitted.
            <br/>
            <br/>
            After the canopy tour and a quick change to swimming trousers, the next activity takes place: this is mountain biking towards the “cenote” (sinkhole) which is one mile away. Our bikes are all aluminum Trek mountain bikes, specifically designed for this path; in this route you will witness the beauty of the regional Mayan jungle.            </td>
</tr>
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:15%;text-align:left;"><u><strong>Includes:</strong></u></td>
    </tr>
    <tr>
        <td style="width:55%;"><li>Round transportation</li></td>
    </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Canopy Tour</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Mountain bikes</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Swim in the cenote (sink hole)</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Light lunch and purified water</li></td>
        </tr>
    </table>
    <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Gratuities $5.00 USD per pax</li></td>
        </tr>
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><strong>SUGGESTED ITINERARY:</strong></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the hotel lobby:</td>
                 <td style="width:20%;">08:45 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Departure to Selvatica:</td>
                 <td style="width:20%;">09:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Arrival to Selvatica:</td>
                 <td style="width:20%;">09:45 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Selvatica Canopy Tour beginning:</td>
                 <td style="width:20%;">10.00 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Swimming at Cenote Verde Lucero :</td>
                 <td style="width:20%;">11:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Return to the Hotel:</td>
                 <td style="width:20%;">01:00 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong><u>Cost of the Tour:</u></strong></td>
            </tr>
            <tr>
            <td style="width:20%;">Canopy Tour</td>
            <td style="width:20%;">US $86.00</td>
            <td style="width:20%;">Per person</td>
        </tr>
    </table>
    <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong><u>Remarks:</u></strong></td>
            </tr>
            <tr>
                <td style="width:15%;text-align:left;">Comfortable footwear to walk in the jungle</td>
            </tr>
            <tr>
                <td style="width:15%;text-align:left;">Swimming gear </td>
            </tr>
            <tr>
                <td style="width:15%;text-align:left;">Extra T-shirt</td>
            </tr>
            <tr>
                <td style="width:15%;text-align:left;">Towel </td>
            </tr>
            <tr>
                <td style="width:15%;text-align:left;">Extra cash (tips, photos and souvenirs).</td>
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