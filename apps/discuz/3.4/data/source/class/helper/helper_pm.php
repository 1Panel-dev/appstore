<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_pm.php 31440 2012-08-28 07:22:57Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_pm {


	public static function sendpm($toid, $subject, $message, $fromid = '', $replypmid = 0, $isusername = 0, $type = 0) {
		global $_G;
		if($fromid === '') {
			$fromid = $_G['uid'];
		}
		if($fromid) {
			if($fromid == $_G['uid']) {
				$sendpmmaxnum = $_G['group']['allowsendpmmaxnum'];
			} else {
				$user = getuserbyuid($fromid);
				loadcache('usergroup_'.$user['groupid']);
				$sendpmmaxnum = $_G['cache']['usergroup_'.$user['groupid']]['allowsendpmmaxnum'];
			}
			$currentnum = C::t('common_member_action_log')->count_day_hours(getuseraction('pmid'), $fromid);
			if($sendpmmaxnum && $currentnum >= $sendpmmaxnum) {
				return -16;
			}
		}

		loaducenter();
		$return = uc_pm_send($fromid, $toid, addslashes($subject), addslashes($message), 1, $replypmid, $isusername, $type);
		if($return > 0 && $fromid) {
			foreach(explode(',', $fromid) as $v) {
				useractionlog($v, 'pmid');
			}
		}

		return $return;
	}
}

?>