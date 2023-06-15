<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: admin.php 1139 2012-05-08 09:02:11Z liulanbo $
*/

error_reporting(0);

if(function_exists('set_magic_quotes_runtime')) {
	set_magic_quotes_runtime(0);
}

$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

define('IN_UC', TRUE);
define('UC_ROOT', __DIR__.'/');
define('UC_API', (is_https() ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
define('UC_DATADIR', UC_ROOT.'data/');
define('UC_DATAURL', UC_API.'/data');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);

$_GET		= daddslashes($_GET, 1, TRUE);
$_POST		= daddslashes($_POST, 1, TRUE);
$_COOKIE	= daddslashes($_COOKIE, 1, TRUE);
$_SERVER	= daddslashes($_SERVER);
$_FILES		= daddslashes($_FILES);
$_REQUEST	= daddslashes($_REQUEST, 1, TRUE);

require UC_ROOT.'./release/release.php';
require UC_DATADIR.'config.inc.php';
require UC_ROOT.'model/base.php';
require UC_ROOT.'model/admin.php';

$m = getgpc('m');
$a = getgpc('a');
$m = empty($m) ? 'frame' : $m;
$a = empty($a) ? 'index' : $a;

define('RELEASE_ROOT', '');

header('Content-Type: text/html; charset='.CHARSET);

if(in_array($m, array('admin', 'app', 'badword', 'cache', 'db', 'domain', 'frame', 'log', 'note', 'feed', 'mail', 'setting', 'user', 'credit', 'seccode', 'tool', 'plugin', 'pm'))) {
	include UC_ROOT."control/admin/$m.php";
	$control = new control();
	$method = 'on'.$a;
	if(method_exists($control, $method) && $a[0] != '_') {
		$control->$method();
	} elseif(method_exists($control, '_call')) {
		$control->_call('on'.$a, '');
	} else {
		exit('Action not found!');
	}
} else {
	exit('Module not found!');
}

$mtime = explode(' ', microtime());
$endtime = $mtime[1] + $mtime[0];

function daddslashes($string, $force = 0, $strip = FALSE) {
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force, $strip);
			}
		} else {
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $string;
}

function getgpc($k, $t='R') {
	switch($t) {
		case 'P': $var = &$_POST; break;
		case 'G': $var = &$_GET; break;
		case 'C': $var = &$_COOKIE; break;
		case 'R': $var = &$_REQUEST; break;
	}
	return isset($var[$k]) ? (is_array($var[$k]) ? $var[$k] : trim($var[$k])) : NULL;
}

function fsocketopen($hostname, $port = 80, &$errno, &$errstr, $timeout = 15) {
	$fp = '';
	if(function_exists('fsockopen')) {
		$fp = @fsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('pfsockopen')) {
		$fp = @pfsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('stream_socket_client')) {
		$fp = @stream_socket_client($hostname.':'.$port, $errno, $errstr, $timeout);
	}
	return $fp;
}

function dhtmlspecialchars($string, $flags = null) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val, $flags);
		}
	} else {
		if($flags === null) {
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if(strpos($string, '&amp;#') !== false) {
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
			}
		} else {
			if(PHP_VERSION < '5.4.0') {
				$string = htmlspecialchars($string, $flags);
			} else {
				if(strtolower(CHARSET) == 'utf-8') {
					$charset = 'UTF-8';
				} else {
					$charset = 'ISO-8859-1';
				}
				$string = htmlspecialchars($string, $flags, $charset);
			}
		}
	}
	return $string;
}

function is_https() {
	if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
		return true;
	}
	if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
		return true;
	}
	if(isset($_SERVER['HTTP_X_CLIENT_SCHEME']) && strtolower($_SERVER['HTTP_X_CLIENT_SCHEME']) == 'https') {
		return true;
	}
	if(isset($_SERVER['HTTP_FROM_HTTPS']) && strtolower($_SERVER['HTTP_FROM_HTTPS']) != 'off') {
		return true;
	}
	if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
		return true;
	}
	return false;
}

?>