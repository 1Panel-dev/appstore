<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: buyattachment.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'misc';
$_GET['action'] = 'attachpay';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;
		$variable = array(
		    'filename' => $GLOBALS['attach']['filename'],
		    'description' => $GLOBALS['attach']['description'],
		    'authorid' => $GLOBALS['attach']['uid'],
		    'author' => $GLOBALS['attach']['author'],
		    'price' => $GLOBALS['attach']['price'],
		    'balance' => $GLOBALS['balance'],
		    'credit' => mobile_core::getvalues($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]], array('title', 'unit')),
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>