<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: collection_edit.php 33065 2013-04-16 10:06:07Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$titlelimit = 30;
$desclimit = 250;
$reasonlimit = 250;

$oplist = array('add', 'edit', 'remove', 'addthread', 'delthread', 'acceptinvite', 'removeworker', 'invite');
if(!in_array($op, $oplist)) {
	$op = '';
}

if(empty($_G['uid'])) {
	showmessage('login_before_enter_home', null, array(), array('showmsg' => true, 'login' => 1));
}

if(empty($op) || $op == 'add') {
	if(!helper_access::check_module('collection')) {
		showmessage('quickclear_noperm');
	}
	$_GET['handlekey'] = 'createcollection';

	$navtitle = lang('core', 'title_collection_create');

	$createdcollectionnum = C::t('forum_collection')->count_by_uid($_G['uid']);
	$reamincreatenum = $_G['group']['allowcreatecollection']-$createdcollectionnum;
	if(!$_G['group']['allowcreatecollection'] || $reamincreatenum <= 0) {
		showmessage('collection_create_exceed_limit');
	}
	if(!$_GET['submitcollection']) {

		include template('forum/collection_add');

	} else {
		if(!submitcheck('collectionsubmit')) {
			showmessage('undefined_action', NULL);
		}
		if(!$_GET['title']) {
			showmessage('collection_edit_checkentire');
		}

		$newCollectionTitle = censor(dhtmlspecialchars($_GET['title']));
		$newCollectionTitle = cutstr($newCollectionTitle, $titlelimit, '');

		$newcollection = array(
		    'name' => $newCollectionTitle,
		    'uid' => $_G['uid'],
		    'username' => $_G['username'],
		    'desc' => dhtmlspecialchars(cutstr(censor($_GET['desc']), $desclimit, '')),
		    'dateline' => $_G['timestamp'],
		    'lastupdate' => $_G['timestamp'],
			'lastvisit' => $_G['timestamp'],
			'keyword' => parse_keyword($_GET['keyword'], true)
		);

		$newctid = C::t('forum_collection')->insert($newcollection, true);

		if($newctid) {
			showmessage('collection_create_succ', 'forum.php?mod=collection&action=view&ctid='.$newctid, array('ctid'=>$newctid, 'title'=>$newCollectionTitle), array('closetime' => '2', 'showmsg' => ($_GET['inajax'] ? '0' : '1')));
		}
	}

} elseif($op == 'edit') {
	$navtitle = lang('core', 'title_collection_edit');

	if(!$ctid) {
		showmessage('undefined_action', NULL);
	}

	if(!$_G['collection']['ctid'] || !checkcollectionperm($_G['collection'], $_G['uid'])) {
		showmessage('collection_permission_deny');
	}

	if(!submitcheck('collectionsubmit')) {

		include template('forum/collection_add');

	} else {
		if(!$_GET['title']) {
			showmessage('collection_edit_checkentire');
		}
		if($_GET['formhash'] != FORMHASH) {
			showmessage('undefined_action', NULL);
		}

		$newCollectionTitle = censor(dhtmlspecialchars($_GET['title']));
		$newCollectionTitle = cutstr($newCollectionTitle, 30, '');

		$newcollection = array(
		    'name' => $newCollectionTitle,
		    'desc' => dhtmlspecialchars(cutstr(censor($_GET['desc']), $desclimit, '')),
			'keyword' => parse_keyword($_GET['keyword'], true)
		);

		C::t('forum_collection')->update($ctid, $newcollection);

		if($_GET['title'] != $_G['collection']['name']) {
			C::t('forum_collectionteamworker')->update_by_ctid($ctid, $_GET['title']);
		}

		showmessage('collection_edit_succ', 'forum.php?mod=collection&action=view&ctid='.$ctid);
	}
} elseif($op == 'remove') {
	if($_GET['formhash'] != FORMHASH) {
		showmessage('undefined_action', NULL);
	}
	if($_G['collection'] && checkcollectionperm($_G['collection'], $_G['uid'])) {
		require_once libfile('function/delete');

		deletecollection($_G['collection']['ctid']);

		showmessage('collection_delete_succ', 'forum.php?mod=collection&op=my');
	} else {
		showmessage('collection_permission_deny');
	}

} elseif($op == 'addthread') {
	if((!$_G['forum_thread'] || !$_G['forum']) && !is_array($_GET['tids'])) {
		showmessage('thread_nonexistence');
	}

	if(!is_array($_GET['tids']) && $_G['forum']['disablecollect']) {
		showmessage('collection_forum_deny', '', array(), array('showdialog' => 1));
	}

	if(!submitcheck('addthread')) {
		$createdcollectionnum = C::t('forum_collection')->count_by_uid($_G['uid']);
		$reamincreatenum = $_G['group']['allowcreatecollection']-$createdcollectionnum;

		$collections = getmycollection($_G['uid']);

		if(count($collections) > 0) {
			$tidrelated = C::t('forum_collectionrelated')->fetch($tid, true);
			$tidcollections = explode("\t", $tidrelated['collection']);
		}
		$allowcollections = array_diff(array_keys($collections), $tidcollections);
		if($reamincreatenum <= 0 && count($allowcollections) <= 0) {
			showmessage('collection_none_avail_collection', '', array(), array('showdialog' => 1));
		}

		include template('forum/collection_select');

	} else {
		if(!$ctid) {
			showmessage('collection_no_selected', '', array(), array('showdialog' => 1));
		}
		if(!is_array($_GET['tids'])) {
			$tid = $_G['tid'];
			$thread[$tid] = &$_G['thread'];
		}
		$collectiondata = C::t('forum_collection')->fetch_all($ctid);
		if(count($collectiondata) < 0) {
			showmessage('undefined_action', NULL);
		} else {
			foreach ($collectiondata as $curcollectiondata) {
				if(!$curcollectiondata['ctid']) {
					showmessage('collection_permission_deny', '', array(), array('showdialog' => 1));
				}

				if(!checkcollectionperm($curcollectiondata, $_G['uid'], true)) {
					showmessage('collection_non_creator', '', array(), array('showdialog' => 1));
				}

				if(!is_array($_GET['tids'])) {
					$checkexistctid[$tid] = C::t('forum_collectionthread')->fetch_by_ctid_tid($curcollectiondata['ctid'], $thread[$tid]['tid']);
					if($checkexistctid[$tid]['ctid']) {
						showmessage('collection_thread_exists', '', array(), array('showdialog' => 1));
					}

					$tids[0] = $tid;
					$checkexist[$tid] = C::t('forum_collectionrelated')->fetch($tid, true);
				} else {
					$thread = C::t('forum_thread')->fetch_all($_GET['tids']);
					foreach ($thread as $perthread) {
						$fids[$perthread['fid']] = $perthread['fid'];
					}
					$fids = array_keys($fids);
					$foruminfo = C::t('forum_forumfield')->fetch_all($fids);
					$tids = array_keys($thread);
					$checkexistctid = C::t('forum_collectionthread')->fetch_all_by_ctid_tid($curcollectiondata['ctid'], $tids);
					$checkexist = C::t('forum_collectionrelated')->fetch_all($tids, true);
				}

				$addsum = 0;
				foreach ($tids as $curtid) {
					$thread_fid = $thread[$curtid]['fid'];
					if(!$checkexistctid[$curtid]['ctid'] && !$foruminfo[$thread_fid]['disablecollect']) {
						$newthread = array(
						    'ctid' => $curcollectiondata['ctid'],
						    'tid' => $thread[$curtid]['tid'],
						    'dateline' => $thread[$curtid]['dateline'],
							'reason' => cutstr(censor(dhtmlspecialchars($_GET['reason'])), $reasonlimit, '')
						);

						C::t('forum_collectionthread')->insert($newthread);
					} else {
						continue;
					}

					if(!$checkexist[$curtid]) {
						C::t('forum_collectionrelated')->insert(array('tid'=>$curtid, 'collection'=>$curcollectiondata['ctid']."\t"));
						$checkexist[$curtid] = 1;
					} else {
						C::t('forum_collectionrelated')->update_collection_by_ctid_tid($curcollectiondata['ctid'], $curtid);
					}
					if(!getstatus($thread[$curtid]['status'], 9)) {
						C::t('forum_thread')->update_status_by_tid($curtid, '256');
					}

					if($_G['uid'] != $thread[$curtid]['authorid']) {
						notification_add($thread[$curtid]['authorid'], "system", 'collection_becollected', array('from_id'=>$_G['collection']['ctid'], 'from_idtype'=>'collectionthread', 'ctid'=>$_G['collection']['ctid'], 'collectionname'=>$_G['collection']['name'], 'tid'=>$curtid, 'threadname'=>$thread[$curtid]['subject']), 1);
					}

					$addsum++;
				}

				if($addsum > 0) {
					$lastpost = array(
						'lastpost' => $thread[$tids[0]]['tid'],
						'lastsubject' => $thread[$tids[0]]['subject'],
						'lastposttime' => $thread[$tids[0]]['dateline'],
						'lastposter' => $thread[$tids[0]]['author']
					);
				   	C::t('forum_collection')->update_by_ctid($curcollectiondata['ctid'], $addsum, 0, 0, $_G['timestamp'], 0, 0, $lastpost);
				}
			}
		}

		showmessage('collection_collect_succ', dreferer(), array(), array('alert'=> 'right', 'closetime' => true, 'locationtime' => true, 'showdialog' => 1));
	}

} elseif($op == 'delthread') {
	if($_GET['formhash'] != FORMHASH) {
		showmessage('undefined_action', NULL);
	}
	if(!$ctid || count($_GET['delthread']) == 0) {
		showmessage('collection_no_thread');
	}

	if(!$_G['collection']['ctid'] || !checkcollectionperm($_G['collection'], $_G['uid'])) {
		showmessage('collection_permission_deny');
	}
	require_once libfile('function/delete');
	deleterelatedtid($_GET['delthread'], $_G['collection']['ctid']);
	$decthread = C::t('forum_collectionthread')->delete_by_ctid_tid($ctid, $_GET['delthread']);

	$lastpost = null;
	if(in_array($_G['collection']['lastpost'], $_GET['delthread']) && ($_G['collection']['threadnum'] - $decthread) > 0) {
		$collection_thread = C::t('forum_collectionthread')->fetch_by_ctid_dateline($ctid);
		if($collection_thread) {
			$thread = C::t('forum_thread')->fetch($collection_thread['tid']);
			$lastpost = array(
				'lastpost' => $thread['tid'],
				'lastsubject' => $thread['subject'],
				'lastposttime' => $thread['dateline'],
				'lastposter' => $thread['authorid']
			);
		}
	}

	C::t('forum_collection')->update_by_ctid($ctid, -$decthread, 0, 0, 0, 0, 0, $lastpost);

	showmessage('collection_remove_thread', 'forum.php?mod=collection&action=view&ctid='.$ctid);
} elseif($op == 'invite') {
	if(!$ctid) {
		showmessage('undefined_action', NULL);
	}

	if(!$_G['collection']['ctid'] || !checkcollectionperm($_G['collection'], $_G['uid'])) {
		showmessage('collection_permission_deny');
	}

	$collectionteamworker = C::t('forum_collectionteamworker')->fetch_all_by_ctid($ctid);

	$submitworkers = count($_GET['users']);

	if((count($collectionteamworker) + $submitworkers) >= $maxteamworkers) {
		showmessage('collection_teamworkers_exceed');
	}

	require_once libfile('function/friend');

	if($_GET['username'] && !$_GET['users']) {
		$_GET['users'][] = $_GET['username'];
	}

	if(!$_GET['users']) {

		if($_POST['formhash']) {
			showmessage('collection_teamworkers_noselect', NULL);
		}

		$friends = array();
		if($space['friendnum']) {
			$query = C::t('home_friend')->fetch_all_by_uid($_G['uid'], 0, 100, true);
			foreach($query as $value) {
				$value['uid'] = $value['fuid'];
				$value['username'] = daddslashes($value['fusername']);
				$friends[] = $value;
			}
		}
		$friendgrouplist = friend_group_list();

		include template('forum/collection_invite');
	} else {
		$invitememberuids = array();
		if(is_array($_GET['users'])) {
			$invitememberuids = C::t('common_member')->fetch_all_uid_by_username($_GET['users']);
		}

		if(!$invitememberuids) {
			showmessage('collection_no_teamworkers');
		}

		if(!friend_check($invitememberuids) || in_array($_G['uid'], $invitememberuids)) {
			showmessage('collection_non_friend');
		}

		$collectionteamworker = array_keys($collectionteamworker);

		if(in_array($invitememberuids, $collectionteamworker)) {
			showmessage('collection_teamworkers_exists');
		}

		foreach($invitememberuids as $invitememberuid) {
			$data = array('ctid'=>$ctid,'uid'=>$invitememberuid,'dateline'=>$_G['timestamp']);

			C::t('forum_collectioninvite')->insert($data, false, true);

			notification_add($invitememberuid, "system", 'invite_collection', array('ctid'=>$_G['collection']['ctid'], 'collectionname'=>$_G['collection']['name'], 'dateline'=>$_G['timestamp']), 1);
		}

		showmessage('collection_invite_succ', 'forum.php?mod=collection&action=view&ctid='.$ctid, array(), array('alert'=> 'right', 'closetime' => true, 'showdialog' => 1));
	}
} elseif($op == 'acceptinvite') {
	if(!submitcheck('ctid', 1)) {
		showmessage('undefined_action', NULL);
	} else {
		$collectioninvite = C::t('forum_collectioninvite')->fetch_by_ctid_uid($ctid, $_G['uid']);
		if(!$collectioninvite['ctid'] || $_GET['dateline'] != $collectioninvite['dateline']) {
			showmessage('undefined_action', NULL);
		}

		$teamworkernum = C::t('forum_collectionteamworker')->count_by_ctid($ctid);
		if($teamworkernum >= $maxteamworkers) {
			showmessage('collection_teamworkers_exceed');
		}

		C::t('forum_collectioninvite')->delete_by_ctid_uid($ctid, $_G['uid']);

		$newworker = array(
			'ctid'=>$ctid,
			'uid'=>$_G['uid'],
			'name'=>$_G['collection']['name'],
			'username'=>$_G['username'],
			'lastvisit' => $_G['timestamp']
		);

		C::t('forum_collectionteamworker')->insert($newworker, false, true);

		showmessage('collection_invite_accept', 'forum.php?mod=collection&action=view&ctid='.$ctid);
	}
} elseif($op == 'removeworker') {
	if(!submitcheck('ctid', 1)) {
		showmessage('undefined_action', NULL);
	} else {
		if($_GET['formhash'] != FORMHASH) {
			showmessage('undefined_action', NULL);
		}

		if(!$_G['collection']['ctid']) {
			showmessage('collection_permission_deny');
		}
		if($_GET['uid'] != $_G['uid']) {
			if($_G['collection']['uid'] != $_G['uid']) {
				showmessage('collection_remove_deny');
			}
			$removeuid = $_GET['uid'];
		} else {
			$removeuid = $_G['uid'];
		}

		$collectionteamworker = array_keys(C::t('forum_collectionteamworker')->fetch_all_by_ctid($ctid));

		if(!in_array($removeuid, $collectionteamworker)) {
			showmessage('collection_teamworkers_nonexists');
		}

		C::t('forum_collectionteamworker')->delete_by_ctid_uid($ctid, $removeuid);

		notification_add($removeuid, "system", 'exit_collection', array('ctid'=>$_G['collection']['ctid'], 'collectionname'=>$_G['collection']['name']), 1);

		if($_GET['inajax']) {
			showmessage('', dreferer(), array(), array('msgtype' => 3, 'showmsg' => 1));
		} else {
			showmessage('collection_teamworkers_exit_succ', 'forum.php?mod=collection&action=view&ctid='.$ctid);
		}
	}
}

?>