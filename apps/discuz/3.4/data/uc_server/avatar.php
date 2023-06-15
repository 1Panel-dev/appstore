<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: avatar.php 1144 2013-01-31 06:47:43Z zhangjie $
*/


error_reporting(0);

_get_script_url();
define('UC_API', (is_https() ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));

$uid = isset($_GET['uid']) ? $_GET['uid'] : 0;
$size = isset($_GET['size']) ? $_GET['size'] : '';
$random = isset($_GET['random']) ? $_GET['random'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$check = isset($_GET['check_file_exists']) ? $_GET['check_file_exists'] : '';

$ts = isset($_GET['ts']) ? $_GET['ts'] : '';

$avatar = './data/avatar/'.get_avatar($uid, $size, $type);
$avatar_file = dirname(__FILE__).'/'.$avatar;
if(file_exists($avatar_file)) {
	if($check) {
		echo 1;
		exit;
	}
	$avatar_url = $avatar;
} else {
	if($check) {
		echo 0;
		exit;
	}
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$avatar_url = 'images/noavatar_'.$size.'.gif';
	$avatar_file = dirname(__FILE__).'/'.$avatar_url;
}

if(empty($random)) {
	if (empty($ts)) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Last-Modified:".date('r'));
		header("Expires: ".date('r', time() + 86400));
	} else {
		$avatar_url .= '?ts='.filemtime($avatar_file);
	}
} else {
	$avatar_url .= '?random='.rand(1000, 9999);
}

header('Location: '.UC_API.'/'.$avatar_url);
exit;

function get_avatar($uid, $size = 'middle', $type = '') {
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';
	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
}

function _get_script_url() {
	$scriptName = basename($_SERVER['SCRIPT_FILENAME']);
	if(basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
		$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
	} else if(basename($_SERVER['PHP_SELF']) === $scriptName) {
		$_SERVER['PHP_SELF'] = $_SERVER['PHP_SELF'];
	} else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
		$_SERVER['PHP_SELF'] = $_SERVER['ORIG_SCRIPT_NAME'];
	} else if(($pos = strpos($_SERVER['PHP_SELF'],'/'.$scriptName)) !== false) {
		$_SERVER['PHP_SELF'] = substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
	} else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT']) === 0) {
		$_SERVER['PHP_SELF'] = str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
		$_SERVER['PHP_SELF'][0] != '/' && $_SERVER['PHP_SELF'] = '/'.$_SERVER['PHP_SELF'];
	} else {
		return false;
	}
	return $_SERVER['PHP_SELF'];
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