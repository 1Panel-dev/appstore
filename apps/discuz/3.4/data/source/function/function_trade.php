<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_trade.php 24961 2011-10-19 06:48:00Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$apitype = empty($apitype) || !preg_match('/^[a-z0-9]+$/i', $apitype) ? 'alipay' : $apitype;

require_once DISCUZ_ROOT.'./api/trade/api_'.$apitype.'.php';

function trade_offline($tradelog, $returndlang = 1, &$trade_message = '') {
	global $_G;
	$tmp = $return = array();
	if($_G['uid'] == $tradelog['buyerid']) {
		$data = array(
			0 => array(4,8),
			1 => array(4,8),
			5 => array(7,10),
			11 => array(10,7),
			12 => array(13)
		);
		$tmp = $data[$tradelog['status']];
	} elseif($_G['uid'] == $tradelog['sellerid']) {
		$data = array(
			4 => array(5),
			10 => array(12,11),
			13 => array(17)
		);
		$tmp = $data[$tradelog['status']];
	}
	if($returndlang) {
		$language = lang('forum/misc');
		for($i = 0, $count = count($tmp);$i < $count;$i++) {
			$return[$tmp[$i]] = lang('forum/misc', 'trade_offline_'.$tmp[$i]);
			$trade_message .= isset($language['trade_message_'.$tmp[$i]]) ? lang('forum/misc', 'trade_message_'.$tmp[$i]).'<br />' : '';
		}
		return $return;
	} else {
		return $tmp;
	}
}

function trade_create($trade) {
	global $_G;
	extract($trade);
	$special = 2;

	$expiration = $item_expiration ? strtotime($item_expiration) : 0;
	$closed = $expiration > 0 && strtotime($item_expiration) < TIMESTAMP ? 1 : $closed;
	$item_price = floatval($item_price);

	switch($transport) {
		case 'offline'	: $item_transport = 0; break;
		case 'seller'	: $item_transport = 1; break;
		case 'buyer'	: $item_transport = 2; break;
		case 'virtual'	: $item_transport = 3; break;
		case 'logistics': $item_transport = 4; break;
	}

	$seller = dhtmlspecialchars($seller);
	$item_name = dhtmlspecialchars($item_name);
	$item_locus = dhtmlspecialchars($item_locus);
	$item_number = intval($item_number);
	$item_quality = intval($item_quality);
	$item_transport = intval($item_transport);
	$postage_mail = intval($postage_mail);
	$postage_express = intval($postage_express);
	$postage_ems = intval($postage_ems);
	$item_type = intval($item_type);
	$typeid = intval($typeid);
	$item_costprice = floatval($item_costprice);
	if(!$item_price || $item_price <= 0) {
		$item_price = $postage_mail = $postage_express = $postage_ems = '';
	}

	if(empty($pid)) {
		$pid = C::t('forum_post')->fetch_threadpost_by_tid_invisible($tid);
		$pid = $pid['pid'];
	}
	if(!$item_price && $item_credit) {
		$seller = '';
	}
	C::t('forum_trade')->insert(array(
		'tid' => $tid,
		'pid' => $pid,
		'typeid' => $typeid,
		'sellerid' => $_G['uid'],
		'seller' => $author,
		'tenpayaccount' => $tenpayaccount,
		'account' => $seller,
		'subject' => $item_name,
		'price' => $item_price,
		'amount' => $item_number,
		'quality' => $item_quality,
		'locus' => $item_locus,
		'transport' => $item_transport,
		'ordinaryfee' => $postage_mail,
		'expressfee' => $postage_express,
		'emsfee' => $postage_ems,
		'itemtype' => $item_type,
		'dateline' => $_G['timestamp'],
		'expiration' => $expiration,
		'lastupdate' => $_G['timestamp'],
		'totalitems' => '0',
		'tradesum' => '0',
		'closed' => $closed,
		'costprice'=>$item_costprice,
		'aid'=>$aid,'credit'=>$item_credit,
		'costcredit'=>$item_costcredit
	));
}

?>