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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>PRIVATE TULUM EXPRESS</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/private_tulum.jpg" width="211" height="142"/></td>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">The famous “walled city” is built under a cliff overlooking the turquoise waters of the Caribbean Sea next to white sandy beach, which makes it spectacular to see. Tulum was inhabited during the postclassical period 900-1521 A.C.; its main buildings are the Castle, the Temple of the Descending God, and Temple of the Initial Series. Pure Mayan Architecture and Pre-Hispanic paintings, impressive murals are well preserved in the sample of the Frescoes.
</td>
</tr>
</table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Tours Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>CTA Cancun Coordination</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Transportation to the Tulum ruins on deluxe air-conditioned motor coaches or deluxe vans depending of the number of the participants (minimum 8 pax)</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Cold bottled water and disposable moist towels on transportation</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Guided Tour with professional bilingual guide</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>“Train” transportation at the Tulum Ruins</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Entrance fee</li></td>
        </tr>
    </table>
    </table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:55%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Gratuities - $5.00 USD per person</li></td>
        </tr>
        <tr>
            <td style="width:55%;text-align:left;"><li>Additional tour Guide US$150.00</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Taxes</li></td>
        </tr>
         
    </table>   
        <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><u><strong>SUGGESTED ITINERARY:</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">Participants ready at the Hotel lobby:</td>
                 <td style="width:20%;">8:00 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure to Tulum</td>
                 <td style="width:20%;">8:15 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at Tulum</td>
                 <td style="width:20%;"> 10:30 A.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Guided tour in Tulum</td>
                 <td style="width:20%;">10:30 A.M-12:30 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Free time in Tulum</td>
                 <td style="width:20%;">12:30 P.M.- 01:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Departure to Cancún</td>
                 <td style="width:20%;">01:00 P.M.</td>
        </tr>
        <tr>
                <td style="width:20%;">Arrival at the hotel</td>
                 <td style="width:20%;">02:30 P.M.</td>
        </tr>        
        </table>
        <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;text-align:left;"><u><strong>Tour (4 HOURS)</strong></u></td>
            </tr>
            <tr>
                <td style="width:20%;">Private Tour </td>
                 <td style="width:20%;">US $ 68.00</td>
                  <td style="width:20%;">Per person</td>
                  <td style="width:20%;">Minimum 35 pax</td>
        </tr>
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Recommendations:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Use comfortable shoes, casual clothes, bring towel and swimming suit if you want to have dip in the ocean next to the Tulum ruins, hat, sun classes and camera.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Bring cash for local handicrafts.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li><u>Only biodegradable  sun block is allowed</u></li></td>
        </tr> 
        <tr>
            <td style="width:15%;text-align:left;"><li>	Professional Cameras require a special permission from Federal Authorities; process will take at least 30 days. </li></td>
        </tr>      
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Remarks:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Schedule is suggested and will be adapted according to the group needs.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Professional Cameras require a special permission from Federal Authorities; process will take at least 30 days. </li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Only biodegradable  sun-block is allowed</li></td>
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