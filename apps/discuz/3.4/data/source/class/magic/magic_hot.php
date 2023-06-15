<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_hot.php 26749 2011-12-22 07:38:37Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_hot {

	var $version = '1.0';
	var $name = 'hot_name';
	var $description = 'hot_desc';
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

		if(C::t('common_magiclog')->count_by_action_uid_targetid_idtype_magicid(2, $_G['uid'], $id, $idtype, $this->magic['magicid'])) {
			showmessage('magicuse_object_once_limit');
		}

		$num = !empty($_G['setting']['feedhotmin']) ? intval($_G['setting']['feedhotmin']) : 3;
		C::t('home_feed')->update_hot_by_id($id, $idtype, $_G['uid'], $num);
		C::t('home_blog')->increase($id, $_G['uid'], array('hot' => $num));

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', '0', $idtype, $id);
		showmessage('magics_use_success', '', array('magicname'=>$_G['setting']['magics']['hot']), array('alert' => 'right', 'showdialog' => 1));
	}

	function show() {
		global $_G;
		$id = intval($_GET['id']);
		$idtype = $_GET['idtype'];
		$blog = magic_check_idtype($id, $idtype);
		if(C::t('common_magiclog')->count_by_action_uid_targetid_idtype_magicid(2, $_G['uid'], $id, $idtype, $this->magic['magicid'])) {
			showmessage('magicuse_object_once_limit');
		}

		$num = !empty($_G['setting']['feedhotmin']) ? intval($_G['setting']['feedhotmin']) : 3;
		magicshowtips(lang('magic/hot', 'hot_info', array('num'=>$num)));
		echo <<<HTML
<p>
	<input type="hidden" name="id" value="'.$id.'" />
	<input type="hidden" name="idtype" value="'.$idtype.'" />
</p>
HTML;
	}

}

?>