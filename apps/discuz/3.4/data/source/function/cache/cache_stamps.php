<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_stamps.php 25773 2011-11-22 04:22:39Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_stamps() {
	$data = array();

	$fillarray = range(0, 99);
	$count = 0;
	$repeats = $stampicon = array();
	foreach(C::t('common_smiley')->fetch_all_by_type(array('stamp','stamplist')) as $stamp) {
		if(isset($fillarray[$stamp['displayorder']])) {
			unset($fillarray[$stamp['displayorder']]);
		} else {
			$repeats[] = $stamp['id'];
		}
		$count++;
	}
	foreach($repeats as $id) {
		reset($fillarray);
		$displayorder = current($fillarray);
		unset($fillarray[$displayorder]);
		C::t('common_smiley')->update($id, array('displayorder'=>$displayorder));
	}
	foreach(C::t('common_smiley')->fetch_all_by_type('stamplist') as $stamp) {
		if($stamp['typeid'] < 1) {
			continue;
		}
		$row = C::t('common_smiley')->fetch_by_id_type($stamp['typeid'], 'stamp');
		$stampicon[$row['displayorder']] = $stamp['displayorder'];
	}
	foreach(C::t('common_smiley')->fetch_all_by_type(array('stamp','stamplist')) as $stamp) {
		$icon = $stamp['type'] == 'stamp' ? (isset($stampicon[$stamp['displayorder']]) ? $stampicon[$stamp['displayorder']] : 0) :
			($stamp['type'] == 'stamplist' && !in_array($stamp['displayorder'], $stampicon) ? 1 : 0);
		$data[$stamp['displayorder']] = array('url' => $stamp['url'], 'text' => $stamp['code'], 'type' => $stamp['type'], 'icon' => $icon);
	}

	savecache('stamps', $data);
}

?>