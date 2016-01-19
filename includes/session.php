<?php

function getUserData(){
	static $user;
	if(is_array($user)){
		return $user;
	}

	$app_token = '';
	if (isset($_COOKIE['APP_TOKEN'])) {
		$app_token = $_COOKIE['APP_TOKEN'];
	}
	elseif (isset($_SESSION['APP_TOKEN'])) {
		$app_token = $_SESSION['APP_TOKEN'];
		$_COOKIE['APP_TOKEN'] = $app_token;
	}

	if ($app_token) {
		$user = getCustomerIdByToken($app_token);
		return $user;
	}
	else {
		return false;
	}
}

function getCustomerIdByToken($token){
	if(!$token || trim($token) == ""){
		return false;
	}
	
	$user_model = GetModel("user");
	$user = $user_model->get(array("token" => $token));
	return $user;
}

function getUser($userid = false){
	if(!$userid){
		return false;
	}

	$user_model = getModel("user");
	$user = $user_model->get(array("userid" => $userid));
	return $user;

}

function appGenerateUserToken()
{
	$rnd = rand(1, 99999);
	$uid = uniqid($rnd, true);
	return $uid;
}

function isIPAddress($ipaddr)
{
	if (preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#", $ipaddr, $digit)) {
		if (($digit[1] <= 255) && ($digit[2] <= 255) && ($digit[3] <= 255) && ($digit[4] <= 255)) {
			return true;
		}
	}

	return false;
}

function AppSetCookie($name, $value = "", $expires = 0, $httpOnly=false)
{
	/*
	 if (!isset($GLOBALS['CookiePath'])) {
	$GLOBALS['CookiePath'] = $GLOBALS["AppPath"];
	}
	*/
	
	if(!isset($GLOBALS['CookieDomain'])) {
		$host = "";
		$url = parse_url(GetConfig('AppPath'), PHP_URL_HOST);
		if(is_array($url)) {
			print_array($url);
			// Strip off the www. at the start
			$host = preg_replace("#^www\.#i", "", $url['host']);
		}

		if($host) {
			$GLOBALS['CookieDomain'] = $host;

			// Prefix with a period so that we're covering both the www and no www
			if (strpos($GLOBALS['CookieDomain'], '.') !== false && !isIPAddress($GLOBALS['CookieDomain'])) {
				$GLOBALS['CookieDomain'] = ".".$GLOBALS['CookieDomain'];
			} else {
				unset($GLOBALS['CookieDomain']);
			}
		}
	}

	// Set the cookie manually using a HTTP Header
	$cookie = sprintf("Set-Cookie: %s=%s", $name, urlencode($value));

	// Adding an expiration date
	if ($expires !== 0) {
		$cookie .= sprintf("; expires=%s", @gmdate('D, d-M-Y H:i:s \G\M\T', $expires));
	}

	$path = parse_url($GLOBALS['AppPath'], PHP_URL_PATH)."/";
	$cookie .= sprintf("; path=%s", trim($path));
	/*
	if (isset($GLOBALS['CookiePath'])) {
		if (substr($GLOBALS['CookiePath'], -1) != "/") {
			$GLOBALS['CookiePath'] .= "/";
		}

		$cookie .= sprintf("; path=%s", trim($GLOBALS['CookiePath']));
	}
	*/

	if (isset($GLOBALS['CookieDomain'])) {
		$cookie .= sprintf("; domain=%s", $GLOBALS['CookieDomain']);
	}

	if ($httpOnly == true) {
		$cookie .= "; HttpOnly";
	}

	header(trim($cookie), false);
}

/**
 * Unset a set cookie.
 *
 * @param string The name of the cookie to unset.
 */
function AppUnsetCookie($name)
{
	AppSetCookie($name, "", 1);
}

function UserHasAccess($url){

	if(trim($url) == ""){
		return false;
	}
	
	if(GetConfig('nodb')){
		return true;
	}
	
	$user = getUserData();
	
	if(!$user){
		$user = array(
			"usergroup" => 0,
		);
	}
	
	$split = explode("/", $url);
	
	if(!isset($split[0])){
		$split = array(
			"index",
			"view",
		);
	}
	
	if(!isset($split[1])){
		$split[1] = "view";
	}

	$controller = getController($split[0]);

	if($split[0] == "index" && !$controller){
		$controllerfile = APP_BASE_PATH.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'controllerbase.index.php';
		include_once $controllerfile;
		$controller = new APPCONTROLLERBASE_INDEX();
	}

	if(!is_object($controller)){
		AddLog(sprintf(GetLang("ErrorAccessControlNoController"), $split[0]));
		return false;
	}
	
	if(/*!method_exists($controller, $split[1]) || */!isset($controller->menu) || !isset($controller->menu[$split[1]])){
		AddLog(sprintf(GetLang("ErrorAccessControlNoMenu"), $split[1], $split[0]));
		return false;
	}

	if($user["usergroup"] >= $controller->menu[$split[1]]){
		return true;
	}
	else {
		return false;
	}
}