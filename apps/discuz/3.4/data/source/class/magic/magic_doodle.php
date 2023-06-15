<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_doodle.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_doodle {

	var $version = '1.0';
	var $name = 'doodle_name';
	var $description = 'doodle_desc';
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

		$config = urlencode(getsiteurl().'home.php?mod=misc&ac=swfupload&op=config&doodle=1');
		include template('home/magic_doodle');
	}

	function show() {
		global $_G;
		magicshowtips(lang('magic/doodle', 'doodle_info'));
		echo '
<p>
	<input type="hidden" name="showid" value="'.htmlspecialchars($_GET[showid]).'" />
	<input type="hidden" name="mtarget" value="'.htmlspecialchars($_GET[target]).'" />
	<input type="hidden" name="from" value="'.htmlspecialchars($_GET[from]).'" />
</p>
';
	}

}

?>