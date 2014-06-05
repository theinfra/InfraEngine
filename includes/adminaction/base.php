<?php

class ADMINACTIONBASE {
	
	public function __construct(){
		if(trim(GetConfig('AuthSalt')) == ''){
			throw new Exception("No se ha configurado AuthSalt, por lo que no se pueden acceder a acciones de admin");
			return;
		}
		
		$request = parseGetVars();
		if(!isset($request['auth']) || trim($request['auth']) == ''){
			throw new Exception("No esta autorizado para realizar la accion ".get_class($this)." indicada. Login intentado en ".date('j-n-Y H:i'));
			return;
		}
		
		if(hash('sha512', date('j').date('n').date('Y').GetConfig('AuthSalt')) != $request['auth']){
			throw new Exception("No esta autorizado para realizar la accion ".get_class($this)." indicada. Login intentado en ".date('j-n-Y H:i'));
			return;
		}
		else {
			return;
		}
	}
}