<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: switch.inc.php 29558 2012-04-18 10:17:22Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$myrepeatsusergroups = (array)dunserialize($_G['cache']['plugin']['myrepeats']['usergroups']);

if(!empty($_GET['list'])) {
	if(in_array('', $myrepeatsusergroups)) {
		$myrepeatsusergroups = array();
	}
	$userlist = array();
	if(!in_array($_G['groupid'], $myrepeatsusergroups)) {
		$userlist = get_rrepeats($_G['username']);
		$count = count($userlist);
		if(!$count) {
			unset($_G['setting']['plugins']['spacecp']['myrepeats:memcp']);
		}
	}

	foreach(C::t('#myrepeats#myrepeats')->fetch_all_by_uid($_G['uid']) as $user) {
		$userlist[$user['username']] = $user['username'];
	}
	$list = '<ul>';
	foreach($userlist as $user) {
		if(!$user) {
			continue;
		}
		$list .= '<li><a href="plugin.php?id=myrepeats:switch&username='.rawurlencode($user).'&formhash='.FORMHASH.'" onclick="showWindow(\'myrepeat\', this.href);return false;">'.$user.'</a></li>';
	}
	$list .= '<li><a href="home.php?mod=spacecp&ac=plugin&id=myrepeats:memcp">'.lang('plugin/myrepeats', 'memcp').'</a></li>';
	include template('common/header_ajax');
	echo $list;
	include template('common/footer_ajax');
	exit;
}

if($_GET['formhash'] != FORMHASH) {
	showmessage('undefined_action');
}

$referer = dreferer();

if(in_array('', $myrepeatsusergroups)) {
	$myrepeatsusergroups = array();
}
if(!in_array($_G['groupid'], $myrepeatsusergroups)) {
	$users = C::t('#myrepeats#myrepeats')->fetch_all_by_username($_G['username']);
	if(!$users) {
		showmessage('myrepeats:usergroup_disabled');
	} else {
		$permusers = array();
		foreach($users as $user) {
			$permusers[] = $user['uid'];
		}
		$member = C::t('common_member')->fetch_by_username($_GET['username']);
		if(!$member || !in_array($member['uid'], $permusers)) {
			showmessage('myrepeats:usergroup_disabled');
		}
	}
}

require_once libfile('function/member');

$_G['myrepeats_loginperm'] = logincheck($_GET['username']);
if(!$_G['myrepeats_loginperm']) {
	showmessage('myrepeats:login_strike', '', array('loginperm' => $_G['myrepeats_loginperm']));
}

if(!empty($_GET['authorfirst']) && submitcheck('myrepeatssubmit')) {
	$result = userlogin($_GET['username'], $_GET['password'], $_GET['questionid'], $_GET['answer'], 'username', $_G['clientip']);
	$_G['myrepeats_ucresult'] = $result['ucresult'];
	if($result['status'] > 0) {
		$logindata = addslashes(authcode($_GET['password']."\t".$_GET['questionid']."\t".$_GET['answer'], 'ENCODE', $_G['config']['security']['authkey']));
		if(C::t('#myrepeats#myrepeats')->count_by_uid_username($_G['uid'], $_GET['username'])) {
			C::t('#myrepeats#myrepeats')->update_logindata_by_uid_username($_G['uid'], $_GET['username'], $logindata);
		} else {
			C::t('#myrepeats#myrepeats')->insert(array(
				'uid' => $_G['uid'],
				'username' => $_GET[username],
				'logindata' => $logindata,
				'comment' => ''
			));
		}
	} else {
		myrepeats_loginfailure($_GET['username'], $_GET['password'], $_GET['questionid'], $_GET['answer']);
	}
}

$user = C::t('#myrepeats#myrepeats')->fetch_all_by_uid_username($_G['uid'], $_GET['username']);
$user = current($user);
$olddiscuz_uid = $_G['uid'];
$olddiscuz_user = $_G['username'];
$olddiscuz_userss = $_G['member']['username'];

if(!$user) {
	$newuid = C::t('common_member')->fetch_uid_by_username($_GET['username']);
	if(C::t('#myrepeats#myrepeats')->count_by_uid_username($newuid, $olddiscuz_userss)) {
		$username = htmlspecialchars($_GET['username']);
		include template('myrepeats:switch_login');
		exit;
	}
	showmessage('myrepeats:user_nonexistence');
} elseif($user['locked']) {
	showmessage('myrepeats:user_locked', '', array('user' => $_GET['username']));
}

list($password, $questionid, $answer) = explode("\t", authcode($user['logindata'], 'DECODE', $_G['config']['security']['authkey']));

$result = userlogin($_GET['username'], $password, $questionid, $answer, 'username', $_G['clientip']);
$_G['myrepeats_ucresult'] = $result['ucresult'];
if($result['status'] > 0) {
	setloginstatus($result['member'], 2592000);
	C::t('#myrepeats#myrepeats')->update_lastswitch_by_uid_username($olddiscuz_uid, $_GET['username'], TIMESTAMP);
	C::t('common_member_status')->update($_G['uid'], array('lastvisit' => TIMESTAMP), 'UNBUFFERED');
	$ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
	dsetcookie('mrn', '');
	dsetcookie('mrd', '');
	$comment = $user['comment'] ? '('.$user['comment'].') ' : '';
	showmessage('myrepeats:login_succeed', $referer, array('user' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'comment' => $comment), array('showmsg' => 1, 'showdialog' => 1, 'locationtime' => 3, 'extrajs' => $ucsynlogin));
} elseif($result['status'] == -1) {
	clearcookies();
	$_G['myrepeats_ucresult']['username'] = addslashes($_G['myrepeats_ucresult']['username']);
	$_G['username'] = '';
	$_G['uid'] = 0;
	$auth = authcode($_G['myrepeats_ucresult']['username']."\t".formhash(), 'ENCODE');
	showmessage('myrepeats:login_activation', 'member.php?mod='.$_G['setting']['regname'].'&action=activation&auth='.rawurlencode($auth).'&referer='.rawurlencode($referer), array('user' => $_G['myrepeats_ucresult']['username']), array('showmsg' => 1, 'showdialog' => 1, 'locationtime' => 3));
} else {
	myrepeats_loginfailure($_GET['username'], $password, $questionid, $answer);
}

function myrepeats_loginfailure($username, $password, $questionid, $answer) {
	global $_G;
	$password = preg_replace("/^(.{".round(strlen($password) / 4)."})(.+?)(.{".round(strlen($password) / 6)."})$/s", "\\1***\\3", $password);
	$errorlog = dhtmlspecialchars(
		TIMESTAMP."\t".
		($_G['myrepeats_ucresult']['username'] ? $_G['myrepeats_ucresult']['username'] : stripslashes($username))."\t".
		$password."\t".
		"Ques #".intval($questionid)."\t".
		$_G['clientip']);
	writelog('illegallog', $errorlog);
	loginfailed($username);
	$fmsg = $_G['myrepeats_ucresult']['uid'] == '-3' ? (empty($questionid) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
	if($_G['myrepeats_loginperm'] > 1) {
		showmessage('myrepeats:'.$fmsg, '', array('loginperm' => $_G['myrepeats_loginperm']));
	} elseif($_G['myrepeats_loginperm'] == -1) {
		showmessage('myrepeats:login_password_invalid');
	} else {
		showmessage('myrepeats:login_strike');
	}
}

function get_rrepeats($username) {
	$users = C::t('#myrepeats#myrepeats')->fetch_all_by_username($username);
	$uids = array();
	foreach($users as $user) {
		$uids[] = $user['uid'];
	}
	$userlist = array();
	foreach(C::t('common_member')->fetch_all($uids) as $user) {
		$userlist[$user['username']] = $user['username'];
	}
	return $userlist;
}

?>