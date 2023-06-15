<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_thread.php 28845 2012-03-15 00:59:32Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

$op = !in_array($op , array('thread', 'post')) ? 'thread' : $op;
$do = getgpc('do') ? dhtmlspecialchars(getgpc('do')) : '';

$modtpl = $op ==  'post' ? 'modcp_post' : 'modcp_thread';
$modtpl = 'forum/'.$modtpl;

$threadoptionselect = array('','','','','','', '', '', '', '', 999=>'', 888=>'');
$threadoptionselect[getgpc('threadoption')] = 'selected';


if($op == 'thread') {

	$result = array();

	foreach (array('threadoption', 'viewsless', 'viewsmore', 'repliesless', 'repliesmore', 'noreplydays', 'typeid') as $key) {
		$_GET[''.$key] = isset($_GET[''.$key]) && is_numeric($_GET[''.$key]) ? intval($_GET[''.$key]) : '';
		$result[$key] = $_GET[''.$key];
	}

	foreach (array('starttime', 'endtime', 'keywords', 'users') as $key) {
		$result[$key] = isset($_GET[''.$key]) ? dhtmlspecialchars($_GET[''.$key]) : '';
	}

	if($_G['fid'] && $_G['forum']['ismoderator']) {

		if($do == 'search' &&  submitcheck('submit', 1)) {

			$conditions = array();
			if($_GET['threadoption'] > 0 && $_GET['threadoption'] < 255) {
				$conditions['specialthread'] = 1;
				$conditions['special'] = $_GET['threadoption'];
			} elseif($_GET['threadoption'] == 999) {
				$conditions['digest'] = array(1,2,3);
			} elseif($_GET['threadoption'] == 888) {
				$conditions['sticky'] = 1;
			}


			$_GET['viewsless'] !== ''? $conditions['viewsless'] = $_GET['viewsless'] : '';
			$_GET['viewsmore'] !== ''? $conditions['viewsmore'] = $_GET['viewsmore'] : '';
			$_GET['repliesless'] !== ''? $conditions['repliesless'] = $_GET['repliesless'] : '';
			$_GET['repliesmore'] !== ''? $conditions['repliesmore'] = $_GET['repliesmore'] : '';
			$_GET['noreplydays'] !== ''? $conditions['noreplydays'] = $_GET['noreplydays'] : '';
			$_GET['starttime'] != '' ? $conditions['starttime'] = $_GET['starttime'] : '';
			$_GET['endtime'] != '' ? $conditions['endtime'] = $_GET['endtime'] : '';

			if(trim($_GET['keywords'])) {

				$conditions['keywords'] = $_GET['keywords'];
			}

			if(trim($_GET['users'])) {
				$conditions['users'] = trim($_GET['users']);
			}

			if($_GET['typeid']) {
				$conditions['intype'] = $_GET['typeid'];
			}

			if(!empty($conditions)) {
				$conditions['inforum'] = $_G['fid'];
				if(!isset($conditions['sticky'])) $conditions['sticky'] = 0;
				$tids = $comma = '';
				$count = 0;
				foreach(C::t('forum_thread')->fetch_all_search($conditions, 0, 0, 1000, 'displayorder DESC, lastpost') as $thread) {
					$tids .= $comma.$thread['tid'];
					$comma = ',';
					$count ++;
				}

				$result['tids'] = $tids;
				$result['count'] = $count;
				$result['fid'] = $_G['fid'];

				$modsession->set('srchresult', $result, true);

				unset($result, $tids);
				$do = 'list';
				$page = 1;

			} else {
				$do = '';
			}
		}

		$page = $_G['page'];
		$total = 0;
		$query = $multipage = '';

		if(empty($do)) {

			$total = C::t('forum_thread')->count_by_fid_typeid_displayorder($_G['fid'], $_GET['typeid'], 0, '>=');
			$tpage = ceil($total / $_G['tpp']);
			$page = min($tpage, $page);
			$multipage = multi($total, $_G['tpp'], $page, "$cpscript?mod=modcp&amp;action=$_GET[action]&amp;op=$op&amp;fid=$_G[fid]&amp;do=$do&amp;posttableid=$posttableid");
			if($total) {
				$start = ($page - 1) * $_G['tpp'];
				$threads = C::t('forum_thread')->fetch_all_by_fid_typeid_displayorder($_G['fid'], $_GET['typeid'], 0, '>=', $start, $_G['tpp']);
			}

		} else {

			$result = $modsession->get('srchresult');
			$threadoptionselect[$result['threadoption']] = 'selected';

			if($result['fid'] == $_G['fid']) {
				$total = $result['count'];
				$tpage = ceil($total / $_G['tpp']);
				$page = min($tpage, $page);
				$multipage = multi($total, $_G['tpp'], $page, "$cpscript?mod=modcp&amp;action=$_GET[action]&amp;op=$op&amp;fid=$_G[fid]&amp;do=$do&amp;posttableid=$posttableid");
				if($total) {
					$start = ($page - 1) * $_G['tpp'];
					$threads = C::t('forum_thread')->fetch_all_by_tid_fid_displayorder(explode(',', $result['tids']), null, null, 'lastpost', $start, $_G['tpp']);
				}
			}
		}

		$postlist = array();
		if(!empty($threads)) {
			require_once libfile('function/misc');
			foreach($threads as $thread) {
				$postlist[] = procthread($thread);
			}
		}
	}
	return;
}


if($op == 'post') {

	$error = 0;

	$result = array();

	$_GET['starttime'] = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", getgpc('starttime')) ? dgmdate(TIMESTAMP - 86400 * ($_G['adminid'] == 2 ? 13 : ($_G['adminid'] == 3 ? 6 : 60)), 'Y-m-d') : getgpc('starttime');
	$_GET['endtime'] = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", getgpc('endtime')) ? dgmdate(TIMESTAMP, 'Y-m-d') : getgpc('endtime');

	foreach (array('threadoption', 'starttime', 'endtime', 'keywords', 'users', 'useip') as $key) {
		$$key = isset($_GET[''.$key]) ? trim($_GET[''.$key]) : '';
		$result[$key] = dhtmlspecialchars($$key);
	}

	$threadoptionselect = range(1, 3);

	$posttableid = intval($_GET['posttableid']);
	$posttableselect = getposttableselect();

	$cachekey = 'srchresult_p_'.$posttableid.'_'.$_G['fid'];
	$fidadd = '';
	$fidaddarr = array();
	if($_G['fid'] && $modforums['list'][$_G['fid']]) {
		$fidaddarr = array($_G['fid']);
		$fidadd = "AND fid='$_G[fid]'";
	} else {
		if($_G['adminid'] == 1 && $_G['adminid'] == $_G['groupid']) {
			$fidadd = '';
		} elseif(!$modforums['fids']) {
			$fidaddarr = array(0);
			$fidadd = 'AND 0 ';
		} else {
			$fidaddarr = explode(',', $modforums['fids']);
			$fidadd = "AND fid in($modforums[fids])";
		}
	}

	if($do == 'delete' && submitcheck('deletesubmit')) {

		if(!$_G['group']['allowmassprune']) {
			$error = 4;
			return;
		}

		$pidsdelete = $tidsdelete = array();
		$prune = array('forums' => array(), 'thread' => array());

		if($pids = dimplode($_GET['delete'])) {
			$result = $modsession->get($cachekey);
			$result['pids'] = explode(',', $result['pids']);
			$keys = array_flip($result['pids']);
			foreach(C::t('forum_post')->fetch_all($posttableid, $_GET['delete'], false) as $post) {
				if($fidaddarr && !in_array($post['fid'], $fidaddarr)) {
					continue;
				}
				$prune['forums'][$post['fid']] = $post['fid'];
				$pidsdelete[$post['fid']][$post['pid']] = $post['pid'];
				$pids_tids[$post['pid']] = $post['tid'];
				if($post['first']) {
					$tidsdelete[$post['pid']] = $post['tid'];
				} else {
					@$prune['thread'][$post['tid']]++;
				}
				$key = $keys[$post['pid']];
				unset($result['pids'][$key]);
			}
			$result['pids'] = implode(',', $result['pids']);
			$result['count'] = count($result['pids']);
			$modsession->set($cachekey, $result, true);
			unset($result);
		}

		if($pidsdelete) {
			require_once libfile('function/post');
			require_once libfile('function/delete');
			$forums = C::t('forum_forum')->fetch_all($prune['forums']);
			foreach($pidsdelete as $fid => $pids) {
				foreach($pids as $pid) {
					if(!$tidsdelete[$pid]) {
						$deletedposts = deletepost($pid, 'pid', !getgpc('nocredit'), $posttableid, $forums[$fid]['recyclebin']);
						updatemodlog($pids_tids[$pid], 'DLP');
					} else {
						$deletedthreads = deletethread(array($tidsdelete[$pid]), false, !getgpc('nocredit'), $forums[$fid]['recyclebin']);
						updatemodlog($tidsdelete[$pid], 'DEL');
					}
				}
			}
			if(count($prune['thread']) < 50) {
				foreach($prune['thread'] as $tid => $decrease) {
					updatethreadcount($tid);
				}
			} else {
				$repliesarray = array();
				foreach($prune['thread'] as $tid => $decrease) {
					$repliesarray[$decrease][] = $tid;
				}
				foreach($repliesarray as $decrease => $tidarray) {
					C::t('forum_thread')->increase($tidarray, array('replies'=>-$decrease));
				}
			}

			foreach(array_unique($prune['forums']) as $id) {
				updateforumcount($id);
			}

		}

		$do = 'list';

		showmessage('modcp_thread_delete_succeed', '', array(), array('break' => 1));
	}

	if($do == 'search' && submitcheck('searchsubmit', 1)) {

		if(($starttime == '0' && $endtime == '0') || ($keywords == '' && $useip == '' && $users == '')) {
			$error = 1;
			return ;
		}

		$sql = '';

		if($threadoption == 1) {
			$first = 1;
		} elseif($threadoption == 2) {
			$first = 0;
		}

		if($starttime != '0') {
			$starttime = strtotime($starttime);
		}

		if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-m-d')) {
			if($endtime != '0') {
				$endtime = strtotime($endtime);
			}
		} else {
			$endtime = TIMESTAMP;
		}

		if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 14) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 7)) {
			$error = '2';
			return;
		}

		if($users != '') {
			$uids = C::t('common_member')->fetch_all_uid_by_username(array_map('trim', explode(',', $users)));
			if(!$uids) {
				$uids = array(0);
			}
		}

		if(trim($keywords)) {
			foreach(explode(',', str_replace(' ', '', $keywords)) as $value) {
				if(strlen($value) <= 3) {
					$error = 3;
					return;
				}
			}
		}

		$useip = trim($useip);
		if($useip != '') {
			$useip = str_replace('*', '%', $useip);
		}

		if($uids || $keywords || $useip) {

			$pids = array();
			foreach(C::t('forum_post')->fetch_all_by_search($posttableid, null, $keywords, 0, $fidaddarr, $uids, null, $starttime, $endtime, $useip, $first, 0, 1000) as $post) {
				$pids[] = $post['pid'];
			}

			$result['pids'] = implode(',', $pids);
			$result['count'] = count($pids);
			$result['fid'] = $_G['fid'];
			$result['posttableid'] = $posttableid;

			$modsession->set($cachekey, $result, true);

			unset($result, $pids);
			$do = 'list';
			$page = 1;

		} else {
			$do = '';
		}
	}

	$page = max(1, intval($_G['page']));
	$total = 0;
	$query = $multipage = '';

	if($do == 'list') {
		$postarray = array();
		$result = $modsession->get($cachekey);
		$threadoptionselect[$result['threadoption']] = 'selected';

		if($result['fid'] == $_G['fid']) {
			$total = $result['count'];
			$tpage = ceil($total / $_G['tpp']);
			$page = min($tpage, $page);
			$multipage = multi($total, $_G['tpp'], $page, "$cpscript?mod=modcp&amp;action=$_GET[action]&amp;op=$op&amp;fid=$_G[fid]&amp;do=$do&amp;posttableid=$posttableid");
			if($total && $result['pids']) {
				$start = ($page - 1) * $_G['tpp'];
				$tids = array();
				$postlist = C::t('forum_post')->fetch_all_by_pid($result['posttableid'], explode(',', $result['pids']), true, 'DESC', $start, $_G['tpp']);
				foreach($postlist as $post) {
					$tids[$post['tid']] = $post['tid'];
				}
				$threadlist = C::t('forum_thread')->fetch_all($tids);
				foreach($postlist as $post) {
					$post['tsubject'] = $threadlist[$post['tid']]['subject'];
					$postarray[] = $post;
				}
				unset($threadlist, $postlist, $tids);
			}
		}
	}

	$postlist = array();

	if($postarray) {
		require_once libfile('function/post');
		foreach($postarray as $post) {
			$post['dateline'] = dgmdate($post['dateline']);
			$post['message'] = messagecutstr($post['message'], 200);
			$post['forum'] = $modforums['list'][$post['fid']];
			$post['modthreadkey'] = modauthkey($post['tid']);
			$postlist[] = $post;
		}
	}

}

?>