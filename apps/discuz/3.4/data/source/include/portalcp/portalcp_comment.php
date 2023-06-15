<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_comment.php 33715 2013-08-07 01:59:25Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$cid = intval($_GET['cid']);
$comment = array();
if($cid && $_GET['op'] != 'requote') {
	$comment = C::t('portal_comment')->fetch($cid);
}
if($_GET['op'] == 'requote') {

	$aid = $_GET['aid'];
	$article = C::t('portal_article_title')->fetch($aid);

	if($article['idtype'] == 'tid') {
		$comment = C::t('forum_post')->fetch('tid:'.$article['id'], $cid);
		$comment['uid'] = $comment['authorid'];
		$comment['username'] = $comment['author'];
	} elseif($article['idtype'] == 'blogid') {
		$comment = C::t('home_comment')->fetch($cid);
		$comment['uid'] = $comment['authorid'];
		$comment['username'] = $comment['author'];
	} else {
		$comment = C::t('portal_comment')->fetch($cid);
	}
	unset($aid, $article);

	if(!empty($comment['message'])) {

		include_once libfile('class/bbcode');
		$bbcode = & bbcode::instance();
		$comment['message'] = $bbcode->html2bbcode($comment['message']);
		$comment['message'] = preg_replace("/\[quote\].*?\[\/quote\]/is", '', $comment['message']);
		$comment['message'] = getstr($comment['message'], 150, 0, 0, 2, -1);
	}

} elseif($_GET['op'] == 'edit') {

	if(empty($comment)) {
		showmessage('comment_edit_noexist');
	}

	if((!$_G['group']['allowmanagearticle'] && $_G['uid'] != $comment['uid'] && $_G['adminid'] != 1 && $_GET['modarticlecommentkey'] != modauthkey($comment['cid'])) || $_G['groupid'] == '7') {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
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

		C::t('portal_comment')->update($comment['cid'], array('message' => $message, 'status' => $comment_status, 'postip' => $_G['clientip'], 'port' => $_G['remoteport']));

		showmessage('do_success', dreferer());
	}

	include_once libfile('class/bbcode');
	$bbcode = & bbcode::instance();
	$comment['message'] = $bbcode->html2bbcode($comment['message']);

} elseif($_GET['op'] == 'delete') {

	if(empty($comment)) {
		showmessage('comment_delete_noexist');
	}

	if(!$_G['group']['allowmanagearticle'] && $_G['uid'] != $comment['uid']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	if(submitcheck('deletesubmit')) {
		C::t('portal_comment')->delete($cid);
		$idtype = in_array($comment['idtype'], array('aid' ,'topicid')) ? $comment['idtype'] : 'aid';
		$tablename = $idtype == 'aid' ? 'portal_article_count' : 'portal_topic';
		C::t($tablename)->increase($comment[id], array('commentnum' => -1));
		showmessage('do_success', dreferer());
	}

}
list($seccodecheck, $secqaacheck) = seccheck('publish');

if(submitcheck('commentsubmit', 0, $seccodecheck, $secqaacheck)) {

	if(!checkperm('allowcommentarticle')) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	$id = 0;
	$idtype = '';
	if(!empty($_POST['aid'])) {
		$id = intval($_POST['aid']);
		$idtype = 'aid';
	} elseif(!empty($_POST['topicid'])) {
		$id = intval($_POST['topicid']);
		$idtype = 'topicid';
	}


	$message = $_POST['message'];

	require_once libfile('function/spacecp');

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime), array('return' => true));
	}

	$retmessage = addportalarticlecomment($id, $message, $idtype);
	if($retmessage == 'do_success') {
		showmessage('do_success', $_POST['referer'] ? $_POST['referer'] : "portal.php?mod=comment&id=$id&idtype=$idtype");
	} else {
		showmessage($retmessage, dreferer("portal.php?mod=comment&id=$id&idtype=$idtype"));
	}
}

include_once template("portal/portalcp_comment");

?>