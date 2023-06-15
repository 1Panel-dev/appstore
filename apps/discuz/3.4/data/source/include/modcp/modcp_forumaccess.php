<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_forumaccess.php 26544 2011-12-15 02:19:09Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

$list = $logids = array();

include_once(libfile('function/forumlist'));
$forumlistall = forumselect(false, false, $_G['fid']);

$adderror = $successed = 0;
$new_user = isset($_GET['new_user']) ? trim($_GET['new_user']) : '';

if($_G['fid'] && $_G['forum']['ismoderator'] && $new_user != '' && submitcheck('addsubmit')) {
	$deleteaccess = isset($_GET['deleteaccess']) ? 1 : 0;
	foreach (array('view', 'post', 'reply', 'getattach', 'getimage', 'postattach', 'postimage') as $key) {
		${'new_'.$key} = isset($_GET['new_'.$key]) ? intval($_GET['new_'.$key]) : '';
	}

	if($new_user != '') {

		$user = C::t('common_member')->fetch_by_username($new_user);
		$uid = $user['uid'];

		if(empty($user)) {
			$adderror = 1;
		} elseif($user['adminid'] && $_G['adminid'] != 1) {
			$adderror = 2;
		} else {

			$access = C::t('forum_access')->fetch_all_by_fid_uid($_G['fid'], $uid);
			$access = $access[0];

			if($deleteaccess) {

				if($access && $_G['adminid'] != 1 && inwhitelist($access)) {
					$adderror = 3;
				} else {
					$successed = true;
					$access && delete_access($uid, $_G['fid']);
				}

			} elseif($new_view || $new_post || $new_reply || $new_getattach || $new_getimage || $new_postattach || $new_postimage) {

				if($new_view == -1) {
					$new_view = $new_post = $new_reply = $new_getattach = $new_getimage = $new_postattach = $new_postimage = -1;
				} else {
					$new_view = 0;
					$new_post = $new_post ? -1 : 0;
					$new_reply = $new_reply ? -1 : 0;
					$new_getattach = $new_getattach ? -1 : 0;
					$new_getimage = $new_getimage ? -1 : 0;
					$new_postattach = $new_postattach ? -1 : 0;
					$new_postimage = $new_postimage ? -1 : 0;
				}

				if(empty($access)) {
					$successed = true;
					$data = array('uid' => $uid, 'fid' => $_G['fid'], 'allowview' => $new_view, 'allowpost' => $new_post, 'allowreply' => $new_reply,
						'allowgetattach' => $new_getattach, 'allowgetimage' => $new_getimage,
						'allowpostattach' => $new_postattach, 'allowpostimage' => $new_postimage,
						'adminuser' => $_G['uid'], 'dateline' => $_G['timestamp']);
					C::t('forum_access')->insert($data);
					C::t('common_member')->update($uid, array('accessmasks' => 1), 'UNBUFFERED');

				} elseif($new_view == -1 && $access['allowview'] == 1 && $_G['adminid'] != 1) {
					$adderror = 3;
				} else {
					if($_G['adminid'] > 1) {
						$new_view = $access['allowview'] == 1 ? 1 : $new_view;
						$new_post = $access['allowpost'] == 1 ? 1 : $new_post;
						$new_reply = $access['allowreply'] == 1 ? 1 : $new_reply;
						$new_getattach = $access['allowgetattach'] == 1 ? 1 : $new_getattach;
						$new_getimage = $access['allowgetimage'] == 1 ? 1 : $new_getimage;
						$new_postattach = $access['postattach'] == 1 ? 1 : $new_postattach;
						$new_postimage = $access['postimage'] == 1 ? 1 : $new_postimage;
					}
					$successed = true;
					$data = array('allowview' => $new_view, 'allowpost' => $new_post, 'allowreply' => $new_reply,
						'allowgetattach' => $new_getattach, 'allowgetimage' => $new_getimage,
						'allowpostattach' => $new_postattach, 'allowpostimage' => $new_postimage,
						'adminuser' => $_G['uid'], 'dateline' => $_G['timestamp']);
					C::t('forum_access')->update_for_uid($uid, $_G['fid'], $data);
					C::t('common_member')->update($uid, array('accessmasks' => 1), 'UNBUFFERED');

				}
			}
		}
	}

	$new_user = $adderror ? $new_user : '';
}

$new_user = dhtmlspecialchars($new_user);
$suser = isset($_GET['suser']) ? trim($_GET['suser']) : '';
if(submitcheck('searchsubmit')) {
	if($suser != '') {
		$suid = C::t('common_member')->fetch_uid_by_username($suser);
	}
}
$suser = dhtmlspecialchars($suser);

$page = max(1, intval($_G['page']));
$ppp = 10;
$list = array('pagelink' => '', 'data' => array());

if($num = C::t('forum_access')->fetch_all_by_fid_uid($_G['fid'], $suid, 1)) {

	$page = $page > ceil($num / $ppp) ? ceil($num / $ppp) : $page;
	$start_limit = ($page - 1) * $ppp;
	$list['pagelink'] = multi($num, $ppp, $page, "forum.php?mod=modcp&fid=$_G[fid]&action=$_GET[action]");

	$query = C::t('forum_access')->fetch_all_by_fid_uid($_G['fid'], $suid, 0, $start_limit, $ppp);
	$uidarray = array();
	foreach($query as $access) {
		$uidarray[$access['uid']] = $access['uid'];
		$uidarray[$access['adminuser']] = $access['adminuser'];
		$access['allowview'] = accessimg($access['allowview']);
		$access['allowpost'] = accessimg($access['allowpost']);
		$access['allowreply'] = accessimg($access['allowreply']);
		$access['allowpostattach'] = accessimg($access['allowpostattach']);
		$access['allowgetattach'] = accessimg($access['allowgetattach']);
		$access['allowgetimage'] = accessimg($access['allowgetimage']);
		$access['allowpostimage'] = accessimg($access['allowpostimage']);
		$access['dateline'] = dgmdate($access['dateline'], 'd');
		$access['forum'] = '<a href="forum.php?mod=forumdisplay&fid='.$access['fid'].'" target="_blank">'.strip_tags($_G['cache']['forums'][$access['fid']]['name']).'</a>';
		$list['data'][] = $access;
	}

	$users = array();
	if($uids = dimplode($uidarray)) {
		$users = C::t('common_member')->fetch_all_username_by_uid($uidarray);
	}
}

function delete_access($uid, $fid) {
	C::t('forum_access')->delete_by_fid($fid, $uid);
	$mask = C::t('forum_access')->count_by_uid($uid);
	if(!$mask) {
		C::t('common_member')->update($uid, array('accessmasks' => ''), 'UNBUFFERED');
	}
}

function accessimg($access) {
	return $access == -1 ? '<img src="'.STATICURL.'image/common/access_disallow.gif" />' :
		($access == 1 ? '<img src="'.STATICURL.'image/common/access_allow.gif" />' : '<img src="'.STATICURL.'image/common/access_normal.gif" />');
}

function inwhitelist($access) {
	$return = false;
	foreach (array('allowview', 'allowpost', 'allowreply', 'allowpostattach', 'allowgetattach', 'allowgetimage') as $key) {
		if($access[$key] == 1) {
			$return = true;
			break;
		}
	}
	return $return;
}

?>