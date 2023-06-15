<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_profilesetting.php 24935 2011-10-17 07:41:48Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_profilesetting() {
	$data = C::t('common_member_profile_setting')->fetch_all_by_available(1);

	savecache('profilesetting', $data);
}

?>