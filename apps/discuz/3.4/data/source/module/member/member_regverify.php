<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_regverify.php 28003 2012-02-20 09:55:39Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if($_G['setting']['regverify'] == 2 && $_G['groupid'] == 8 && submitcheck('verifysubmit')) {

	if(($verify_member = C::t('common_member_validate')->fetch($_G['uid'])) && $verify_member['status'] == 1) {
		C::t('common_member_validate')->update($_G['uid'], array(
			'submittimes' => $verify_member['submittimes']+1,
			'submitdate' => $_G['timestamp'],
			'status' => '0',
			'message' => dhtmlspecialchars($_GET['regmessagenew'])
		));
		showmessage('submit_verify_succeed', 'home.php?mod=spacecp&ac=profile');
	} else {
		showmessage('undefined_action');
	}

}

?>