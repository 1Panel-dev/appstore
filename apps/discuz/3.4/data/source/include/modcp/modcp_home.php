<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_home.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}


if($op == 'addnote' && submitcheck('submit')) {
	$newaccess = 4 + ($_GET['newaccess'][2] << 1) + $_GET['newaccess'][3];
	$newexpiration = TIMESTAMP + (intval($_GET['newexpiration']) > 0 ? intval($_GET['newexpiration']) : 30) * 86400;
	$newmessage = nl2br(dhtmlspecialchars(trim($_GET['newmessage'])));
	if($newmessage != '') {
		C::t('common_adminnote')->insert(array(
			'admin' => $_G['username'],
			'access' => $newaccess,
			'adminid' => $_G['adminid'],
			'dateline' => $_G['timestamp'],
			'expiration' => $newexpiration,
			'message' => $newmessage,
		));
	}
}

if($op == 'delete' && submitcheck('notlistsubmit')) {
	if(is_array($_GET['delete']) && $deleteids = dimplode($_GET['delete'])) {
		C::t('common_adminnote')->delete($_GET['delete'], ($_G['adminid'] == 1 ? '' : $_G['username']));
	}
}

switch($_G['adminid']) {
	case 1: $access = '1,2,3,4,5,6,7'; break;
	case 2: $access = '2,3,6,7'; break;
	default: $access = '1,3,5,7'; break;
}

$notelist = array();
foreach(C::t('common_adminnote')->fetch_all_by_access(explode(',', $access)) as $note) {
	if($note['expiration'] < TIMESTAMP) {
		C::t('common_adminnote')->delete($note['id']);
	} else {
		$note['expiration'] = ceil(($note['expiration'] - $note['dateline']) / 86400);
		$note['dateline'] = dgmdate($note['dateline']);
		$note['checkbox'] = '<input type="checkbox" name="delete[]" class="pc" '.($note['admin'] == $_G['member']['username'] || $_G['adminid'] == 1 ? "value=\"$note[id]\"" : 'disabled').'>';
		$note['admin'] = '<a href="home.php?mod=space&username='.rawurlencode($note['admin']).'" target="_blank">'.$note['admin'].'</a>';
		$notelist[] = $note;
	}
}

?>