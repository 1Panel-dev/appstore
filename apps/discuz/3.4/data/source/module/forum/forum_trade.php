<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_trade.php 27054 2011-12-31 06:04:21Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);
$apitype = $_GET['apitype'];

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$page = max(1, intval($_GET['page']));
$orderid = $_GET['orderid'];
if(!empty($orderid) && empty($_GET['apitype'])) {
	$orderinfo = C::t('forum_tradelog')->fetch($orderid);
	$paytype = $orderinfo['paytype'];
	if($paytype == 1) {
		$apitype = 'alipay';
	}
	if($paytype == 2) {
		$apitype = 'tenpay';
	}
}

require_once libfile('function/trade');
if(!empty($orderid)) {

	$language = lang('forum/misc');

	$tradelog = C::t('forum_tradelog')->fetch($orderid);
	if(!$_G['forum_auditstatuson'] && (empty($tradelog) || $_G['uid'] != $tradelog['sellerid'] && $_G['uid'] != $tradelog['buyerid'])) {
		showmessage('undefined_action', NULL);
	}

	$limit = 6;
	$query = C::t('forum_trade')->fetch_all_for_seller($tradelog['sellerid'], $limit);
	$usertrades = array();
	$usertradecount = 0;
	foreach($query as $usertrade) {
		$usertradecount++;
		$usertrades[] = $usertrade;
	}

	$trade_message = '';
	$currentcredit = $_G['setting']['creditstrans'] ? getuserprofile('extcredits'.$_G['setting']['creditstrans']) : 0;
	$discountprice = $tradelog['baseprice'] * $tradelog['number'];

	if(!empty($_GET['pay']) && !$tradelog['offline'] && $tradelog['status'] == 0 && $tradelog['buyerid'] == $_G['uid']) {
		if($_G['setting']['creditstransextra'][5] != -1 && $tradelog['credit']) {
			if($tradelog['credit'] > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][5])) {
				showmessage('trade_credit_lack');
			}
			updatemembercount($tradelog['buyerid'], array($_G['setting']['creditstransextra'][5] => -$tradelog['credit']));
		}
		$trade = C::t('forum_trade')->fetch_goods($tradelog['tid'], $tradelog['pid']);

		if($_G['uid'] && $currentcredit < $discountcredit && $tradelog['discount']) {
			showmessage('trade_credits_no_enough', '', array('credittitle' => $_G['setting']['extcredits'][$_G['setting']['creditstrans']]['title']));
		}
		$pay = array();
		$pay['commision'] = 0;
		$transport = $tradelog['transport'];
		$transportfee = 0;
		trade_setprice(array('fee' => $fee, 'trade' => $trade, 'transport' => $transport), $price, $pay, $transportfee);
		$payurl = trade_payurl($pay, $trade, $tradelog);
		$paytype = 0;
		if($apitype == 'alipay') {
			$paytype = 1;
		} elseif($apitype == 'tenpay') {
			$paytype = 2;
		}
		C::t('forum_tradelog')->update($orderid, array('paytype' => $paytype));
		showmessage('trade_directtopay', $payurl);
	}

	if(submitcheck('offlinesubmit') && in_array($_GET['offlinestatus'], trade_offline($tradelog, 0))) {

		loaducenter();
		$ucresult = uc_user_login($_G['username'], $_GET['password']);
		list($tmp['uid']) = daddslashes($ucresult);

		if($tmp['uid'] <= 0) {
			showmessage('trade_password_error', 'forum.php?mod=trade&orderid='.$orderid);
		}
		if($_GET['offlinestatus'] == 4) {
			if($_G['setting']['creditstransextra'][5] != -1 && $tradelog['credit']) {
				if($tradelog['credit'] > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][5])) {
					showmessage('trade_credit_lack');
				}
				updatemembercount($tradelog['buyerid'], array($_G['setting']['creditstransextra'][5] => -$tradelog['credit']));
			}
			$trade = C::t('forum_trade')->fetch_goods($tradelog['tid'], $tradelog['pid']);
			notification_add($tradelog['sellerid'], 'goods', 'trade_seller_send', array(
				'buyerid' => $tradelog['buyerid'],
				'buyer' => $tradelog['buyer'],
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
		} elseif($_GET['offlinestatus'] == 5) {
			notification_add($tradelog['buyerid'], 'goods', 'trade_buyer_confirm', array(
				'sellerid' => $tradelog['sellerid'],
				'seller' => $tradelog['seller'],
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
		} elseif($_GET['offlinestatus'] == 7) {
			if($_G['setting']['creditstransextra'][5] != -1 && $tradelog['basecredit']) {
				$netcredit = round($tradelog['number'] * $tradelog['basecredit'] * (1 - $_G['setting']['creditstax']));
				updatemembercount($tradelog['sellerid'], array($_G['setting']['creditstransextra'][5] => $netcredit));
			} else {
				$netcredit = 0;
			}
			$data = array('lastbuyer' => $tradelog['buyer'], 'lastupdate' => $_G['timestamp']);
			C::t('forum_trade')->update($tradelog['tid'], $tradelog['pid'], $data);
			C::t('forum_trade')->update_counter($tradelog['tid'], $tradelog['pid'], $tradelog['number'], $tradelog['price'], $netcredit);
			notification_add($tradelog['sellerid'], 'goods', 'trade_success', array(
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
			notification_add($tradelog['buyerid'], 'goods', 'trade_success', array(
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
		} elseif($_GET['offlinestatus'] == 17) {
			C::t('forum_trade')->update_counter($tradelog['tid'], $tradelog['pid'], 0, 0, 0, $tradelog['number']);
			notification_add($tradelog['sellerid'], 'goods', 'trade_fefund_success', array(
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
			notification_add($tradelog['buyerid'], 'goods', 'trade_fefund_success', array(
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
			if($_G['setting']['creditstransextra'][5] != -1 && $tradelog['basecredit']) {
				updatemembercount($tradelog['buyerid'], array($_G['setting']['creditstransextra'][5] => $tradelog['number'] * $tradelog['basecredit']));
			}
		}

		$_GET['message'] = trim($_GET['message']);
		if($_GET['message']) {
			$_GET['message'] = $tradelog['message']."\t\t\t".$_G['uid']."\t".$_G['member']['username']."\t".TIMESTAMP."\t".nl2br(strip_tags(substr($_GET['message'], 0, 200)));
		} else {
			$_GET['message'] = $tradelog['message'];
		}

		C::t('forum_tradelog')->update($orderid, array(
		    'status' => $_GET['offlinestatus'],
		    'lastupdate' => $_G['timestamp'],
		    'message' => $_GET['message']
		));
		showmessage('trade_orderstatus_updated', 'forum.php?mod=trade&orderid='.$orderid);
	}

	if(submitcheck('tradesubmit')) {

		if($tradelog['status'] == 0) {

			$update = array();
			$oldbasecredit = $tradelog['basecredit'];
			$oldnumber = $tradelog['number'];
			if($tradelog['sellerid'] == $_G['uid']) {
				$tradelog['baseprice'] = floatval($_GET['newprice']);
				$tradelog['basecredit'] = intval($_GET['newcredit']);
				if(!$tradelog['baseprice'] < 0 || $tradelog['basecredit'] < 0) {
					showmessage('trade_pricecredit_error');
				}
				$tradelog['transportfee'] = intval($_GET['newfee']);
				$newnumber = $tradelog['number'];
				$update = array(
					'baseprice' => $tradelog['baseprice'],
					'basecredit' => $tradelog['basecredit'],
					'transportfee' => $tradelog['transportfee']
				);
				notification_add($tradelog['buyerid'], 'goods', 'trade_order_update_sellerid', array(
					'seller' => $tradelog['seller'],
					'sellerid' => $tradelog['sellerid'],
					'orderid' => $orderid,
					'subject' => $tradelog['subject']
				));
			}
			if($tradelog['buyerid'] == $_G['uid']) {
				$newnumber = intval($_GET['newnumber']);
				if($newnumber <= 0) {
					showmessage('trade_input_no');
				}
				$trade = C::t('forum_trade')->fetch_goods($tradelog['tid'], $tradelog['pid']);
				if($newnumber > $trade['amount'] + $tradelog['number']) {
					showmessage('trade_lack');
				}
				$amount = $trade['amount'] + $tradelog['number'] - $newnumber;
				C::t('forum_trade')->update($tradelog['tid'], $tradelog['pid'], array('amount' => $amount));
				$tradelog['number'] = $newnumber;

				$update = array(
					'number' => $tradelog['number'],
					'discount' => 0,
					'buyername' => dhtmlspecialchars($_GET['newbuyername']),
					'buyercontact' => dhtmlspecialchars($_GET['newbuyercontact']),
					'buyerzip' => dhtmlspecialchars($_GET['newbuyerzip']),
					'buyerphone' => dhtmlspecialchars($_GET['newbuyerphone']),
					'buyermobile' => dhtmlspecialchars($_GET['newbuyermobile']),
					'buyermsg' => dhtmlspecialchars($_GET['newbuyermsg'])
				);
				notification_add($tradelog['sellerid'], 'goods', 'trade_order_update_buyerid', array(
					'buyer' => $tradelog['buyer'],
					'buyerid' => $tradelog['buyerid'],
					'orderid' => $orderid,
					'subject' => $tradelog['subject']
				));
			}
			if($update) {
				if($tradelog['discount']) {
					$tradelog['baseprice'] = $tradelog['baseprice'] - $tax;
					$price = $tradelog['baseprice'] * $tradelog['number'];
				} else {
					$price = $tradelog['baseprice'] * $tradelog['number'];
				}
				if($_G['setting']['creditstransextra'][5] != -1 && ($oldnumber != $newnumber || $oldbasecredit != $tradelog['basecredit'])) {
					$tradelog['credit'] = $newnumber * $tradelog['basecredit'];
					$update['credit'] = $tradelog['credit'];
				}

				$update['price'] = $price + ($tradelog['transport'] == 2 ? $tradelog['transportfee'] : 0);
				C::t('forum_tradelog')->update($orderid, $update);
				$tradelog = C::t('forum_tradelog')->fetch($orderid);
			}
		}

	}

	$tradelog['lastupdate'] = dgmdate($tradelog['lastupdate'], 'u');
	$tradelog['statusview'] = trade_getstatus($tradelog['status']);

	$messagelist = array();
	if($tradelog['offline']) {
		$offlinenext = trade_offline($tradelog, 1, $trade_message);
		$message = explode("\t\t\t", $tradelog['message']);
		foreach($message as $row) {
			$row = explode("\t", $row);
			$row[2] = dgmdate($row[2], 'u');
			$row[0] && $messagelist[] = $row;
		}
	} else {
		$loginurl = trade_getorderurl($tradelog['tradeno']);
	}

	$trade = C::t('forum_trade')->fetch_goods($tradelog['tid'], $tradelog['pid']);

	include template('forum/trade_view');

} else {

	if(empty($_GET['pid'])) {
		$pid = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid']);
		$pid = $pid['pid'];
	} else {
		$pid = $_GET['pid'];
	}
	$thread = C::t('forum_thread')->fetch($_G['tid']);
	if($thread['closed']) {
		showmessage('trade_closed', 'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page);
	}
	$trade = C::t('forum_trade')->fetch_goods($_G['tid'], $pid);
	if(empty($trade)) {
		showmessage('trade_not_found');
	}
	$fromcode = false;

	if($trade['closed']) {
		showmessage('trade_closed', 'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page);
	}

	if($trade['price'] <= 0 && $trade['credit'] <= 0) {
		showmessage('trade_invalid', 'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page);
	}
	if($trade['credit'] > 0 && $_G['setting']['creditstransextra'][5] == -1) {
		showmessage('trade_credit_invalid', 'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page);
	}

	$limit = 6;
	$query = C::t('forum_trade')->fetch_all_for_seller($trade['sellerid'], $limit);
	$usertrades = array();
	$usertradecount = 0;
	foreach($query as $usertrade) {
		$usertradecount++;
		$usertrades[] = $usertrade;
	}

	if($_GET['action'] != 'trade' && !submitcheck('tradesubmit')) {
		$lastbuyerinfo = dhtmlspecialchars(C::t('forum_tradelog')->fetch_last($_G['uid']));
		$extra = rawurlencode($extra);
		include template('forum/trade');
	} else {

		if($trade['sellerid'] == $_G['uid']) {
			showmessage('trade_by_myself');
		} elseif($_GET['number'] <= 0) {
			showmessage('trade_input_no');
		} elseif(!$fromcode && $_GET['number'] > $trade['amount']) {
			showmessage('trade_lack');
		}

		$pay['number'] = $_GET['number'];
		$pay['price'] = $trade['price'];
		$credit = 0;
		if($_G['setting']['creditstransextra'][5] != -1 && $trade['credit']) {
			$credit = $_GET['number'] * $trade['credit'];
		}

		$price = $pay['price'] * $pay['number'];
		$buyercredits = 0;
		$pay['commision'] = 0;

		$orderid = $pay['orderid'] = dgmdate(TIMESTAMP, 'YmdHis').random(18);
		$transportfee = 0;
		trade_setprice(array('fee' => $fee, 'trade' => $trade, 'transport' => $_GET['transport']), $price, $pay, $transportfee);

		$buyerid = $_G['uid'] ? $_G['uid'] : 0;
		$_G['username'] = $_G['username'] ? $_G['username'] : $guestuser;
		$trade = daddslashes($trade, 1);
		$buyermsg = dhtmlspecialchars($_GET['buyermsg']);
		$buyerzip = dhtmlspecialchars($_GET['buyerzip']);
		$buyerphone = dhtmlspecialchars($_GET['buyerphone']);
		$buyermobile = dhtmlspecialchars($_GET['buyermobile']);
		$buyername = dhtmlspecialchars($_GET['buyername']);
		$buyercontact = dhtmlspecialchars($_GET['buyercontact']);

		$offline = !empty($_GET['offline']) ? 1 : 0;
		C::t('forum_tradelog')->insert(array(
			'tid' => $trade['tid'],
			'pid' => $trade['pid'],
			'orderid' => $orderid,
			'subject' => $trade['subject'],
			'price' => $price,
			'quality' => $trade['quality'],
			'itemtype' => $trade['itemtype'],
			'number' => $_GET['number'],
			'tax' => $tax,
			'locus' => $trade['locus'],
			'sellerid' => $trade['sellerid'],
			'seller' => $trade['seller'],
			'selleraccount' => $trade['account'],
			'tenpayaccount' => $trade['tenpayaccount'],
			'buyerid' => $_G['uid'],
			'buyer' => $_G['username'],
			'buyercontact' => $buyercontact,
			'buyercredits' => 0,
			'buyermsg' => $buyermsg,
			'lastupdate' => $_G['timestamp'],
			'offline' => $offline,
			'buyerzip' => $buyerzip,
			'buyerphone' => $buyerphone,
			'buyermobile' => $buyermobile,
			'buyername' => $buyername,
			'transport' => $_GET['transport'],
			'transportfee' => $transportfee,
			'baseprice' => $trade['price'],
			'discount' => 0,
			'credit' => $credit,
			'basecredit' => $trade['credit']
		));

		C::t('forum_trade')->update_counter($trade['tid'], $trade['pid'], 0, 0, 0, '-'.$_GET['number']);
		showmessage('trade_order_created', 'forum.php?mod=trade&orderid='.$orderid);
	}

}

?>