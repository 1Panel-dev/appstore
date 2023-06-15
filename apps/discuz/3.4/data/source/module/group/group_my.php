<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group_my.php 30630 2012-06-07 07:16:14Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_G['mnid'] = 'mn_group';
if(!$_G['uid']) {
	showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
}
require_once libfile('function/group');

$view = $_GET['view'] && in_array($_GET['view'], array('manager', 'join', 'groupthread', 'mythread')) ? $_GET['view'] : 'groupthread';
$actives = array('manager' => '', 'join' => '', 'groupthread' => '', 'mythread' => '');
$actives[$view] = ' class="a"';

$perpage = 20;
$page = intval($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $perpage;
if($view == 'groupthread' || $view == 'mythread') {
	$typeid = intval($_GET['typeid']);
	$attentiongroups = $usergroups = array();
	$usergroups = update_usergroups($_G['uid']);
	if($view == 'groupthread' && empty($typeid) && !empty($usergroups['grouptype'])) {
		$attentiongroup = $_G['member']['attentiongroup'];
		if(empty($attentiongroup)) {
			$attentiongroups = array_slice(array_keys($usergroups['groups']), 0, 1);
		} else {
			$attentiongroups = explode(',', $attentiongroup);
		}
		$attentionthread = $attentiongroup_icon = array();
		$attentiongroupfid = '';
		$query = C::t('forum_forum')->fetch_all_info_by_fids($attentiongroups);
		foreach($query as $row) {
			$attentiongroup_icon[$row[fid]] = get_groupimg($row['icon'], 'icon');
		}
		foreach($attentiongroups as $groupid) {
			$attentiongroupfid .= $attentiongroupfid ? ','.$groupid : $groupid;
			if($page == 1) {
				foreach(C::t('forum_thread')->fetch_all_by_fid_displayorder($groupid, 0, null, null, 0, 5, 'lastpost', 'DESC', '=') as $thread) {
					$attentionthread[$groupid][$thread['tid']]['fid'] = $thread['fid'];
					$attentionthread[$groupid][$thread['tid']]['subject'] = $thread['subject'];
					$attentionthread[$groupid][$thread['tid']]['groupname'] = $usergroups['groups'][$thread['fid']];
					$attentionthread[$groupid][$thread['tid']]['views'] =  $thread['views'];
					$attentionthread[$groupid][$thread['tid']]['replies'] =  $thread['replies'];
					$attentionthread[$groupid][$thread['tid']]['lastposter'] =  $thread['lastposter'];
					$attentionthread[$groupid][$thread['tid']]['lastpost'] = dgmdate($thread['lastpost'], 'u');
					$attentionthread[$groupid][$thread['tid']]['folder'] = 'common';
					if(empty($_G['cookie']['oldtopics']) || strpos($_G['cookie']['oldtopics'], 'D'.$thread['tid'].'D') === FALSE) {
						$attentionthread[$groupid][$thread['tid']]['folder'] = 'new';
					}
				}
			}
		}
	}

	$mygrouplist = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), 50);
	if($mygrouplist) {
		$managegroup = $commongroup = $groupthreadlist = array();
		foreach($mygrouplist as $fid => $group) {
			if($group['level'] == 1 || $group['level'] == 2) {
				if(count($managegroup) == 8) {
					continue;
				}
				$managegroup[$fid]['name'] = $group['name'];
				$managegroup[$fid]['icon'] = $group['icon'];
			} else {
				if(count($commongroup) == 8) {
					continue;
				}
				$commongroup[$fid]['name'] = $group['name'];
				$commongroup[$fid]['icon'] = $group['icon'];
			}
		}

		$mygroupfid = array_keys($mygrouplist);
		if($typeid && !empty($usergroups['grouptype'][$typeid]['groups'])) {
			$mygroupfid = explode(',', $usergroups['grouptype'][$typeid]['groups']);
			$typeurl = '&typeid='.$typeid;
		} else {
			$typeid = 0;
		}
		if(!empty($attentiongroupfid) && !empty($mygroupfid)) {
			$mygroupfid = array_diff($mygroupfid, explode(',', $attentiongroupfid));
		}
		if($mygroupfid) {
			$lastpost = 0;
			$displayorder = null;
			if($view != 'mythread') {
				$displayorder = 0;
				$lastpost = TIMESTAMP - 86400*30;
			}
			$authorid = $_G['uid'];
			foreach(C::t('forum_thread')->fetch_all_by_fid_authorid_displayorder($mygroupfid, $authorid, $displayorder, $lastpost, $start, $perpage) as $thread) {
				$groupthreadlist[$thread['tid']]['fid'] = $thread['fid'];
				$groupthreadlist[$thread['tid']]['subject'] = $thread['subject'];
				$groupthreadlist[$thread['tid']]['groupname'] = $mygrouplist[$thread['fid']]['name'];
				$groupthreadlist[$thread['tid']]['views'] =  $thread['views'];
				$groupthreadlist[$thread['tid']]['replies'] =  $thread['replies'];
				$groupthreadlist[$thread['tid']]['lastposter'] =  $thread['lastposter'];
				$groupthreadlist[$thread['tid']]['lastpost'] = dgmdate($thread['lastpost'], 'u');
				$groupthreadlist[$thread['tid']]['folder'] = 'common';
				if(empty($_G['cookie']['oldtopics']) || strpos($_G['cookie']['oldtopics'], 'D'.$thread['tid'].'D') === FALSE) {
					$groupthreadlist[$thread['tid']]['folder'] = 'new';
				}
			}
			if($view == 'mythread') {
				$multipage = simplepage(count($groupthreadlist), $perpage, $page, 'group.php?mod=my&view='.$view.$typeurl);
			}
		}
	}
} elseif($view == 'manager' || $view == 'join') {
	$perpage = 40;
	$start = ($page - 1) * $perpage;
	$ismanager = $view == 'manager' ? 1 : 2;
	$num = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), 0, 0, $ismanager, 1);
	$multipage = multi($num, $perpage, $page, 'group.php?mod=my&view='.$view);
	$grouplist = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), $perpage, $start, $ismanager);
}

$frienduidarray = $friendgrouplist = $randgroupdata = $randgrouplist = $randgroup = array();
loadcache('groupindex');
$randgroupdata = $_G['cache']['groupindex']['randgroupdata'];
if($randgroupdata) {
	foreach($randgroupdata as $groupid => $rgroup) {
		if($rgroup['iconstatus']) {
			$randgrouplist[$groupid] = $rgroup;
		}
	}
}

if(count($randgrouplist) > 9) {
	foreach(array_rand($randgrouplist, 9) as $fid) {
		$randgroup[] = $randgrouplist[$fid];
	}
} elseif (count($randgrouplist)) {
	$randgroup = $randgrouplist;
}

require_once libfile('function/friend');
$frienduid = friend_list($_G['uid'], 50);
if($frienduid && is_array($frienduid)) {
	foreach($frienduid as $friend) {
		$frienduidarray[] = $friend['fuid'];
	}
	$fids = C::t('forum_groupuser')->fetch_all_fid_by_uids($frienduidarray);
	$query = C::t('forum_forum')->fetch_all_info_by_fids($fids, 0, 9);
	foreach($query as $group) {
		$icon = get_groupimg($group['icon'], 'icon');
		$friendgrouplist[$group['fid']] = array('fid' => $group['fid'], 'name' => $group['name'], 'icon' => $icon);
	}
}

$navtitle = $_G['username'].lang('core', 'title_of').$_G['setting']['navs'][3]['navname'];

include_once template("diy:group/group_my");

?>