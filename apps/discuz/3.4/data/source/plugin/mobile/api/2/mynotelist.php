<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
*      This is NOT a freeware, use is subject to license terms
*
*      $Id: mynotelist.php 32489 2013-01-29 03:57:16Z monkey $
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'space';
$_GET['do'] = 'notice';
include_once 'home.php';

class mobile_api {
	function common() {

	}

	function output() {
		global $_G;
		$variable = array(
			'list' => mobile_core::getvalues($GLOBALS['list'], array('/^\d+$/'), array('id', 'uid', 'type', 'new', 'authorid', 'author', 'note', 'dateline', 'from_id', 'from_idtype', 'from_num', 'style', 'rowid')),
			'count' => $GLOBALS['count'],
			'perpage' => $GLOBALS['perpage'],
			'page' => intval($GLOBALS['page']),
		);
		mobile_core::result(mobile_core::variable($variable));
	}
}
?>