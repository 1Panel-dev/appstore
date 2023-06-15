<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_profilevalidate.php 6790 2010-03-25 12:30:53Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$profilevalidate = array(
	'telephone' => '/^((\\(?\\d{3,4}\\)?)|(\\d{3,4}-)?)\\d{7,8}$/',
	'mobile' => '/^(\+)?(86)?0?1\\d{10}$/',
	'zipcode' => '/^\\d{5,6}$/',
	'revenue' => '/^\\d+$/',
	'height' => '/^\\d{1,3}$/',
	'weight' => '/^\\d{1,3}$/',
	'qq' => '/^[1-9]*[1-9][0-9]*$/'
);

?>