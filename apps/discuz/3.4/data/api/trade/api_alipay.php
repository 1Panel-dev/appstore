<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: api_alipay.php 31606 2012-09-13 07:26:35Z monkey $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
list($ec_contract, $ec_securitycode, $ec_partner, $ec_creditdirectpay) = explode("\t", authcode($_G['setting']['ec_contract'], 'DECODE', $_G['config']['security']['authkey']));

define('DISCUZ_PARTNER', $ec_partner);
define('DISCUZ_SECURITYCODE', $ec_securitycode);
define('DISCUZ_DIRECTPAY', $ec_creditdirectpay);

define('STATUS_SELLER_SEND', 4);
define('STATUS_WAIT_BUYER', 5);
define('STATUS_TRADE_SUCCESS', 7);
define('STATUS_REFUND_CLOSE', 17);

function credit_payurl($price, &$orderid) {
	global $_G;

	$orderid = dgmdate(TIMESTAMP, 'YmdHis').random(18);

	$args = array(
		'subject' 		=> $_G['setting']['bbname'].' - '.$_G['member']['username'].' - '.lang('forum/misc', 'credit_payment'),
		'body' 			=> lang('forum/misc', 'credit_forum_payment').' '.$_G['setting']['extcredits'][$_G['setting']['creditstrans']]['title'].' '.intval($price * $_G['setting']['ec_ratio']).' '.$_G['setting']['extcredits'][$_G['setting']['creditstrans']]['unit'],
		'service' 		=> 'trade_create_by_buyer',
		'partner' 		=> DISCUZ_PARTNER,
		'notify_url' 		=> $_G['siteurl'].'api/trade/notify_credit.php',
		'return_url' 		=> $_G['siteurl'].'api/trade/notify_credit.php',
		'show_url'		=> $_G['siteurl'],
		'_input_charset' 	=> CHARSET,
		'out_trade_no' 		=> $orderid,
		'price' 		=> $price,
		'quantity' 		=> 1,
		'seller_email' 		=> $_G['setting']['ec_account'],
		'extend_param'	=> 'isv^dz11'
	);
	if(DISCUZ_DIRECTPAY) {
		$args['service'] = 'create_direct_pay_by_user';
		$args['payment_type'] = '1';
	} else {
		$args['logistics_type'] = 'EXPRESS';
		$args['logistics_fee'] = 0;
		$args['logistics_payment'] = 'SELLER_PAY';
		$args['payment_type'] = 1;
	}
	return trade_returnurl($args);
}

function invite_payurl($amount, $price, &$orderid) {
	global $_G;

	$orderid = dgmdate(TIMESTAMP, 'YmdHis').random(18);

	$args = array(
		'subject' 		=> $_G['setting']['bbname'].' - '.lang('forum/misc', 'invite_payment'),
		'body' 			=> lang('forum/misc', 'invite_forum_payment').' '.intval($amount).' '.lang('forum/misc', 'invite_forum_payment_unit'),
		'service' 		=> 'trade_create_by_buyer',
		'partner' 		=> DISCUZ_PARTNER,
		'notify_url' 		=> $_G['siteurl'].'api/trade/notify_invite.php',
		'return_url' 		=> $_G['siteurl'].'api/trade/notify_invite.php',
		'show_url'		=> $_G['siteurl'],
		'_input_charset' 	=> CHARSET,
		'out_trade_no' 		=> $orderid,
		'price' 		=> $price,
		'quantity' 		=> 1,
		'seller_email' 		=> $_G['setting']['ec_account'],
		'extend_param'	=> 'isv^dz11'
	);
	if(DISCUZ_DIRECTPAY) {
		$args['service'] = 'create_direct_pay_by_user';
		$args['payment_type'] = '1';
	} else {
		$args['logistics_type'] = 'EXPRESS';
		$args['logistics_fee'] = 0;
		$args['logistics_payment'] = 'SELLER_PAY';
		$args['payment_type'] = 1;
	}
	return trade_returnurl($args);
}

function trade_payurl($pay, $trade, $tradelog) {
	global $_G;

	$args = array(
		'service' 		=> 'trade_create_by_buyer',
		'partner' 		=> DISCUZ_PARTNER,
		'notify_url' 		=> $_G['siteurl'].'api/trade/notify_trade.php',
		'return_url' 		=> $_G['siteurl'].'api/trade/notify_trade.php',
		'show_url'		=> $tradelog['tid'] ? $_G['siteurl'].'forum.php?mod=viewthread&do=tradeinfo&tid='.$tradelog['tid'].'&pid='.$tradelog['pid'] : $_G['siteurl'],
		'_input_charset' 	=> CHARSET,
		'subject' 		=> $trade['subject'],
		'body' 			=> $trade['subject'],
		'out_trade_no' 		=> $tradelog['orderid'],
		'price' 		=> $tradelog['baseprice'],
		'quantity' 		=> $tradelog['number'],
		'logistics_type' 	=> $pay['logistics_type'],
		'logistics_fee' 	=> $tradelog['transportfee'],
		'logistics_payment' 	=> $pay['transport'],
		'payment_type' 		=> $trade['itemtype'],
		'seller_email' 		=> $trade['account'],
		'extend_param'	=> 'isv^dz11'
	);

	if($pay['logistics_type'] == 'VIRTUAL') {
		if(DISCUZ_DIRECTPAY) {
			$args['service'] = 'create_direct_pay_by_user';
			$args['payment_type'] = '1';
			unset($args['logistics_type'], $args['logistics_fee'], $args['logistics_payment']);
		} else {
			$args['logistics_type'] = 'EXPRESS';
			$args['logistics_payment'] = 'SELLER_PAY';
			$args['payment_type'] = '1';
		}
	}
	return trade_returnurl($args);
}

function trade_returnurl($args) {
	global $_G;
	ksort($args);
	$urlstr = $sign = '';
	foreach($args as $key => $val) {
		$sign .= '&'.$key.'='.$val;
		$urlstr .= $key.'='.rawurlencode($val).'&';
	}
	$sign = substr($sign, 1);
	$sign = md5($sign.DISCUZ_SECURITYCODE);
	return 'https://www.alipay.com/cooperate/gateway.do?'.$urlstr.'sign='.$sign.'&sign_type=MD5';
}

function trade_notifycheck($type) {
	global $_G;
	if(!empty($_POST)) {
		$notify = $_POST;
		$location = FALSE;
	} elseif(!empty($_GET)) {
		$notify = $_GET;
		$location = TRUE;
	} else {
		exit('Access Denied');
	}
	unset($notify['diy']);
	if(dfsockopen("https://notify.alipay.com/trade/notify_query.do?partner=".DISCUZ_PARTNER."&notify_id=".$notify['notify_id'], 60) !== 'true') {
		exit('Access Denied');
	}

	if($type == 'trade') {
		$urlstr = '';
		foreach($notify as $key => $val) {
			$urlstr .= $key.'='.rawurlencode($val).'&';
		}
	} else {
		if(!DISCUZ_SECURITYCODE) {
			exit('Access Denied');
		}
		ksort($notify);
		$sign = '';
		foreach($notify as $key => $val) {
			if($key != 'sign' && $key != 'sign_type') $sign .= "&$key=$val";
		}
		if($notify['sign'] != md5(substr($sign,1).DISCUZ_SECURITYCODE)) {
			exit('Access Denied');
		}
	}

	if(($type == 'credit' || $type == 'invite') && (!DISCUZ_DIRECTPAY && $notify['notify_type'] == 'trade_status_sync' && ($notify['trade_status'] == 'WAIT_SELLER_SEND_GOODS' || $notify['trade_status'] == 'TRADE_FINISHED') || DISCUZ_DIRECTPAY && ($notify['trade_status'] == 'TRADE_FINISHED' || $notify['trade_status'] == 'TRADE_SUCCESS'))
		|| $type == 'trade' && $notify['notify_type'] == 'trade_status_sync') {
		return array(
			'validator'	=> TRUE,
			'status' 	=> trade_getstatus(!empty($notify['refund_status']) ? $notify['refund_status'] : $notify['trade_status'], 1),
			'order_no' 	=> $notify['out_trade_no'],
			'price' 	=> !DISCUZ_DIRECTPAY && $notify['price'] ? $notify['price'] : $notify['total_fee'],
			'trade_no'	=> $notify['trade_no'],
			'notify'	=> 'success',
			'location'	=> $location
			);
	} else {
		return array(
			'validator'	=> FALSE,
			'notify'	=> 'fail',
			'location'	=> $location
			);
	}
}

function trade_getorderurl($orderid) {
	return 'https://www.alipay.com/trade/query_trade_detail.htm?trade_no='.$orderid;
}

function trade_typestatus($method, $status = -1) {
	switch($method) {
		case 'buytrades'	: $methodvalue = array(1, 5, 11, 12);break;
		case 'selltrades'	: $methodvalue = array(2, 4, 10, 13);break;
		case 'successtrades'	: $methodvalue = array(7);break;
		case 'tradingtrades'	: $methodvalue = array(1, 2, 3, 4, 5, 6, 10, 11, 12, 13, 14, 15, 16);break;
		case 'closedtrades'	: $methodvalue = array(8, 17);break;
		case 'refundsuccess'	: $methodvalue = array(17);break;
		case 'refundtrades'	: $methodvalue = array(14, 15, 16, 17, 18);break;
		case 'unstarttrades'	: $methodvalue = array(0);break;
		case 'eccredittrades'	: $methodvalue = array(7, 17);break;
	}
	return $status != -1 ? in_array($status, $methodvalue) : $methodvalue;
}

function trade_getstatus($key, $method = 2) {
	$language = lang('forum/misc');
	$status[1] = array(
		'WAIT_BUYER_PAY' => 1,
		'WAIT_SELLER_CONFIRM_TRADE' => 2,
		'WAIT_SYS_CONFIRM_PAY' => 3,
		'WAIT_SELLER_SEND_GOODS' => 4,
		'WAIT_BUYER_CONFIRM_GOODS' => 5,
		'WAIT_SYS_PAY_SELLER' => 6,
		'TRADE_FINISHED' => 7,
		'TRADE_CLOSED' => 8,
		'WAIT_SELLER_AGREE' => 10,
		'SELLER_REFUSE_BUYER' => 11,
		'WAIT_BUYER_RETURN_GOODS' => 12,
		'WAIT_SELLER_CONFIRM_GOODS' => 13,
		'WAIT_ALIPAY_REFUND' => 14,
		'ALIPAY_CHECK' => 15,
		'OVERED_REFUND' => 16,
		'REFUND_SUCCESS' => 17,
		'REFUND_CLOSED' => 18
	);
	$status[2] = array(
		0  => $language['trade_unstart'],
		1  => $language['trade_waitbuyerpay'],
		2  => $language['trade_waitsellerconfirm'],
		3  => $language['trade_sysconfirmpay'],
		4  => $language['trade_waitsellersend'],
		5  => $language['trade_waitbuyerconfirm'],
		6  => $language['trade_syspayseller'],
		7  => $language['trade_finished'],
		8  => $language['trade_closed'],
		10 => $language['trade_waitselleragree'],
		11 => $language['trade_sellerrefusebuyer'],
		12 => $language['trade_waitbuyerreturn'],
		13 => $language['trade_waitsellerconfirmgoods'],
		14 => $language['trade_waitalipayrefund'],
		15 => $language['trade_alipaycheck'],
		16 => $language['trade_overedrefund'],
		17 => $language['trade_refundsuccess'],
		18 => $language['trade_refundclosed']
	);
	return $method == -1 ? $status[2] : $status[$method][$key];
}

function trade_setprice($data, &$price, &$pay, &$transportfee) {
	if($data['transport'] == 1) {
		$pay['transport'] = 'SELLER_PAY';
	} elseif($data['transport'] == 2) {
		$pay['transport'] = 'BUYER_PAY';
	} elseif($data['transport'] == 3) {
		$pay['logistics_type'] = 'VIRTUAL';
	} else {
		$pay['transport'] = 'BUYER_PAY_AFTER_RECEIVE';
	}

	if($data['transport'] != 3) {
		if($data['fee'] == 1) {
			$pay['logistics_type'] = 'POST';
			$pay['logistics_fee'] = $data['trade']['ordinaryfee'];
			if($data['transport'] == 2) {
				$price = $price + $data['trade']['ordinaryfee'];
				$transportfee = $data['trade']['ordinaryfee'];
			}
		} elseif($data['fee'] == 2) {
			$pay['logistics_type'] = 'EMS';
			$pay['logistics_fee'] = $data['trade']['emsfee'];
			if($data['transport'] == 2) {
				$price = $price + $data['trade']['emsfee'];
				$transportfee = $data['trade']['emsfee'];
			}
		} else {
			$pay['logistics_type'] = 'EXPRESS';
			$pay['logistics_fee'] = $data['trade']['expressfee'];
			if($data['transport'] == 2) {
				$price = $price + $data['trade']['expressfee'];
				$transportfee = $data['trade']['expressfee'];
			}
		}
	}
}

?>