<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install_extvar.php 31245 2012-07-31 02:54:24Z liulanbo $
 */

if(!defined('IN_COMSENZ')) {
	exit('Access Denied');
}
$settings = array(
	'extcredits' => array(
		1 => array('title' => $lang['init_credits_money'], 'ratio' => 0, 'available' => 1),
		2 => array('title' => $lang['init_credits_karma'], 'ratio' => 0, 'available' => 1),
	),
	'postnocustom' => array(
		0 => $lang['init_postno0'], 1 => $lang['init_postno1'], 2 => $lang['init_postno2'], 3 => $lang['init_postno3']
	),

	'recommendthread' => array(
		'status' => '1',
		'addtext' => $lang['init_support'],
		'subtracttext' => $lang['init_opposition'],
		'defaultshow' => '1',
		'daycount' => '0',
		'ownthread' => '0',
		'iconlevels' => '0,100,200',
	),

	'tasktypes' => array(
		'promotion' => array(
			'name' => $lang['init_promotion_task'],
			'version' => '1.0',
		),
		'gift' => array(
			'name' => $lang['init_gift_task'],
			'version' => '1.0',
		),
		'avatar' => array(
			'name' => $lang['init_avatar_task'],
			'version' => '1.0',
		),
	),

);

?>