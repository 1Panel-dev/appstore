<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_groupcreditslog.php 27763 2012-02-14 03:42:56Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_groupcreditslog extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_groupcreditslog';
		$this->_pk    = '';

		parent::__construct();
	}
	public function check_logdate($fid, $uid, $logdate) {
		return DB::result_first("SELECT logdate FROM %t WHERE fid=%d AND uid=%d AND logdate=%s", array($this->_table, $fid, $uid, $logdate));
	}
	public function delete_by_fid($fid) {
		if(empty($fid)) {
			return false;
		}
		DB::query("DELETE FROM ".DB::table('forum_groupcreditslog')." WHERE ".DB::field('fid', $fid));
	}
}

?>