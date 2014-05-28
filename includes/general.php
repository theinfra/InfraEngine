<?php

define('APP_SEVERITY_SUCCESS', 0);
define('APP_SEVERITY_ERROR', 1);
define('APP_SEVERITY_WARNING', 2);
define('APP_SEVERITY_NOTICE', 3);
define('APP_SEVERITY_DEBUG', 4);

function print_array($array, $nopre = false){
	if(!is_array($array)){
		$array = array($array);
	}

	if(!$nopre) print "<pre>";
	print_r($array);
	if(!$nopre) print "</pre>";
}

function getClass($classname){
	$classfile = APP_BASE_PATH.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'class.'.$classname.'.php';
	if($classname != '' and file_exists($classfile)){
		include_once $classfile;
		$class = 'APPCLASS_'.strtoupper($classname);
		$class = new $class();
		return $class;
	}
}

function redirectRequest(){
	$request = parseGetVars();

	if(isset($request[0]) && trim($request[0]) != ''){
		$handler = strtolower($request[0]);
	}
	else {
		$handler = 'index';
	}

	if(isset($request[1]) && trim($request[1]) != ''){
		$operation = strtolower($request[1]);
	}
	else {
		$operation = 'view';
	}

	$class = getClass($handler);
	if(method_exists($class, $operation)){
		$class->$operation();
	}
	else if (method_exists($class, 'view')){
		$class->View();
	}
	else {
		print "no hay el metodo ".$operation." de la clase ".$handler." ni tampoco su metodo view por omisión";
		exit;
	}
}

function parseGetVars(){
	$request = array();

	$uri = '';
	
	if(isset($_SERVER['REDIRECT_URL']) && trim($_SERVER['REDIRECT_URL']) != '' && trim($GLOBALS['AppSubDir']) != '') {
		$redirect_url = preg_replace('/^' . preg_quote($GLOBALS['AppSubDir'], '/') . '/', '', $_SERVER['REDIRECT_URL']);
	}
	else {
		$redirect_url = '';
	}
	
	if(isset($redirect_url) && trim($redirect_url) != '') {
		$uri = $redirect_url;
		$uri = preg_replace("#^/#i", "", $uri);
		$uri = preg_replace("#/$#i", "", $uri);
	}

	if(isset($uri) && trim($uri) != ''){
		$request = array_merge($request, explode('/', $uri));
	}

	if(isset($_GET) && !empty($_GET)){
		$request = array_merge($request, $_GET);
	}

	if(isset($_SERVER['REDIRECT_QUERY_STRING']) && trim($_SERVER['REDIRECT_QUERY_STRING']) != ''){
		$gets = explode('&', $_SERVER['REDIRECT_QUERY_STRING']);
		foreach($gets as $get_var){
			$get_var = explode('=', $get_var);
			if(!isset($request[$get_var[0]])){
				$request[$get_var[0]] = $get_var[1];
			}
		}
	}
	
	foreach($request as $k => $v){
		$request[$k] = preg_replace("#[-]#", "", $v);
	}

	return $request;
}

function GetConfig($name){
	if(!isset($GLOBALS['APP_CONFIG'][$name])) {
		return '';
	}

	return $GLOBALS['APP_CONFIG'][$name];
}

function GetLang($name){
	if(!isset($GLOBALS['APP_LANG'][$name])) {
		return '';
	}

	return $GLOBALS['APP_LANG'][$name];
}

function flashMessage($msg, $severity = APP_SEVERITY_ERROR){
	if(!isset($GLOBALS['APP_MESSAGES'])){
		$GLOBALS['APP_MESSAGES'] = array();
	}
	
	$GLOBALS['APP_MESSAGES'][] = array(
		'msg' => $msg,
		'sev' => $severity,
	);
}

function emptyFlashMessages(){
	$GLOBALS['APP_MESSAGES'] = array();
}

function checkFlashMessages($severity = false){
	if(empty($GLOBALS['APP_MESSAGES'])) {
		return false;
	}
	else {
		if(in_array($severity, array(APP_SEVERITY_DEBUG, APP_SEVERITY_ERROR, APP_SEVERITY_NOTICE, APP_SEVERITY_WARNING))){
			foreach ($GLOBALS['APP_MESSAGES'] as $msg){
				if($msg['sev'] == $severity){
					return true;
				}
			}
		}
		else{
			return true;
		}
	}
	return false;
}

function getUserData(){
	static $user;
	if(is_array($user)){
		return $user;
	}
	
	if(!isset($_SESSION)){
		return false;
	}
	
	if(!isset($_SESSION['userid'])){
		return false;
	}
	
	$user = $GLOBALS['APP_CLASS_DB']->FetchRow('SELECT * FROM users WHERE UsuarioId = "'.$_SESSION['userid'].'"');
	return $user;
	
}

function getUser($userid = false){
	if(!$userid){
		return false;
	}

	$user = $GLOBALS['APP_CLASS_DB']->FetchRow('SELECT * FROM users WHERE UsuarioId = "'.$userid.'"');
	return $user;

}

function cryptPassword($user, $password, $salt){
	return hash('sha512', $user.$password.$salt);
}

function formatPrice($price, $currencyid = ''){
	static $currencies;
	static $defaultcurrencyid = '';

	if($defaultcurrencyid == ''){
		$defaultcurrencyid = $GLOBALS['APP_CLASS_DB']->FetchOne('SELECT MonedaID FROM currencies WHERE MonedaOmision = "1"', 'MonedaID');
	}
	
	if($currencyid == ''){
		$currencyid = $defaultcurrencyid;
	}
	
	if(!isset($currencies[$currencyid])){
		$currencies[$currencyid] = $GLOBALS['APP_CLASS_DB']->FetchRow('SELECT * FROM currencies WHERE MonedaID = "'.$currencyid.'"');
	}
	
	$currency = $currencies[$currencyid];
	
	$return = number_format($price, $currency['MonedaNumeroDecimales'], $currency['MonedaSimboloDecimal'], $currency['MonedaSimboloSeparadorMiles']);
	
	if($currency['MonedaPosicionSimbolo'] == 'DER'){
		$return = $return . $currency['MonedaSimbolo'];
	}
	else {
		$return = $currency['MonedaSimbolo'] . $return;
	}
	
	return $return;
}

function getStatusColor($statusid){
	static $statuses;
	
	if(isset($statuses[$statusid])){
		return $statuses[$statusid];
	}
	
	$statuses[$statusid] = $GLOBALS['APP_CLASS_DB']->FetchOne('SELECT EstatusColor FROM product_status WHERE EstatusID = "'.$statusid.'"', 'EstatusColor');
	
	return $statuses[$statusid];
}

function app_json_encode($a=false)
{
	if(is_null($a)) {
		return 'null';
	}
	else if($a === false) {
		return 'false';
	}
	else if($a === true) {
		return 'true';
	}
	else if(is_scalar($a)) {
		if(is_float($a)) {
			// Always use "." for floats.
			return floatval(str_replace(",", ".", strval($a)));
		}

		if(is_string($a)) {
			static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"', "\0"), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"', '\u0000'));
			return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
		}
		else {
			return $a;
		}
	}
	$isList = true;
	for($i = 0, reset($a); $i < count($a); $i++, next($a)) {
		if(key($a) !== $i) {
			$isList = false;
			break;
		}
	}
	$result = array();
	if($isList) {
		foreach($a as $v) {
			$result[] = app_json_encode($v);
		}
		return '[' . implode(',', $result) . ']';
	}
	else {
		foreach($a as $k => $v) {
			$result[] = app_json_encode((string)$k).':'.app_json_encode($v);
		}
		return '{' . implode(',', $result) . '}';
	}
}