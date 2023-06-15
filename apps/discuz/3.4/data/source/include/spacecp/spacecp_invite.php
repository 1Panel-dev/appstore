<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_invite.php 25042 2011-10-24 03:27:47Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$creditid = 0;
$creditnum = $_G['group']['inviteprice'];
if($_G['setting']['creditstrans']) {
	$creditid = intval($_G['setting']['creditstransextra'][6] ? $_G['setting']['creditstransextra'][6] : $_G['setting']['creditstrans']);
} elseif($creditnum) {
	showmessage('trade_credit_invalid', '', array(), array('return' => 1));
}

space_merge($space, 'count');

$baseurl = 'home.php?mod=spacecp&ac=invite';

$siteurl = getsiteurl();

$maxcount = 50;

$config = $_G['setting']['inviteconfig'];
$creditname = $config['inviterewardcredit'];
$allowinvite = ($_G['setting']['regstatus'] > 1 && $creditname && $_G['group']['allowinvite']) ? 1 : 0;
$unit = $_G['setting']['extcredits'][$creditname]['unit'];
$credittitle = $_G['setting']['extcredits'][$creditname]['title'];
$creditname = 'extcredits'.$creditname;

$inviteurl = $invite_code = '';
$appid = 0;

$creditkey = 'extcredits'.$creditid;
$extcredits = $_G['setting']['extcredits'][$creditid];

$mailvar = array(
	'avatar' => avatar($space['uid'], 'middle'),
	'uid' => $space['uid'],
	'username' => $space['username'],
	'sitename' => $_G['setting']['sitename'],
	'siteurl' => $siteurl
);

$appinfo = array();

if(!$creditnum) {
	$inviteurl = getinviteurl(0, 0, $appid);
}
if(!$allowinvite) {
	showmessage('close_invite', '', array(), $_G['inajax'] ? array('showdialog'=>1, 'showmsg' => true, 'closetime' => true) : array());
}

if(submitcheck('emailinvite')) {

	if(!$_G['group']['allowmailinvite']) {
		showmessage('mail_invite_not_allow', $baseurl);
	}

	$_POST['email'] = str_replace("\n", ',', $_POST['email']);
	$newmails = array();
	$mails = explode(",", $_POST['email']);
	foreach ($mails as $value) {
		$value = trim($value);
		if(isemail($value)) {
			$newmails[] = $value;
		}
	}
	$newmails = array_unique($newmails);
	$invitenum = count($newmails);

	if($invitenum < 1) {
		showmessage('mail_can_not_be_empty', $baseurl);
	}

	$msetarr = array();
	if($creditnum) {
		$allcredit = $invitenum * $creditnum;
		if($space[$creditkey] < $allcredit) {
			showmessage('mail_credit_inadequate', $baseurl);
		}

		foreach($newmails as $value) {
			$code = strtolower(random(6));
			$setarr = array(
				'uid' => $_G['uid'],
				'code' => $code,
				'email' => daddslashes($value),
				'type' => 1,
				'appid' => $appid,
				'inviteip' => $_G['clientip'],
				'dateline' => $_G['timestamp'],
				'status' => 3,
				'endtime' => ($_G['group']['maxinviteday']?($_G['timestamp']+$_G['group']['maxinviteday']*24*3600):0)
			);
			$id = C::t('common_invite')->insert($setarr, true);

			$mailvar['inviteurl'] = getinviteurl($id, $code, $appid);

			createmail($value, $mailvar);
		}

		updatemembercount($_G['uid'], array($creditkey => "-$allcredit"));

	} else {

		$mailvar['inviteurl'] = $inviteurl;
		foreach($newmails as $value) {
			createmail($value, $mailvar);
		}
	}

	showmessage('send_result_succeed',$baseurl);

} else if(submitcheck('invitesubmit')) {

	$invitenum = intval($_POST['invitenum']);
	if($invitenum < 1) $invitenum = 1;

	if($_G['group']['maxinvitenum']) {
		$daytime = $_G['timestamp'] - 24*3600;
		$invitecount = C::t('common_invite')->count_by_uid_dateline($_G['uid'], $daytime);
		if($invitecount + $invitenum > $_G['group']['maxinvitenum']) {
			showmessage('max_invitenum_error', NULL, array('maxnum'=>$_G['group']['maxinvitenum']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		}
	}

	$allcredit = $invitenum * $creditnum;
	if($space[$creditkey] < $allcredit) {
		showmessage('mail_credit_inadequate', $baseurl, array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}

	$havecode = false;
	$dateline = $_G['timestamp'];
	for($i=0; $i<$invitenum; $i++) {
		$code = strtolower(random(6));
		$havecode = true;
		$invitedata = array(
				'uid' => $_G['uid'],
				'code' => $code,
				'dateline' => $dateline,
				'endtime' => $_G['group']['maxinviteday'] ? ($_G['timestamp']+$_G['group']['maxinviteday']*24*3600) : 0,
				'inviteip' => $_G['clientip']
		);
		C::t('common_invite')->insert($invitedata);
	}

	if($havecode) {
		require_once libfile('class/credit');
		$creditobj = new credit();
		$creditobj->updatemembercount(array($creditkey=>0-$allcredit), $_G['uid']);
	}
	showmessage('do_success', $baseurl, array('deduction' => $allcredit, 'dateline' => $dateline), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true, 'return' => false));
}

if($_GET['op'] == 'resend') {

	$id = $_GET['id'] ? intval($_GET['id']) : 0;

	if(submitcheck('resendsubmit')) {

		if(empty($id)) {
			showmessage('send_result_resend_error', $baseurl);
		}

		if($value = C::t('common_invite')->fetch_by_id_uid($id, $_G['uid'])) {
			if($creditnum) {
				$inviteurl = getinviteurl($value['id'], $value['code'], $value['appid']);
			}
			$mailvar['inviteurl'] = $inviteurl;

			createmail($value['email'], $mailvar);
			showmessage('send_result_succeed', dreferer(), array('id' => $id), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));

		} else {
			showmessage('send_result_resend_error', $baseurl, array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		}
	}

} elseif($_GET['op'] == 'delete') {

	$id = $_GET['id'] ? intval($_GET['id']) : 0;
	if(empty($id)) {
		showmessage('there_is_no_record_of_invitation_specified', $baseurl);
	}
	if($value = C::t('common_invite')->fetch_by_id_uid($id, $_G['uid'])) {
		if(submitcheck('deletesubmit')) {
			C::t('common_invite')->delete($id);
			showmessage('do_success', dreferer(), array('id' => $id), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		}
	} else {
		showmessage('there_is_no_record_of_invitation_specified', $baseurl, array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}

} elseif ($_GET['op'] == 'showinvite') {
	foreach(C::t('common_invite')->fetch_all_by_uid($_G['uid']) as $value) {
		if(!$value['fuid'] && !$value['type']) {
			$inviteurl = getinviteurl($value['id'], $value['code'], $value['appid']);
			$list[$value[code]] = $inviteurl;
		}
	}
} else {

	$list = $flist = $dels = array();
	$invitedcount = $count = 0;

	foreach(C::t('common_invite')->fetch_all_by_uid($_G['uid']) as $value) {
		if($value['fuid']) {
			$flist[] = $value;
			$invitedcount++;
		} else {

			if($_G['timestamp'] > $value['endtime']) {
				$dels[] = $value['id'];
				continue;
			}

			$inviteurl = getinviteurl($value['id'], $value['code'], $value['appid']);

			if($value['type']) {
				$maillist[] = array(
					'email' => $value['email'],
					'url' => $inviteurl,
					'id' => $value['id']
				);
			} else {
				$list[$value[code]] = $inviteurl;
				$count++;
			}
		}
	}

	if($dels) {
		C::t('common_invite')->delete($dels);
	}

	$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$uri = substr($uri, 0, strrpos($uri, '/')+1);

	$actives = array('invite'=>' class="a"');
}

$navtitle = lang('core', 'title_invite_friend');

include template('home/spacecp_invite');

function createmail($mail, $mailvar) {
	global $_G, $space, $appinfo;

	$mailvar['saymsg'] = empty($_POST['saymsg'])?'':getstr($_POST['saymsg'], 500);

	require_once libfile('function/mail');

	$subject = lang('spacecp', $appinfo?'app_invite_subject':'invite_subject', $mailvar);
	$message = lang('spacecp', $appinfo?'app_invite_massage':'invite_massage', $mailvar);

	if(!sendmail($mail, $subject, $message)) {
		runlog('sendmail', "$mail sendmail failed.");
	}
}

function getinviteurl($inviteid, $invitecode, $appid) {
	global $_G;

	if($inviteid && $invitecode) {
		$inviteurl = getsiteurl()."home.php?mod=invite&amp;id={$inviteid}&amp;c={$invitecode}";
	} else {
		$invite_code = space_key($_G['uid'], $appid);
		$inviteapp = $appid?"&amp;app=$appid":'';
		$inviteurl = getsiteurl()."home.php?mod=invite&amp;u=$_G[uid]&amp;c=$invite_code{$inviteapp}";
	}
	return $inviteurl;
}

?>