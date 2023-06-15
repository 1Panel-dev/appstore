<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_thread.php 31365 2012-08-20 03:19:33Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$opactives['thread'] = 'class="a"';

if(empty($_GET['view'])) $_GET['view'] = 'me';
$_GET['order'] = empty($_GET['order']) ? 'dateline' : $_GET['order'];

$allowviewuserthread = $_G['setting']['allowviewuserthread'];

$perpage = 20;
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = array();
$userlist = array();
$hiddennum = $count = $pricount = 0;
$_GET['from'] = dhtmlspecialchars(preg_replace("/[^\[A-Za-z0-9_\]]/", '', $_GET['from']));
$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'thread',
	'fid' => $_GET['fid'],
	'view' => $_GET['view'],
	'type' => $_GET['type'],
	'order' => $_GET['order'],
	'fuid' => $_GET['fuid'],
	'searchkey' => $_GET['searchkey'],
	'from' => $_GET['from'],
	'filter' => $_GET['filter']
);
$theurl = 'home.php?'.url_implode($gets);
unset($gets['fid']);
$forumurl = 'home.php?'.url_implode($gets);
$multi = '';
$authorid = 0;
$replies = $closed = $displayorder = null;
$dglue = '=';
$vfid = $_GET['fid'] ? intval($_GET['fid']) : null;

require_once libfile('function/misc');
require_once libfile('function/forum');
loadcache(array('forums'));
$fids = $comma = '';
if($_GET['view'] != 'me') {
	$displayorder = 0;
	$dglue = '>=';
}
$f_index = '';
$ordersql = 't.dateline DESC';
$need_count = true;
$viewuserthread = false;
$listcount = 0;

if($_GET['view'] == 'me') {

	if($_GET['from'] == 'space') $diymode = 1;
	$allowview = true;
	$viewtype = in_array($_GET['type'], array('reply', 'thread', 'postcomment')) ? $_GET['type'] : 'thread';
	$filter = in_array($_GET['filter'], array('recyclebin', 'ignored', 'save', 'aduit', 'close', 'common')) ? $_GET['filter'] : '';
	if($space['uid'] != $_G['uid'] && in_array($viewtype, array('reply', 'thread'))) {
		if($allowviewuserthread === -1 && $_G['adminid'] != 1) {
			$allowview = false;
		}
		if($allowview) {
			$viewuserthread = true;
			$viewfids = str_replace("'", '', $allowviewuserthread);
			if(!empty($viewfids)) {
				$viewfids = explode(',', $viewfids);
			}
		}
	}

	if($viewtype == 'thread' && $allowview) {
		$authorid = $space['uid'];




		if($filter == 'recyclebin') {
			$displayorder = -1;
		} elseif($filter == 'aduit') {
			$displayorder = -2;
		} elseif($filter == 'ignored') {
			$displayorder = -3;
		} elseif($filter == 'save') {
			$displayorder = -4;
		} elseif($filter == 'close') {
			$closed = 1;
		} elseif($filter == 'common') {
			$closed = 0;
			$displayorder = 0;
			$dglue = '>=';
		}

		$ordersql = 't.tid DESC';
	} elseif($viewtype == 'postcomment') {
		$posttable = getposttable();
		require_once libfile('function/post');
		$pids = $tids = array();
		$postcommentarr = C::t('forum_postcomment')->fetch_all_by_authorid($_G['uid'], $start, $perpage);
		foreach($postcommentarr as $value) {
			$pids[] = $value['pid'];
			$tids[] = $value['tid'];
		}
		$pids = C::t('forum_post')->fetch_all(0, $pids);
		$tids = C::t('forum_thread')->fetch_all($tids);

		$list = $fids = array();
		foreach($postcommentarr as $value) {
			$value['authorid'] = $pids[$value['pid']]['authorid'];
			$value['fid'] = $pids[$value['pid']]['fid'];
			$value['invisible'] = $pids[$value['pid']]['invisible'];
			$value['dateline'] = $pids[$value['pid']]['dateline'];
			$value['message'] = $pids[$value['pid']]['message'];
			$value['special'] = $tids[$value['tid']]['special'];
			$value['status'] = $tids[$value['tid']]['status'];
			$value['subject'] = $tids[$value['tid']]['subject'];
			$value['digest'] = $tids[$value['tid']]['digest'];
			$value['attachment'] = $tids[$value['tid']]['attachment'];
			$value['replies'] = $tids[$value['tid']]['replies'];
			$value['views'] = $tids[$value['tid']]['views'];
			$value['lastposter'] = $tids[$value['tid']]['lastposter'];
			$value['lastpost'] = $tids[$value['tid']]['lastpost'];
			$value['tid'] = $pids[$value['pid']]['tid'];

			$fids[] = $value['fid'];
			$value['comment'] = messagecutstr($value['comment'], 100);
			$list[] = procthread($value);
		}
		unset($pids, $tids, $postcommentarr);
		if($fids) {
			$fids = array_unique($fids);
			$query = C::t('forum_forum')->fetch_all($fids);
			foreach($query as $forum) {
				$forums[$forum['fid']] = $forum['name'];
			}
		}

		$multi = simplepage(count($list), $perpage, $page, $theurl);
		$need_count = false;

	} elseif($allowview) {
		$invisible = null;

		$postsql = $threadsql = '';
		if($filter == 'recyclebin') {
			$invisible = -5;
		} elseif($filter == 'aduit') {
			$invisible = -2;
		} elseif($filter == 'save' || $filter == 'ignored') {
			$invisible = -3;
			$displayorder = -4;
		} elseif($filter == 'close') {
			$closed = 1;
		} elseif($filter == 'common') {
			$invisible = 0;
			$displayorder = 0;
			$dglue = '>=';
			$closed = 0;
		} else {
			if($space['uid'] != $_G['uid']) {
				$invisible = 0;
			}
		}
		require_once libfile('function/post');
		$posts = C::t('forum_post')->fetch_all_by_authorid(0, $space['uid'], true, 'DESC', $start, $perpage, 0, $invisible, $vfid);
		$listcount = count($posts);
		foreach($posts as $pid => $post) {
			$delrow = false;
			if($post['anonymous'] && $post['authorid'] != $_G['uid']) {
				$delrow = true;
			} elseif($viewuserthread && $post['authorid'] != $_G['uid']) {
				if(($_G['adminid'] != 1 && !empty($viewfids) && !in_array($post['fid'], $viewfids))) {
					$delrow = true;
				}
			}
			if($delrow) {
				unset($posts[$pid]);
				$hiddennum++;
				continue;
			} else {
				$tids[$post['tid']][] = $pid;
				$post['message'] = $post['status'] & 1 && $_G['adminid'] != 1 ? '' : (!getstatus($post['status'], 2) || $post['authorid'] == $_G['uid'] ? messagecutstr($post['message'], 100) : '');
				$posts[$pid] = $post;
			}
		}

		if(!empty($tids)) {

			$threads = C::t('forum_thread')->fetch_all_by_tid_displayorder(array_keys($tids), $displayorder, $dglue, array(), $closed);

			foreach($threads as $tid => $thread) {
				$delrow = false;
				if($_G['adminid'] != 1 && $thread['displayorder'] < 0) {
					$delrow = true;
				} elseif($_G['adminid'] != 1 && $_G['uid'] != $thread['authorid'] && getstatus($thread['status'], 2)) {
					$delrow = true;
				} elseif(!isset($_G['cache']['forums'][$thread['fid']])) {
					if(!$_G['setting']['groupstatus']) {
						$delrow = true;
					} else {
						$gids[$thread['fid']] = $thread['tid'];
					}
				}
				if($delrow) {
					foreach($tids[$tid] as $pid) {
						unset($posts[$pid]);
						$hiddennum++;
					}
					unset($tids[$tid]);
					unset($threads[$tid]);
					continue;
				} else {
					$threads[$tid] = procthread($thread);
					$forums[$thread['fid']] = $threads[$tid]['forumname'];
				}

			}
			if(!empty($gids)) {
				$groupforums = C::t('forum_forum')->fetch_all_name_by_fid(array_keys($gids));
				foreach($gids as $fid => $tid) {
					$threads[$tid]['forumname'] = $groupforums[$fid]['name'];
					$forums[$fid] = $groupforums[$fid]['name'];
				}
			}
			if(!empty($tids)) {
				foreach($tids as $tid => $pids) {
					foreach($pids as $pid) {
						if(!isset($threads[$tid])) {
							unset($posts[$pid]);
							unset($tids[$tid]);
							$hiddennum++;
							continue;
						}
					}
				}
			}
			$list = &$threads;
		}


		$multi = simplepage($listcount, $perpage, $page, $theurl);

		$need_count = false;
	}
	if(!$allowview) {
		$need_count = false;
	}
	$orderactives = array($viewtype => ' class="a"');

} else {

	space_merge($space, 'field_home');

	if($space['feedfriend']) {

		$fuid_actives = array();

		require_once libfile('function/friend');
		$fuid = intval($_GET['fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$authorid = $fuid;
			$fuid_actives = array($fuid=>' selected');
		} else {
			$authorid = explode(',', $space['feedfriend']);
		}

		$query = C::t('home_friend')->fetch_all_by_uid($_G['uid'], 0, 100, true);
		foreach($query as $value) {
			$userlist[] = $value;
		}
	} else {
		$need_count = false;
	}
}

$actives = array($_GET['view'] =>' class="a"');

if($need_count) {

	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$searchkey = dhtmlspecialchars($searchkey);
	}


	loadcache('forums');
	$gids = $fids = $forums = array();

	foreach(C::t('forum_thread')->fetch_all_by_authorid_displayorder($authorid, $displayorder, $dglue, $closed, $searchkey, $start, $perpage, $replies, $vfid) as $tid => $value) {
		if(empty($value['author']) && $value['authorid'] != $_G['uid']) {
			$hiddennum++;
			continue;
		} elseif($viewuserthread && $value['authorid'] != $_G['uid']) {
			if(($_G['adminid'] != 1 && !empty($viewfids) && !in_array($value['fid'], $viewfids)) || $value['displayorder'] < 0) {
				$hiddennum++;
				continue;
			}
		}
		if(!isset($_G['cache']['forums'][$value['fid']])) {
			if(!$_G['setting']['groupstatus']) {
				$hiddennum++;
				continue;
			} else {
				$gids[$value['fid']] = $value['tid'];
			}
		}
		$list[$value['tid']] = procthread($value);
		$forums[$value['fid']] = $list[$value['tid']]['forumname'];
	}

	if(!empty($gids)) {
		$gforumnames = C::t('forum_forum')->fetch_all_name_by_fid(array_keys($gids));
		foreach($gids as $fid => $tid) {
			$list[$tid]['forumname'] = $gforumnames[$fid]['name'];
			$forums[$fid] = $gforumnames[$fid]['name'];
		}
	}

	$threads = &$list;


	if($_GET['view'] != 'all') {
		$listcount = count($list)+$hiddennum;
		$multi = simplepage($listcount, $perpage, $page, $theurl);
	}
}

require_once libfile('function/forumlist');
$forumlist = forumselect(FALSE, 0, intval($_GET['fid']));
dsetcookie('home_diymode', $diymode);

if($_G['uid']) {
	$_GET['view'] = !$_GET['view'] ? 'we' : $_GET['view'];
	$navtitle = lang('core', 'title_'.$_GET['view'].'_thread');
} else {
	$navtitle = lang('core', 'title_thread');
}

if($space['username']) {
	$navtitle = lang('space', 'sb_thread', array('who' => $space['username']));
}
$metakeywords = $navtitle;
$metadescription = $navtitle;
if(!$_G['follow']) {
	include_once template("diy:home/space_thread");
}
?>