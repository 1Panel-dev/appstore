<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_comment.php 28261 2012-02-27 02:26:09Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


$tospace = $pic = $blog = $album = $share = $poll = array();

include_once libfile('class/bbcode');
$bbcode = & bbcode::instance();

if($_POST['idtype'] == 'uid' && ($seccodecheck || $secqaacheck)) {
	$seccodecheck = 0;
	$secqaacheck = 0;
}

if(submitcheck('commentsubmit', 0, $seccodecheck, $secqaacheck)) {

	if(!checkperm('allowcomment')) {
		showmessage('no_privilege_comment', '', array(), array('return' => true));
	}

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime), array('return' => true));
	}

	$id = intval($_POST['id']);
	$idtype = $_POST['idtype'];
	$message = getstr($_POST['message'], 0, 0, 0, 2);
	$cid = empty($_POST['cid'])?0:intval($_POST['cid']);

	if(strlen($message) < 2) {
		showmessage('content_is_too_short', '', array(), array());
	}

	require_once libfile('function/comment');
	$cidarr = add_comment($message, $id, $idtype, $cid);

	if($cidarr['cid'] != 0) {
		showmessage($cidarr['msg'], dreferer(), $cidarr['magvalues'], $_GET['quickcomment'] ? array('msgtype' => 3, 'showmsg' => true) : array('showdialog' => 3, 'showmsg' => true, 'closetime' => true));
	} else {
		showmessage('no_privilege_comment', '', array(), array('return' => true));
	}
}

$cid = empty($_GET['cid'])?0:intval($_GET['cid']);

if($_GET['op'] == 'edit') {
	if($_G['adminid'] != 1 && $_GET['modcommentkey'] != modauthkey($_GET['cid'])) {
		$authorid = intval($_G['uid']);
	} else {
		$authorid = '';
	}
	if(!$comment = C::t('home_comment')->fetch($cid, $authorid)) {
		showmessage('no_privilege_comment_edit');
	}

	if(submitcheck('editsubmit')) {

		$message = getstr($_POST['message'], 0, 0, 0, 2);
		if(strlen($message) < 2) showmessage('content_is_too_short');
		$message = censor($message, NULL, FALSE, FALSE);
		if(censormod($message)) {
			$comment_status = 1;
		} else {
			$comment_status = 0;
		}
		if($comment_status == 1) {
			$idtype = $comment['idtype'];
			updatemoderate($idtype.'_cid', $cid);
			manage_addnotify('verifycommontes');
		}
		C::t('home_comment')->update($comment['cid'], array('message'=>$message, 'status'=>$comment_status));
		showmessage('do_success', dreferer(), array('cid' => $comment['cid']), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
	}

	$comment['message'] = $bbcode->html2bbcode($comment['message']);

} elseif($_GET['op'] == 'delete') {

	if(submitcheck('deletesubmit')) {
		require_once libfile('function/delete');
		if(deletecomments(array($cid))) {
			showmessage('do_success', dreferer(), array('cid' => $cid), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		} else {
			showmessage('no_privilege_comment_del');
		}
	}

} elseif($_GET['op'] == 'reply') {

	if(!$comment = C::t('home_comment')->fetch($cid)) {
		showmessage('comments_do_not_exist');
	}
	if($comment['idtype'] == 'uid' && ($seccodecheck || $secqaacheck)) {
		$seccodecheck = 0;
		$secqaacheck = 0;
	}
	$config = urlencode(getsiteurl().'home.php?mod=misc&ac=swfupload&op=config&doodle=1');
} else {

	showmessage('undefined_action');
}

include template('home/spacecp_comment');

?>