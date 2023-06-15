<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_admingroups.php 24830 2011-10-12 08:23:34Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_admingroups() {
	foreach(C::t('common_admingroup')->range() as $data) {
		savecache('admingroup_'.$data['admingid'], $data);
	}
}

?>