<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forumdisplay.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

define('MOBILE_HIDE_STICKY', !isset($_GET['hidesticky']) ? 1 : $_GET['hidesticky']);

$_GET['mod'] = 'forumdisplay';
include_once 'forum.php';

class mobile_api {

	function common() {
		global $_G;
		if(!empty($_GET['pw'])) {
			$_GET['action'] = 'pwverify';
		}
		$_G['forum']['allowglobalstick'] = false;
	}

	function output() {
		global $_G;
		foreach($_G['forum_threadlist'] as $k => $thread) {
			$_G['forum_threadlist'][$k]['cover'] = array();
			if($thread['cover']) {
				$_img = @getimagesize($thread['coverpath']);
				if($_img) {
					$_G['forum_threadlist'][$k]['cover'] = array('w' => $_img[0], 'h' => $_img[1]);
				}
			}
			if(!$thread['authorid'] || !$thread['author']) {
				$_G['forum_threadlist'][$k]['author'] = $_G['setting']['anonymoustext'];
				$_G['forum_threadlist'][$k]['authorid'] = 0;
			}
		}
		$variable = array(
			'forum' => mobile_core::getvalues($_G['forum'], array('fid', 'fup', 'name', 'threads', 'posts', 'rules', 'autoclose', 'password')),
			'group' => mobile_core::getvalues($_G['group'], array('groupid', 'grouptitle')),
			'forum_threadlist' => mobile_core::getvalues($_G['forum_threadlist'], array('/^\d+$/'), array('tid', 'author', 'authorid', 'subject', 'subject', 'dbdateline', 'dateline', 'dblastpost', 'lastpost', 'lastposter', 'attachment', 'replies', 'readperm', 'views', 'digest', 'cover')),
			'sublist' => mobile_core::getvalues($GLOBALS['sublist'], array('/^\d+$/'), array('fid', 'name', 'threads', 'todayposts', 'posts')),
			'tpp' => $_G['tpp'],
			'page' => $GLOBALS['page'],
		);
		if(!empty($_G['forum']['threadtypes']) || !empty($_GET['debug'])) {
			$variable['threadtypes'] = $_G['forum']['threadtypes'];
		}
		if(!empty($_G['forum']['threadsorts']) || !empty($_GET['debug'])) {
			$variable['threadsorts'] = $_G['forum']['threadsorts'];
		}
		$variable['forum']['password'] = $variable['forum']['password'] ? '1' : '0';
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>