<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_security.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_security extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_member_security';
		$this->_pk    = 'securityid';

		parent::__construct();
	}

}

?>