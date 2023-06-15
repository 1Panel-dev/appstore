<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_forumfield.php 32916 2013-03-22 08:51:36Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_forumfield extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_forumfield';
		$this->_pk    = 'fid';

		parent::__construct();
	}
	public function fetch_all_by_fid($fids) {
		$fids = array_map('intval', (array)$fids);
		if(!empty($fids)) {
			return DB::fetch_all("SELECT * FROM %t WHERE fid IN(%n)", array($this->_table, $fids), $this->_pk);
		} else {
			return array();
		}
	}
	public function fetch_all_field_perm() {
		return DB::fetch_all("SELECT fid, viewperm, postperm, replyperm, getattachperm, postattachperm, postimageperm FROM ".DB::table($this->_table)." WHERE founderuid=0");
	}
	public function fetch_groupnum_by_founderuid($uid) {
		if(empty($uid)) {
			return false;
		}
		return DB::result_first("SELECT COUNT(*) FROM ".DB::table($this->_table)." WHERE founderuid=%d", array($uid));
	}
	public function update($val, $data, $unbuffered = false, $low_priority = false) {
		C::t('forum_forum')->clear_cache(array($val, $val."_with_fields"));
		return parent::update($val, $data, $unbuffered, $low_priority);
	}
	public function update_groupnum($fid, $num) {
		if(!intval($fid) || !intval($num)) {
			return false;
		}
		DB::query("UPDATE %t SET ".DB::field('groupnum', $num, '+')." WHERE fid=%d", array('forum_forumfield', $fid));
	}
	public function update_membernum($fid, $num = 1) {
		if(!intval($fid) || !intval($num)) {
			return false;
		}
		DB::query("UPDATE %t SET ".DB::field('membernum', $num, '+')." WHERE fid=%d", array('forum_forumfield', $fid));
	}
	public function fetch_info_for_attach($fid, $uid) {
		return DB::fetch_first("SELECT f.fid, f.viewperm, f.getattachperm, a.allowgetattach, a.allowgetimage FROM %t f LEFT JOIN %t a ON a.uid=%d AND a.fid=f.fid WHERE f.fid=%d", array('forum_forumfield', 'forum_access', $uid, $fid));
	}
	public function check_moderator_for_uid($fid, $uid, $accessmasks = 0) {
		if(!intval($fid) || !intval($uid)) {
			return false;
		}
		if($accessmasks) {
			$accessadd1 = ', a.allowview, a.allowpost, a.allowreply, a.allowgetattach, a.allowgetimage, a.allowpostattach';
			$accessadd2 = "LEFT JOIN ".DB::table('forum_access')." a ON a.".DB::field('uid', $uid)." AND a.".DB::field('fid', $fid);
		}
		return DB::fetch_first("SELECT ff.postperm, m.uid AS istargetmod $accessadd1
				FROM ".DB::table($this->_table)." ff
				$accessadd2
				LEFT JOIN ".DB::table('forum_moderator')." m ON m.fid=%d AND m.uid=%d
				WHERE ff.fid=%d", array($fid, $uid, $fid));
	}
}

?>