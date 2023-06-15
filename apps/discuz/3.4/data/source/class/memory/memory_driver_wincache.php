<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memory_driver_wincache.php 31432 2012-08-28 03:04:18Z zhangguosheng $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class memory_driver_wincache {

	public $cacheName = 'WinCache';
	public $enable;

	public function env() {
		return function_exists('wincache_ucache_meminfo') && wincache_ucache_meminfo();
	}

	public function init($config) {
		$this->enable = $this->env();
	}

	public function get($key) {
		return wincache_ucache_get($key);
	}

	public function getMulti($keys) {
		return wincache_ucache_get($keys);
	}

	public function set($key, $value, $ttl = 0) {
		return wincache_ucache_set($key, $value, $ttl);
	}

	public function rm($key) {
		return wincache_ucache_delete($key);
	}

	public function clear() {
		return wincache_ucache_clear();
	}

	public function inc($key, $step = 1) {
		return wincache_ucache_inc($key, $step);
	}

	public function dec($key, $step = 1) {
		return wincache_ucache_dec($key, $step);
	}

}