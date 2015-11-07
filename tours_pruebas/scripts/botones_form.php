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
		"metodo"=>"referenciado",
		"tabla"=>"f_articulos.php",
		"nombre"=>"Articulos",
	),
	array(
		"accion"=>"boton_abrir_form_dos",
		"metodo"=>"referenciado",
		"tabla"=>"f_paquetes.php",
		"nombre"=>"Actividades",
	),
	
	array(
		"accion"=>"boton_abrir_form_dos",
		"metodo"=>"individual",
		"tabla"=>"f_salones.php",
		"nombre"=>"Hoteles",
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