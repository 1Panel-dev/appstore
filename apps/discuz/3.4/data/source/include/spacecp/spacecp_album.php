<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_album.php 34045 2013-09-25 03:39:47Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$albumid = empty($_GET['albumid'])?0:intval($_GET['albumid']);
$picid = empty($_GET['picid'])?0:intval($_GET['picid']);

if($_GET['op'] == 'edit') {

	if($albumid < 1) {
		showmessage('photos_do_not_support_the_default_settings', "home.php?mod=spacecp&ac=album&uid=$_G[uid]&op=editpic&quickforward=1");
	}

	if(!$album = C::t('home_album')->fetch($albumid)) {
		showmessage('album_does_not_exist');
	}

	if($album['uid'] != $_G['uid'] && !checkperm('managealbum')) {
		showmessage('no_privilege_album_edit');
	}

	if(submitcheck('editsubmit')) {
		$_POST['albumname'] = getstr($_POST['albumname'], 50);
		$_POST['albumname'] = censor($_POST['albumname']);
		if(empty($_POST['albumname'])) {
			showmessage('album_name_errors');
		}

		$_POST['friend'] = intval($_POST['friend']);
		$_POST['target_ids'] = '';
		if($_POST['friend'] == 2) {
			$uids = array();
			$names = empty($_POST['target_names'])?array():explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_names']));
			if($names) {
				$uids = C::t('common_member')->fetch_all_uid_by_username($names);
			}
			if(empty($uids)) {
				$_POST['friend'] = 3;
			} else {
				$_POST['target_ids'] = implode(',', $uids);
			}
		} elseif($_POST['friend'] == 4) {
			$_POST['password'] = trim($_POST['password']);
			if($_POST['password'] == '') $_POST['friend'] = 0;
		}
		if($_POST['friend'] !== 2) {
			$_POST['target_ids'] = '';
		}
		if($_POST['friend'] !== 4) {
			$_POST['password'] = '';
		}

		$_POST['catid'] = intval($_POST['catid']);
		if($_POST['catid'] != $album['catid']) {
			if($album['catid']) {
				C::t('home_album_category')->update_num_by_catid('-1', $album['catid'], true);
			}
			if($_POST['catid']) {
				C::t('home_album_category')->update_num_by_catid('1', $_POST['catid']);
			}
		}

		C::t('home_album')->update($albumid, array('albumname'=>$_POST['albumname'], 'catid'=>$_POST['catid'], 'friend'=>$_POST['friend'], 'password'=>$_POST['password'], 'target_ids'=>$_POST['target_ids'], 'depict'=>dhtmlspecialchars($_POST['depict'])));
		showmessage('spacecp_edit_ok', "home.php?mod=spacecp&ac=album&op=edit&albumid=$albumid");
	}

	$album['target_names'] = '';

	$friendarr = array($album['friend'] => ' selected');

	$passwordstyle = $selectgroupstyle = 'display:none';
	if($album['friend'] == 4) {
		$passwordstyle = '';
	} elseif($album['friend'] == 2) {
		$selectgroupstyle = '';
		if($album['target_ids']) {
			$names = array();
			foreach(C::t('common_member')->fetch_all($album['target_ids']) as $uid => $value) {
				$names[$uid] = $value['username'];
			}
			$album['target_names'] = implode(' ', $names);
		}
	}

	require_once libfile('function/friend');
	$groups = friend_group_list();

	if($_G['setting']['albumcategorystat']) {
		loadcache('albumcategory');
		$category = $_G['cache']['albumcategory'];

		$categoryselect = '';
		if($category) {
			$categoryselect = "<select id=\"catid\" name=\"catid\" width=\"120\"><option value=\"0\">------</option>";
			foreach ($category as $value) {
				if($value['level'] == 0) {
					$selected = $album['catid'] == $value['catid']?' selected':'';
					$categoryselect .= "<option value=\"$value[catid]\"{$selected}>$value[catname]</option>";
					if(!$value['children']) {
						continue;
					}
					foreach ($value['children'] as $catid) {
						$selected = $album['catid'] == $catid?' selected':'';
						$categoryselect .= "<option value=\"{$category[$catid][catid]}\"{$selected}>-- {$category[$catid][catname]}</option>";
						if($category[$catid]['children']) {
							foreach ($category[$catid]['children'] as $catid2) {
								$selected = $album['catid'] == $catid2?' selected':'';
								$categoryselect .= "<option value=\"{$category[$catid2][catid]}\"{$selected}>---- {$category[$catid2][catname]}</option>";
							}
						}
					}
				}
			}
			$categoryselect .= "</select>";
		}
	}

} elseif($_GET['op'] == 'delete') {

	if(!$album = C::t('home_album')->fetch($albumid)) {
		showmessage('album_does_not_exist');
	}

	if($album['uid'] != $_G['uid'] && !checkperm('managealbum')) {
		showmessage('no_privilege_album_del');
	}

	$albums = getalbums($album['uid']);
	if(empty($albums[$albumid])) {
		showmessage('no_privilege_album_delother');
	}

	if(submitcheck('deletesubmit')) {
		$_POST['moveto'] = intval($_POST['moveto']);
		if($_POST['moveto'] < 0) {
			require_once libfile('function/delete');
			deletealbums(array($albumid));
		} else {
			if($_POST['moveto'] > 0 && $_POST['moveto'] != $albumid && !empty($albums[$_POST['moveto']])) {
				C::t('home_pic')->update_for_albumid($albumid, array('albumid'=>$_POST['moveto']));
				album_update_pic($_POST['moveto']);
			} else {
				C::t('home_pic')->update_for_albumid($albumid, array('albumid'=>0));
			}
			C::t('home_album')->delete($albumid);
		}
		showmessage('do_success', "home.php?mod=space&uid=$_GET[uid]&do=album&view=me");
	}
} elseif($_GET['op'] == 'editpic') {

	$managealbum = checkperm('managealbum');

	require_once libfile('class/bbcode');

	if($albumid > 0) {
		if(!$album = C::t('home_album')->fetch($albumid)) {
			showmessage('album_does_not_exist', 'home.php?mod=space&uid='.$_G['uid'].'&do=album&view=me', array(), array('return' => true));
		}

		if($album['uid'] != $_G['uid'] && !$managealbum) {
			showmessage('no_privilege_pic_edit', 'home.php?mod=space&uid='.$_G['uid'].'&do=album&view=me', array(), array('return' => true));
		}
	} else {
		$album['uid'] = $_G['uid'];
	}
	if(submitcheck('editpicsubmit')) {
		$return = true;
		if($_GET['subop'] == 'update') {
			foreach ($_POST['title'] as $picid => $value) {
				if($value == $_GET['oldtitle'][$picid]) {
					continue;
				}
				$title = getstr($value, 150);
				$title = censor($title, NULL, FALSE, FALSE);
				if(censormod($title) || $_G['group']['allowuploadmod']) {
					$pic_status = 1;
					updatemoderate("picid", $picid);
					manage_addnotify('verifypic');
				} else {
					$pic_status = 0;
				}
				$wherearr = array('picid'=>$picid);
				if(!$managealbum) $wherearr['uid']  = $_G['uid'];
				C::t('home_pic')->update($picid, array('title'=>$title, 'status' => $pic_status));
			}
		}
		if($_GET['subop'] == 'delete') {
			if($_POST['ids']) {
				require_once libfile('function/delete');
				deletepics($_POST['ids']);

				if($albumid > 0) $return = album_update_pic($albumid);
			}

		} elseif($_GET['subop'] == 'move') {
			if($_POST['ids']) {
				$sqluid = $managealbum ? '' : $_G['uid'];
				$_POST['newalbumid'] = intval($_POST['newalbumid']);
				if($_POST['newalbumid']) {
					if(!$album = C::t('home_album')->fetch($_POST['newalbumid'], $sqluid)) {
						$_POST['newalbumid'] = 0;
					}
				}
				if($managealbum) {
					$updatecount = C::t('home_pic')->update($_POST['ids'], array('albumid' => $_POST['newalbumid']));
				}else {
					$updatecount = C::t('home_pic')->update_for_uid($_G['uid'], $_POST['ids'], array('albumid' => $_POST['newalbumid']));
				}
				if($updatecount) {
					if($albumid>0) {
						C::t('home_album')->update_num_by_albumid($albumid, -$updatecount, 'picnum', $sqluid);
						$return = album_update_pic($albumid);
					}
					if($_POST['newalbumid']) {
						C::t('home_album')->update_num_by_albumid($_POST['newalbumid'], $updatecount, 'picnum', $sqluid);
						$return = album_update_pic($_POST['newalbumid']);
					}
				}
			}

		}

		$url = $return ? "home.php?mod=spacecp&ac=album&op=editpic&albumid=$albumid&page=$_POST[page]" : 'home.php?mod=space&uid='.$_G['uid'].'&do=album&view=me';
		if($_G['inajax']) {
			showmessage('do_success', $url, array('title' => $title),  array('showdialog' => 3, 'showmsg' => true, 'closetime' => true));
		} else {
			showmessage('do_success', $url);
		}
	}

	$perpage = 10;
	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);


	if($albumid > 0) {
		$count = $picid?1:$album['picnum'];
	} else {
		$count = C::t('home_pic')->fetch_all_by_albumid($albumid, 0, 0, $picid, 0, 0, $_G['uid'], true);
	}

	$list = array();
	if($count) {
		if($page > 1 && $start >=$count) {
			$page--;
			$start = ($page-1)*$perpage;
		}
		$bbcode = & bbcode::instance();
		$query = C::t('home_pic')->fetch_all_by_albumid($albumid, $start, $perpage, $picid, 0, 1, ($albumid > 0 ? 0 : $_G['uid']));
		foreach($query as $value) {
			if($picid) {
				$value['checked'] = ' checked';
			}
			$value['title'] = $bbcode->html2bbcode($value['title']);
			$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
			$value['bigpic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote'], 0);
			$list[] = $value;
		}
	}

	$multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=album&op=editpic&albumid=$albumid");

	$albumlist = getalbums($album['uid']);

} elseif($_GET['op'] == 'setpic') {

	album_update_pic($albumid, $picid);
	showmessage('do_success', dreferer(), array('picid' => $picid), array('showmsg' => true, 'closetime' => true));

} elseif($_GET['op'] == 'edittitle') {

	$picid = empty($_GET['picid'])?0:intval($_GET['picid']);
	$pic = C::t('home_pic')->fetch($picid);
	if(!checkperm('managealbum') && $pic['uid'] != $_G['uid']) {
		$pic = array();
	}

} elseif($_GET['op'] == 'edithot') {
	if(!checkperm('managealbum')) {
		showmessage('no_privilege_edithot_album');
	}

	if(!$pic = C::t('home_pic')->fetch($picid)) {
		showmessage('image_does_not_exist');
	}

	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		C::t('home_pic')->update($picid, array('hot'=>$_POST['hot']));
		if($_POST['hot'] > 0) {
			require_once libfile('function/feed');
			feed_publish($picid, 'picid');
		} else {
			C::t('home_feed')->update($picid, array('hot'=>$_POST['hot']), 'picid');
		}
		showmessage('do_success', dreferer());
	}
} elseif($_GET['op'] == 'saveforumphoto') {
	if(submitcheck('savephotosubmit')) {
		$aid = intval($_GET['aid']);
		$albumid = intval($_POST['albumid']);
		if(!$aid) {
			showmessage('parameters_error');
		}
		$attach = C::t('forum_attachment_n')->fetch('aid:'.$aid, $aid);
		if(empty($attach) || $attach['uid'] != $_G['uid'] || !$attach['isimage']) {
			showmessage('parameters_error');
		}
		if($albumid) {
			$album = C::t('home_album')->fetch($albumid, $_G['uid']);
			if(empty($album)) {
				showmessage('album_does_not_exist');
			}
		} else {
			$album = array('albumid' => 0);
		}
		$picdata = array(
				'albumid' => $album['albumid'],
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'dateline' => $attach['dateline'],
				'postip' => $_G['clientip'],
				'port' => $_G['remoteport'],
				'filename' => censor($attach['filename']),
				'title' => censor(cutstr(dhtmlspecialchars($attach['description']), 100)),
				'type' => fileext($attach['attachment']),
				'size' => $attach['filesize'],
				'filepath' => $attach['attachment'],
				'thumb' => $attach['thumb'],
				'remote' => $attach['remote'] + 2
			);
		$picid = C::t('home_pic')->insert($picdata, 1);
		showmessage('do_success', dreferer(), array('picid' => $picid), array('showdialog'=>true, 'showmsg' => true, 'closetime' => true));
	} else {
		$albumlist = C::t('home_album')->fetch_all_by_uid($_G['uid'], 'updatetime');
	}
}

include_once template("home/spacecp_album");

?>