<?php 
date_default_timezone_set('America/Mexico_City');

define('APP_BASE_PATH', dirname(realpath(dirname(__FILE__).'/../index.php')));

include_once ''.APP_BASE_PATH.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
include_once ''.APP_BASE_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'general.php';

if(!function_exists('mysql_connect')){
	print "No hay soporte para MySQL.";
	exit;
}

include_once ''.APP_BASE_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'db.php';
include_once ''.APP_BASE_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'view.php';
include_once ''.APP_BASE_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'session.php';
include_once ''.APP_BASE_PATH.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'base.php';

$GLOBALS['APP_CLASS_VIEW'] = new CLASS_VIEW();

if(isset($_SERVER['HTTP_HOST']) && isset($_SERVER['SCRIPT_NAME'])){
	$GLOBALS['AppPath'] = str_replace('/index.php', '', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);
	$GLOBALS['AppSubDir'] = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
}

$GLOBALS['APP_CLASS_DB'] = new Db(GetConfig('db_host'), GetConfig('db_user'), GetConfig('db_pwd'), GetConfig('db_name'));

if(!$GLOBALS['APP_CLASS_DB']->connection){
	print "Error al conectarse a la base de datos. ".$GLOBALS['APP_CLASS_DB']->GetError();
	exit;
}

$file = APP_BASE_PATH.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.GetConfig('language').DIRECTORY_SEPARATOR.'common.ini';
$vars = parse_ini_file($file);

if (isset($GLOBALS['APP_LANG'])) {
	$GLOBALS['APP_LANG'] = array_merge($GLOBALS['ISC_LANG'], $vars);
} else {
	$GLOBALS['APP_LANG'] = $vars;
}

if(PHP_SAPI === 'cli'){
	define('APP_EOL', PHP_EOL);
}
else {
	define('APP_EOL', "<br />".PHP_EOL);
}