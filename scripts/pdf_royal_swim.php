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
        <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>ROYAL SWIM AT PUERTO AVENTURAS, COZUMEL & ISLA MUJERES</strong></td>
    </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/royal_swim.jpg" width="211" height="142"/></td>
            <td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
                Have you ever dreamed about swimming with dolphins in the beautiful waters of the Caribbean Sea? Today you can make your dreams come true! The Dolphin Royal Swim ® program is best defined with the words “action” and “speed”. It is the most dynamic of all the swim with dolphins programs, and this time, Dolphin Discovery Cancun will give you an experience to talk about for the rest of your life with family and friends.
                <br/>
                You will find interesting aquatic activities with these tender animals that seem to know you already. While you walk by the pier, you will notice them following you, waiting for their playtime. They will conquer your heart before you even get into the water. They will give you a handshake, kisses and will pull you with their dorsal tows to give you a speed ride! After that, the dolphins will push your feet to raise you up the water surface, you will feel like flying!
            </td>
</tr>
</table>
<table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
    <tr>
        <td style="width:15%;text-align:left;"><u><strong>Includes:</strong></u></td>
    </tr>
    <tr>
        <td style="width:55%;"><li>CTA Cancun supervision</li></td>
    </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Round trip transportation</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Island cruise</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Cold bottled water and disposable moist towels during the transportation</li></td>
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
        <tr>
            <td style="width:15%;text-align:left;"><li>Photo and Video of your experience with our dolphins.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Other activities in Cancun or Isla Mujeres.</li></td>
        </tr>
         <tr>
            <td style="width:15%;text-align:left;"><li>$3.00 USD dock fee per person that must be paid at Marina Aquatours check in.</li></td>
        </tr>
         <tr>
            <td style="width:15%;text-align:left;"><li>US$8.00 coral reef federal tax, per person </li></td>
        </tr>
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><strong>Suggested itinerary:</strong></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the hotel lobby:</td>
                 <td style="width:20%;">09:15 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Departure from the hotel:</td>
                 <td style="width:20%;">09:30 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Arrival at Marina:</td>
                 <td style="width:20%;">10:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Departure from Marina:</td>
                 <td style="width:20%;">10:15 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Arrival at Isla Mujeres:</td>
                 <td style="width:20%;">10:30 AM – 12:30 P.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Dolphins meeting:</td>
                 <td style="width:20%;">10:30 AM – 12:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure to the hotel:</td>
                 <td style="width:20%;">12:45 P.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Arrival at the hotel:</td>
                 <td style="width:20%;">01:15 P.M.</td>
        </tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong><u>Costs:</u></strong></td>
            </tr>
            <tr>
            <td style="width:20%;">Private tour</td>
            <td style="width:20%;">US $ 139.00</td>
            <td style="width:20%;">Per person</td>
             <td style="width:20%;">Isla Mujeres</td>
        </tr>
        </tr>
            <tr>
            <td style="width:20%;">Private tour</td>
            <td style="width:20%;">US $ 130.00</td>
            <td style="width:20%;">Per person</td>
             <td style="width:20%;">Puerto Aventuras</td>
        </tr>
         </tr>
            <tr>
            <td style="width:20%;">Private tour</td>
            <td style="width:20%;">US $ 130.00</td>
            <td style="width:20%;">Per person</td>
             <td style="width:20%;">Cozumel</td>
        </tr>
    </table>
    <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong>In order to keep the activity as private, we need a minimum of 16 pax, maximum per shift 20 pax</strong></td>
            </tr>
        </table>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Transportation ROYAL SWIM ISLA MUJERES:</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Motorcoach for 45 pax</td>
                 <td style="width:20%;">US $ 660.00</td>
                  <td style="width:20%;">Per Unit, Round Trip</td>
        </tr>
        <tr>
                <td style="width:20%;">Van max 10 pax</td>
                 <td style="width:20%;">US $ 120.00</td>
                  <td style="width:20%;">Per Unit, Round Trip</td>
        </tr>
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Transportation ROYAL SWIM COZUMEL:</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Motorcoach for 45 pax</td>
                 <td style="width:20%;">US $ 850.00</td>
                  <td style="width:20%;">Per Unit, Round Trip</td>
        </tr>
        <tr>
                <td style="width:20%;">Van max 10 pax</td>
                 <td style="width:20%;">US $ 130.00</td>
                  <td style="width:20%;">Per Unit, Round Trip</td>
        </tr>
    </table>
<br/>
 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Transportation ROYAL SWIM PUERTO AVENTURAS</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Motorcoach for 45 pax</td>
                 <td style="width:20%;">US $ 950.00</td>
                  <td style="width:20%;">Per Unit, Round Trip</td>
        </tr>
        <tr>
                <td style="width:20%;">Van max 10 pax</td>
                 <td style="width:20%;">US $ 150.00</td>
                  <td style="width:20%;">Per Unit, Round Trip</td>
        </tr>
    </table>
    <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Remarks:</u></strong></td>
            </tr>
        <tr>
                <td style="width:20%;"><li>Comfortable clothing is suggested: Bermudas, T-shirts, Tennis Shoes, Sunglasses, swimming suit and Cameras, biodegradable Suntan lotion I </li></td>
        </tr>
        <tr>
                <td style="width:20%;"><li>Local Arts & Crafts offered at the stores; bring cash</li></td>
        </tr>
         <tr>
                <td style="width:20%;"><li>Professional Cameras require a special permission from Federal Authorities; process will take at least 20 days. </li></td>
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