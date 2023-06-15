<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: profile.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'space';
$_GET['do'] = 'profile';
include_once 'home.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;
		$data = $GLOBALS['space'];
		unset($data['password'], $data['email'], $data['regip'], $data['lastip'], $data['regip_loc'], $data['lastip_loc'], $data['qq'], $data['alipay'], $data['icq'], $data['yahoo'], $data['msn'], $data['taobao'], $data['mobile'], $data['telephone'], $data['idcard'], $data['idcardtype'], $data['address'], $data['realname'], $data['zipcode']);
		$variable = array(
			'space' => $data,
			'extcredits' => $_G['setting']['extcredits'],
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>