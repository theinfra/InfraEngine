<?php
$GLOBALS['APP_CONFIG']['AuthSalt'] = 'algovaaqui';

$GLOBALS['APP_CONFIG']['HidePHPErrors'] = false;

$GLOBALS['APP_CONFIG']['language'] = 'es';

$GLOBALS['APP_CONFIG']['SiteName'] = 'Sitio de Prueba';

$GLOBALS['APP_CONFIG']['TitleTemplate'] = '%%%%GLOBAL_SiteName%%%% - %s';

$GLOBALS['APP_CONFIG']['nodb'] = false;
$GLOBALS['APP_CONFIG']['db_host'] = 'localhost';
$GLOBALS['APP_CONFIG']['db_user'] = 'app_user';
$GLOBALS['APP_CONFIG']['db_pwd'] = 'apppwd';
$GLOBALS['APP_CONFIG']['db_name'] = 'app_db';

$GLOBALS['APP_CONFIG']['CharacterSet'] = 'UTF-8';

$GLOBALS['APP_CONFIG']["Currencies"] = array(
		"MXN" => array(
				"NumDecimals" => 2,
				"SymbolDecimals" => ".",
				"SymbolThou" => ",",
				"SymbolPre" => "$",
				"SymbolPost" => "",
		),
);

$GLOBALS['APP_CONFIG']["MainMenu"] = array(
		"Home" => "index",
		"LogInOut" => "user/login",
);

/*
 * INSERT INTO `user` (`firstname`, `lastname`, `mail`, `username`, `password`, `salt`, `phone`, `status`, `membershiptype`, `usergroup`) VALUES ('Admin', 'Admin', 'admin@localhost', 'admin', '5d38559629b3a4241e77d09699e4fe8f5580eebed5a18593f97e053c40f58183edda8a2de0dcec1e9fbeb7a65b6139a583d8599022f10640489632b088a749d1', '3a287ab2b87e0e99', '0', 2, 0, 3);
*/