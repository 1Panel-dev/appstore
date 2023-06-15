<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_stylevar.php 28934 2012-03-20 04:00:22Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_stylevar extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_stylevar';
		$this->_pk    = 'stylevarid';

		parent::__construct();
	}

	public function fetch_all_by_styleid($styleid, $available = false) {
		if($available !== false) {
			return DB::fetch_all("SELECT sv.* FROM %t sv INNER JOIN %t s ON s.styleid = sv.styleid AND (s.available=%d OR s.styleid=%d)", array($this->_table, 'common_style', $available, $styleid));
		} else {
			return DB::fetch_all("SELECT * FROM %t WHERE styleid=%d", array($this->_table, $styleid));
		}
	}

	public function check_duplicate($styleid, $variable) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE styleid=%d AND variable=%s", array($this->_table, $styleid, $variable));
	}

	public function update_substitute_by_styleid($substitute, $id, $stylevarids = array()) {
		if(!is_string($substitute) || !$id) {
			return;
		}
		DB::update($this->_table, array('substitute' => $substitute), ($stylevarids ? DB::field('stylevarid', $stylevarids).' AND ' : '').DB::field('styleid', $id));
	}

	public function delete_by_styleid($id, $stylevarids = array()) {
		if(!$id) {
			return;
		}
		DB::delete($this->_table,($stylevarids ? DB::field('stylevarid', $stylevarids).' AND ' : '').DB::field('styleid', $id));
	}

}

?>