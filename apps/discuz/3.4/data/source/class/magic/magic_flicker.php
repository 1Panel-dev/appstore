<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_flicker.php 26901 2011-12-27 07:16:24Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_flicker {
	var $version = '1.0';
	var $name = 'flicker_name';
	var $description = 'flicker_desc';
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

		C::t('home_comment')->update($_GET['id'], array('magicflicker' => 1), $_G['uid']);
		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0');
		showmessage(lang('magic/flicker', 'flicker_succeed'), dreferer(), array(), array('alert' => 'right', 'showdialog' => 1, 'closetime' => true, 'locationtime' => true));
	}

	function show() {
		global $_G;
		magicshowtips(lang('magic/flicker', 'flicker_info'));
	}
}

?>