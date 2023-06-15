<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect.php 34240 2013-11-21 08:32:04Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'connect';
include_once 'member.php';

class mobile_api {

	function common() {
		global $_G, $seccodecheck, $secqaacheck, $connect_guest;
		if($_G['uid'] && $_G['member']['conisbind']) {
			dheader('location: '.$_G['siteurl'].'index.php');
		}
		$connect_guest = array();
		if($_G['connectguest'] && (submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck) || submitcheck('loginsubmit', 1, $seccodestatus))) {
			if(!$_GET['auth_hash']) {
				$_GET['auth_hash'] = $_G['cookie']['con_auth_hash'];
			}
			$conopenid = authcode($_GET['auth_hash']);
			$connect_guest = C::t('#qqconnect#common_connect_guest')->fetch($conopenid);
			if(!$connect_guest) {
				dsetcookie('con_auth_hash');
				showmessage('qqconnect:connect_login_first');
			}
		}
	}

	function output() {
		if(!empty($_POST)) {
			mobile_core::result(mobile_core::variable());
		} else {
			global $_G;
			$bbrulehash = $_G['setting']['bbrules'] ? substr(md5(FORMHASH), 0, 8) : '';
			$isconnect = $_G['qc']['connect_app_id'] && $_G['qc']['connect_openid'];
			include template('mobile:register');
			exit;
		}
	}

}

?>