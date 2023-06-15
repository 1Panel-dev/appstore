<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_attach.php 32531 2013-02-06 10:15:19Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_attach {

	public static function makethumbpath($id, $w, $h){
		$dw = intval($w);
		$dh = intval($h);
		$_daid = sprintf("%09d", $id);
		$dir1 = substr($_daid, 0, 3);
		$dir2 = substr($_daid, 3, 2);
		$dir3 = substr($_daid, 5, 2);
		return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($_daid, -2).'_'.$dw.'_'.$dh.'.jpg';
	}

	public static function attachpreurl() {
		static $attachurl = null;
		if($attachurl === null) {
			global $_G;
			$parse = parse_url($_G['setting']['attachurl']);
			$attachurl = !isset($parse['host']) ? $_G['siteurl'].$_G['setting']['attachurl'] : $_G['setting']['attachurl'];
		}
		return $attachurl;
	}

}

?>