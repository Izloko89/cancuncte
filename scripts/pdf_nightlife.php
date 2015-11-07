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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>NIGHTLIFE IN CANCUN</strong></td>
		</tr>
		</table>  
        <table style="width:100%;">
			<tr>
				<td width="100%" style="width:65%;text-align:justify;  font-size:13px;">
The night life in Cancun is famous for its intensity and variety: from calm bars, lively Bubs with televising screens, lounges, colorful clubs with live entertainment that pulses on the rhythms of the Latin-Caribbean music to the discotheques that feature the very latest in audio and video entertainment with live bands and music that is always on the vanguard with the Top 40 around the world. <br/>
<br/>
The City has three principal areas, including a disco, a lounge and a bar terrace. “The Lounge” is located inside the club, and it provides an intimate atmosphere, with capacity for 200 persons.</td>
                </tr>
                 </table>
                 <br/>  
                 <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/nightlife.png" width="198" height="137"/></td>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;">
The City is the ultimate entertainment complex in Cancun, private parties as intimate as 50 to as large as 5,000. Enjoy any occasion with superb service in the VIP area, vibrant music and fun.<br/>
The City offers guests a variety of entertainment.</td>
                </tr>
                 </table>                 
                 <br/>
                 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
          <tr>
            <td style="width:20%;"><strong><u>Prices </u></strong></td>
          </tr>
          <tr>
            <td style="width:20%; border:0.3px">Domestic Open Bar and cover</td>
            <td style="width:20%; border:0.3px">US$80.00</td>
            <td style="width:20%; border:0.3px">Friday</td>
          </tr>
          <tr>
            <td style="width:20%; border:0.3px">Domestic Open Bar and  cover</td>
            <td style="width:20%; border:0.3px">US$65.00</td>
            <td style="width:20%; border:0.3px">Saturday</td>
          </tr>
                    
</table>
<br/>
<table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/nightlife2.png" width="198" height="137"/></td>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;">
Dady'O is with no doubt the best nightclub in Mexico. Its architecture recreates a mysterious and fascinating cavern, and after millions of years in the bottom of the Caribbean Sea, it emerges to the heart of Cancun, Mexico. It is characterized for its standards of high quality and it has a snack bar and terrace bar.  Discothèque has a capacity of 2500 people, offering the possibility of organizing private events.</td>
                </tr>
                 </table>
                 <br/>
                 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
          <tr>
            <td style="width:20%;"><strong><u>Price</u></strong></td>
          </tr>
          <tr>
            <td style="width:20%; border:0.3px">Domestic Open Bar and Cover</td>
            <td style="width:20%; border:0.3px">US$55.00</td>
            <td style="width:20%; border:0.3px">Monday, Thursday, Friday and Saturday</td>
          </tr>                    
</table>
<br/>
<table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/nightlife3.png" width="198" height="137"/></td>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;">
Here the Vegas Showtime meets the Party. With its 1.800 persons capacity, the multilevel seating location in the heart of the bustling Hotel Zone and nightly Rock & Roll and Salsa Bands makes of Coco Bongo Cancun’s most exciting and unique club. Add up the extraordinary Musical mix, 70’s & 80’s, Dance, Trance, Hip Hop & Rave and it’s easy to understand why it’s # 1 rated dance floor is always full. Coco Bongo features the very latest in audio and video entertainment, including a huge video screen, soap bubbles, balloons, confetti, streamers, and much, much more.</td>
                </tr>
                 </table>
                 <br/>
                 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
          <tr>
            <td style="width:20%;"><strong><u>Prices </u></strong></td>
          </tr>
          <tr>
            <td style="width:20%;">Domestic Open Bar and Cover</td>
            <td style="width:20%;">US$60.00</td>
            <td style="width:20%;">Monday to Wednesday</td>
          </tr>
          <tr>
            <td style="width:20%;">Domestic Open Bar and Cover</td>
            <td style="width:20%;">US$70.00 </td>
            <td style="width:20%;">Thursday to Sunday</td>
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