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
		$data['groupiconid'] = mobile_core::usergroupIconId($data['groupid']);
		if($data['group']['type'] == 'member' && $data['group']['groupcreditslower'] != 999999999) {
			$data['upgradecredit'] = $data['group']['creditslower'] - $data['credits'];
			$data['upgradeprogress'] = 100 - ceil($data['upgradecredit'] / ($data['group']['creditslower'] - $data['group']['creditshigher']) * 100);
			$data['upgradeprogress'] = max($data['upgradeprogress'], 2);
		}
		unset($data['password'], $data['email'], $data['regip'], $data['lastip'], $data['regip_loc'], $data['lastip_loc'], $data['qq'], $data['alipay'], $data['icq'], $data['yahoo'], $data['msn'], $data['taobao'], $data['mobile'], $data['telephone'], $data['idcard'], $data['idcardtype'], $data['address'], $data['realname'], $data['zipcode']);
		$variable = array(
			'space' => $data,
			'extcredits' => $_G['setting']['extcredits'],
			'wsq' => array('wsq_apicredit' => getuserprofile('extcredits'.$_G['wechat']['setting']['wsq_apicredit'])),
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>