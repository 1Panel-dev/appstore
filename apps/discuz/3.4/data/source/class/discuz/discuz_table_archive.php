<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: discuz_table_archive.php 31076 2012-07-13 03:30:58Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


class discuz_table_archive extends discuz_table
{

	public $membersplit = null;
	public function __construct($para = array()) {
		$this->membersplit = getglobal('setting/membersplit');
		parent::__construct($para);
	}

	public $tablestatus = array();

	public function fetch($id, $force_from_db = false, $fetch_archive = 0){
		$data = array();
		if(!empty($id)) {
			$data = parent::fetch($id, $force_from_db);
			if(isset($this->membersplit) && $fetch_archive && empty($data)) {
				$data = C::t($this->_table.'_archive')->fetch($id);
			}
		}
		return $data;
	}


	public function fetch_all($ids, $force_from_db = false, $fetch_archive = 1) {
		$data = array();
		if(!empty($ids)) {
			$data = parent::fetch_all($ids, $force_from_db);
			if(isset($this->membersplit) && $fetch_archive && count($data) != count($ids)) {
				$data = $data + C::t($this->_table.'_archive')->fetch_all(array_diff($ids, array_keys($data)));
			}
		}
		return $data;
	}


	public function delete($val, $unbuffered = false, $fetch_archive = 0) {
		$ret = false;
		if($val) {
			$ret = parent::delete($val, $unbuffered);
			if(isset($this->membersplit) && $fetch_archive) {
				$_ret = C::t($this->_table.'_archive')->delete($val, $unbuffered);
				if(!$unbuffered) {
					$ret = $ret + $_ret;
				}
			}
		}
		return $ret;
	}

	public function split_check($wheresql) {
		$status = helper_dbtool::gettablestatus(DB::table($this->_table), false);
		if($status && $status['Data_length'] > 100 * 1048576) {//400 * 1048576
			if($moverows = DB::result_first('SELECT COUNT(*) FROM %t WHERE '.$wheresql, array($this->_table))) {
				$status['Move_rows'] = $moverows;
				$this->tablestatus = $status;
				return true;
			}
		}
		return false;
	}

	public function create_relatedtable($relatedtablename) {
		if(!helper_dbtool::isexisttable($relatedtablename)) {
			DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
			$tableinfo = DB::fetch_first("SHOW CREATE TABLE ".DB::table($this->_table));
			$createsql = $tableinfo['Create Table'];
			$createsql = str_replace($this->_table, $relatedtablename, $createsql);
			DB::query($createsql);
		}
		return true;
	}

	public function split_table($wheresql) {
		$limit = 2000;
		$targettable = helper_dbtool::showtablecloumn($this->_table);
		$fieldstr = '`'.implode('`, `', array_keys($targettable)).'`';

		if(!$this->_pk && !in_array('split_id', array_keys($targettable))) {
			DB::query('ALTER TABLE %t ADD split_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, ADD UNIQUE KEY split_id (split_id)', array($this->_table));
			return 1;
		}

		$tmptable = $this->_table.'_tmp___';
		$archivetable = $this->_table.'_archive';
		$key = $this->_pk ? $this->_pk : 'split_id';
		$this->create_relatedtable($tmptable);
		$this->create_relatedtable($archivetable);
		DB::query("INSERT INTO %t ($fieldstr) SELECT $fieldstr FROM %t WHERE $wheresql ".DB::limit($limit), array($tmptable, $this->_table));
		if(DB::result_first('SELECT COUNT(*) FROM %t', array($tmptable))) {
			$keylist = DB::fetch_all('SELECT '.$key.' FROM %t', array($tmptable), $key);
			$keylist = dimplode(array_keys($keylist));
			if(DB::query("INSERT INTO %t ($fieldstr) SELECT $fieldstr FROM %t WHERE $key in ($keylist)", array($archivetable, $this->_table), false, true)) {
				DB::query("DELETE FROM %t WHERE $key in ($keylist)", array($this->_table), false, true);
			}
			DB::query('DROP TABLE %t', array($tmptable));
			return 1;
		} else {
			DB::query('DROP TABLE %t', array($tmptable));
			$this->optimize();
			return 2;
		}
	}

	public function merge_table() {
		$limit = 2000;

		$tmptable = $this->_table.'_tmp___';
		$archivetable = $this->_table.'_archive';
		$key = $this->_pk ? $this->_pk : 'split_id';

		if(!helper_dbtool::isexisttable($archivetable)) {
			return 2;
		}

		$this->create_relatedtable($tmptable);
		$targettable = helper_dbtool::showtablecloumn($this->_table);
		$fieldstr = '`'.implode('`, `', array_keys($targettable)).'`';
		DB::query("INSERT INTO %t ($fieldstr) SELECT $fieldstr FROM %t ".DB::limit($limit), array($tmptable, $archivetable));
		if(DB::result_first('SELECT COUNT(*) FROM %t', array($tmptable))) {
			$keylist = DB::fetch_all('SELECT '.$key.' FROM %t', array($tmptable), $key);
			$keylist = dimplode(array_keys($keylist));
			if(DB::query("INSERT INTO %t ($fieldstr) SELECT $fieldstr FROM %t WHERE $key in ($keylist)", array($this->_table, $archivetable), false, true)) {
				DB::query("DELETE FROM %t WHERE $key in ($keylist)", array($archivetable), false, true);
			}
			DB::query('DROP TABLE %t', array($tmptable));
			return 1;
		} else {
			DB::query('DROP TABLE %t', array($tmptable));
			DB::query('DROP TABLE %t', array($archivetable));
			$this->optimize();
			return 2;
		}
	}
}

?>