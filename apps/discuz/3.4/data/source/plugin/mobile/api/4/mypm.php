<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mypm.php 35183 2015-01-14 07:46:53Z nemohou $
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
		foreach($GLOBALS['list'] as $_k => $_v) {
			if($_v['lastdateline']) {
				$GLOBALS['list'][$_k]['vdateline'] = dgmdate($_v['lastdateline'], 'u');
			} elseif($_v['dateline']) {
				$GLOBALS['list'][$_k]['vdateline'] = dgmdate($_v['dateline'], 'u');
			}
		}
		$variable = array(
			'list' => mobile_core::getvalues($GLOBALS['list'], array('/^\d+$/'), array('plid', 'isnew', 'vdateline', 'subject', 'pmid', 'msgfromid', 'msgfrom', 'message', 'touid', 'tousername')),
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