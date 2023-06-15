<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memory_driver_memcached.php 27449 2017-07-11 05:32:35Z ladyff $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class memory_driver_memcached
{
	public $cacheName = 'MemCached';
	public $enable;
	public $obj;

	public function env() {
		return extension_loaded('memcached');
	}
	public function init($config) {
		if (!$this->env()) {
			$this->enable = false;
			return;
		}
		if(!empty($config['server'])) {
			$this->obj = new Memcached;
			$this->obj->addServer($config['server'], $config['port']);
			$connect=$this->obj->set('connect', '1');
			$this->enable = $connect ? true : false;
		}
	}

	public function get($key) {
		return $this->obj->get($key);
	}

	public function getMulti($keys) {
		return $this->obj->getMulti($keys);
	}
	public function set($key, $value, $ttl = 0) {
		return $this->obj->set($key, $value, $ttl);
	}

	public function add($key, $value, $ttl = 0) {
		return $this->obj->add($key, $value, $ttl);
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