<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_wechatmp.php 35227 2015-03-05 06:49:24Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_wechatmp extends discuz_table {

	public function __construct() {
		$this->_table = 'common_member_wechatmp';
		$this->_pk = 'uid';
		$this->_pre_cache_key = 'common_member_wechatmp_';

		parent::__construct();
	}

	public function fetch_by_openid($openid) {
		return DB::fetch_all('SELECT * FROM %t WHERE openid=%s', array($this->_table, $openid), 'uid');
	}

}