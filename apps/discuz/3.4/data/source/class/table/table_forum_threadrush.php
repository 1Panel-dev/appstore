<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_threadrush.php 36296 2016-12-14 07:48:12Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_threadrush extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_threadrush';
		$this->_pk    = 'tid';
		$this->_pre_cache_key = 'forum_threadrush_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}

}

?>