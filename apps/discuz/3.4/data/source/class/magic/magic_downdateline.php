<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_downdateline.php 26749 2011-12-22 07:38:37Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_downdateline {

	var $version = '1.0';
	var $name = 'downdateline_name';
	var $description = 'downdateline_desc';
	var $price = '20';
	var $weight = '20';
	var $useevent = 0;
	var $targetgroupperm = false;
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {}

	function setsetting(&$magicnew, &$parameters) {}

	function usesubmit() {
		global $_G;

		$id = intval($_GET['id']);
		$idtype = $_GET['idtype'];
		$blog = magic_check_idtype($id, $idtype);

		$newdateline = strtotime($_POST['newdateline']);
		if(!$_POST['newdateline'] || $newdateline < strtotime('1970-1-1') || $newdateline > $blog['dateline']) {
			showmessage('magicuse_bad_dateline');
		}

		$tablename = gettablebyidtype($idtype);
		C::t($tablename)->update_dateline_by_id_idtype_uid($id, $idtype, $newdateline, $_G['uid']);

		C::t('home_feed')->update($id, array('dateline'=>$newdateline), $idtype, $_G['uid']);

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', '0', $idtype, $id);
		showmessage('magics_use_success', '', array('magicname'=>$_G['setting']['magics']['downdateline']), array('alert' => 'right', 'showdialog' => 1));
	}

	function show() {
		$id = intval($_GET['id']);
		$idtype = $_GET['idtype'];
		$blog = magic_check_idtype($id, $idtype);
		magicshowtips(lang('magic/downdateline', 'downdateline_info'));
		$time = dgmdate($blog['dateline'], 'Y-m-d H:i');
		$op='use';
		include template('home/magic_downdateline');
	}

}

?>