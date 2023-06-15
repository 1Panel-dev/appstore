<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_ranklist_member.php 26628 2011-12-16 10:20:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
loadcache('usergroups');

$multi = $gettype = '';
$list = array();
$cachetip = TRUE;
$perpage = 20;
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
if($page < 1) {
	$page = 1;
}
$start = ($page - 1) * $perpage;

require_once libfile('function/home');
ckstart($start, $perpage);

$creditkey = $cache_name = '';
$fuids = array();
$count = 0;
$now_pos = 0;
$now_choose = '';

if ($_GET['view'] == 'credit') {

	$gettype = 'credit';
	$creditsrank_change = 1;
	$extcredits = $_G['setting']['extcredits'];
	$now_choose = $_GET['orderby'] && $extcredits[$_GET['orderby']] ? $_GET['orderby'] : 'all';
	if(!$_GET['orderby'] || !$extcredits[$_GET['orderby']]) {
		$_GET['orderby'] = 'all';
	}
	if($_G['uid']) {
		$mycredits = $now_choose == 'all' ? $_G['member']['credits'] : getuserprofile('extcredits'.$now_choose);
		$cookie_name = 'space_top_credit_'.$_G['uid'].'_'.$now_choose;
		if($_G['cookie'][$cookie_name]) {
			$now_pos = $_G['cookie'][$cookie_name];
		} else {
			if($now_choose == 'all') {
				$now_pos = C::t('common_member')->count_by_credits($mycredits);
			} else {
				$now_pos = C::t('common_member_count')->count_by_extcredits($now_choose, $mycredits);
			}
			$now_pos++;
			dsetcookie($cookie_name, $now_pos);
		}
	} else {
		$now_pos = -1;
	}
	$view = $_GET['view'];
	$orderby = $_GET['orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif ($_GET['view'] == 'friendnum') {

	$gettype = 'friend';
	if($_G['uid']) {
		$space = $_G['member'];
		space_merge($space, 'count');
		$cookie_name = 'space_top_'.$_GET['view'].'_'.$_G['uid'];
		if($_G['cookie'][$cookie_name]) {
			$now_pos = $_G['cookie'][$cookie_name];
		} else {
			$now_pos = C::t('common_member_count')->count_by_friends($space['friends']);
			$now_pos++;
			dsetcookie($cookie_name, $now_pos);
		}
	} else {
		$now_pos = -1;
	}
	$view = $_GET['view'];
	$orderby = $_GET['orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif ($_GET['view'] == 'invite') {

	$gettype = 'invite';
	$now_pos = -1;
	$inviterank_change = 1;
	$now_choose = 'thisweek';
	switch($_GET['orderby']) {
		case 'thismonth':
			$now_choose = 'thismonth';
			break;
		case 'today':
			$now_choose = 'today';
			break;
		case 'thisweek':
			$now_choose = 'thisweek';
			break;
		default :
			$now_choose = 'all';
	}
	$view = $_GET['view'];
	$orderby = $_GET['orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif($_GET['view'] == 'blog') {

	$gettype = 'blog';
	$now_pos = -1;
	$view = $_GET['view'];
	$orderby = $_GET['orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif($_GET['view'] == 'beauty') {

	$gettype = 'girl';
	$now_pos = -1;
	$view = $_GET['view'];
	$orderby = $_GET['orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif($_GET['view'] == 'handsome') {

	$gettype = 'boy';
	$now_pos = -1;
	$view = $_GET['view'];
	$orderby = $_GET['orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif($_GET['view'] == 'post') {

	$gettype = 'post';
	$postsrank_change = 1;
	$now_pos = -1;
	$now_choose = 'posts';
	switch($_GET['orderby']) {
		case 'digestposts':
			$now_choose = 'digestposts';
			break;
		case 'thismonth':
			$now_choose = 'thismonth';
			break;
		case 'today':
			$now_choose = 'today';
			break;
	}
	$view = $_GET['view'];
	$orderby = $_GET['orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif($_GET['view'] == 'onlinetime') {

	$gettype = 'onlinetime';
	$onlinetimerank_change = 1;
	$now_pos = -1;
	$now_choose = 'thismonth';
	switch($_GET['orderby']) {
		case 'thismonth':
			$now_choose = 'thismonth';
			break;
		case 'all':
			$now_choose = 'all';
			break;
		default :
			$_GET['orderby'] = 'thismonth';
	}

	$view = $_GET['view'];
	$orderby = $_GET['orderby'];
	$list = getranklistdata($type, $view, $orderby);

} else {
	if(!$ranklist_setting['membershow']) {
		header('Location: misc.php?mod=ranklist&type=member&view=beauty');
	}
	$announcement = $ranklist_setting['membershowannouncement'];
	$gettype = 'bid';
	$cachetip = FALSE;
	$_GET['view'] = 'show';
	$creditid = 0;
	if($_G['setting']['creditstransextra'][6]) {
		$creditid = intval($_G['setting']['creditstransextra'][6]);
		$creditkey = 'extcredits'.$creditid;
	} elseif ($_G['setting']['creditstrans']) {
		$creditid = intval($_G['setting']['creditstrans']);
		$creditkey = 'extcredits'.$creditid;
	}
	$extcredits = $_G['setting']['extcredits'];
	$count = C::t('home_show')->count_by_credit();
	$space = array();
	if($count) {
		$space = $_G['member'];
		space_merge($space, 'count');
		$space['credit'] = empty($creditkey) ? 0 : $space[$creditkey];

		$myshowinfo = C::t('home_show')->fetch_by_uid_credit($space['uid']); //DB::fetch_first("SELECT unitprice, credit FROM ".DB::table('home_show')." WHERE uid='$space[uid]' AND credit>0");
		$myallcredit = intval($myshowinfo['credit']);
		$space['unitprice'] = intval($myshowinfo['unitprice']);
		$now_pos = C::t('home_show')->count_by_credit($space['unitprice']);//DB::result_first("SELECT COUNT(*) FROM ".DB::table('home_show')." WHERE unitprice>='$space[unitprice]' AND credit>0");

		$deluser = false;
		$query = C::t('home_show')->fetch_all_by_unitprice($start, $perpage);
		foreach ($query as $value) {
			if(!$deluser && $value['show_credit'] < 1) {
				$deluser = true;
			} else {
				$list[$value['uid']] = $value;
			}
		}
		if($deluser) {
			C::t('home_show')->delete_by_credit(1);
		}
		$multi = multi($count, $perpage, $page, "misc.php?mod=ranklist&type=member&view=$_GET[view]");
	}
}

if($cachetip) {
	$lastupdate = $_G['lastupdate'];
	$nextupdate = $_G['nextupdate'];
}

$myfuids =array();
$query = C::t('home_friend')->fetch_all($_G['uid']);
foreach($query as $value) {
	$myfuids[$value['fuid']] = $value['fuid'];
}
$myfuids[$_G['uid']] = $_G['uid'];

$i = $_GET['page'] ? ($_GET['page']-1)*$perpage+1 : 1;
foreach($list as $key => $value) {
	$fuids[] = $value['uid'];
	if(isset($value['lastactivity'])) $value['lastactivity'] = dgmdate($value['lastactivity'], 't');
	$value['isfriend'] = empty($myfuids[$value['uid']])?0:1;
	$list[$key] = $value;
	$list[$key]['rank'] = $i;
	$i++;
}

$ols = array();
if($fuids) {
	foreach(C::app()->session->fetch_all_by_uid($fuids) as $value) {
		if(!$value['invisible']) {
			$ols[$value['uid']] = $value['lastactivity'];
		} elseif ($_GET['view'] == 'online' && $list[$value['uid']]) {
			unset($list[$value['uid']]);
		}
	}
}

$a_actives = array($_GET['view'] => ' class="a"');

$navname = $_G['setting']['navs'][8]['navname'];
$navtitle = lang('ranklist/navtitle', 'ranklist_title_member_'.$gettype).' - '.$navname;
$metakeywords = lang('ranklist/navtitle', 'ranklist_title_member_'.$gettype);
$metadescription = lang('ranklist/navtitle', 'ranklist_title_member_'.$gettype);

include template('diy:ranklist/member');

?>