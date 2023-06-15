<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: showactivity.php 35113 2014-11-26 03:31:52Z anezhou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'misc';
$_GET['action'] = 'commentmore';
$_GET['inajax'] = 1;
include_once 'forum.php';

class mobile_api {

	function common() {
		global $_G;
		if($_GET['do'] == 'recommend') {
			if(!$_GET['hash'] || FORMHASH != $_GET['hash'] || !$_GET['pid'] || !$_GET['tid'] || !$_G['uid']) {
				mobile_core::result(mobile_core::variable(array('result' => -1)));
			}
			$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);
			if(!$_G['wechat']['setting']['wsq_allow'] || !in_array($_GET['tid'], (array)$_G['wechat']['setting']['showactivity']['tids'])) {
				mobile_core::result(mobile_core::variable(array('result' => -2)));
			}
			$postinfo = C::t('forum_post')->fetch('tid:'.$_GET['tid'], $_GET['pid']);
			if(!$postinfo) {
				mobile_core::result(mobile_core::variable(array('result' => -3)));
			}
			$activity = C::t('forum_activity')->fetch($_G['tid']);
			if($activity['starttimeto'] && $activity['starttimeto'] < TIMESTAMP) {
				mobile_core::result(mobile_core::variable(array('result' => -4)));
			}
			$post = C::t('forum_debatepost')->fetch($_GET['pid']);
			if(!$post) {
				C::t('forum_debatepost')->insert(array(
				    'pid' => $_GET['pid'],
				    'tid' => $_GET['tid'],
				    'dateline' => TIMESTAMP,
				    'stand' => 0,
				    'voters' => 1,
				    'voterids' => "$_G[uid]\t",
				));
				mobile_core::result(mobile_core::variable(array('result' => 1)));
			} elseif(strpos("\t".$post['voterids'], "\t$_G[uid]\t") === FALSE) {
				C::t('forum_debatepost')->update_voters($_GET['pid'], $_G['uid']);
				mobile_core::result(mobile_core::variable(array('result' => 1)));
			} else {
				mobile_core::result(mobile_core::variable(array('result' => 0)));
			}
		}

	}

	function output() {
		$comments = array();
		foreach($GLOBALS['comments'] as $comment) {
			$comments[] = array(
				'author' => $comment['author'],
				'authorid' => $comment['authorid'],
				'avatar' => avatar($comment['authorid'], 'small', 1),
				'message' => $comment['comment'],
				'dateline' => strip_tags($comment['dateline']),
			);
		}
		$variable = array(
			'tid' => $_GET['tid'],
			'pid' => $_GET['pid'],
			'postlist' => $comments,
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>