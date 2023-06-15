<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_dbexception.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class DbException extends Exception{

	public $sql;

	public function __construct($message, $code = 0, $sql = '') {
		$this->sql = $sql;
		parent::__construct($message, $code);
	}

	public function getSql() {
		return $this->sql;
	}
}
?>