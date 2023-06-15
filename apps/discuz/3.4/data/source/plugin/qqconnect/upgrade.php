<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: upgrade.php 33545 2013-07-04 07:06:27Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = '';

$sql .= <<<EOF

CREATE TABLE IF NOT EXISTS pre_connect_postfeedlog (
  flid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  pid int(10) unsigned NOT NULL DEFAULT '0',
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  publishtimes mediumint(8) unsigned NOT NULL DEFAULT '0',
  lastpublished int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (flid),
  UNIQUE KEY pid (pid)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_connect_tthreadlog (
  twid char(16) NOT NULL,
  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
  conopenid char(32) NOT NULL,
  pagetime int(10) unsigned DEFAULT '0',
  lasttwid char(16) DEFAULT NULL,
  nexttime int(10) unsigned DEFAULT '0',
  updatetime int(10) unsigned DEFAULT '0',
  dateline int(10) unsigned DEFAULT '0',
  PRIMARY KEY (twid),
  KEY nexttime (tid,nexttime),
  KEY updatetime (tid,updatetime)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_common_connect_guest (
  `conopenid` char(32) NOT NULL default '',
  `conuin` char(40) NOT NULL default '',
  `conuinsecret` char(16) NOT NULL default '',
  `conqqnick` char(100) NOT NULL default '',
  `conuintoken` char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (conopenid)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `pre_connect_disktask` (
 `taskid` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `aid` int(10) unsigned NOT NULL DEFAULT '0',
 `uid` int(10) unsigned NOT NULL DEFAULT '0',
 `openid` char(32) NOT NULL DEFAULT '',
 `filename` varchar(255) NOT NULL DEFAULT '',
 `verifycode` char(32) NOT NULL DEFAULT '',
 `status` smallint(6) unsigned NOT NULL DEFAULT '0',
 `dateline` int(10) unsigned NOT NULL DEFAULT '0',
 `downloadtime` int(10) unsigned NOT NULL DEFAULT '0',
 `extra` text,
 PRIMARY KEY (`taskid`),
 KEY `openid` (`openid`),
 KEY `status` (`status`)
) TYPE=MyISAM;

EOF;

runquery($sql);

$sql = '';

$columnexisted = false;

$query = DB::query("SHOW COLUMNS FROM ".DB::table('common_member_connect'));
while($temp = DB::fetch($query)) {
	if($temp['Field'] == 'conisqqshow') {
		$columnexisted = true;
		continue;
	}
	if($temp['Field'] == 'conuintoken') {
		$uintokenexisted = true;
		continue;
	}
}
$sql .= !$columnexisted ? "ALTER TABLE ".DB::table('common_member_connect')." ADD COLUMN conisqqshow tinyint(1) unsigned NOT NULL default '0';\n" : '';
$sql .= !$uintokenexisted ? "ALTER TABLE ".DB::table('common_member_connect')." ADD COLUMN conuintoken char(32) NOT NULL DEFAULT '';\n" : '';

$query = DB::query("SHOW COLUMNS FROM ".DB::table('common_connect_guest'));
while($row = DB::fetch($query)) {
	if($row['Field'] == 'conqqnick') {
		$qqnickexisted = true;
		continue;
	}
	if($row['Field'] == 'conuintoken') {
		$guintokenexisted = true;
		continue;
	}
}
$sql .= !$qqnickexisted ? "ALTER TABLE ".DB::table('common_connect_guest')." ADD COLUMN conqqnick char(100) NOT NULL default '';\n" : '';
$sql .= !$guintokenexisted ? "ALTER TABLE ".DB::table('common_connect_guest')." ADD COLUMN conuintoken char(32) NOT NULL DEFAULT '';\n" : '';

if($sql) {
	runquery($sql);
}

$connect = C::t('common_setting')->fetch('connect', true);

if (!array_key_exists('reply', $connect['t'])) {
	$connect['t']['reply'] = 1;
}
if (!array_key_exists('reply_showauthor', $connect['t'])) {
	$connect['t']['reply_showauthor'] = 1;
}

$needCreateGroup = false;
if ($connect['guest_groupid']) {
	$group = C::t('common_usergroup')->fetch($connect['guest_groupid']);
	if (!$group) {
		$needCreateGroup = true;
	}
} else {
	$needCreateGroup = true;
}

$newConnect = array();
$name = $installlang['connect_guest_group_name'];
if ($needCreateGroup) {
	$userGroupData = array(
		'type' => 'special',
		'grouptitle' => $name,
		'allowvisit' => 1,
		'color' => '',
		'stars' => '',
	);
	$newGroupId = C::t('common_usergroup')->insert($userGroupData, true);

	$dataField = array(
		'groupid' => $newGroupId,
		'allowsearch' => 2,
		'readaccess' => 1,
		'allowgetattach' => 1,
		'allowgetimage' => 1,
	);
	C::t('common_usergroup_field')->insert($dataField);

	$newConnect['guest_groupid'] = $newGroupId;
}

$https = json_decode(dfsockopen('https://graph.qq.com/user/get_user_info'));
$newConnect['oauth2'] = $https->ret == -1 ? 1 : 0;

$updateData = array_merge($connect, $newConnect);
C::t('common_setting')->update('connect', serialize($updateData));
updatecache('setting');
$finish = true;