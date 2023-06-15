<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_groupinvite.php 27763 2012-02-14 03:42:56Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_groupinvite extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_groupinvite';
		$this->_pk    = 'fid';

		parent::__construct();
	}
	public function fetch_uid_by_inviteuid($fid, $inviteuid) {
		return DB::result_first("SELECT uid FROM %t WHERE fid=%d AND inviteuid=%d", array($this->_table, $fid, $inviteuid));
	}
	public function fetch_all_inviteuid($fid, $inviteuids, $uid) {
		if(empty($fid) || empty($uid) || empty($inviteuids)) {
			return array();
		}
		return DB::fetch_all("SELECT inviteuid FROM %t WHERE fid=%d AND ".DB::field('inviteuid', $inviteuids)." AND uid=%d", array($this->_table, $fid, $uid));
	}
	public function delete_by_inviteuid($fid, $inviteuid) {
		DB::query("DELETE FROM %t WHERE fid=%d AND inviteuid=%d", array($this->_table, $fid, $inviteuid));
	}
	public function affected_rows() {
		return DB::affected_rows();
	}
}

?>