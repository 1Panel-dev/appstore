<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: discuz_process.php 28412 2012-02-29 06:14:48Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class discuz_process
{
	public static function islocked($process, $ttl = 0, $autounlock = 0) {
		$ttl = $ttl < 1 ? 600 : intval($ttl);
		$status = discuz_process::_status('get', $process) || discuz_process::_find($process, $ttl);

		if($autounlock && !$status) {
			register_shutdown_function('discuz_process::unlock', $process);
		}

		return $status;
	}

	public static function unlock($process) {
		discuz_process::_status('rm', $process);
		discuz_process::_cmd('rm', $process);
	}

	private static function _status($action, $process) {
		static $plist = array();
		switch ($action) {
			case 'add' : $plist[$process] = true; break;
			case 'get' : return !empty($plist[$process]); break;
			case 'rm' : $plist[$process] = null; break;
			case 'clear' : $plist = array(); break;
		}
		return true;
	}

	private static function _find($name, $ttl) {

		if(!discuz_process::_cmd('get', $name)) {
			if(discuz_process::_cmd('add', $name, $ttl) == true) {
				$ret = false;
			} else {
				$ret = true;
			}
		} else {
			$ret = true;
		}
		discuz_process::_status('add', $name);
		return $ret;
	}

	private static function _cmd($cmd, $name, $ttl = 0) {
		static $allowmem;
		if($allowmem === null) {
			$mc = strtolower(memory('check'));
			$allowmem = $mc == 'memcache' || $mc == 'redis' || $mc == 'memcached';
		}
		if($allowmem) {
			return discuz_process::_process_cmd_memory($cmd, $name, $ttl);
		} else {
			return discuz_process::_process_cmd_db($cmd, $name, $ttl);
		}
	}

	private static function _process_cmd_memory($cmd, $name, $ttl = 0) {
		$ret = '';
		switch ($cmd) {
			case 'add' :
				$ret = memory('add', 'process_lock_'.$name, time(), $ttl);
				break;
			case 'get' :
				$ret = memory('get', 'process_lock_'.$name);
				break;
			case 'rm' :
				$ret = memory('rm', 'process_lock_'.$name);
		}
		return $ret;
	}

	private static function _process_cmd_db($cmd, $name, $ttl = 0) {
		$ret = '';
		switch ($cmd) {
			case 'add':
				$ret = C::t('common_process')->insert(array('processid' => $name, 'expiry' => time() + $ttl), FALSE, true);
				break;
			case 'get':
				$ret = C::t('common_process')->fetch($name);
				if(empty($ret) || $ret['expiry'] < time()) {
					C::t('common_process')->delete_process($name, time());
					$ret = false;
				} else {
					$ret = true;
				}
				break;
			case 'rm':
				$ret = C::t('common_process')->delete_process($name, time());
				break;
		}
		return $ret;
	}
}

?>