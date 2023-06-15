<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_count.php 31022 2012-07-10 03:16:07Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_count extends discuz_table_archive
{
	public function __construct() {

		$this->_table = 'common_member_count';
		$this->_pk    = 'uid';
		$this->_pre_cache_key = 'common_member_count_';

		parent::__construct();
	}

	public function increase($uids, $creditarr) {
		$uids = dintval((array)$uids, true);
		$sql = array();
		$allowkey = array('extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8',
						'friends', 'posts',	'threads', 'oltime', 'digestposts', 'doings', 'blogs', 'albums', 'sharings', 'attachsize', 'views',
						'todayattachs', 'todayattachsize', 'follower', 'following', 'newfollower', 'feeds', 'blacklist');
		foreach($creditarr as $key => $value) {
			if(($value = intval($value)) && $value && in_array($key, $allowkey)) {
				$sql[] = "`$key`=`$key`+'$value'";
			}
		}
		if(!empty($sql)){
			DB::query("UPDATE ".DB::table($this->_table)." SET ".implode(',', $sql)." WHERE uid IN (".dimplode($uids).")", 'UNBUFFERED');
			$this->increase_cache($uids, $creditarr);
		}
	}

	public function clear_extcredits($uids, $extcredits) {
		$uids = dintval((array)$uids, true);
		$sql = $data = array();
		$allowkey = array('extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8');
		foreach($extcredits as $value) {
			if(in_array($value, $allowkey, true)) {
				$sql[] = "`$value`='0'";
				$data[$value] = 0;
			}
		}
		if(!empty($sql)) {
			DB::query("UPDATE ".DB::table($this->_table)." SET ".implode(',', $sql)." WHERE uid IN (".dimplode($uids).")", 'UNBUFFERED');
			$this->update_batch_cache($uids, $data);
		}
	}

	public function count_by_posts($num) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE posts=%d', array($this->_table, $num));
	}

	public function range_by_field($start = 0, $limit = 0, $orderby = '', $sort = '') {
		$orderby = in_array($orderby, array(
			'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8',
			'friends', 'posts',	'threads', 'oltime', 'digestposts', 'doings', 'blogs', 'albums', 'sharings', 'attachsize', 'views',
			'todayattachs', 'todayattachsize', 'follower', 'following', 'newfollower', 'feeds', 'blacklist'), true) ? $orderby : '';
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).($orderby ? ' ORDER BY '.DB::order($orderby, $sort) : '').DB::limit($start, $limit), null, $this->_pk);
	}

	public function clear_digestposts() {
		$uids = array();
		if($this->_allowmem) {
			$uids = DB::fetch_all('SELECT uid FROM '.DB::table($this->_table).' WHERE digestposts<>0', null, $this->_pk);
		}
		$data = DB::query("UPDATE ".DB::table($this->_table)." SET digestposts=0", 'UNBUFFERED');
		if(!empty($uids)) {
			$this->update_batch_cache(array_keys($uids), array('digestposts' => 0));
		}
		return $data;
	}

	public function clear_today_data() {
		$uids = array();
		if($this->_allowmem) {
			$uids = DB::fetch_all('SELECT uid FROM '.DB::table($this->_table).' WHERE todayattachs<>0 OR todayattachsize<>0', null, $this->_pk);
		}
		$data = DB::query("UPDATE ".DB::table($this->_table)." SET todayattachs='0',todayattachsize='0'", 'UNBUFFERED');
		if(!empty($uids)) {
			$this->update_batch_cache(array_keys($uids), array('todayattachs' => 0, 'todayattachsize' => 0));
		}
		return $data;
	}

	public function count_by_extcredits($extcredits, $credits) {
		$count = 0;
		if(in_array($extcredits, array(1,2,3,4,5,6,7,8))) {
			$count =  DB::result_first('SELECT COUNT(*) FROM %t WHERE extcredits'.$extcredits.'>%d', array($this->_table, $credits));
		}
		return $count;
	}


	public function count_by_friends($friends) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE friends>%d', array($this->_table, $friends));
	}
}

?>