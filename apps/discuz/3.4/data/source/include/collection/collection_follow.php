<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: collection_follow.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$op || !$ctid || $_GET['formhash'] != FORMHASH) {
	showmessage('undefined_action', NULL);
}

if(!$_G['collection']['ctid'] || $_G['collection']['uid'] == $_G['uid']) {
	showmessage('collection_permission_deny');
}
$_GET['handlekey'] = 'followcollection';
if($op == 'follow') {
	$follownum = C::t('forum_collectionfollow')->count_by_uid($_G['uid']);
	if($follownum >= $_G['group']['allowfollowcollection']) {
		showmessage('collection_follow_limited', '', array('limit' => $_G['group']['allowfollowcollection']), array('closetime' => '2', 'showmsg' => '1'));
	}

	$collectionfollow = C::t('forum_collectionfollow')->fetch_by_ctid_uid($ctid, $_G['uid']);
	if(!$collectionfollow['ctid']) {
		$data = array(
		    'uid' => $_G['uid'],
		    'username' => $_G['username'],
		    'ctid' => $ctid,
		    'dateline' => $_G['timestamp'],
			'lastvisit' => $_G['timestamp']
		);

		C::t('forum_collectionfollow')->insert($data);
		C::t('forum_collection')->update_by_ctid($ctid, 0, 1, 0);

		if($_G['collection']['uid'] != $_G['uid']) {
			updatecreditbyaction('followedcollection', $_G['collection']['uid']);
			notification_add($_G['collection']['uid'], "system", 'collection_befollowed', array('from_id'=>$_G['collection']['ctid'], 'from_idtype'=>'collectionfollow', 'ctid'=>$_G['collection']['ctid'], 'collectionname'=>$_G['collection']['name']), 1);
		}

		showmessage('collection_follow_succ', dreferer(), array('status'=>1), array('closetime' => '2', 'showmsg' => '1'));
	}


} elseif($op == 'unfo') {
	$collectionfollow = C::t('forum_collectionfollow')->fetch_by_ctid_uid($ctid, $_G['uid']);
	if($collectionfollow['ctid']) {
		C::t('forum_collectionfollow')->delete_by_ctid_uid($ctid, $_G['uid']);
		C::t('forum_collection')->update_by_ctid($ctid, 0, -1, 0);
		showmessage('collection_unfollow_succ', dreferer(), array('status'=>2), array('closetime' => '2', 'showmsg' => '1'));
	}
}

?>