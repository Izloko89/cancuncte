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
		<td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>CTA CANCUN CANCELLATION POLICIES</strong></td>
		</tr>
		</table>    
                 <table style="width:100%;">
			<tr>
				<td width="80%" style="width:65%;text-align:center;  font-size:13px;"><u>
Contracted Sightseeing Tours</u></td>
                </tr>
                 </table>                 
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;">Sightseeing Tour = Archaeological Zones and Parks</td>
            </tr>
            <tr>
                <td style="width:50%;">&nbsp;72 hrs in advance:</td>
                <td style="width:50%;">No charge</td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">48 – 25 hrs in advance:</td>
                <td style="width:50%;">50% Penalty over guarantee</td>                
        </tr>
        <tr>
                <td style="width:50%;">24 hrs in advance or Same Day:</td>
                <td style="width:50%;">100% Penalty over guarantee</td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">No Shows:</td>
                <td style="width:50%;">100% penalty</td>                
        </tr>                 
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong>Food & Beverage on Contracted Sightseeing Tours</strong></td>
            </tr>
            <tr>
                <td  style="width:50%;"><strong>Changes or Modifications</strong></td>
                <td style="width:50%;"></td>
                                 
  </tr>
         <tr>
                <td style="width:50%;">Menu changes:</td>
                <td style="width:50%;">7 days in advance</td>                
        </tr>
        <tr>
                <td style="width:50%;">Final Guarantee:</td>
                <td style="width:50%;">72 hrs in advance</td>
                                 
  </tr>
  <tr>
                <td style="width:50%;"></td>
                <td style="width:50%;">&nbsp;</td>                
        </tr>
        <tr>
                <td style="width:50%;"><strong>Cancellations</strong></td>
                <td style="width:50%;"></td>
                                 
  </tr>
  <tr>
                <td style="width:50%;">Less than 72 hrs:</td>
                <td style="width:50%;">100% charge over guarantee&nbsp;</td>                
        </tr>
        <tr>
                <td style="width:50%;">No Shows:</td>
                <td style="width:50%;"><strong>Guarantee will be applied</strong></td>
                                 
  </tr>                         
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong>Water Sports Activities</strong></td>
            </tr>
         <tr>
                <td style="width:50%;">Snorkeling, Sailing, Diving, Water Toys, Sub See, Jungle Tour ext</td>
                <td style="width:50%;"></td>                
        </tr>
        <tr>
                <td style="width:50%;"></td>
                <td style="width:50%;"></td>
                                 
  </tr>
  <tr>
                <td style="width:50%;"></td>
                <td style="width:50%;">&nbsp;</td>                
        </tr>
        <tr>
                <td style="width:50%;">72 hrs in advance:</td>
                <td style="width:50%;">No charge</td>
                                 
  </tr>
  <tr>
                <td style="width:50%;">48 – 25 hrs prior:</td>
                <td style="width:50%;">50% Penalty over guarantee</td>                
        </tr>
        <tr>
                <td style="width:50%;">24 hrs in advance or same day:</td>
                <td style="width:50%;">100% Penalty over guarantee</td>
                                 
  </tr>                         
</table>
<br/>
<table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:50%;text-align:left;"><strong>Deep Sea Fishing</strong></td>
            </tr>
         <tr>
                <td style="width:50%;">8 Days in prior:</td>
                <td style="width:50%;">No charge</td>                
        </tr>
        <tr>
                <td style="width:50%;"></td>
                <td style="width:50%;"></td>
                                 
  </tr>
  <tr>
                <td style="width:50%;">1 – 7 Days prior:</td>
                <td style="width:50%;">100% Penalty over guarantee</td>                
        </tr>
        <tr>
                <td style="width:50%;"></td>
                <td style="width:50%;">&nbsp;</td>
                                 
  </tr>
  <tr>
                <td style="width:50%;text-align:left;"><strong>Team Building Events</strong></td>
            </tr>
  <tr>
                <td style="width:50%;">8 Days in prior:</td>
                <td style="width:50%;">No charge</td>                
        </tr>
        <tr>
                <td style="width:50%;">3 – 7 Days in advance:	</td>
                <td style="width:50%;">50% charge over guarantee</td>
                                 
  </tr>  
  <tr>
                <td style="width:50%;">2 – Same day:</td>
                <td style="width:50%;">100% Penalty over guarantee</td>
                                 
  </tr>
  <tr>
                <td style="width:50%;"><strong>No Shows:</strong></td>
                <td style="width:50%;">Guarantee will apply</td>                  
  </tr>                       
</table>
<br/>
<table style="width:100%;">
			<tr>
				<td width="80%" height="17" style="width:65%;text-align:center;  font-size:13px;"><strong>PLEASE CONSIDER 16% TAX OF 5% OF SERVICE FEE IN THE FINAL PRICE</strong></td>
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