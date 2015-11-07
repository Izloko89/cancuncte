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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>REEF PLUS</strong></td>
		</tr>
		</table>
    <table style="width:100%;">
			<tr>
            <td width="15%" style="text-align:center;"><img src="../img/activities/reef_plus.png" width="198" height="137"/></td>
				<td width="80%" style="width:65%;text-align:justify;  font-size:13px;">
Relax in a catamaran cruise, just before a snorkeling experience where colorful reefs await for you.<br/>
<br/>
<strong>Description</strong>
<br/>
Enjoy a relaxing catamaran cruise and admire the rich biodiversity of the Caribbean reefs. Your journey will end with a delicious meal and open bar (national drinks) in the Arrecifes restaurant in Maroma Adventures.
</td>
                
                </tr>
                 </table>
                 <br/>
                 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Includes</u></strong></td>
                <td style="width:50%;text-align:left;"></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Alcoholic and non alcoholic beverages with your meal</td>
                <td style="width:50%;text-align:left;">&nbsp;	Taxes</td>                 
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Snacks (tortilla chips and refried beans) served at the restaurant at the end of the tour Bilingual guide</td>
                <td style="width:50%;text-align:left;">&nbsp;	Local medical insurance</td>
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Lunch with the following menu: *Mixed Salad *Your choice of main course:chicken fajitas or beef fajitas or mixed fajitas or fish filet or hamburger * Dessert (ice cream)</td>
                <td style="width:50%;text-align:left;">&nbsp;	Use of snorkeling gear</td>
        </tr>              
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Not Included</u></strong></td>
                <td style="width:50%;text-align:left;"></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Lockers and key (A $5 USD deposit is required. At the end of the tour $2 USD will be returned)</td>
                <td style="width:50%;text-align:left;">&nbsp;	Marine Park tax ($2 USD per person, to be paid in cash on arrival)</td>                 
  </tr>
         <tr>
                <td style="width:50%;"></td>
                <td style="width:50%;text-align:left;">&nbsp;	Tips </td>
        </tr>                     
</table>
<br/>
 <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:20%;"><strong>Tour Price</strong></td>
        </tr>
        <tr>
                <td style="width:20%;">Reef Plus</td>
                 <td style="width:20%;">US $85.00</td>
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
                  <td style="width:20%;">Up to 45 pax</td>
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
                <td style="width:50%;">&nbsp;	Activity is not allowed for pregnant women, people with recent surgery or back problems</td>                
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Groups of 20 passengers are offered a boat for private trip</td>
         </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Maximum passenger capacity per shift: 350 (5 vessels with a capacity of 50 and one with a capacity of 100)</td>
                </tr>
                 <tr>
                <td style="width:50%;text-align:left;">&nbsp;	Children from 6 to 9 years pay child rate</td>               
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;	For groups, the program may be modified according to customer needs (optional open bar for $ 10 USD per person)</td>                
        </tr>                      
        </table>
        <br/>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong><u>Remarks:</u></strong></td>
                <td style="width:50%;text-align:left;"></td>
            </tr>
            <tr>
                <td height="20" style="width:50%;">&nbsp;	Biodegradable sunscreen</td>
                <td style="width:50%;text-align:left;">&nbsp;	Cash</td>                 
  </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Change of clothes</td>
                <td style="width:50%;text-align:left;">&nbsp;	Sunglasses</td>
        </tr>
         <tr>
                <td style="width:50%;">&nbsp;	Waterproof camera</td>
                <td style="width:50%;text-align:left;">&nbsp;	Swimsuit and towel</td>
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