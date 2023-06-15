<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: api_tenpay.php 32222 2012-12-03 02:28:43Z monkey $
 */


define('IN_API', true);
define('CURSCRIPT', 'api');

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('DISCUZ_PARTNER', $_G['setting']['ec_tenpay_bargainor']);
define('DISCUZ_SECURITYCODE', $_G['setting']['ec_tenpay_key']);
define('DISCUZ_AGENTID', '1204737401');

define('DISCUZ_TENPAY_OPENTRANS_CHNID', $_G['setting']['ec_tenpay_opentrans_chnid']);
define('DISCUZ_TENPAY_OPENTRANS_KEY', $_G['setting']['ec_tenpay_opentrans_key']);

define('STATUS_SELLER_SEND', 3);
define('STATUS_WAIT_BUYER', 4);
define('STATUS_TRADE_SUCCESS', 5);
define('STATUS_REFUND_CLOSE', 9);

class RequestHandler {

	var $gateUrl;

	var $key;

	var $parameters;

	var $debugInfo;

	function __construct() {
		$this->RequestHandler();
	}

	function RequestHandler() {
		$this->gateUrl = "https://www.tenpay.com/cgi-bin/med/show_opentrans.cgi";
		$this->key = "";
		$this->parameters = array();
		$this->debugInfo = "";
	}

	function init() {
	}

	function getGateURL() {
		return $this->gateUrl;
	}

	function setGateURL($gateUrl) {
		$this->gateUrl = $gateUrl;
	}

	function getKey() {
		return $this->key;
	}

	function setKey($key) {
		$this->key = $key;
	}

	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}

	function setParameter($parameter, $parameterValue) {
		$this->parameters[$parameter] = $parameterValue;
	}

	function getAllParameters() {
		$this->createSign();

		return $this->parameters;
	}

	function getRequestURL() {
		$this->createSign();
		$reqPar = "";
		ksort($this->parameters);
		foreach($this->parameters as $k => $v) {
			$reqPar .= $k . "=" . urlencode($v) . "&";
		}

		$reqPar = substr($reqPar, 0, strlen($reqPar)-1);
		$requestURL = $this->getGateURL() . "?" . $reqPar;
		return $requestURL;

	}

	function getDebugInfo() {
		return $this->debugInfo;
	}

	function doSend() {
		header("Location:" . $this->getRequestURL());
		exit;
	}

	function createSign() {
		$signPars = "";
		ksort($this->parameters);
		foreach($this->parameters as $k => $v) {
			if("" !== $v && "sign" !== $k) {
				$signPars .= $k . "=" . $v . "&";
			}
		}
		$signPars .= "key=" . $this->getKey();
		$sign = strtolower(md5($signPars));
		$this->setParameter("sign", $sign);
		$this->_setDebugInfo($signPars . " => sign:" . $sign);

	}

	function _setDebugInfo($debugInfo) {
		$this->debugInfo = $debugInfo;
	}

}

class ResponseHandler  {

	var $key;

	var $parameters;

	var $debugInfo;

	function __construct() {
		$this->ResponseHandler();
	}

	function ResponseHandler() {
		$this->key = "";
		$this->parameters = array();
		$this->debugInfo = "";

		foreach($_GET as $k => $v) {
			$this->setParameter($k, $v);
		}
		foreach($_POST as $k => $v) {
			$this->setParameter($k, $v);
		}
	}

	function getKey() {
		return $this->key;
	}

	function setKey($key) {
		$this->key = $key;
	}

	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}

	function setParameter($parameter, $parameterValue) {
		$this->parameters[$parameter] = $parameterValue;
	}

	function getAllParameters() {
		return $this->parameters;
	}

	function isTenpaySign() {
		$signPars = "";

		ksort($this->parameters);
		foreach($this->parameters as $k => $v) {
			if("sign" !== $k && "" !== $v) {
				$signPars .= $k . "=" . $v . "&";
			}
		}
		$signPars .= "key=" . $this->getKey();
		$sign = strtolower(md5($signPars));
		$tenpaySign = strtolower($this->getParameter("sign"));
		$this->_setDebugInfo($signPars . " => sign:" . $sign .
				" tenpaySign:" . $this->getParameter("sign"));

		return $sign == $tenpaySign;

	}

	function getDebugInfo() {
		return $this->debugInfo;
	}

	function _setDebugInfo($debugInfo) {
		$this->debugInfo = $debugInfo;
	}
}


class MediPayRequestHandler extends RequestHandler {

	function __construct() {
		$this->MediPayRequestHandler();
	}

	function MediPayRequestHandler() {
		$this->setGateURL("https://www.tenpay.com/cgi-bin/med/show_opentrans.cgi");
	}

	function init() {
		$this->setParameter("attach", "1");

		$this->setParameter("chnid",  "");

		$this->setParameter("cmdno", "12");

		$this->setParameter("encode_type", "1");

		$this->setParameter("mch_desc", "");

		$this->setParameter("mch_name", "");

		$this->setParameter("mch_price",  "");

		$this->setParameter("mch_returl",  "");

		$this->setParameter("mch_type",  "");

		$this->setParameter("mch_vno",  "");

		$this->setParameter("need_buyerinfo",  "");

		$this->setParameter("seller",  "");

		$this->setParameter("show_url",  "");

		$this->setParameter("transport_desc",  "");

		$this->setParameter("transport_fee",  "");

		$this->setParameter("version",  "2");

		$this->setParameter("sign",  "");

	}

}

class MediPayResponseHandler extends ResponseHandler {

	function doShow() {
		$strHtml = "<html><head>\r\n" .
			"<meta name=\"TENCENT_ONLINE_PAYMENT\" content=\"China TENCENT\">" .
			"</head><body></body></html>";

		echo $strHtml;

		exit;
	}
	function isTenpaySign() {

		$signParameterArray = array(
			'attach',
			'buyer_id',
			'cft_tid',
			'chnid',
			'cmdno',
			'mch_vno',
			'retcode',
			'seller',
			'status',
			'total_fee',
			'trade_price',
			'transport_fee',
			'version'
		);

		ksort($signParameterArray);

		foreach($signParameterArray as $k ) {
			$v = $this->getParameter($k);
			if(isset($v)) {
				$signPars .= $k . "=" . urldecode($v) . "&";
			}
		}

		$signPars .= "key=" . $this->getKey();

		$sign = strtolower(md5($signPars));

		$tenpaySign = strtolower($this->getParameter("sign"));

		$this->_setDebugInfo($signPars . " => sign:" . $sign .
				" tenpaySign:" . $this->getParameter("sign"));

		return $sign == $tenpaySign;

	}

}

function credit_payurl($price, &$orderid, $bank = 'DEFAULT') {
	include_once DISCUZ_ROOT . './source/class/class_chinese.php';
	global $_G;

	$date = dgmdate(TIMESTAMP, 'YmdHis');
	$suffix = dgmdate(TIMESTAMP, 'His').rand(1000, 9999);
	$transaction_id = DISCUZ_PARTNER.$date.$suffix;

	$orderid = dgmdate(TIMESTAMP, 'YmdHis').random(14);

	$chinese = new Chinese(strtoupper(CHARSET), 'GBK');
	$subject = $chinese->Convert(lang('forum/misc', 'credit_forum_payment').' '.$_G['setting']['extcredits'][$_G['setting']['creditstrans']]['title'].' '.intval($price * $_G['setting']['ec_ratio']).' '.$_G['setting']['extcredits'][$_G['setting']['creditstrans']]['unit']);

	$reqHandler = new RequestHandler();
	$reqHandler->setGateURL("https://gw.tenpay.com/gateway/pay.htm");

	$reqHandler->init();
	$reqHandler->setKey(DISCUZ_SECURITYCODE);

	$reqHandler->setParameter("partner", DISCUZ_PARTNER);
	$reqHandler->setParameter("out_trade_no", $orderid);
	$reqHandler->setParameter("total_fee", $price * 100);
	$reqHandler->setParameter("return_url", $_G['siteurl'].'api/trade/notify_credit.php');
	$reqHandler->setParameter("notify_url", $_G['siteurl'].'api/trade/notify_credit.php');
	$reqHandler->setParameter("body", $subject);
	$reqHandler->setParameter("bank_type", $bank);

	$reqHandler->setParameter("spbill_create_ip", $_G['clientip']);
	$reqHandler->setParameter("fee_type", "1");
	$reqHandler->setParameter("subject", $subject);

	$reqHandler->setParameter("sign_type", "MD5");
	$reqHandler->setParameter("service_version", "1.0");
	$reqHandler->setParameter("input_charset", "GBK");
	$reqHandler->setParameter("sign_key_index", "1");

	$reqHandler->setParameter("attach", "tenpay");
	$reqHandler->setParameter("time_start", $date);
	$reqHandler->setParameter("trade_mode","1");
	$reqHandler->setParameter("trans_type","1");
	$reqHandler->setParameter("agentid", DISCUZ_AGENTID);
	$reqHandler->setParameter("agent_type","2");

	$reqUrl = $reqHandler->getRequestURL();
	return $reqUrl;
}

function trade_payurl($pay, $trade, $tradelog) {
	global $_G;

	$key = DISCUZ_TENPAY_OPENTRANS_KEY;

	$chnid = DISCUZ_TENPAY_OPENTRANS_CHNID;

	$seller = $trade['tenpayaccount'];

	$mch_desc = $trade['subject'];

	$mch_name = $trade['subject'];

	$mch_price = $tradelog['baseprice'] * $tradelog['number'] * 100;

	$mch_returl = $_G['siteurl'].'api/trade/notify_trade.php';

	$mch_vno = $tradelog['orderid'];

	$show_url = $_G['siteurl'].'api/trade/notify_trade.php';

	$transport_desc = $pay['logistics_type'];

	$transport_fee = $tradelog['transportfee'] * 100;

	if(strtolower(CHARSET) == 'gbk') {
		$encode_type = '1';
	} else {
		$encode_type = '2';
	}

	$mch_type = '1';
	$need_buyerinfo = '1';
	if($pay['logistics_type'] == 'VIRTUAL') {
		$mch_type = '2';
		$need_buyerinfo = '2';
	}

	$reqHandler = new MediPayRequestHandler();
	$reqHandler->init();
	$reqHandler->setKey($key);

	$reqHandler->setParameter("chnid", $chnid);
	$reqHandler->setParameter("encode_type", $encode_type);
	$reqHandler->setParameter("mch_desc", $mch_desc);
	$reqHandler->setParameter("mch_name", $mch_name);
	$reqHandler->setParameter("mch_price", $mch_price);
	$reqHandler->setParameter("mch_returl", $mch_returl);
	$reqHandler->setParameter("mch_type", $mch_type);
	$reqHandler->setParameter("mch_vno", $mch_vno);
	$reqHandler->setParameter("need_buyerinfo", $need_buyerinfo);
	$reqHandler->setParameter("seller", $seller);
	$reqHandler->setParameter("show_url",	$show_url);
	$reqHandler->setParameter("transport_desc", $transport_desc);
	$reqHandler->setParameter("transport_fee", $transport_fee);
	$reqHandler->setParameter('attach', 'tenpay');

	$reqUrl = $reqHandler->getRequestURL();
	return $reqUrl;
}


function invite_payurl($amount, $price, &$orderid, $bank = 'DEFAULT') {
	include_once DISCUZ_ROOT . './source/class/class_chinese.php';
	global $_G;

	$date = dgmdate(TIMESTAMP, 'YmdHis');
	$suffix = dgmdate(TIMESTAMP, 'His').rand(1000, 9999);
	$transaction_id = DISCUZ_PARTNER.$date.$suffix;

	$orderid = dgmdate(TIMESTAMP, 'YmdHis').random(14);

	$chinese = new Chinese(strtoupper(CHARSET), 'GBK');
	$subject = $chinese->Convert(lang('forum/misc', 'invite_forum_payment').' '.intval($amount).' '.lang('forum/misc', 'invite_forum_payment_unit'));

	$reqHandler = new RequestHandler();
	$reqHandler->setGateURL("https://gw.tenpay.com/gateway/pay.htm");

	$reqHandler->init();
	$reqHandler->setKey(DISCUZ_SECURITYCODE);

	$reqHandler->setParameter("partner", DISCUZ_PARTNER);
	$reqHandler->setParameter("out_trade_no", $orderid);
	$reqHandler->setParameter("total_fee", $price * 100);
	$reqHandler->setParameter("return_url", $_G['siteurl'].'api/trade/notify_invite.php');
	$reqHandler->setParameter("notify_url", $_G['siteurl'].'api/trade/notify_invite.php');
	$reqHandler->setParameter("body", $subject);
	$reqHandler->setParameter("bank_type", $bank);

	$reqHandler->setParameter("spbill_create_ip", $_G['clientip']);
	$reqHandler->setParameter("fee_type", "1");
	$reqHandler->setParameter("subject", $subject);

	$reqHandler->setParameter("sign_type", "MD5");
	$reqHandler->setParameter("service_version", "1.0");
	$reqHandler->setParameter("input_charset", "GBK");
	$reqHandler->setParameter("sign_key_index", "1");

	$reqHandler->setParameter("attach", "tenpay");
	$reqHandler->setParameter("time_start", $date);
	$reqHandler->setParameter("trade_mode","1");
	$reqHandler->setParameter("trans_type","1");
	$reqHandler->setParameter("agentid", DISCUZ_AGENTID);
	$reqHandler->setParameter("agent_type","2");

	$reqUrl = $reqHandler->getRequestURL();
	return $reqUrl;
}
function trade_notifycheck($type) {
	global $_G;

	if($type == 'credit' || $type == 'invite') {
		if(!DISCUZ_SECURITYCODE) {
			exit('Access Denied');
		}
		$resHandler = new ResponseHandler();
		$resHandler->setKey(DISCUZ_SECURITYCODE);

		$resHandler->setParameter("bankname", "");
	} else {
		if(!DISCUZ_TENPAY_OPENTRANS_KEY) {
			exit('Access Denied');
		}
		$resHandler = new MediPayResponseHandler();
		$resHandler->setKey(DISCUZ_TENPAY_OPENTRANS_KEY);
	}
	if($type == 'credit' || $type == 'invite') {
		if($resHandler->isTenpaySign() && DISCUZ_PARTNER == $_GET['partner']) {
			return array(
				'validator'	=> isset($_GET['trade_state']) ? !$_GET['trade_state'] : 0,
				'order_no' 	=> $_GET['out_trade_no'],
				'trade_no'	=> isset($_GET['transaction_id']) ? $_GET['transaction_id'] : '',
				'price' 	=> $_GET['total_fee'] / 100,
				'bargainor_id' => $_GET['partner'],
				'location'	=> true,
				);
		}
	} elseif($type == 'trade') {
		if($resHandler->isTenpaySign()) {
			return array(
				'validator' => $resHandler->getParameter('retcode') == '0',
				'order_no' => $resHandler->getParameter('mch_vno'),
				'trade_no' => $resHandler->getParameter('cft_tid'),
				'price' => $resHandler->getParameter('total_fee') / 100,
				'status' => $resHandler->getParameter('status'),
				'location'	=> true,
			);
		}
	} else {
		return array(
			'validator'	=> FALSE,
			'location'	=> 'forum.php?mod=memcp&action=credits&operation=addfunds&return=fail'
		);
	}
}

function trade_setprice($data, &$price, &$pay, &$transportfee) {
	if($data['transport'] == 3) {
		$pay['logistics_type'] = 'VIRTUAL';
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

function trade_getorderurl($orderid) {
	return "https://www.tenpay.com/med/tradeDetail.shtml?b=1&trans_id=$orderid";
}

function trade_typestatus($method, $status = -1) {
	switch($method) {
		case 'buytrades'	: $methodvalue = array(1, 3);break;
		case 'selltrades'	: $methodvalue = array(2, 4);break;
		case 'successtrades'	: $methodvalue = array(5);break;
		case 'tradingtrades'	: $methodvalue = array(1, 2, 3, 4);break;
		case 'closedtrades'	: $methodvalue = array(6, 10);break;
		case 'refundsuccess'	: $methodvalue = array(9);break;
		case 'refundtrades'	: $methodvalue = array(9, 10);break;
		case 'unstarttrades'	: $methodvalue = array(0);break;
	}
	return $status != -1 ? in_array($status, $methodvalue) : $methodvalue;
}

function trade_getstatus($key, $method = 2) {
	$language = lang('forum/misc');
	$status[1] = array(
		'WAIT_BUYER_PAY' => 1,
		'WAIT_SELLER_CONFIRM_TRADE' => 2,
		'WAIT_SELLER_SEND_GOODS' => 3,
		'WAIT_BUYER_CONFIRM_GOODS' => 4,
		'TRADE_FINISHED' => 5,
		'TRADE_CLOSED' => 6,
		'REFUND_SUCCESS' => 9,
		'REFUND_CLOSED' => 10,
	);
	$status[2] = array(
		0  => $language['trade_unstart'],
		1  => $language['trade_waitbuyerpay'],
		2  => $language['trade_waitsellerconfirm'],
		3  => $language['trade_waitsellersend'],
		4  => $language['trade_waitbuyerconfirm'],
		5  => $language['trade_finished'],
		6  => $language['trade_closed'],
		9 => $language['trade_refundsuccess'],
		10 => $language['trade_refundclosed']
	);
	return $method == -1 ? $status[2] : $status[$method][$key];
}

?>