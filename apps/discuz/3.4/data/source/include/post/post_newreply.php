<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newreply.php 33709 2013-08-06 09:06:56Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');

$isfirstpost = 0;
$_G['group']['allowimgcontent'] = 0;
$showthreadsorts = 0;
$quotemessage = '';

if($special == 5) {
	$debate = array_merge($thread, daddslashes(C::t('forum_debate')->fetch($_G['tid'])));
	$firststand = C::t('forum_debatepost')->get_firststand($_G['tid'], $_G['uid']);
	$stand = $firststand ? $firststand : intval($_GET['stand']);

	if($debate['endtime'] && $debate['endtime'] < TIMESTAMP) {
		showmessage('debate_end');
	}
}

if(!$_G['uid'] && !((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])))) {
	showmessage('replyperm_login_nopermission', NULL, array(), array('login' => 1));
} elseif(empty($_G['forum']['allowreply'])) {
	if(!$_G['forum']['replyperm'] && !$_G['group']['allowreply']) {
		showmessage('replyperm_none_nopermission', NULL, array(), array('login' => 1));
	} elseif($_G['forum']['replyperm'] && !forumperm($_G['forum']['replyperm'])) {
		showmessagenoperm('replyperm', $_G['forum']['fid']);
	}
} elseif($_G['forum']['allowreply'] == -1) {
	showmessage('post_forum_newreply_nopermission', NULL);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage('replyperm_login_nopermission', NULL, array(), array('login' => 1));
}

if(empty($thread)) {
	showmessage('thread_nonexistence');
} elseif($thread['price'] > 0 && $thread['special'] == 0 && !$_G['uid']) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

checklowerlimit('reply', 0, 1, $_G['forum']['fid']);

if($_G['setting']['commentnumber'] && !empty($_GET['comment'])) {
	if(!submitcheck('commentsubmit', 0, $seccodecheck, $secqaacheck)) {
		showmessage('submitcheck_error', NULL);
	}
	$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['pid']);
	if(!$post || !($_G['setting']['commentpostself'] || $post['authorid'] != $_G['uid']) || !(($post['first'] && $_G['setting']['commentfirstpost'] && in_array($_G['group']['allowcommentpost'], array(1, 3)) || (!$post['first'] && in_array($_G['group']['allowcommentpost'], array(2, 3)))))) {
		showmessage('postcomment_error');
	}
	if($thread['closed'] && !$_G['forum']['ismoderator'] && !$thread['isgroup']) {
		showmessage('post_thread_closed');
	} elseif(!$thread['isgroup'] && $post_autoclose = checkautoclose($thread)) {
		showmessage($post_autoclose, '', array('autoclose' => $_G['forum']['autoclose']));
	} elseif(checkflood()) {
		showmessage('post_flood_ctrl', '', array('floodctrl' => $_G['setting']['floodctrl']));
	} elseif(checkmaxperhour('pid')) {
		showmessage('post_flood_ctrl_posts_per_hour', '', array('posts_per_hour' => $_G['group']['maxpostsperhour']));
	}
	$commentscore = '';
	if(!empty($_GET['commentitem']) && !empty($_G['uid']) && $post['authorid'] != $_G['uid']) {
		foreach($_GET['commentitem'] as $itemk => $itemv) {
			if($itemv !== '') {
				$commentscore .= strip_tags(trim($itemk)).': <i>'.intval($itemv).'</i> ';
			}
		}
	}
	$comment = cutstr(($commentscore ? $commentscore.'<br />' : '').censor(trim(dhtmlspecialchars($_GET['message'])), '***'), 200, ' ');
	if(!$comment) {
		showmessage('post_sm_isnull');
	}
	$pcid = C::t('forum_postcomment')->insert(array(
		'tid' => $post['tid'],
		'pid' => $post['pid'],
		'author' => $_G['username'],
		'authorid' => $_G['uid'],
		'dateline' => TIMESTAMP,
		'comment' => $comment,
		'score' => $commentscore ? 1 : 0,
		'useip' => $_G['clientip'],
		'port'=> $_G['remoteport']
	), true);
	C::t('forum_post')->update('tid:'.$_G['tid'], $_GET['pid'], array('comment' => 1));

	$comments = $thread['comments'] ? $thread['comments'] + 1 : C::t('forum_postcomment')->count_by_tid($_G['tid']);
	C::t('forum_thread')->update($_G['tid'], array('comments' => $comments));
	!empty($_G['uid']) && $thread['displayorder'] != -4 && updatepostcredits('+', $_G['uid'], 'reply', $_G['fid']);
	if(!empty($_G['uid']) && $_G['uid'] != $post['authorid']) {
		notification_add($post['authorid'], 'pcomment', 'comment_add', array(
			'tid' => $_G['tid'],
			'pid' => $_GET['pid'],
			'subject' => $thread['subject'],
			'from_id' => $_G['tid'],
			'from_idtype' => 'pcomment',
			'commentmsg' => cutstr(str_replace(array('[b]', '[/b]', '[/color]'), '', preg_replace("/\[color=([#\w]+?)\]/i", "", $comment)), 200)
		));
	}
	update_threadpartake($post['tid']);
	$pcid = C::t('forum_postcomment')->fetch_standpoint_by_pid($_GET['pid']);
	$pcid = $pcid['id'];
	if(!empty($_G['uid']) && $_GET['commentitem']) {
		$totalcomment = array();
		foreach(C::t('forum_postcomment')->fetch_all_by_pid_score($_GET['pid'], 1) as $comment) {
			$comment['comment'] = addslashes($comment['comment']);
			if(strexists($comment['comment'], '<br />')) {
				if(preg_match_all("/([^:]+?):\s<i>(\d+)<\/i>/", $comment['comment'], $a)) {
					foreach($a[1] as $k => $itemk) {
						$totalcomment[trim($itemk)][] = $a[2][$k];
					}
				}
			}
		}
		$totalv = '';
		foreach($totalcomment as $itemk => $itemv) {
			$totalv .= strip_tags(trim($itemk)).': <i>'.(floatval(sprintf('%1.1f', array_sum($itemv) / count($itemv)))).'</i> ';
		}

		if($pcid) {
			C::t('forum_postcomment')->update($pcid, array('comment' => $totalv, 'dateline' => TIMESTAMP + 1));
		} else {
			C::t('forum_postcomment')->insert(array(
				'tid' => $post['tid'],
				'pid' => $post['pid'],
				'author' => '',
				'authorid' => '-1',
				'dateline' => TIMESTAMP + 1,
				'comment' => $totalv
			));
		}
	}
	C::t('forum_postcache')->delete($post['pid']);
	showmessage('comment_add_succeed', "forum.php?mod=viewthread&tid=$post[tid]&pid=$post[pid]&page=$_GET[page]&extra=$extra#pid$post[pid]", array('tid' => $post['tid'], 'pid' => $post['pid']));
}

if($special == 127) {
	$postinfo = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid']);
	$sppos = strrpos($postinfo['message'], chr(0).chr(0).chr(0));
	$specialextra = substr($postinfo['message'], $sppos + 3);
}
if(getstatus($thread['status'], 3)) {
	$rushinfo = C::t('forum_threadrush')->fetch($_G['tid']);
	if($rushinfo['creditlimit'] != -996) {
		$checkcreditsvalue = $_G['setting']['creditstransextra'][11] ? getuserprofile('extcredits'.$_G['setting']['creditstransextra'][11]) : $_G['member']['credits'];
		if($checkcreditsvalue < $rushinfo['creditlimit']) {
			$creditlimit_title = $_G['setting']['creditstransextra'][11] ? $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][11]]['title'] : lang('forum/misc', 'credit_total');
			showmessage('post_rushreply_creditlimit', '', array('creditlimit_title' => $creditlimit_title, 'creditlimit' => $rushinfo['creditlimit']));
		}
	}

}

if(!submitcheck('replysubmit', 0, $seccodecheck, $secqaacheck)) {

	$st_p = $_G['uid'].'|'.TIMESTAMP;
	dsetcookie('st_p', $st_p.'|'.md5($st_p.$_G['config']['security']['authkey']));

	if($thread['special'] == 2 && ((!isset($_GET['addtrade']) || $thread['authorid'] != $_G['uid']) && !$tradenum = C::t('forum_trade')->fetch_counter_thread_goods($_G['tid']))) {
		showmessage('trade_newreply_nopermission', NULL);
	}

	$language = lang('forum/misc');
	$noticeauthor = $noticetrimstr = '';
	if(isset($_GET['repquote']) && $_GET['repquote'] = intval($_GET['repquote'])) {
		$thaquote = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['repquote']);
		if(!($thaquote && ($thaquote['invisible'] == 0 || $thaquote['authorid'] == $_G['uid'] && $thaquote['invisible'] == -2))) {
			$thaquote = array();
		}
		if($thaquote['tid'] != $_G['tid']) {
			showmessage('reply_quotepost_error', NULL);
		}

		if(getstatus($thread['status'], 2) && $thaquote['authorid'] != $_G['uid'] && $_G['uid'] != $thread['authorid'] && $thaquote['first'] != 1 && !$_G['forum']['ismoderator']) {
			showmessage('reply_quotepost_error', NULL);
		}

		if(!($thread['price'] && !$thread['special'] && $thaquote['first'])) {
			$quotefid = $thaquote['fid'];
			$message = $thaquote['message'];

			if(strpos($message, '[/password]') !== FALSE) {
				$message = '';
			}

			if($_G['setting']['bannedmessages'] && $thaquote['authorid']) {
				$author = getuserbyuid($thaquote['authorid']);
				if(!$author['groupid'] || $author['groupid'] == 4 || $author['groupid'] == 5) {
					$message = $language['post_banned'];
				} elseif($thaquote['status'] & 1) {
					$message = $language['post_single_banned'];
				}
			}

			$time = dgmdate($thaquote['dateline']);
			$message = messagecutstr($message, 100);
			$message = implode("\n", array_slice(explode("\n", $message), 0, 3));

			$thaquote['useip'] = substr($thaquote['useip'], 0, strrpos($thaquote['useip'], '.')).'.x';
			if($thaquote['author'] && $thaquote['anonymous']) {
				$thaquote['author'] = lang('forum/misc', 'anonymoususer');
			} elseif(!$thaquote['author']) {
				$thaquote['author'] = lang('forum/misc', 'guestuser').' '.$thaquote['useip'];
			} else {
				$thaquote['author'] = $thaquote['author'];
			}

			$post_reply_quote = lang('forum/misc', 'post_reply_quote', array('author' => $thaquote['author'], 'time' => $time));
			$noticeauthormsg = dhtmlspecialchars($message);
			if(!defined('IN_MOBILE')) {
				$message = "[quote][size=2][url=forum.php?mod=redirect&goto=findpost&pid=$_GET[repquote]&ptid={$_G['tid']}][color=#999999]{$post_reply_quote}[/color][/url][/size]\n{$message}[/quote]";
			} else {
				$message = "[quote][color=#999999]{$post_reply_quote}[/color]\n[color=#999999]{$message}[/color][/quote]";
			}
			$quotemessage = discuzcode($message, 0, 0);
			$noticeauthor = dhtmlspecialchars(authcode('q|'.$thaquote['authorid'], 'ENCODE'));
			$noticetrimstr = dhtmlspecialchars($message);
			$message = '';
		}
		$reppid = $_GET['repquote'];

	} elseif(isset($_GET['reppost']) && $_GET['reppost'] = intval($_GET['reppost'])) {
		$thapost = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['reppost']);
		if(!($thapost && ($thapost['invisible'] == 0 || $thapost['authorid'] == $_G['uid'] && $thapost['invisible'] == -2))) {
			$thapost = array();
		}
		if($thapost['tid'] != $_G['tid']) {
			showmessage('targetpost_donotbelongto_thisthread', NULL);
		}

		$thapost['useip'] = substr($thapost['useip'], 0, strrpos($thapost['useip'], '.')).'.x';
		if($thapost['author'] && $thapost['anonymous']) {
			$thapost['author'] = '[color=Olive]'.lang('forum/misc', 'anonymoususer').'[/color]';
		} elseif(!$thapost['author']) {
			$thapost['author'] = '[color=Olive]'.lang('forum/misc', 'guestuser').'[/color] '.$thapost['useip'];
		} else {
			$thapost['author'] = '[color=Olive]'.$thapost['author'].'[/color]';
		}
		$quotemessage = discuzcode($message, 0, 0);
		$noticeauthormsg = dhtmlspecialchars(messagecutstr($thapost['message'], 100));
		$noticeauthor = dhtmlspecialchars(authcode('r|'.$thapost['authorid'], 'ENCODE'));
		$noticetrimstr = dhtmlspecialchars($message);
		$message = '';
		$reppid = $_GET['reppost'];
	}

	if(isset($_GET['addtrade']) && $thread['special'] == 2 && $_G['group']['allowposttrade'] && $thread['authorid'] == $_G['uid']) {
		$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
		$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
		$trade['expiration'] = $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
		$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
		$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
		$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));
	}

	if($thread['replies'] <= $_G['ppp']) {
		$postlist = array();
		if($thread['price'] > 0 && $thread['special'] == 0) {
			$postlist = C::t('forum_post')->fetch_all_by_tid('tid:'.$_G['tid'], $_G['tid'], true, 'DESC', 0, 0, 0, 0);
		} else {
			$postlist = C::t('forum_post')->fetch_all_by_tid('tid:'.$_G['tid'], $_G['tid'], true, 'DESC', 0, 0, null, 0);
		}
		if($_G['setting']['bannedmessages']) {
			$uids = array();
			foreach($postlist as $post) {
				$uids[] = $post['authorid'];
			}
			$users = C::t('common_member')->fetch_all($uids);
		}
		foreach($postlist as $k => $post) {

			$post['dateline'] = dgmdate($post['dateline'], 'u');

			if($_G['setting']['bannedmessages'] && ($post['authorid'] && (!$post['groupid'] || $post['groupid'] == 4 || $post['groupid'] == 5))) {
				$post['message'] = $language['post_banned'];
			} elseif($post['status'] & 1) {
				$post['message'] = $language['post_single_banned'];
			} else {
				$post['message'] = preg_replace("/\[hide=?\d*\](.*?)\[\/hide\]/is", "[b]$language[post_hidden][/b]", $post['message']);
				$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], $_G['forum']['allowimgcode'], $_G['forum']['allowhtml'], $_G['forum']['jammer']);
			}

			if($_G['setting']['bannedmessages']) {
				$post['groupid'] = $users[$post['authorid']]['groupid'];
			}

			$postlist[$k] = $post;
		}
	}
	unset($uids, $users);

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach(0);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
	}

	getgpc('infloat') ? include template('forum/post_infloat') : include template('forum/post');

} else {

	$modpost = C::m('forum_post', $_G['tid']);
	$bfmethods = $afmethods = array();


	$params = array(
		'subject' => $subject,
		'message' => $message,
		'special' => $special,
		'extramessage' => $extramessage,
		'bbcodeoff' => $_GET['bbcodeoff'],
		'smileyoff' => $_GET['smileyoff'],
		'htmlon' => $_GET['htmlon'],
		'parseurloff' => $_GET['parseurloff'],
		'usesig' => $_GET['usesig'],
		'isanonymous' => $_GET['isanonymous'],
		'noticetrimstr' => $_GET['noticetrimstr'],
		'noticeauthor' => $_GET['noticeauthor'],
		'from' => $_GET['from'],
		'sechash' => $_GET['sechash'],
		'geoloc' => diconv($_GET['geoloc'], 'UTF-8'),
	);


	if(!empty($_GET['trade']) && $thread['special'] == 2 && $_G['group']['allowposttrade']) {
		$bfmethods[] = array('class' => 'extend_thread_trade', 'method' => 'before_newreply');
	}




	$attentionon = empty($_GET['attention_add']) ? 0 : 1;
	$attentionoff = empty($attention_remove) ? 0 : 1;
	$bfmethods[] = array('class' => 'extend_thread_rushreply', 'method' => 'before_newreply');
	if($_G['group']['allowat']) {
		$bfmethods[] = array('class' => 'extend_thread_allowat', 'method' => 'before_newreply');
	}

	$bfmethods[] = array('class' => 'extend_thread_comment', 'method' => 'before_newreply');
	$modpost->attach_before_method('newreply', array('class' => 'extend_thread_filter', 'method' => 'before_newreply'));



	if($_G['group']['allowat']) {
		$afmethods[] = array('class' => 'extend_thread_allowat', 'method' => 'after_newreply');
	}


	$afmethods[] = array('class' => 'extend_thread_rushreply', 'method' => 'after_newreply');



		$afmethods[] = array('class' => 'extend_thread_comment', 'method' => 'after_newreply');



	if(helper_access::check_module('follow') && !empty($_GET['adddynamic'])) {
		$afmethods[] = array('class' => 'extend_thread_follow', 'method' => 'after_newreply');
	}


	if($thread['replycredit'] > 0 && $thread['authorid'] != $_G['uid'] && $_G['uid']) {
		$afmethods[] = array('class' => 'extend_thread_replycredit', 'method' => 'after_newreply');
	}


	if($special == 5) {
		$afmethods[] = array('class' => 'extend_thread_debate', 'method' => 'after_newreply');
	}



	$afmethods[] = array('class' => 'extend_thread_image', 'method' => 'after_newreply');



	if($special == 2 && $_G['group']['allowposttrade'] && $thread['authorid'] == $_G['uid']) {
		$afmethods[] = array('class' => 'extend_thread_trade', 'method' => 'after_newreply');
	}
	$afmethods[] = array('class' => 'extend_thread_filter', 'method' => 'after_newreply');





		if($_G['forum']['allowfeed']) {
			if($special == 2 && !empty($_GET['trade'])) {
				$modpost->attach_before_method('replyfeed', array('class' => 'extend_thread_trade', 'method' => 'before_replyfeed'));
				$modpost->attach_after_method('replyfeed', array('class' => 'extend_thread_trade', 'method' => 'after_replyfeed'));
			} elseif($special == 3 && $thread['authorid'] != $_G['uid']) {
				$modpost->attach_before_method('replyfeed', array('class' => 'extend_thread_reward', 'method' => 'before_replyfeed'));
			} elseif($special == 5 && $thread['authorid'] != $_G['uid']) {
				$modpost->attach_before_method('replyfeed', array('class' => 'extend_thread_debate', 'method' => 'before_replyfeed'));
			}
		}




	if(!isset($_GET['addfeed'])) {
		$space = array();
		space_merge($space, 'field_home');
		$_GET['addfeed'] = $space['privacy']['feed']['newreply'];
	}

	$modpost->attach_before_methods('newreply', $bfmethods);
	$modpost->attach_after_methods('newreply', $afmethods);

	$return = $modpost->newreply($params);
	$pid = $modpost->pid;

	if($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newreply_submit_end')) {
			$threadpluginclass->newreply_submit_end($_G['fid'], $_G['tid']);
		}

	}

	if($modpost->pid && !$modpost->param('modnewreplies')) {

		if(!empty($_GET['addfeed'])) {
			$modpost->replyfeed();
		}
	}


	if($modpost->param('modnewreplies')) {
		$url = "forum.php?mod=viewthread&tid=".$_G['tid'];
	} else {

		$antitheft = '';
		if(!empty($_G['setting']['antitheft']['allow']) && empty($_G['setting']['antitheft']['disable']['thread']) && empty($_G['forum']['noantitheft'])) {
			$sign = helper_antitheft::get_sign($_G['tid'], 'tid');
			if($sign) {
				$antitheft = '&_dsign='.$sign;
			}
		}

		$url = "forum.php?mod=viewthread&tid=".$_G['tid']."&pid=".$modpost->pid."&page=".$modpost->param('page')."$antitheft&extra=".$extra."#pid".$modpost->pid;
	}

	if(!isset($inspacecpshare)) {
		showmessage($return , $url, $modpost->param('showmsgparam'));
	}

}

?>