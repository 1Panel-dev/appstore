<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_common.php 24550 2011-09-26 02:58:48Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = empty($_GET['op'])?'':trim($_GET['op']);

if($op == 'ignore') {

	$type = empty($_GET['type'])?'':preg_replace("/[^0-9a-zA-Z\_\-\.]/", '', $_GET['type']);
	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
	if(submitcheck('ignoresubmit')) {
		$authorid = empty($_POST['authorid']) ? 0 : intval($_POST['authorid']);
		if($type) {
			$type_uid = $type.'|'.$authorid;
			if(empty($space['privacy']['filter_note']) || !is_array($space['privacy']['filter_note'])) {
				$space['privacy']['filter_note'] = array();
			}
			$space['privacy']['filter_note'][$type_uid] = $type_uid;
			privacy_update();
		}
		showmessage('do_success', dreferer(), array('id' => $id ,'type' => $type ,'uid' => $authorid), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}
	$formid = random(8);

} elseif($op == 'closefeedbox') {

	dsetcookie('closefeedbox', 1);

} elseif($op == 'modifyunitprice') {
	if(!$_G['setting']['ranklist']['membershow']) {
		exit('Access Denied');
	}
	$showinfo = C::t('home_show')->fetch($_G['uid']); //DB::fetch_first("SELECT credit, unitprice FROM ".DB::table('home_show')." WHERE uid='$_G[uid]'");
	if(submitcheck('modifysubmit')) {
		$unitprice = intval($_POST['unitprice']);
		if($unitprice < 1) {
			showmessage('showcredit_error', '', array(), array('return' => 1));
		}
		$unitprice = $unitprice > $showinfo['credit'] ? $showinfo['credit'] : $unitprice;
		C::t('home_show')->update($_G['uid'], array('unitprice' => $unitprice));

		showmessage('do_success', dreferer(), array('unitprice' => $unitprice), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}
}

include template('home/spacecp_common');

?>