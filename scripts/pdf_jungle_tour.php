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
                <td style="width:100%; text-align:center; background-color:#066; color:#FFF;"><strong>JUNGLE TOUR</strong></td>
            </tr>
        </table>
<table style="width:100%;">
<tr>
<td width="15%" style="text-align:center;"><img src="../img/activities/jungle.jpg" width="211" height="142"/></td>
<td width="76%" style="width:65%;text-align:justify;  font-size:13px;">
Experience the excitement of driving your own two-person speedboat through lagoon and dense mangrove channels, entering to the second largest reef in the world for great snorkeling with multicolored fish and delicate coral formations.</td>
</tr>
</table>

         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Includes:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Two-persons speedboats.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Snorkeling equipment & life jackets.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Reef access. </li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Experienced bilingual tour guides<strong></li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Bottled water, soft drinks and disposable Moist Towels on board transportation<strong></li></td>
        </tr>
    </table>
    <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style="font-size:12px;width:100%;">
        <tr>
            <td style="width:15%;text-align:left;"><u><strong>Not Included:</strong></u></td>
        </tr>
        <tr>
            <td style="width:55%;"><li>Fee to protect the reefs – US $10.00</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Picture of the tour - $10.00 USD each.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Gratuities - $5.00 USD per person.</li></td>
        </tr>
        <tr>
            <td style="width:15%;text-align:left;"><li>Transportation Hotel – Marina - Hotel</li></td>
        </tr>
    </table>
    <br/>
     <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:20%;text-align:left;"><strong>Prices:</strong></td>
        </tr>
        <tr>
            <td style="width:20%;">Per Person</td>
            <td style="width:20%;">US $ 60.00</td>
            <td style="width:20%;">2 persons-speed boat</td>
        </tr>
        <tr>
            <td style="width:20%;">Per speed boat</td>
            <td style="width:20%;">US $ 120.00</td>
            <td style="width:20%;">If one person, full charge applies</td>
        </tr>            
        <tr>
        </table>
        <br/>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <td style="width:50%;text-align:left;"><strong>Transportation Hotel –Marina - Hotel</strong></td>
        </tr>
        <tr>
            <td style="width:20%;">Van for small groups up to 10 pax </td>
            <td style="width:20%;">US $ 120.00</td>
            <td style="width:20%;">Per Unit, Round Trip</td>
        </tr>        
        </table>
        <br />
        <table style="width:100%;">
            <tr>               
                <td width="25%" style="text-align:justify;  font-size:13px;"><u><strong>Suggested Itinerary:</strong></u></td>
            </tr>
            <tr>
                <td width="25%" style="text-align:justify; font-size:13px;">Participants ready at the hotel lobby:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">08.00 A.M..</td>
            </tr>
            <tr>
                <td width="25%" style="text-align:justify; font-size:13px;">Departure from the hotel:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">08.10 A.M.</td>
            </tr>
            <tr>
                <td width="25%" style="text-align:justify; font-size:13px;">Estimated arrival time at the Marina:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">08.30 A.M.</td>
            </tr>
            <tr>
                <td width="25%" style="text-align:justify; font-size:13px;">Guided tour:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">09.00 A.M.</td>
            </tr>
            <tr>
                <td width="25%" style="text-align:justify; font-size:13px;">Departure to the Hotel:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">12:00 P.M.</td>
            </tr>
            <tr>
                <td width="25%" style="text-align:justify; font-size:13px;">Arrival at the hotel:</td>
                <td width="25%" style="text-align:justify; font-size:13px;">12:20 P.M.</td>
            </tr>            
        </table>
        <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
            <tr>
                <td style="width:100%;text-align:left;"><u><strong>Remarks:</strong></u></td>
            </tr>
            <tr>
                <td style="width:100%;"><li>Schedules may vary according to the season.</li></td>
            </tr>
            <tr>
                <td style="width:100%;"><li>Participants have to be at the Marina 1/2 hour before tour departure.</li></td>
            </tr>
            <tr>
            <td style="width:100%;"><li>Speedboat for two, single person may pay full rate for the boat.</li></td>
            </tr>
             <tr>
            <td style="width:100%;"><li>Bathing suit, towels, hat, and sunglasses with string (to avoid them flying off the head), sandals, and biodegradable suntan lotion, waterproof or disposable photograph camera recommended.</li></td>
            </tr>
             <tr>
            <td style="width:100%;"><li>Locker compartments are available for towels or personal belongings.</li></td>
            </tr>
            <tr>
            <td style="width:100%;"><li>Minors fewer than 5 years of age, pregnant women, and persons with back problems are not recommended to use these services.</li></td>
            </tr>
        </table>
         <table border="0.5" cellpadding="0.5" cellspacing="0" style=" font-size:12px;width:100%;">
        <tr>
            <td style="width:20%;text-align:left;"><strong>*** For all activities Deluxe Box, lunch available/ different options from US$ 10.00 up tp US$ 22.00 per person Printed group’s logo in bag, optional US $2.00
Deluxe Nuts and dry fruit snack bag $10.00/ printed company logo in bag .Optional US $2.00
</strong></td>
        
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