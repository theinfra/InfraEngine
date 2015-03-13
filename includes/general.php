<?php

define('APP_SEVERITY_SUCCESS', 0);
define('APP_SEVERITY_ERROR', 1);
define('APP_SEVERITY_WARNING', 2);
define('APP_SEVERITY_NOTICE', 3);
define('APP_SEVERITY_DEBUG', 4);

function print_array($array, $nopre = false, $return = false){
	if(!is_array($array)){
		$array = array($array);
	}
	
	if(!$return) {
		if(!$nopre) print "<pre>";
		print_r($array);
		if(!$nopre) print "</pre>";
	}
	else {
		$return = "";
		if(!$nopre) $return .= "<pre>";
		$return .= print_r($array, true);
		if(!$nopre) $return .= "</pre>";
		return $return;
	}
}

function getController($controller){
	if(trim($controller) == ""){
		$controller = "index";
	}
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

function getLib($libname){
	$libfile = APP_BASE_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'lib.'.$libname.'.php';
	if($libname != '' and file_exists($libfile)){
		include_once $libfile;
		$lib = 'APPLIB_'.strtoupper($libname);
		$lib = new $lib();
		return $lib;
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
	
	if(empty($GLOBALS["AppRequestVars"])){
		$GLOBALS['AppRequestVars'][0] = "index";
		$GLOBALS['AppRequestVars'][1] = "view";
	}
	
	if(isset($GLOBALS['AppRequestVars']['adminaction']) && trim($GLOBALS['AppRequestVars']['adminaction']) != ''){
		redirectAdminAction($GLOBALS['AppRequestVars']);
		exit;
	}

	/*
	if(isset($GLOBALS['AppRequestVars'][0]) && trim($GLOBALS['AppRequestVars'][0]) != ''){
		$handler = strtolower($GLOBALS['AppRequestVars'][0]);
	}
	else {
		$handler = 'index';
	}
	*/

	if(!isset($GLOBALS['AppRequestVars'][1]) || trim($GLOBALS['AppRequestVars'][1]) == ''){
		$GLOBALS['AppRequestVars'][1] = 'view';
	}

	$GLOBALS["ViewStylesheet"] = "";
	$GLOBALS["ViewScripts"] = "";
	$controller = getController($GLOBALS['AppRequestVars'][0]);
	
	if(isset($controller->menu) && isset($controller->menu[$GLOBALS["AppRequestVars"][1]])){
		if(!UserHasAccess($GLOBALS["AppRequestVars"][0]."/".$GLOBALS["AppRequestVars"][1])){
			flashMessage(GetLang("NotPermitted"), APP_SEVERITY_ERROR);
			header("Location: ".$GLOBALS["AppPath"]."/");
			exit;
		}
	}
	
	if(method_exists($controller, $GLOBALS['AppRequestVars'][1])){
		$action = $GLOBALS['AppRequestVars'][1];
		$controller->$action();
	}
	/*
	else if (method_exists($controller, 'view')){
		$controller->view();
	}
	else {
		print "no hay el metodo ".$GLOBALS['AppRequestVars'][1]." de la clase ".$GLOBALS['AppRequestVars'][0]." ni tampoco su metodo view por omisión";
		exit;
	}
	*/
	
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

	if(file_exists(APP_BASE_PATH.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$viewname.".css")){
		$GLOBALS["ViewStylesheet"] .= "<link rel=\"stylesheet\" href=\"".$GLOBALS["AppPath"].'/views/'.$viewname.".css\" />";
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
	if(!isset($_SESSION['APP_MESSAGES'])){
		$_SESSION['APP_MESSAGES'] = array();
	}
	
	$_SESSION['APP_MESSAGES'][] = array(
		'msg' => $msg,
		'sev' => $severity,
	);
}

function emptyFlashMessages(){
	$_SESSION['APP_MESSAGES'] = array();
}

function checkFlashMessages($severity = false){
	if(empty($_SESSION['APP_MESSAGES'])) {
		return false;
	}
	else {
		if(in_array($severity, array(APP_SEVERITY_DEBUG, APP_SEVERITY_ERROR, APP_SEVERITY_NOTICE, APP_SEVERITY_WARNING))){
			foreach ($_SESSION['APP_MESSAGES'] as $msg){
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

function formatPrice($price, $currencyid = ''){
	$currencies = GetConfig("Currencies");
	
	if(!is_array($currencies) || empty($currencies)){
		return $price;
	}
	
	if(!isset($currencies[$currencyid])){
		reset($currencies);
		$currencyid = key($currencies);
	}
	
	$currency = $currencies[$currencyid];
	
	$return = number_format($price, $currency['NumDecimals'], $currency['SymbolDecimals'], $currency['SymbolThou']);
	
	$return = $return . $currency['SymbolPost'];
	$return = $currency['SymbolPre'] . $return;
	
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
	//array_shift($trace);
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

function HandlePHPErrors($errno, $errstr, $errfile, $errline)
{
	// Error reporting turned off (either globally or by @ before erroring statement)
	if(error_reporting() == 0) {
		return;
	}

	$msg = "$errstr in $errfile at $errline<br/>\n";
	//$msg .= GetLogTrace(false,true);

	// This switch uses case fallthrough's intentionally
	switch ($errno) {
		case E_USER_ERROR:
		case E_ERROR:
		case E_PARSE:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
			AddLog($msg, APP_SEVERITY_ERROR, 'php');
			exit(1);
			break;

		case E_USER_WARNING:
		case E_WARNING:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
			AddLog($msg, APP_SEVERITY_WARNING, 'php');
			break;

		case E_USER_NOTICE:
		case E_NOTICE:
			AddLog($msg, APP_SEVERITY_NOTICE, 'php');
			break;

		case E_STRICT:
			//$this->LogSystemNotice('php', isc_substr($errstr, 0, 250), $msg);
			break;

		default:
			AddLog($msg, APP_SEVERITY_NOTICE, 'php');
			break;
	}

	// If we're stopping the default PHP error handler then we return true
	if(GetConfig('HidePHPErrors') == 1) {
		return true;
	}
	// Otherwise allow the PHP error handler to run after ours
	else {
		return false;
	}
}

function AddLog($logmsg = "", $logseverity = APP_SEVERITY_ERROR, $logmodule = "php"){
	if(is_array($logmsg)){
		$logmsg = print_array($logmsg, false, true);
	}
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

function app_substr_count($haystack, $needle)
{
	if(function_exists("mb_substr_count")) {
		return mb_substr_count($haystack, $needle);
	}
	else {
		return substr_count($haystack, $needle);
	}
}

function app_strpos($haystack, $needle, $offset=0)
{
	if(function_exists("mb_strpos")) {
		return mb_strpos($haystack, $needle, $offset);
	}
	else {
		return strpos($haystack, $needle, $offset);
	}
}

function app_substr($str, $start, $length=0)
{
	if(function_exists("mb_substr")) {
		if($length == 0) {
			return mb_substr($str, $start);
		}
		else {
			return mb_substr($str, $start, $length);
		}
	}
	else {
		if($length == 0) {
			return substr($str, $start);
		}
		else {
			return substr($str, $start, $length);
		}
	}
}

function is_email_address($email)
{
	// If the email is empty it can't be valid
	if (empty($email)) {
		return false;
	}

	// If the email doesnt have exactle 1 @ it isnt valid
	if (app_substr_count($email, '@') != 1) {
		return false;
	}

	$matches = array();
	$local_matches = array();
	preg_match(':^([^@]+)@([a-zA-Z0-9\-][a-zA-Z0-9\-\.]{0,254})$:', $email, $matches);

	if (count($matches) != 3) {
		return false;
	}

	$local = $matches[1];
	$domain = $matches[2];

	// If the local part has a space but isnt inside quotes its invalid
	if (app_strpos($local, ' ') && (app_substr($local, 0, 1) != '"' || app_substr($local, -1, 1) != '"')) {
		return false;
	}

	// If there are not exactly 0 and 2 quotes
	if (app_substr_count($local, '"') != 0 && app_substr_count($local, '"') != 2) {
		return false;
	}

	// if the local part starts or ends with a dot (.)
	if (app_substr($local, 0, 1) == '.' || app_substr($local, -1, 1) == '.') {
		return false;
	}

	// If the local string doesnt start and end with quotes
	if ((app_strpos($local, '"') || app_strpos($local, ' ')) && (app_substr($local, 0, 1) != '"' || app_substr($local, -1, 1) != '"')) {
		return false;
	}

	preg_match(':^([\ \"\w\!\#\$\%\&\'\*\+\-\/\=\?\^\_\`\{\|\}\~\.]{1,64})$:', $local, $local_matches);

	// Check the domain has at least 1 dot in it
	if (app_strpos($domain, '.') === false) {
		return false;
	}

	if (!empty($local_matches) ) {
		return true;
	} else {
		return false;
	}
}

function appGeneratePasswordHash($password, $salt){
	return hash('sha512', $salt.'-'.$password);
}

function formatDateSpanish($time, $short = true, $dayofweek = false){
	if(is_null($time) || trim($time) == "" || !is_int($time)){
		return "N/A";
	}
	
	$days = array(
		'Lunes',
		'Martes',
		'Miercoles',
		'Jueves',
		'Viernes',
		'Sabado',
		'Domingo',
	);
	
	$shortdays = array(
			'Lun',
			'Mar',
			'Mie',
			'Jue',
			'Vie',
			'Sab',
			'Dom',
	);
	
	$months = array(
		1 => 'Enero',
		2 => 'Febrero',
		3 => 'Marzo',
		4 => 'Abril',
		5 => 'Mayo',
		6 => 'Junio',
		7 => 'Julio',
		8 => 'Agosto',
		9 => 'Septiembre',
		10 => 'Octubre',
		11 => 'Noviembre',
		12 => 'Diciembre',
	);
	
	$shortmonths = array(
			1 => 'Ene',
			2 => 'Feb',
			3 => 'Mar',
			4 => 'Abr',
			5 => 'May',
			6 => 'Jun',
			7 => 'Jul',
			8 => 'Ago',
			9 => 'Sep',
			10 => 'Oct',
			11 => 'Nov',
			12 => 'Dic',
	);
	
	if($short){
		if($dayofweek)
		{
			return $shortdays[date('w', $time)].' '.date('j', $time).'-'.$shortmonths[date('n')].'-'.date('Y', $time);
		} 
		else {
			return date('j', $time).'-'.$shortmonths[date('n', $time)].'-'.date('Y', $time);
		}
	}
	else {
		if($dayofweek)
		{
			return $days[date('w', $time)].' '.date('j', $time).' de '.$months[date('n', $time)].' '.date('Y', $time);
		}
		else {
			return date('j', $time).' de '.$months[date('n', $time)].' '.date('Y', $time);
		}
	}
}

function is_associative_array($array)
{
	if (!is_array($array) || empty($array)) {
		return false;
	}

	$keys = array_keys($array);
	$total = count($keys);
	$filtered = array_filter($keys, "app_is_int");

	if (count($filtered) == $total) {
		return false;
	}

	return true;
}

function overwritePostToGlobalVars($source = "post"){
	if($source == "post") $source = $_POST;

	if(!is_array($source) || empty($source)){
		return;
	}

	foreach($source as $key => $val){
		$GLOBALS[$key] = $val;
		$GLOBALS[$key.$val."selected"] = 'selected="selected"';
	}
}

function app_mkdir($pathname, $mode = "", $recursive = false)
{
	if(trim($mode) == ""){
		$mode = fileperms(APP_BASE_PATH);
	}
	
	if (is_string($mode)) {
		$mode = octdec($mode);
	}

	$old = umask(0);

	$result = @mkdir($pathname, $mode, $recursive);

	umask($old);

	return $result;
}

function app_chmod($file, $mode)
{
	if (DIRECTORY_SEPARATOR!=='/') {
		return true;
	}

	if (is_string($mode)) {
		$mode = octdec($mode);
	}

	$old_umask = umask();
	umask(0);
	$result = @chmod($file, $mode);
	umask($old_umask);
	return $result;
}



function appGetMonthSelectOptions($selected = null){
	if(!is_null($selected) && $selected == "current") $selected = date('m');
	
	$return = "";
	
	if(is_null($selected) || !in_array($selected, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 12))) {
		$return .= "<option value='' selected='selected'> -- ".GetLang("SelectMonth")." -- </option>";; 
	}
	
	for($i=1;$i<=12;$i++){
		$selectedText = ($selected == $i) ? "selected='selected'" : "";
		$return .= "<option value='".$i."' ".$selectedText.">".ucfirst(strftime('%B', mktime(0, 0, 0, $i, 10)))."</option>";
	}
	
	return $return;
}

function appGetYearSelectOptions($selected = null){
	if(!is_null($selected) && $selected == "current") $selected = date('Y');
	
	$return = "";
	
	$years = array(2014, 2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025); //Empiezo en 2014 porque en este año se implemento la app
	if(is_null($selected) || !in_array($selected, $years)) {
		$return .= "<option value='' selected='selected'> -- ".GetLang("SelectYear")." -- </option>";; 
	}
	else {
		$return .= "<option value=''> -- ".GetLang("SelectYear")." -- </option>";;
	}
	
	$return = "";
	foreach($years as $year){ 
		$selectedText = ($selected == $year) ? "selected='selected'" : "";
		$return .= "<option value='".$year."' ".$selectedText.">".$year."</option>";
	}
	
	return $return;
}

function formatTime($seconds){
	$units = array(
			"week"   => 7*24*3600,
			"day"    =>   24*3600,
			"hour"   =>      3600,
			"minute" =>        60,
			"second" =>         1,
	);

	// specifically handle zero
	if ( $seconds == 0 ) return "0 seconds";

	$s = "";

	foreach ( $units as $name => $divisor ) {
		if ( $quot = intval($seconds / $divisor) ) {
			$s .= "$quot $name";
			$s .= (abs($quot) > 1 ? "s" : "") . ", ";
			$seconds -= $quot * $divisor;
		}
	}

	return substr($s, 0, -2);
}

// Asegurarse de siempre enviar el scheme (http:// o https://) en caso de redireccionar a algo externo porque si no, parse_url no lo reconoce 
function RedirectHeader($location = "", $exit = true){
	$AppPath = $GLOBALS["AppPath"];
	$url = parse_url($location);
	
	if(!is_string($location) || trim($location) == "" || !$url){
		header("Location: ".$AppPath);
		exit;
	}
	
	if(isset($url["host"]) || trim($url["host"]) != ""){
		header("Location: ".$location);
	}
	else {
		header("Location: ".$AppPath."/".$location);
	}
	
	if($exit){
		exit;
	}
	
}