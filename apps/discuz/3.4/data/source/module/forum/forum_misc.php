<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_misc.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

require_once libfile('function/post');

$feed = array();
if($_GET['action'] == 'paysucceed') {
	$orderid = trim($_GET['orderid']);
	$url = !empty($orderid) ? 'forum.php?mod=trade&orderid='.$orderid : 'home.php?mod=spacecp&ac=credit';
	showmessage('payonline_succeed', $url);

} elseif($_GET['action'] == 'nav') {

	require_once libfile('misc/forumselect', 'include');
	exit;

} elseif($_GET['action'] == 'attachcredit') {
	if($_GET['formhash'] != FORMHASH || !$_G['uid']) {
		showmessage('undefined_action', NULL);
	}

	$aid = intval($_GET['aid']);

	$attach = C::t('forum_attachment_n')->fetch('aid:'.$aid, $aid);
	$thread = C::t('forum_thread')->fetch_by_tid_displayorder($attach['tid'], 0);

	checklowerlimit('getattach', 0, 1, $thread['fid']);
	$getattachcredits = updatecreditbyaction('getattach', $_G['uid'], array(), '', 1, 1, $thread['fid']);
	$_G['policymsg'] = $p = '';
	if($getattachcredits['updatecredit']) {
		if($getattachcredits['updatecredit']) for($i = 1;$i <= 8;$i++) {
			if($policy = $getattachcredits['extcredits'.$i]) {
				$_G['policymsg'] .= $p.($_G['setting']['extcredits'][$i]['img'] ? $_G['setting']['extcredits'][$i]['img'].' ' : '').$_G['setting']['extcredits'][$i]['title'].' '.$policy.' '.$_G['setting']['extcredits'][$i]['unit'];
				$p = ', ';
			}
		}
	}

	$ck = substr(md5($aid.TIMESTAMP.md5($_G['config']['security']['authkey'])), 0, 8);
	$aidencode = aidencode($aid, 0, $attach['tid']);
	showmessage('attachment_credit', "forum.php?mod=attachment&aid=$aidencode&ck=$ck", array('policymsg' => $_G['policymsg'], 'filename' => $attach['filename']), array('redirectmsg' => 1, 'login' => 1));

} elseif($_GET['action'] == 'attachpay') {
	$aid = intval($_GET['aid']);
	if(!$aid) {
		showmessage('parameters_error');
	} elseif(!isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]])) {
		showmessage('credits_transaction_disabled');
	} elseif(!$_G['uid']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} else {
		$attachtable = !empty($_GET['tid']) ? 'tid:'.dintval($_GET['tid']) : 'aid:'.$aid;
		$attach = C::t('forum_attachment_n')->fetch($attachtable, $aid);
		$attachmember = getuserbyuid($attach['uid']);
		$attach['author'] = $attachmember['username'];
		if($attach['price'] <= 0) {
			showmessage('undefined_action');
		}
	}

	if($attach['readperm'] && $attach['readperm'] > $_G['group']['readaccess']) {
		showmessage('attachment_forum_nopermission', NULL, array(), array('login' => 1));
	}

	$balance = getuserprofile('extcredits'.$_G['setting']['creditstransextra'][1]);
	$status = $balance < $attach['price'] ? 1 : 0;

	if($_G['adminid'] == 3) {
		$fid = C::t('forum_thread')->fetch($attach['tid']);
		$fid = $fid['fid'];
		$ismoderator = C::t('forum_moderator')->fetch_uid_by_fid_uid($fid, $_G['uid']);
	} elseif(in_array($_G['adminid'], array(1, 2))) {
		$ismoderator = 1;
	} else {
		$ismoderator = 0;
	}
	$exemptvalue = $ismoderator ? 64 : 8;
	if($_G['uid'] == $attach['uid'] || $_G['group']['exempt'] & $exemptvalue) {
		$status = 2;
	} else {
		$payrequired = $_G['uid'] ? !C::t('common_credit_log')->count_by_uid_operation_relatedid($_G['uid'], 'BAC', $attach['aid']) : 1;
		$status = $payrequired ? $status : 2;
	}
	$balance = $status != 2 ? $balance - $attach['price'] : $balance;

	$sidauth = rawurlencode(authcode($_G['sid'], 'ENCODE', $_G['authkey']));

	$aidencode = aidencode($aid, 0, $attach['tid']);

	if(C::t('common_credit_log')->count_by_uid_operation_relatedid($_G['uid'], 'BAC', $aid)) {
		showmessage('attachment_yetpay', "forum.php?mod=attachment&aid=$aidencode", array(), array('redirectmsg' => 1));
	}

	$attach['netprice'] = $status != 2 ? round($attach['price'] * (1 - $_G['setting']['creditstax'])) : 0;
	$lockid = 'attachpay_'.$_G['uid'];
	if(!submitcheck('paysubmit')) {
		$post = C::t('forum_post')->fetch('tid:'.$attach['tid'], $attach['pid']);
		if($post['anonymous'] && !$_G['forum']['ismoderator']) {
			$attach['uid'] = 0;
			$attach['author'] = $_G['setting']['anonymoustext'];
		}
		include template('forum/attachpay');
	} elseif(!discuz_process::islocked($lockid)) {
		if(!empty($_GET['buyall'])) {
			$aids = $prices = array();
			$tprice = 0;
			foreach(C::t('forum_attachment_n')->fetch_all_by_id('aid:'.$aid, 'pid', $attach['pid'], '', false, true) as $tmp) {
				$aids[$tmp['aid']] = $tmp['aid'];
				$prices[$tmp['aid']] = $status != 2 ? array($tmp['price'], round($tmp['price'] * (1 - $_G['setting']['creditstax']))) : array(0, 0);
			}
			if($aids) {
				foreach(C::t('common_credit_log')->fetch_all_by_uid_operation_relatedid($_G['uid'], 'BAC', $aids) as $tmp) {
					unset($aids[$tmp['relatedid']]);
				}
			}
			foreach($aids as $aid) {
				$tprice += $prices[$aid][0];
			}
			$status = getuserprofile('extcredits'.$_G['setting']['creditstransextra'][1]) < $tprice ? 1 : 0;
		} else {
			$aids = array($aid);
			$prices[$aid] = $status != 2 ? array($attach['price'], $attach['netprice']) : array(0, 0);
		}

		if($status == 1) {
			discuz_process::unlock($lockid);
			showmessage('credits_balance_insufficient', '', array('title' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'], 'minbalance' => (empty($_GET['buyall']) ? $attach['price'] : $tprice)));
		}
		foreach($aids as $aid) {
			$updateauthor = 1;
			$authorEarn = $prices[$aid][1];
			if($_G['setting']['maxincperthread'] > 0) {
				$extcredit = 'extcredits'.$_G['setting']['creditstransextra'][1];
				$alog = C::t('common_credit_log')->count_credit_by_uid_operation_relatedid($attach['uid'], 'SAC', $aid, $_G['setting']['creditstransextra'][1]);
				if($alog >= $_G['setting']['maxincperthread']) {
					$updateauthor = 0;
				} else {
					$authorEarn = min($_G['setting']['maxincperthread'] - $alog['credit'], $prices[$aid][1]);
				}
			}
			if($updateauthor) {
				updatemembercount($attach['uid'], array($_G['setting']['creditstransextra'][1] => $authorEarn), 1, 'SAC', $aid);
			}
			updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][1] => -$prices[$aid][0]), 1, 'BAC', $aid);

			$aidencode = aidencode($aid, 0, $_GET['tid']);
		}
		discuz_process::unlock($lockid);
		if(defined('IN_MOBILE')) {
			showmessage('attachment_mobile_buy', 'forum.php?mod=redirect&goto=findpost&ptid='.$attach['tid'].'&pid='.$attach['pid']);
		} else {
			if(count($aids) > 1) {
				showmessage('attachment_buyall', 'forum.php?mod=redirect&goto=findpost&ptid='.$attach['tid'].'&pid='.$attach['pid']);
			} else {
				$_G['forum_attach_filename'] = $attach['filename'];
				showmessage('attachment_buy', "forum.php?mod=attachment&aid=$aidencode", array('filename' => $_G['forum_attach_filename']), array('redirectmsg' => 1));
			}
		}
	} else {
		showmessage('attachment_locked');
	}

} elseif($_GET['action'] == 'viewattachpayments') {

	$aid = intval($_GET['aid']);
	$extcreditname = 'extcredits'.$_G['setting']['creditstransextra'][1];

	$loglist = array();
	$logs = C::t('common_credit_log')->fetch_all_by_uid_operation_relatedid(0, 'BAC', $aid);
	$luids = array();
	foreach($logs as $log) {
		$luids[$log['uid']] = $log['uid'];
	}
	$members = C::t('common_member')->fetch_all($luids);
	foreach($logs as $log) {
		$log['username'] = $members[$log['uid']]['username'];
		$log['dateline'] = dgmdate($log['dateline'], 'u');
		$log[$extcreditname] = abs($log[$extcreditname]);
		$loglist[] = $log;
	}
	include template('forum/attachpay_view');

} elseif($_GET['action'] == 'getonlines') {

	$num = C::app()->session->count();
	showmessage($num);

} elseif($_GET['action'] == 'upload') {

	$type = !empty($_GET['type']) && in_array($_GET['type'], array('image', 'file')) ? $_GET['type'] : 'image';
	$attachexts = $imgexts = '';
	$_G['group']['allowpostattach'] = $_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm'])));
	$_G['group']['allowpostimage'] = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostimage']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));
	$_G['group']['attachextensions'] = $_G['forum']['attachextensions'] ? $_G['forum']['attachextensions'] : $_G['group']['attachextensions'];
	if($_G['group']['attachextensions']) {
		$imgexts = explode(',', str_replace(' ', '', $_G['group']['attachextensions']));
		$imgexts = array_intersect(array('jpg','jpeg','gif','png','bmp'), $imgexts);
		$imgexts = implode(', ', $imgexts);
	} else {
		$imgexts = 'jpg, jpeg, gif, png, bmp';
	}
	if($type == 'image' && (!$_G['group']['allowpostimage'] || !$imgexts)) {
		showmessage('no_privilege_postimage');
	}
	if($type == 'file' && !$_G['group']['allowpostattach']) {
		showmessage('no_privilege_postattach');
	}
	include template('forum/upload');

} elseif($_GET['action'] == 'comment') {

	if(!$_G['setting']['commentnumber']) {
		showmessage('postcomment_closed');
	}
	$thread = C::t('forum_thread')->fetch($_GET['tid']);
	if($thread['closed'] && !$_G['forum']['ismoderator']) {
		showmessage('thread_closed');
	}
	$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['pid']);
	if($_G['group']['allowcommentitem'] && !empty($_G['uid']) && $post['authorid'] != $_G['uid']) {
		$thread = C::t('forum_thread')->fetch($post['tid']);
		$itemi = $thread['special'];
		if($thread['special'] > 0) {
			if($thread['special'] == 2){
				$thread['special'] = $post['first'] || C::t('forum_trade')->check_goods($post['pid']) ? 2 : 0;
			} elseif($thread['special'] == 127) {
				$thread['special'] = $_GET['special'];
			} else {
				$thread['special'] = $post['first'] ? $thread['special'] : 0;
			}
		}
		$_G['setting']['commentitem'] = $_G['setting']['commentitem'][$thread['special']];
		if($thread['special'] == 0) {
			loadcache('forums');
			if($_G['cache']['forums'][$post['fid']]['commentitem']) {
				$_G['setting']['commentitem'] = $_G['cache']['forums'][$post['fid']]['commentitem'];
			}
		}
		if($_G['setting']['commentitem'] && !C::t('forum_postcomment')->count_by_pid($_GET['pid'], $_G['uid'], 1)) {
			$commentitem = explode("\n", $_G['setting']['commentitem']);
		}
	}
	if(!$post || !($_G['setting']['commentpostself'] || $post['authorid'] != $_G['uid']) || !(($post['first'] && $_G['setting']['commentfirstpost'] && in_array($_G['group']['allowcommentpost'], array(1, 3)) || (!$post['first'] && in_array($_G['group']['allowcommentpost'], array(2, 3)))))) {
		showmessage('postcomment_error');
	}
	$extra = !empty($_GET['extra']) ? rawurlencode($_GET['extra']) : '';
	list($seccodecheck, $secqaacheck) = seccheck('post', 'reply');

	include template('forum/comment');

} elseif($_GET['action'] == 'commentmore') {

	function forum_misc_commentmore_callback_1($matches, $action = 0) {
		static $cic = 0;

		if($action == 1) {
			$cic = $matches;
		} else {
			return '<i class="cmstarv" style="background-position:20px -'.(intval($matches[1]) * 16).'px">'.sprintf('%1.1f', $matches[1]).'</i>'.($cic++ % 2 ? '<br />' : '');
		}
	}

	if(!$_G['setting']['commentnumber'] || !$_G['inajax']) {
		showmessage('postcomment_closed');
	}
	require_once libfile('function/discuzcode');
	$commentlimit = intval($_G['setting']['commentnumber']);
	$page = max(1, $_G['page']);
	$start_limit = ($page - 1) * $commentlimit;
	$comments = array();
	foreach(C::t('forum_postcomment')->fetch_all_by_search(null, $_GET['pid'], null, null, null, null, null, $start_limit, $commentlimit) as $comment) {
		$comment['avatar'] = avatar($comment['authorid'], 'small');
		$comment['dateline'] = dgmdate($comment['dateline'], 'u');
		$comment['comment'] = str_replace(array('[b]', '[/b]', '[/color]'), array('<b>', '</b>', '</font>'), preg_replace("/\[color=([#\w]+?)\]/i", "<font color=\"\\1\">", $comment['comment']));
		$comments[] = $comment;
	}
	forum_misc_commentmore_callback_1(0, 1);
	$totalcomment = C::t('forum_postcomment')->fetch_standpoint_by_pid($_GET['pid']);
	$totalcomment = $totalcomment['comment'];
	$totalcomment = preg_replace_callback('/<i>([\.\d]+)<\/i>/', 'forum_misc_commentmore_callback_1', $totalcomment);
	$count = C::t('forum_postcomment')->count_by_search(null, $_GET['pid']);
	$multi = multi($count, $commentlimit, $page, "forum.php?mod=misc&action=commentmore&tid=$_G[tid]&pid=$_GET[pid]");
	include template('forum/comment_more');

} elseif($_GET['action'] == 'postappend') {

	if(!$_G['setting']['postappend']) {
		showmessage('postappend_not_open');
	}

	$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['pid']);
	if($post['authorid'] != $_G['uid']) {
		showmessage('postappend_only_yourself');
	}
	if(submitcheck('postappendsubmit')) {
		$message = censor($_GET['postappendmessage']);
		$sppos = 0;
		if($post['first'] && strexists($post['message'], chr(0).chr(0).chr(0))) {
			$sppos = strpos($post['message'], chr(0).chr(0).chr(0));
			$specialextra = substr($post['message'], $sppos + 3);
			$post['message'] = substr($post['message'], 0, $sppos);
		}
		$message = $post['message'] . "\n\n[b]".lang('forum/misc', 'postappend_content')." (".dgmdate(TIMESTAMP)."):[/b]\n$message";
		if($sppos) {
			$message .= chr(0).chr(0).chr(0).$specialextra;
		}
		require_once libfile('function/post');
		$bbcodeoff = checkbbcodes($message, 0);
		C::t('forum_post')->update('tid:'.$_G['tid'], $_GET['pid'], array(
			'message' => $message,
			'bbcodeoff' => $bbcodeoff,
			'port' => $_G['remoteport']
		));
		showmessage('postappend_add_succeed', "forum.php?mod=viewthread&tid=$post[tid]&pid=$post[pid]&page=$_GET[page]&extra=$_GET[extra]#pid$post[pid]", array('tid' => $post['tid'], 'pid' => $post['pid']));
	} else {
		include template('forum/postappend');
	}

} elseif($_GET['action'] == 'pubsave') {

	$return = threadpubsave($_G['tid']);
	if($return > 0) {
		showmessage('post_newthread_succeed', dreferer(), array('coverimg' => ''));
	} elseif($return == -1) {
		showmessage('post_newthread_mod_succeed', dreferer(), array('coverimg' => ''));
	} elseif($return == -2) {
		showmessage('post_reply_mod_succeed', dreferer());
	} else {
		showmessage('thread_nonexistence');
	}

} elseif($_GET['action'] == 'loadsave') {

	$message = '&nbsp;';
	$savepost = C::t('forum_post')->fetch(0, $_GET['pid']);
	if($savepost && $_G['uid'] == $savepost['authorid']) {
		$message = $savepost['message'];
		if($_GET['type']) {
			require_once libfile('function/discuzcode');
			$message = discuzcode($message, $savepost['smileyoff'], $savepost['bbcodeoff'], $savepost['htmlon']);
		}
		$message = $message ? $message : '&nbsp;';
	}
	include template('common/header_ajax');
	echo $message;
	include template('common/footer_ajax');
	exit;

} elseif($_GET['action'] == 'replynotice') {
	$tid = intval($_GET['tid']);
	$status = $_GET['op'] == 'ignore' ? 0 : 1;
	if(!empty($tid)) {
		$thread = C::t('forum_thread')->fetch_by_tid_displayorder($tid, 0);
		if($thread['authorid'] == $_G['uid']) {
			$thread['status'] = setstatus(6, $status, $thread['status']);
			C::t('forum_thread')->update($tid, array('status'=>$thread['status']), true);
			showmessage('replynotice_success_'.$status);
		}
	}
	showmessage('replynotice_error', 'forum.php?mod=viewthread&tid='.$tid);

} elseif($_GET['action'] == 'removeindexheats') {

	if($_G['adminid'] != 1) {
		showmessage('no_privilege_indexheats');
	}
	C::t('forum_thread')->update($_G['tid'], array('heats'=>0));
	require_once libfile('function/cache');
	updatecache('heats');
	dheader('Location: '.dreferer());

} elseif($_GET['action'] == 'showdarkroom') {

	include_once libfile('class/member');
	if($_G['setting']['darkroom']) {
		$limit = $_G['tpp'];
		$cid = $_GET['cid'] ? dintval($_GET['cid']) : 0;
		$crimelist = array();
		$i = 0;
		foreach(C::t('common_member_crime')->fetch_all_by_cid($cid, array(4, 5), $limit) as $crime) {
			$i++;
			$cid = $crime['cid'];
			if(isset($crimelist[$crime['uid']])) {
				continue;
			}
			$crime['action'] = lang('forum/template', crime_action_ctl::$actions[$crime['action']]);
			$crime['dateline'] = dgmdate($crime['dateline'], 'u');
			$crimelist[$crime['uid']] = $crime;
		}
		if($crimelist && $i == $limit) {
			$dataexist = 1;
		} else {
			$dataexist = 0;
		}
		foreach(C::t('common_member')->fetch_all(array_keys($crimelist)) as $uid => $user) {
			if($user['groupid'] == 4 || $user['groupid'] == 5) {
				$crimelist[$uid]['username'] = $user['username'];
				$crimelist[$uid]['groupexpiry'] = $user['groupexpiry'] ? dgmdate($user['groupexpiry'], 'u') : lang('forum/misc', 'never_expired');
			} else {
				unset($crimelist[$uid]);
			}
		}
		if($_GET['ajaxdata'] === 'json') {
			showmessage($dataexist.'|'.$cid, '', $crimelist);
		} else {
			include_once template("forum/darkroom");
		}
		exit;
	}
	showmessage('undefined_action');
} elseif($_GET['action'] == 'shortcut') {

	if($_GET['type'] == 'ico') {
		$shortcut = @readfile(DISCUZ_ROOT.'favicon.ico');
		$filename = 'favicon.ico';
	} else {
		$shortcut = '[InternetShortcut]
URL='.$_G['siteurl'].'
IconFile='.$_G['siteurl'].'favicon.ico
IconIndex=1
';
		$filename = $_G['setting']['bbname'].'.url';
	}

	$filenameencode = strtolower(CHARSET) == 'utf-8' ? rawurlencode($filename) : rawurlencode(diconv($filename, CHARSET, 'UTF-8'));
	$rfc6266blacklist = strexists($_SERVER['HTTP_USER_AGENT'], 'UCBrowser') || strexists($_SERVER['HTTP_USER_AGENT'], 'Quark') || strexists($_SERVER['HTTP_USER_AGENT'], 'SogouM') || strexists($_SERVER['HTTP_USER_AGENT'], 'baidu');
	dheader('Content-type: application/octet-stream');
	dheader('Content-Disposition: attachment; filename="'.$filenameencode.'"'.(($filename == $filenameencode || $rfc6266blacklist) ? '' : '; filename*=utf-8\'\''.$filenameencode));
	echo $shortcut;
	exit;
} elseif($_GET['action'] == 'livelastpost') {
	$fid = dintval($_GET['fid']);
	$forum = C::t('forum_forumfield')->fetch($fid);
	$livetid = $forum['livetid'];
	$postlist = array();
	if($livetid) {
		$thread = C::t('forum_thread')->fetch($livetid);
		$postlist['count'] = $thread['replies'];
		$postarr = C::t('forum_post')->fetch_all_by_tid('tid:'.$livetid, $livetid, true, 'DESC', 20);
		ksort($postarr);
		foreach($postarr as $post) {
			if($post['first'] == 1 || getstatus($post['status'], 1)) {
				continue;
			}
			$contentarr = array(
				'authorid' => !$post['anonymous'] ? $post['authorid'] : '',
				'author' => !$post['anonymous'] ? $post['author'] : lang('forum/misc', 'anonymous'),
				'message' => str_replace("\r\n", '<br>', messagecutstr($post['message'])),
				'dateline' => dgmdate($post['dateline'], 'u'),
				'avatar' => !$post['anonymous'] ? avatar($post['authorid'], 'small') : '',
			);
			$postlist['list'][$post['pid']] = $contentarr;
		}
	}

	showmessage('', '', $postlist);
	exit;

} else {

	if(empty($_G['forum']['allowview'])) {
		if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
			showmessage('forum_nopermission', NULL, array($_G['group']['grouptitle']), array('login' => 1));
		}
	}

	$thread = C::t('forum_thread')->fetch($_G['tid']);
	if(!($thread['displayorder']>=0 || $thread['displayorder']==-4 && $thread['authorid']==$_G['uid'])) {
		$thread = array();
	}
	if($thread['readperm'] && $thread['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $thread['authorid'] != $_G['uid']) {
		showmessage('thread_nopermission', NULL, array('readperm' => $thread['readperm']), array('login' => 1));
	}

	if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
		showmessage('forum_passwd', "forum.php?mod=forumdisplay&fid=$_G[fid]");
	}


	if(!$thread) {
		showmessage('thread_nonexistence');
	}

	if($_G['forum']['type'] == 'forum') {
		$navigation = '<a href="forum.php">'.$_G['setting']['navs'][2]['navname']."</a> <em>&rsaquo;</em> <a href=\"forum.php?mod=forumdisplay&fid=$_G[fid]\">".$_G['forum']['name']."</a> <em>&rsaquo;</em> <a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a> ";
		$navtitle = strip_tags($_G['forum']['name']).' - '.$thread['subject'];
	} elseif($_G['forum']['type'] == 'sub') {
		$fup = C::t('forum_forum')->fetch($_G['forum']['fup']);
		$navigation = '<a href="forum.php">'.$_G['setting']['navs'][2]['navname']."</a> <em>&rsaquo;</em> <a href=\"forum.php?mod=forumdisplay&fid=$fup[fid]\">$fup[name]</a> &raquo; <a href=\"forum.php?mod=forumdisplay&fid=$_G[fid]\">".$_G['forum']['name']."</a> <em>&rsaquo;</em> <a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a> ";
		$navtitle = strip_tags($fup['name']).' - '.strip_tags($_G['forum']['name']).' - '.$thread['subject'];
	}

}

if($_GET['action'] == 'votepoll' && submitcheck('pollsubmit', 1)) {

	if(!$_G['group']['allowvote']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif(!empty($thread['closed'])) {
		showmessage('thread_poll_closed', NULL, array(), array('login' => 1));
	} elseif(empty($_GET['pollanswers'])) {
		showmessage('thread_poll_invalid', NULL, array(), array('login' => 1));
	}

	$pollarray = C::t('forum_poll')->fetch($_G['tid']);
	$overt = $pollarray['overt'];
	if(!$pollarray) {
		showmessage('poll_not_found');
	} elseif($pollarray['expiration'] && $pollarray['expiration'] < TIMESTAMP) {
		showmessage('poll_overdue', NULL, array(), array('login' => 1));
	} elseif($pollarray['maxchoices'] && $pollarray['maxchoices'] < count($_GET['pollanswers'])) {
		showmessage('poll_choose_most', NULL, array('maxchoices' => $pollarray['maxchoices']), array('login' => 1));
	}

	$voterids = $_G['uid'] ? $_G['uid'] : $_G['clientip'];

	$polloptionid = array();
	$query = C::t('forum_polloption')->fetch_all_by_tid($_G['tid']);
	foreach($query as $pollarray) {
		if(strexists("\t".$pollarray['voterids']."\t", "\t".$voterids."\t")) {
			showmessage('thread_poll_voted', NULL, array(), array('login' => 1));
		}
		$polloptionid[] = $pollarray['polloptionid'];
	}

	$polloptionids = array();
	foreach($_GET['pollanswers'] as $key => $id) {
		if(!in_array($id, $polloptionid)) {
			showmessage('parameters_error');
		}
		unset($polloptionid[$key]);
		$polloptionids[] = $id;
	}

	C::t('forum_polloption')->update_vote($polloptionids, $voterids."\t", 1);
	C::t('forum_thread')->update($_G['tid'], array('lastpost'=>$_G['timestamp']), true);
	C::t('forum_poll')->update_vote($_G['tid']);
	C::t('forum_pollvoter')->insert(array(
		'tid' => $_G['tid'],
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'options' => implode("\t", $_GET['pollanswers']),
		'dateline' => $_G['timestamp'],
		));
	updatecreditbyaction('joinpoll');

	$space = array();
	space_merge($space, 'field_home');

	if($overt && !empty($space['privacy']['feed']['newreply'])) {
		$feed['icon'] = 'poll';
		$feed['title_template'] = 'feed_thread_votepoll_title';
		$feed['title_data'] = array(
			'subject' => "<a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
			'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>",
			'hash_data' => "tid{$_G[tid]}"
		);
		$feed['id'] = $_G['tid'];
		$feed['idtype'] = 'tid';
		postfeed($feed);
	}

	if(!empty($_G['inajax'])) {
		showmessage('thread_poll_succeed', "forum.php?mod=viewthread&tid=$_G[tid]".($_GET['from'] ? '&from='.$_GET['from'] : ''), array(), array('location' => true));
	} else {
		showmessage('thread_poll_succeed', "forum.php?mod=viewthread&tid=$_G[tid]".($_GET['from'] ? '&from='.$_GET['from'] : ''));
	}

} elseif($_GET['action'] == 'viewvote') {
	if($_G[forum_thread][special] != 1) {
		showmessage('thread_poll_none');
	}
	require_once libfile('function/post');
	$polloptionid = is_numeric($_GET['polloptionid']) ? $_GET['polloptionid'] : '';

	$page = intval($_GET['page']) ? intval($_GET['page']) : 1;
	$perpage = 100;
	$pollinfo = C::t('forum_poll')->fetch($_G['tid']);
	$overt = $pollinfo['overt'];

	$polloptions = array();
	$query = C::t('forum_polloption')->fetch_all_by_tid($_G['tid']);
	foreach($query as $options) {
		if(empty($polloptionid)) {
			$polloptionid = $options['polloptionid'];
		}
		$options['polloption'] = preg_replace("/\[url=(https?){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i",
			"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $options['polloption']);
		$polloptions[] = $options;
	}

	$arrvoterids = array();
	if($overt || $_G['adminid'] == 1 || $thread['authorid'] == $_G['uid']) {
		$polloptioninfo = C::t('forum_polloption')->fetch($polloptionid);
		$voterids = $polloptioninfo['voterids'];
		$arrvoterids = explode("\t", trim($voterids));
	} else {
		showmessage('thread_poll_nopermission');
	}

	if(!empty($arrvoterids)) {
		$count = count($arrvoterids);
		$multi = $perpage * ($page - 1);
		$multipage = multi($count, $perpage, $page, "forum.php?mod=misc&action=viewvote&tid=$_G[tid]&polloptionid=$polloptionid".( $_GET[handlekey] ? "&handlekey=".$_GET[handlekey] : '' ));
		$arrvoterids = array_slice($arrvoterids, $multi, $perpage);
	}
	$voterlist = $voter = array();
	if($arrvoterids) {
		$voterlist = C::t('common_member')->fetch_all($arrvoterids);
	}
	include template('forum/viewthread_poll_voter');

} elseif($_GET['action'] == 'rate' && $_GET['pid']) {

	$_GET['tid'] = dintval($_GET['tid']);
	$_GET['pid'] = dintval($_GET['pid']);

	if($_GET['showratetip']) {
		include template('forum/rate');
		exit();
	}

	if(!$_G['inajax']) {
		showmessage('undefined_action');
	}
	if(!$_G['group']['raterange']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif($_G['setting']['modratelimit'] && $_G['adminid'] == 3 && !$_G['forum']['ismoderator']) {
		showmessage('thread_rate_moderator_invalid', NULL);
	}
	$reasonpmcheck = $_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3 ? 'checked="checked" disabled' : '';
	if(($_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3) || !empty($_GET['sendreasonpm'])) {
		$forumname = strip_tags($_G['forum']['name']);
		$sendreasonpm = 1;
	} else {
		$sendreasonpm = 0;
	}

	$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['pid']);
	if($post['invisible'] != 0 || $post['authorid'] == 0) {
		$post = array();
	}

	if(!$post || $post['tid'] != $thread['tid'] || !$post['authorid']) {
		showmessage('rate_post_error');
	} elseif(!$_G['forum']['ismoderator'] && $_G['setting']['karmaratelimit'] && TIMESTAMP - $post['dateline'] > $_G['setting']['karmaratelimit'] * 3600) {
		showmessage('thread_rate_timelimit', NULL, array('karmaratelimit' => $_G['setting']['karmaratelimit']));
	} elseif($post['authorid'] == $_G['uid'] || $post['tid'] != $_G['tid']) {
		showmessage('thread_rate_member_invalid', NULL);
	} elseif($post['anonymous']) {
		showmessage('thread_rate_anonymous', NULL);
	} elseif($post['status'] & 1) {
		showmessage('thread_rate_banned', NULL);
	}

	$allowrate = TRUE;
	if(!$_G['setting']['dupkarmarate']) {
		if(C::t('forum_ratelog')->count_by_uid_pid($_G['uid'], $_GET['pid'])) {
			showmessage('thread_rate_duplicate', NULL);
		}
	}

	$page = intval($_GET['page']);

	require_once libfile('function/misc');

	$maxratetoday = getratingleft($_G['group']['raterange']);

	if(!submitcheck('ratesubmit')) {
		$referer = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page.($_GET['from'] ? '&from='.$_GET['from'] : '').'#pid'.$_GET['pid'];
		$ratelist = getratelist($_G['group']['raterange']);
		include template('forum/rate');

	} else {
		if($_G['setting']['submitlock'] && discuz_process::islocked('ratelock_'.$_G['uid'].'_'.$_GET['pid'], 0, 1)){
			showmessage('thread_rate_locked');
		}

		$reason = checkreasonpm();
		$rate = $ratetimes = 0;
		$creditsarray = $sub_self_credit = array();
		getuserprofile('extcredits1');
		foreach($_G['group']['raterange'] as $id => $rating) {
			$score = intval($_GET['score'.$id]);
			if(isset($_G['setting']['extcredits'][$id]) && !empty($score)) {
				if($rating['isself'] && (intval($_G['member']['extcredits'.$id]) - $score < 0)) {
					showmessage('thread_rate_range_self_invalid', '', array('extcreditstitle' => $_G['setting']['extcredits'][$id]['title']));
				}
				if(abs($score) <= $maxratetoday[$id]) {
					if($score > $rating['max'] || $score < $rating['min']) {
						showmessage('thread_rate_range_invalid');
					} else {
						$creditsarray[$id] = $score;
						if($rating['isself']) {
							$sub_self_credit[$id] = -abs($score);
						}
						$rate += $score;
						$ratetimes += ceil(max(abs($rating['min']), abs($rating['max'])) / 5);
					}
				} else {
					showmessage('thread_rate_ctrl');
				}
			}
		}

		if(!$creditsarray) {
			showmessage('thread_rate_range_invalid', NULL);
		}

		updatemembercount($post['authorid'], $creditsarray, 1, 'PRC', $_GET['pid']);

		if(!empty($sub_self_credit)) {
			updatemembercount($_G['uid'], $sub_self_credit, 1, 'RSC', $_GET['pid']);
		}
		C::t('forum_post')->increase_rate_by_pid('tid:'.$_G['tid'], $_GET['pid'], $rate, $ratetimes);
		if($post['first']) {
			$threadrate = intval(@($post['rate'] + $rate) / abs($post['rate'] + $rate));
			C::t('forum_thread')->update($_G['tid'], array('rate'=>$threadrate));

		}

		require_once libfile('function/discuzcode');
		$sqlvalues = $comma = '';
		$sqlreason = censor(trim($_GET['reason']));
		$sqlreason = cutstr(dhtmlspecialchars($sqlreason), 40, '.');
		foreach($creditsarray as $id => $addcredits) {
			$insertarr = array(
				'pid' => $_GET['pid'],
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'extcredits' => $id,
				'dateline' => $_G['timestamp'],
				'score' => $addcredits,
				'reason' => $sqlreason
			);
			C::t('forum_ratelog')->insert($insertarr);
		}

		include_once libfile('function/post');
		$_G['forum']['threadcaches'] && @deletethreadcaches($_G['tid']);

		$reason = dhtmlspecialchars(censor(trim($reason)));
		if($sendreasonpm) {
			$ratescore = $slash = '';
			foreach($creditsarray as $id => $addcredits) {
				$ratescore .= $slash.$_G['setting']['extcredits'][$id]['title'].' '.($addcredits > 0 ? '+'.$addcredits : $addcredits).' '.$_G['setting']['extcredits'][$id]['unit'];
				$slash = ' / ';
			}
			sendreasonpm($post, 'rate_reason', array(
				'tid' => $thread['tid'],
				'pid' => $_GET['pid'],
				'subject' => $thread['subject'],
				'ratescore' => $ratescore,
				'reason' => $reason,
				'from_id' => 0,
				'from_idtype' => 'rate'
			));
		}

		$logs = array();
		foreach($creditsarray as $id => $addcredits) {
			$logs[] = dhtmlspecialchars("$_G[timestamp]\t{$_G[member][username]}\t$_G[adminid]\t$post[author]\t$id\t$addcredits\t$_G[tid]\t$thread[subject]\t$reason");
		}
		update_threadpartake($post['tid']);
		C::t('forum_postcache')->delete($_GET['pid']);
		writelog('ratelog', $logs);

		showmessage('thread_rate_succeed', dreferer());
	}
} elseif($_GET['action'] == 'removerate' && $_GET['pid']) {

	if(!$_G['forum']['ismoderator'] || !$_G['group']['raterange']) {
		showmessage('no_privilege_removerate');
	}

	$reasonpmcheck = $_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3 ? 'checked="checked" disabled' : '';
	if(($_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3) || !empty($_GET['sendreasonpm'])) {
		$forumname = strip_tags($_G['forum']['name']);
		$sendreasonpm = 1;
	} else {
		$sendreasonpm = 0;
	}

	foreach($_G['group']['raterange'] as $id => $rating) {
		$maxratetoday[$id] = $rating['mrpd'];
	}
	$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['pid']);
	if($post['invisible'] != 0 || $post['authorid'] == 0) {
		$post = array();
	}

	if(!$post || $post['tid'] != $thread['tid'] || !$post['authorid']) {
		showmessage('rate_post_error');
	}

	require_once libfile('function/misc');

	if(!submitcheck('ratesubmit')) {

		$referer = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page.($_GET['from'] ? '&from='.$_GET['from'] : '').'#pid'.$_GET['pid'];
		$ratelogs = array();

		foreach(C::t('forum_ratelog')->fetch_all_by_pid($_GET['pid'], 'ASC') as $ratelog) {
			$ratelog['dbdateline'] = $ratelog['dateline'];
			$ratelog['dateline'] = dgmdate($ratelog['dateline'], 'u');
			$ratelog['scoreview'] = $ratelog['score'] > 0 ? '+'.$ratelog['score'] : $ratelog['score'];
			$ratelogs[] = $ratelog;
		}

		include template('forum/rate');

	} else {

		$reason = checkreasonpm();

		if(!empty($_GET['logidarray'])) {
			if($sendreasonpm) {
				$ratescore = $slash = '';
			}

			$rate = $ratetimes = 0;
			$logs = array();
			foreach(C::t('forum_ratelog')->fetch_all_by_pid($_GET['pid']) as $ratelog) {
				if(in_array($ratelog['uid'].' '.$ratelog['extcredits'].' '.$ratelog['dateline'], $_GET['logidarray'])) {
					$rate += $ratelog['score'] = -$ratelog['score'];
					$ratetimes += ceil(max(abs($rating['min']), abs($rating['max'])) / 5);
					updatemembercount($post['authorid'], array($ratelog['extcredits'] => $ratelog['score']));
					C::t('common_credit_log')->delete_by_uid_operation_relatedid($post['authorid'], 'PRC', $_GET['pid']);
					C::t('forum_ratelog')->delete_by_pid_uid_extcredits_dateline($_GET['pid'], $ratelog['uid'], $ratelog['extcredits'], $ratelog['dateline']);
					$logs[] = dhtmlspecialchars("$_G[timestamp]\t{$_G[member][username]}\t$_G[adminid]\t$ratelog[username]\t$ratelog[extcredits]\t$ratelog[score]\t$_G[tid]\t$thread[subject]\t$reason\tD");
					if($sendreasonpm) {
						$ratescore .= $slash.$_G['setting']['extcredits'][$ratelog['extcredits']]['title'].' '.($ratelog['score'] > 0 ? '+'.$ratelog['score'] : $ratelog['score']).' '.$_G['setting']['extcredits'][$ratelog['extcredits']]['unit'];
						$slash = ' / ';
					}
				}
			}
			C::t('forum_postcache')->delete($_GET['pid']);
			writelog('ratelog', $logs);

			if($sendreasonpm) {
				sendreasonpm($post, 'rate_removereason', array(
					'tid' => $thread['tid'],
					'pid' => $_GET['pid'],
					'subject' => $thread['subject'],
					'ratescore' => $ratescore,
					'reason' => $reason,
					'from_id' => 0,
					'from_idtype' => 'removerate'
				));
			}
			C::t('forum_post')->increase_rate_by_pid('tid:'.$_G['tid'], $_GET['pid'], $rate, $ratetimes);
			if($post['first']) {
				$threadrate = @intval(@($post['rate'] + $rate) / abs($post['rate'] + $rate));
				C::t('forum_thread')->update($_G['tid'], array('rate'=>$threadrate));
			}

		}

		showmessage('thread_rate_removesucceed', dreferer());

	}

} elseif($_GET['action'] == 'viewratings' && $_GET['pid']) {

	$loglist = $logcount = array();

	$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['pid']);
	if($post['invisible'] != 0) {
		$post = array();
	}
	if($post) {
		$loglist = C::t('forum_ratelog')->fetch_all_by_pid($_GET['pid']);
	}
	if(empty($post) || empty($loglist)) {
		showmessage('thread_rate_log_nonexistence');
	}
	if($post['tid'] != $thread['tid']) {
		showmessage('targetpost_donotbelongto_thisthread');
	}
	if($_G['setting']['bannedmessages']) {
		$postmember = getuserbyuid($post['authorid']);
		$post['groupid'] = $postmember['groupid'];
	}

	foreach($loglist as $k => $log) {
		$logcount[$log['extcredits']] += $log['score'];
		$log['dateline'] = dgmdate($log['dateline'], 'u');
		$log['score'] = $log['score'] > 0 ? '+'.$log['score'] : $log['score'];
		$log['reason'] = dhtmlspecialchars($log['reason']);
		$loglist[$k] = $log;
	}

	include template('forum/rate_view');

} elseif($_GET['action'] == 'viewwarning' && $_GET['uid']) {

	$warnuser = getuserbyuid($_GET['uid']);
	$warnuser = $warnuser['username'];
	if(!$warnuser) {
		showmessage('member_no_found');
	}

	$warnings = array();
	$warnings = C::t('forum_warning')->fetch_all_by_authorid($_GET['uid']);

	if(!$warnings) {
		showmessage('thread_warning_nonexistence');
	}

	foreach($warnings as $key => $warning) {
		$warning['dateline'] = dgmdate($warning['dateline'], 'u');
		$warning['reason'] = dhtmlspecialchars($warning['reason']);
		$warnings[$key] = $warning;
	}
	$warnnum = count($warnings);

	include template('forum/warn_view');

} elseif($_GET['action'] == 'pay') {

	if(!isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]])) {
		showmessage('credits_transaction_disabled');
	} elseif($thread['price'] <= 0 || $thread['special'] <> 0) {
		showmessage('thread_pay_error', NULL);
	} elseif(!$_G['uid']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	if(($balance = getuserprofile('extcredits'.$_G['setting']['creditstransextra'][1]) - $thread['price']) < ($minbalance = 0)) {
		if($_G['setting']['creditstrans'][0] == $_G['setting']['creditstransextra'][1]) {
			showmessage('credits_balance_insufficient_and_charge', '', array('title' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'], 'minbalance' => $thread['price']));
		} else {
			showmessage('credits_balance_insufficient', '', array('title' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'], 'minbalance' => $thread['price']));
		}
	}

	if(C::t('common_credit_log')->count_by_uid_operation_relatedid($_G['uid'], 'BTC', $_G['tid'])) {
		showmessage('credits_buy_thread', 'forum.php?mod=viewthread&tid='.$_G['tid'].($_GET['from'] ? '&from='.$_GET['from'] : ''));
	}

	$thread['netprice'] = floor($thread['price'] * (1 - $_G['setting']['creditstax']));

	if(!submitcheck('paysubmit')) {

		if(empty($thread['author'])) {
			if($_G['forum']['ismoderator']) {
				$authorinfo = getuserbyuid($thread['authorid']);
				$thread['author'] = $authorinfo['username'];
			} else {
				$thread['authorid'] = 0;
				$thread['author'] = $_G['setting']['anonymoustext'];
			}
		}
		include template('forum/pay');

	} else {

		$updateauthor = true;
		$authorEarn = $thread['netprice'];
		if($_G['setting']['maxincperthread'] > 0) {
			$extcredit = 'extcredits'.$_G['setting']['creditstransextra'][1];
			$log = C::t('common_credit_log')->count_credit_by_uid_operation_relatedid($thread['authorid'], 'STC', $_G['tid'], $_G['setting']['creditstransextra'][1]);
			if($log >= $_G['setting']['maxincperthread']) {
				$updateauthor = false;
			} else {
				$authorEarn = min($_G['setting']['maxincperthread'] - $log['credit'], $thread['netprice']);
			}
		}
		if($updateauthor) {
			updatemembercount($thread['authorid'], array($_G['setting']['creditstransextra'][1] => $authorEarn), 1, 'STC', $_G['tid']);
		}
		updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][1] => -$thread['price']), 1, 'BTC', $_G['tid']);

		showmessage('thread_pay_succeed', "forum.php?mod=viewthread&tid=$_G[tid]".($_GET['from'] ? '&from='.$_GET['from'] : ''));

	}

} elseif($_GET['action'] == 'viewpayments') {
	$extcreditname = 'extcredits'.$_G['setting']['creditstransextra'][1];
	$loglist = array();
	$logs = C::t('common_credit_log')->fetch_all_by_uid_operation_relatedid(0, 'BTC', $_G['tid']);
	$luids = array();
	foreach($logs as $log) {
		$luids[$log['uid']] = $log['uid'];
	}
	$members = C::t('common_member')->fetch_all($luids);
	foreach($logs as $log) {
		$log['username'] = $members[$log['uid']]['username'];
		$log['dateline'] = dgmdate($log['dateline'], 'u');
		$log[$extcreditname] = abs($log[$extcreditname]);
		$loglist[] = $log;
	}
	include template('forum/pay_view');

} elseif($_GET['action'] == 'viewthreadmod' && $_G['tid']) {

	$modactioncode = lang('forum/modaction');
	$loglist = array();

	foreach(C::t('forum_threadmod')->fetch_all_by_tid($_G['tid']) as $log) {
		$log['dateline'] = dgmdate($log['dateline'], 'u');
		$log['expiration'] = !empty($log['expiration']) ? dgmdate($log['expiration'], 'dt') : '';
		$log['status'] = empty($log['status']) ? 'style="text-decoration: line-through" disabled' : '';
		if(!$modactioncode[$log['action']] && preg_match('/S(\d\d)/', $log['action'], $a) || $log['action'] == 'SPA') {
			loadcache('stamps');
			if($log['action'] == 'SPA') {
				$log['action'] = 'SPA'.$log['stamp'];
				$stampid = $log['stamp'];
			} else {
				$stampid = intval($a[1]);
			}
			$modactioncode[$log['action']] = $modactioncode['SPA'].' '.$_G['cache']['stamps'][$stampid]['text'];
		} elseif(preg_match('/L(\d\d)/', $log['action'], $a)) {
			loadcache('stamps');
			$modactioncode[$log['action']] = $modactioncode['SLA'].' '.$_G['cache']['stamps'][intval($a[1])]['text'];
		}
		if($log['magicid']) {
			loadcache('magics');
			$log['magicname'] = $_G['cache']['magics'][$log['magicid']]['name'];
		}
		$loglist[] = $log;
	}

	if(empty($loglist)) {
		showmessage('threadmod_nonexistence');
	}

	$reasons_public = $_G['setting']['modreasons_public'];

	include template('forum/viewthread_mod');

} elseif($_GET['action'] == 'bestanswer' && $_G['tid'] && $_GET['pid'] && submitcheck('bestanswersubmit')) {

	$forward = 'forum.php?mod=viewthread&tid='.$_G['tid'].($_GET['from'] ? '&from='.$_GET['from'] : '');
	$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['pid'], false);
	if($post['tid'] != $_G['tid']) {
		$post = array();
	}

	if(!($thread['special'] == 3 && $post && ($_G['forum']['ismoderator'] && (!$_G['setting']['rewardexpiration'] || $_G['setting']['rewardexpiration'] > 0 && ($_G['timestamp'] - $thread['dateline']) / 86400 > $_G['setting']['rewardexpiration']) || $thread['authorid'] == $_G['uid']) && $post['authorid'] != $thread['authorid'] && $post['first'] == 0 && $_G['uid'] != $post['authorid'] && $thread['price'] > 0)) {
		showmessage('reward_cant_operate');
	} elseif($post['authorid'] == $thread['authorid']) {
		showmessage('reward_cant_self');
	} elseif($thread['price'] < 0) {
		showmessage('reward_repeat_selection');
	}
	updatemembercount($post['authorid'], array($_G['setting']['creditstransextra'][2] => $thread['price']), 1, 'RAC', $_G['tid']);
	$thread['price'] = '-'.$thread['price'];
	C::t('forum_thread')->update($_G['tid'], array('price'=>$thread['price']));
	C::t('forum_post')->update('tid:'.$_G['tid'], $_GET['pid'], array(
		'dateline' => $thread['dateline'] + 1,
	));

	$thread['dateline'] = dgmdate($thread['dateline']);
	if($_G['uid'] != $thread['authorid']) {
		notification_add($thread['authorid'], 'reward', 'reward_question', array(
			'tid' => $thread['tid'],
			'subject' => $thread['subject'],
		));
	}
	if($thread['authorid'] == $_G['uid']) {
		notification_add($post['authorid'], 'reward', 'reward_bestanswer', array(
			'tid' => $thread['tid'],
			'subject' => $thread['subject'],
		));
	} else {
		notification_add($post['authorid'], 'reward', 'reward_bestanswer_moderator', array(
			'tid' => $thread['tid'],
			'subject' => $thread['subject'],
		));
	}


	showmessage('reward_completion', $forward);

} elseif($_GET['action'] == 'activityapplies') {

	if(!$_G['uid']) {
		showmessage('not_loggedin', NULL, array(), array('login' => 1));
	}

	if(submitcheck('activitysubmit')) {
		$activity = C::t('forum_activity')->fetch($_G['tid']);
		if($activity['expiration'] && $activity['expiration'] < TIMESTAMP) {
			showmessage('activity_stop', NULL, array(), array('login' => 1));
		}
		$applyinfo = array();
		$applyinfo = C::t('forum_activityapply')->fetch_info_for_user($_G['uid'], $_G['tid']);
		if($applyinfo && $applyinfo['verified'] < 2) {
			showmessage('activity_repeat_apply', NULL, array(), array('login' => 1));
		}
		$payvalue = intval($_GET['payvalue']);
		$payment = $_GET['payment'] ? $payvalue : -1;
		$message = cutstr(dhtmlspecialchars($_GET['message']), 200);
		$verified = $thread['authorid'] == $_G['uid'] ? 1 : 0;
		if($activity['ufield']) {
			$ufielddata = array();
			$activity['ufield'] = dunserialize($activity['ufield']);
			if(!empty($activity['ufield']['userfield'])) {
				$censor = discuz_censor::instance();
				loadcache('profilesetting');
				foreach($activity['ufield']['userfield'] as $filedname) {
					$value = $_POST[$filedname];
					if(is_array($value)) {
						$value = implode(',', $value);
					}
					$value = cutstr(dhtmlspecialchars(trim($value)), 100, '.');
					if($_G['cache']['profilesetting'][$filedname]['formtype'] == 'file' && !preg_match("/^https?:\/\/(.*)?\.(jpg|png|gif|jpeg|bmp)$/i", $value)) {
						showmessage('activity_imgurl_error');
					}
					if(empty($value) && $filedname != 'residedist' && $filedname != 'residecommunity') {
						showmessage('activity_exile_field');
					}
					$ufielddata['userfield'][$filedname] = $value;
				}
			}
			if(!empty($activity['ufield']['extfield'])) {
				foreach($activity['ufield']['extfield'] as $fieldid) {
					$value = cutstr(dhtmlspecialchars(trim($_GET[''.$fieldid])), 50, '.');
					$ufielddata['extfield'][$fieldid] = $value;
				}
			}
			$ufielddata = !empty($ufielddata) ? serialize($ufielddata) : '';
		}
		if($_G['setting']['activitycredit'] && $activity['credit'] && empty($applyinfo['verified'])) {
			checklowerlimit(array('extcredits'.$_G['setting']['activitycredit'] => '-'.$activity['credit']));
			updatemembercount($_G['uid'], array($_G['setting']['activitycredit'] => '-'.$activity['credit']), true, 'ACC', $_G['tid']);
		}
		if($applyinfo && $applyinfo['verified'] == 2) {
			$newinfo = array(
				'tid' => $_G['tid'],
				'username' => $_G['username'],
				'uid' => $_G['uid'],
				'message' => $message,
				'verified' => $verified,
				'dateline' => $_G['timestamp'],
				'payment' => $payment,
				'ufielddata' => $ufielddata
			);
			C::t('forum_activityapply')->update($applyinfo['applyid'], $newinfo);
		} else {
			$data = array('tid' => $_G['tid'], 'username' => $_G['username'], 'uid' => $_G['uid'], 'message' => $message, 'verified' => $verified, 'dateline' => $_G['timestamp'], 'payment' => $payment, 'ufielddata' => $ufielddata);
			C::t('forum_activityapply')->insert($data);
		}

		$applynumber = C::t('forum_activityapply')->fetch_count_for_thread($_G['tid']);
		C::t('forum_activity')->update($_G['tid'], array('applynumber' => $applynumber));

		if($thread['authorid'] != $_G['uid']) {
			notification_add($thread['authorid'], 'activity', 'activity_notice', array(
				'tid' => $_G['tid'],
				'subject' => $thread['subject'],
			));
			$space = array();
			space_merge($space, 'field_home');

			if(!empty($space['privacy']['feed']['newreply'])) {
				$feed['icon'] = 'activity';
				$feed['title_template'] = 'feed_reply_activity_title';
				$feed['title_data'] = array(
					'subject' => "<a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
					'hash_data' => "tid{$_G[tid]}"
				);
				$feed['id'] = $_G['tid'];
				$feed['idtype'] = 'tid';
				postfeed($feed);
			}
		}
		showmessage('activity_completion', "forum.php?mod=viewthread&tid=$_G[tid]".($_GET['from'] ? '&from='.$_GET['from'] : ''), array(), array('showdialog' => 1, 'showmsg' => true, 'locationtime' => true, 'alert' => 'right'));

	} elseif(submitcheck('activitycancel')) {
		C::t('forum_activityapply')->delete_for_user($_G['uid'], $_G['tid']);
		$applynumber = C::t('forum_activityapply')->fetch_count_for_thread($_G['tid']);
		C::t('forum_activity')->update($_G['tid'], array('applynumber' => $applynumber));
		$message = cutstr(dhtmlspecialchars($_GET['message']), 200);
		if($thread['authorid'] != $_G['uid']) {
			notification_add($thread['authorid'], 'activity', 'activity_cancel', array(
				'tid' => $_G['tid'],
				'subject' => $thread['subject'],
				'reason' => $message
			));
		}
		showmessage('activity_cancel_success', "forum.php?mod=viewthread&tid=$_G[tid]&do=viewapplylist".($_GET['from'] ? '&from='.$_GET['from'] :''), array(), array('showdialog' => 1, 'closetime' => true));
	}

} elseif($_GET['action'] == 'getactivityapplylist') {
	$pp = $_G['setting']['activitypp'];
	$page = max(1, $_G['page']);
	$start = ($page - 1) * $pp;
	$activity = C::t('forum_activity')->fetch($_G['tid']);
	if(!$activity || $thread['special'] != 4) {
		showmessage('undefined_action');
	}
	$query = C::t('forum_activityapply')->fetch_all_for_thread($_G['tid'], $start, $pp);
	foreach($query as $activityapplies) {
		$activityapplies['dateline'] = dgmdate($activityapplies['dateline']);
		$applylist[] = $activityapplies;
	}
	$multi = multi($activity['applynumber'], $pp, $page, "forum.php?mod=misc&action=getactivityapplylist&tid=$_G[tid]&pid=$_GET[pid]");
	include template('forum/activity_applist_more');
} elseif($_GET['action'] == 'activityapplylist') {

	$isactivitymaster = $thread['authorid'] == $_G['uid'] ||
						(in_array($_G['group']['radminid'], array(1, 2)) || ($_G['group']['radminid'] == 3 && $_G['forum']['ismoderator'])
						&& $_G['group']['alloweditactivity']);
	if(!$isactivitymaster) {
		showmessage('activity_is_not_manager');
	}

	$activity = C::t('forum_activity')->fetch($_G['tid']);
	if(empty($activity) || $thread['special'] != 4) {
		showmessage('activity_is_not_exists');
	}

	if(!submitcheck('applylistsubmit')) {
		$applylist = array();
		$activity['ufield'] = $activity['ufield'] ? dunserialize($activity['ufield']) : array();
		$query = C::t('forum_activityapply')->fetch_all_for_thread($_G['tid'], 0, 500, $_GET['uid'], $isactivitymaster);
		foreach($query as $activityapplies) {
			$ufielddata = '';
			$activityapplies['dateline'] = dgmdate($activityapplies['dateline'], 'u');
			$activityapplies['ufielddata'] = !empty($activityapplies['ufielddata']) ? dunserialize($activityapplies['ufielddata']) : '';
			if($activityapplies['ufielddata']) {
				if($activityapplies['ufielddata']['userfield']) {
					require_once libfile('function/profile');
					loadcache('profilesetting');
					$data = '';
					foreach($activity['ufield']['userfield'] as $fieldid) {
						if($fieldid == 'qq') {
							$fieldid = 'qqnumber';
						}
						$data = profile_show($fieldid, $activityapplies['ufielddata']['userfield']);
						$ufielddata .= '<li>'.$_G['cache']['profilesetting'][$fieldid]['title'].'&nbsp;&nbsp;:&nbsp;&nbsp;';
						if(empty($data)) {
							$ufielddata .= '</li>';
							continue;
						}
						if($_G['cache']['profilesetting'][$fieldid]['formtype'] != 'file') {
							$ufielddata .= $data;
						} else {
							$ufielddata .= '<a href="'.$data.'" target="_blank" onclick="zoom(this, this.href, 0, 0, 0); return false;">'.lang('forum/misc', 'activity_viewimg').'</a>';
						}
						$ufielddata .= '</li>';
					}
				}
				if($activityapplies['ufielddata']['extfield']) {
					foreach($activity['ufield']['extfield'] as $name) {
						$ufielddata .= '<li>'.$name.'&nbsp;&nbsp;:&nbsp;&nbsp;'.$activityapplies['ufielddata']['extfield'][$name].'</li>';
					}
				}
			}
			$activityapplies['ufielddata'] = $ufielddata;
			$applylist[] = $activityapplies;
		}

		$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'u');
		$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto'], 'u') : 0;
		$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration'], 'u') : 0;

		include template('forum/activity_applylist');
	} else {
		if(empty($_GET['applyidarray'])) {
			showmessage('activity_choice_applicant');
		} else {
			$reason = cutstr(dhtmlspecialchars($_GET['reason']), 200);
			$tempuid = $uidarray = $unverified = array();
			$query = C::t('forum_activityapply')->fetch_all($_GET['applyidarray']);
			foreach($query as $row) {
				if($row['tid'] == $_G['tid']) {
					$tempusers[$row['uid']] = $row;
				}
			}
			$query  = C::t('common_member')->fetch_all(array_keys($tempusers));
			foreach($query as $user) {
				$uidarray[] = $user['uid'];
				if($tempusers[$user['uid']]['verified'] != 1) {
					$unverified[] = $user['uid'];
				}
			}
			$activity_subject = $thread['subject'];

			if($_GET['operation'] == 'notification') {
				if(empty($uidarray)) {
					showmessage('activity_notification_user');
				}
				if(empty($reason)) {
					showmessage('activity_notification_reason');
				}
				if($uidarray) {
					foreach($uidarray as $uid) {
						notification_add($uid, 'activity', 'activity_notification', array('tid' => $_G['tid'], 'subject' => $activity_subject, 'msg' => $reason));
					}
					showmessage('activity_notification_success', "forum.php?mod=viewthread&tid=$_G[tid]&do=viewapplylist".($_GET['from'] ? '&from='.$_GET['from'] : ''), array(), array('showdialog' => 1, 'closetime' => true));
				}
			} elseif($_GET['operation'] == 'delete') {
				if($uidarray) {
					C::t('forum_activityapply')->delete_for_thread($_G['tid'], $_GET['applyidarray']);
					foreach($uidarray as $uid) {
						notification_add($uid, 'activity', 'activity_delete', array(
							'tid' => $_G['tid'],
							'subject' => $activity_subject,
							'reason' => $reason,
						));
					}
				}
				$applynumber = C::t('forum_activityapply')->fetch_count_for_thread($_G['tid']);
				C::t('forum_activity')->update($_G['tid'], array('applynumber' => $applynumber));
				showmessage('activity_delete_completion', "forum.php?mod=viewthread&tid=$_G[tid]&do=viewapplylist".($_GET['from'] ? '&from='.$_GET['from'] : ''), array(), array('showdialog' => 1, 'closetime' => true));
			} else {
				if($unverified) {
					$verified = $_GET['operation'] == 'replenish' ? 2 : 1;

					C::t('forum_activityapply')->update_verified_for_thread($verified, $_G['tid'], $_GET['applyidarray']);
					$notification_lang = $verified == 1 ? 'activity_apply' : 'activity_replenish';
					foreach($unverified as $uid) {
						notification_add($uid, 'activity', $notification_lang, array(
							'tid' => $_G['tid'],
							'subject' => $activity_subject,
							'reason' => $reason,
						));
					}
				}
				$applynumber = C::t('forum_activityapply')->fetch_count_for_thread($_G['tid']);
				C::t('forum_activity')->update($_G['tid'], array('applynumber' => $applynumber));

				showmessage('activity_auditing_completion', "forum.php?mod=viewthread&tid=$_G[tid]&do=viewapplylist".($_GET['from'] ? '&from='.$_GET['from'] : ''), array(), array('showdialog' => 1, 'closetime' => true));
			}
		}
	}

} elseif($_GET['action'] == 'activityexport') {

	$isactivitymaster = $thread['authorid'] == $_G['uid'] ||
						(in_array($_G['group']['radminid'], array(1, 2)) || ($_G['group']['radminid'] == 3 && $_G['forum']['ismoderator'])
						&& $_G['group']['alloweditactivity']);
	if(!$isactivitymaster) {
		showmessage('activity_is_not_manager');
	}

	$activity = C::t('forum_activity')->fetch($_G['tid']);
	$postinfo = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid']);
	$activity['message'] = $postinfo['message'];
	if(empty($activity) || $thread['special'] != 4) {
		showmessage('activity_is_not_exists');
	}
	$ufield = '';
	if($activity['ufield']) {
		$activity['ufield'] = dunserialize($activity['ufield']);
		if($activity['ufield']['userfield']) {
			loadcache('profilesetting');
			foreach($activity['ufield']['userfield'] as $fieldid) {
				$ufield .= ','.$_G['cache']['profilesetting'][$fieldid]['title'];
			}
		}
		if($activity['ufield']['extfield']) {
			foreach($activity['ufield']['extfield'] as $extname) {
				$ufield .= ','.$extname;
			}
		}
	}
	$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'dt');
	$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto'], 'dt') : 0;
	$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration'], 'dt') : 0;
	$activity['message'] = trim(preg_replace('/\[.+?\]/', '', $activity['message']));
	$applynumbers = C::t('forum_activityapply')->fetch_count_for_thread($_G['tid']);

	$applylist = array();
	$query = C::t('forum_activityapply')->fetch_all_for_thread($_G['tid'], 0, 2000, 0, 1);
	foreach($query as $apply) {
		$apply = str_replace(',', lang('forum/thread', 't_comma'), $apply);
		$apply['dateline'] = dgmdate($apply['dateline'], 'dt');
		$apply['ufielddata'] = !empty($apply['ufielddata']) ? dunserialize($apply['ufielddata']) : '';
		$ufielddata = '';
		if($apply['ufielddata'] && $activity['ufield']) {
			if($apply['ufielddata']['userfield'] && $activity['ufield']['userfield']) {
				require_once libfile('function/profile');
				loadcache('profilesetting');
				foreach($activity['ufield']['userfield'] as $fieldid) {
					if($fieldid == 'qq') {
						$fieldid = 'qqnumber';
					}
					$data = profile_show($fieldid, $apply['ufielddata']['userfield']);
					if(strlen($data) > 11 && is_numeric($data)) {
						$data = '['.$data.']';
					}
					$ufielddata .= ','.strip_tags(str_replace('&nbsp;', ' ', $data));
				}
			}
			if($activity['ufield']['extfield']) {
				foreach($activity['ufield']['extfield'] as $extname) {
					if(strlen($apply['ufielddata']['extfield'][$extname]) > 11 && is_numeric($apply['ufielddata']['extfield'][$extname])) {
						$apply['ufielddata']['extfield'][$extname] = '['.$apply['ufielddata']['extfield'][$extname].']';
					}
					$ufielddata .= ','.strip_tags(str_replace('&nbsp;', ' ', $apply['ufielddata']['extfield'][$extname]));
				}
			}
		}
		$apply['fielddata'] = $ufielddata;
		if(strlen($apply['message']) > 11 && is_numeric($apply['message'])) {
			$apply['message'] = '['.$apply['message'].']';
		}
		$applylist[] = $apply;
	}
	$filename = "activity_{$_G[tid]}.csv";

	include template('forum/activity_export');
	$csvstr = ob_get_contents();
	ob_end_clean();
	header('Content-Encoding: none');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$filename);
	header('Pragma: no-cache');
	header('Expires: 0');
	if($_G['charset'] != 'gbk') {
		$csvstr = diconv($csvstr, $_G['charset'], 'GBK');
	}
	echo $csvstr;
} elseif($_GET['action'] == 'tradeorder') {

	$trades = array();
	$query = C::t('forum_trade')->fetch_all_thread_goods($_G['tid']);
	if($thread['authorid'] != $_G['uid'] && !$_G['group']['allowedittrade']) {
		showmessage('no_privilege_tradeorder');
	}

	if(!submitcheck('tradesubmit')) {

		$stickcount = 0;$trades = $tradesstick = array();
		foreach($query as $trade) {
			$stickcount = $trade['displayorder'] > 0 ? $stickcount + 1 : $stickcount;
			$trade['displayorderview'] = $trade['displayorder'] < 0 ? 128 + $trade['displayorder'] : $trade['displayorder'];
			if($trade['expiration']) {
				$trade['expiration'] = ($trade['expiration'] - TIMESTAMP) / 86400;
				if($trade['expiration'] > 0) {
					$trade['expirationhour'] = floor(($trade['expiration'] - floor($trade['expiration'])) * 24);
					$trade['expiration'] = floor($trade['expiration']);
				} else {
					$trade['expiration'] = -1;
				}
			}
			if($trade['displayorder'] < 0) {
				$trades[] = $trade;
			} else {
				$tradesstick[] = $trade;
			}
		}
		$trades = array_merge($tradesstick, $trades);
		include template('forum/trade_displayorder');

	} else {

		$count = 0;
		foreach($query as $trade) {
			$displayordernew = abs(intval($_GET['displayorder'][$trade['pid']]));
			$displayordernew = $displayordernew > 128 ? 0 : $displayordernew;
			if($_GET['stick'][$trade['pid']]) {
				$count++;
				$displayordernew = $displayordernew == 0 ? 1 : $displayordernew;
			}
			if(!$_GET['stick'][$trade['pid']] || $displayordernew > 0 && $_G['group']['tradestick'] < $count) {
				$displayordernew = -1 * (128 - $displayordernew);
			}
			C::t('forum_trade')->update($_G['tid'], $trade['pid'], array('displayorder' => $displayordernew));
		}

		showmessage('trade_displayorder_updated', "forum.php?mod=viewthread&tid=$_G[tid]".($_GET['from'] ? '&from='.$_GET['from'] : ''));

	}

} elseif($_GET['action'] == 'debatevote') {

	if(!empty($thread['closed'])) {
		showmessage('thread_poll_closed');
	}

	if(!$_G['uid']) {
		showmessage('debate_poll_nopermission', NULL, array(), array('login' => 1));
	}

	$isfirst = empty($_GET['pid']) ? TRUE : FALSE;

	$debate = C::t('forum_debate')->fetch($_G['tid']);

	if(empty($debate)) {
		showmessage('debate_nofound');
	}

	if($isfirst) {
		$stand = intval($_GET['stand']);

		if($stand == 1 || $stand == 2) {
			if(strpos("\t".$debate['affirmvoterids'], "\t{$_G['uid']}\t") !== FALSE || strpos("\t".$debate['negavoterids'], "\t{$_G['uid']}\t") !== FALSE) {
				showmessage('debate_poll_voted');
			} elseif($debate['endtime'] && $debate['endtime'] < TIMESTAMP) {
				showmessage('debate_poll_end');
			}
		}
		C::t('forum_debate')->update_voters($_G['tid'], $_G['uid'], $stand);

		showmessage('debate_poll_succeed', 'forum.php?mod=viewthread&tid='.$_G['tid'], array(), array('showmsg' => 1, 'locationtime' => true));
	}

	$debatepost = C::t('forum_debatepost')->fetch($_GET['pid']);
	if(empty($debatepost) || $debatepost['tid'] != $_G['tid']) {
		showmessage('debate_nofound');
	}
	$debate = array_merge($debate, $debatepost);
	unset($debatepost);

	if($debate['uid'] == $_G['uid']) {
		showmessage('debate_poll_myself', "forum.php?mod=viewthread&tid=$_G[tid]".($_GET['from'] ? '&from='.$_GET['from'] : ''), array(), array('showmsg' => 1));
	} elseif(strpos("\t".$debate['voterids'], "\t$_G[uid]\t") !== FALSE) {
		showmessage('debate_poll_voted', "forum.php?mod=viewthread&tid=$_G[tid]".($_GET['from'] ? '&from='.$_GET['from'] : ''), array(), array('showmsg' => 1));
	} elseif($debate['endtime'] && $debate['endtime'] < TIMESTAMP) {
		showmessage('debate_poll_end', "forum.php?mod=viewthread&tid=$_G[tid]".($_GET['from'] ? '&from='.$_GET['from'] : ''), array(), array('showmsg' => 1));
	}

	C::t('forum_debatepost')->update_voters($_GET['pid'], $_G['uid']);

	showmessage('debate_poll_succeed', "forum.php?mod=viewthread&tid=$_G[tid]".($_GET['from'] ? '&from='.$_GET['from'] : ''), array(), array('showmsg' => 1));

} elseif($_GET['action'] == 'debateumpire') {

	$debate = C::t('forum_debate')->fetch($_G['tid']);

	if(empty($debate)) {
		showmessage('debate_nofound');
	}elseif(!empty($thread['closed']) && TIMESTAMP - $debate['endtime'] > 3600) {
		showmessage('debate_umpire_edit_invalid');
	} elseif($_G['member']['username'] != $debate['umpire']) {
		showmessage('debate_umpire_nopermission');
	}

	$debate = array_merge($debate, $thread);

	if(!submitcheck('umpiresubmit')) {
		$candidates = array();
		$uids = array();
		$voters = C::t('forum_debatepost')->fetch_all_voters($_G['tid'], 30);
		foreach($voters as $candidate) {
			$uids[] = $candidate['uid'];
		}
		$users = C::t('common_member')->fetch_all_username_by_uid($uids);
		foreach($voters as $candidate) {
			$candidate['username'] = dhtmlspecialchars($users[$candidate['uid']]);
			$candidates[$candidate['username']] = $candidate;
		}
		$winnerchecked = array($debate['winner'] => ' checked="checked"');

		list($debate['bestdebater']) = preg_split("/\s/", $debate['bestdebater']);

		include template('forum/debate_umpire');
	} else {
		if(empty($_GET['bestdebater'])) {
			showmessage('debate_umpire_nofound_bestdebater');
		} elseif(empty($_GET['winner'])) {
			showmessage('debate_umpire_nofound_winner');
		} elseif(empty($_GET['umpirepoint'])) {
			showmessage('debate_umpire_nofound_point');
		}
		$bestdebateruid = C::t('common_member')->fetch_uid_by_username($_GET['bestdebater']);
		if(!$bestdebateruid) {
			showmessage('debate_umpire_bestdebater_invalid');
		}
		if(!($bestdebaterstand = C::t('forum_debatepost')->get_stand_by_bestuid($_G['tid'], $bestdebateruid, array($debate['uid'], $_G['uid'])))) {
			showmessage('debate_umpire_bestdebater_invalid');
		}
		list($bestdebatervoters, $bestdebaterreplies) = C::t('forum_debatepost')->get_numbers_by_bestuid($_G['tid'], $bestdebateruid);

		$umpirepoint = dhtmlspecialchars($_GET['umpirepoint']);
		$bestdebater = dhtmlspecialchars($_GET['bestdebater']);
		$winner = intval($_GET['winner']);
		C::t('forum_thread')->update($_G['tid'], array('closed' => 1));
		C::t('forum_debate')->update($_G['tid'], array('umpirepoint' => $umpirepoint, 'winner' => $winner, 'bestdebater' => "$bestdebater\t$bestdebateruid\t$bestdebaterstand\t$bestdebatervoters\t$bestdebaterreplies", 'endtime' => $_G['timestamp']));
		showmessage('debate_umpire_comment_succeed', 'forum.php?mod=viewthread&tid='.$_G['tid'].($_GET['from'] ? '&from='.$_GET['from'] : ''));
	}

} elseif($_GET['action'] == 'recommend') {

	dsetcookie('discuz_recommend', '', -1, 0);
	if(empty($_G['uid'])) {
		showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
	}

	if(empty($_GET['hash']) || $_GET['hash'] != formhash()) {
		showmessage('submit_invalid');
	}
	if(!$_G['setting']['recommendthread']['status'] || !$_G['group']['allowrecommend']) {
		showmessage('no_privilege_recommend');
	}

	if($thread['authorid'] == $_G['uid'] && !$_G['setting']['recommendthread']['ownthread']) {
		showmessage('recommend_self_disallow', '', array('recommendc' => $thread['recommends']), array('msgtype' => 3));
	}
	if(C::t('forum_memberrecommend')->fetch_by_recommenduid_tid($_G['uid'], $_G['tid'])) {
		showmessage('recommend_duplicate', '', array('recommendc' => $thread['recommends']), array('msgtype' => 3));
	}

	$recommendcount = C::t('forum_memberrecommend')->count_by_recommenduid_dateline($_G['uid'], $_G['timestamp']-86400);
	if($_G['setting']['recommendthread']['daycount'] && $recommendcount >= $_G['setting']['recommendthread']['daycount']) {
		showmessage('recommend_outoftimes', '', array('recommendc' => $thread['recommends']), array('msgtype' => 3));
	}

	$_G['group']['allowrecommend'] = intval($_GET['do'] == 'add' ? $_G['group']['allowrecommend'] : -$_G['group']['allowrecommend']);
	$fieldarr = array();
	if($_GET['do'] == 'add') {
		$heatadd = 'recommend_add=recommend_add+1';
		$fieldarr['recommend_add'] = 1;
	} else {
		$heatadd = 'recommend_sub=recommend_sub+1';
		$fieldarr['recommend_sub'] = 1;
	}

		update_threadpartake($_G['tid']);
		$fieldarr['heats'] = 0;
	$fieldarr['recommends'] = $_G['group']['allowrecommend'];
	C::t('forum_thread')->increase($_G['tid'], $fieldarr);
	if(empty($thread['closed'])) {
		C::t('forum_thread')->update($_G['tid'], array('lastpost' => TIMESTAMP));
	}
	C::t('forum_memberrecommend')->insert(array('tid'=>$_G['tid'], 'recommenduid'=>$_G['uid'], 'dateline'=>$_G['timestamp']));

	dsetcookie('recommend', 1, 43200);
	$recommendv = $_G['group']['allowrecommend'] > 0 ? '+'.$_G['group']['allowrecommend'] : $_G['group']['allowrecommend'];
	if($_G['setting']['recommendthread']['daycount']) {
		$daycount = $_G['setting']['recommendthread']['daycount'] - $recommendcount;
		showmessage('recommend_daycount_succeed', '', array('recommendv' => $recommendv, 'recommendc' => $thread['recommends'], 'daycount' => $daycount), array('msgtype' => 3));
	} else {
		showmessage('recommend_succeed', '', array('recommendv' => $recommendv, 'recommendc' => $thread['recommends']), array('msgtype' => 3));
	}

} elseif($_GET['action'] == 'protectsort') {
	$tid = $_GET['tid'];
	$optionid = $_GET['optionid'];
	include template('common/header_ajax');
	$typeoptionvarvalue = C::t('forum_typeoptionvar')->fetch_all_by_tid_optionid($tid, $optionid);
	$typeoptionvarvalue[0]['expiration'] = $typeoptionvarvalue[0]['expiration'] && $typeoptionvarvalue[0]['expiration'] <= TIMESTAMP ? 1 : 0;
	$option = C::t('forum_typeoption')->fetch($optionid);

	if(($option['expiration'] && !$typeoptionvarvalue[0]['expiration']) || empty($option['expiration'])) {
		$protect = dunserialize($option['protect']);
		include_once libfile('function/threadsort');
		if(protectguard($protect)) {
			if(empty($option['permprompt'])) {
				echo lang('forum/misc', 'view_noperm');
			} else {
				echo $option['permprompt'];
			}
		} else {
			echo nl2br($typeoptionvarvalue[0]['value']);
		}
	} else {
		echo lang('forum/misc', 'has_expired');
	}
	include template('common/footer_ajax');

} elseif($_GET['action'] == 'usertag') {
	if($_G['tid'] && $_G['group']['alloweditusertag']) {
		if(!submitcheck('addusertag')) {
			$recent_use_tag = $lastlog = $polloptions = array();
			$i = 0;
			$query = C::t('common_tagitem')->select(0, 0, 'uid', 'tagid', 'DESC', 200);
			foreach($query as $result) {
				if($i > 4) {
					break;
				}
				if($recent_use_tag[$result['tagid']] == '') {
					$i++;
				}
				$recent_use_tag[$result['tagid']] = 1;
			}
			if($recent_use_tag) {
				$query = C::t('common_tag')->fetch_all(array_keys($recent_use_tag));
				foreach($query as $result) {
					$recent_use_tag[$result[tagid]] = $result['tagname'];
				}
			}
			foreach(C::t('forum_threadmod')->fetch_all_by_tid($_G['tid'], 'AUT', 3) as $row) {
				$row['dateline'] = dgmdate($row['dateline'], 'u');
				$lastlog[] = $row;
			}
			if($_G['thread']['special'] == 1) {
				$query = C::t('forum_polloption')->fetch_all_by_tid($_G['tid']);
				foreach($query as $polloption) {
					if($polloption['votes'] > 0) {
						$polloptions[] = $polloption;
					}

				}
				if(empty($polloptions)) {
					showmessage('thread_poll_voter_isnull', '', array('haserror' => 1));
				}
			} elseif($_G['thread']['special'] == 4) {
				$activityapplys = C::t('forum_activityapply')->fetch_all_for_thread($_G['tid'], 0, 1);
				if(empty($activityapplys)) {
					showmessage('thread_activityapply_isnull', '', array('haserror' => 1));
				}
			}
		} else {
			$class_tag = new tag();
			$tagarray = $class_tag->add_tag($_GET['tags'], 0, 'uid', 1);
			if($tagarray) {
				$uids = array();
				if($_G['thread']['special'] == 1) {
					if($_GET['polloptions']) {
						$query = C::t('forum_polloption')->fetch_all($_GET['polloptions']);
					} else {
						$query = C::t('forum_polloption')->fetch_all_by_tid($_G['tid']);
					}
					$uids = '';
					foreach($query as $row) {
						$uids .= $row['voterids'];
					}
					if($uids) {
						$uids = explode("\t", trim($uids));
					}
				} elseif($_G['thread']['special'] == 4) {
					$query = C::t('forum_activityapply')->fetch_all_for_thread($_G['tid'], 0, 2000);
					foreach($query as $row) {
						$uids[] = $row['uid'];
					}
				} else {
					foreach(C::t('forum_post')->fetch_all_by_tid('tid:'.$_G['tid'], $_G['tid'], false) as $author) {
						$uids[] = $author['authorid'];
					}
				}

				$uids = @array_unique($uids);
				$count = count($uids);
				$limit = intval($_GET['limit']);
				$per = 200;
				$uids = @array_slice($uids, $limit, $per);
				if($uids) {
					foreach($uids as $uid) {
						if(empty($uid)) continue;
						foreach($tagarray as $tagid => $tagname) {
							C::t('common_tagitem')->insert(array('tagid' => $tagid, 'itemid' => $uid, 'idtype' => 'uid'), 0, 1);
						}
					}
					updatemodlog($_G['tid'], 'AUT', 0, 0, implode(',', $tagarray));
					showmessage('forum_usertag_set_continue', '', array('limit' => $limit, 'next' => min($limit + $per, $count), 'count' => $count), array('alert' => 'right'));
				}
				showmessage('forum_usertag_succeed', '', array(), array('alert' => 'right'));
			} else {
				showmessage('parameters_error', '', array('haserror' => 1));
			}
		}

	} else {
		showmessage('parameters_error', '', array('haserror' => 1));
	}
	include_once template("forum/usertag");
} elseif($_GET['action'] == 'postreview') {

	if(!$_G['setting']['repliesrank'] || empty($_G['uid'])) {
		showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
	}
	if(empty($_GET['hash']) || $_GET['hash'] != formhash()) {
		showmessage('submit_invalid');
	}

	$doArray = array('support', 'against');

	$post = C::t('forum_post')->fetch('tid:'.$_GET['tid'], $_GET['pid'], false);

	if(!in_array($_GET['do'], $doArray) || empty($post) || $post['first'] == 1 || ($_G['setting']['threadfilternum'] && $_G['setting']['filterednovote'] && getstatus($post['status'], 11)) || $post['invisible'] < 0) {
		showmessage('undefined_action', NULL);
	}

	$hotreply = C::t('forum_hotreply_number')->fetch_by_pid($post['pid']);
	if($_G['uid'] == $post['authorid']) {
		showmessage('noreply_yourself_error', '', array(), array('msgtype' => 3));
	}

	if(empty($hotreply)) {
		$hotreply['pid'] = C::t('forum_hotreply_number')->insert(array(
			'pid' => $post['pid'],
			'tid' => $post['tid'],
			'support' => 0,
			'against' => 0,
			'total' => 0,
		), true);
	} else {
		if(C::t('forum_hotreply_member')->fetch($post['pid'], $_G['uid'])) {
			showmessage('noreply_voted_error', '', array(), array('msgtype' => 3));
		}
	}

	$typeid = $_GET['do'] == 'support' ? 1 : 0;

	C::t('forum_hotreply_number')->update_num($post['pid'], $typeid);
	C::t('forum_hotreply_member')->insert(array(
		'tid' => $post['tid'],
		'pid' => $post['pid'],
		'uid' => $_G['uid'],
		'attitude' => $typeid,
	));

	$hotreply[$_GET['do']]++;

	showmessage('thread_poll_succeed', '', array(), array('msgtype' => 3, 'extrajs' => '<script type="text/javascript">postreviewupdate('.$post['pid'].', '.$typeid.');</script>'));
} elseif($_GET['action'] == 'hidden') {
	if($_GET['formhash'] != FORMHASH) {
		showmessage('undefined_action', NULL);
	}
	if(!$_G['uid']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}
	if(in_array($thread['fid'], $_G['setting']['security_forums_white_list']) || $thread['displayorder'] > 0 || $thread['highlight'] || $thread['digest'] || $thread['stamp'] > -1) {
		showmessage('thread_hidden_error', NULL);
	}
	$member = C::t('common_member')->fetch($thread['authorid']);
	if(in_array($member['groupid'], $_G['setting']['security_usergroups_white_list'])) {
		showmessage('thread_hidden_error', NULL);
	}
	if(C::t('forum_forumrecommend')->fetch($thread['tid'])) {
		showmessage('thread_hidden_error', NULL);
	}
	C::t('forum_threadhidelog')->insert($_GET['tid'], $_G['uid']);
	if($thread['hidden'] + 1 == $_G['setting']['threadhidethreshold']) {
		notification_add($thread['authorid'], 'post', 'thread_hidden', array('tid' => $thread['tid'], 'subject' => $thread['subject']), 1);
	}
	$thide = explode('|', $_G['cookie']['thide']);
	$thide = array_slice($thide, -20);
	if(!in_array($_GET['tid'], $thide)) {
		$thide[] = $_GET['tid'];
	}
	dsetcookie('thide', implode('|', $thide), 2592000);
	showmessage('thread_hidden_success', dreferer(), array(), array('showdialog' => true, 'closetime' => true, 'extrajs' => '<script type="text/javascript" reload="1">$(\'normalthread_'.$_GET['tid'].'\').style.display = \'none\'</script>'));
} elseif($_GET['action'] == 'hiderecover') {
	if($_GET['formhash'] != FORMHASH) {
		showmessage('undefined_action', NULL);
	}
	$seccodecheck = true;
	if(submitcheck('hiderecoversubmit')) {
		C::t('forum_threadhidelog')->delete_by_tid($_GET['tid']);
		showmessage('thread_hiderecover_success', dreferer());
	} else {
		include template('forum/hiderecover');
	}
}

function getratelist($raterange) {
	global $_G;
	$maxratetoday = getratingleft($raterange);

	$ratelist = array();
	foreach($raterange as $id => $rating) {
		if(isset($_G['setting']['extcredits'][$id])) {
			$ratelist[$id] = '';
			$rating['max'] = $rating['max'] < $maxratetoday[$id] ? $rating['max'] : $maxratetoday[$id];
			$rating['min'] = -$rating['min'] < $maxratetoday[$id] ? $rating['min'] : -$maxratetoday[$id];
			$offset = abs(ceil(($rating['max'] - $rating['min']) / 10));
			if($rating['max'] > $rating['min']) {
				for($vote = $rating['max']; $vote >= $rating['min']; $vote -= $offset) {
					$ratelist[$id] .= $vote ? '<li>'.($vote > 0 ? '+'.$vote : $vote).'</li>' : '';
				}
			}
		}
	}
	return $ratelist;
}

function getratingleft($raterange) {
	global $_G;
	$maxratetoday = array();

	foreach($raterange as $id => $rating) {
		$maxratetoday[$id] = $rating['mrpd'];
	}

	foreach(C::t('forum_ratelog')->fetch_all_sum_score($_G['uid'], $_G['timestamp']-86400) as $rate) {
		$maxratetoday[$rate['extcredits']] = $raterange[$rate['extcredits']]['mrpd'] - $rate['todayrate'];
	}
	return $maxratetoday;
}

?>