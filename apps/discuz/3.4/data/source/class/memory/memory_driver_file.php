<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memory_driver_yac.php 27635 2017-02-02 17:02:46Z NaiXiaoxIN $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class memory_driver_file {

	public $cacheName = 'File';
	public $enable;
	public $path;

	public function env() {
		return true;
	}

	public function init($config) {
		$this->path = $config['server'].'/';
		if($config['server']) {
			$this->enable = is_dir(DISCUZ_ROOT.$this->path);
			if(!$this->enable) {
				dmkdir(DISCUZ_ROOT.$this->path);
				$this->enable = is_dir(DISCUZ_ROOT.$this->path);
			}
		} else {
			$this->enable = false;
		}
	}

	private function cachefile($key) {
		return str_replace('_', '/', $key).'/'.$key;
	}

	public function get($key) {
		$file = DISCUZ_ROOT.$this->path.$this->cachefile($key).'.php';
		if(!file_exists($file)) {
			return false;
		}
		$data = null;
		@include $file;
		if($data !== null) {
			if($data['exp'] && $data['exp'] < TIMESTAMP) {
				return false;
			} else {
				return $data['data'];
			}
		} else {
			return false;
		}
	}

	public function set($key, $value, $ttl = 0) {
		$file = DISCUZ_ROOT.$this->path.$this->cachefile($key).'.php';
		dmkdir(dirname($file));
		$data = array(
		    'exp' => $ttl ? TIMESTAMP + $ttl : 0,
		    'data' => $value,
		);
		return file_put_contents($file, "<?php\n\$data = ".var_export($data, 1).";\n", LOCK_EX) !== false;
	}

	public function rm($key) {
		return @unlink(DISCUZ_ROOT.$this->path.$this->cachefile($key).'.php');
	}

	private function dir_clear($dir) {
		if($directory = @dir($dir)) {
			while($entry = $directory->read()) {
				$filename = $dir.'/'.$entry;
				if($entry != '.' && $entry != '..') {
					if(is_file($filename)) {
						@unlink($filename);
					} else {
						$this->dir_clear($filename);
						@rmdir($filename);
					}
				}
			}
			$directory->close();
		}
	}

	public function clear() {
		return $this->dir_clear(DISCUZ_ROOT.$this->path);
	}

	public function inc($key, $step = 1) {
		$old = $this->get($key);
		if (!$old) {
			return false;
		}
		return $this->set($key, $old + $step);
	}

	public function dec($key, $step = 1) {
		$old = $this->get($key);
		if (!$old) {
			return false;
		}
		return $this->set($key, $old - $step);
	}

	public function exists($key) {
		return $this->get($key) !== false;
	}
}