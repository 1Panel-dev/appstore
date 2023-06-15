<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_article_content.php 32272 2012-12-13 07:20:34Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_article_content extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_article_content';
		$this->_pk    = 'cid';

		parent::__construct();
	}

	public function update_by_aid($aid, $data) {
		if(($aid = dintval($aid)) && !empty($data) && is_array($data)) {
			return DB::update($this->_table, $data, array('aid' => $aid));
		}
		return 0;
	}
	public function fetch_by_aid_page($aid, $page = 1) {
		if(($page = dintval($page))<1) $page = 1;
		return $aid ? DB::fetch_first('SELECT * FROM %t WHERE aid=%d ORDER BY pageorder'.DB::LIMIT($page-1, 1), array($this->_table, $aid)) : false;
	}

	public function fetch_all($aid) {
		return $aid ? DB::fetch_all('SELECT * FROM %t WHERE aid=%d ORDER BY pageorder', array($this->_table, $aid)) : array();
	}

	public function fetch_max_pageorder_by_aid($aid) {
		return $aid ? DB::result_first('SELECT MAX(pageorder) FROM %t WHERE aid=%d', array($this->_table, $aid)) : 0;
	}

	public function insert_batch($inserts) {
		$sql = array();
		foreach($inserts as $value) {
			$value['aid'] = dintval($value['aid']);
			$sql[] = "('$value[aid]', '".addslashes($value['content'])."', '$value[pageorder]', '$value[dateline]', '$value[id]', '$value[idtype]')";
		}
		if($sql) {
			DB::query('INSERT INTO '.DB::table($this->_table)."(`aid`, `content`, `pageorder`, `dateline`, `id`, `idtype`) VALUES ".implode(', ', $sql));
		}
	}

	public function count_by_aid($aid) {
		return $aid ? DB::result_first('SELECT COUNT(*) FROM %t WHERE aid=%d', array($this->_table, $aid)) : 0;
	}

	public function delete_by_aid($aid) {
		return dintval($aid, true) ? DB::delete($this->_table, DB::field('aid', $aid)) : 0;
	}
}

?>