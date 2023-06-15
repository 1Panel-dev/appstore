<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_eccredit.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/ec_credit');

if($_GET['op'] == 'list') {

	$from = !empty($_GET['from']) && in_array($_GET['from'], array('buyer', 'seller', 'myself')) ? $_GET['from'] : '';
	$uid = !empty($_GET['uid']) ? intval($_GET['uid']) : $_G['uid'];


	$filter = !empty($_GET['filter']) ? $_GET['filter'] : '';
	switch($filter) {
		case 'thisweek':
			$dateline = intval($_G['timestamp'] - 604800);
			break;
		case 'thismonth':
			$dateline = intval($_G['timestamp'] - 2592000);
			break;
		case 'halfyear':
			$dateline = intval($_G['timestamp'] - 15552000);
			break;
		case 'before':
			$dateline = intval($_G['timestamp'] - 15552000);
			break;
		default:
			$dateline = false;
	}

	$level = !empty($_GET['level']) ? $_GET['level'] : '';
	switch($level) {
		case 'good':
			$score = 1;
			break;
		case 'soso':
			$score = 0;
			break;
		case 'bad':
			$score = -1;
			break;
		default:
			$score = false;
	}

	$page = max(1, intval($_GET['page']));
	$start_limit = ($page - 1) * 10;
	$num = C::t('forum_tradecomment')->count_list($from, $uid, $dateline, $score);
	$multipage = multi($num, 10, $page, "home.php?mod=spacecp&ac=list&uid=$uid".($from ? "&from=$from" : NULL).($filter ? "&filter=$filter" : NULL).($level ? "&level=$level" : NULL));

	$comments = array();
	foreach(C::t('forum_tradecomment')->fetch_all_list($from, $uid, $dateline, $score, $start_limit) as $comment) {
		$comment['expiration'] = dgmdate($comment['dateline'] + 30 * 86400, 'u');
		$comment['dbdateline'] = $comment['dateline'];
		$comment['dateline'] = dgmdate($comment['dateline'], 'u');
		$comment['baseprice'] = sprintf('%0.2f', $comment['baseprice']);
		$comments[] = $comment;
	}

	include template('home/spacecp_ec_list');

} elseif($_GET['op'] == 'rate' && ($orderid = $_GET['orderid']) && isset($_GET['type'])) {

	require_once libfile('function/trade');

	$type = intval($_GET['type']);
	if(!$type) {
		$raterid = 'buyerid';
		$ratee = 'seller';
		$rateeid = 'sellerid';
	} else {
		$raterid = 'sellerid';
		$ratee = 'buyer';
		$rateeid = 'buyerid';
	}
	$order = C::t('forum_tradelog')->fetch($orderid);
	if(!$order || $order[$raterid] != $_G['uid']) {
		showmessage('eccredit_order_notfound');
	} elseif($order['ratestatus'] == 3 || ($type == 0 && $order['ratestatus'] == 1) || ($type == 1 && $order['ratestatus'] == 2)) {
		showmessage('eccredit_rate_repeat');
	} elseif(!trade_typestatus('successtrades', $order['status']) && !trade_typestatus('refundsuccess', $order['status'])) {
		showmessage('eccredit_nofound');
	}

	$uid = $_G['uid'] == $order['buyerid'] ? $order['sellerid'] : $order['buyerid'];

	if(!submitcheck('ratesubmit')) {

		include template('home/spacecp_ec_rate');

	} else {

		$score = intval($_GET['score']);
		$message = cutstr(dhtmlspecialchars($_GET['message']), 200);
		$level = $score == 1 ? 'good' : ($score == 0 ? 'soso' : 'bad');
		$pid = intval($order['pid']);
		$order = daddslashes($order, 1);

		C::t('forum_tradecomment')->insert(array(
		    'pid' => $pid,
		    'orderid' => $orderid,
		    'type' => $type,
		    'raterid' => $_G['uid'],
		    'rater' => $_G['username'],
		    'ratee' => $order[$ratee],
		    'rateeid' => $order[$rateeid],
		    'score' => $score,
		    'message' => $message,
		    'dateline' => $_G['timestamp']
		));

		if(!$order['offline'] || $order['credit']) {
			if(C::t('forum_tradecomment')->get_month_score($_G['uid'], $type, $order[$rateeid]) < $_G['setting']['ec_credit']['maxcreditspermonth']) {
				updateusercredit($uid, $type ? 'sellercredit' : 'buyercredit', $level);
			}
		}

		if($type == 0) {
			$ratestatus = $order['ratestatus'] == 2 ? 3 : 1;
		} else {
			$ratestatus = $order['ratestatus'] == 1 ? 3 : 2;
		}

		C::t('forum_tradelog')->update($order['orderid'], array('ratestatus' => $ratestatus));

		if($ratestatus != 3) {
			notification_add($order[$rateeid], 'goods', 'eccredit', array(
				'orderid' => $orderid,
			), 1);
		}

		showmessage('eccredit_succeed', 'home.php?mod=space&uid='.$_G['uid'].'&do=trade&view=eccredit');

	}

} elseif($_GET['op'] == 'explain' && $_GET['id']) {

	$id = intval($_GET['id']);
	$ajaxmenuid = $_GET['ajaxmenuid'];
	if(!submitcheck('explainsubmit', 1)) {
		include template('home/spacecp_ec_explain');
	} else {
		$comment = C::t('forum_tradecomment')->fetch($id);
		if(!$comment || $comment['rateeid'] != $_G['uid']) {
			showmessage('eccredit_nofound');
		} elseif($comment['explanation']) {
			showmessage('eccredit_reexplanation_repeat');
		} elseif($comment['dateline'] < TIMESTAMP - 30 * 86400) {
			showmessage('eccredit_reexplanation_closed');
		}

		$explanation = cutstr(dhtmlspecialchars($_GET['explanation']), 200);

		C::t('forum_tradecomment')->update($id, array('explanation' => $explanation));

		$language = lang('forum/misc');
		showmessage($language['eccredit_explain'].'&#58; '.$explanation, '', array(), array('msgtype' => 3, 'showmsg' => 1));
	}

}
?>