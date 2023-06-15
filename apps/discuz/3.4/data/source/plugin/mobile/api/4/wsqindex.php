<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wsqindex.php 34422 2014-04-23 09:56:17Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

define('MOBILE_HIDE_STICKY', !isset($_GET['hidesticky']) ? 1 : $_GET['hidesticky']);

$_GET['mod'] = 'forumdisplay';
include_once 'forum.php';

class mobile_api {

	function common() {
		global $_G;
		$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);
		if (!in_array('wechat', $_G['setting']['plugins']['available']) || !$_G['wechat']['setting']['wsq_fid']) {
			mobile_core::result(mobile_core::variable(array()));
		}
		$_GET['fid'] = $_G['fid'] = $_G['wechat']['setting']['wsq_fid'];
		loadforum();
		if (!empty($_GET['pw'])) {
			$_GET['action'] = 'pwverify';
		}
		$_G['forum']['allowglobalstick'] = false;
	}

	function output() {
		global $_G;
		include_once 'source/plugin/mobile/api/4/sub_threadlist.php';

		loadcache('mobile_stats');
		if (!$_G['cache']['mobile_stats'] || TIMESTAMP - $_G['cache']['mobile_stats']['expiration'] > 3600) {
			$forums = C::t('forum_forum')->fetch_all_by_status(1);
			foreach ($forums as $forum) {
				$posts += $forum['posts'];
			}
			loadcache('userstats');
			$_G['cache']['mobile_stats']['variable'] = array(
			    'totalposts' => $posts,
			    'totalmembers' => $_G['cache']['userstats']['totalmembers'],
			);
			savecache('mobile_stats', array('variable' => $_G['cache']['mobile_stats']['variable'], 'expiration' => TIMESTAMP));
		}
		$variable['stats'] = $_G['cache']['mobile_stats']['variable'];
		require_once DISCUZ_ROOT . './source/plugin/wechat/wsq.class.php';
		$variable['wsqsiteinfo'] = wsq::siteinfo();
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>