<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_perm.php 22528 2011-05-11 05:43:55Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

array_splice($menu['global'], 4, 0, array(
	array('setting_memory', 'setting_memory'),
	array('setting_serveropti', 'setting_serveropti'),
));

array_splice($menu['global'], 9, 0, array(
	array('founder_perm_credits', 'credits'),
));

array_splice($menu['style'], 8, 0, array(
	array('setting_editor_code', 'misc_bbcode'),
));

array_splice($menu['user'], 1, 0, array(
	array('founder_perm_members_group', 'members_group'),
	array('founder_perm_members_access', 'members_access'),
	array('founder_perm_members_credit', 'members_credit'),
	array('founder_perm_members_medal', 'members_medal'),
	array('founder_perm_members_repeat', 'members_repeat'),
	array('founder_perm_members_clean', 'members_clean'),
	array('founder_perm_members_edit', 'members_edit'),
));

array_splice($menu['group'], 1, 0, array(
	array('founder_perm_group_editgroup', 'group_editgroup'),
	array('founder_perm_group_deletegroup', 'group_deletegroup'),
));

array_splice($menu['extended'], 4, 0, array(
	array('founder_perm_members_confermedal', 'members_confermedal'),
));

array_splice($menu['extended'], 7, 0, array(
	array('founder_perm_ec_alipay', 'ec_alipay'),
	array('founder_perm_ec_tenpay', 'ec_tenpay'),
	array('founder_perm_ec_credit', 'ec_credit'),
	array('founder_perm_ec_orders', 'ec_orders'),
	array('founder_perm_tradelog', 'tradelog'),
));

?>