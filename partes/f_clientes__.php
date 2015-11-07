<?php session_start(); 
include("../scripts/funciones.php");
include("../scripts/func_form.php");
include("../scripts/datos.php");
$emp=$_SESSION["id_empresa"];

try{
	$bd=new PDO($dsnw,$userw,$passw,$optPDO);
	$sql="SELECT
		*
	FROM clientes
	WHERE clientes.id_empresa=$emp;";
	$articulos=array();
	$res=$bd->query($sql);
	foreach($res->fetchAll(PDO::FETCH_ASSOC) as $d){
		$clientes[$d["id_cliente"]]=$d;
	}
}catch(PDOException $err){
	echo "Error: ".$err->getMessage();
}

?>
<style>
.dbc{
	cursor:pointer;
	color:#900;
}
</style>

<script>


$("#clave").on("change keyup paste", function(){
	//alert("entrando en el evento");
   //realizaProceso($('#clave').val());return false;
})

function realizaProceso(valorCaja2){
	/*
	valorCaja2 = document.getElementById("clave").value;

        var parametros = {

              

                "aidi" : valorCaja2

        };

		
		
        $.ajax({

                data:  parametros,

                url:   'select.php',

                type:  'post',

                beforeSend: function () {

                        $("#destino").html("Procesando, espere por favor...");

                },

                success:  function (response) {

                        $("#destino").html(response);

                }

        });*/

}

</script>

<script>



</script>

<script>
$(document).ready(function(e) {
    $(".nombre").focusout(function(e) {
		$(".razon").val($(this).val());
    });
	$( ".cliente_clave" ).keyup(function(e){
		_this=$(this);
		if(e.keyCode!=8 && _this.val()!=""){
			if(typeof timer=="undefined"){
				timer=setTimeout(function(){
					ClaveCliente();
					
				},300);
			}else{
				clearTimeout(timer);
				timer=setTimeout(function(){
					ClaveCliente();
					
				},300);
			}
		}else{
			resetform();
		}
    }); //termina buscador de cotizacion
	$(".dbc").dblclick(function(e) {
        accion=$(this).attr("data-action");
		val=$(this).text();
		switch(accion){
			case 'clave':
				$(".clave").val(val);
				scrollTop();
				ClaveCliente();
				realizaProceso($('#clave').val());return false;
			break;
		}
    });
	$("#f_proveedores select").change(function(e) {
		campo="."+$(this).attr("data-campo");
		$(campo).val($(this).val());
		clave=$('option:selected', this).attr("data-clave");
		nombre=$('option:selected', this).attr("data-nombre");
		$(".clave").val(clave);
		$(".nombre").val(nombre);
    });
	
	
	$(".guardar").click(function(e) {
		nombre = document.getElementById("nombre").value;
		limitecredito = document.getElementById("limitecredito").value;
		
		
		clave = document.getElementById("clave").value;
		direccion = document.getElementById("direccion").value;
		colonia = document.getElementById("colonia").value;
		ciudad = document.getElementById("ciudad").value;
		estado = document.getElementById("estado").value;
		cp = document.getElementById("cp").value;
		telefono = document.getElementById("telefono").value;
		celular = document.getElementById("celular").value;
		email = document.getElementById("email").value;
		rfcf = document.getElementById("rfcf").value;
		razonf = document.getElementById("razonf").value;
		nombrecomercialf = document.getElementById("nombrecomercialf").value;
		direccionf = document.getElementById("direccionf").value;
		coloniaf = document.getElementById("coloniaf").value;
		ciudadf = document.getElementById("ciudadf").value;
		estadof = document.getElementById("estadof").value;
		//procesamiento de datos
		$.ajax({
			url:'scripts/s_guardar_cliente.php',
			cache:false,
			async:false,
			type:'POST',
			data:{
				'nombre':nombre,
				'limitecredito':limitecredito,
				'clave':clave,
				'direccion':direccion,
				'colonia':colonia,
				'ciudad':ciudad,
				'estado':estado,
				'cp':cp,
				'telefono':telefono,
				'celular':celular,
				'email':email,
				'rfcf':rfcf,
				'razonf':razonf,
				'nombrecomercialf':nombrecomercialf,
				'direccionf':direccionf,
				'coloniaf':coloniaf,
				'ciudadf':ciudadf,
				'estadof':estadof
			},
			success: function(r){
				if(r){
					alerta("info",r.info);
					$(".volover").click();
				}else{
					alerta("error",r.info);
				}
			}
		});
    });
    $(".volver").click(function(e) {
		ingresar=true;
    	$("#formularios_modulo").hide("slide",{direction:'right'},rapidez,function(){
			$("#botones_modulo").fadeIn(rapidez);
		});
    });
});
</script>
<?php 	

$sql="SELECT
		MAX(id_cliente ) as cliente
	FROM clientes
	WHERE clientes.id_empresa=$emp;";
	$res=$bd->query($sql);
	$wea=$res->fetchAll(PDO::FETCH_ASSOC);
?>
<form id="f_clientes" class="formularios">
  <h3 class="titulo_form">CLIENTE</h3>
  	<input type="hidden" name="id_cliente" id="id_cliente" class="id_cliente" value="<?php echo $wea[0]["cliente"] + 1;?>"/>
    <div class="campo_form">
    <label class="label_width">CLAVE</label>
    <input type="text" name="clave" id="clave" class="clave cliente_clave text_corto requerido mayuscula"
	value="<?php echo $wea[0]["cliente"] + 1;?>">
    </div>
    <div class="campo_form">
    <label class="label_width">Nombre Comercial</label>
    <input type="text" name="nombre" id="nombre" class="nombre text_largo nombre_buscar">
    </div>
    <div class="campo_form">
    <label class="label_width">Nombre de Contacto</label>
    <input type="text" name="limitecredito" id="limitecredito" class="limitecredito text_mediano">
    </div>
    <input class="boton_dentro" type="reset" value="Limpiar" />
</form>

<table>
<tr>
<td>
<form id="f_clientes_contacto" class="formularios">
  <h3 class="titulo_form">DATOS DE CONTACTO</h3>
  <input type="hidden" name="id" class="id" />
  <input type="hidden" name="id_empresa" value="<?php echo $_SESSION["id_empresa"]; ?>" />
    <div class="campo_form">
        <label class="label_width">Giro</label>
        <input type="text" name="clave" id="clave" class="requerido mayuscula clave">
    </div>
    <div class="campo_form">
        <label class="label_width">Dirección</label>
        <input type="text" name="direccion" id="direccion" class="direccion">
    </div>
    <div class="campo_form">
        <label class="label_width">Colonia</label>
        <input type="text" name="colonia" id="colonia" class="colonia">
    </div>
    <div class="campo_form">
        <label class="label_width">Ciudad</label>
        <input type="text" name="ciudad" id="ciudad" class="ciudad">
    </div>
    <div class="campo_form">
        <label class="label_width">Estado</label>
        <input type="text" name="estado" id="estado" class="estado">
    </div>
    <div class="campo_form">
        <label class="label_width">Código Postal</label>
        <input type="text" name="cp" id="cp" class="cp">
    </div>
    <div class="campo_form">
        <label class="label_width">Telefono</label>
        <input type="text" name="telefono" id="telefono" class="telefono">
    </div>
    <div class="campo_form">
        <label class="label_width">Celular</label>
        <input type="text" name="celular" id="celular" class="celular">
    </div>
    <div class="campo_form">
        <label class="label_width">E-mail</label>
        <input type="text" name="email" id="email" class="email">
    </div>
</form>
</td>
<td >
<form id="f_clientes_fiscal" class="formularios" style="margin-left:30px;">
  <h3 class="titulo_form">INFORMACIóN FISCAL</h3>
  <input type="hidden" name="id" class="id" />
  <input type="hidden" name="id_empresa" value="<?php echo $_SESSION["id_empresa"]; ?>" />
    <div class="campo_form">
        <label class="label_width">RFC</label>
        <input type="text" name="rfc" id="rfcf" class="requerido mayuscula rfc">
    </div>
    <div class="campo_form" style="display:none;">
        <label class="label_width">Razón social</label>
        <input type="text" name="razon" id="razonf" class="razon">
    </div>
    <div class="campo_form">
        <label class="label_width">Razón social</label>
        <input type="text" name="nombrecomercial" id="nombrecomercialf" class="requerido nombrecomercial">
    </div>
    <div class="campo_form">
        <label class="label_width">Direccion Fiscal</label>
        <input type="text" name="direccion" id="direccionf" class="requerido direccion">
    </div>
    <div class="campo_form">
        <label class="label_width">Colonia</label>
        <input type="text" name="colonia" id="coloniaf" class="requerido colonia">
    </div>
    <div class="campo_form">
        <label class="label_width">Ciudad</label>
        <input type="text" name="ciudad" id="ciudadf" class="requerido ciudad">
    </div>
    <div class="campo_form">
        <label class="label_width">Estado</label>
        <input type="text" name="estado" id="estadof" class="requerido estado">
    </div>
    <div class="campo_form">
        <label class="label_width">Código Postal</label>
        <input type="text" name="cp" id="cpf" class="requerido cp">
    </div>
    </form>
	</td>
	</tr>
	</table>
	
	
	
	
	
    <div align="right">
        <input type="button" class="guardar" value="GUARDAR" data-wrap="#" data-accion="nuevo" data-m="pivote" />
        <input type="button" class="modificar" value="MODIFICAR" style="display:none;" />
    	<input type="button" class="volver" value="VOLVER">
    </div>
	
	<div class="formularios">
	<h3 class="titulo_form">Estado de Cuenta</h3>


<!--<input type="text" name="aidi" id="aidi" onchange="realizaProceso($('#aidi').val());return false;"></p>-->
<br>
<br>


<div id="destino" align="center"></div>
	
	
	</div>
</div>
<div class="formularios">
<h3 class="titulo_form">Listado de clientes registrados</h3>
	<table style="width:100%;">
    	<tr>
        	<th>CLAVE<br /><font style="font-size:0.4em; color:#999;">Doble Clic<br />para modificar</font></th>
            <th>NOMBRE</th>
        </tr>
        
    <?php if(count($clientes)>0){foreach($clientes as $art=>$d){
		echo '<tr>';
		echo '<td class="dbc" data-action="clave">'.$d["clave"].'</td>';
		echo '<td>'.$d["nombre"].'</td>';
		echo '</tr>';
	}//foreach
	}//if end ?>
    </table>
</div>
<script>
function ClaveCliente(){
	
	
	
	$(".id_cliente").val('');
	dato=$(".cliente_clave").val();
	input=$(".cliente_clave");
	input.addClass("ui-autocomplete-loading");
	$.ajax({
	  url:"scripts/busca_clientes1.php",
	  cache:false,
	  async:false,
	  data:{
		term:dato
	  },
	  success: function(r){
		clave=$(".cliente_clave").val();
		resetform();
		$(".cliente_clave").val(clave);
		$.each(r[0],function(i,v){
			/*$("."+i).text(v);
			$("."+i).val(v);*/
		});
		document.getElementById("clave").value = r[0].clave;
		document.getElementById("direccion").value = r[0].direccion;
		document.getElementById("colonia").value = r[0].colonia;
		document.getElementById("ciudad").value = r[0].ciudad;
		document.getElementById("estado").value = r[0].estado;
		document.getElementById("limitecredito").value = r[0].ncom;
		document.getElementById("cp").value = r[0].cp;
		document.getElementById("cpf").value = r[0].cpf;
		document.getElementById("telefono").value = r[0].telefono;
		document.getElementById("celular").value = r[0].celular;
		document.getElementById("email").value = r[0].email;
		document.getElementById("rfcf").value = r[0].rfcf;
		document.getElementById("razonf").value = r[0].razonf;
		document.getElementById("nombre").value = r[0].razonf;
		document.getElementById("nombrecomercialf").value = r[0].nombrecomercialf;
		document.getElementById("direccionf").value = r[0].direccionf;
		document.getElementById("coloniaf").value = r[0].coloniaf;
		document.getElementById("ciudadf").value = r[0].ciudadf;
		document.getElementById("estadof").value = r[0].estadof;
		//datosFiscal(r[0].id_cliente,"clientes")
		//asigna el id de cotización
		input.removeClass("ui-autocomplete-loading");
	  }
	});
	realizaProceso($('#clave').val());return false;
}
</script>