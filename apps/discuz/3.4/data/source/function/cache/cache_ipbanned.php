<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_ipbanned.php 24468 2011-09-20 11:41:28Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_ipbanned() {
	C::t('common_banned')->delete_by_expiration(TIMESTAMP);
	$data = array();
	$bannedarr = C::t('common_banned')->fetch_all();
	if(!empty($bannedarr)) {
		$data['expiration'] = 0;
		$data['regexp'] = $separator = '';
	}
	foreach($bannedarr as $banned) {
		$data['expiration'] = !$data['expiration'] || $banned['expiration'] < $data['expiration'] ? $banned['expiration'] : $data['expiration'];
		$data['regexp'] .= $separator.
			($banned['ip1'] == '-1' ? '\\d+\\.' : $banned['ip1'].'\\.').
			($banned['ip2'] == '-1' ? '\\d+\\.' : $banned['ip2'].'\\.').
			($banned['ip3'] == '-1' ? '\\d+\\.' : $banned['ip3'].'\\.').
			($banned['ip4'] == '-1' ? '\\d+' : $banned['ip4']);
		$separator = '|';
	}

	savecache('ipbanned', $data);
}

?>