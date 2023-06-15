<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newthread.php 33695 2013-08-03 04:39:22Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['forum']['fid']) || $_G['forum']['type'] == 'group') {
	showmessage('forum_nonexistence');
}

if(($special == 1 && !$_G['group']['allowpostpoll']) || ($special == 2 && !$_G['group']['allowposttrade']) || ($special == 3 && !$_G['group']['allowpostreward']) || ($special == 4 && !$_G['group']['allowpostactivity']) || ($special == 5 && !$_G['group']['allowpostdebate'])) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

if($_G['setting']['connect']['allow'] && $_G['setting']['accountguard']['postqqonly'] && !$_G['member']['conisbind']) {
	showmessage('postperm_qqonly_nopermission');
}

if(!$_G['uid'] && !((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])))) {
	if(!defined('IN_MOBILE')) {
		showmessage('postperm_login_nopermission', NULL, array(), array('login' => 1));
	} else {
		showmessage('postperm_login_nopermission_mobile', NULL, array('referer' => rawurlencode(dreferer())), array('login' => 1));
	}
} elseif(empty($_G['forum']['allowpost'])) {
	if(!$_G['forum']['postperm'] && !$_G['group']['allowpost']) {
		showmessage('postperm_none_nopermission', NULL, array(), array('login' => 1));
	} elseif($_G['forum']['postperm'] && !forumperm($_G['forum']['postperm'])) {
		showmessagenoperm('postperm', $_G['fid'], $_G['forum']['formulaperm']);
	}
} elseif($_G['forum']['allowpost'] == -1) {
	showmessage('post_forum_newthread_nopermission', NULL);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage('postperm_login_nopermission', NULL, array(), array('login' => 1));
}

checklowerlimit('post', 0, 1, $_G['forum']['fid']);

if(!submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck)) {

	$st_t = $_G['uid'].'|'.TIMESTAMP;
	dsetcookie('st_t', $st_t.'|'.md5($st_t.$_G['config']['security']['authkey']));

	if(helper_access::check_module('group')) {
		$mygroups = $groupids = array();
		$groupids = C::t('forum_groupuser')->fetch_all_fid_by_uids($_G['uid']);
		array_slice($groupids, 0, 20);
		$query = C::t('forum_forum')->fetch_all_info_by_fids($groupids);
		foreach($query as $group) {
			$mygroups[$group['fid']] = $group['name'];
		}
	}

	$savethreads = array();
	$savethreadothers = array();
	foreach(C::t('forum_post')->fetch_all_by_authorid(0, $_G['uid'], false, '', 0, 20, 1, -3) as $savethread) {
		$savethread['dateline'] = dgmdate($savethread['dateline'], 'u');
		if($_G['fid'] == $savethread['fid']) {
			$savethreads[] = $savethread;
		} else {
			$savethreadothers[] = $savethread;
		}
	}
	$savethreadcount = count($savethreads);
	$savethreadothercount = count($savethreadothers);
	if($savethreadothercount) {
		loadcache('forums');
	}
	$savecount = $savethreadcount + $savethreadothercount;
	unset($savethread);

	$isfirstpost = 1;
	$allownoticeauthor = 1;
	$tagoffcheck = '';
	$showthreadsorts = !empty($sortid) || $_G['forum']['threadsorts']['required'] && empty($special);
	if(empty($sortid) && empty($special) && $_G['forum']['threadsorts']['required'] && $_G['forum']['threadsorts']['types']) {
		$tmp = array_keys($_G['forum']['threadsorts']['types']);
		$sortid = $tmp[0];

		require_once libfile('post/threadsorts', 'include');
	}

	if($special == 2 && $_G['group']['allowposttrade']) {

		$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
		$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
		$trade['expiration'] = $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
		$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
		$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
		$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));

	} elseif($specialextra) {

		$threadpluginclass = null;
		if(isset($_G['setting']['threadplugins'][$specialextra]['module'])) {
			$threadpluginfile = DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
			if(file_exists($threadpluginfile)) {
				@include_once $threadpluginfile;
				$classname = 'threadplugin_'.$specialextra;
				if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread')) {
					$threadplughtml = $threadpluginclass->newthread($_G['fid']);
					$pluginlang = lang('plugin/'.$specialextra);
					$buttontext = property_exists($threadpluginclass, 'buttontext') ? (isset($pluginlang[$threadpluginclass->buttontext]) ? $pluginlang[$threadpluginclass->buttontext] : $threadpluginclass->buttontext) : '';
					$iconfile = $threadpluginclass->iconfile;
					$iconsflip = array_flip($_G['cache']['icons']);
					$thread['iconid'] = $iconsflip[$iconfile];
				}
			}
		}

		if(!is_object($threadpluginclass)) {
			$specialextra = '';
		}
	}

	if($special == 4) {
		$activity = array('starttimeto' => '', 'starttimefrom' => '', 'place' => '', 'class' => '', 'cost' => '', 'number' => '', 'gender' => '', 'expiration' => '');
		$activitytypelist = $_G['setting']['activitytype'] ? explode("\n", trim($_G['setting']['activitytype'])) : '';
	}

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach(0);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
	}

	!isset($attachs['unused']) && $attachs['unused'] = array();
	!isset($imgattachs['unused']) && $imgattachs['unused'] = array();

	getgpc('infloat') ? include template('forum/post_infloat') : include template('forum/post');

} else {
	if($_GET['mygroupid']) {
		$mygroupid = explode('__', $_GET['mygroupid']);
		$mygid = intval($mygroupid[0]);
		if($mygid) {
			$mygname = $mygroupid[1];
			if(count($mygroupid) > 2) {
				unset($mygroupid[0]);
				$mygname = implode('__', $mygroupid);
			}
			$message .= '[groupid='.intval($mygid).']'.$mygname.'[/groupid]';
			C::t('forum_forum')->update_commoncredits(intval($mygroupid[0]));
		}
	}
	$modthread = C::m('forum_thread');
	$bfmethods = $afmethods = array();

	$params = array(
		'subject' => $subject,
		'message' => $message,
		'typeid' => $typeid,
		'sortid' => $sortid,
		'special' => $special,
	);

	$_GET['save'] = $_G['uid'] ? $_GET['save'] : 0;

	if ($_G['group']['allowsetpublishdate'] && $_GET['cronpublish'] && $_GET['cronpublishdate']) {
		$publishdate = strtotime($_GET['cronpublishdate']);
		if ($publishdate > $_G['timestamp']) {
			$_GET['save'] = 1;
		} else {
			$publishdate = $_G['timestamp'];
		}
	} else {
		$publishdate = $_G['timestamp'];
	}
	$params['publishdate'] = $publishdate;
	$params['save'] = $_GET['save'];

	$params['sticktopic'] = $_GET['sticktopic'];

	$params['digest'] = $_GET['addtodigest'];
	$params['readperm'] = $readperm;
	$params['isanonymous'] = $_GET['isanonymous'];
	$params['price'] = $_GET['price'];


	if(in_array($special, array(1, 2, 3, 4, 5))) {
		$specials = array(
			1 => 'extend_thread_poll',
			2 => 'extend_thread_trade',
			3 => 'extend_thread_reward',
			4 => 'extend_thread_activity',
			5 => 'extend_thread_debate'
		);
		$bfmethods[] = array('class' => $specials[$special], 'method' => 'before_newthread');
		$afmethods[] = array('class' => $specials[$special], 'method' => 'after_newthread');

		if(!empty($_GET['addfeed'])) {
			$modthread->attach_before_method('feed', array('class' => $specials[$special], 'method' => 'before_feed'));
		}
	}

	if($special == 1) {


	} elseif($special == 3) {


	} elseif($special == 4) {
	} elseif($special == 5) {


	} elseif($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit')) {
			$threadpluginclass->newthread_submit($_G['fid']);
		}
		$special = 127;
		$params['special'] = 127;
		$params['message'] .= chr(0).chr(0).chr(0).$specialextra;

	}

	$params['typeexpiration'] = $_GET['typeexpiration'];






	$params['ordertype'] = $_GET['ordertype'];

	$params['hiddenreplies'] = $_GET['hiddenreplies'];

	$params['allownoticeauthor'] = $_GET['allownoticeauthor'];
	$params['tags'] = $_GET['tags'];
	$params['bbcodeoff'] = $_GET['bbcodeoff'];
	$params['smileyoff'] = $_GET['smileyoff'];
	$params['parseurloff'] = $_GET['parseurloff'];
	$params['usesig'] = $_GET['usesig'];
	$params['htmlon'] = $_GET['htmlon'];
	if($_G['group']['allowimgcontent']) {
		$params['imgcontent'] = $_GET['imgcontent'];
		$params['imgcontentwidth'] = $_G['setting']['imgcontentwidth'] ? intval($_G['setting']['imgcontentwidth']) : 100;
	}

	$params['geoloc'] = diconv($_GET['geoloc'], 'UTF-8');

	if($_GET['rushreply']) {
		$bfmethods[] = array('class' => 'extend_thread_rushreply', 'method' => 'before_newthread');
		$afmethods[] = array('class' => 'extend_thread_rushreply', 'method' => 'after_newthread');
	}

	$bfmethods[] = array('class' => 'extend_thread_replycredit', 'method' => 'before_newthread');
	$afmethods[] = array('class' => 'extend_thread_replycredit', 'method' => 'after_newthread');

	if($sortid) {
		$bfmethods[] = array('class' => 'extend_thread_sort', 'method' => 'before_newthread');
		$afmethods[] = array('class' => 'extend_thread_sort', 'method' => 'after_newthread');
	}
	$bfmethods[] = array('class' => 'extend_thread_allowat', 'method' => 'before_newthread');
	$afmethods[] = array('class' => 'extend_thread_allowat', 'method' => 'after_newthread');
	$afmethods[] = array('class' => 'extend_thread_image', 'method' => 'after_newthread');

	if(!empty($_GET['adddynamic'])) {
		$afmethods[] = array('class' => 'extend_thread_follow', 'method' => 'after_newthread');
	}

	$modthread->attach_before_methods('newthread', $bfmethods);
	$modthread->attach_after_methods('newthread', $afmethods);

	$return = $modthread->newthread($params);
	$tid = $modthread->tid;
	$pid = $modthread->pid;









	dsetcookie('clearUserdata', 'forum');
	if($specialextra) {
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit_end')) {
			$threadpluginclass->newthread_submit_end($_G['fid'], $modthread->tid);
		}
	}
	if(!$modthread->param('modnewthreads') && !empty($_GET['addfeed'])) {
		$modthread->feed();
	}

	if(!empty($_G['setting']['rewriterule']['forum_viewthread']) && in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
		$returnurl = rewriteoutput('forum_viewthread', 1, '', $modthread->tid, 1, '', $extra);
	} else {
		$returnurl = "forum.php?mod=viewthread&tid={$modthread->tid}&extra=$extra";
	}
	$values = array('fid' => $modthread->forum('fid'), 'tid' => $modthread->tid, 'pid' => $modthread->pid, 'coverimg' => '', 'sechash' => !empty($_GET['sechash']) ? $_GET['sechash'] : '');
	showmessage($return, $returnurl, array_merge($values, (array)$modthread->param('values')), $modthread->param('param'));


}


?>