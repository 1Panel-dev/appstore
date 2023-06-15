<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: app.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class appmodel {

	var $db;
	var $base;

	function __construct(&$base) {
		$this->appmodel($base);
	}

	function appmodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function get_apps($col = '*', $where = '') {
		$arr = $this->db->fetch_all("SELECT $col FROM ".UC_DBTABLEPRE."applications".($where ? ' WHERE '.$where : ''), 'appid');
		foreach($arr as $k => $v) {
			isset($v['extra']) && !empty($v['extra']) && $v['extra'] = unserialize($v['extra']);
			if($tmp = $this->base->authcode($v['authkey'], 'DECODE', UC_MYKEY)) {
				$v['authkey'] = $tmp;
			}
			$arr[$k] = $v;
		}
		return $arr;
	}

	function get_app_by_appid($appid, $includecert = FALSE) {
		$appid = intval($appid);
		$arr = $this->db->fetch_first("SELECT * FROM ".UC_DBTABLEPRE."applications WHERE appid='$appid'");
		$arr['extra'] = unserialize($arr['extra']);
		if($tmp = $this->base->authcode($arr['authkey'], 'DECODE', UC_MYKEY)) {
			$arr['authkey'] = $tmp;
		}
		if($includecert) {
			$this->load('plugin');
			$certfile = $_ENV['plugin']->cert_get_file();
			$appdata = $_ENV['plugin']->cert_dump_decode($certfile);
			if(is_array($appdata[$appid])) {
				$arr += $appdata[$appid];
			}
		}
		return $arr;
	}

	function delete_apps($appids) {
		$appids = $this->base->implode($appids);
		$this->db->query("DELETE FROM ".UC_DBTABLEPRE."applications WHERE appid IN ($appids)");
		return $this->db->affected_rows();
	}


	function alter_app_table($appid, $operation = 'ADD') {
		if($operation == 'ADD') {
			$this->db->query("ALTER TABLE ".UC_DBTABLEPRE."notelist ADD COLUMN app$appid tinyint NOT NULL", 'SILENT');
		} else {
			$this->db->query("ALTER TABLE ".UC_DBTABLEPRE."notelist DROP COLUMN app$appid", 'SILENT');
		}
	}

	function test_api($url, $ip = '') {
		$this->base->load('misc');
		if(!$ip) {
			$ip = $_ENV['misc']->get_host_by_url($url);
		}

		if($ip < 0) {
			return FALSE;
		}
		return $_ENV['misc']->dfopen($url, 0, '', '', 1, $ip);
	}
}
?>