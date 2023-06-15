<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_heats.php 27425 2012-01-31 06:53:25Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_heats() {

	global $_G;
	$addsql = '';
	$data = array();
	if(discuz_process::islocked('update_heats_list')) {
		return false;
	}
	if($_G['setting']['indexhot']['status']) {
		require_once libfile('function/post');
		$_G['setting']['indexhot'] = array(
			'status' => 1,
			'limit' => intval($_G['setting']['indexhot']['limit'] ? $_G['setting']['indexhot']['limit'] : 10),
			'days' => intval($_G['setting']['indexhot']['days'] ? $_G['setting']['indexhot']['days'] : 7),
			'expiration' => intval($_G['setting']['indexhot']['expiration'] ? $_G['setting']['indexhot']['expiration'] : 900),
			'messagecut' => intval($_G['setting']['indexhot']['messagecut'] ? $_G['setting']['indexhot']['messagecut'] : 200)
		);


		$messageitems = 2;
		$limit = $_G['setting']['indexhot']['limit'];
		foreach(C::t('forum_thread')->fetch_all_heats() as $heat) {
			$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($heat['tid']);
			$heat = array_merge($heat, (array)$post);
			if($limit == 0) {
				break;
			}
			if($messageitems > 0) {
				$heat['message'] = !$heat['price'] ? messagecutstr($heat['message'], $_G['setting']['indexhot']['messagecut']) : '';
				$data['message'][$heat['tid']] = $heat;
			} else {
				unset($heat['message']);
				$data['subject'][$heat['tid']] = $heat;
			}
			$messageitems--;
			$limit--;
		}
		$data['expiration'] = TIMESTAMP + $_G['setting']['indexhot']['expiration'];
	}

	savecache('heats', $data);
	discuz_process::unlock('update_heats_list');
}

?>