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
		
		if(strtoupper(hash('sha512', date('j').date('n').date('Y').GetConfig('AuthSalt'))) != $request['auth']){
			throw new Exception("No esta autorizado para realizar la accion ".get_class($this)." indicada. Login intentado en ".date('j-n-Y H:i'));
			return;
		}
		else {
			return;
		}
	}
	
	protected function printLog(){
		if(is_array($GLOBALS['ADMINACTION_LOG'][get_class($this)]) && !empty($GLOBALS['ADMINACTION_LOG'][get_class($this)])){
			print "<ul>";
			foreach($GLOBALS['ADMINACTION_LOG'][get_class($this)] as $logentry){
				print "<li>".$logentry."</li>";
			}
			print "</ul>";
		}
	}
	
	public function addToLog($logentry){
		$actionname = get_class($this);
		if(!isset($GLOBALS['ADMINACTION_LOG'][$actionname]) || !is_array($GLOBALS['ADMINACTION_LOG'][$actionname])){
			$GLOBALS['ADMINACTION_LOG'][$actionname] = array();
		}
		
		$GLOBALS['ADMINACTION_LOG'][$actionname][] = $logentry;
	}
}