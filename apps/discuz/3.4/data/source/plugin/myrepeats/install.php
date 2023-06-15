<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 8889 2010-04-23 07:48:22Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS cdb_myrepeats;
CREATE TABLE cdb_myrepeats (
  `uid` mediumint(8) unsigned NOT NULL,
  `username` varchar(15) NOT NULL DEFAULT '',
  `logindata` varchar(255) NOT NULL DEFAULT '',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `lastswitch` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`,`username`),
  KEY `username` (`username`)
) TYPE=MyISAM;

EOF;

runquery($sql);

$finish = TRUE;

?>