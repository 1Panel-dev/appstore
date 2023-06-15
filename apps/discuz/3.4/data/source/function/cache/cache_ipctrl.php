<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_ipctrl.php 24152 2011-08-26 10:04:08Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_ipctrl() {
	$data = C::t('common_setting')->fetch_all(array('ipregctrl', 'ipverifywhite'));
	savecache('ipctrl', $data);
}

?>