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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>PUNTA VENADO</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/punta_venado.jpg" width="211" height="142"/></td>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">Punta Venado is an ecological adventure that extends itself in 2060 hectares of forest and 3.72 miles of beach in the heart of the Riviera Maya in the Mexican Caribbean. It also offers a traditional Mexican Hacienda surrounded by unique Caribbean style stables, pets and traditional buildings as region styled palapas that figure a traditional Mayan village in the middle of the virgin tropical jungle. 
Explore and discover the natural flora as the characteristic ceiba trees, palm trees, sea grapes, poplar and zapotes as well as a big variety of colorful birds, small cats, mammals and reptiles, particularities of this beautiful region.
</td>
</tr>
</table>
<table style="width:100%;">
<tr>

<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
You will admire the beauty of its white sand beaches, surrounded by an amazing amount of coral reefs, the second of the world by its size, and practice kayaking in the protected reef in shape of a big tube. 
Nothing is better than enjoy a horse ride in the jungle or at the beach, or the exciting ATV trails, as well as snorkeling in an amazing reef just by the beach. The children will also enjoy kayaking, swimming in beautiful cenotes and live a real adventure by exploring the caves or simply relaxing on the beach and enjoying the scenery that offers Punta Venado.
</td>
</tr>
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
Start the ATV adventure expedition through the jungle with a walking tour into the exciting cave and visit the beautiful cenote. A couple of minutes away from the cenote continue through the jungle, and you will find the beach and go snorkeling in the reef barrier. Returning from the water, some snacks, soft drinks and a white sandy beach will be waiting for your rest and refreshment. The trip ends after a ride along the beach to the Rancho. The rest of the day can be spent at El Coprero Beach Club.
</td>
</tr>
</table>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Entrance fee, ATV, helmet, goggles, lanterns, snorkeling equipment, soft drinks and snacks.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Shared Transportation</li></td>
        </tr>
    </table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:55%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Gratuities $5.00 USD per pax</li></td>
        </tr>       
    </table>
    <table style="width:100%;">
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">Horse ride in Punta Venado:
</td>
</tr>
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
A horse will be attributed to you according to your age. This activity is suitable for the whole family. El The program includes a tour along the coast, returning to the ranch through one side of the beach where you can find lush vegetation, and a free time enjoying a beautiful Cenote where you can swim or just relax.
 Expeditions 09:00 A.M. / 12:00 A.M./ 03:00 P.M.
Capacity:  Groups containing 14 participants maximum
<td width="15%" style="text-align:center;"><img src="../img/activities/horse.jpg" width="211" height="142"/></td>
</tr>
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
<strong>Includes: </strong>entry price, horseback riding and 1 bottle of water</td>
</tr>
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
<strong>Duration:</strong>1 hour and 15 minutes</td>
</tr>
</table>

<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/horse2.jpg" width="211" height="142"/></td>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
Meeting in Punta Venado to start the ATV expedition through the jungle, until arriving at a completely dark and exciting cave. This cave is located 5 minutes away by a walk through the jungle track.
One inside the cave, you need to go and admire the ancient rock formations by using flashlights, as it is completely dark. The ATV expedition goes on until a beautiful cenote where you can capture the best photos, swim or just take a little break. 
</td>
</tr>
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
Expeditions  09:00 A.M. / 12:00 A.M./ 03.00 P.M.
</td>
</tr>
<tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
Capacities: Groups of 15 persons maximum
</td>
</tr>
</table>  
 <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>ATV for 1 or 2 people, helmet, goggles, flashlights and 1 bottle of water</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Duration: 1 hour and 45 minutes. Minimal age to drive: 16 years
</li></td>
        </tr>
    </table>
    <table style="width:100%;">
    <tr>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
Snorkeling and Cenote:
 </td>
 </tr>
    <tr>
<td width="76%" style="width:65%;text-align:justify; font-size:13px;">
Discover the beauty of one of the most impressive barriers of coral reefs in the Riviera Maya. You will enjoy a fascinating travel by snorkeling in a few deep reefs with gentle currents, where you will have an amazing visibility under the water.  The rest of the day could be spent at the “Coprero Beach Club”.
Capacity: Groups of 50 people maximum </td>
 </tr>
 <tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/horse2.jpg" width="211" height="142"/></td>
</tr>
<tr>
            <td style="width:15%;text-align:left;font-size:13px;"><u><strong>Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;font-size:13px;">Entry price, snorkeling o kayaking material, water bottles and</td>
        </tr>
         <tr>
            <td style="width:15%;text-align:left;font-size:13px;"><u><strong>Duration:</strong>3 hours</u></td>
        </tr>
         <tr>
            <td style="width:15%;text-align:left;font-size:13px;">Expeditions:  09:00 A.M. / 11:00 A.M.</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;font-size:13px;">Schedule: From 9:00 A.M. to 05:00 P.M. From Monday to Sunday.</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;font-size:13px;"><u><strong>Price:</strong></u></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;font-size:13px;">$ 20 USD for the beach club’s entry.</td>
        </tr>
        <br/>
        <tr>
            <td style="width:15%;text-align:left;font-size:13px;"><u><strong>Suggested itinerary:</strong></u></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;font-size:13px;">Participants ready at the hotel lobby:</td>
             <td style="width:15%;text-align:left;font-size:13px;">07:30 A.M.</td>
        </tr>
         <tr>
            <td style="width:15%;text-align:left;font-size:13px;">Departure from the hotel:</td>
            <td style="width:15%;text-align:left;font-size:13px;">07:45 A.M.</td>
            
        </tr>
         <tr>
            <td style="width:15%;text-align:left;font-size:13px;">Arrival at Punta Venado:</td>
            <td style="width:15%;text-align:left;font-size:13px;">08:45 A.M.</td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;font-size:13px;">Departure from Punta Venado:</td>
             <td style="width:15%;text-align:left;font-size:13px;">03:00 P.M.</td>
        </tr>
         <tr>
            <td style="width:15%;text-align:left;font-size:13px;">Arrival at hotel:</td>
             <td style="width:15%;text-align:left;font-size:13px;">04:00 P.M.</td>
        </tr>

</table>
        <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">           
         <tr>
                <td style="width:20%;">Tour Price:</td>
                 <td style="width:20%;">8:00 A.M.</td>
        </tr>
         <tr>
                <td style="width:20%;">Tour Horse riding</td>
                 <td style="width:20%;">US $ 73.00</td>
                  <td style="width:20%;">Pro person</td>
        </tr>
        <tr>
                <td style="width:20%;">Tour ATV </td>
                 <td style="width:20%;">US $ 72.00</td>
                  <td style="width:20%;">Pro person</td>
        </tr>
        <tr>
                <td style="width:20%;">Tour Snorkeling</td>
                 <td style="width:20%;">US $ 52.00</td>
                  <td style="width:20%;">Pro person</td>
        </tr>
        <tr>
                <td style="width:20%;">Van Min. 8 pax Max. 10 pax</td>
                 <td style="width:20%;">US $ 200.00</td>
                  <td style="width:20%;">Per unit / Open Service (6 hours)</td>
        </tr>
         <tr>
                <td style="width:20%;">Van Min. 8 pax Max. 10 pax</td>
                 <td style="width:20%;">US $ 200.00</td>
                  <td style="width:20%;">Per unit / Open Service (6 hours)</td>
        </tr>
        <tr>
                <td style="width:20%;">Motor coach up to 45pax</td>
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