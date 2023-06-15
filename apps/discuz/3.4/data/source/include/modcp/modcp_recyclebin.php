<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_recyclebin.php 27222 2012-01-11 08:01:39Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}


$op = !in_array($op , array('list', 'delete', 'search', 'restore')) ? 'list' : $op;
$do = !empty($_GET['do']) ? dhtmlspecialchars($_GET['do']) : '';

$tidarray = array();
$action = $_GET['action'];

$result = array();
foreach (array('threadoption', 'viewsless', 'viewsmore', 'repliesless', 'repliesmore', 'noreplydays', 'typeid') as $key) {
	$$key = isset($_GET[''.$key]) && is_numeric($_GET[''.$key]) ? intval($_GET[''.$key]) : '';
	$result[$key] = $$key;
}

foreach (array('starttime', 'endtime', 'keywords', 'users') as $key) {
	$$key = isset($_GET[''.$key]) ? trim($_GET[''.$key]) : '';
	$result[$key] = isset($_GET[''.$key]) ? dhtmlspecialchars($_GET[''.$key]) : '';
}

$threadoptionselect = array('','','','','','', '', '', '', '', 999=>'', 888=>'');
$threadoptionselect[$threadoption] = 'selected';

$postlist = array();

$total = $multipage = '';

$cachekey = 'srchresult_recycle_thread'.$_G['fid'];
if($_G['fid'] && $_G['forum']['ismoderator'] && $modforums['recyclebins'][$_G['fid']]) {

	$srchupdate = false;

	if(in_array($_G['adminid'], array(1, 2, 3)) && ($op == 'delete' || $op == 'restore') && submitcheck('dosubmit')) {
		if(!empty($_GET['moderate'])) {
			foreach(C::t('forum_thread')->fetch_all_by_tid_displayorder($_GET['moderate'], -1, '=', $_G['fid']) as $tid) {
				$tidarray[] = $tid['tid'];
			}

			if($tidarray) {
				if($op == 'delete' && $_G['group']['allowclearrecycle']) {
					require_once libfile('function/delete');
					deletethread($tidarray);
				}
				if($op == 'restore') {
					require_once libfile('function/post');
					undeletethreads($tidarray);
				}

				if($_GET['oldop'] == 'search') {
					$srchupdate = true;
				}
			}
		}

		$op = dhtmlspecialchars($_GET['oldop']);

		showmessage('modcp_recyclebin_'.$op.'_succeed', '', array(), array('break' => 1));

	}



	if($op == 'search' &&  submitcheck('searchsubmit')) {

		$conditions = array();

		if($threadoption > 0 && $threadoption < 255) {
			$conditions['specialthread'] = 1;
			$conditions['special'] = $threadoption;
		} elseif($threadoption == 999) {
			$conditions['digest'] = array(1,2,3);
		} elseif($threadoption == 888) {
			$conditions['sticky'] = 1;
		}


		$viewsless !== ''? $conditions['viewsless'] = $viewsless : '';
		$viewsmore !== ''? $conditions['viewsmore'] = $viewsmore : '';
		$repliesless !== ''? $conditions['repliesless'] = $repliesless : '';
		$repliesmore !== ''? $conditions['repliesmore'] = $repliesmore : '';
		$noreplydays !== ''? $conditions['noreplydays'] = $noreplydays : '';
		$starttime != '' ? $conditions['starttime'] = $starttime : '';
		$endtime != '' ? $conditions['endtime'] = $endtime : '';

		if(trim($keywords)) {
			$conditions['keywords'] = $keywords;

		}

		if(trim($users)) {
			$conditions['users'] = trim($users);
		}

		if($_GET['typeid']) {
			$conditions['intype'] = $_GET['typeid'];

		}

		if(!empty($conditions)) {

			$tids = $comma = '';
			$count = 0;
			$conditions['fid'] = $_G['fid'];
			$conditions['sticky'] = 3;
			foreach(C::t('forum_thread')->fetch_all_search($conditions, 0, 0, 1000, 'lastpost') as $thread) {
				$tids .= $comma.$thread['tid'];
				$comma = ',';
				$count ++;
			}

			$result['tids'] = $tids;
			$result['count'] = $count;
			$result['fid'] = $_G['fid'];

			$modsession->set($cachekey, $result, true);

			unset($result, $tids);
			$page = 1;

		} else {
			$op = 'list';
		}
	}

	$page = max(1, intval($_G['page']));
	$total = 0;
	$query = $multipage = '';

	if($op == 'list') {
		$total = C::t('forum_thread')->count_by_fid_typeid_displayorder($_G['fid'], $_GET['typeid'], -1);
		$tpage = ceil($total / $_G['tpp']);
		$page = min($tpage, $page);
		$multipage = multi($total, $_G['tpp'], $page, "$cpscript?mod=modcp&action=$action&op=$op&fid=$_G[fid]&do=$do");
		if($total) {
			$start = ($page - 1) * $_G['tpp'];
			$threads = C::t('forum_thread')->fetch_all_by_fid_typeid_displayorder($_G['fid'], $_GET['typeid'], -1, '=', $start, $_G['tpp']);
		}
	}

	if($op == 'search') {

		$result = $modsession->get($cachekey);

		if($result) {

			if($srchupdate && $result['count'] && $tidarray) {
				$td = explode(',', $result['tids']);
				$newtids = $comma = $newcount = '';
				if(is_array($td)) {
					foreach ($td as $v) {
						$v = intval($v);
						if(!in_array($v, $tidarray)) {
							$newcount ++;
							$newtids .= $comma.$v;
							$comma = ',';
						}
					}
					$result['count'] = $newcount;
					$result['tids'] = $newtids;
					$modsession->set($cachekey, $result, true);
				}
			}

			$threadoptionselect[$result['threadoption']] = 'selected';

			$total = $result['count'];
			$tpage = ceil($total / $_G['tpp']);
			$page = min($tpage, $page);
			$multipage = multi($total, $_G['tpp'], $page, "$cpscript?mod=modcp&action=$action&op=$op&fid=$_G[fid]&do=$do");
			if($total) {
				$start = ($page - 1) * $_G['tpp'];
				$threads = C::t('forum_thread')->fetch_all_by_tid_fid_displayorder(explode(',', $result['tids']), $_G['fid'], -1, 'lastpost', $start, $_G['tpp']);
			}

		}

	}

	$postlist = array();
	if($threads) {
		require_once libfile('function/misc');
		foreach($threads as $thread) {
			$post = procthread($thread);
			$post['modthreadkey'] = modauthkey($post['tid']);
			$postlist[$post['tid']] = $post;
		}
		if($postlist) {
			$tids = array_keys($postlist);
			foreach(C::t('forum_threadmod')->fetch_all_by_tid($tids) as $row) {
				if(empty($postlist[$row['tid']]['reason'])) {
					$postlist[$row['tid']]['reason'] = $row['reason'];
				}
			}
		}
	}

}

?>