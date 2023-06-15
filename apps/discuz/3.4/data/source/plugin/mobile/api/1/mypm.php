<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mypm.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'space';
$_GET['do'] = 'pm';
include_once 'home.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;
		$variable = array(
			'list' => mobile_core::getvalues($GLOBALS['list'], array('/^\d+$/'), array('plid', 'isnew', 'pmnum', 'lastupdate', 'lastdateline', 'authorid', 'author', 'pmtype', 'subject', 'members', 'dateline', 'touid', 'pmid', 'lastauthorid', 'lastauthor', 'lastsummary', 'msgfromid', 'msgfrom', 'message', 'msgtoid', 'tousername')),
			'count' => $GLOBALS['count'],
			'perpage' => $GLOBALS['perpage'],
			'page' => intval($GLOBALS['page']),
		);
		if($_GET['subop']) {
			$variable = array_merge($variable, array('pmid' => $GLOBALS['pmid']));
		}
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>