<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_doing.php 33714 2013-08-07 01:42:26Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
$id = empty($_GET['id'])?0:intval($_GET['id']);
if(helper_access::check_module('doing')) {
	if(submitcheck('addsubmit')) {

		if(!checkperm('allowdoing')) {
			showmessage('no_privilege_doing');
		}

		cknewuser();

		$waittime = interval_check('post');
		if($waittime > 0) {
			showmessage('operating_too_fast', '', array('waittime' => $waittime));
		}

		$message = getstr($_POST['message'], 200, 0, 0, 1);
		$message = preg_replace("/\<br.*?\>/i", ' ', $message);
		if(strlen($message) < 1) {
			showmessage('should_write_that');
		}

		$message = censor($message, NULL, TRUE, TRUE);
		if(is_array($message) && $message['message']) {
			showmessage('do_success', dreferer(), array('message'=>$message['message']));
		}

		if(censormod($message) || $_G['group']['allowdoingmod']) {
			$doing_status = 1;
		} else {
			$doing_status = 0;
		}


		$setarr = array(
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'message' => $message,
			'ip' => $_G['clientip'],
			'port' => $_G['remoteport'],
			'status' => $doing_status,
		);
		$newdoid = C::t('home_doing')->insert($setarr, 1);

		$setarr = array('recentnote'=>$message, 'spacenote'=>$message);
		$credit = $experience = 0;
		$extrasql = array('doings' => 1);

		updatecreditbyaction('doing', 0, $extrasql);

		C::t('common_member_field_home')->update($_G['uid'], $setarr);

		if($_POST['to_signhtml'] && $_G['group']['maxsigsize']) {
			if($_G['group']['maxsigsize'] < 200) {
				$signhtml = getstr($_POST['message'], $_G['group']['maxsigsize'], 0, 0, 1);
				$signhtml = preg_replace("/\<br.*?\>/i", ' ', $signhtml);
			} else {
				$signhtml = $message;
			}
			C::t('common_member_field_forum')->update($_G['uid'], array('sightml'=>$signhtml));
		}

		if(helper_access::check_module('feed') && ckprivacy('doing', 'feed') && $doing_status == '0') {
			$feedarr = array(
				'appid' => '',
				'icon' => 'doing',
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'dateline' => $_G['timestamp'],
				'title_template' => lang('feed', 'feed_doing_title'),
				'title_data' => serialize(array('message'=>$message)),
				'body_template' => '',
				'body_data' => '',
				'id' => $newdoid,
				'idtype' => 'doid'
			);
			C::t('home_feed')->insert($feedarr);
		}
		if($doing_status == '1') {
			updatemoderate('doid', $newdoid);
			manage_addnotify('verifydoing');
		}

		require_once libfile('function/stat');
		updatestat('doing');
		C::t('common_member_status')->update($_G['uid'], array('lastpost' => TIMESTAMP), 'UNBUFFERED');
		if(!empty($_GET['fromcard'])) {
			showmessage($message.lang('spacecp','card_update_doing'));
		} else {
			showmessage('do_success', dreferer(), array('doid' => $newdoid), $_GET['spacenote'] ? array('showmsg' => false):array('header' => true));
		}

	} elseif (submitcheck('commentsubmit')) {

		if(!checkperm('allowdoing')) {
			showmessage('no_privilege_doing_comment');
		}
		cknewuser();

		$waittime = interval_check('post');
		if($waittime > 0) {
			showmessage('operating_too_fast', '', array('waittime' => $waittime));
		}

		$message = getstr($_POST['message'], 200, 0, 0, 1);
		$message = preg_replace("/\<br.*?\>/i", ' ', $message);
		if(strlen($message) < 1) {
			showmessage('should_write_that');
		}
		$message = censor($message);


		$updo = array();
		if($id) {
			$updo = C::t('home_docomment')->fetch($id);
		}
		if(empty($updo) && $doid) {
			$updo = C::t('home_doing')->fetch($doid);
		}
		if(empty($updo)) {
			showmessage('docomment_error');
		} else {
			if(isblacklist($updo['uid'])) {
				showmessage('is_blacklist');
			}
		}

		$updo['id'] = intval($updo['id']);
		$updo['grade'] = intval($updo['grade']);

		$setarr = array(
			'doid' => $updo['doid'],
			'upid' => $updo['id'],
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'message' => $message,
			'ip' => $_G['clientip'],
			'grade' => $updo['grade']+1
		);

		if($updo['grade'] >= 3) {
			$setarr['upid'] = $updo['upid'];
		}

		$newid = C::t('home_docomment')->insert($setarr, true);

		C::t('home_doing')->update_replynum_by_doid(1, $updo['doid']);

		if($updo['uid'] != $_G['uid']) {
			notification_add($updo['uid'], 'comment', 'doing_reply', array(
				'url'=>"home.php?mod=space&uid=$updo[uid]&do=doing&view=me&doid=$updo[doid]&highlight=$newid",
				'from_id'=>$updo['doid'],
				'from_idtype'=>'doid'));
			updatecreditbyaction('comment', 0, array(), 'doing'.$updo['doid']);
		}

		include_once libfile('function/stat');
		updatestat('docomment');
		C::t('common_member_status')->update($_G['uid'], array('lastpost' => TIMESTAMP), 'UNBUFFERED');
		showmessage('do_success', dreferer(), array('doid' => $updo['doid']));
	}
}
if($_GET['op'] == 'delete') {

	if(submitcheck('deletesubmit')) {
		if($id) {
			$allowmanage = checkperm('managedoing');
			if($value = C::t('home_docomment')->fetch($id)) {
				$home_doing = C::t('home_doing')->fetch($value['doid']);
				$value['duid'] = $home_doing['uid'];
				if($allowmanage || $value['uid'] == $_G['uid'] || $value['duid'] == $_G['uid'] ) {
					C::t('home_docomment')->update($id, array('uid' => 0, 'username' => '', 'message' => ''));
					if($value['uid'] != $_G['uid'] && $value['duid'] != $_G['uid']) {
						batchupdatecredit('comment', $value['uid'], array(), -1);
					}
					C::t('home_doing')->update_replynum_by_doid(-1, $updo['doid']);
				}
			}
		} else {
			require_once libfile('function/delete');
			deletedoings(array($doid));
		}

		dheader('location: '.dreferer());
		exit();
	}

} elseif ($_GET['op'] == 'getcomment') {

	include_once(DISCUZ_ROOT.'./source/class/lib/lib_tree.php');
	$tree = new lib_tree();

	$list = array();
	$highlight = 0;
	$count = 0;

	if(empty($_GET['close'])) {
		foreach(C::t('home_docomment')->fetch_all_by_doid($doid) as $value) {
			$tree->setNode($value['id'], $value['upid'], $value);
			$count++;
			if($value['uid'] == $space['uid']) $highlight = $value['id'];
		}
	}

	if($count) {
		$values = $tree->getChilds();
		foreach ($values as $key => $vid) {
			$one = $tree->getValue($vid);
			$one['layer'] = $tree->getLayer($vid) * 2;
			$one['style'] = "padding-left:{$one['layer']}em;";
			if($one['layer'] > 0){
				if($one['layer']%3 == 2) {
					$one['class'] = ' dtls';
				} else {
					$one['class'] = ' dtll';
				}
			}
			if($one['id'] == $highlight && $one['uid'] == $space['uid']) {
				$one['style'] .= 'color:#F60;';
			}
			$list[] = $one;
		}
	}
} elseif ($_GET['op'] == 'spacenote') {
	space_merge($space, 'field_home');
}

include template('home/spacecp_doing');

?>