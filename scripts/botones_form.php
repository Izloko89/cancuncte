<?php
//ordenado por array categoría
$botones["ventas"]=array(
//metodo, tabla, nombre botón 
	array(
		"accion"=>"boton_abrir_form_dos",
		"metodo"=>"pivote",
		"tabla"=>"f_clientes.php",
		"nombre"=>"Clientes",
	),
	array(
		"accion"=>"boton_abrir_form_dos",
		"metodo"=>"pivote",
		"tabla"=>"f_proveedores.php",
		"nombre"=>"Proveedores",
	),
	
	array(
		"accion"=>"boton_abrir_form_dos",
		"metodo"=>"individual",
		"tabla"=>"f_tipo_eventos.php",
		"nombre"=>"Actividades y<br />articulos",
	),
	array(
		"accion"=>"boton_abrir_form_dos",
		"metodo"=>"individual",
		"tabla"=>"f_bancos.php",
		"nombre"=>"Bancos",
	),
	array(
		"accion"=>"boton_abrir_form_dos",
		"metodo"=>"individual",
		"tabla"=>"f_usuarios.php",
		"nombre"=>"Usuarios",
	),
	array(
		"accion"=>"boton_abrir_form_dos",
		"metodo"=>"individual",
		"tabla"=>"f_empleado.php",
		"nombre"=>"Empleados",
	),
	array(
		"accion"=>"boton_abrir_form_dos",
		"metodo"=>"individual",
		"tabla"=>"f_gastos.php",
		"nombre"=>"Gastos",
	),
);

//botones para coordinador de ventas
$botones["coordinador"]=array(

);
	//agrega los botones de ventas a administrador
	foreach($botones["ventas"] as $ind => $val){
		array_push($botones["coordinador"],$val);
	}

//botones para administrador
$botones["administrador"]=array(
);
	//agrega los botones de ventas a administrador
	foreach($botones["ventas"] as $ind => $val){
		array_push($botones["administrador"],$val);
	}

//botones para almacenista
$botones["almacenista"]=array(

);
	//agrega los botones de ventas a administrador
	foreach($botones["ventas"] as $ind => $val){
		array_push($botones["almacenista"],$val);
	}
?>