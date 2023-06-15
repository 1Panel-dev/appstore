<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_sql.php 24721 2011-10-09 10:30:22Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class ultrax_cache {

	function ultrax_cache($conf) {
		$this->conf = $conf;
	}

	function get_cache($key) {
		static $data = null;
		if(!isset($data[$key])) {
			$cache = C::t('common_cache')->fetch($key);
			if(!$cache) {
				return false;
			}
			$data[$key] = unserialize($cache['cachevalue']);
			if($cache['life'] && ($cache['dateline'] < time() - $data[$key]['life'])) {
				return false;
			}
		}
		return $data[$key]['data'];
	}

	function set_cache($key, $value, $life) {
		$data = array(
			'cachekey' => $key,
			'cachevalue' => serialize(array('data' => $value, 'life' => $life)),
			'dateline' => time(),
			);
		return C::t('common_cache')->insert($data);
	}

	function del_cache($key) {
		return C::t('common_cache')->delete($key);
	}
}