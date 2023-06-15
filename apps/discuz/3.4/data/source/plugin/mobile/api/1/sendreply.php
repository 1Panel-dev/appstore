<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sendreply.php 34771 2014-07-30 09:29:44Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'post';
$_GET['action'] = 'reply';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function post_mobile_message($message, $url_forward, $values, $extraparam, $custom) {
		if($values['tid'] && $values['pid']) {
			global $_G;

			$threadstatus = DB::result_first("SELECT status FROM ".DB::table('forum_thread')." WHERE tid='$values[tid]'");
			$setstatusold = base_convert(getstatus($threadstatus, 13).getstatus($threadstatus, 12).getstatus($threadstatus, 11), 2, 10);
			$updatestatus = false;
			if(!empty($_POST['allowsound'])) {
				$setstatus = array(1, 0, 0);
				$updatestatus = $setstatusold < 4;
			} elseif(!empty($_POST['allowphoto'])) {
				$setstatus = array(0, 1, 1);
				$updatestatus = $setstatusold < 3;
			} elseif(!empty($_POST['allowlocal'])) {
				$setstatus = array(0, 1, 0);
				$updatestatus = $setstatusold < 2;
			} else {
				$setstatus = array(0, 0, 1);
			}
			if($updatestatus) {
				foreach($setstatus as $i => $bit) {
					$threadstatus = setstatus(13 - $i, $bit, $threadstatus);
				}
				C::t('forum_thread')->update($values['tid'], array('status' => $threadstatus));
			}

			$posttable = getposttablebytid($values['tid']);
			$poststatus = DB::result_first("SELECT status FROM ".DB::table($posttable)." WHERE pid='$values[pid]'");
			$poststatus = setstatus(4, 1, $poststatus);
			if(!empty($_POST['allowlocal'])) {
				$poststatus = setstatus(6, 1, $poststatus);
			}
			if(!empty($_POST['allowsound'])) {
				$poststatus = setstatus(7, 1, $poststatus);
			}
			if(!empty($_POST['mobiletype']) && $_POST['mobiletype'] < 8) {
				$mobiletype = base_convert($_POST['mobiletype'], 10, 2);
				$mobiletype = sprintf('%03d', $mobiletype);
				for($i = 0;$i < 3;$i++) {
					$poststatus = setstatus(10 - $i, $mobiletype[$i], $poststatus);
				}
			}
			C::t('forum_post')->update('tid:'.$values['tid'], $values['pid'], array('status' => $poststatus));

			if($_POST['location']) {
				list($mapx, $mapy, $location) = explode('|', dhtmlspecialchars($_POST['location']));
				C::t('forum_post_location')->insert(array(
					'pid' => $values['pid'],
					'tid' => $values['tid'],
					'uid' => $_G['uid'],
					'mapx' => $mapx,
					'mapy' => $mapy,
					'location' => $location,
				));
			}
		}
	}

	function output() {
		global $_G;
		$variable = array(
			'tid' => $_G['tid'],
			'pid' => $GLOBALS['pid'],
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>