<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_threadexpiry_hourly.php 33625 2013-07-19 06:03:49Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

C::t('common_seccheck')->delete_expiration();

$actionarray = array();
foreach(C::t('forum_threadmod')->fetch_all_by_expiration_status($_G['timestamp']) as $expiry) {
	switch($expiry['action']) {
		case 'EST':	$actionarray['UES'][] = $expiry['tid']; break;
		case 'EHL':	$actionarray['UEH'][] = $expiry['tid'];	break;
		case 'ECL':	$actionarray['UEC'][] = $expiry['tid'];	break;
		case 'EOP':	$actionarray['UEO'][] = $expiry['tid'];	break;
		case 'EDI':	$actionarray['UED'][] = $expiry['tid'];	break;
		case 'TOK':	$actionarray['UES'][] = $expiry['tid']; break;
		case 'CCK':	$actionarray['UEH'][] = $expiry['tid'];	break;
		case 'CLK':	$actionarray['UEC'][] = $expiry['tid']; break;
		case 'SPA':	$actionarray['SPD'][] = $expiry['tid']; break;
	}
}

if($actionarray) {

	foreach($actionarray as $action => $tids) {


		switch($action) {

			case 'UES':
				C::t('forum_thread')->update($actionarray[$action], array('displayorder'=>0), true);
				C::t('forum_threadmod')->update_by_tid_action($tids, array('EST', 'TOK'), array('status'=>0));
				require_once libfile('function/cache');
				updatecache('globalstick');
				break;

			case 'UEH':
				C::t('forum_thread')->update($actionarray[$action], array('highlight'=>0), true);
				C::t('forum_threadmod')->update_by_tid_action($tids, array('EHL', 'CCK'), array('status'=>0));
				break;

			case 'UEC':
			case 'UEO':
				$closed = $action == 'UEO' ? 1 : 0;
				C::t('forum_thread')->update($actionarray[$action], array('closed'=>$closed), true);
				C::t('forum_threadmod')->update_by_tid_action($tids, array('EOP', 'ECL', 'CLK'), array('status'=>0));
				break;

			case 'UED':
				C::t('forum_threadmod')->update_by_tid_action($tids, array('EDI'), array('status'=>0));
				$digestarray = $authoridarry = array();
				foreach(C::t('forum_thread')->fetch_all_by_tid($actionarray[$action]) as $digest) {
					$authoridarry[] = $digest['authorid'];
					$digestarray[$digest['digest']][] = $digest['authorid'];
				}
				foreach($digestarray as $digest => $authorids) {
					batchupdatecredit('digest', $authorids, array("digestposts=digestposts+'-1'"), -$digest, $fid = 0);
				}
				C::t('forum_thread')->update($actionarray[$action], array('digest'=>0), true);
				break;

			case 'SPD':
				C::t('forum_thread')->update($actionarray[$action], array('stamp'=>-1), true);
				C::t('forum_threadmod')->update_by_tid_action($tids, array('SPA'), array('status'=>0));
				break;

		}
	}

	require_once libfile('function/post');

	foreach($actionarray as $action => $tids) {
		updatemodlog(implode(',', $tids), $action, 0, 1);
	}

}

?>