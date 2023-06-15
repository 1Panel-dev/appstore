<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_threadclosed.php 27449 2012-02-15 05:32:35Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class table_forum_threadclosed extends discuz_table {

	public function __construct() {
		$this->_table = 'forum_threadclosed';
		$this->_pk    = 'tid';
		parent::__construct();
	}
}

?>