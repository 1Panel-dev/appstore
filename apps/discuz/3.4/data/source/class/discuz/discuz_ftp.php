<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: discuz_ftp.php 32473 2013-01-24 07:11:38Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!defined('FTP_ERR_SERVER_DISABLED')) {
	define('FTP_ERR_SERVER_DISABLED', -100);
	define('FTP_ERR_CONFIG_OFF', -101);
	define('FTP_ERR_CONNECT_TO_SERVER', -102);
	define('FTP_ERR_USER_NO_LOGGIN', -103);
	define('FTP_ERR_CHDIR', -104);
	define('FTP_ERR_MKDIR', -105);
	define('FTP_ERR_SOURCE_READ', -106);
	define('FTP_ERR_TARGET_WRITE', -107);
}



class discuz_ftp
{

	var $enabled = false;
	var $config = array();

	var $func;
	var $connectid;
	var $_error;

	function &instance($config = array()) {
		static $object;
		if(empty($object)) {
			$object = new discuz_ftp($config);
		}
		return $object;
	}

	function __construct($config = array()) {
		$this->set_error(0);
		$this->config = !$config ? getglobal('setting/ftp') : $config;
		$this->enabled = false;
		if(empty($this->config['on']) || empty($this->config['host'])) {
			$this->set_error(FTP_ERR_CONFIG_OFF);
		} else {
			$this->func = $this->config['ssl'] && function_exists('ftp_ssl_connect') ? 'ftp_ssl_connect' : 'ftp_connect';
			if($this->func == 'ftp_connect' && !function_exists('ftp_connect')) {
				$this->set_error(FTP_ERR_SERVER_DISABLED);
			} else {
				$this->config['host'] = discuz_ftp::clear($this->config['host']);
				$this->config['port'] = intval($this->config['port']);
				$this->config['ssl'] = intval($this->config['ssl']);
				$this->config['username'] = discuz_ftp::clear($this->config['username']);
				$this->config['password'] = authcode($this->config['password'], 'DECODE', md5(getglobal('config/security/authkey')));
				$this->config['timeout'] = intval($this->config['timeout']);
				$this->enabled = true;
			}
		}
	}

	function upload($source, $target) {
		if($this->error()) {
			return 0;
		}
		$old_dir = $this->ftp_pwd();
		$dirname = dirname($target);
		$filename = basename($target);
		if(!$this->ftp_chdir($dirname)) {
			if($this->ftp_mkdir($dirname)) {
				$this->ftp_chmod($dirname);
				if(!$this->ftp_chdir($dirname)) {
					$this->set_error(FTP_ERR_CHDIR);
				}
				$this->ftp_put('index.htm', getglobal('setting/attachdir').'/index.htm', FTP_BINARY);
			} else {
				$this->set_error(FTP_ERR_MKDIR);
			}
		}

		$res = 0;
		if(!$this->error()) {
			if($fp = @fopen($source, 'rb')) {
				$res = $this->ftp_fput($filename, $fp, FTP_BINARY);
				@fclose($fp);
				!$res && $this->set_error(FTP_ERR_TARGET_WRITE);
			} else {
				$this->set_error(FTP_ERR_SOURCE_READ);
			}
		}

		$this->ftp_chdir($old_dir);

		return $res ? 1 : 0;
	}

	function connect() {
		if(!$this->enabled || empty($this->config)) {
			return 0;
		} else {
			return $this->ftp_connect(
				$this->config['host'],
				$this->config['username'],
				$this->config['password'],
				$this->config['attachdir'],
				$this->config['port'],
				$this->config['timeout'],
				$this->config['ssl'],
				$this->config['pasv']
				);
		}

	}

	function ftp_connect($ftphost, $username, $password, $ftppath, $ftpport = 21, $timeout = 30, $ftpssl = 0, $ftppasv = 0) {
		$res = 0;
		$fun = $this->func;
		if($this->connectid = $fun($ftphost, $ftpport, 20)) {

			$timeout && $this->set_option(FTP_TIMEOUT_SEC, $timeout);
			if($this->ftp_login($username, $password)) {
				$this->ftp_pasv($ftppasv);
				if($this->ftp_chdir($ftppath)) {
					$res =  $this->connectid;
				} else {
					$this->set_error(FTP_ERR_CHDIR);
				}
			} else {
				$this->set_error(FTP_ERR_USER_NO_LOGGIN);
			}

		} else {
			$this->set_error(FTP_ERR_CONNECT_TO_SERVER);
		}

		if($res > 0) {
			$this->set_error();
			$this->enabled = 1;
		} else {
			$this->enabled = 0;
			$this->ftp_close();
		}

		return $res;

	}

	function set_error($code = 0) {
		$this->_error = $code;
	}

	function error() {
		return $this->_error;
	}

	function clear($str) {
		return str_replace(array( "\n", "\r", '..'), '', $str);
	}


	function set_option($cmd, $value) {
		if(function_exists('ftp_set_option')) {
			return @ftp_set_option($this->connectid, $cmd, $value);
		}
	}

	function ftp_mkdir($directory) {
		$directory = discuz_ftp::clear($directory);
		$epath = explode('/', $directory);
		$dir = '';$comma = '';
		foreach($epath as $path) {
			$dir .= $comma.$path;
			$comma = '/';
			$return = @ftp_mkdir($this->connectid, $dir);
			$this->ftp_chmod($dir);
		}
		return $return;
	}

	function ftp_rmdir($directory) {
		$directory = discuz_ftp::clear($directory);
		return @ftp_rmdir($this->connectid, $directory);
	}

	function ftp_put($remote_file, $local_file, $mode = FTP_BINARY) {
		$remote_file = discuz_ftp::clear($remote_file);
		$local_file = discuz_ftp::clear($local_file);
		$mode = intval($mode);
		return @ftp_put($this->connectid, $remote_file, $local_file, $mode);
	}

	function ftp_fput($remote_file, $sourcefp, $mode = FTP_BINARY) {
		$remote_file = discuz_ftp::clear($remote_file);
		$mode = intval($mode);
		return @ftp_fput($this->connectid, $remote_file, $sourcefp, $mode);
	}

	function ftp_size($remote_file) {
		$remote_file = discuz_ftp::clear($remote_file);
		return @ftp_size($this->connectid, $remote_file);
	}

	function ftp_close() {
		return @ftp_close($this->connectid);
	}

	function ftp_delete($path) {
		$path = discuz_ftp::clear($path);
		return @ftp_delete($this->connectid, $path);
	}

	function ftp_get($local_file, $remote_file, $mode, $resumepos = 0) {
		$remote_file = discuz_ftp::clear($remote_file);
		$local_file = discuz_ftp::clear($local_file);
		$mode = intval($mode);
		$resumepos = intval($resumepos);
		return @ftp_get($this->connectid, $local_file, $remote_file, $mode, $resumepos);
	}

	function ftp_login($username, $password) {
		$username = $this->clear($username);
		$password = str_replace(array("\n", "\r"), array('', ''), $password);
		return @ftp_login($this->connectid, $username, $password);
	}

	function ftp_pasv($pasv) {
		return @ftp_pasv($this->connectid, $pasv ? true : false);
	}

	function ftp_chdir($directory) {
		$directory = discuz_ftp::clear($directory);
		return @ftp_chdir($this->connectid, $directory);
	}

	function ftp_site($cmd) {
		$cmd = discuz_ftp::clear($cmd);
		return @ftp_site($this->connectid, $cmd);
	}

	function ftp_chmod($filename, $mod = 0777) {
		$filename = discuz_ftp::clear($filename);
		if(function_exists('ftp_chmod')) {
			return @ftp_chmod($this->connectid, $mod, $filename);
		} else {
			return @ftp_site($this->connectid, 'CHMOD '.$mod.' '.$filename);
		}
	}

	function ftp_pwd() {
		return @ftp_pwd($this->connectid);
	}

}