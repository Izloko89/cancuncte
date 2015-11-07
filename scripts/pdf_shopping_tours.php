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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>SHOPPING TOURS</strong></td>
		</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
                We offer two options for great shopping tours; <strong>Playa del Carmen </strong>with its famous <strong>5th Avenue </strong>or <strong>Cancun’s combined Shopping City Tour</strong>, visiting local handicraft market at the Downtown Cancun and two of the most important shopping malls, <strong>Plaza Kukulcan Mall</strong> and <strong>La Isla Shopping Village at the Hotel Zone.</strong></td>
                </tr>
                 </table>
                  <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Includes:</u></strong></td>
                <td style="width:50%;text-align:left;"></td>
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;CTA Cancun supervision</td>
                <td style="width:50%;text-align:left;">&nbsp;Cold bottled water and disposable moist towels. </td>                 
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;Service for 5 hours</td>
                <td style="width:50%;text-align:left;">&nbsp;Professional Driver</td>
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;Transportation in deluxe motor coach with air conditioned </td>
                <td style="width:50%;text-align:left;">&nbsp;Bilingual government licensed tour guide</td>
        </tr>              
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:15%;text-align:left;"><strong><u>No included:</u> </strong></td>
            </tr>
            <tr>
                <td style="width:15%;text-align:left;">&nbsp;Gratuities $5.00 USD per pax</td>
            </tr>            
        </table>
            <br/>
            <table style="width:100%;">
			<tr>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;"><strong>5th Avenue at Playa del Carmen</strong><br/>
This long pedestrian street is full of Mexican souvenirs, boutiques and brand name shops, Caribbean style open-air restaurants and cafes and some very nice new shopping plazas, all just one block away from a very lively beach full of bars with live music and excellent sea food restaurants. 
</td>
                <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours1.jpg" width="198" height="137"/></td>
                </tr>
                 </table>
                 <table style="width:100%;">
			<tr>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;"><strong>Down Town Handcraft Market</strong><br/>
This very popular Market is located in the downtown of Cancun, full of little shops with the best samples of handcrafts from all over Mexico: silver, pottery, carved wooden items, masks, sarapes, sombreros, traditional dresses, hammocks, hand painted ceramics, traditional toys and so on, the list is endless.</td>
                <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours2.jpg" width="198" height="137"/></td>
                </tr>
                 </table>
                 <table style="width:100%;">
			<tr>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;"><strong>La Isla Shopping Mall</strong><br/>
<strong>La Isla</strong>, Mexico’s number one shopping mall, ultra trendy Shopping Village located on the Nichupte Lagoon under a giant canopy. Series of canals and small bridges give it a lovely Venetian look; in addition, there are over 150 stores the mall has a marina, interactive aquarium, many cafes, bars, restaurants and Movie Theater.</td>
                <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours3.png" width="198" height="137"/></td>
                </tr>
                 </table>
                 <table style="width:100%;">
			<tr>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;"><strong>Kukulkan Mall</strong><br/>
Cancun’s finest Mall with more than 250 fancy shops and the famed <strong>Luxury Avenue</strong>; international famous designers boutiques, haute couture dresses, fine jewelry, perfumes, brand name stores, fine Mexican art crafts and nice choice of excellent restaurants and cafes, all sheltered within a spacious modern air-conditioned areas.</td>
                <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours4.png" width="198" height="137"/></td>
                </tr>
                 </table>
                 <br/>
                 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong><u>Prices:</u></strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Shopping Tour Cancun</td>
                 <td style="width:20%;">US $560.00 </td>
                  <td style="width:20%;">Van for 4 hours  (min 6, max 8 pax)</td>
        </tr>
        <tr>
                <td style="width:20%;">Shopping Tour Playa del Carmen</td>
                 <td style="width:20%;">US $1,000.00</td>
                  <td style="width:20%;">Motor coach 4 hours  (minimum 35 pax)</td>
        </tr>        
    </table>
    <br/>
    <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours4.png" width="198" height="137"/></td>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;"><strong>City Tour</strong><br/>
Enjoy this pleasant ride through the city of Cancun. We'll take you to the most interesting places of the Hotel Zone and Downtown Cancun; a ride made exclusively for you. <br/>
<br/>
Know the Surrounds where you will see the Hotel Zone, Downtown, and travel through the main streets and residential areas, crafts centers before visiting the Wonderful New Maya Museum of Cancun.</td>
                
                </tr>
                 </table>
                 <br/>
                 <table style="width:100%;">
			<tr>            
				<td width="100%" style="width:65%;text-align:justify;  font-size:13px;"><strong>Downtown / Hotel Zone Cancun:</strong><br/>
Cancun in Quintana Roo State was the seat of the Itza that arrived from the South. Mayan people learned to live with the forest. There are Vestiges of their extraordinary greatness in Tulum, Coba City, Kohunlich, among others. There are remains of innumerable sites known but largely unexplored. No exaggeration to say that every piece of rainforest is a trace of its splendid culture.<br/>
<br/>
Based on these guidelines, the Bank of Mexico Infratur created in 1969 a program to carry out Integrated Resorts. Thus, studies were initiated to identify areas favorable for the implementation of tourism infrastructure projects and Cancun was selected as a priority for investment.
<br/>
<br/>
Thus arises the majestic city of Cancun today as the number 1 tourist destination worldwide. Today, boasts stunning Cancun hotels, villas and condos of big worldwide tourism chains are present in Cancun and provide malls of excellent quality. World's best shops with prestigious reputation can also be found in Cancun and the culinary art is the best for the most demanding palates, who will be pleased to discover in Cancun such varied food choices from fast food establishments to the finest restaurants.</td>
                
                </tr>
                 </table>
                 <br/>
    <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours4.png" width="198" height="137"/></td>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;"><strong>Cancun Maya Museum:</strong><br/>
After six years of work, the Museum Maya Cancun opened, designed as a large reservoir of this Ancient culture, one of the most recognized in the world. From 1964 to 1987 opened the National Museum of Anthropology and the Templo Mayor, as well as the National Institute of Anthropology and History (INAH).</td>
                
                </tr>
                 </table>
                 <table style="width:100%;">
			<tr>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;">
                Cancun Maya Museum has three exhibition halls, 1,350 m² square mounted. Two of them welcome permanent and temporary exhibitions of national and international levels. The museum tour begins with the skeletal remains of up to 14,000 years old, discovered in the last twelve years in Tulum’s underwater caves, spaces that offer great contributions to research on the arrival of man on the American continent.</td>
                <td width="15%" style="text-align:center;"><img src="../img/activities/shopping_tours4.png" width="198" height="137"/></td>
                </tr>
                 </table> 
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Includes:</u></strong></td>
                <td style="width:50%;text-align:left;"></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;CTA Cancun supervision</td>
                <td style="width:50%;text-align:left;">&nbsp;Cold bottled water and disposable moist towels.</td>                 
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;Service for 4 hours</td>
                <td style="width:50%;text-align:left;">&nbsp;Professional Driver</td>
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;Transportation in deluxe motor coach with air conditioned </td>
                <td style="width:50%;text-align:left;">&nbsp;Bilingual government licensed tour guide</td>
        </tr>              
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Duration:   </u></strong>4 hours</td>
            </tr>                      
</table>
<br/>
   <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:30%;"><strong>Transportation Hotel – City Tour - Restaurant – Hotel </strong></td>
        </tr>
        <tr>
                <td style="width:30%;">Van Max. 8 pax</td>
                 <td style="width:20%;">US $ 610.00</td>
                  <td style="width:20%;">Per Unit </td>
        </tr>
        <tr>
                <td style="width:30%;">Motor coach Max. 53pax</td>
                 <td style="width:20%;">US $1,150.00</td>
                  <td style="width:20%;">Per Unit </td>
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