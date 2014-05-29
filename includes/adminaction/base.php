<?php

class ADMINACTIONBASE {
	
	public function __construct(){
		if(trim(GetConfig('AuthSalt')) == ''){
			print "No se ha configurado AuthSalt, por lo que no se pueden acceder a acciones de admin";
			return false;
		}
		
		$request = parseGetVars();
		if(!isset($request['auth']) || trim($request['auth']) == ''){
			print "No esta autorizado para realizar la accion ".get_class($this)." indicada. Login intentado en ".date('j-n-Y H:i');
			return false;
		}
		
		if(hash('sha512', date('j').date('n').date('Y').GetConfig('AuthSalt')) != $request['auth']){
			print "No esta autorizado para realizar la accion ".get_class($this)." indicada. Login intentado en ".date('j-n-Y H:i');
			return false;
		}
		else {
			return true;
		}
	}
}