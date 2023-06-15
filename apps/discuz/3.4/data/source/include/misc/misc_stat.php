<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_stat.php 34937 2014-09-04 03:08:56Z hypowang $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (empty($_G['setting']['updatestat'])) {
	showmessage('not_open_updatestat');
}

$stat_hash = md5($_G['setting']['siteuniqueid'] . "\t" . substr($_G['timestamp'], 0, 6));

if (!checkperm('allowstatdata') && $_GET['hash'] != $stat_hash) {
	showmessage('no_privilege_statdata');
}

$cols = array();
$cols['login'] = array('login', 'mobilelogin', 'connectlogin', 'register', 'invite', 'appinvite');
if (!$_G['setting']['connect']['allow']) {
	unset($cols['login'][2]);
}
$cols['forum'] = array('thread', 'poll', 'activity', 'reward', 'debate', 'trade', 'post');
$cols['tgroup'] = array('group', 'groupthread', 'grouppost');
$cols['home'] = array('doing', 'docomment', 'blog', 'blogcomment', 'pic', 'piccomment', 'share', 'sharecomment');
$cols['space'] = array('wall', 'poke', 'click', 'sendpm', 'addfriend', 'friend');

$type = !empty($_GET['types']) ? array() : (empty($_GET['type']) ? 'all' : $_GET['type']);

$primarybegin = !empty($_GET['primarybegin']) ? $_GET['primarybegin'] : dgmdate($_G['timestamp'] - 2592000, 'Y-m-d');
$primaryend = !empty($_GET['primaryend']) ? $_GET['primaryend'] : dgmdate($_G['timestamp'], 'Y-m-d');

$beginunixstr = strtotime($primarybegin);
$endunixstr = strtotime($primaryend);
if ($beginunixstr > $endunixstr) {
	showmessage('start_time_is_greater_than_end_time', NULL, array(), array('return' => true));
} else if ($beginunixstr == $endunixstr) {
	showmessage('start_time_end_time_is_equal_to', NULL, array(), array('return' => true));
}
if (!empty($_GET['xml'])) {
	$xaxis = '';
	$graph = array();
	$count = 1;
	$begin = dgmdate($beginunixstr, 'Ymd');
	$end = dgmdate($endunixstr, 'Ymd');
	$field = '*';
	if (!empty($_GET['merge'])) {
		if (empty($_GET['types'])) {
			$_GET['types'] = array_merge($cols['login'], $cols['forum'], $cols['tgroup'], $cols['home'], $cols['space']);
		}

		if (!array_diff($_GET['types'], array_merge($cols['login'], $cols['forum'], $cols['tgroup'], $cols['home'], $cols['space']))) {
			$field = 'daytime,`' . implode('`+`', $_GET['types']) . '` AS statistic';
		}
		$type = 'statistic';
	}
	foreach (C::t('common_stat')->fetch_all($begin, $end, $field) as $value) {
		$xaxis .= "<value xid='$count'>" . substr($value['daytime'], 4, 4) . "</value>";
		if ($type == 'all') {
			foreach ($cols as $ck => $cvs) {
				if ($ck == 'login') {
					$graph['login'] .= "<value xid='$count'>$value[login]</value>";
					$graph['register'] .= "<value xid='$count'>$value[register]</value>";
				} else {
					$num = 0;
					foreach ($cvs as $cvk) {
						$num = $value[$cvk] + $num;
					}
					$graph[$ck] .= "<value xid='$count'>" . $num . "</value>";
				}
			}
		} else {
			if (empty($_GET['types']) || !empty($_GET['merge'])) {
				$graph[$type] .= "<value xid='$count'>" . $value[$type] . "</value>";
			} else {
				foreach ($_GET['types'] as $t) {
					$graph[$t] .= "<value xid='$count'>" . $value[$t] . "</value>";
				}
			}
		}
		$count++;
	}
	$xml = '';
	$xml .= '<' . "?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$xml .= '<chart><xaxis>';
	$xml .= $xaxis;
	$xml .= "</xaxis><graphs>";
	$count = 0;
	foreach ($graph as $key => $value) {
		$title = diconv(lang('spacecp', "do_stat_$key"), CHARSET, 'utf-8');
		if ($title == '') {
			continue;
		}
		$xml .= "<graph gid='$count' title='" . $title . "'>";
		$xml .= $value;
		$xml .= '</graph>';
		$count++;
	}
	$xml .= '</graphs></chart>';

	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	@header("Content-type: application/xml; charset=utf-8");
	echo $xml;
	exit();
}

$actives = array($type => ' class="a"');

require_once libfile('function/home');
$siteurl = getsiteurl();
$types = '';
$merge = !empty($_GET['merge']) ? '&merge=1' : '';
foreach ($_GET['types'] as $value) {
	$types .= '&types[]=' . $value;
	$actives[$value] = ' class="a"';
}
$statuspara = "misc.php?mod=stat&op=trend&xml=1&type=$type&primarybegin=$primarybegin&primaryend=$primaryend{$types}{$merge}&hash=$stat_hash";

include template('home/misc_stat');