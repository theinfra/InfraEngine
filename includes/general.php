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

function getController($controller){
	$controllerfile = APP_BASE_PATH.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'controller.'.$controller.'.php';
	if($controller != '' and file_exists($controllerfile)){
		include_once $controllerfile;
		$controllername = 'APPCONTROLLER_'.strtoupper($controller);
		$controller = new $controllername();
		return $controller;
	}
}

function getAllModels(){
	$models = array();
	$dir = scandir(APP_BASE_PATH.DIRECTORY_SEPARATOR.'models');
	foreach($dir as $file) {
		$filepath = APP_BASE_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$file;
		if(in_array($file, array('.', '..', 'base.php')) || is_dir($filepath) || (strpos($file, '.') == '0') || !(strpos($file, 'model.') == '0')){
			continue;
		}
		else {
			$models[] = preg_replace('#model\.#', '', preg_replace('#\.php$#', '', $file));
		}
	}
	return $models;
}

function getModel($modelname){
	$modelfile = APP_BASE_PATH.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'model.'.$modelname.'.php';
	if($modelname != '' and file_exists($modelfile)){
		include_once $modelfile;
		$model = 'APPMODEL_'.strtoupper($modelname);
		$model = new $model();
		return $model;
	}
}

function getAdminAction($actionname){
	$actionfile = APP_BASE_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'adminaction'.DIRECTORY_SEPARATOR.'action.'.$actionname.'.php';
	if($actionname != '' and file_exists($actionfile)){
		include_once $actionfile;
		$action = 'APPACTION_'.strtoupper($actionname);
		try{
			$action = new $action();
		}
		catch(Exception $e){
			throw new Exception(GetLang('ExceptionWhileCreatingAdminAction') .' - '. $e->getMessage());
			return false;
		}
		return $action;
	}
}

function redirectAdminAction($request){
	try{
		$action = getAdminAction($request['adminaction']);
	}
	catch(Exception $e){
		print $e->getMessage();
		return false;
	}

	if(false){
		print "erkgjergk";
		return false;
	}
	
	if(!isset($request['subaction']) || trim($request['subaction']) == ''){
		$subaction = 'actiondefault';
	}
	else {
		$subaction = $request['subaction'];
	}
	
	if(method_exists($action, $subaction)){
		$action->$subaction();
	}
	else {
		print "No hay la subaccion ".$subaction." de la accion ".$request['adminaction'];
		exit;
	}
}

function redirectRequest(){
	$GLOBALS['AppRequestVars'] = parseGetVars();
	
	if(isset($GLOBALS['AppRequestVars']['adminaction']) && trim($GLOBALS['AppRequestVars']['adminaction']) != ''){
		redirectAdminAction($GLOBALS['AppRequestVars']);
		exit;
	}

	if(isset($GLOBALS['AppRequestVars'][0]) && trim($GLOBALS['AppRequestVars'][0]) != ''){
		$handler = strtolower($GLOBALS['AppRequestVars'][0]);
	}
	else {
		$handler = 'index';
	}

	if(!isset($GLOBALS['AppRequestVars'][1]) || trim($GLOBALS['AppRequestVars'][1]) == ''){
		$GLOBALS['AppRequestVars'][1] = 'view';
	}

	$controller = getController($GLOBALS['AppRequestVars'][0]);
	if(method_exists($controller, $GLOBALS['AppRequestVars'][1])){
		$action = $GLOBALS['AppRequestVars'][1];
		$controller->$action();
	}
	else if (method_exists($controller, 'view')){
		$controller->view();
	}
	else {
		print "no hay el metodo ".$GLOBALS['AppRequestVars'][1]." de la clase ".$GLOBALS['AppRequestVars'][0]." ni tampoco su metodo view por omisión";
		exit;
	}
	
	$viewname = '';
	if($GLOBALS['AppRequestVars'][1] == 'view'){
		if(file_exists(APP_BASE_PATH.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$GLOBALS['AppRequestVars'][0].'.view.tpl')){
			$viewname = $GLOBALS['AppRequestVars'][0].'.view';
		}
		else{
			$viewname = $GLOBALS['AppRequestVars'][0];
		}
	}
	else {
		$viewname = $GLOBALS['AppRequestVars'][0].'.'.$GLOBALS['AppRequestVars'][1];
	}
	
	if(!file_exists(APP_BASE_PATH.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$viewname.".tpl")){
		$viewname = 'default';
	}

	$GLOBALS['APP_CLASS_VIEW']->parseView($viewname);

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
		return 'ErrorNoLangName: "'.$name.'"';
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

function app_is_int($x)
{
	if (is_numeric($x)) {
		return (intval($x+0) == $x);
	}

	return false;
}

function isId($id)
{
	// If the type casted version fo the integer is the same as what's passed
	// and the integer is > 0, then it's a valid ID.
	if(app_is_int($id) && $id > 0) {
		return true;
	}
	else {
		return false;
	}
}

function app_strtolower($str)
{
	if(function_exists("mb_strtolower")) {
		return mb_strtolower($str);
	}
	else {
		return strtolower($str);
	}
}

function GetLogTrace($die=false, $return=true){
	$trace = debug_backtrace();
	$backtrace = "<table style=\"width: 100%; margin: 10px 0; border: 1px solid #aaa; border-collapse: collapse; border-bottom: 0;\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
	$backtrace .= "<thead><tr>\n";
	$backtrace .= "<th style=\"border-bottom: 1px solid #aaa; background: #ccc; padding: 4px; text-align: left; font-size: 11px;\">File</th>\n";
	$backtrace .= "<th style=\"border-bottom: 1px solid #aaa; background: #ccc; padding: 4px; text-align: left; font-size: 11px;\">Line</th>\n";
	$backtrace .= "<th style=\"border-bottom: 1px solid #aaa; background: #ccc; padding: 4px; text-align: left; font-size: 11px;\">Function</th>\n";
	$backtrace .= "</tr></thead>\n<tbody>\n";
	
	// Strip off last item (the call to this function)
	array_shift($trace);
	array_shift($trace);
	
	foreach ($trace as $call) {
		if (!isset($call['file'])) {
			$call['file'] = "[PHP]";
		}
		if (!isset($call['line'])) {
			$call['line'] = "&nbsp;";
		}
		if (isset($call['class'])) {
			$call['function'] = $call['class'].$call['type'].$call['function'];
		}
		if(function_exists('textmate_backtrace')) {
			$call['file'] .= " <a href=\"txmt://open?url=file://".$call['file']."&line=".$call['line']."\">[Open in TextMate]</a>";
		}
		$backtrace .= "<tr>\n";
		$backtrace .= "<td style=\"font-size: 11px; padding: 4px; border-bottom: 1px solid #ccc;\">{$call['file']}</td>\n";
		$backtrace .= "<td style=\"font-size: 11px; padding: 4px; border-bottom: 1px solid #ccc;\">{$call['line']}</td>\n";
		$backtrace .= "<td style=\"font-size: 11px; padding: 4px; border-bottom: 1px solid #ccc;\">{$call['function']}</td>\n";
		$backtrace .= "</tr>\n";
	}
	$backtrace .= "</tbody></table>\n";
	if (!$return) {
		echo $backtrace;
		if ($die === true) {
			die();
		}
	} else {
		return $backtrace;
	}
}

function AddLog($logmsg = "", $logseverity = APP_SEVERITY_ERROR, $logmodule = "php"){
	if(trim($logmsg) == ""){
		$logmsg = GetLang("ErrorMsgGeneric");
	}
	
	$log = array();
	$log['logsummary'] = substr($logmsg, 0, 50);
	$log['logmsg'] = $logmsg . GetLogTrace();
	$log['logseverity'] = $logseverity;
	$log['logmodule'] = $logmodule;
	$log['logdate'] = microtime(true);

	$logmodel = GetModel('log');
	$logid = $logmodel->add($log);
	
	if(!$logid){
		print $logmodel->getError();
		die();
	}
}

function AddLogError($logmsg = "", $logmodule = "php"){
	AddLog($logmsg, APP_SEVERITY_ERROR, $logmodule);
}

function AddLogWarning($logmsg = "", $logmodule = "php"){
	AddLog($logmsg, APP_SEVERITY_WARNING, $logmodule);
}

function AddLogNotice($logmsg = "", $logmodule = "php"){
	AddLog($logmsg, APP_SEVERITY_NOTICE, $logmodule);
}

function AddLogDebug($logmsg = "", $logmodule = "php"){
	AddLog($logmsg, APP_SEVERITY_DEBUG, $logmodule);
}

function AddLogSuccess($logmsg = "", $logmodule = "php"){
	AddLog($logmsg, APP_SEVERITY_SUCCESS, $logmodule);
}