<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: collection_comment.php 33715 2013-08-07 01:59:25Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['uid'])) {
	showmessage('login_before_enter_home', null, array(), array('showmsg' => true, 'login' => 1));
}

$oplist = array('add', 'del', 'pop', 'recommend');
if(!in_array($op, $oplist)) {
	$op = '';
}

if(empty($op) || $op == 'add') {
	$_GET['handlekey'] = 'addComment';
	if(!$ctid) {
		showmessage('undefined_action', NULL);
	}

	if(!$_G['group']['allowcommentcollection']) {
		showmessage('collection_comment_closed');
	}

	require_once libfile('function/spacecp');


	if(!$_G['collection']['ctid']) {
		showmessage('collection_permission_deny');
	}

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime), array('return' => true));
	}

	$memberrate = C::t('forum_collectioncomment')->fetch_rate_by_ctid_uid($_G['collection']['ctid'], $_G['uid']);

	if(!trim($_GET['message']) && ((!$memberrate && !$_GET['ratescore']) || $memberrate)) {
		showmessage('collection_edit_checkentire');
	}

	if($_G['setting']['maxpostsize'] && strlen($_GET['message']) > $_G['setting']['maxpostsize']) {
		showmessage('post_message_toolong', '', array('maxpostsize' => $_G['setting']['maxpostsize']));
	}

	$newcomment = array(
	    'ctid' => $_G['collection']['ctid'],
	    'uid' => $_G['uid'],
	    'username' => $_G['username'],
		'message' => dhtmlspecialchars(censor($_GET['message'])),
	    'dateline' => $_G['timestamp'],
		'useip' => $_G['clientip'],
		'port' => $_G['remoteport']
	);

	if(!$memberrate) {
		$newcomment['rate'] = $_GET['ratescore'];
	} else {
		$_GET['ratescore'] = 0;
	}

	C::t('forum_collectioncomment')->insert($newcomment);
	C::t('forum_collection')->update_by_ctid($_G['collection']['ctid'], 0, 0, 1, 0, $_GET['ratescore'], $_G['collection']['ratenum']);

	if($_G['collection']['uid'] != $_G['uid']) {
		notification_add($_G['collection']['uid'], "system", 'collection_becommented', array('from_id'=>$_G['collection']['ctid'], 'from_idtype'=>'collectioncomment', 'ctid'=>$_G['collection']['ctid'], 'collectionname'=>$_G['collection']['name']), 1);
	}

	C::t('common_member_status')->update($_G['uid'], array('lastpost' => TIMESTAMP), 'UNBUFFERED');

	showmessage('collection_comment_succ', $tid ? 'forum.php?mod=viewthread&tid='.$tid : dreferer());
} elseif($op == 'del') {
	if(!submitcheck('formhash')) {
		showmessage('undefined_action', NULL);
	} else {
		if(!$_G['collection']['ctid'] || !checkcollectionperm($_G['collection'], $_G['uid']) || count($_GET['delcomment']) == 0) {
			showmessage('undefined_action', NULL);
		}
		$delrows = C::t('forum_collectioncomment')->delete_by_cid_ctid($_GET['delcomment'], $_G['collection']['ctid']);
		C::t('forum_collection')->update_by_ctid($_G['collection']['ctid'], 0, 0, -$delrows);

		showmessage('collection_comment_remove_succ', 'forum.php?mod=collection&action=view&op=comment&ctid='.$ctid);
	}
} elseif($op == 'pop') {
	$collectionthread = C::t('forum_collectionthread')->fetch_by_ctid_tid($ctid, $tid);
	if(!$collectionthread['ctid']) {
		showmessage('collection_permission_deny');
	}
	$thread = C::t('forum_thread')->fetch($tid);

	include template('forum/collection_commentpop');
} elseif($op == 'recommend') {
	if(!$_G['collection']['ctid']) {
		showmessage('collection_permission_deny');
	}
	if(!submitcheck('formhash')) {
		include template('forum/collection_recommend');
	} else {
		if(!$_GET['threadurl']) {
			showmessage('collection_recommend_url', '', array(), array('alert'=> 'error', 'closetime' => true, 'showdialog' => 1));
		}

		$touid = &$_G['collection']['uid'];
		$coef = 1;

		$subject = $message = lang('message', 'collection_recommend_message', array('fromuser' => $_G['username'], 'collectioname' => $_G['collection']['name'], 'url' => $_GET['threadurl']));
		if(C::t('home_blacklist')->count_by_uid_buid($touid, $_G['uid'])) {
			showmessage('is_blacklist', '', array(), array('return' => true));
		}
		if(($value = getuserbyuid($touid))) {
			require_once libfile('function/friend');
			$value['onlyacceptfriendpm'] = $value['onlyacceptfriendpm'] ? $value['onlyacceptfriendpm'] : ($_G['setting']['onlyacceptfriendpm'] ? 1 : 2);
			if($_G['group']['allowsendallpm'] || $value['onlyacceptfriendpm'] == 2 || ($value['onlyacceptfriendpm'] == 1 && friend_check($touid))) {
				$return = sendpm($touid, $subject, $message, '', 0, 0);
			} else {
				showmessage('message_can_not_send_onlyfriend', '', array(), array('return' => true));
			}
		} else {
			showmessage('message_bad_touid', '', array(), array('return' => true));
		}

		if($return > 0) {
			include_once libfile('function/stat');
			updatestat('sendpm', 0, $coef);

			C::t('common_member_status')->update($_G['uid'], array('lastpost' => TIMESTAMP), 'UNBUFFERED');
			!($_G['group']['exempt'] & 1) && updatecreditbyaction('sendpm', 0, array(), '', $coef);
			showmessage('collection_recommend_succ', '', array(), array('alert'=> 'right', 'closetime' => true, 'showdialog' => 1));
		}
	}
}

?>