<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_magic_daily.php 24589 2011-09-27 07:45:55Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!empty($_G['setting']['magicstatus'])) {
	$magicarray = array();
	foreach(C::t('common_magic')->fetch_all_data(1) as $magic) {
		if($magic['supplytype'] && $magic['supplynum']) {
			$magicarray[$magic['magicid']]['supplytype'] = $magic['supplytype'];
			$magicarray[$magic['magicid']]['supplynum'] = $magic['supplynum'];
		}
	}

	list($daynow, $weekdaynow) = explode('-', dgmdate(TIMESTAMP, 'd-w', $_G['setting']['timeoffset']));

	foreach($magicarray as $id => $magic) {
		$autosupply = 0;
		if($magic['supplytype'] == 1) {
			$autosupply = 1;
		} elseif($magic['supplytype'] == 2 && $weekdaynow == 1) {
			$autosupply = 1;
		} elseif($magic['supplytype'] == 3 && $daynow == 1) {
			$autosupply = 1;
		}

		if(!empty($autosupply)) {
			C::t('common_magic')->update($id, array('num' => $magic['supplynum']));
		}
	}
}

?>