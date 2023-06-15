<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_attachment_exif.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_attachment_exif extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_attachment_exif';
		$this->_pk    = 'aid';

		parent::__construct();
	}

	public function insert($aid, $exif) {
		DB::insert($this->_table, array('aid' => $aid, 'exif' => $exif), false, true, true);
	}

}

?>