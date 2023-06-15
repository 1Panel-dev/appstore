<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_trade.php 28348 2012-02-28 06:16:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_GET['do']) || $_GET['do'] == 'tradeinfo') {

	if($_GET['do'] == 'tradeinfo') {
		$_GET['pid'] = intval($_GET['pid']);
	} else {
		$_GET['pid'] = '';
		!$tradenum && $allowpostreply = FALSE;
	}

	$query = C::t('forum_trade')->fetch_all_thread_goods($_G['tid'], $_GET['pid']);
	$trades = $tradesstick = array();$tradelist = 0;
	if(empty($_GET['do'])) {
		$sellerid = 0;
		$listcount = count($query);
		$tradelist = $tradenum - $listcount;
	}

	$tradesaids = $tradespids = array();
	foreach($query as $trade) {
		if($trade['expiration']) {
			$trade['expiration'] = ($trade['expiration'] - TIMESTAMP) / 86400;
			if($trade['expiration'] > 0) {
				$trade['expirationhour'] = floor(($trade['expiration'] - floor($trade['expiration'])) * 24);
				$trade['expiration'] = floor($trade['expiration']);
			} else {
				$trade['expiration'] = -1;
			}
		}
		$tradesaids[] = $trade['aid'];
		$tradespids[] = $trade['pid'];
		if($trade['displayorder'] < 0) {
			$trades[$trade['pid']] = $trade;
		} else {
			$tradesstick[$trade['pid']] = $trade;
		}
	}
	if(empty($_GET['do'])) {
		$tradepostlist = C::t('forum_post')->fetch_all('tid:'.$_G['tid'], $tradespids);
	}
	$trades = $tradesstick + $trades;
	unset($trade);

	if($tradespids) {
		foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$_G['tid'], 'pid', $tradespids) as $attach) {
			if($attach['isimage'] && is_array($tradesaids) && in_array($attach['aid'], $tradesaids)) {
				$trades[$attach['pid']]['attachurl'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
				$trades[$attach['pid']]['thumb'] = $attach['thumb'] ? getimgthumbname($trades[$attach['pid']]['attachurl']) : $trades[$attach['pid']]['attachurl'];
				$trades[$attach['pid']]['width'] = $attach['thumb'] && $_G['setting']['thumbwidth'] < $attach['width'] ? $_G['setting']['thumbwidth'] : $attach['width'];
			}
		}
	}

	if($_GET['do'] == 'tradeinfo') {
		$trade = $trades[$_GET['pid']];
		unset($trades);
		$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['pid']);
		if($post) {
			$post = array_merge(C::t('common_member_status')->fetch($post['authorid']), C::t('common_member_profile')->fetch($post['authorid']),
				$post, getuserbyuid($post['authorid']));
			if($_G['setting']['verify']['enabled']) {
				$post = array_merge($post, C::t('common_member_verify')->fetch($post['authorid']));
			}
		}

		$postlist[$post['pid']] = viewthread_procpost($post, $lastvisit, $ordertype);

		$usertrades = $userthreads = array();
		if(!$_G['inajax']) {
			$limit = 6;
			$query = C::t('forum_trade')->fetch_all_for_seller($_G['forum_thread']['authorid'], $limit + 1, $_G['tid']);
			$usertradecount = 0;
			foreach($query as $usertrade) {
				if($usertrade['pid'] == $post['pid']) {
					continue;
				}
				$usertradecount++;
				$usertrades[] = $usertrade;
				if($usertradecount == $limit) {
					break;
				}
			}

		}

		if($_G['forum_attachpids'] && !defined('IN_ARCHIVER')) {
			require_once libfile('function/attachment');
			parseattach($_G['forum_attachpids'], $_G['forum_attachtags'], $postlist, array($trade['aid']));
		}

		$post = $postlist[$_GET['pid']];

		$post['buyerrank'] = 0;
		if($post['buyercredit']){
			foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
				if($post['buyercredit'] <= $credit) {
					$post['buyerrank'] = $level;
					break;
				}
			}
		}
		$post['sellerrank'] = 0;
		if($post['sellercredit']){
			foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
				if($post['sellercredit'] <= $credit) {
					$post['sellerrank'] = $level;
					break;
				}
			}
		}

		$navtitle = $trade['subject'];

		if($post['authorid']) {
			$online = $sessioninfo = C::app()->session->fetch_by_uid($post['authorid']) && empty($sessioninfo['invisible']) ? 1 : 0;
		}

		include template('forum/trade_info');
		exit;

	}
}

?>