<?php

//Para hacer el controlador del indice es necesario copiar este archivo, renombrarlo a controller.index.php, descomentar la siguiente linea y renombrar la clase a APPCONTROLLER_INDEX
//include_once APP_BASE_PATH.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'controllerbase.index.php';

class APPCONTROLLERBASE_INDEX extends APP_BASE {

	public $menu = array(
			"view" => 0,
	);
	
	function view(){
		print "base";
	}
}