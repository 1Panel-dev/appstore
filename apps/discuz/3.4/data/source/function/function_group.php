<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_group.php 32367 2013-01-07 02:30:12Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function delgroupcache($fid, $cachearray) {
	C::t('forum_groupfield')->delete_by_type($cachearray, $fid);
}

function groupperm(&$forum, $uid, $action = '', $isgroupuser = '') {
	if($forum['status'] != 3 || $forum['type'] != 'sub') {
		return -1;
	}
	if(!empty($forum['founderuid']) && $forum['founderuid'] == $uid) {
		return 'isgroupuser';
	}
	$isgroupuser = empty($isgroupuser) && $isgroupuser !== false ? C::t('forum_groupuser')->fetch_userinfo($uid, $forum['fid']) : $isgroupuser;
	if($forum['ismoderator'] && !$isgroupuser) {
		return '';
	}
	if($forum['jointype'] < 0 && !$forum['ismoderator']) {
		return 1;
	}
	if(!$forum['gviewperm'] && !$isgroupuser) {
		return 2;
	}
	if($forum['jointype'] == 2 && (!$forum['gviewperm'] || $action == 'post') && !empty($isgroupuser['uid']) && $isgroupuser['level'] == 0) {
		return 3;
	}
	if($action == 'post' && !$isgroupuser) {
		return 4;
	}
	if(is_array($isgroupuser['level']) && $isgroupuser['level'] === 0) {
		return 5;
	}
	return $isgroupuser ? 'isgroupuser' : '';
}

function grouplist($orderby = 'displayorder', $fieldarray = array(), $num = 1, $fids = array(), $sort = 0, $getcount = 0, $grouplevel = array()) {
	$query = C::t('forum_forum')->fetch_all_for_grouplist($orderby, $fieldarray, $num, $fids, $sort, $getcount);
	if($getcount) {
		return $query;
	}
	$grouplist = array();
	foreach($query as $group) {
		$group['iconstatus'] = $group['icon'] ? 1 : 0;
		isset($group['icon']) && $group['icon'] = get_groupimg($group['icon'], 'icon');
		isset($group['banner']) && $group['banner'] = get_groupimg($group['banner']);
		$group['orderid'] = $orderid ? intval($orderid) : '';
		isset($group['dateline']) && $group['dateline'] = $group['dateline'] ? dgmdate($group['dateline'], 'd') : '';
		isset($group['lastupdate']) && $group['lastupdate'] = $group['lastupdate'] ? dgmdate($group['lastupdate'], 'd') : '';
		$group['level'] = !empty($grouplevel) ? intval($grouplevel[$group['fid']]) : 0;
		isset($group['description']) && $group['description'] = cutstr($group['description'], 130);
		$grouplist[$group['fid']] = $group;
		$orderid ++;
	}

	return $grouplist;
}

function mygrouplist($uid, $orderby = '', $fieldarray = array(), $num = 0, $start = 0, $ismanager = 0, $count = 0) {
	$uid = intval($uid);
	if(empty($uid)) {
		return array();
	}
	$groupfids = $grouplevel = array();
	$query = C::t('forum_groupuser')->fetch_all_group_for_user($uid, $count, $ismanager, $start, $num);
	if($count == 1) {
		return $query;
	}
	foreach($query as $group) {
		$groupfids[] = $group['fid'];
		$grouplevel[$group['fid']] = $group['level'];
	}
	if(empty($groupfids)) {
		return false;
	}
	$mygrouplist = grouplist($orderby, $fieldarray, $num, $groupfids, 0, 0, $grouplevel);

	return $mygrouplist;
}

function get_groupimg($imgname, $imgtype = '') {
	global $_G;
	$imgpath = $_G['setting']['attachurl'].'group/'.$imgname;
	if($imgname) {
		return $imgpath;
	} else {
		if($imgtype == 'icon') {
			return 'static/image/common/groupicon.gif';
		} else {
			return '';
		}
	}
}

function get_groupselect($fup = 0, $groupid = 0, $ajax = 1) {
	global $_G;
	loadcache('grouptype');
	$firstgroup = $_G['cache']['grouptype']['first'];
	$secondgroup = $_G['cache']['grouptype']['second'];
	$grouptypeselect = array('first' => '', 'second' => '');
	if($ajax) {
		$fup = intval($fup);
		$groupid = intval($groupid);
		foreach($firstgroup as $gid => $group) {
			$selected = $fup == $gid ? 'selected="selected"' : '';
			$grouptypeselect['first'] .= '<option value="'.$gid.'" '.$selected.'>'.$group['name'].'</option>';
		}

		if($fup && !empty($firstgroup[$fup]['secondlist'])) {
			foreach($firstgroup[$fup]['secondlist'] as $sgid) {
				$selected = $sgid == $groupid ? 'selected="selected"' : '';
				$grouptypeselect['second'] .= '<option value="'.$sgid.'" '.$selected.'>'.$secondgroup[$sgid]['name'].'</option>';
			}
		}
	} else {
		foreach($firstgroup as $gid => $group) {
			$gselected = $groupid == $gid ? 'selected="selected"' : '';
			$grouptypeselect .= '<option value="'.$gid.'" '.$gselected.'>'.$group['name'].'</option>';
			if(is_array($group['secondlist'])) {
				foreach($group['secondlist'] as $secondid) {
					$selected = $groupid == $secondid ? 'selected="selected"' : '';
					$grouptypeselect .= '<option value="'.$secondid.'" '.$selected.'>&nbsp;&nbsp;'.$secondgroup[$secondid]['name'].'</option>';
				}
			}
			$grouptypeselect .= '</optgroup>';
		}
	}
	return $grouptypeselect;
}

function get_groupnav($forum) {
	global $_G;
	if(empty($forum) || empty($forum['fid']) || empty($forum['name'])) {
		return '';
	}
	loadcache('grouptype');
	$groupnav = '';
	$groupsecond = $_G['cache']['grouptype']['second'];
	if($forum['type'] == 'sub') {
		$secondtype = !empty($groupsecond[$forum['fup']]) ? $groupsecond[$forum['fup']] : array();
	} else {
		$secondtype = !empty($groupsecond[$forum['fid']]) ? $groupsecond[$forum['fid']] : array();
	}
	$firstid = !empty($secondtype) ? $secondtype['fup'] : (!empty($forum['fup']) ? $forum['fup'] : $forum['fid']);
	$firsttype = $_G['cache']['grouptype']['first'][$firstid];
	if($firsttype) {
		$groupnav = ' <em>&rsaquo;</em> <a href="group.php?gid='.$firsttype['fid'].'">'.$firsttype['name'].'</a>';
	}
	if($secondtype) {
		$groupnav .= ' <em>&rsaquo;</em> <a href="group.php?sgid='.$secondtype['fid'].'">'.$secondtype['name'].'</a>';
	}
	if($forum['type'] == 'sub') {
		$mod_action = $_GET['mod'] == 'forumdisplay' || $_GET['mod'] == 'viewthread' ? 'mod=forumdisplay&action=list' : 'mod=group';
		$groupnav .= ($groupnav ? ' <em>&rsaquo;</em> ' : '').'<a href="forum.php?'.$mod_action.'&fid='.$forum['fid'].'">'.$forum['name'].'</a>';
	}
	return array('nav' => $groupnav, 'first' => $firsttype, 'second' => $secondtype);
}

function get_viewedgroup() {
	$groupviewed_list = $list = array();
	$groupviewed = getcookie('groupviewed');
	$groupviewed = $groupviewed ? explode(',', $groupviewed) : array();
	if($groupviewed) {
		$query = C::t('forum_forum')->fetch_all_info_by_fids($groupviewed);
		foreach($query as $row) {
			$icon = get_groupimg($row['icon'], 'icon');
			$list[$row['fid']] = array('fid' => $row['fid'], 'name' => $row['name'], 'icon' => $icon, 'membernum' => $row['membernum']);
		}
	}
	foreach($groupviewed as $fid) {
		$groupviewed_list[$fid] = $list[$fid];
	}
	return $groupviewed_list;
}

function getgroupthread($fid, $type, $timestamp = 0, $num = 10) {
	$typearray = array('replies', 'views', 'dateline', 'lastpost', 'digest', 'comments');
	$type = in_array($type, $typearray) ? $type : '';

	$groupthreadlist = array();
	if($type) {
		$dateline = $lastpost = $digest = null;
		if($timestamp && in_array($type, array('dateline', 'lastpost'))) {
			if($type == 'dateline') {
				$dateline = TIMESTAMP - $timestamp;
			} else {
				$lastpost = TIMESTAMP - $timestamp;
			}
		}
		if($type == 'digest') {
			$digest = 0;
			$type = 'dateline';
		}
		foreach(C::t('forum_thread')->fetch_all_group_thread_by_fid_displayorder($fid, 0, $dateline, $lastpost, $digest, $type, 0, $num) as $thread) {
			$groupthreadlist[$thread['tid']]['tid'] = $thread['tid'];
			$groupthreadlist[$thread['tid']]['subject'] = $thread['subject'];
			$groupthreadlist[$thread['tid']]['special'] = $thread['special'];
			$groupthreadlist[$thread['tid']]['closed'] = $thread['closed'];
			$groupthreadlist[$thread['tid']]['dateline'] = dgmdate($thread['dateline'], 'd');
			$groupthreadlist[$thread['tid']]['author'] = $thread['author'];
			$groupthreadlist[$thread['tid']]['authorid'] = $thread['authorid'];
			$groupthreadlist[$thread['tid']]['views'] = $thread['views'];
			$groupthreadlist[$thread['tid']]['replies'] = $thread['replies'];
			$groupthreadlist[$thread['tid']]['comments'] = $thread['comments'];
			$groupthreadlist[$thread['tid']]['lastpost'] = dgmdate($thread['lastpost'], 'u');
			$groupthreadlist[$thread['tid']]['lastposter'] = $thread['lastposter'];
			$groupthreadlist[$thread['tid']]['lastposterenc'] = rawurlencode($thread['lastposter']);
		}
	}

	return $groupthreadlist;
}

function getgroupcache($fid, $typearray = array(), $timestamp = 0, $num = 10, $privacy = 0, $force = 0) {
	$groupcache = array();

	if(!$force) {
		$query = C::t('forum_groupfield')->fetch_all_group_cache($fid, $typearray, $privacy);
		foreach($query as $group) {
			$groupcache[$group['type']] = dunserialize($group['data']);
			$groupcache[$group['type']]['dateline'] = $group['dateline'];
		}
	}

	$cachetimearray = array('replies' => 3600, 'views' => 3600, 'dateline' => 900, 'lastpost' => 3600, 'digest' => 86400, 'ranking' => 86400, 'activityuser' => 3600);
	$userdataarray = array('activityuser' => 'lastupdate', 'newuserlist' => 'joindateline');
	foreach($typearray as $type) {
		if(empty($groupcache[$type]) || (!empty($cachetimearray[$type]) && TIMESTAMP - $groupcache[$type]['dateline'] > $cachetimearray[$type])) {
			if($type == 'ranking') {
				$groupcache[$type]['data'] = getgroupranking($fid, $groupcache[$type]['data']['today']);
			} elseif(in_array($type, array('activityuser', 'newuserlist'))) {
				$num = $type == 'activityuser' ? 50 : 8;
				$groupcache[$type]['data'] = C::t('forum_groupuser')->groupuserlist($fid, $userdataarray[$type], $num, '', "AND level>'0'");
			} else {
				$groupcache[$type]['data'] = getgroupthread($fid, $type, $timestamp, $num);
			}
			if(!$force && $fid) {
				C::t('forum_groupfield')->insert(array('fid' => $fid, 'dateline' => TIMESTAMP, 'type' => $type, 'data' => serialize($groupcache[$type])), false, true);
			}
		}
	}

	return $groupcache;
}

function getgroupranking($fid = '', $nowranking = '') {
	$topgroup = $rankingdata = $topyesterday = array();
	$ranking = 1;
	$query = C::t('forum_forum')->fetch_all_group_for_ranking();
	foreach($query as $group) {
		$topgroup[$group['fid']] = $ranking++;
	}

	if($fid && $topgroup) {
		$rankingdata['yesterday'] = intval($nowranking);
		$rankingdata['today'] = intval($topgroup[$fid]);
		$rankingdata['trend'] = $rankingdata['yesterday'] ? grouptrend($rankingdata['yesterday'], $rankingdata['today']) : 0;
		$topgroup = $rankingdata;
	}

	return $topgroup;
}

function grouponline($fid, $getlist = '') {
	$fid = intval($fid);
	if(empty($getlist)) {
		$onlinemember = C::app()->session->count_by_fid($fid);
		$onlinemember['count'] = $onlinemember ? intval($onlinemember) : 0;
	} else {
		$onlinemember = array('count' => 0, 'list' => array());
		$onlinemember['list'] = C::app()->session->fetch_all_by_fid($fid);
		$onlinemember['count'] = count($onlinemember['list']);
	}
	return $onlinemember;
}

function grouptrend($yesterday, $today) {
	$trend = $yesterday - $today;
	return $trend;
}

function write_groupviewed($fid) {
	$fid = intval($fid);
	if($fid) {
		$groupviewed_limit = 8;
		$groupviewed = getcookie('groupviewed');
		if(!strexists(",$groupviewed,", ",$fid,")) {
			$groupviewed = $groupviewed ? explode(',', $groupviewed) : array();
			$groupviewed[] = $fid;
			if(count($groupviewed) > $groupviewed_limit) {
				array_shift($groupviewed);
			}
			dsetcookie('groupviewed', implode(',', $groupviewed), 86400);
		}
	}
}

function update_groupmoderators($fid) {
	if(empty($fid)) return false;
	$moderators = C::t('forum_groupuser')->groupuserlist($fid, 'level_join', 0, 0, array('level' => array('1', '2')), array('username', 'level'));
	if(!empty($moderators)) {
		C::t('forum_forumfield')->update($fid, array('moderators' => serialize($moderators)));
		return $moderators;
	} else {
		return array();
	}
}

function update_usergroups($uids) {
	global $_G;
	if(empty($uids)) return '';
	if(!is_array($uids)) $uids = array($uids);
	foreach($uids as $uid) {
		$groups = $grouptype = $usergroups = array();
		$fids = C::t('forum_groupuser')->fetch_all_fid_by_uids($uid);
		$query = C::t('forum_forum')->fetch_all_info_by_fids($fids);
		foreach($query as $group) {
			$groups[$group['fid']] = $group['name'];
			$typegroup[$group['fup']][] = $group['fid'];
		}
		if(!empty($typegroup)) {
			$fups = array_keys($typegroup);
			$query = C::t('forum_forum')->fetch_all_info_by_fids($fups);
			foreach($query as $fup) {
				$grouptype[$fup['fid']] = array('fid' => $fup['fid'], 'fup' => $fup['fup'], 'name' => $fup['name']);
				$grouptype[$fup['fid']]['groups'] = implode(',', $typegroup[$fup['fid']]);
			}
			$usergroups = array('groups' => $groups, 'grouptype' => $grouptype);
			if(!empty($usergroups)) {
				$setarr = array();
				$member = C::t('common_member_field_forum')->fetch($uid);
				$attentiongroups = $member['attentiongroup'];
				if($attentiongroups) {
					$attentiongroups = explode(',', $attentiongroups);
					$updateattention = 0;
					foreach($attentiongroups as $key => $val) {
						if(empty($usergroups['groups'][$val])) {
							unset($attentiongroups[$key]);
							$updateattention = 1;
						}
					}
					if($updateattention) {
						$setarr['attentiongroup'] = implode(',', $attentiongroups);
						C::t('common_member_field_forum')->update($uid, $setarr);
					}
					$_G['member']['attentiongroup'] = implode(',', $attentiongroups);
				}

			}
		} else {
		}
	}
	return $usergroups;
}
?>