<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: viewthread.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'viewthread';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G, $thread;
		if($GLOBALS['hiddenreplies']) {
			foreach($GLOBALS['postlist'] as $k => $post) {
				if(!$post['first'] && $_G['uid'] != $post['authorid'] && $_G['uid'] != $_G['forum_thread']['authorid'] && !$_G['forum']['ismoderator']) {
					$GLOBALS['postlist'][$k]['message'] = lang('plugin/mobile', 'mobile_post_author_visible');
					$GLOBALS['postlist'][$k]['attachments'] = array();
				}
			}
		}

		$_G['thread']['lastpost'] = dgmdate($_G['thread']['lastpost']);

		$variable = array(
			'thread' => $_G['thread'],
			'fid' => $_G['fid'],
			'postlist' => array_values(mobile_core::getvalues($GLOBALS['postlist'], array('/^\d+$/'), array('pid', 'tid', 'author', 'first', 'dbdateline', 'dateline', 'username', 'adminid', 'memberstatus', 'authorid', 'username', 'groupid', 'memberstatus', 'status', 'message', 'number', 'memberstatus', 'groupid', 'attachment', 'attachments', 'attachlist', 'imagelist', 'anonymous'))),
			'imagelist' => array(),
			'ppp' => $_G['ppp'],
			'setting_rewriterule' => $_G['setting']['rewriterule'],
			'setting_rewritestatus' => $_G['setting']['rewritestatus'],
			'forum_threadpay' => $_G['forum_threadpay'],
			'cache_custominfo_postno' => $_G['cache']['custominfo']['postno'],
		);

		if(!empty($GLOBALS['threadsortshow'])) {
			$optionlist = array();
			foreach ($GLOBALS['threadsortshow']['optionlist'] AS $key => $val) {
				$val['optionid'] = $key;
				$optionlist[] = $val;
			}
			if(!empty($optionlist)) {
				$GLOBALS['threadsortshow']['optionlist'] = $optionlist;
				$GLOBALS['threadsortshow']['threadsortname'] = $_G['forum']['threadsorts']['types'][$thread['sortid']];
			}
		}
		$threadsortshow = mobile_core::getvalues($GLOBALS['threadsortshow'], array('/^(?!typetemplate).*$/'));
		if(!empty($threadsortshow)) {
			$variable['threadsortshow'] = $threadsortshow;
		}
		foreach($variable['postlist'] as $k => $post) {
			if(!$_G['forum']['ismoderator'] && $_G['setting']['bannedmessages'] & 1 && (($post['authorid'] && !$post['username']) || ($_G['thread']['digest'] == 0 && ($post['groupid'] == 4 || $post['groupid'] == 5 || $post['memberstatus'] == '-1')))) {
				$message = lang('forum/template', 'message_banned');
			} elseif(!$_G['forum']['ismoderator'] && $post['status'] & 1) {
				$message = lang('forum/template', 'message_single_banned');
			} elseif($GLOBALS['needhiddenreply']) {
				$message = lang('forum/template', 'message_ishidden_hiddenreplies');
			} elseif($post['first'] && $_G['forum_threadpay']) {
				$message = lang('forum/template', 'pay_threads').' '.$GLOBALS['thread']['price'].' '.$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'];
			} elseif($_G['forum_discuzcode']['passwordlock']) {
				$message = lang('forum/template', 'message_password_exists');
			} else {
				$message = '';
			}
			if($message) {
				$variable['postlist'][$k]['message'] = $message;
			}
			if($post['anonymous'] && !$_G['forum']['ismoderator']) {
				$variable['postlist'][$k]['username'] = $variable['postlist'][$k]['author'] = $_G['setting']['anonymoustext'];
				$variable['postlist'][$k]['adminid'] = $variable['postlist'][$k]['groupid'] = $variable['postlist'][$k]['authorid'] = 0;
				if($post['first']) {
					$variable['thread']['authorid'] = 0;
				}
			}
			if(strpos($variable['postlist'][$k]['message'], '[/tthread]') !== FALSE) {
				$matches = array();
				preg_match('/\[tthread=(.+?),(.+?)\](.*?)\[\/tthread\]/', $variable['postlist'][$k]['message'], $matches);
				$variable['postlist'][$k]['message'] = preg_replace('/\[tthread=(.+?)\](.*?)\[\/tthread\]/', lang('plugin/qqconnect', 'connect_tthread_message', array('username' => $matches[1], 'nick' => $matches[2])), $variable['postlist'][$k]['message']);
			}
			$firstpost = current($GLOBALS['postarr']);
			if($firstpost['first'] && strpos($firstpost['message'],'[/hide]') !== FALSE ){
				$authorreplyexist = false;
				if(!$_G['forum']['ismoderator']) {
					if($_G['uid']) {
						$_post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $pid);
						$authorreplyexist = $_post['tid'] == $_G['tid'] ? C::t('forum_post')->fetch_pid_by_tid_authorid($_G['tid'], $_G['uid']) : false;
					}
				} else {
					$authorreplyexist = true;
				}
				if(!$authorreplyexist){
					$aids = array();
					preg_match_all("/\[hide(.*?)?\]\s*(.*?)\s*\[\/hide\]/is",$firstpost['message'],$matches);
					foreach ($matches[2] as $match){
						preg_match_all("/\[attach\](\d+)\[\/attach\]/i",$match,$matchaids);
						$aids = array_merge($aids,$matchaids[1]);
					}
					foreach($aids as $aid){
						unset($variable['postlist'][$k]['attachments'][$aid]);
					}
					$variable['postlist'][$k]['attachlist'] = array_diff($variable['postlist'][$k]['attachlist'],$aids);
					$variable['postlist'][$k]['imagelist'] = array_diff($variable['postlist'][$k]['imagelist'],$aids);
				}
			}
		}

		foreach($GLOBALS['aimgs'] as $pid => $aids) {
			foreach($aids as $aid) {
				$variable['imagelist'][] = $GLOBALS['postlist'][$pid]['attachments'][$aid]['url'].$GLOBALS['postlist'][$pid]['attachments'][$aid]['attachment'];
			}
		}

		if(!empty($GLOBALS['polloptions'])) {
			$variable['special_poll']['polloptions'] = $GLOBALS['polloptions'];
			$variable['special_poll']['expirations'] = $GLOBALS['expirations'];
			$variable['special_poll']['multiple'] = $GLOBALS['multiple'];
			$variable['special_poll']['maxchoices'] = $GLOBALS['maxchoices'];
			$variable['special_poll']['voterscount'] = $GLOBALS['voterscount'];
			$variable['special_poll']['visiblepoll'] = $GLOBALS['visiblepoll'];
			$variable['special_poll']['allowvote'] = $_G['group']['allowvote'];
			$variable['special_poll']['remaintime'] = $thread['remaintime'];
		}
		if(!empty($GLOBALS['rewardprice'])) {
			$variable['special_reward']['rewardprice'] = $GLOBALS['rewardprice'].' '.$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]['title'];
			$variable['special_reward']['bestpost'] = $GLOBALS['bestpost'];
		}
		if(!empty($GLOBALS['trades'])) {
			$variable['special_trade'] = $GLOBALS['trades'];
		}
		if(!empty($GLOBALS['debate'])) {
			$variable['special_debate'] = $GLOBALS['debate'];
		}
		if(!empty($GLOBALS['activity'])) {
			$variable['special_activity'] = $GLOBALS['activity'];
		}

		$variable['forum']['password'] = $variable['forum']['password'] ? '1' : '0';
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>