<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_sendmail.php 30849 2012-06-26 02:21:32Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

header('Content-Type: text/javascript');

$pernum = 1;

dsetcookie('sendmail', '1', 300);
$lockfile = DISCUZ_ROOT.'./data/sendmail.lock';
@$filemtime = filemtime($lockfile);

if($_G['timestamp'] - $filemtime < 5) exit();

touch($lockfile);

@set_time_limit(0);

$list = $sublist = $cids = $touids = array();
foreach(C::t('common_mailcron')->fetch_all_by_sendtime($_G['timestamp'], 0, $pernum) as $value) {
	if($value['touid']) $touids[$value['touid']] = $value['touid'];
	$cids[] = $value['cid'];
	$list[$value['cid']] = $value;
}

if(empty($cids)) exit();

foreach(C::t('common_mailqueue')->fetch_all_by_cid($cids) as $value) {
	$sublist[$value['cid']][] = $value;
}

if($touids) {
	C::t('common_member_status')->update($touids, array('lastsendmail' => TIMESTAMP), 'UNBUFFERED');
}

C::t('common_mailcron')->delete($cids);
C::t('common_mailqueue')->delete_by_cid($cids);

require_once libfile('function/mail');

foreach ($list as $cid => $value) {
	$mlist = $sublist[$cid];
	if($value['email'] && $mlist) {
		$subject = getstr($mlist[0]['subject'], 80, 0, 0, 0, -1);
		$message = '';
		if(count($mlist) == 1) {
			$message = '<br>'.$mlist[0]['message'];
		} else {
			foreach ($mlist as $subvalue) {
				if($subvalue['message']) {
					$message .= "<br><strong>$subvalue[subject]</strong><br>$subvalue[message]<br>";
				} else {
					$message .= $subvalue['subject'].'<br>';
				}
			}
		}
		if(!sendmail($value['email'], $subject, $message)) {
			runlog('sendmail', "$value[email] sendmail failed.");
		}
	}
}

?>