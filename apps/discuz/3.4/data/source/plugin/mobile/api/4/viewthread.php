<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: viewthread.php 36278 2016-12-09 07:52:35Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'viewthread';
include_once 'forum.php';

class mobile_api {

	function common() {

	}

	function output() {
		global $_G, $thread;
		if ($GLOBALS['hiddenreplies']) {
			foreach ($GLOBALS['postlist'] as $k => $post) {
				if (!$post['first'] && $_G['uid'] != $post['authorid'] && $_G['uid'] != $_G['forum_thread']['authorid'] && !$_G['forum']['ismoderator']) {
					$GLOBALS['postlist'][$k]['message'] = lang('plugin/mobile', 'mobile_post_author_visible');
					$GLOBALS['postlist'][$k]['attachments'] = array();
				}
			}
		}

		$_G['thread']['lastpost'] = dgmdate($_G['thread']['lastpost']);
		$_G['thread']['ordertype'] = $GLOBALS['ordertype'];
		$_G['thread']['recommend'] = $_G['uid'] && C::t('forum_memberrecommend')->fetch_by_recommenduid_tid($_G['uid'], $_G['tid']) ? 1 : 0;
		if (!empty($_GET['viewpid'])) {
			$GLOBALS['postlist'][$_GET['viewpid']] = $GLOBALS['post'];
		}
		if ($GLOBALS['rushreply']) {
			$_G['thread']['rushreply'] = $GLOBALS['rushreply'];
			$_G['thread']['rushresult'] = $GLOBALS['rushresult'];
		}
		foreach ($GLOBALS['comments'] as $pid => $comments) {
			$comments = mobile_core::getvalues($comments, array('/^\d+$/'), array('id', 'tid', 'pid', 'author', 'authorid', 'dateline', 'comment', 'avatar'));
			foreach ($comments as $k => $c) {
				$comments[$k]['avatar'] = avatar($c['authorid'], 'small', true);
				$comments[$k]['dateline'] = dgmdate($c['dateline'], 'u');
			}
			$GLOBALS['comments'][$pid] = $comments;
		}
		$variable = array(
		    'thread' => $_G['thread'],
		    'fid' => $_G['fid'],
		    'postlist' => array_values(mobile_core::getvalues($GLOBALS['postlist'], array('/^\d+$/'), array('pid', 'tid', 'author', 'first', 'dbdateline', 'dateline', 'username', 'adminid', 'memberstatus', 'authorid', 'username', 'groupid', 'memberstatus', 'status', 'message', 'number', 'memberstatus', 'groupid', 'attachment', 'attachments', 'attachlist', 'imagelist', 'anonymous', 'position', 'rewardfloor', 'replycredit'))),
		    'allowpostcomment' => $_G['setting']['allowpostcomment'],
		    'comments' => $GLOBALS['comments'],
		    'commentcount' => $GLOBALS['commentcount'],
		    'ppp' => $_G['ppp'],
		    'setting_rewriterule' => $_G['setting']['rewriterule'],
		    'setting_rewritestatus' => $_G['setting']['rewritestatus'],
		    'forum_threadpay' => $_G['forum_threadpay'],
		    'cache_custominfo_postno' => $_G['cache']['custominfo']['postno'],
		);

		if (!empty($GLOBALS['threadsortshow'])) {
			$optionlist = array();
			foreach ($GLOBALS['threadsortshow']['optionlist'] AS $key => $val) {
				$val['optionid'] = $key;
				$optionlist[] = $val;
			}
			if (!empty($optionlist)) {
				$GLOBALS['threadsortshow']['optionlist'] = $optionlist;
				$GLOBALS['threadsortshow']['threadsortname'] = $_G['forum']['threadsorts']['types'][$thread['sortid']];
			}
		}
		$threadsortshow = mobile_core::getvalues($GLOBALS['threadsortshow'], array('/^(?!typetemplate).*$/'));
		if (!empty($threadsortshow)) {
			$variable['threadsortshow'] = $threadsortshow;
		}
		foreach ($variable['postlist'] as $k => $post) {
			if (!$_G['forum']['ismoderator'] && $_G['setting']['bannedmessages'] & 1 && (($post['authorid'] && !$post['username']) || ($_G['thread']['digest'] == 0 && ($post['groupid'] == 4 || $post['groupid'] == 5 || $post['memberstatus'] == '-1')))) {
				$message = lang('forum/template', 'message_banned');
			} elseif (!$_G['forum']['ismoderator'] && $post['status'] & 1) {
				$message = lang('forum/template', 'message_single_banned');
			} elseif ($GLOBALS['needhiddenreply']) {
				$message = lang('forum/template', 'message_ishidden_hiddenreplies');
			} elseif ($post['first'] && $_G['forum_threadpay']) {
				$message = lang('forum/template', 'pay_threads') . ' ' . $GLOBALS['thread']['price'] . ' ' . $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['unit'] . $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'];
			} elseif ($_G['forum_discuzcode']['passwordlock']) {
				$message = lang('forum/template', 'message_password_exists');
			} else {
				$message = '';
			}
			if ($message) {
				$variable['postlist'][$k]['message'] = $message;
			}
			if ($post['anonymous'] && !$_G['forum']['ismoderator']) {
				$variable['postlist'][$k]['username'] = $variable['postlist'][$k]['author'] = $_G['setting']['anonymoustext'];
				$variable['postlist'][$k]['adminid'] = $variable['postlist'][$k]['groupid'] = $variable['postlist'][$k]['authorid'] = 0;
				if ($post['first']) {
					$variable['thread']['authorid'] = 0;
				}
			}
			if (strpos($variable['postlist'][$k]['message'], '[/tthread]') !== FALSE) {
				$matches = array();
				preg_match('/\[tthread=(.+?),(.+?)\](.*?)\[\/tthread\]/', $variable['postlist'][$k]['message'], $matches);
				$variable['postlist'][$k]['message'] = preg_replace('/\[tthread=(.+?)\](.*?)\[\/tthread\]/', lang('plugin/qqconnect', 'connect_tthread_message', array('username' => $matches[1], 'nick' => $matches[2])), $variable['postlist'][$k]['message']);
			}
			$variable['postlist'][$k]['message'] = preg_replace("/<a\shref=\"([^\"]+?)\"\starget=\"_blank\">\[viewimg\]<\/a>/is", "<img src=\"\\1\" />", $variable['postlist'][$k]['message']);
			$variable['postlist'][$k]['message'] = mobile_api::_findimg($variable['postlist'][$k]['message']);
			if ($GLOBALS['aimgs'][$post['pid']]) {
				$imagelist = array();
				foreach ($GLOBALS['aimgs'][$post['pid']] as $aid) {
					$extra = '';
					$url = mobile_api::_parseimg('', $GLOBALS['postlist'][$post['pid']]['attachments'][$aid]['url'] . $GLOBALS['postlist'][$post['pid']]['attachments'][$aid]['attachment'], '');
					if ($GLOBALS['postlist'][$post['pid']]['attachments'][$aid]['thumb']) {
						$extra = 'file="' . $url . '" ';
						$url .= '.thumb.jpg';
					}
					$extra .= 'attach="' . $post['pid'] . '" ';
					if (strexists($variable['postlist'][$k]['message'], '[attach]' . $aid . '[/attach]')) {
						$variable['postlist'][$k]['message'] = str_replace('[attach]' . $aid . '[/attach]', mobile_image($url, $extra), $variable['postlist'][$k]['message']);
						unset($variable['postlist'][$k]['attachments'][$aid]);
					} elseif (!in_array($aid, $_G['forum_attachtags'][$post['pid']])) {
						$imagelist[] = $aid;
					}
				}
				$variable['postlist'][$k]['imagelist'] = $imagelist;
			}
			$variable['postlist'][$k]['message'] = preg_replace("/\[attach\]\d+\[\/attach\]/i", '', $variable['postlist'][$k]['message']);
			$variable['postlist'][$k]['message'] = preg_replace('/(&nbsp;){2,}/', '', $variable['postlist'][$k]['message']);
			$variable['postlist'][$k]['dateline'] = strip_tags($post['dateline']);
			$variable['postlist'][$k]['groupiconid'] = mobile_core::usergroupIconId($post['groupid']);
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
				}
			}
		}

		if (!empty($GLOBALS['polloptions'])) {
			$variable['special_poll']['polloptions'] = $GLOBALS['polloptions'];
			$variable['special_poll']['expirations'] = $GLOBALS['expirations'];
			$variable['special_poll']['multiple'] = $GLOBALS['multiple'];
			$variable['special_poll']['maxchoices'] = $GLOBALS['maxchoices'];
			$variable['special_poll']['voterscount'] = $GLOBALS['voterscount'];
			$variable['special_poll']['visiblepoll'] = $GLOBALS['visiblepoll'];
			$variable['special_poll']['allowvote'] = $_G['group']['allowvote'];
			$variable['special_poll']['remaintime'] = $thread['remaintime'];
		}
		if (!empty($GLOBALS['rewardprice'])) {
			$variable['special_reward']['rewardprice'] = $GLOBALS['rewardprice'] . ' ' . $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]['title'];
			$variable['special_reward']['bestpost'] = $GLOBALS['bestpost'];
		}
		if (!empty($GLOBALS['trades'])) {
			$variable['special_trade'] = $GLOBALS['trades'];
		}
		if (!empty($GLOBALS['debate'])) {
			$variable['special_debate'] = $GLOBALS['debate'];
		}
		if (!empty($GLOBALS['activity'])) {
			$variable['special_activity'] = $GLOBALS['activity'];
			$variable['special_activity']['allapplynum'] = $GLOBALS['allapplynum'];
			if ($_G['setting']['activitycredit'] && $GLOBALS['activity']['credit'] && !$GLOBALS['applied']) {
				$variable['special_activity']['creditcost'] = $GLOBALS['activity']['credit'] . ' ' . $_G['setting']['extcredits'][$_G['setting']['activitycredit']]['title'];
			}
			$setting = array();
			foreach ($GLOBALS['activity']['ufield']['userfield'] as $field) {
				$setting[$field] = $_G['cache']['profilesetting'][$field];
			}
			$variable['special_activity']['joinfield'] = mobile_core::getvalues($setting, array('/./'), array('fieldid', 'formtype', 'available', 'title', 'formtype', 'choices'));
			$variable['special_activity']['userfield'] = $GLOBALS['ufielddata']['userfield'];
			$variable['special_activity']['extfield'] = $GLOBALS['ufielddata']['extfield'];
			$variable['special_activity']['basefield'] = mobile_core::getvalues($GLOBALS['applyinfo'], array('message', 'payment'));
			$variable['special_activity']['closed'] = $GLOBALS['activityclose'];
			if ($GLOBALS['applied'] && $GLOBALS['isverified'] < 2) {
				if (!$GLOBALS['isverified']) {
					$variable['special_activity']['status'] = 'wait';
				} else {
					$variable['special_activity']['status'] = 'joined';
				}
				if (!$GLOBALS['activityclose']) {
					$variable['special_activity']['button'] = 'cancel';
				}
			} elseif (!$GLOBALS['activityclose']) {
				if ($GLOBALS['isverified'] != 2) {
					$variable['special_activity']['status'] = 'join';
				} else {
					$variable['special_activity']['status'] = 'complete';
				}
				$variable['special_activity']['button'] = 'join';
			}
		}

		$variable['forum']['password'] = $variable['forum']['password'] ? '1' : '0';
		mobile_core::result(mobile_core::variable($variable));
	}

	function _findimg($string) {
		return preg_replace_callback('/(<img src=\")(.+?)(\".*?\>)/is', array(__CLASS__, 'findimg_callback_parseimg_123'), $string);
	}

	static function findimg_callback_parseimg_123($matches) {
		return mobile_api::_parseimg($matches[1], $matches[2], $matches[3]);
	}

	function _parseimg($before, $img, $after) {
		$before = stripslashes($before);
		$after = stripslashes($after);
		if (!in_array(strtolower(substr($img, 0, 6)), array('http:/', 'https:', 'ftp://'))) {
			global $_G;
			$img = $_G['siteurl'] . $img;
		}
		return $before . $img . $after;
	}

}

?>