<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_block_item_data.php 31958 2012-10-26 05:11:05Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_block_item_data extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_block_item_data';
		$this->_pk    = 'dataid';

		parent::__construct();
	}

	public function fetch_all_by_bid($bid, $isverified = 1, $start = 0, $limit = 0, $bannedids = array(), $format = true) {
		$data = array();
		if(($bid = dintval($bid, true))) {
			$addsql = $bannedids = dintval($bannedids, true) ? ' AND id NOT IN ('.dimplode($bannedids).')' : '';
			$query = DB::query('SELECT * FROM %t WHERE '.DB::field('bid', $bid).' AND isverified=%d'.$addsql.' ORDER BY stickgrade DESC, displayorder DESC, verifiedtime DESC, dataid DESC '.DB::limit($start, $limit), array($this->_table, $isverified));
			while($value = DB::fetch($query)) {
				if($format) {
					$value['fields'] = unserialize($value['fields']);
					$value['fields']['timestamp'] = $value['fields']['dateline'];
					$value['fields']['dateline'] = dgmdate($value['fields']['dateline']);
					$value['pic'] = $value['pic'] !== STATICURL.'image/common/nophoto.gif' ? $value['pic'] : '';
					if($value['pic'] && $value['picflag'] == '1') {
						$value['pic'] = getglobal('setting/attachurl').$value['pic'];
					} elseif ($value['picflag'] == '2') {
						$value['pic'] = getglobal('setting/ftp/attachurl').$value['pic'];
					}
					$value['dateline'] = dgmdate($value['dateline'], 'u');
					$value['verifiedtime'] = dgmdate($value['verifiedtime'], 'u');
				}
				$data[$value['id']] = $value;
			}
		}
		return $data;
	}

	public function count_by_bid($bid, $isverified = 1) {
		return ($bid = dintval($bid, true)) ? DB::result_first('SELECT COUNT(*) FROM %t WHERE '.DB::field('bid', $bid).' AND isverified=%d', array($this->_table, $isverified)) : 0;
	}

	public function delete_by_bid($bid) {
		if(($bid = dintval($bid, true))) {
			DB::delete($this->_table, DB::field('bid', $bid));
		}
	}

	public function delete_by_dataid_bid($dataids, $bid) {
		if(($dataids = dintval($dataids, true)) && ($dataids = DB::fetch_all('SELECT dataid FROM %t WHERE dataid IN (%n) AND bid=%d', array($this->_table, $dataids, $bid), $this->_pk))) {
			$this->delete(array_keys($dataids));
		}
	}

	public function fetch_by_bid_id_idtype($bid, $id, $idtype) {
		return $bid && $id && $idtype ? DB::fetch_first('SELECT * FROM %t WHERE bid=%d AND id=%d AND idtype=%s', array($this->_table, $bid, $id, $idtype)) : array();
	}
}

?>