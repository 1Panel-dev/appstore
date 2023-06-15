<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_tag.php 32232 2012-12-03 08:57:08Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$id = intval($_GET['id']);
$type = trim($_GET['type']);
$name = trim($_GET['name']);
$page = intval($_GET['page']);
if($type == 'countitem') {
	$num = 0;
	if($id) {
		$num = C::t('common_tagitem')->count_by_tagid($id);
	}
	include_once template('tag/tag');
	exit();
}
$taglang = lang('tag/template', 'tag');
if($id || $name) {

	$tpp = 20;
	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $tpp;
	if($id) {
		$tag = C::t('common_tag')->fetch_info($id);
	} else {
		if(!preg_match('/^([\x7f-\xff_-]|\w|\s)+$/', $name) || strlen($name) > 20) {
			showmessage('parameters_error');
		}
		$name = addslashes($name);
		$tag = C::t('common_tag')->fetch_info(0, $name);
	}

	if($tag['status'] == 1) {
		showmessage('tag_closed');
	}
	$tagname = $tag['tagname'];
	$id = $tag['tagid'];
	$searchtagname = $name;
	$navtitle = $tagname ? $taglang.' - '.$tagname : $taglang;
	$metakeywords = $tagname ? $taglang.' - '.$tagname : $taglang;
	$metadescription = $tagname ? $taglang.' - '.$tagname : $taglang;


	$showtype = '';
	$count = '';
	$summarylen = 300;

	if($type == 'thread') {
		$showtype = 'thread';
		$tidarray = $threadlist = array();
		$count = C::t('common_tagitem')->select($id, 0, 'tid', '', '', 0, 0, 0, 1);
		if($count) {
			$query = C::t('common_tagitem')->select($id, 0, 'tid', '', '', $start_limit, $tpp);
			foreach($query as $result) {
				$tidarray[$result['itemid']] = $result['itemid'];
			}
			$threadlist = getthreadsbytids($tidarray);
			$multipage = multi($count, $tpp, $page, "misc.php?mod=tag&id=$tag[tagid]&type=thread");
		}
	} elseif($type == 'blog') {
		$showtype = 'blog';
		$blogidarray = $bloglist = array();
		$count = C::t('common_tagitem')->select($id, 0, 'blogid', '', '', 0, 0, 0, 1);
		if($count) {
			$query = C::t('common_tagitem')->select($id, 0, 'blogid', '', '', $start_limit, $tpp);
			foreach($query as $result) {
				$blogidarray[$result['itemid']] = $result['itemid'];
			}
			$bloglist = getblogbyid($blogidarray);

			$multipage = multi($count, $tpp, $page, "misc.php?mod=tag&id=$tag[tagid]&type=blog");
		}
	} else {
		$shownum = 20;

		$tidarray = $threadlist = array();
		$query = C::t('common_tagitem')->select($id, 0, 'tid', '', '', $shownum);
		foreach($query as $result) {
			$tidarray[$result['itemid']] = $result['itemid'];
		}
		$threadlist = getthreadsbytids($tidarray);

		if(helper_access::check_module('blog')) {
			$blogidarray = $bloglist = array();
			$query = C::t('common_tagitem')->select($id, 0, 'blogid', '', '', $shownum);
			foreach($query as $result) {
				$blogidarray[$result['itemid']] = $result['itemid'];
			}
			$bloglist = getblogbyid($blogidarray);
		}

	}

	include_once template('tag/tagitem');

} else {
	$navtitle = $metakeywords = $metadescription = $taglang;
	$viewthreadtags = 100;
	$tagarray = array();
	$query = C::t('common_tag')->fetch_all_by_status(0, '', $viewthreadtags, 0, 0, 'DESC');
	foreach($query as $result) {
		$tagarray[] = $result;
	}
	include_once template('tag/tag');
}

function getthreadsbytids($tidarray) {
	global $_G;

	$threadlist = array();
	if(!empty($tidarray)) {
		loadcache('forums');
		include_once libfile('function_misc', 'function');
		$fids = array();
		foreach(C::t('forum_thread')->fetch_all_by_tid($tidarray) as $result) {
			if($result['displayorder'] >= 0){
				if(!isset($_G['cache']['forums'][$result['fid']]['name'])) {
					$fids[$result['fid']][] = $result['tid'];
				} else {
					$result['name'] = $_G['cache']['forums'][$result['fid']]['name'];
				}
				$threadlist[$result['tid']] = procthread($result);
			}
		}
		if(!empty($fids)) {
			foreach(C::t('forum_forum')->fetch_all_by_fid(array_keys($fids)) as $fid => $forum) {
				foreach($fids[$fid] as $tid) {
					$threadlist[$tid]['forumname'] = $forum['name'];
				}
			}
		}
	}
	return $threadlist;
}

function getblogbyid($blogidarray) {
	global $_G, $summarylen;

	$bloglist = array();
	if(!empty($blogidarray)) {
		$data_blog = C::t('home_blog')->fetch_all($blogidarray, 'dateline', 'DESC');
		$data_blogfield = C::t('home_blogfield')->fetch_all($blogidarray);

		require_once libfile('function/spacecp');
		require_once libfile('function/home');
		$classarr = array();
		foreach($data_blog as $curblogid => $result) {
			$result = array_merge($result, (array)$data_blogfield[$curblogid]);
			$result['dateline'] = dgmdate($result['dateline']);
			$classarr = getclassarr($result['uid']);
			$result['classname'] = $classarr[$result[classid]]['classname'];
			if($result['friend'] == 4) {
				$result['message'] = $result['pic'] = '';
			} else {
				$result['message'] = getstr($result['message'], $summarylen, 0, 0, 0, -1);
			}
			$result['message'] = preg_replace("/&[a-z]+\;/i", '', $result['message']);
			if($result['pic']) {
				$result['pic'] = pic_cover_get($result['pic'], $result['picflag']);
			}
			$bloglist[] = $result;
		}
	}
	return $bloglist;
}
?>