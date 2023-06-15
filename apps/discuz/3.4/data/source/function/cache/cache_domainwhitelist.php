<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_domainwhitelist.php 24152 2011-08-26 10:04:08Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_domainwhitelist() {

	$data = C::t('common_setting')->fetch('domainwhitelist');
	$data = $data ? explode("\r\n", $data) : array();
	savecache('domainwhitelist', $data);
}

?>