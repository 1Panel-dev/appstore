<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_magic.php 27757 2012-02-14 03:08:15Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_magic extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_member_magic';
		$this->_pk    = '';

		parent::__construct();
	}

	public function delete($uid = null, $magicid = null) {
		$para = array();
		if($uid) {
			$para[] = DB::field('uid', $uid);
		}
		if($magicid) {
			$para[] = DB::field('magicid', $magicid);
		}
		if(!($where = $para ? implode(' AND ', $para) : '')) {
			return null;
		}
		return DB::delete($this->_table, $where);
	}

	public function fetch_all($uid, $magicid = '', $start = 0, $limit = 0) {
		$para = array();
		if($uid) {
			$para[] = DB::field('uid', $uid);
		}
		if($magicid) {
			$para[] = DB::field('magicid', $magicid);
		}
		if($limit) {
			$sql = DB::limit($start, $limit);
		}
		if(!count($para)) {
			return null;
		}
		$para = implode(' AND ', $para);
		return DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, $para.$sql));
	}

	public function fetch($uid, $magicid) {
		$para = array();
		if($uid) {
			$para[] = DB::field('uid', $uid);
		}
		if($magicid) {
			$para[] = DB::field('magicid', $magicid);
		}
		if(!count($para)) {
			return null;
		}
		$sql = implode(' AND ', $para);
		return DB::fetch_first('SELECT * FROM %t WHERE %i', array($this->_table, $sql));
	}

	public function count($uid, $magicid) {
		$para = array();
		if($uid) {
			$para[] = DB::field('uid', $uid);
		}
		if($magicid) {
			$para[] = DB::field('magicid', $magicid);
		}
		if(!count($para)) {
			return null;
		}
		$sql = implode(' AND ', $para);
		return (int) DB::result_first('SELECT count(*) FROM %t WHERE %i', array($this->_table, $sql));
	}

	public function increase($uid, $magicid, $setarr, $slient = false, $unbuffered = false) {
		$para = array();
		$setsql = array();
		$allowkey = array('num');
		foreach($setarr as $key => $value) {
			if(($value = intval($value)) && in_array($key, $allowkey)) {
				$setsql[] = "`$key`=`$key`+'$value'";
			}
		}
		if($uid) {
			$para[] = DB::field('uid', $uid);
		}
		if($magicid) {
			$para[] = DB::field('magicid', $magicid);
		}
		if(!count($para) || !count($setsql)) {
			return null;
		}
		$sql = implode(' AND ', $para);
		return DB::query('UPDATE %t SET %i WHERE %i', array($this->_table, implode(',', $setsql), $sql), $slient, $unbuffered);
	}

	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t mm, %t m WHERE mm.uid=%d AND mm.magicid=m.magicid', array($this->_table, 'common_magic', $uid));
	}

	public function fetch_magicid_by_identifier($uid, $identifier) {
		return DB::result_first('SELECT m.magicid FROM %t mm,%t m WHERE mm.uid=%d AND m.identifier=%s AND mm.magicid=m.magicid', array($this->_table, 'common_magic', $uid, $identifier));
	}

}

?>