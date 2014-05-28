<?php

function getUserLogged(){
	if(isset($_SESSION['userid']) && trim($_SESSION['userid']) != ''){
		return true;
	}
	else {
		return false;
	}
}

function AppSetCookie($name, $value = '', $expires = 0, $httpOnly=false){
	if(isset($_SERVER['HTTP_HOST'])){
		$host = $_SERVER['HTTP_HOST'];
	}else {
		$host = '';
	}
	
	$host = preg_replace("#^www\.#i", "", $host);
	$host = preg_replace("#:[0-9]*$#i", "", $host);
	
	$cookie = sprintf("Set-Cookie: %s=%s", $name, urlencode($value));
	
	// Adding an expiration date
	if ($expires !== 0) {
		$cookie .= sprintf("; expires=%s", @gmdate('D, d-M-Y H:i:s \G\M\T', $expires));
	}

	$cookie .= sprintf("; path=%s", trim($host));
	
	$cookie .= sprintf("; domain=%s", $host);
	
	if ($httpOnly == true) {
		$cookie .= "; HttpOnly";
	}

	setcookie(md5($name), md5($value), $expires, '/'/*, $host*/);
	
}

function AppGetCookie($name){
	if(isset($_COOKIE[md5($name)])){
		return $_COOKIE[md5($name)];
	}
	
	return '';
}