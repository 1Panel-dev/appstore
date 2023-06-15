<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: toplist.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'forumdisplay';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;
		$threads = array();
		loadcache('mobile_toplist_'.$_G['fid']);
		if(!$_G['cache']['mobile_toplist_'.$_G['fid']] || TIMESTAMP - $_G['cache']['mobile_toplist_'.$_G['fid']]['expiration'] > 3600) {
			$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE tid IN (".dimplode($GLOBALS['stickytids']).") ORDER BY lastpost DESC");
			while($thread = DB::fetch($query)) {
				$threads[] = $thread;
			}
			$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE `fid`='".$_G['fid']."' AND `displayorder`='1' ORDER BY lastpost DESC");
			while($thread = DB::fetch($query)) {
				$threads[] = $thread;
			}
			savecache('mobile_toplist_'.$_G['fid'], array('variable' => $threads, 'expiration' => TIMESTAMP));
		} else {
			$threads = $_G['cache']['mobile_toplist_'.$_G['fid']]['variable'];
		}
		$variable = array(
			'forum_threadlist' => mobile_core::getvalues($threads, array('/^\d+$/')),
		);
		$variable['forum']['password'] = $variable['forum']['password'] ? '1' : '0';
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>