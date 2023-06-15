<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: upgrade.php 34692 2014-07-09 01:17:48Z qingrongfu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = '';

$sql .= <<<EOF

CREATE TABLE IF NOT EXISTS pre_common_devicetoken (
  `uid` mediumint(8) unsigned NOT NULL,
  `token` char(50) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `token` (`token`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_mobile_setting (
  `skey` varchar(255) NOT NULL DEFAULT '',
  `svalue` text NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_mobile_wsq_threadlist (
  `skey` int(10) unsigned NOT NULL,
  `svalue` text NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=MyISAM;

REPLACE INTO pre_mobile_setting VALUES ('extend_used', '1');
REPLACE INTO pre_mobile_setting VALUES ('extend_lastupdate', '1343182299');

EOF;

runquery($sql);

DB::query( "REPLACE INTO ".DB::table("common_credit_rule")." VALUES (NULL, '".$installlang['mobilesign']."', 'mobilesign', '1', '0', '1', '0', '0', '2', '0', '0', '0', '0', '0', '0', '');"
);

$finish = true;