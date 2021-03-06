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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>EXOTIC RIDES</strong></td>
		</tr>
		</table>    
                 <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/exotic_rides.png" width="198" height="137"/></td>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;">
Located in the center of Cancun, our private racetrack expands over 10 acres of land, with the beautiful Cancun Nichupte Lagoon as its backdrop.<br/>
Exotic Rides Mexico designed and built a 15000 sq. ft. building that encloses a multiple-use area, Lobby & Reception, Video-equipped classroom, Lounge, Coffee Shop, Restaurant, VIP Lounge for private functions, and a souvenir shop. Adjacent to this building we crafted the Pits & Workshop area with enough space for 20 of our Exotic Rides Mexico Super Cars and 30 exciting and fast Go Karts.
</td>
                </tr>
                 </table>                 
                 <br/>
                 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Track day:</u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Cars branded with your company’s logo.</td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Theoretical class on sports driving. </td>                
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Two recognition laps with an instructor.</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Five laps driving one of our cars (Ferrari 360, Ferrari F430, Ferrari 612, Audi R8, Mercedes-Benz SLS AMG y Lamborghini Gallardo)</td>
        </tr>              
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
                <td style="width:20%;">Track Day</td>
                 <td style="width:20%;">US $350.00</td>
                  <td style="width:20%;">Per person</td>
        </tr>                        
    </table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>GO KARTS:</strong></td>
        </tr>
        <tr>
                <td style="width:55%;">Races of 10 people for 15 minutes at our racetrack, and show your skills as a professional pilot. Fighting to reach the final and become the winning team.</td>
                 <td style="width:15%; border:0.3px"><strong>Go Karts</strong></td>
                  <td style="width:15%; border:0.3px">US $20.00</td>
                  <td style="width:15%; border:0.3px">Per person</td>
        </tr>
                            
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>HOT LAPS:</strong></td>
        </tr>
        <tr>
                <td style="width:55%;">Guests will be the companions of one of our professional driver as he tries to break the track's record. Choose one of our amazing cars (Ferrari 360, Ferrari 430, Audi R8, Mercedes- Benz SLS AMG or Lamborghini Gallardo)</td>
                 <td style="width:15%; border:0.3px"><strong>Hot Laps</strong></td>
                  <td style="width:15%; border:0.3px">US $85.00</td>
                  <td style="width:15%; border:0.3px">Per person</td>
        </tr>
                            
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Transportation Cancun Hotels – Exotic Rides Location – Cancun Hotels </strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Motor coach</td>
                 <td style="width:20%;">US $660.00</td>
                  <td style="width:20%;">Per Vehicle, Round Trip</td>
                  <td style="width:20%;">Up to 45 pax </td>
        </tr>
        <tr>
                <td style="width:20%;">Vans</td>
                 <td style="width:20%;">US $ 120.00</td>
                  <td style="width:20%;">Per Vehicle, Round Trip</td>
                  <td style="width:20%;">1-10 pax </td>
        </tr>                       
    </table>
    <br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Not Included</u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Meals</td>                 
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Lockers and key (with additional fee of $5 USD, at the end of the tour $2 USD will be returned)</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Tips</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Towels</td>
        </tr>                     
</table>
<br/>
                 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong><u>Tour Price</u></strong></td>
        </tr>
        <tr>
                <td style="width:20%;">ATV Maroma Paradise</td>
                 <td style="width:20%;">Single Ride US $67.00</td>
                  <td style="width:20%;">Per person</td>
        </tr>
        <tr>
                <td style="width:20%;">ATV Maroma Paradise</td>
                 <td style="width:20%;">Double Ride US 55.00</td>
                  <td style="width:20%;">Per person</td>
        </tr>                
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Round Trip Transportation Playa del Carmen- Marina</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Motor coach</td>
                 <td style="width:20%;">US $ 950.00</td>
                  <td style="width:20%;">Per Vehicle, Round Trip</td>
                  <td style="width:20%;">Up to 45 pax </td>
        </tr>
        <tr>
                <td style="width:20%;">Vans</td>
                 <td style="width:20%;">US $ 135.00</td>
                  <td style="width:20%;">Per Vehicle, Round Trip</td>
                  <td style="width:20%;">1-10 pax </td>
        </tr>                       
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong>Regulations</strong></td>               
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;	This activity is not permitted for pregnant women, people with recent surgery or back problems</td>                
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Maximum passenger capacity per shift: 350 (5 ships with a capacity of 50 and one with a capacity of 100)</td>
         </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Children from 6 to 9 years pay child rate</td>
                </tr>
                 <tr>
                <td style="width:50%;text-align:left;">&nbsp;With a guaranteed amount of 20 passengers a boat for private trip is assigned</td>               
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;	The program may be modified for group travels</td>                
        </tr>                      
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><strong><u>Remarks:</u></strong></td>                
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;	Biodegradable Sunscreen</td>
                                
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Extra money</td>
                
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Cap</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Sunglasses</td>
                
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Comfortable clothing</td>
        </tr>                     
</table>
<br/>
    <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/reef_plus.png" width="198" height="137"/></td>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;"><strong>Speed Boats</strong><br/>
Explore the Caribbean Sea aboard a speedboat that will take you for a high-speed fun adventure.<br/>
<br/>
<strong>Description</strong>
<br/>
Enjoy the thrill of driving your own speed boat for 30 minutes or 1 hour in the clear waters of the Caribbean, only here in Maroma Adventures, located on the beautiful Playa Maroma in the Riviera Maya, considered one of the best beaches in the world.</td>
                
                </tr>
                 </table>
                 <br/>
                 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Includes</u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Access to and use of facilities at the marina</td>
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Water and soft drinks at the end of the activity</td>
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Local medical insurance</td>
        </tr>              
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Not Included</u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Meals</td>                 
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Lockers and key (with additional fee of $5 USD, at the end of the tour $2USD will be returned)</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Tax for use of Marine Park (you must pay $2 USD in cash on arrival)</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Tips</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Towels</td>
        </tr>                     
</table>
<br/>
 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Tour Price</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Speed Boat </td>
                 <td style="width:20%;">Single Ride US $67.00</td>
                  <td style="width:20%;">Per person</td>
        </tr>
        <tr>
                <td style="width:20%;">Speed Boat </td>
                 <td style="width:20%;">Double Ride US $55.00</td>
                  <td style="width:20%;">Per person</td>
        </tr>                        
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Round Trip Transportation Playa del Carmen- Marina</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Motor coach</td>
                 <td style="width:20%;">US $ 950.00</td>
                  <td style="width:20%;">Per Vehicle, Round Trip</td>
                  <td style="width:20%;">Up to 57 pax</td>
        </tr>
        <tr>
                <td style="width:20%;">Vans</td>
                 <td style="width:20%;">US $ 135.00</td>
                  <td style="width:20%;">Per Vehicle, Round Trip</td>
                  <td style="width:20%;">1-10 pax</td>
        </tr>                
    </table> 
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong>Regulations</strong></td>               
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;	Minimum age required: 6 years</td>                
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Children 6 to 12 years old must be accompanied by a paying adult</td>
         </tr>
         <tr>
                <td style="width:50%;">&nbsp;	The rate will be determined according to the time of your choice (30 min or 1 h)</td>
                </tr>          
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Remarks:</u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Biodegradable sunscreen</td>                
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Extra money</td>
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Cap</td>
        </tr>
        <tr>
                <td height="20" style="width:50%;">&nbsp;	Sunglasses</td>                
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Comfortable clothing</td>
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Towel</td>
        </tr>              
</table>
<br/>
<table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/reef_plus.png" width="198" height="137"/></td>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;"><strong>Wave runners (Maroma Adventures)</strong><br/>
Enjoy a fun tour driving a fast wave runner through the crystal clear waters of the Caribbean Sea.<br/>
<br/>
<strong>Description</strong>
<br/>
Feel the Caribbean breeze while you enjoy an incredible adventure driving a wave runner for 30 minutes or 1 h. This tour includes soft drinks at the end of the activity and round-trip transportation.</td>
 </tr>
 </table>
 <br/>
 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Includes</u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Access to the marina and use of facilitiesa</td>
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Water and refreshments at the end of the activity</td>
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Local medical insurance</td>
        </tr>              
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Not Included</u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Meals</td>                 
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Lockers ($5 USD deposit is required. $2 USD will be returned at the end of the activities)</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Reef tax ($2 USD per person, to be paid directly at the reception of the Marina)</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Tips</td>
        </tr>
        <tr>
                <td style="width:50%;">&nbsp;	Towels</td>
        </tr>                     
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Tour Price</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Wave runners Maroma Paradise</td>
                 <td style="width:20%;">Half Hour US $55.00</td>
                  <td style="width:20%;">Per person</td>
        </tr>
        <tr>
                <td style="width:20%;">Wave runners Maroma Paradiset </td>
                 <td style="width:20%;">1 hour US $95.00</td>
                  <td style="width:20%;">Per person</td>
        </tr>                        
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Round Trip Transportation Playa del Carmen- Marina</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Motor coach</td>
                 <td style="width:20%;">US $ 950.00</td>
                  <td style="width:20%;">Per Vehicle, Round Trip</td>
                  <td style="width:20%;">Up to 57 pax</td>
        </tr>
        <tr>
                <td style="width:20%;">Vans</td>
                 <td style="width:20%;">US $ 135.00</td>
                  <td style="width:20%;">Per Vehicle, Round Trip</td>
                  <td style="width:20%;">1-10 pax</td>
        </tr>                
    </table>
    <br/>
    <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong>Regulations</strong></td>               
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;	Minimum age to participate: 6 years</td>                
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Children 6 to 12 years old must be accompanied by a paying adult</td>
         </tr>  
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Remarks:</u></strong></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Biodegradable sunscreen</td>                
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Change of clothes</td>
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Extra cash</td>
        </tr>
        <tr>
                <td height="20" style="width:50%;">&nbsp;	Cap, Sunglasses</td>                
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Towel</td>
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Swimsuit</td>
        </tr>              
</table>
<br/>
<table style="width:100%; text-align:center">
			<tr>
            <td width="25%" style="text-align:center;"><img src="../img/activities/exotic_rides2.png" width="198" height="137"/></td>
				
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