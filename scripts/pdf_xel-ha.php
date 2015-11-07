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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XEL-HA</strong></td>
		</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<td width="15%" style="text-align:center;"><img src="../img/activities/xel-ha.jpg" width="198" height="137"/></td>
				<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
				 A visit to the world’s biggest natural aquarium, the beautiful inlet of Xel-Ha for a great snorkeling time.</td>
                </tr>
                 </table>
                  <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><strong><strong><u>Tour includes:</u></strong></strong></td>
            </tr>
            <tr>
                <td style="width:100%;">&nbsp;CTA Cancun supervision</td>
                 
        </tr>
         <tr>
                <td style="width:20%;"><p>&nbsp;Roundtrip transportation on a special Motor coach for 43 passengers with air-conditioning from the hotel</p></td>
        </tr>
         <tr>
                <td style="width:20%;">&nbsp;Cold bottled water, disposable moist towels on board the motor coach</td>
        </tr>
         <tr>
                <td style="width:20%;">&nbsp;Entrance fee to Xel-Ha park and parking lot.</td>
        </tr>
         <tr>
                <td style="width:20%;">&nbsp;Buffet lunch with domestic open bar, ice cream, Snorkel equipment, Towel, Lockers and Showers </td>
        </tr>
        <tr>
                <td style="width:20%;">&nbsp;Taxes</td>
        </tr>         
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong><u>No included:</u> </strong></td>
            </tr>
            <tr>
                <td style="width:15%;text-align:left;">&nbsp;	Swimming with Dolphins.</td>
            </tr>
            <tr>
                <td style="width:15%;text-align:left;">&nbsp;	Gratuities US $ 5.00 per person</td>
            </tr>
        </table>
            <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>SUGGESTED ITINERARY:</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Participants ready at the Hotel lobby	</td>
                 <td style="width:20%;">:  8:20 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Motor coaches depart from Hotel</td>
                 <td style="width:20%;">:  8:30 A.M.</td>
        </tr> 
        <tr>
                <td style="width:20%;">Estimated arrival time at Tulum</td>
                 <td style="width:20%;">: 10.30 A.M.</td>
        </tr> 
        <tr>
                <td style="width:20%;">Explanation and Visit of the Site</td>
                 <td style="width:20%;">: 10.30/11.30 A.M.</td>
        </tr> 
        <tr>
                <td style="width:20%;">Participants ready to board the coaches</td>
                 <td style="width:20%;">: 11.45 A.M.</td>
        </tr> 
        <tr>
                <td style="width:20%;">Departure time to Xel-Ha	</td>
                 <td style="width:20%;">: 12.00 noon</td>
        </tr>
        <tr>
                <td style="width:20%;">Estimated arrival time at Xel-Ha</td>
                 <td style="width:20%;">: 12.15 P.M.</td>
        </tr> 
        <tr>
                <td style="width:20%;">Free time at Xel-Ha</td>
                 <td style="width:20%;">: 12.15 – 3.15 P.M.</td>
        </tr> 
        <tr>
                <td style="width:20%;">Participants ready to board the coaches</td>
                 <td style="width:20%;">: 3.30 P.M.</td>
        </tr>         <tr>
                <td style="width:20%;">Departure to Cancun</td>
                 <td style="width:20%;">: 3.45 P.M.</td>
        </tr> 
        <tr>
                <td style="width:20%;">Estimated arrival time at the Hotel</td>
                 <td style="width:20%;">: 5.30 P.M.</td>
        </tr>
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>* Tour Cost  </strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Private Tour </td>
                 <td style="width:20%;">US $ 100.00</td>
                  <td style="width:20%;">Per person</td>
        </tr>        
    </table>
<br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong><u>Remarks:</u></strong></td>
            </tr>
            	Comfortable clothing is suggested: Bermudas, T-shirts, Tennis Shoes, Sunglasses, swimming suit and Cameras, biodegradable Suntan lotion
            <tr>
                <td style="width:100%;">&nbsp;Comfortable clothing is suggested: Bermudas, T-shirts, Tennis Shoes, Sunglasses, swimming suit and Cameras, biodegradable Suntan lotion  </td>
        </tr>
           <tr>
                <td style="width:100%;">&nbsp;Local Arts & Crafts offered at the stores; bring cash </td>
        </tr>
        <tr>
                <td style="width:100%;">	Professional Cameras require a special permission from Federal Authorities; process will take at least 20 days. </td>
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