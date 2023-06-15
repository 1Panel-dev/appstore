<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_manyou.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_manyou {

	public static function manyoulog($logtype, $uids, $action, $fid = '') {
	}

	public static function getuserapp($panel = 0) {
		return true;
	}

	public static function getmyappiconpath($appid, $iconstatus=0) {
		return '';
	}

	public static function checkupdate() {
	}

}

?>