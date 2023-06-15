<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_recyclebinpost.php 27222 2012-01-11 08:01:39Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}


$op = !in_array($op , array('list', 'delete', 'search', 'restore')) ? 'list' : $op;
$do = !empty($_GET['do']) ? dhtmlspecialchars($_GET['do']) : '';

$pidarray = array();
$action = $_GET['action'];

$result = array();

foreach (array('starttime', 'endtime', 'keywords', 'users') as $key) {
	$$key = isset($_GET[''.$key]) ? trim($_GET[''.$key]) : '';
	$result[$key] = isset($_GET[''.$key]) ? dhtmlspecialchars($_GET[''.$key]) : '';
}

$postlist = array();
$total = $multipage = '';

$posttableid = intval($_GET['posttableid']);
$posttableselect = getposttableselect();

$cachekey = 'srchresult_recycle_post_'.$posttableid.'_'.$_G['fid'];

if($_G['fid'] && $_G['forum']['ismoderator'] && $modforums['recyclebins'][$_G['fid']]) {

	$srchupdate = false;

	if(in_array($_G['adminid'], array(1, 2, 3)) && ($op == 'delete' || $op == 'restore') && submitcheck('dosubmit')) {
		if($ids = dimplode($_GET['moderate'])) {
			$pidarray = array();
			foreach(C::t('forum_post')->fetch_all($posttableid, $_GET['moderate'], false) as $post) {
				if($post['fid'] != $_G['fid'] || $post['invisible'] != '-5') {
					continue;
				}
				$pidarray[] = $post['pid'];
			}
			if($pidarray) {
				require_once libfile('function/misc');
				if ($op == 'delete' && $_G['group']['allowclearrecycle']){
					recyclebinpostdelete($pidarray, $posttableid);
				}
				if ($op == 'restore') {
					recyclebinpostundelete($pidarray, $posttableid);
				}

				if($_GET['oldop'] == 'search') {
					$srchupdate = true;
				}
			}
		}

		$op = dhtmlspecialchars($_GET['oldop']);

		showmessage('modcp_recyclebinpost_'.$op.'_succeed', '', array(), array('break' => 1));

	}

	if($op == 'search' &&  submitcheck('searchsubmit')) {


		if($starttime || $endtime || trim($keywords) || trim($users)) {

			$pids = array();

			foreach(C::t('forum_post')->fetch_all_by_search($posttableid, null, $keywords, -5, null, null, ($users ? explode(',', str_replace(' ', '', trim($users))) : null), strtotime($starttime), strtotime($endtime), null, null, 0, 1000) as $value) {
				$postlist[] = $value;
				$pids[] = $value['pid'];
			}

			$result['pids'] = implode(',', $pids);
			$result['count'] = count($pids);
			$result['fid'] = $_G['fid'];
			$result['posttableid'] = $posttableid;

			$modsession->set($cachekey, $result, true);

			unset($result, $pids);
			$page = 1;

		} else {
			$op = 'list';
		}
	}

	$page = max(1, intval($_G['page']));
	$total = 0;
	$query = $multipage = '';
	$fields = 'message, useip, attachment, htmlon, smileyoff, bbcodeoff, pid, tid, fid, author, dateline, subject, authorid, anonymous';

	if($op == 'list') {
		$total = C::t('forum_post')->count_by_fid_invisible($posttableid, $_G['fid'], '-5');
		$tpage = ceil($total / $_G['tpp']);
		$page = min($tpage, $page);
		$multipage = multi($total, $_G['tpp'], $page, "$cpscript?mod=modcp&action=$action&amp;op=$op&amp;fid=$_G[fid]&amp;do=$do");
		if($total) {
			$start = ($page - 1) * $_G['tpp'];
			foreach(C::t('forum_post')->fetch_all_by_fid($posttableid, $_G['fid'], true, 'DESC', $start, $_G['tpp'], null, '-5') as $value) {
				$postlist[] = $value;
			}
		}
	}

	if($op == 'search') {

		$result = $modsession->get($cachekey);

		if($result) {

			if($srchupdate && $result['count'] && $pidarray) {
				$pd = explode(',', $result['pids']);
				$newpids = $comma = $newcount = '';
				if(is_array($pd)) {
					foreach ($pd as $v) {
						$v = intval($v);
						if(!in_array($v, $pidarray)) {
							$newcount ++;
							$newpids .= $comma.$v; $comma = ',';
						}
					}
					$result['count'] = $newcount;
					$result['pids'] = $newpids;
					$modsession->set($cachekey, $result, true);
				}
			}

			$total = $result['count'];
			$tpage = ceil($total / $_G['tpp']);
			$page = min($tpage, $page);
			$multipage = multi($total, $_G['tpp'], $page, "$cpscript?mod=modcp&action=$action&amp;op=$op&amp;fid=$_G[fid]&amp;do=$do");
			if($total) {
				$start = ($page - 1) * $_G['tpp'];
				$postlist = C::t('forum_post')->fetch_all_by_pid($posttableid, explode(',', $result['pids']), true, 'DESC', $start, $_G['tpp'], $_G['fid'], -5);
			}

		}

	}

	if($postlist) {
		require_once libfile('function/misc');
		require_once libfile('function/post');
		require_once libfile('function/discuzcode');
		foreach($postlist as $key => $post) {
			$post['modthreadkey'] = modauthkey($post['tid']);
			$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], $_G['forum']['allowimgcode'], $_G['forum']['allowhtml']);
			$post['dateline'] = dgmdate($post['dateline'], 'Y-m-d H:i:s');
			$postlist[$key] = $post;
		}
	}
}

?>