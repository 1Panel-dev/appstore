<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_credit_log_field.php 31380 2012-08-21 07:25:54Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_credit_log_field extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_credit_log_field';
		$this->_pk    = '';

		parent::__construct();
	}
}

?>