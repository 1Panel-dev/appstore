<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: login.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'logging';
$_GET['action'] = !empty($_GET['action']) ? $_GET['action'] : 'login';
include_once 'member.php';

class mobile_api {

	function common() {
		if(!empty($_GET['mlogout'])) {
			if($_GET['hash'] == formhash()) {
				clearcookies();
			}
			mobile_core::result(array());
		}
	}

	function output() {
		global $_G;
		parse_str($_G['messageparam'][1], $p);
		$variable = array('auth' => $p['auth']);
		if($_G['uid']) {
			require_once DISCUZ_ROOT.'./source/plugin/wechat/wsq.class.php';
			if(method_exists('wsq', 'userloginUrl')) {
				$_source = isset($_GET['_source']) ? $_GET['_source'] : '';
				if(!$_source && !empty($_GET['openid']) && !empty($_GET['openidsign'])) {
					$variable['loginUrl'] = wsq::userloginUrl($_G['uid'], $_GET['openid'], $_GET['openidsign']);
					if(!C::t('#wechat#common_member_wechatmp')->fetch($_G['uid'])) {
						C::t('#wechat#common_member_wechatmp')->insert(array('uid' => $_G['uid'], 'openid' => $_GET['openid'], 'status' => 1), false, true);
					}
				} else {
					$variable['loginUrl'] = wsq::userloginUrl2($_G['uid']);
				}
			}
		}
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>