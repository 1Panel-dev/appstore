<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_block_item.php 27937 2012-02-17 02:51:31Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_block_item extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_block_item';
		$this->_pk    = 'itemid';

		parent::__construct();
	}

	public function delete_by_bid($bid) {
		if(($bid = dintval($bid, true))) {
			return DB::delete($this->_table, DB::field('bid', $bid));
		}
		return false;
	}

	public function delete_by_bid_displayorder($bid, $displayorder) {
		return ($bid = dintval($bid)) ? DB::delete($this->_table, DB::field('bid', $bid).' AND '.DB::field('displayorder', dintval($displayorder))) : false;
	}

	public function fetch_all_by_bid($bids, $sort = false) {
		return ($bids = dintval($bids, true)) ? DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field('bid', $bids).($sort ? ' ORDER BY displayorder, itemtype DESC' : ''), null, $this->_pk) : array();
	}

	public function delete_by_itemid_bid($itemid, $bid) {
		return ($itemid = dintval($itemid, true)) && ($bid = dintval($bid)) ? DB::delete($this->_table, DB::field('itemid', $itemid).' AND '.DB::field('bid', $bid)) : false;
	}

	public function insert_batch($bid, $itemlist) {
		$inserts = array();
		if(($bid = dintval($bid))) {
			foreach($itemlist as $value) {
				if($value) {
					$value = daddslashes($value);
					$inserts[] = "('$value[itemid]', '$bid', '$value[itemtype]', '$value[id]', '$value[idtype]', '$value[title]',
						'$value[url]', '$value[pic]', '$value[picflag]', '$value[makethumb]', '$value[thumbpath]', '$value[summary]',
						'$value[showstyle]', '$value[related]', '$value[fields]', '$value[displayorder]', '$value[startdate]', '$value[enddate]')";
				}
			}
		}
		if($inserts) {
			DB::query('REPLACE INTO '.DB::table($this->_table).'(itemid, bid, itemtype, id, idtype, title, url, pic, picflag, makethumb, thumbpath, summary, showstyle, related, `fields`, displayorder, startdate, enddate) VALUES '.implode(',', $inserts));
		}
	}
}

?>