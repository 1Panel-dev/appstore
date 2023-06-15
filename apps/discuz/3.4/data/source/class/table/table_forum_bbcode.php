<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_bbcode.php 27786 2012-02-14 07:53:14Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_bbcode extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_bbcode';
		$this->_pk    = 'id';

		parent::__construct();
	}
	public function fetch_all_by_available_icon($available = null, $haveicon = false, $glue = '=', $order = 'displayorder', $sort = 'ASC') {
		$parameter = array($this->_table);
		if($available !== null) {
			$parameter[] = $available;
			$glue = helper_util::check_glue($glue);
			$wherearr[] = "available{$glue}%d";
		}
		if($haveicon) {
			$wherearr[] = "icon!=''";
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		$ordersql = !empty($order) ? ' ORDER BY '.DB::order($order, $sort) : '';
		return DB::fetch_all("SELECT * FROM %t $wheresql $ordersql", $parameter, $this->_pk);
	}
}

?>