<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memcp.inc.php 29364 2012-04-09 02:51:41Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$myrepeatsusergroups = (array)unserialize($_G['cache']['plugin']['myrepeats']['usergroups']);
if(in_array('', $myrepeatsusergroups)) {
	$myrepeatsusergroups = array();
}
$singleprem = FALSE;
$permusers = $permuids = array();
if(!in_array($_G['groupid'], $myrepeatsusergroups)) {
	$singleprem = TRUE;
}

foreach(C::t('#myrepeats#myrepeats')->fetch_all_by_username($_G['username']) as $user) {
	$permuids[] = $user['uid'];
}
$permusers = C::t('common_member')->fetch_all_username_by_uid($permuids);
if(!$permusers && $singleprem) {
	showmessage('myrepeats:usergroup_disabled');
}

if($_GET['pluginop'] == 'add' && submitcheck('adduser')) {
	if($singleprem && in_array($_GET['usernamenew'], $permusers) || !$singleprem) {
		$usernamenew = addslashes(strip_tags($_GET['usernamenew']));
		$logindata = addslashes(authcode($_GET['passwordnew']."\t".$_GET['questionidnew']."\t".$_GET['answernew'], 'ENCODE', $_G['config']['security']['authkey']));
		if(C::t('#myrepeats#myrepeats')->count_by_uid_username($_G['uid'], $usernamenew)) {
			DB::query("UPDATE ".DB::table('myrepeats')." SET logindata='$logindata' WHERE uid='$_G[uid]' AND username='$usernamenew'");
		} else {
			$_GET['commentnew'] = addslashes($_GET['commentnew']);
			DB::query("INSERT INTO ".DB::table('myrepeats')." (uid, username, logindata, comment) VALUES ('$_G[uid]', '$usernamenew', '$logindata', '".strip_tags($_GET['commentnew'])."')");
		}
		dsetcookie('mrn', '');
		dsetcookie('mrd', '');
		showmessage('myrepeats:adduser_succeed', 'home.php?mod=spacecp&ac=plugin&id=myrepeats:memcp', array('usernamenew' => stripslashes($usernamenew)));
	}
} elseif($_GET['pluginop'] == 'update' && submitcheck('updateuser')) {
	if(!empty($_GET['delete'])) {
		C::t('#myrepeats#myrepeats')->delete_by_uid_usernames($_G['uid'], $_GET['delete']);
	}
	$_GET['comment'] = daddslashes($_GET['comment']);
	foreach($_GET['comment'] as $user => $v) {
		C::t('#myrepeats#myrepeats')->update_comment_by_uid_username($_G['uid'], $user, strip_tags($v));
	}
	dsetcookie('mrn', '');
	dsetcookie('mrd', '');
	showmessage('myrepeats:updateuser_succeed', 'home.php?mod=spacecp&ac=plugin&id=myrepeats:memcp');
}

$username = empty($_GET['username']) ? '' : htmlspecialchars($_GET['username']);

$repeatusers = array();
foreach(C::t('#myrepeats#myrepeats')->fetch_all_by_uid($_G['uid']) as $myrepeat) {
	$myrepeat['lastswitch'] = $myrepeat['lastswitch'] ? dgmdate($myrepeat['lastswitch']) : '';
	$myrepeat['usernameenc'] = rawurlencode($myrepeat['username']);
	$myrepeat['comment'] = htmlspecialchars($myrepeat['comment']);
	$repeatusers[] = $myrepeat;
}

$_G['basescript'] = 'home';

?>