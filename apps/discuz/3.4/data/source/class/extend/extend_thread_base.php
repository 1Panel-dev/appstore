<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_base.php 30673 2012-06-11 07:51:54Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_base extends discuz_extend {

	public $forum;
	public $thread;
	public $post;
	public $feed;

	public function init_base_var() {
		$this->forum = &$this->_obj->forum;
		$this->thread = &$this->_obj->thread;
		$this->post = &$this->_obj->post;
		$this->feed = &$this->_obj->feed;
		parent::init_base_var();
	}

}

?>