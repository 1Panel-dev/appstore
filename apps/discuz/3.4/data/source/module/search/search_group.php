<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_group.php 30187 2012-05-16 03:24:35Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

require_once libfile('function/home');
require_once libfile('function/post');

if(!$_G['setting']['search']['group']['status']) {
	showmessage('search_group_closed');
}

if($_G['adminid'] != 1 && !($_G['group']['allowsearch'] & 16)) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$_G['setting']['search']['group']['searchctrl'] = intval($_G['setting']['search']['group']['searchctrl']);

$srchmod = 5;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$srchtype = empty($_GET['srchtype']) ? '' : trim($_GET['srchtype']);
$searchid = isset($_GET['searchid']) ? intval($_GET['searchid']) : 0;

$srchtxt = $_GET['srchtxt'];
$srchfid = intval($_GET['srchfid']);
$viewgroup = intval($_GET['viewgroup']);
$keyword = isset($srchtxt) ? dhtmlspecialchars(trim($srchtxt)) : '';

if(!submitcheck('searchsubmit', 1)) {

	include template('search/group');

} else {

	$orderby = in_array($_GET['orderby'], array('dateline', 'replies', 'views')) ? $_GET['orderby'] : 'lastpost';
	$ascdesc = isset($_GET['ascdesc']) && $_GET['ascdesc'] == 'asc' ? 'asc' : 'desc';

	if(!empty($searchid)) {

		require_once libfile('function/group');

		$page = max(1, intval($_GET['page']));
		$start_limit = ($page - 1) * $_G['tpp'];

		$index = C::t('common_searchindex')->fetch_by_searchid_srchmod($searchid, $srchmod);
		if(!$index) {
			showmessage('search_id_invalid');
		}

		$keyword = dhtmlspecialchars($index['keywords']);
		$keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';

		$index['keywords'] = rawurlencode($index['keywords']);
		$index['ids'] = dunserialize($index['ids']);
		$searchstring = explode('|', $index['searchstring']);
		$srchfid = $searchstring[2];
		$threadlist = $grouplist = $posttables = array();
		if($index['ids']['thread'] && ($searchstring[2] || empty($viewgroup))) {
			require_once libfile('function/misc');
			$threads = C::t('forum_thread')->fetch_all_by_tid_fid_displayorder(explode(',', $index['ids']['thread']), null, 0, $orderby, $start_limit, $_G['tpp'], '>=', $ascdesc);
			foreach($threads as $value) {
				$fids[$value['fid']] = $value['fid'];
			}
			$forums = C::t('forum_forum')->fetch_all_name_by_fid($fids);
			foreach($threads as $thread) {
				$thread['forumname'] = $forums[$thread['fid']]['name'];
				$thread['subject'] = bat_highlight($thread['subject'], $keyword);
				$thread['realtid'] = $thread['tid'];
				$threadlist[$thread['tid']] = procthread($thread);
				$posttables[$thread['posttableid']][] = $thread['tid'];
			}
			if($threadlist) {
				foreach($posttables as $tableid => $tids) {
					foreach(C::t('forum_post')->fetch_all_by_tid($tableid, $tids, true, '', 0, 0, 1) as $post) {
						$threadlist[$post['tid']]['message'] = bat_highlight(messagecutstr($post['message'], 200), $keyword);
					}
				}
			}
		}
		$groupnum = !empty($index['ids']['group']) ? count(explode(',', $index['ids']['group'])) - 1 : 0;
		if($index['ids']['group'] && ($viewgroup || empty($searchstring[2]))) {
			if(empty($viewgroup)) {
				$index['ids']['group'] = implode(',', array_slice(explode(',', $index['ids']['group']), 0, 9));
			}
			if($viewgroup) {
				$query = C::t('forum_forum')->fetch_all_info_by_fids(explode(',', $index['ids']['group']), 3, $_G['tpp'], 0, 0, 0, 0, 'sub', $start_limit);
			} else {
				$query = C::t('forum_forum')->fetch_all_info_by_fids(explode(',', $index['ids']['group']), 3, 0, 0, 0, 0, 0, 'sub');
			}

			foreach($query as $group) {
				$group['icon'] = get_groupimg($group['icon'], 'icon');
				$group['name'] = bat_highlight($group['name'], $keyword);
				$group['description'] = bat_highlight($group['description'], $keyword);
				$group['dateline'] = dgmdate($group['dateline'], 'u');
				$grouplist[] = $group;
			}
		}
		if(empty($viewgroup)) {
			$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=group&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes".($viewgroup ? '&viewgroup=1' : ''));
		} else {
			$multipage = multi($groupnum, $_G['tpp'], $page, "search.php?mod=group&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes".($viewgroup ? '&viewgroup=1' : ''));
		}

		$url_forward = 'search.php?mod=group&'.$_SERVER['QUERY_STRING'];

		include template('search/group');

	} else {

		$srchuname = isset($_GET['srchuname']) ? trim($_GET['srchuname']) : '';

		$searchstring = 'group|title|'.$srchfid.'|'.addslashes($srchtxt);
		$searchindex = array('id' => 0, 'dateline' => '0');

		foreach(C::t('common_searchindex')->fetch_all_search($_G['setting']['search']['group']['searchctrl'], $_G['clientip'], $_G['uid'], $_G['timestamp'], $searchstring, $srchmod) as $index) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=group', array('searchctrl' => $_G['setting']['search']['group']['searchctrl']));
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			!($_G['group']['exempt'] & 2) && checklowerlimit('search');

			if(!$srchtxt && !$srchuid && !$srchuname) {
				dheader('Location: search.php?mod=group');
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['group']['maxspm']) {
				if(C::t('common_searchindex')->count_by_dateline($_G['timestamp'], $srchmod) >= $_G['setting']['search']['group']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=group', array('maxspm' => $_G['setting']['search']['group']['maxspm']));
				}
			}

			$num = $ids = $tnum = $tids = 0;
			$_G['setting']['search']['group']['maxsearchresults'] = $_G['setting']['search']['group']['maxsearchresults'] ? intval($_G['setting']['search']['group']['maxsearchresults']) : 500;
			list($srchtxt, $srchtxtsql) = searchkey($keyword, "subject LIKE '%{text}%'", true);

			$threads = C::t('forum_thread')->fetch_all_by_tid_fid(0, $srchfid, 1, '', $keyword, $_G['setting']['search']['group']['maxsearchresults']);
			foreach($threads as $value) {
				$fids[$value['fid']] = $value['fid'];
			}
			$forums = C::t('forum_forum')->fetch_all_by_fid($fids);
			foreach($threads as $thread) {
				if($forums[$thread['fid']]['status'] == 3) {
					$tids .= ','.$thread['tid'];
					$tnum++;
				}
			}

			if(!$srchfid) {
				$srchtxtsql = str_replace('subject LIKE', 'name LIKE', $srchtxtsql);
				$query = C::t('forum_forum')->fetch_all_fid_for_group(0, $_G['setting']['search']['group']['maxsearchresults'], 1, $srchtxtsql);
				foreach($query as $group) {
					$ids .= ','.$group['fid'];
					$num++;
				}
			}
			$allids = array('thread' => $tids, 'group' => $ids);
			$keywords = str_replace('%', '+', $srchtxt);
			$expiration = TIMESTAMP + $cachelife_text;

			$searchid = C::t('common_searchindex')->insert(array(
				'srchmod' => $srchmod,
				'keywords' => $keywords,
				'searchstring' => $searchstring,
				'useip' => $_G['clientip'],
				'uid' => $_G['uid'],
				'dateline' => $_G['timestamp'],
				'expiration' => $expiration,
				'num' => $tnum,
				'ids' => serialize($allids)
			), true);

			!($_G['group']['exempt'] & 2) && updatecreditbyaction('search');
		}

		dheader("location: search.php?mod=group&searchid=$searchid&searchsubmit=yes&kw=".urlencode($keyword));

	}

}

?>