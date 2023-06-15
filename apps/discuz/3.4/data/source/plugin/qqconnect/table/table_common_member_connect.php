<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_connect.php 29265 2012-03-31 06:03:26Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_connect extends discuz_table {

	private $_fields;

	public function __construct() {
		$this->_table = 'common_member_connect';
		$this->_pk = 'uid';
		$this->_fields = array('uid', 'conuin', 'conuinsecret', 'conopenid', 'conisfeed', 'conispublishfeed', 'conispublisht', 'conisregister', 'conisqzoneavatar');
		$this->_pre_cache_key = 'common_member_connect_';
		$this->_cache_ttl = 0;

		parent::__construct();
	}

	public function fetch_fields_by_openid($openid, $fields = array()) {
		$fields = (array)$fields;
		if(!empty($fields)) {
			$field = implode(',', array_intersect($fields, $this->_fields));
		} else {
			$field = '*';
		}

		return DB::fetch_first('SELECT %i FROM %t WHERE conopenid=%s', array($field, $this->_table, $openid));
	}

	public function update_by_openid($openId, $data) {

		return DB::update($this->_table, $data, DB::field('openId', $openId));
	}
}