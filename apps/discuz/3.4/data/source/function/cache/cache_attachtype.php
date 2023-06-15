<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_attachtype.php 28372 2012-02-28 08:15:06Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_attachtype() {
	$attachtypes = C::t('forum_attachtype')->fetch_all_data();
	$data = array();
	foreach($attachtypes as $row) {
		$data[$row['fid']][strtolower($row['extension'])] = $row['maxsize'];
	}

	savecache('attachtype', $data);
}

?>