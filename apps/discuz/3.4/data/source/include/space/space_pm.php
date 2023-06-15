<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_pm.php 33421 2013-06-09 03:30:16Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loaducenter();

$list = array();

$plid = empty($_GET['plid'])?0:intval($_GET['plid']);
$daterange = empty($_GET['daterange'])?0:intval($_GET['daterange']);
$touid = empty($_GET['touid'])?0:intval($_GET['touid']);
$opactives['pm'] = 'class="a"';

if(empty($_G['member']['category_num']['manage']) && !in_array($_G['adminid'], array(1,2,3))) {
	unset($_G['notice_structure']['manage']);
}

if($_GET['subop'] == 'view') {
	$type = $_GET['type'];
	$page = empty($_GET['page']) ? 0 : intval($_GET['page']);

	$chatpmmember = intval($_GET['chatpmmember']);
	$chatpmmemberlist = array();
	if($chatpmmember) {
		$chatpmmember = uc_pm_chatpmmemberlist($_G['uid'], $plid);
		if(!empty($chatpmmember)) {
			$authorid = $founderuid = $chatpmmember['author'];
			$chatpmmemberlist = C::t('common_member')->fetch_all($chatpmmember['member']);
			foreach(C::t('common_member_field_home')->fetch_all($chatpmmember['member']) as $uid => $member) {
				$chatpmmemberlist[$uid] = array_merge($member, $chatpmmemberlist[$uid]);
			}
		}
		require_once libfile('function/friend');
		$friendgrouplist = friend_group_list();
		$actives = array('chatpmmember'=>' class="a"');
	} else {
		if($touid) {
			$ols = array();
			if(defined('IN_MOBILE')) {
				$perpage = 5;
			} else {
				$perpage = 10;
			}
			$perpage = mob_perpage($perpage);
			if(!$daterange) {
				$member = getuserbyuid($touid);
				$tousername = $member['username'];
				unset($member);
				$count = uc_pm_view_num($_G['uid'], $touid, 0);
				if(!$page) {
					$page = ceil($count/$perpage);
				}
				$list = uc_pm_view($_G['uid'], 0, $touid, 5, ceil($count/$perpage)-$page+1, $perpage, 0, 0);
				$multi = pmmulti($count, $perpage, $page, "home.php?mod=space&do=pm&subop=view&touid=$touid");
			} else {
				showmessage('parameters_error');
			}
		} else {
			if(defined('IN_MOBILE')) {
				$perpage = 10;
			} else {
				$perpage = 50;
			}
			$perpage = mob_perpage($perpage);
			$count = uc_pm_view_num($_G['uid'], $plid, 1);
			if(!$daterange) {
				if(!$page) {
					$page = ceil($count/$perpage);
				}
				$list = uc_pm_view($_G['uid'], 0, $plid, 5, ceil($count/$perpage)-$page+1, $perpage, $type, 1);
				$multi = pmmulti($count, $perpage, $page, "home.php?mod=space&do=pm&subop=view&plid=$plid&type=$type");
			} else {
				$list = uc_pm_view($_G['uid'], 0, $plid, 5, ceil($count/$perpage)-$page+1, $perpage, $type, 1);
				$chatpmmember = uc_pm_chatpmmemberlist($_G['uid'], $plid);
				if(!empty($chatpmmember)) {
					$authorid = $founderuid = $chatpmmember['author'];
					$chatpmmemberlist = C::t('common_member')->fetch_all($chatpmmember['member']);
					foreach(C::t('common_member_field_home')->fetch_all($chatpmmember['member']) as $uid => $member) {
						$chatpmmemberlist[$uid] = array_merge($member, $chatpmmemberlist[$uid]);
					}
					foreach(C::app()->session->fetch_all_by_uid($chatpmmember['member']) as $value) {
						if(!$value['invisible']) {
							$ols[$value['uid']] = $value['lastactivity'];
						}
					}
				}
				$membernum = count($chatpmmemberlist);
				$subject = $list[0]['subject'];
				$refreshtime = $_G['setting']['chatpmrefreshtime'];

			}
		}
		$founderuid = empty($list)?0:$list[0]['founderuid'];
		$pmid = empty($list)?0:$list[0]['pmid'];
	}
	$actives['privatepm'] = ' class="a"';

} elseif($_GET['subop'] == 'viewg') {

	$grouppm = C::t('common_grouppm')->fetch($_GET['pmid']);
	if(!$grouppm) {
		$grouppm = array_merge((array)C::t('common_member_grouppm')->fetch($_G['uid'], $_GET['pmid']), $grouppm);
	}
	if($grouppm) {
		$grouppm['numbers'] = $grouppm['numbers'] - 1;
	}
	if(!$grouppm['status']) {
		C::t('common_member_grouppm')->update($_G['uid'], $_GET['pmid'], array('status' => 1, 'dateline' => TIMESTAMP));
	}
	$actives['announcepm'] = ' class="a"';

} elseif($_GET['subop'] == 'ignore') {

	$ignorelist = uc_pm_blackls_get($_G['uid']);
	$actives = array('ignore'=>' class="a"');

} elseif($_GET['subop'] == 'setting') {

	$actives = array('setting'=>' class="a"');
	$acceptfriendpmstatus = $_G['member']['onlyacceptfriendpm'] ? $_G['member']['onlyacceptfriendpm'] : ($_G['setting']['onlyacceptfriendpm'] ? 1 : 2);
	$ignorelist = uc_pm_blackls_get($_G['uid']);

} else {

	$filter = in_array($_GET['filter'], array('newpm', 'privatepm', 'announcepm')) ? $_GET['filter'] : 'privatepm';

	$perpage = 15;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;

	$grouppms = $gpmids = $gpmstatus = array();
	$newpm = $newpmcount = 0;

	if($filter == 'privatepm' && $page == 1 || $filter == 'announcepm' || $filter == 'newpm') {
		$announcepm  = 0;
		foreach(C::t('common_member_grouppm')->fetch_all_by_uid($_G['uid'], $filter == 'announcepm' ? 1 : 0) as $gpmid => $gpuser) {
			$gpmstatus[$gpmid] = $gpuser['status'];
			if($gpuser['status'] == 0) {
				$announcepm ++;
			}
		}
		$gpmids = array_keys($gpmstatus);
		if($gpmids) {
			foreach(C::t('common_grouppm')->fetch_all_by_id_authorid($gpmids) as $grouppm) {
				$grouppm['message'] = cutstr(strip_tags($grouppm['message']), 100, '');
				$grouppms[] = $grouppm;
			}
		}
	}

	if($filter == 'privatepm' || $filter == 'newpm') {
		$result = uc_pm_list($_G['uid'], $page, $perpage, 'inbox', $filter, 200);
		$count = $result['count'];
		$list = $result['data'];
	}

	if($filter == 'privatepm' && $page == 1 || $filter == 'newpm') {
		$newpmarr = uc_pm_checknew($_G['uid'], 1);
		$newpm = $newpmarr['newpm'];
	}
	$newpmcount = $newpm + $announcepm;
	if($_G['member']['newpm']) {
		C::t('common_member')->update($_G['uid'], array('newpm' => 0));
		uc_pm_ignore($_G['uid']);
	}
	$multi = multi($count, $perpage, $page, "home.php?mod=space&do=pm&filter=$filter", 0, 5);
	$actives = array($filter=>' class="a"');
}

if(!empty($list)) {
	$today = $_G['timestamp'] - ($_G['timestamp'] + $_G['setting']['timeoffset'] * 3600) % 86400;
	foreach ($list as $key => $value) {
		$value['lastsummary'] = str_replace('&amp;', '&', $value['lastsummary']);
		$value['lastsummary'] = preg_replace("/&[a-z]+\;/i", '', $value['lastsummary']);
		$value['daterange'] = 5;
		if($value['lastdateline'] >= $today) {
			$value['daterange'] = 1;
		} elseif($value['lastdateline'] >= $today - 86400) {
			$value['daterange'] = 2;
		} elseif($value['lastdateline'] >= $today - 172800) {
			$value['daterange'] = 3;
		} elseif($value['lastdateline'] >= $today - 604800) {
			$value['daterange'] = 4;
		}
		$list[$key] = $value;
	}
}
include_once template("diy:home/space_pm");

function pmmulti($count, $perpage, $curpage, $mpurl) {
	$return = '';
	$lang['next'] = lang('core', 'nextpage');
	$lang['prev'] = lang('core', 'prevpage');
	$next = $curpage < ceil($count/$perpage) ? '<a href="'.$mpurl.'&amp;page='.($curpage + 1).'#last" class="nxt">'.$lang['next'].'</a>' : '';
	$prev = $curpage > 1 ? '<span class="pgb"><a href="'.$mpurl.'&amp;page='.($curpage - 1).'#last">'.$lang['prev'].'</a></span>' : '';
	if($next || $prev) {
		$return = '<div class="pg">'.$prev.$next.'</div>';
	}
	return $return;
}

?>