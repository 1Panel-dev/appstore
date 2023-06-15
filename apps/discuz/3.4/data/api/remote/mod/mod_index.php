<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mod_index.php 30462 2012-05-30 03:27:10Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class mod_index extends remote_service {

	var $config;
	function mod_index() {
		parent::remote_service();
	}

	function run() {
		$this->success('Discuz! Remote Service API '.$this->version);
	}
}