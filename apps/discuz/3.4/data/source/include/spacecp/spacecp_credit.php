<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_credit.php 32023 2012-10-31 08:20:37Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['inajax'] && $_GET['showcredit']) {
	include template('common/extcredits');
	exit;
}

include_once libfile('function/credit');

$perpage = 20;
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
if($page < 1) $page = 1;
$start = ($page-1) * $perpage;
ckstart($start, $perpage);

checkusergroup();

$operation = in_array($_GET['op'], array('base', 'buy', 'transfer', 'exchange', 'log', 'rule')) ? trim($_GET['op']) : 'base';
$opactives = array($operation =>' class="a"');
if(in_array($operation, array('base', 'buy', 'transfer', 'exchange', 'rule'))) {
	$operation = 'base';
}
include_once libfile('spacecp/credit_'.$operation, 'include');


?>