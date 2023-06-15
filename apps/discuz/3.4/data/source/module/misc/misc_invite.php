<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_invite.php 33107 2013-04-26 03:43:21Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/friend');

$_GET['action'] = dhtmlspecialchars(preg_replace("/[^\[A-Za-z0-9_\]]/", '', $_GET['action']));
$friendgrouplist = friend_group_list();
if($_GET['action'] == 'group') {
	$id = intval($_GET['id']);
	$groupuserinfo = C::t('forum_groupuser')->fetch_userinfo($_G['uid'], $id);
	if(empty($groupuserinfo['uid'])) {
		showmessage('group_invite_failed');
	}
	$foruminfo = C::t('forum_forum')->fetch($id);
	$grouplevel = $foruminfo['level'];
	loadcache('grouplevels');
	$grouplevel = $_G['grouplevels'][$grouplevel];
	$membermaximum = $grouplevel['specialswitch']['membermaximum'];
	if(!empty($membermaximum)) {
		$curnum = C::t('forum_groupuser')->fetch_count_by_fid($id, -1);
		if($curnum >= $membermaximum) {
			showmessage('group_member_maximum', '', array('membermaximum' => $membermaximum));
		}
	}

	$groupname = $foruminfo['name'];
	$invitename = lang('group/misc', 'group_join', array('groupname' => $groupname));
	if(!submitcheck('invitesubmit')) {
		$friends = friend_list($_G['uid'], 100);
		if(!empty($friends)) {
			$frienduids = array_keys($friends);
			$inviteduids = array();
			$query = C::t('forum_groupinvite')->fetch_all_inviteuid($id, $frienduids, $_G['uid']);
			foreach($query as $inviteuser) {
				$inviteduids[$inviteuser['inviteuid']] = $inviteuser['inviteuid'];
			}
			$query = C::t('forum_groupuser')->fetch_all_userinfo($frienduids, $id);
			foreach($query as $inviteuser) {
				$inviteduids[$inviteuser['uid']] = $inviteuser['uid'];
			}
		}
		$inviteduids = !empty($inviteduids) ? implode(',', $inviteduids) : '';
	} else {
		$uids = $_GET['uids'];
		if($uids) {
			if(count($uids) > 20) {
				showmessage('group_choose_friends_max');
			}
			foreach(C::t('common_member')->fetch_all($uids, false, 0) as $uid => $user) {
				C::t('forum_groupinvite')->insert(array('fid' => $id, 'uid' => $_G['uid'], 'inviteuid' => $uid, 'dateline' => TIMESTAMP), true, true);
				$already = C::t('forum_groupinvite')->affected_rows();
				if($already == 1) {
					notification_add($uid, 'group', 'group_member_invite', array('groupname' => $groupname, 'fid' => $id, 'url' =>'forum.php?mod=group&action=join&fid='.$id, 'from_id' => $id, 'from_idtype' => 'invite_group'), 1);
				}
			}
			showmessage('group_invite_succeed', "forum.php?mod=group&fid=$id");
		} else {
			showmessage('group_invite_choose_member', "forum.php?mod=group&fid=$id");
		}
	}
} elseif($_GET['action'] == 'thread') {
	$inviteduids = array();
	$id = intval($_GET['id']);
	$thread = C::t('forum_thread')->fetch($id);
	$at = 0;
	$maxselect = 20;
	if(empty($_GET['activity'])) {
		$at = 1;
		$maxselect = 0;
		if($_G['group']['allowat']) {
			$atnum = 0;
			foreach(C::t('home_notification')->fetch_all_by_authorid_fromid($_G['uid'], $id, 'at') as $row) {
				$atnum ++;
				$inviteduids[$row[uid]] = $row['uid'];
			}
			$maxselect = $_G['group']['allowat'] - $atnum;
		} else {
			showmessage('noperm_at_user');
		}
		if($maxselect <= 0) {
			showmessage('thread_at_usernum_limit');
		}
		$invitename =  lang('forum/misc', 'at_invite');
	} else {
		$invitename =  lang('forum/misc', 'join_activity');
	}

	if(!submitcheck('invitesubmit')) {
		$inviteduids = !empty($inviteduids) ? implode(',', $inviteduids) : '';
	} else {
		$uids = $_GET['uids'];
		if($uids) {
			if(count($uids) > $maxselect) {
				showmessage('group_choose_friends_max');
			}
			$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($id);
			require_once libfile('function/post');
			$post['message'] = messagecutstr($post['message'], 150);
			foreach(C::t('common_member')->fetch_all($uids, false, 0) as $uid => $user) {
				if($at) {
					notification_add($uid, 'at', 'at_message', array('from_id' => $id, 'from_idtype' => 'at', 'buyerid' => $_G['uid'], 'buyer' => $_G['username'], 'tid' => $id, 'subject' => $thread['subject'], 'pid' => $post['pid'], 'message' => $post['message']));
				} else {
					notification_add($uid, 'thread', 'thread_invite', array('subject' => $thread['subject'], 'invitename' => $invitename, 'tid' => $id, 'from_id' => $id, 'from_idtype' => 'invite_thread'));
				}
			}
			showmessage(($at ? 'at_succeed' : 'group_invite_succeed'), "forum.php?mod=viewthread&tid=$id");
		} else {
			showmessage(($at ? 'at_choose_member' : 'group_invite_choose_member'), "forum.php?mod=viewthread&tid=$id");
		}
	}
} elseif($_GET['action'] == 'blog') {
	$id = intval($_GET['id']);
	$blog = C::t('home_blog')->fetch($id);

	if(!submitcheck('invitesubmit')) {
		$inviteduids = '';
	} else {
		$uids = $_GET['uids'];
		if($uids) {
			if(count($uids) > 20) {
				showmessage('group_choose_friends_max');
			}
			foreach(C::t('common_member')->fetch_all($uids, false, 0) as $uid => $user) {
				notification_add($uid, 'blog', 'blog_invite', array('subject' => $blog['subject'], 'uid' => $blog['uid'], 'blogid' => $id, 'from_id' => $id, 'from_idtype' => 'invite_blog'));
			}
			showmessage('group_invite_succeed', "home.php?mod=space&uid=".$blog['uid']."&do=blog&id=$id");
		} else {
			showmessage('group_invite_choose_member', "home.php?mod=space&uid=".$blog['uid']."&do=blog&id=$id");
		}
	}
} elseif($_GET['action'] == 'article') {
	$id = intval($_GET['id']);
	$article = C::t('portal_article_title')->fetch($id);

	if(!submitcheck('invitesubmit')) {
		$inviteduids = '';
	} else {
		require_once libfile('function/portal');
		$article_url = fetch_article_url($article);
		$uids = $_GET['uids'];
		if($uids) {
			if(count($uids) > 20) {
				showmessage('group_choose_friends_max');
			}
			foreach(C::t('common_member')->fetch_all($uids, false, 0) as $uid => $user) {
				notification_add($uid, 'article', 'article_invite', array('subject' => $article['title'], 'url' => $article_url, 'from_id' => $id, 'from_idtype' => 'invite_article'));
			}
			showmessage('group_invite_succeed', $article_url);
		} else {
			showmessage('group_invite_choose_member', $article_url);
		}
	}
}

include template('common/invite');
?>