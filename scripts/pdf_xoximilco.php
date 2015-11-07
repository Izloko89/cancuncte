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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>XOXIMILCO</strong></td>
		</tr>
		</table>    
                 <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/xoximilco.png" width="198" height="137"/></td>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;">
Let the beautiful Tour of Xoximilco take your breath with its traditional and musical atmosphere. Let your imagination take you away to the living memory of Mexico and of its golden age. <br/>In this place, the present meets the past in order to show you the country’s identity through festivities, friends, romance, nature and flavors. Decorated with motifs of the 32 states of Mexico, the famous trajineras that were used to transport flowers, fruits and vegetables harvested in the chinampas today are transportation and joy for themselves and others who seek to Xoximilco one time that may only be live here in Cancun, in its modern version of the legendary ride through the canals, with the color and charm of those magic moments in our memories. A real Mexican fiesta, flowers, songs of our land, flavors that reach the soul; an encounter with another time that exists in this new attraction as Mexican.</td>
                </tr>
                 </table>                 
                 <br/>
                 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Includes: </u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;•	Night Tour aboard a punt in Cancun,  </td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;•	Tasting dinner with the best of Mexican cuisine</td>                
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;•	Open bar tequila, beer, water and soft drinks, </td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;•	Typical live Mexican music such as mariachi, bolero group Jarocho group marimba.</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;• Transportation</td>                
        </tr>                       
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:100%;"><strong>Approximated duration:</strong> 3 hours</td>
                 
        </tr>                        
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Suggested itinerary: </u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;Participants ready at the hotel lobby:</td>
                <td style="width:50%;">&nbsp;07:30 P.M.</td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;Departure from the hotel:</td>
                <td style="width:50%;">&nbsp;07:45 P.M. </td>                
        </tr>
        <tr>
                <td height="20" style="width:50%;">&nbsp;Arrival at Xoximilco:</td>
                <td style="width:50%;">&nbsp;08:15 P.M.</td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">Trajinera tour and dinner:</td>
                <td style="width:50%;">&nbsp;08:30  P.M.-11:00 P.M.</td>                
        </tr>
        <tr>
                <td height="20" style="width:50%;">&nbsp;Departure from Xoximilco:</td>
                <td style="width:50%;">&nbsp;11:45 P.M.</td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">Arrival at hotel:          </td>
                <td style="width:50%;">&nbsp;12:15 P.M.</td>                
        </tr>                       
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:100%;"><strong>Appetizers:</strong> Dips (Squash blossoms, huitlacoche -corn smut-, annatto seed, Mayan Pumpkin seed, guacamole) and chips</td>
                 
        </tr>                        
</table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>First Course:</u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Tamales</td>
                <td style="width:50%;">&nbsp;	Fried silversides</td>                 
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Corn on a cup and pear squash</td>
                <td style="width:50%;">&nbsp;	Baby Crickets</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Oaxaca cheese ball</td>
                <td style="width:50%;">&nbsp;	Cactus pad salad </td>
        </tr>                   
</table>
<br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong>Second Course:</strong></td>               
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;	Steamed fish with wormseed</td>                
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Pork in green salsa (on corn leaf)</td>
         </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Chicken in mole sauce</td>
                </tr>
                 <tr>
                <td style="width:50%;text-align:left;">&nbsp;	Beef roll with corn smut sauce and cotija cheese (on a banana leaf)</td>               
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;	Shrimp with tamarind sauce</td>                
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Barbecued mutton</td>                
        </tr>
                              
</table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Third Course:</u></strong></td>                
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;	Corn Flan</td>
                                
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Milk caramel</td>
                
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Coconut sweet</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Pine nut milk fudge</td>
                
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Amaranth sweet</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Wafers</td>
                
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Oaxaca milled chocolate</td>
        </tr>                     
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Menu for children available</u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">If you have special food requirements, we have a gourmet menu for vegetarians, which must be requested on the day of purchase email: servicioalcliente@experienciasxcaret.com, or at the Customer service numbers.</td>
  </tr>          
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Recommendations: </u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">Children from 5 years Bring comfortable clothes, booking in advance 
Do not forget to bring cash to take home a souvenir photo and your visit 
Bring repellent chemical free
</td>
  </tr>          
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
          <tr>
            <td style="width:20%;"><strong><u>Cost:</u></strong></td>
          </tr>
          <tr>
            <td style="width:20%;">Tour</td>
            <td style="width:20%;">US$ 105.00</td>
            <td style="width:20%;">Per person + taxes</td>
          </tr>          
</table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:100%;"><strong>** Each trajinera (boat)  holds  20 pax, in order to keep the trajinare in a private basis 20 pax need to be paid</strong></td>
                 
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