<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: plugin.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class pluginmodel {

	var $db;
	var $base;

	function __construct(&$base) {
		$this->pluginmodel($base);
	}

	function pluginmodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function get_plugins() {
		include_once UC_ROOT.'./lib/xml.class.php';
		$arr = array();
		$dir = UC_ROOT.'./plugin';
		$d = opendir($dir);
		while($f = readdir($d)) {
			if($f != '.' && $f != '..' && $f != '.svn' && is_dir($dir.'/'.$f)) {
				$s = file_get_contents($dir.'/'.$f.'/plugin.xml');
				$arr1 = xml_unserialize($s);
				$arr1['dir'] = $f;
				unset($arr1['lang']);
				$arr[] = $arr1;
			}
		}
		$arr = $this->orderby_tabindex($arr);
		return $arr;
	}

	function get_plugin($pluginname) {
		$f = file_get_contents(UC_ROOT."./plugin/$pluginname/plugin.xml");
		include_once UC_ROOT.'./lib/xml.class.php';
		return xml_unserialize($f);
	}

	function get_plugin_by_name($pluginname) {
		$dir = UC_ROOT.'./plugin';
		$s = file_get_contents($dir.'/'.$pluginname.'/plugin.xml');
		return xml_unserialize($s, TRUE);
	}

	function orderby_tabindex($arr1) {
		$arr2 = array();
		$t = array();
		foreach($arr1 as $k => $v) {
			$t[$k] = $v['tabindex'];
		}
		asort($t);
		$arr3 = array();
		foreach($t as $k => $v) {
			$arr3[$k] = $arr1[$k];
		}
		return $arr3;
	}

	function cert_get_file() {
		return UC_ROOT.'./data/tmp/ucenter_'.substr(md5(UC_KEY), 0, 16).'.cert';
	}

	function cert_dump_encode($arr, $life = 0) {
		$s = "# UCenter Applications Setting Dump\n".
		"# Version: UCenter ".UC_SERVER_VERSION."\n".
		"# Time: ".$this->time."\n".
		"# Expires: ".($this->time + $life)."\n".
		"# From: ".UC_API."\n".
		"#\n".
		"# This file was BASE64 encoded\n".
		"#\n".
		"# UCenter Community: https://www.discuz.vip\n".
		"# Please visit our website for latest news about UCenter\n".
		"# --------------------------------------------------------\n\n\n".
		wordwrap(base64_encode(serialize($arr)), 50, "\n", 1);
		return $s;
	}

	function cert_dump_decode($certfile) {
		$s = @file_get_contents($certfile);
		if(empty($s)) {
			return array();
		}
		preg_match("/# Expires: (.*?)\n/", $s, $m);
		if(empty($m[1]) || $m[1] < $this->time) {
			unlink($certfile);
			return array();
		}
		$s = preg_replace("/(#.*\s+)*/", '', $s);
		$arr = daddslashes(unserialize(base64_decode($s)), 1);
		return $arr;
	}
}

?>