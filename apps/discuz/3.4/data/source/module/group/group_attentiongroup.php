<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group_attentiongroup.php 25889 2011-11-24 09:52:20Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_GET['handlekey'] = 'attentiongroup';
require_once libfile('function/group');
$usergroups = update_usergroups($_G['uid']);
$attentiongroup = !empty($_G['member']['attentiongroup']) ? explode(',', $_G['member']['attentiongroup']) : array();
$counttype = count($attentiongroup);
if(submitcheck('attentionsubmit')) {
	if(is_array($_GET['attentiongroupid'])) {
		$_GET['attentiongroupid'] = array_slice($_GET['attentiongroupid'], 0, 5);
		C::t('common_member_field_forum')->update($_G['uid'], array('attentiongroup' => implode(',', $_GET['attentiongroupid'])));
	} else {
		C::t('common_member_field_forum')->update($_G['uid'], array('attentiongroup' => ''));
	}
	showmessage('setup_finished', 'group.php?mod=my&view=groupthread');
}
include template('group/group_attentiongroup');

?>