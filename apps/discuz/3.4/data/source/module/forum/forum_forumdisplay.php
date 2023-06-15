<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_forumdisplay.php 36328 2016-12-26 00:38:47Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');

if($_G['forum']['redirect']) {
	dheader("Location: {$_G[forum][redirect]}");
} elseif($_G['forum']['type'] == 'group') {
	dheader("Location: forum.php?gid=$_G[fid]");
} elseif(empty($_G['forum']['fid'])) {
	showmessage('forum_nonexistence', NULL);
} elseif($_G['fid'] == $_G['setting']['followforumid'] && $_G['adminid'] != 1) {
	dheader("Location: home.php?mod=follow");
}

$st_t = $_G['uid'].'|'.TIMESTAMP;
dsetcookie('st_t', $st_t.'|'.md5($st_t.$_G['config']['security']['authkey']));

$_G['action']['fid'] = $_G['fid'];

$_GET['specialtype'] = isset($_GET['specialtype']) ? $_GET['specialtype'] : '';
$_GET['dateline'] = isset($_GET['dateline']) ? intval($_GET['dateline']) : 0;
$_GET['digest'] = isset($_GET['digest']) ? 1 : '';
$_GET['archiveid'] = isset($_GET['archiveid']) ? intval($_GET['archiveid']) : 0;

$showoldetails = isset($_GET['showoldetails']) ? $_GET['showoldetails'] : '';
switch($showoldetails) {
	case 'no': dsetcookie('onlineforum', ''); break;
	case 'yes': dsetcookie('onlineforum', 1, 31536000); break;
}

if(!isset($_G['cookie']['atarget'])) {
	if($_G['setting']['targetblank']) {
		dsetcookie('atarget', 1, 2592000);
		$_G['cookie']['atarget'] = 1;
	}
}

$_G['forum']['name'] = strip_tags($_G['forum']['name']) ? strip_tags($_G['forum']['name']) : $_G['forum']['name'];
$_G['forum']['extra'] = empty($_G['forum']['extra']) ? array() : dunserialize($_G['forum']['extra']);
if(!is_array($_G['forum']['extra'])) {
	$_G['forum']['extra'] = array();
}


$threadtable_info = !empty($_G['cache']['threadtable_info']) ? $_G['cache']['threadtable_info'] : array();
$forumarchive = array();
if($_G['forum']['archive']) {
	foreach(C::t('forum_forum_threadtable')->fetch_all_by_fid($_G['fid']) as $archive) {
		$forumarchive[$archive['threadtableid']] = array(
			'displayname' => dhtmlspecialchars($threadtable_info[$archive['threadtableid']]['displayname']),
			'threads' => $archive['threads'],
			'posts' => $archive['posts'],
		);
		if(empty($forumarchive[$archive['threadtableid']]['displayname'])) {
			$forumarchive[$archive['threadtableid']]['displayname'] = lang('forum/thread', 'forum_archive').' '.$archive['threadtableid'];
		}
	}
}

$forum_up = $_G['cache']['forums'][$_G['forum']['fup']];
if($_G['forum']['type'] == 'forum') {
	$fgroupid = $_G['forum']['fup'];
	if(empty($_GET['archiveid'])) {
		$navigation = ' <em>&rsaquo;</em> <a href="forum.php?gid='.$forum_up['fid'].'">'.$forum_up['name'].'</a><em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$_G['forum']['fid'].'">'.$_G['forum']['name'].'</a>';
	} else {
		$navigation = ' <em>&rsaquo;</em> '.'<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a> <em>&rsaquo;</em> '.$forumarchive[$_GET['archiveid']]['displayname'];
	}
	$seodata = array('forum' => $_G['forum']['name'], 'fgroup' => $forum_up['name'], 'page' => intval($_GET['page']));
} else {
	$fgroupid = $forum_up['fup'];
	if(empty($_GET['archiveid'])) {
		$forum_top =  $_G['cache']['forums'][$forum_up[fup]];
		$navigation = ' <em>&rsaquo;</em> <a href="forum.php?gid='.$forum_top['fid'].'">'.$forum_top['name'].'</a><em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$forum_up['fid'].'">'.$forum_up['name'].'</a><em>&rsaquo;</em> '.$_G['forum']['name'];
	} else {
		$navigation = ' <em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$_G['forum']['fup'].'">'.$forum_up['name'].'</a> <em>&rsaquo;</em> '.'<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a> <em>&rsaquo;</em> '.$forumarchive[$_GET['archiveid']]['displayname'];
	}
	$seodata = array('forum' => $_G['forum']['name'], 'fup' => $forum_up['name'], 'fgroup' => $forum_top['name'], 'page' => intval($_GET['page']));
}

$rssauth = $_G['rssauth'];

$forumseoset = array(
	'seotitle' => $_G['forum']['seotitle'],
	'seokeywords' => $_G['forum']['keywords'],
	'seodescription' => $_G['forum']['seodescription']
);

$seotype = 'threadlist';
if($_G['forum']['status'] == 3) {
	$navtitle = helper_seo::get_title_page($_G['forum']['name'], $_G['page']).' - '.$_G['setting']['navs'][3]['navname'];
	$metakeywords = $_G['forum']['metakeywords'];
	$metadescription = $_G['forum']['description'];
	if($_G['forum']['level'] == -1) {
		showmessage('group_verify', '', array(), array('alert' => 'info'));
	}
	$_G['seokeywords'] = $_G['setting']['seokeywords']['group'];
	$_G['seodescription'] = $_G['setting']['seodescription']['group'];
	$action = getgpc('action') ? $_GET['action'] : 'list';
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid']);
	if($status == -1) {
		showmessage('forum_not_group', 'group.php');
	} elseif($status == 1) {
		showmessage('forum_group_status_off');
	} elseif($status == 2) {
		showmessage('forum_group_noallowed', 'forum.php?mod=group&fid='.$_G['fid']);
	} elseif($status == 3) {
		showmessage('forum_group_moderated', 'forum.php?mod=group&fid='.$_G['fid']);
	}
	$_G['forum']['icon'] = get_groupimg($_G['forum']['icon'], 'icon');
	$_G['grouptypeid'] = $_G['forum']['fup'];
	$_G['forum']['dateline'] = dgmdate($_G['forum']['dateline'], 'd');

	$nav = get_groupnav($_G['forum']);
	$groupnav = $nav['nav'];
	$onlinemember = grouponline($_G['fid']);
	$groupmanagers = $_G['forum']['moderators'];
	$groupcache = getgroupcache($_G['fid'], array('replies', 'views', 'digest', 'lastpost', 'ranking', 'activityuser', 'newuserlist'));
	$seotype = 'grouppage';
	$seodata['first'] = $nav['first']['name'];
	$seodata['second'] = $nav['second']['name'];
	$seodata['gdes'] = $_G['forum']['description'];
	$forumseoset = array();
}
$_G['forum']['banner'] = get_forumimg($_G['forum']['banner']);

list($navtitle, $metadescription, $metakeywords) = get_seosetting($seotype, $seodata, $forumseoset);

if(!$navtitle) {
	$navtitle = helper_seo::get_title_page($_G['forum']['name'], $_G['page']);
	$nobbname = false;
} else {
	$nobbname = true;
}
$_GET['typeid'] = intval($_GET['typeid']);
if(!empty($_GET['typeid']) && !empty($_G['forum']['threadtypes']['types'][$_GET['typeid']])) {
	$navtitle = strip_tags($_G['forum']['threadtypes']['types'][$_GET['typeid']]).' - '.$navtitle;
}

$rsshead = $_G['setting']['rssstatus'] ? ('<link rel="alternate" type="application/rss+xml" title="'.$_G['setting']['bbname'].' - '.$navtitle.'" href="'.$_G['siteurl'].'forum.php?mod=rss&fid='.$_G['fid'].'&amp;auth='.$rssauth."\" />\n") : '';

if(!$metakeywords) {
	$metakeywords = $_G['forum']['name'];
}
if(!$metadescription) {
	$metadescription = $_G['forum']['name'];
}
if($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm']) && !$_G['forum']['allowview']) {
	showmessagenoperm('viewperm', $_G['fid'], $_G['forum']['formulaperm']);
} elseif($_G['forum']['formulaperm']) {
	formulaperm($_G['forum']['formulaperm']);
}

if($_G['forum']['password']) {
	if($_GET['action'] == 'pwverify') {
		if($_GET['pw'] != $_G['forum']['password']) {
			showmessage('forum_passwd_incorrect', NULL);
		} else {
			dsetcookie('fidpw'.$_G['fid'], $_GET['pw']);
			showmessage('forum_passwd_correct', "forum.php?mod=forumdisplay&fid=$_G[fid]");
		}
	} elseif($_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
		include template('forum/forumdisplay_passwd');
		exit();
	}
}

if($_G['forum']['price'] && !$_G['forum']['ismoderator']) {
	$membercredits = C::t('common_member_forum_buylog')->get_credits($_G['uid'], $_G['fid']);
	$paycredits = $_G['forum']['price'] - $membercredits;
	if($paycredits > 0) {
		if($_GET['action'] == 'paysubmit') {
			updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][1] => -$paycredits), 1, 'FCP', $_G['fid']);
			C::t('common_member_forum_buylog')->update_credits($_G['uid'], $_G['fid'], $_G['forum']['price']);
			showmessage('forum_pay_correct', "forum.php?mod=forumdisplay&fid=$_G[fid]");
		} else {
			if(getuserprofile('extcredits'.$_G['setting']['creditstransextra'][1]) < $paycredits) {
				showmessage('forum_pay_incorrect', NULL, array('paycredits' => $paycredits, 'credits' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'], 'title' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title']));
			} else {
				include template('forum/forumdisplay_pay');
				exit();
			}
		}
	}
}

if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'forum_rules_'.$_G['fid']) === FALSE) {
	$collapse['forum_rules'] = '';
	$collapse['forum_rulesimg'] = 'no';
} else {
	$collapse['forum_rules'] = 'display: none';
	$collapse['forum_rulesimg'] = 'yes';
}

$forumlastvisit = 0;
if(empty($_G['forum']['picstyle']) && isset($_G['cookie']['forum_lastvisit']) && strexists($_G['cookie']['forum_lastvisit'], 'D_'.$_G['fid'])) {
	preg_match('/D\_'.$_G['fid'].'\_(\d+)/', $_G['cookie']['forum_lastvisit'], $a);
	$forumlastvisit = $a[1];
	unset($a);
}
dsetcookie('forum_lastvisit', preg_replace("/D\_".$_G['fid']."\_\d+/", '', $_G['cookie']['forum_lastvisit']).'D_'.$_G['fid'].'_'.TIMESTAMP, 604800);

$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();

$tableid = $_GET['archiveid'] && in_array($_GET['archiveid'], $threadtableids) ? intval($_GET['archiveid']) : 0;

if($_G['setting']['allowmoderatingthread'] && $_G['uid']) {
	$threadmodcount = C::t('forum_thread')->count_by_fid_displayorder_authorid($_G['fid'], -2, $_G['uid'], $tableid);
}

$optionadd = $filterurladd = $searchsorton = '';

$quicksearchlist = array();
if(!empty($_G['forum']['threadsorts']['types'])) {
	require_once libfile('function/threadsort');

	$showpic = intval($_GET['showpic']);
	$templatearray = $sortoptionarray = array();
	foreach($_G['forum']['threadsorts']['types'] as $stid => $sortname) {
		loadcache(array('threadsort_option_'.$stid, 'threadsort_template_'.$stid));
		sortthreadsortselectoption($stid);
		$templatearray[$stid] = $_G['cache']['threadsort_template_'.$stid]['subject'];
		$sortoptionarray[$stid] = $_G['cache']['threadsort_option_'.$stid];
	}

	if(!empty($_G['forum']['threadsorts']['defaultshow']) && empty($_GET['sortid']) && empty($_GET['sortall'])) {
		$_GET['sortid'] = $_G['forum']['threadsorts']['defaultshow'];
		$_GET['filter'] = 'sortid';
		$_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'].'&sortid='.$_GET['sortid'] : 'sortid='.$_GET['sortid'];
		$filterurladd = '&amp;filter=sort';
	}

	$_GET['sortid'] = $_GET['sortid'] ? $_GET['sortid'] : $_GET['searchsortid'];
	if(isset($_GET['sortid']) && $_G['forum']['threadsorts']['types'][$_GET['sortid']]) {
		$searchsortoption = $sortoptionarray[$_GET['sortid']];
		$quicksearchlist = quicksearch($searchsortoption);
		$_G['forum_optionlist'] = $_G['cache']['threadsort_option_'.$_GET['sortid']];
		$forum_optionlist = getsortedoptionlist();
	}
}
$_GET['sortid'] = intval($_GET['sortid']);
$moderatedby = $_G['forum']['status'] != 3 ? moddisplay($_G['forum']['moderators'], 'forumdisplay') : '';
$_GET['highlight'] = empty($_GET['highlight']) ? '' : dhtmlspecialchars($_GET['highlight']);
if($_G['forum']['autoclose']) {
	$closedby = $_G['forum']['autoclose'] > 0 ? 'dateline' : 'lastpost';
	$_G['forum']['autoclose'] = abs($_G['forum']['autoclose']) * 86400;
}

$subexists = 0;
foreach($_G['cache']['forums'] as $sub) {
	if($sub['type'] == 'sub' && $sub['fup'] == $_G['fid'] && (!$_G['setting']['hideprivate'] || !$sub['viewperm'] || forumperm($sub['viewperm']) || strstr($sub['users'], "\t$_G[uid]\t"))) {
		if(!$sub['status']) {
			continue;
		}
		$subexists = 1;
		$sublist = array();
		$query = C::t('forum_forum')->fetch_all_info_by_fids(0, 'available', 0, $_G['fid'], 1, 0, 0, 'sub');

		if(!empty($_G['member']['accessmasks'])) {
			$fids = array_keys($query);
			$accesslist = C::t('forum_access')->fetch_all_by_fid_uid($fids, $_G['uid']);
			foreach($query as $key => $val) {
				$query[$key]['allowview'] = $accesslist[$key];
			}
		}
		foreach($query as $sub) {
			$sub['extra'] = dunserialize($sub['extra']);
			if(!is_array($sub['extra'])) {
				$sub['extra'] = array();
			}
			if(forum($sub)) {
				$sub['orderid'] = count($sublist);
				$sublist[] = $sub;
			}
		}
		break;
	}
}

if(!empty($_GET['archiveid']) && in_array($_GET['archiveid'], $threadtableids)) {
	$subexists = 0;
}

if($subexists) {
	if($_G['forum']['forumcolumns']) {
		$_G['forum']['forumcolwidth'] = (floor(100 / $_G['forum']['forumcolumns']) - 0.1).'%';
		$_G['forum']['subscount'] = count($sublist);
		$_G['forum']['endrows'] = '';
		if($colspan = $_G['forum']['subscount'] % $_G['forum']['forumcolumns']) {
			while(($_G['forum']['forumcolumns'] - $colspan) > 0) {
				$_G['forum']['endrows'] .= '<td>&nbsp;</td>';
				$colspan ++;
			}
			$_G['forum']['endrows'] .= '</tr>';
		}
	}
	if(empty($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'subforum_'.$_G['fid']) === FALSE) {
		$collapse['subforum'] = '';
		$collapseimg['subforum'] = 'collapsed_no.gif';
	} else {
		$collapse['subforum'] = 'display: none';
		$collapseimg['subforum'] = 'collapsed_yes.gif';
	}
}


$page = $_G['page'];
$subforumonly = $_G['forum']['simple'] & 1;
$simplestyle = !$_G['forum']['allowside'] || $page > 1 ? true : false;

if($subforumonly) {
	$_G['setting']['fastpost'] = false;
	$_GET['orderby'] = '';
	if(!defined('IN_ARCHIVER')) {
		include template('diy:forum/forumdisplay:'.$_G['fid']);
	} else {
		include loadarchiver('forum/forumdisplay');
	}
	exit();
}
if($_GET['filter'] != 'hot') {
	$page = $_G['setting']['threadmaxpages'] && $page > $_G['setting']['threadmaxpages'] ? 1 : $page;
}

if($_G['forum']['modrecommend'] && $_G['forum']['modrecommend']['open']) {
	$_G['forum']['recommendlist'] = recommendupdate($_G['fid'], $_G['forum']['modrecommend'], '', 1);
}
$recommendgroups = array();
if($_G['forum']['status'] != 3 && helper_access::check_module('group')) {
	loadcache('forumrecommend');
	$recommendgroups = $_G['cache']['forumrecommend'][$_G['fid']];
}

if($recommendgroups) {
	if(empty($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'recommendgroups_'.$_G['fid']) === FALSE) {
		$collapse['recommendgroups'] = '';
		$collapseimg['recommendgroups'] = 'collapsed_no.gif';
	} else {
		$collapse['recommendgroups'] = 'display: none';
		$collapseimg['recommendgroups'] = 'collapsed_yes.gif';
	}
}
if(!$simplestyle || !$_G['forum']['allowside'] && $page == 1) {
	if($_G['cache']['announcements_forum'] && (!$_G['cache']['announcements_forum']['endtime'] || $_G['cache']['announcements_forum']['endtime'] > TIMESTAMP)) {
		$announcement = $_G['cache']['announcements_forum'];
		$announcement['starttime'] = dgmdate($announcement['starttime'], 'd');
	} else {
		$announcement = NULL;
	}
}

$filteradd = $sortoptionurl = $sp = '';
$sorturladdarray = $selectadd = array();
$forumdisplayadd = array('orderby' => '');
$specialtype = array('poll' => 1, 'trade' => 2, 'reward' => 3, 'activity' => 4, 'debate' => 5);
$filterfield = array('digest', 'recommend', 'sortall', 'typeid', 'sortid', 'dateline', 'page', 'orderby', 'specialtype', 'author', 'view', 'reply', 'lastpost', 'heat', 'hot');

foreach($filterfield as $v) {
	$forumdisplayadd[$v] = '';
}

$filter = isset($_GET['filter']) && in_array($_GET['filter'], $filterfield) ? $_GET['filter'] : '';
$filterbool = !empty($filter);
$filterarr = $multiadd = array();
$threadclasscount = array();

if($filter && $filter != 'hot') {
	if($query_string = $_SERVER['QUERY_STRING']) {
		$query_string = substr($query_string, (strpos($query_string, "&") + 1));
		parse_str($query_string, $geturl);
		$geturl = daddslashes($geturl, 1);
		if($geturl && is_array($geturl)) {
			$issort = isset($_GET['sortid']) && isset($_G['forum']['threadsorts']['types'][$_GET['sortid']]) && $quicksearchlist ? TRUE : FALSE;
			$selectadd = $issort ? $geturl : array();
			foreach($filterfield as $option) {
				foreach($geturl as $field => $value) {
					if(in_array($field, $filterfield) && $option != $field && $field != 'page' && $option != 'page' && ($field != 'orderby' || !in_array($option, array('author', 'reply', 'view', 'lastpost', 'heat')))) {
						if(!(in_array($option, array('digest', 'recommend')) && in_array($field, array('digest', 'recommend')))) {
							$forumdisplayadd[$option] .= '&'.rawurlencode($field).'='.rawurlencode($value);
						}
					}
				}
			}
			if($issort) {
				$sfilterfield = array_merge(array('filter', 'sortid', 'orderby', 'fid'), $filterfield);
				foreach($geturl as $soption => $value) {
					$forumdisplayadd[$soption] .= !in_array($soption, $sfilterfield) ? '&'.rawurlencode($soption).'='.rawurlencode($value) : '';
				}
				unset($sfilterfield);
			}
			if($issort && is_array($quicksearchlist)) {
				foreach($quicksearchlist as $option) {
					$identifier = $option['identifier'];
					foreach($geturl as $option => $value) {
						$sorturladdarray[$identifier] .= !in_array($option, array('filter', 'sortid', 'orderby', 'fid', 'searchsort', $identifier)) ? '&amp;'.rawurlencode($option).'='.rawurlencode($value) : '';
					}
				}
			}

			foreach($geturl as $field => $value) {
				if($field != 'page' && $field != 'fid' && $field != 'searchoption' && $field != 't') {
					$multiadd[] = rawurlencode($field).'='.rawurlencode($value);
					if(in_array($field, $filterfield)) {
						if($field == 'digest') {
							$filterarr['digest'] = 1;
						} elseif($field == 'recommend') {
							$filterarr['recommends'] = intval($_G['setting']['recommendthread']['iconlevels'][0]);
						} elseif($field == 'specialtype') {
							$filterarr['special'] = $specialtype[$value];
							$filterarr['specialthread'] = 1;
							if($value == 'reward') {
								if($_GET['rewardtype'] == 1) {
									$filterarr['pricemore'] = 0;
								} elseif($_GET['rewardtype'] == 2) {
									$filterarr['pricesless'] = 0;
								}
							}
						} elseif($field == 'dateline') {
							if($value) {
								$filterarr['lastpostmore'] = TIMESTAMP - $value;
							}
						} elseif($field == 'typeid' || $field == 'sortid') {
							$fieldstr = $field == 'typeid' ? 'intype' : 'insort';
							$filterarr[$fieldstr] = dintval($value);
						}
						$sp = ' ';
					}
				}
			}
			if(count($filterarr) == 1) {
				foreach($filterarr as $key => $value) {
					if($key == 'intype') {
						$threadclasscount = array('id' => $value, 'idtype' => 'typeid');
					} elseif($key == 'insort') {
						$threadclasscount = array('id' => $value, 'idtype' => 'sortid');
					}
				}
			}
		}
	}
	$simplestyle = true;
}

if(!empty($_GET['orderby']) && !$_G['setting']['closeforumorderby'] && in_array($_GET['orderby'], array('lastpost', 'dateline', 'replies', 'views', 'recommends', 'heats'))) {
	$forumdisplayadd['orderby'] .= '&orderby='.$_GET['orderby'];
} else {
	$_GET['orderby'] = isset($_G['cache']['forums'][$_G['fid']]['orderby']) ? $_G['cache']['forums'][$_G['fid']]['orderby'] : 'lastpost';
}

$_GET['ascdesc'] = isset($_G['cache']['forums'][$_G['fid']]['ascdesc']) ? $_G['cache']['forums'][$_G['fid']]['ascdesc'] : 'DESC';

$check = array();
$check[$filter] = $check[$_GET['orderby']] = $check[$_GET['ascdesc']] = 'selected="selected"';

if(($_G['forum']['status'] != 3 && $_G['forum']['allowside'])) {
	updatesession();
	$onlinenum = C::app()->session->count_by_fid($_G['fid']);
	if(!IS_ROBOT && ($_G['setting']['whosonlinestatus'] == 2 || $_G['setting']['whosonlinestatus'] == 3)) {
		$_G['setting']['whosonlinestatus'] = 1;
		$detailstatus = $showoldetails == 'yes' || (((!isset($_G['cookie']['onlineforum']) && !$_G['setting']['whosonline_contract']) || $_G['cookie']['onlineforum']) && !$showoldetails);

		if($detailstatus) {
			$actioncode = lang('forum/action');
			$whosonline = array();
			$forumname = strip_tags($_G['forum']['name']);

			$whosonline = C::app()->session->fetch_all_by_fid($_G['fid'], 12);
			$_G['setting']['whosonlinestatus'] = 1;
		}
	} else {
		$_G['setting']['whosonlinestatus'] = 0;
	}
}

if($_G['forum']['threadsorts']['types'] && $sortoptionarray && ($_GET['searchoption'] || $_GET['searchsort'])) {
	$sortid = intval($_GET['sortid']);

	if($_GET['searchoption']){
		$forumdisplayadd['page'] = '&sortid='.$sortid;
		foreach($_GET['searchoption'] as $optionid => $option) {
			$optionid = intval($optionid);
			$searchoption = '';
			if(is_array($option['value'])) {
				foreach($option['value'] as $v) {
					$v = rawurlencode((string)$v);
					$searchoption .= "&searchoption[$optionid][value][$v]=$v";
				}
			} else {
				$option['value'] = rawurlencode((string)$option['value']);
				$option['value'] && $searchoption = "&searchoption[$optionid][value]=$option[value]";
			}
			$option['type'] = rawurlencode((string)$option['type']);
			$identifier = $sortoptionarray[$sortid][$optionid]['identifier'];
			$forumdisplayadd['page'] .= $searchoption ? "$searchoption&searchoption[$optionid][type]=$option[type]" : '';
		}
	}

	$searchsorttids = sortsearch($_GET['sortid'], $sortoptionarray, $_GET['searchoption'], $selectadd, $_G['fid']);
	$filterarr['intids'] = $searchsorttids ? $searchsorttids : array(0);
}

if(isset($_GET['searchoption'])) {
    $_GET['searchoption'] = dhtmlspecialchars($_GET['searchoption']);
}

if($_G['forum']['relatedgroup']) {
	$relatedgroup = explode(',', $_G['forum']['relatedgroup']);
	$relatedgroup[] = $_G['fid'];
	$filterarr['inforum'] = $relatedgroup;
} else {
	$filterarr['inforum'] = $_G['fid'];
}
if(empty($filter) && empty($_GET['sortid']) && empty($_G['forum']['relatedgroup'])) {
	if($forumarchive) {
		if($_GET['archiveid']) {
			$_G['forum_threadcount'] = $forumarchive[$_GET['archiveid']]['threads'];
		} else {
			$primarytabthreads = $_G['forum']['threads'];
			foreach($forumarchive as $arcid => $avalue) {
				if($arcid) {
					$primarytabthreads = $primarytabthreads - $avalue['threads'];
				}
			}
			$_G['forum_threadcount'] = $primarytabthreads;
		}
	} else {
		$_G['forum_threadcount'] = $_G['forum']['threads'];
	}
} else {
	$filterarr['sticky'] = 0;
	$_G['forum_threadcount'] = C::t('forum_thread')->count_search($filterarr, $tableid);
	if($threadclasscount) {
		threadclasscount($_G['fid'], $threadclasscount['id'], $threadclasscount['idtype'], $_G['forum_threadcount']);
	}
}

$thisgid = $_G['forum']['type'] == 'forum' ? $_G['forum']['fup'] : (!empty($_G['cache']['forums'][$_G['forum']['fup']]['fup']) ? $_G['cache']['forums'][$_G['forum']['fup']]['fup'] : 0);
$forumstickycount = $stickycount = 0;
$stickytids = array();
$showsticky = !defined('MOBILE_HIDE_STICKY') || !MOBILE_HIDE_STICKY;
if($showsticky) {
	$forumstickytids = array();
	if($_G['page'] !== 1 || $filterbool === false) {
		if($_G['setting']['globalstick'] && $_G['forum']['allowglobalstick']) {
			if(!empty($_G['cache']['globalstick']['global']['tids'])) {
				$stickytids = explode(',', str_replace("'", '', $_G['cache']['globalstick']['global']['tids']));
			}
			if(!empty($_G['cache']['globalstick']['categories'][$thisgid]['count'])) {
				$stickytids = array_merge($stickytids, explode(',', str_replace("'", '', $_G['cache']['globalstick']['categories'][$thisgid]['tids'])));
			}

			if($_G['forum']['status'] != 3) {
				$stickycount = $_G['cache']['globalstick']['global']['count'];
				if(!empty($_G['cache']['globalstick']['categories'][$thisgid])) {
					$stickycount += $_G['cache']['globalstick']['categories'][$thisgid]['count'];
				}
			}
		}

		if($_G['forum']['allowglobalstick']) {
			$forumstickycount = 0;
			$forumstickfid = $_G['forum']['status'] != 3 ? $_G['fid'] : $_G['forum']['fup'];
			if(isset($_G['cache']['forumstick'][$forumstickfid])) {
				$forumstickycount = count($_G['cache']['forumstick'][$forumstickfid]);
				$forumstickytids = $_G['cache']['forumstick'][$forumstickfid];
			}
			if(!empty($forumstickytids)) {
				$stickytids = array_merge($stickytids, $forumstickytids);
			}
			$stickycount += $forumstickycount;
		}
	}
}

if($_G['forum']['picstyle']) {
	$forumdefstyle = isset($_GET['forumdefstyle']) ? $_GET['forumdefstyle'] : '';
	if($forumdefstyle) {
		switch($forumdefstyle) {
			case 'no': dsetcookie('forumdefstyle', ''); break;
			case 'yes': dsetcookie('forumdefstyle', 1, 31536000); break;
		}
	}
	if(empty($_G['cookie']['forumdefstyle'])) {
		if(!empty($_G['setting']['forumpicstyle']['thumbnum'])) {
			$_G['tpp'] = $_G['setting']['forumpicstyle']['thumbnum'];
		}
		$stickycount = $showsticky = 0;
	}
}

if(!empty($_G['cookie']['forumdefstyle'])) {
	$_G['cookie']['forumdefstyle'] = 1;
}

if($filter != 'hot' && @ceil($_G['forum_threadcount']/$_G['tpp']) < $page) {
	$page = 1;
}
$start_limit = ($page - 1) * $_G['tpp'];

$forumdisplayadd['page'] = !empty($forumdisplayadd['page']) ? $forumdisplayadd['page'] : '';
$multipage_archive = $_GET['archiveid'] && in_array($_GET['archiveid'], $threadtableids) ? "&archiveid={$_GET['archiveid']}" : '';
$multipage = multi($_G['forum_threadcount'], $_G['tpp'], $page, "forum.php?mod=forumdisplay&fid=$_G[fid]".$forumdisplayadd['page'].($multiadd ? '&'.implode('&', $multiadd) : '')."$multipage_archive", $_G['setting']['threadmaxpages']);

$realpages = @ceil($_G['forum_threadcount']/$_G['tpp']);
$maxpage = ($_G['setting']['threadmaxpages'] && $_G['setting']['threadmaxpages'] < $realpages) ? $_G['setting']['threadmaxpages'] : $realpages;
$nextpage = ($page + 1) > $maxpage ? 1 : ($page + 1);
$multipage_more = "forum.php?mod=forumdisplay&fid=$_G[fid]".$forumdisplayadd['page'].($multiadd ? '&'.implode('&', $multiadd) : '')."$multipage_archive".'&page='.$nextpage;

$extra = rawurlencode(!IS_ROBOT ? 'page='.$page.($forumdisplayadd['page'] ? '&filter='.$filter.$forumdisplayadd['page'] : '') : 'page=1');

$separatepos = 0;
$_G['forum_threadlist'] = $threadids = array();
$_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');

$filterarr['sticky'] = 4;
$filterarr['displayorder'] = !$filterbool && $stickycount ? array(0, 1) : array(0, 1, 2, 3, 4);
if($filter !== 'hot') {
	$threadlist = array();
	$indexadd = '';
	$_order = "displayorder DESC, $_GET[orderby] $_GET[ascdesc]";
	if($filterbool) {
		if($filterarr['digest']) {
			$indexadd = " FORCE INDEX (digest) ";
		}
	} elseif($showsticky && is_array($stickytids) && $stickytids[0]) {
		$filterarr1 = $filterarr;
		$filterarr1['inforum'] = '';
		$filterarr1['intids'] = $stickytids;
		$filterarr1['displayorder'] = array(2, 3, 4);
		$threadlist = C::t('forum_thread')->fetch_all_search($filterarr1, $tableid, $start_limit, $_G['tpp'], $_order, '');
		unset($filterarr1);
	}
	$threadlist = array_merge($threadlist, C::t('forum_thread')->fetch_all_search($filterarr, $tableid, $start_limit, $_G['tpp'], $_order, '', $indexadd));
	unset($_order);

	if(empty($threadlist) && $page <= ceil($_G['forum_threadcount'] / $_G['tpp'])) {
		require_once libfile('function/post');
		updateforumcount($_G['fid']);
	}
} else {
	$hottime = dintval(str_replace('-', '', $_GET['time']));
	$multipage = '';
	if($hottime && checkdate(substr($hottime, 4, 2), substr($hottime, 6, 2), substr($hottime, 0, 4))) {
		$calendartime = abs($hottime);
		$ctime = sprintf('%04d', substr($hottime, 0, 4)).'-'.sprintf('%02d', substr($hottime, 4, 2)).'-'.sprintf('%02d', substr($hottime, 6, 2));
	} else {
		$calendartime = dgmdate(strtotime(dgmdate(TIMESTAMP, 'Y-m-d')) - 86400, 'Ymd');
		$ctime = dgmdate(strtotime(dgmdate(TIMESTAMP, 'Y-m-d')) - 86400, 'Y-m-d');
	}
	$caldata = C::t('forum_threadcalendar')->fetch_by_fid_dateline($_G['fid'], $calendartime);
	$_G['forum_threadcount'] = 0;
	if($caldata) {
		$hottids = C::t('forum_threadhot')->fetch_all_tid_by_cid($caldata['cid']);
		$threadlist = C::t('forum_thread')->fetch_all_by_tid($hottids);
		$_G['forum_threadcount'] = count($threadlist);
	}

}

$_G['ppp'] = $_G['forum']['threadcaches'] && !$_G['uid'] ? $_G['setting']['postperpage'] : $_G['ppp'];
$page = $_G['page'];
$todaytime = strtotime(dgmdate(TIMESTAMP, 'Ymd'));

$verify = $verifyuids = $authorids = $grouptids = $rushtids = array();

$thide = !empty($_G['cookie']['thide']) ? explode('|', $_G['cookie']['thide']) : array();
$_G['showrows'] = $_G['hiddenexists'] = 0;

$threadindex = 0;
foreach($threadlist as $thread) {
	$thread['allreplies'] = $thread['replies'] + $thread['comments'];
	$thread['ordertype'] = getstatus($thread['status'], 4);
	if($_G['forum']['picstyle'] && empty($_G['cookie']['forumdefstyle'])) {
		if($thread['fid'] != $_G['fid'] && empty($thread['cover'])) {
			continue;
		}
		$thread['coverpath'] = getthreadcover($thread['tid'], $thread['cover']);
		$thread['cover'] = abs($thread['cover']);
	}
	$thread['forumstick'] = in_array($thread['tid'], $forumstickytids);
	$thread['related_group'] = 0;
	if($_G['forum']['relatedgroup'] && $thread['fid'] != $_G['fid']) {
		if($thread['closed'] > 1) continue;
		$thread['related_group'] = 1;
		$grouptids[] = $thread['tid'];
	}
	$thread['lastposterenc'] = rawurlencode($thread['lastposter']);
	if($thread['typeid'] && !empty($_G['forum']['threadtypes']['prefix']) && isset($_G['forum']['threadtypes']['types'][$thread['typeid']])) {
		if($_G['forum']['threadtypes']['prefix'] == 1) {
			$thread['typehtml'] = '<em>[<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=typeid&amp;typeid='.$thread['typeid'].'">'.$_G['forum']['threadtypes']['types'][$thread['typeid']].'</a>]</em>';
		} elseif($_G['forum']['threadtypes']['icons'][$thread['typeid']] && $_G['forum']['threadtypes']['prefix'] == 2) {
			$thread['typehtml'] = '<em><a title="'.strip_tags($_G['forum']['threadtypes']['types'][$thread['typeid']]).'" href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=typeid&amp;typeid='.$thread['typeid'].'">'.'<img style="vertical-align: middle;padding-right:4px;" src="'.$_G['forum']['threadtypes']['icons'][$thread['typeid']].'" alt="'.strip_tags($_G['forum']['threadtypes']['types'][$thread['typeid']]).'" /></a></em>';
		}
		$thread['typename'] = $_G['forum']['threadtypes']['types'][$thread['typeid']];
	} else {
		$thread['typename'] = $thread['typehtml'] = '';
	}

	$thread['sorthtml'] = $thread['sortid'] && !empty($_G['forum']['threadsorts']['prefix']) && isset($_G['forum']['threadsorts']['types'][$thread['sortid']]) ?
		'<em>[<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=sortid&amp;sortid='.$thread['sortid'].'">'.$_G['forum']['threadsorts']['types'][$thread['sortid']].'</a>]</em>' : '';
	$thread['multipage'] = '';
	$topicposts = $thread['special'] ? $thread['replies'] : $thread['replies'] + 1;
	$multipate_archive = $_GET['archiveid'] && in_array($_GET['archiveid'], $threadtableids) ? "archiveid={$_GET['archiveid']}" : '';
	if($topicposts > $_G['ppp']) {
		$pagelinks = '';
		$thread['pages'] = ceil($topicposts / $_G['ppp']);
		$realtid = $_G['forum']['status'] != 3 && $thread['isgroup'] == 1 ? $thread['closed'] : $thread['tid'];
		for($i = 2; $i <= 6 && $i <= $thread['pages']; $i++) {
			$pagelinks .= "<a href=\"forum.php?mod=viewthread&tid=$realtid&amp;".(!empty($multipate_archive) ? "$multipate_archive&amp;" : '')."extra=$extra&amp;page=$i\" onclick=\"atarget(this)\">$i</a>";
		}
		if($thread['pages'] > 6) {
			$pagelinks .= "..<a href=\"forum.php?mod=viewthread&tid=$realtid&amp;".(!empty($multipate_archive) ? "$multipate_archive&amp;" : '')."extra=$extra&amp;page=$thread[pages]\" onclick=\"atarget(this)\">$thread[pages]</a>";
		}
		$thread['multipage'] = '&nbsp;...'.$pagelinks;
	}

	if($thread['highlight']) {
		$string = sprintf('%02d', $thread['highlight']);
		$stylestr = sprintf('%03b', $string[0]);

		$thread['highlight'] = ' style="';
		$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
		$thread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
		$thread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$thread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]].';' : '';
		if($thread['bgcolor']) {
			$thread['highlight'] .= "background-color: $thread[bgcolor];";
		}
		$thread['highlight'] .= '"';
	} else {
		$thread['highlight'] = '';
	}

	$thread['recommendicon'] = '';
	if(!empty($_G['setting']['recommendthread']['status']) && $thread['recommends']) {
		foreach($_G['setting']['recommendthread']['iconlevels'] as $k => $i) {
			if($thread['recommends'] > $i) {
				$thread['recommendicon'] = $k+1;
				break;
			}
		}
	}

	$thread['moved'] = $thread['heatlevel'] = $thread['new'] = 0;
	if($_G['forum']['status'] != 3 && ($thread['closed'] || ($_G['forum']['autoclose'] && $thread['fid'] == $_G['fid'] && TIMESTAMP - $thread[$closedby] > $_G['forum']['autoclose']))) {
		if($thread['isgroup'] == 1) {
			$thread['folder'] = 'common';
			$grouptids[] = $thread['closed'];
		} else {
			if($thread['closed'] > 1) {
				$thread['moved'] = $thread['tid'];
				$thread['allreplies'] = $thread['replies'] = '-';
				$thread['views'] = '-';
			}
			$thread['folder'] = 'lock';
		}
	} elseif($_G['forum']['status'] == 3 && $thread['closed'] == 1) {
		$thread['folder'] = 'lock';
	} else {
		$thread['folder'] = 'common';
		$thread['weeknew'] = TIMESTAMP - 604800 <= $thread['dbdateline'];
		if($thread['allreplies'] > $thread['views']) {
			$thread['views'] = $thread['allreplies'];
		}
		if($_G['setting']['heatthread']['iconlevels']) {
			foreach($_G['setting']['heatthread']['iconlevels'] as $k => $i) {
				if($thread['heats'] > $i) {
					$thread['heatlevel'] = $k + 1;
					break;
				}
			}
		}
	}
	$thread['icontid'] = $thread['forumstick'] || !$thread['moved'] && $thread['isgroup'] != 1 ? $thread['tid'] : $thread['closed'];
	if(!$thread['forumstick'] && ($thread['isgroup'] == 1 || $thread['fid'] != $_G['fid'])) {
		$thread['icontid'] = $thread['closed'] > 1 ? $thread['closed'] : $thread['tid'];
	}
	$thread['istoday'] = $thread['dateline'] > $todaytime ? 1 : 0;
	$thread['dbdateline'] = $thread['dateline'];
	$thread['dateline'] = dgmdate($thread['dateline'], 'u', '9999', getglobal('setting/dateformat'));
	$thread['dblastpost'] = $thread['lastpost'];
	$thread['lastpost'] = dgmdate($thread['lastpost'], 'u');
	$thread['hidden'] = $_G['setting']['threadhidethreshold'] && $thread['hidden'] >= $_G['setting']['threadhidethreshold'] || in_array($thread['tid'], $thide);
	if($thread['hidden']) {
		$_G['hiddenexists']++;
	}

	if(in_array($thread['displayorder'], array(1, 2, 3, 4))) {
		$thread['id'] = 'stickthread_'.$thread['tid'];
		$separatepos++;
	} else {
		$thread['id'] = 'normalthread_'.$thread['tid'];
		if($thread['folder'] == 'common' && $thread['dblastpost'] >= $forumlastvisit || !$forumlastvisit) {
			$thread['new'] = 1;
			$thread['folder'] = 'new';
			$thread['weeknew'] = TIMESTAMP - 604800 <= $thread['dbdateline'];
		}
		$_G['showrows']++;
	}
	if(isset($_G['setting']['verify']['enabled']) && $_G['setting']['verify']['enabled']) {
		$verifyuids[$thread['authorid']] = $thread['authorid'];
	}
	$authorids[$thread['authorid']] = $thread['authorid'];
	$thread['mobile'] = base_convert(getstatus($thread['status'], 13).getstatus($thread['status'], 12).getstatus($thread['status'], 11), 2, 10);
	$thread['rushreply'] = getstatus($thread['status'], 3);
	if($thread['rushreply']) {
		$rushtids[$thread['tid']] = $thread['tid'];
	}
	$threadids[$threadindex] = $thread['tid'];
	$_G['forum_threadlist'][$threadindex] = $thread;
	$threadindex++;

}

$_G['hiddenexists'] = !$_G['forum']['ismoderator'] && $_G['hiddenexists'] && $_G['showrows'] >= $_G['hiddenexists'];

$livethread = array();
if($_G['forum']['livetid'] && $page == 1 && (!$filter || ($filter == 'sortid' && $_G['forum']['threadsorts']['defaultshow'] == $_GET['sortid']))) {
	include_once libfile('function/post');
	$livethread = C::t('forum_thread')->fetch($_G['forum']['livetid']);
	$livepost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['forum']['livetid']);
	$livemessage = messagecutstr($livepost['message'], 200);
	$liveallowpostreply = ($_G['forum']['allowreply'] != -1) && (($livethread['isgroup'] || (!$livethread['closed'] && !checkautoclose($livethread))) || $_G['forum']['ismoderator']) && ((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])) || $_G['forum']['allowreply']);
}

if($rushtids) {
	$rushinfo = C::t('forum_threadrush')->fetch_all($rushtids);
	foreach($rushinfo as $tid => $info) {
		if($info['starttimefrom'] > TIMESTAMP) {
			$info['timer'] = $info['starttimefrom'] - TIMESTAMP;
			$info['timertype'] = 'start';
		} elseif($info['starttimeto'] > TIMESTAMP) {
			$info['timer'] = $info['starttimeto'] - TIMESTAMP;
			$info['timertype'] = 'end';
		} else {
			$info = '';
		}
		$rushinfo[$tid] = $info;
	}
}
if(!empty($threadids)) {
	$indexlist = array_flip($threadids);
	foreach(C::t('forum_threadaddviews')->fetch_all($threadids) as $tidkey => $value) {
		$index = $indexlist[$tidkey];
		$threadlist[$index]['views'] += $value['addviews'];
		$_G['forum_threadlist'][$index]['views'] += $value['addviews'];
	}
}

$verify = array();
if($_G['setting']['verify']['enabled'] && $verifyuids) {
	$verify = forumdisplay_verify_author($verifyuids);
}

if($authorids) {
	loadcache('usergroups');
	$groupcolor = array();
	foreach(C::t('common_member')->fetch_all($authorids) as $value) {
		$groupcolor[$value['uid']] = $_G['cache']['usergroups'][$value['groupid']]['color'];
	}
}
$_G['forum_threadnum'] = count($_G['forum_threadlist']) - $separatepos;

if(!empty($grouptids)) {
	$groupfids = array();
	foreach(C::t('forum_thread')->fetch_all_by_tid($grouptids) as $row) {
		$groupnames[$row['tid']]['fid'] = $row['fid'];
		$groupnames[$row['tid']]['views'] = $row['views'];
		$groupfids[] = $row['fid'];
	}
	$forumsinfo = C::t('forum_forum')->fetch_all($groupfids);
	foreach($groupnames as $gtid => $value) {
		$gfid = $groupnames[$gtid]['fid'];
		$groupnames[$gtid]['name'] = $forumsinfo[$gfid]['name'];
		$groupnames[$gtid]['type'] = $forumsinfo[$gfid]['type'];
		$groupnames[$gtid]['status'] = $forumsinfo[$gfid]['status'];
	}
}

$stemplate = null;
if($_G['forum']['threadsorts']['types'] && $sortoptionarray && $templatearray && $threadids) {
	$sortid = intval($_GET['sortid']);
	if(!strexists($templatearray[$sortid], '{subject_url}') && !strexists($templatearray[$sortid], '{tid}')) {
		$sortlistarray = showsorttemplate($sortid, $_G['fid'], $sortoptionarray, $templatearray, $_G['forum_threadlist'], $threadids);
		$stemplate = $sortlistarray['template'];
	} else {
		$sorttemplate = showsortmodetemplate($sortid, $_G['fid'], $sortoptionarray, $templatearray, $_G['forum_threadlist'], $threadids, $verify);
		$_G['forum']['sortmode'] = 1;
	}

	if(($_GET['searchoption'] || $_GET['searchsort']) && empty($searchsorttids)) {
		$_G['forum_threadlist'] = $multipage = '';
	}
}

$separatepos = $separatepos ? $separatepos + 1 : 0;

$_G['setting']['visitedforums'] = $_G['setting']['visitedforums'] && $_G['forum']['status'] != 3 ? visitedforums() : '';


$_G['group']['allowpost'] = (!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])) || (isset($_G['forum']['allowpost']) && $_G['forum']['allowpost'] == 1 && $_G['group']['allowpost']);
$fastpost = $_G['setting']['fastpost'] && !$_G['forum']['allowspecialonly'] && !$_G['forum']['threadsorts']['required'] && !$_G['forum']['picstyle'];
$allowfastpost = $fastpost && $_G['group']['allowpost'];
$_G['group']['allowpost'] = isset($_G['forum']['allowpost']) && $_G['forum']['allowpost'] == -1 ?  false : $_G['group']['allowpost'];

$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
$allowpostattach = $fastpost && ($_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm']))));

if($fastpost || $livethread) {
	if(in_array($_G['adminid'], array(0, -1)) && (!cknewuser(1) || $_G['setting']['newbiespan'] && (!getuserprofile('lastpost') || TIMESTAMP - getuserprofile('lastpost') < $_G['setting']['newbiespan'] * 60) && TIMESTAMP - $_G['member']['regdate'] < $_G['setting']['newbiespan'] * 60)) {
		$allowfastpost = false;
	}
	$usesigcheck = $_G['uid'] && $_G['group']['maxsigsize'];
	list($seccodecheck, $secqaacheck) = seccheck('post', 'newthread');
} elseif(!$_G['uid']) {
	$fastpostdisabled = true;
}

$showpoll = $showtrade = $showreward = $showactivity = $showdebate = 0;
if($_G['forum']['allowpostspecial']) {
	$showpoll = $_G['forum']['allowpostspecial'] & 1;
	$showtrade = $_G['forum']['allowpostspecial'] & 2;
	$showreward = isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($_G['forum']['allowpostspecial'] & 4);
	$showactivity = $_G['forum']['allowpostspecial'] & 8;
	$showdebate = $_G['forum']['allowpostspecial'] & 16;
}

if($_G['group']['allowpost']) {
	$_G['group']['allowpostpoll'] = $_G['group']['allowpostpoll'] && $showpoll;
	$_G['group']['allowposttrade'] = $_G['group']['allowposttrade'] && $showtrade;
	$_G['group']['allowpostreward'] = $_G['group']['allowpostreward'] && $showreward;
	$_G['group']['allowpostactivity'] = $_G['group']['allowpostactivity'] && $showactivity;
	$_G['group']['allowpostdebate'] = $_G['group']['allowpostdebate'] && $showdebate;
}

$showthreadclasscount = array();
if(($_G['forum']['threadtypes'] && $_G['forum']['threadtypes']['listable']) || count($_G['forum']['threadsorts']['types']) > 0) {
	$showthreadclasscount = threadclasscount($_G['fid']);
}

$_G['forum']['threadplugin'] = $_G['group']['allowpost'] && $_G['setting']['threadplugins'] ? dunserialize($_G['forum']['threadplugin']) : array();

$allowleftside = !$subforumonly && $_G['setting']['leftsidewidth'] && !$_G['forum']['allowside'];
if(isset($_GET['leftsidestatus'])) {
	dsetcookie('disableleftside', $_GET['leftsidestatus'], 2592000);
	$_G['cookie']['disableleftside'] = $_GET['leftsidestatus'];
}
$leftside = empty($_G['cookie']['disableleftside']) && $allowleftside ? forumleftside() : array();
$leftsideswitch = $allowleftside ? "forum.php?mod=forumdisplay&fid=$_G[fid]&page=$page".($multiadd ? '&'.implode('&', $multiadd) : '') : '';

require_once libfile('function/upload');
$swfconfig = getuploadconfig($_G['uid'], $_G['fid']);

$template = 'diy:forum/forumdisplay:'.$_G['fid'];

if($_G['forum']['status'] == 3) {
	$groupviewed_list = get_viewedgroup();
	write_groupviewed($_G['fid']);
	$template = 'diy:group/group:'.$_G['fid'];
}

if(!defined('IN_ARCHIVER')) {
	include template($template);
} else {
	include loadarchiver('forum/forumdisplay');
}


function forumdisplay_verify_author($ids) {
	global $_G;
	$verify = array();
	foreach(C::t('common_member_verify')->fetch_all($ids) as $value) {
		foreach($_G['setting']['verify'] as $vid => $vsetting) {
			if($vsetting['available'] && $vsetting['showicon'] && $value['verify'.$vid] == 1) {
				$srcurl = !empty($vsetting['icon']) ? $vsetting['icon'] : '';
				$verify[$value['uid']] .= "<a href=\"home.php?mod=spacecp&ac=profile&op=verify&vid=$vid\" target=\"_blank\">".(!empty($srcurl) ? '<img src="'.$srcurl.'" class="vm" alt="'.$vsetting['title'].'" title="'.$vsetting['title'].'" />' : $vsetting['title']).'</a>';
			}
		}

	}
	return $verify;
}
?>