// JavaScript Document
$(document).ready(function(e) {
    //busca cliente
	$( ".nombre" ).autocomplete({
      source: "scripts/busca_articulos.php",
      minLength: 1,
      select: function( event, ui ) {
		//asignacion individual alos campos
		$("#f_tipo_evento .id_tipo").val(ui.item.id_tipo);
		$(".modificar").show();
		$(".guardar_individual").hide();
	  }
    });
	$(".nombre").keyup(function(e) {
        if(e.keyCode==8){
			if($(this).val()==""){
				$(".modificar").hide();
				$(".guardar_individual").show();
			}
		}
    });
    $(".volver").click(function(e) {
		ingresar=true;
    	$("#formularios_modulo").hide("slide",{direction:'right'},rapidez,function(){
			$("#botones_modulo").fadeIn(rapidez);
		});
    });
	$(".guardar").click(function(e) {
		  nombre = document.getElementById("nombre").value;
		  precio = document.getElementById("precio").value;
		
		//procesamiento de datos
		$.ajax({
			url:'scripts/s_guardar_tEvento.php',
			cache:false,
			async:false,
			type:'POST',
			data:{
				'nombre':nombre,
				'precio':precio
			},
			success: function(r){
				if(r){
					alerta("info","<strong>Actividad</strong> guardada exitosamente");
					$(".volver").click();
				}else{
					alerta("error",r.info);
				}
			}
		});
    });
});
function requerido(){
	selector=".requerido";
	continuar=true;
	$.each($(selector).parent().find(".requerido"),function(i,v){
		if($(this).val()==""){
			$(this).addClass("falta_llenar");
			continuar=false;
		}
	});
	return continuar;
}