<?php

/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: db.class.php 976 2009-12-03 03:02:44Z zhaoxiongfei $
*/

if(!defined('IN_COMSENZ')) {
	exit('Access Denied');
}

class dbstuff {
	var $querynum = 0;
	var $link;
	var $histories;
	var $time;
	var $tablepre;

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset, $pconnect = 0, $tablepre='', $time = 0) {
		$this->time = $time;
		$this->tablepre = $tablepre;

		if(!$this->link = new mysqli($dbhost, $dbuser, $dbpw, $dbname)) {
			$this->halt('Can not connect to MySQL server');
		}

		if($dbcharset) {
			$this->link->set_charset($dbcharset);
		}

		$this->link->query("SET sql_mode=''");

		$this->link->query("SET character_set_client=binary");

	}

	function fetch_array($query, $result_type = MYSQLI_ASSOC) {
		return $query ? $query->fetch_array($result_type) : null;
	}

	function result_first($sql, &$data) {
		$query = $this->query($sql);
		$data = $this->result($query, 0);
	}

	function fetch_first($sql, &$arr) {
		$query = $this->query($sql);
		$arr = $this->fetch_array($query);
	}

	function fetch_all($sql, &$arr) {
		$query = $this->query($sql);
		while($data = $this->fetch_array($query)) {
			$arr[] = $data;
		}
	}

	function cache_gc() {
		$this->query("DELETE FROM {$this->tablepre}sqlcaches WHERE expiry<$this->time");
	}

	function query($sql, $type = '', $cachetime = FALSE) {
		$resultmode = $type == 'UNBUFFERED' ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT;
		if(!($query = $this->link->query($sql, $resultmode)) && $type != 'SILENT') {
			$this->halt('MySQL Query Error', $sql);
		}
		$this->querynum++;
		$this->histories[] = $sql;
		return $query;
	}

	function affected_rows() {
		return $this->link->affected_rows;
	}

	function error() {
		return (($this->link) ? $this->link->error : mysqli_error());
	}

	function errno() {
		return intval(($this->link) ? $this->link->errno : mysqli_errno());
	}

	function result($query, $row) {
		if(!$query || $query->num_rows == 0) {
			return null;
		}
		$query->data_seek($row);
		$assocs = $query->fetch_row();
		return $assocs[0];
	}

	function num_rows($query) {
		$query = $query ? $query->num_rows : 0;
		return $query;
	}

	function num_fields($query) {
		return $query ? $query->field_count : 0;
	}

	function free_result($query) {
		return $query ? $query->free() : false;
	}

	function insert_id() {
		return ($id = $this->link->insert_id) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = $query ? $query->fetch_row() : null;
		return $query;
	}

	function fetch_fields($query) {
		return $query ? $query->fetch_field() : null;
	}

	function version() {
		return $this->link->server_info;
	}

	function escape_string($str) {
		return $this->link->escape_string($str);
	}

	function close() {
		return $this->link->close();
	}

	function halt($message = '', $sql = '') {
		show_error('run_sql_error', $message.'<br /><br />'.$sql.'<br /> '.$this->error(), 0);
	}
}

?>