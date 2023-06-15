<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_class.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$classid = empty($_GET['classid'])?0:intval($_GET['classid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$class = array();
if($classid) {
	$class = C::t('home_class')->fetch($classid);
	if($class['uid'] != $_G['uid']) {
		$class = null;
	}
}
if(empty($class)) showmessage('did_not_specify_the_type_of_operation');

if ($op == 'edit') {

	if(submitcheck('editsubmit')) {

		$_POST['classname'] = getstr($_POST['classname'], 40);
		$_POST['classname'] = censor($_POST['classname']);
		if(strlen($_POST['classname']) < 1) {
			showmessage('enter_the_correct_class_name');
		}
		C::t('home_class')->update($classid, array('classname'=>$_POST['classname']));
		showmessage('do_success', dreferer(),array('classid'=>$classid, 'classname' => $_POST['classname']), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
	}

} elseif ($op == 'delete') {
	if(submitcheck('deletesubmit')) {
		C::t('home_blog')->update_classid_by_classid($classid, 0);
		C::t('home_class')->delete($classid);

		showmessage('do_success', dreferer());
	}
}

include_once template("home/spacecp_class");

?>