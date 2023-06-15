<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: profiles.php 34989 2014-09-24 07:22:03Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once 'misc.php';

class mobile_api {

	function common() {
		global $_G;
		$uids = explode(',', $_GET['uids']);
		if(!$uids) {
			mobile_core::result(mobile_core::variable(array()));
		}
		$profiles = C::t('common_member')->fetch_all_username_by_uid($uids);
		$return = array();
		foreach($uids as $uid) {
			$return[] = array('uid' => $uid, 'username' => $profiles[$uid]);
		}
		mobile_core::result(mobile_core::variable(array('profiles' => $return)));
	}

	function output() {

	}

}

?>