<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memory_driver_memcache.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class memory_driver_memcache {

	public $cacheName = 'MemCache';
	public $enable;
	public $obj;

	public function env() {
		return extension_loaded('memcache');
	}

	public function init($config) {
		if (!$this->env()) {
			$this->enable = false;
			return;
		}
		if (!empty($config['server'])) {
			$this->obj = new Memcache;
			if ($config['pconnect']) {
				$connect = @$this->obj->pconnect($config['server'], $config['port']);
			} else {
				$connect = @$this->obj->connect($config['server'], $config['port']);
			}
			$this->enable = $connect ? true : false;
		}
	}

	public function get($key) {
		return $this->obj->get($key);
	}

	public function getMulti($keys) {
		return $this->obj->get($keys);
	}

	public function set($key, $value, $ttl = 0) {
		return $this->obj->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
	}

	public function add($key, $value, $ttl = 0) {
		return $this->obj->add($key, $value, MEMCACHE_COMPRESSED, $ttl);
	}

	public function rm($key) {
		return $this->obj->delete($key);
	}

	public function clear() {
		return $this->obj->flush();
	}

	public function inc($key, $step = 1) {
		return $this->obj->increment($key, $step);
	}

	public function dec($key, $step = 1) {
		return $this->obj->decrement($key, $step);
	}

}

?>