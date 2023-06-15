<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_article_trash.php 27836 2012-02-15 08:14:02Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_article_trash extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_article_trash';
		$this->_pk    = 'aid';

		parent::__construct();
	}

	public function insert_batch($inserts) {
		$sql = array();
		foreach($inserts as $value) {
			if(($value['aid'] = dintval($value['aid']))) {
				$sql[] = "('$value[aid]', '".addslashes($value['content'])."')";
			}
		}
		if($sql) {
			DB::query('INSERT INTO '.DB::table($this->_table)."(`aid`, `content`) VALUES ".implode(', ', $sql));
		}
	}
}

?>