<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_ec.php 30969 2012-07-04 10:18:10Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
if(!defined('APPTYPEID')) {
	define('APPTYPEID', 2);
}
$checktype = $_GET['checktype'];
cpheader();

if($operation == 'alipay') {

	$settings = C::t('common_setting')->fetch_all(array('ec_account', 'ec_contract'));

	if(!empty($checktype)) {
		require_once DISCUZ_ROOT.'./api/trade/api_alipay.php';
		if($checktype == 'credit') {
			ob_end_clean();
			dheader('location: '.credit_payurl(0.01, $orderid));
		} elseif($checktype == 'virtualgoods') {
			$pay = array(
				'logistics_type' => 'VIRTUAL'
			);
			$trade = array(
				'subject' => $lang['ec_alipay_check_virtualgoodssubject'],
				'itemtype' => 0.01,
				'account' => $settings['ec_account'],
			);
			$tradelog = array(
				'orderid' => 'TEST'.dgmdate(TIMESTAMP, 'YmdHis').random(18),
				'baseprice' => 0.01,
				'number' => 1,
				'transportfee' => 0,
			);
			dheader('location: '.trade_payurl($pay, $trade, $tradelog));
		} elseif($checktype == 'goods') {
			$pay = array(
				'logistics_type' => 'EMS',
				'transport' => 'SELLER_PAY',
			);
			$trade = array(
				'subject' => $lang['ec_alipay_check_goodssubject'],
				'itemtype' => 1,
				'account' => $settings['ec_account'],
			);
			$tradelog = array(
				'orderid' => 'TEST'.dgmdate(TIMESTAMP, 'YmdHis').random(18),
				'baseprice' => 0.01,
				'number' => 1,
				'transportfee' => 0,
			);
			dheader('location: '.trade_payurl($pay, $trade, $tradelog));
		}
		exit;
	}

	list($ec_contract, $ec_securitycode, $ec_partner, $ec_creditdirectpay) = explode("\t", authcode($settings['ec_contract'], 'DECODE', $_G['config']['security']['authkey']));
	$ec_securitycodemask = $ec_securitycode ? $ec_securitycode[0].'********'.substr($ec_securitycode, -4) : '';

	if(!submitcheck('alipaysubmit')) {

		shownav('extended', 'nav_ec');
		showsubmenu('nav_ec', array(
			array('nav_ec_config', 'setting&operation=ec', 0),
			array('nav_ec_tenpay', 'ec&operation=tenpay', 0),
			array('nav_ec_alipay', 'ec&operation=alipay', 1),
			array('nav_ec_credit', 'ec&operation=credit', 0),
			array('nav_ec_orders', 'ec&operation=orders', 0),
			array('nav_ec_tradelog', 'tradelog', 0),
			array('nav_ec_inviteorders', 'ec&operation=inviteorders', 0)
		));

		showtips('ec_alipay_tips');
		showformheader('ec&operation=alipay');

		showtableheader('','nobottom');
		showtitle('ec_alipay');
		showsetting('ec_alipay_account', 'settingsnew[ec_account]', $settings['ec_account'], 'text');
		showsetting('ec_alipay_check', '', '',
			'<a href="'.ADMINSCRIPT.'?action=ec&operation=alipay&checktype=credit" target="_blank">'.$lang['ec_alipay_checklink_credit'].'</a><br />'
		);
		showtitle('ec_contract');
		showsetting('ec_alipay_creditdirectpay', 'settingsnew[ec_creditdirectpay]', $ec_creditdirectpay, 'radio', '', 0, '', ' onclick="changeAliApi(this.value)"');
		showsetting('ec_alipay_partner', 'settingsnew[ec_partner]', $ec_partner, 'text');
		showsetting('ec_alipay_securitycode', 'settingsnew[ec_securitycode]', $ec_securitycodemask, 'text');
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('alipaysubmit');
		showtablefooter();
		showformfooter();
		echo <<<EOT
<script type="text/JavaScript">
function changeAliApi(type) {
	var apiUrl = 'https://b.alipay.com/order/pidKey.htm?pid=2088501719138773&product=';
	if(type == 1) {
		apiUrl += 'fastpay';
	} else {
		apiUrl += 'dualpay';
	}
	$('aliapitype').href = apiUrl;
}
changeAliApi($ec_creditdirectpay);
</script>
EOT;

	} else {
		$settingsnew = $_GET['settingsnew'];
		$settingsnew['ec_contract'] = 0;
		if(!empty($settingsnew['ec_securitycode']) && !empty($settingsnew['ec_partner'])) {
			$settingsnew['ec_contract'] = 1;
		}
		if($settingsnew['ec_account'] && !$settingsnew['ec_contract']) {
			cpmsg('alipay_not_contract', 'action=ec&operation=alipay', 'error');
		}
		$settingsnew['ec_account'] = trim($settingsnew['ec_account']);
		$settingsnew['ec_securitycode'] = trim($settingsnew['ec_securitycode']);
		C::t('common_setting')->update('ec_account', $settingsnew['ec_account']);
		$ec_securitycodemasknew = $settingsnew['ec_securitycode'] ? $settingsnew['ec_securitycode'][0].'********'.substr($settingsnew['ec_securitycode'], -4) : '';
		$settingsnew['ec_securitycode'] = $ec_securitycodemasknew == $ec_securitycodemask ? $ec_securitycode : $settingsnew['ec_securitycode'];
		$ec_contract = addslashes(authcode($settingsnew['ec_contract']."\t".$settingsnew['ec_securitycode']."\t".$settingsnew['ec_partner']."\t".$settingsnew['ec_creditdirectpay'], 'ENCODE', $_G['config']['security']['authkey']));
		C::t('common_setting')->update('ec_contract', $ec_contract);
		updatecache('setting');

		cpmsg('alipay_succeed', 'action=ec&operation=alipay', 'succeed');

	}

} elseif($operation == 'tenpay') {

	$settings = C::t('common_setting')->fetch_all(array('ec_tenpay_direct', 'ec_tenpay_account', 'ec_tenpay_bargainor', 'ec_tenpay_key', 'ec_tenpay_opentrans_chnid', 'ec_tenpay_opentrans_key'));
	if(!empty($checktype)) {
		require_once DISCUZ_ROOT.'./api/trade/api_tenpay.php';
		if($checktype == 'credit') {
			dheader('location: '.credit_payurl(1, $orderid));
		} elseif($checktype == 'virtualgoods') {
			$pay = array(
				'logistics_type' => 'VIRTUAL'
			);
			$trade = array(
				'subject' => $lang['ec_tenpay_check_virtualgoodssubject'],
				'itemtype' => 1,
				'tenpayaccount' => $settings['ec_tenpay_opentrans_chnid'],
			);
			$tradelog = array(
				'orderid' => 'TEST'.dgmdate(TIMESTAMP, 'YmdHis').random(18),
				'baseprice' => 1,
				'number' => 1,
				'transportfee' => 0,
			);
			dheader('location: '.trade_payurl($pay, $trade, $tradelog));
		} elseif($checktype == 'goods') {
			$pay = array(
				'logistics_type' => 'EMS',
				'transport' => 'SELLER_PAY',
			);
			$trade = array(
				'subject' => $lang['ec_tenpay_check_goodssubject'],
				'itemtype' => 1,
				'tenpayaccount' => $settings['ec_tenpay_opentrans_chnid'],
			);
			$tradelog = array(
				'orderid' => 'TEST'.dgmdate(TIMESTAMP, 'YmdHis').random(18),
				'baseprice' => 1,
				'number' => 1,
				'transportfee' => 0,
			);
			dheader('location: '.trade_payurl($pay, $trade, $tradelog));
		}
		exit;
	}

	if(!submitcheck('tenpaysubmit')) {

		shownav('extended', 'nav_ec');
		showsubmenu('nav_ec', array(
			array('nav_ec_config', 'setting&operation=ec', 0),
			array('nav_ec_tenpay', 'ec&operation=tenpay', 1),
			array('nav_ec_alipay', 'ec&operation=alipay', 0),
			array('nav_ec_credit', 'ec&operation=credit', 0),
			array('nav_ec_orders', 'ec&operation=orders', 0),
			array('nav_ec_tradelog', 'tradelog', 0),
			array('nav_ec_inviteorders', 'ec&operation=inviteorders', 0)
		));

		showtips('ec_tenpay_tips');
		showformheader('ec&operation=tenpay');

		showtableheader('','nobottom');


		showtitle('ec_tenpay_opentrans');
		showsetting('ec_tenpay_opentrans_chnid', 'settingsnew[ec_tenpay_opentrans_chnid]', $settings['ec_tenpay_opentrans_chnid'], 'text');
		$tenpay_securitycodemask = $settings['ec_tenpay_opentrans_key'] ? $settings['ec_tenpay_opentrans_key'][0].'********'.substr($settings['ec_tenpay_opentrans_key'], -4) : '';
		showsetting('ec_tenpay_opentrans_key', 'settingsnew[ec_tenpay_opentrans_key]', $tenpay_securitycodemask, 'text');

		showtitle('ec_tenpay');
		showsetting('ec_tenpay_bargainor', 'settingsnew[ec_tenpay_bargainor]', $settings['ec_tenpay_bargainor'], 'text');

		$tenpay_securitycodemask = $settings['ec_tenpay_key'] ? $settings['ec_tenpay_key'][0].'********'.substr($settings['ec_tenpay_key'], -4) : '';
		showsetting('ec_tenpay_key', 'settingsnew[ec_tenpay_key]', $tenpay_securitycodemask, 'text');
		showsetting('ec_tenpay_check', '', '',
			'<a href="'.ADMINSCRIPT.'?action=ec&operation=tenpay&checktype=credit" target="_blank">'.$lang['ec_alipay_checklink_credit'].'</a><br />'.
			'<a href="'.ADMINSCRIPT.'?action=ec&operation=tenpay&checktype=virtualgoods" target="_blank">'.$lang['ec_alipay_checklink_virtualgoods'].'</a><br />'.
			'<a href="'.ADMINSCRIPT.'?action=ec&operation=tenpay&checktype=goods" target="_blank">'.$lang['ec_alipay_checklink_goods'].'</a><br />'
		);
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('tenpaysubmit');
		showtablefooter();
		showformfooter();

	} else {
		$settingsnew = $_GET['settingsnew'];
		$settingsnew['ec_tenpay_bargainor'] = trim($settingsnew['ec_tenpay_bargainor']);
		$settingsnew['ec_tenpay_key'] = trim($settingsnew['ec_tenpay_key']);
		$tenpay_securitycodemask = $settings['ec_tenpay_key'] ? $settings['ec_tenpay_key'][0].'********'.substr($settings['ec_tenpay_key'], -4) : '';
		$settingsnew['ec_tenpay_key'] = $tenpay_securitycodemask == $settingsnew['ec_tenpay_key'] ? $settings['ec_tenpay_key'] : $settingsnew['ec_tenpay_key'];

		$settingsnew['ec_tenpay_opentrans_key'] = trim($settingsnew['ec_tenpay_opentrans_key']);
		$tenpay_securitycodemask = $settings['ec_tenpay_opentrans_key'] ? $settings['ec_tenpay_opentrans_key'][0].'********'.substr($settings['ec_tenpay_opentrans_key'], -4) : '';
		$settingsnew['ec_tenpay_opentrans_key'] = $tenpay_securitycodemask == $settingsnew['ec_tenpay_opentrans_key'] ? $settings['ec_tenpay_opentrans_key'] : $settingsnew['ec_tenpay_opentrans_key'];
		if($settingsnew['ec_tenpay_direct'] && (!empty($settingsnew['ec_tenpay_bargainor']) && !preg_match('/^\d{10}$/', $settingsnew['ec_tenpay_bargainor']))) {
			cpmsg('tenpay_bargainor_invalid', 'action=ec&operation=tenpay', 'error');
		}
		if($settingsnew['ec_tenpay_direct'] && (empty($settingsnew['ec_tenpay_key']) || !preg_match('/^[a-zA-Z0-9]{32}$/', $settingsnew['ec_tenpay_key']))) {
			cpmsg('tenpay_key_invalid', 'action=ec&operation=tenpay', 'error');
		}
		$data = array('ec_tenpay_direct' => $settingsnew['ec_tenpay_direct'],
			'ec_tenpay_bargainor' => $settingsnew['ec_tenpay_bargainor'],
			'ec_tenpay_key' => $settingsnew['ec_tenpay_key'],
			'ec_tenpay_opentrans_chnid' => $settingsnew['ec_tenpay_opentrans_chnid'],
			'ec_tenpay_opentrans_key' => $settingsnew['ec_tenpay_opentrans_key']);
		C::t('common_setting')->update_batch($data);
		updatecache('setting');

		cpmsg('tenpay_succeed', 'action=ec&operation=tenpay', 'succeed');

	}

} elseif($operation == 'orders') {

	$orderurl = array(
		'alipay' => 'https://www.alipay.com/trade/query_trade_detail.htm?trade_no=',
		'tenpay' => 'https://www.tenpay.com/med/tradeDetail.shtml?trans_id=',
	);

	if(!$_G['setting']['creditstrans'] || !$_G['setting']['ec_ratio']) {
		cpmsg('orders_disabled', '', 'error');
	}

	if(!submitcheck('ordersubmit')) {

		echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
		shownav('extended', 'nav_ec');
		showsubmenu('nav_ec', array(
			array('nav_ec_config', 'setting&operation=ec', 0),
			array('nav_ec_tenpay', 'ec&operation=tenpay', 0),
			array('nav_ec_alipay', 'ec&operation=alipay', 0),
			array('nav_ec_credit', 'ec&operation=credit', 0),
			array('nav_ec_orders', 'ec&operation=orders', 1),
			array('nav_ec_tradelog', 'tradelog', 0),
			array('nav_ec_inviteorders', 'ec&operation=inviteorders', 0)
		));
		showtips('ec_orders_tips');
		showtagheader('div', 'ordersearch', !submitcheck('searchsubmit', 1));
		showformheader('ec&operation=orders');
		showtableheader('ec_orders_search');
		showsetting('ec_orders_search_status', array('orderstatus', array(
			array('', $lang['ec_orders_search_status_all']),
			array(1, $lang['ec_orders_search_status_pending']),
			array(2, $lang['ec_orders_search_status_auto_finished']),
			array(3, $lang['ec_orders_search_status_manual_finished'])
		)), intval($orderstatus), 'select');
		showsetting('ec_orders_search_id', 'orderid', $orderid, 'text');
		showsetting('ec_orders_search_users', 'users', $users, 'text');
		showsetting('ec_orders_search_buyer', 'buyer', $buyer, 'text');
		showsetting('ec_orders_search_admin', 'admin', $admin, 'text');
		showsetting('ec_orders_search_submit_date', array('sstarttime', 'sendtime'), array($sstarttime, $sendtime), 'daterange');
		showsetting('ec_orders_search_confirm_date', array('cstarttime', 'cendtime'), array($cstarttime, $cendtime), 'daterange');
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		if(submitcheck('searchsubmit', 1)) {

			$start_limit = ($page - 1) * $_G['tpp'];


			$ordercount = C::t('forum_order')->count_by_search(null, $_GET['orderstatus'], $_GET['orderid'], null, ($_GET['users'] ? explode(',', str_replace(' ', '', $_GET['users'])) : null), $_GET['buyer'], $_GET['admin'], strtotime($_GET['sstarttime']), strtotime($_GET['sendtime']), strtotime($_GET['cstarttime']), strtotime($_GET['cendtime']));
			$multipage = multi($ordercount, $_G['tpp'], $page, ADMINSCRIPT."?action=ec&operation=orders&searchsubmit=yes&orderstatus={$_GET['orderstatus']}&orderid={$_GET['orderid']}&users={$_GET['users']}&buyer={$_GET['buyer']}&admin={$_GET['admin']}&sstarttime={$_GET['sstarttime']}&sendtime={$_GET['sendtime']}&cstarttime={$_GET['cstarttime']}&cendtime={$_GET['cendtime']}");

			showtagheader('div', 'orderlist', TRUE);
			showformheader('ec&operation=orders');
			showtableheader('result');
			showsubtitle(array('', 'ec_orders_id', 'ec_orders_status', 'ec_orders_buyer', 'ec_orders_amount', 'ec_orders_price', 'ec_orders_submitdate', 'ec_orders_confirmdate'));


			foreach(C::t('forum_order')->fetch_all_by_search(null, $_GET['orderstatus'], $_GET['orderid'], null, ($_GET['users'] ? explode(',', str_replace(' ', '', $_GET['users'])) : null), $_GET['buyer'], $_GET['admin'], strtotime($_GET['sstarttime']), strtotime($_GET['sendtime']), strtotime($_GET['cstarttime']), strtotime($_GET['cendtime']), $start_limit, $_G['tpp']) as $order) {
				switch($order['status']) {
					case 1: $order['orderstatus'] = $lang['ec_orders_search_status_pending']; break;
					case 2: $order['orderstatus'] = '<b>'.$lang['ec_orders_search_status_auto_finished'].'</b>'; break;
					case 3: $order['orderstatus'] = '<b>'.$lang['ec_orders_search_status_manual_finished'].'</b><br />(<a href="home.php?mod=space&username='.rawurlencode($order['admin']).'" target="_blank">'.$order['admin'].'</a>)'; break;
				}
				$order['submitdate'] = dgmdate($order['submitdate']);
				$order['confirmdate'] = $order['confirmdate'] ? dgmdate($order['confirmdate']) : 'N/A';

				list($orderid, $apitype) = explode("\t", $order['buyer']);
				$apitype = $apitype ? $apitype : 'alipay';
				$orderid = '<a href="'.$orderurl[$apitype].$orderid.'" target="_blank">'.$orderid.'</a>';
				showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"validate[]\" value=\"$order[orderid]\" ".($order['status'] != 1 ? 'disabled' : '').">",
					"$order[orderid]<br />$orderid",
					$order[orderstatus],
					"<a href=\"home.php?mod=space&uid=$order[uid]\" target=\"_blank\">$order[username]</a>",
					"{$_G[setting][extcredits][$_G[setting][creditstrans]]['title']} $order[amount] {$_G[setting][extcredits][$_G[setting][creditstrans]]['unit']}",
					"$lang[rmb] $order[price] $lang[rmb_yuan]",
					$order[submitdate],
					$order[confirmdate]
				));
			}

			showsubmit('ordersubmit', 'submit', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'validate\')" /><label for="chkall">'.cplang('ec_orders_validate').'</label>', '<a href="#" onclick="$(\'orderlist\').style.display=\'none\';$(\'ordersearch\').style.display=\'\';">'.cplang('research').'</a>', $multipage);
			showtablefooter();
			showformfooter();
			showtagfooter('div');
		}

	} else {

		$numvalidate = 0;
		if($_GET['validate']) {
			$orderids = array();
			$confirmdate = dgmdate(TIMESTAMP);

			foreach(C::t('forum_order')->fetch_all($_GET['validate'], '1') as $order) {
				updatemembercount($order['uid'], array($_G['setting']['creditstrans'] => $order['amount']));
				$orderids[] = $order['orderid'];

				$submitdate = dgmdate($order['submitdate']);
				notification_add($order['uid'], 'system', 'addfunds', array(
					'orderid' => $order['orderid'],
					'price' => $order['price'],
					'from_id' => 0,
					'from_idtype' => 'buycredit',
					'value' => $_G['setting']['extcredits'][$_G['setting']['creditstrans']]['title'].' '.$order['amount'].' '.$_G['setting']['extcredits'][$_G['setting']['creditstrans']]['unit']
				), 1);
			}
			if($orderids) {
				C::t('forum_order')->update($orderids, array('status' => '3', 'admin' => $_G['username'], 'confirmdate' => $_G['timestamp']));
			}
		}

		cpmsg('orders_validate_succeed', "action=ec&operation=orders&searchsubmit=yes&orderstatus={$_GET['orderstatus']}&orderid={$_GET['orderid']}&users={$_GET['users']}&buyer={$_GET['buyer']}&admin={$_GET['admin']}&sstarttime={$_GET['sstarttime']}&sendtime={$_GET['sendtime']}&cstarttime={$_GET['cstarttime']}&cendtime={$_GET['cendtime']}", 'succeed');

	}

} elseif($operation == 'credit') {

	$defaultrank = array(
		1 => 4,
		2 => 11,
		3 => 41,
		4 => 91,
		5 => 151,
		6 => 251,
		7 => 501,
		8 => 1001,
		9 => 2001,
		10 => 5001,
		11 => 10001,
		12 => 20001,
		13 => 50001,
		14 => 100001,
		15 => 200001
	);

	if(!submitcheck('creditsubmit')) {

		$ec_credit = C::t('common_setting')->fetch('ec_credit', true);
		$ec_credit = $ec_credit ? $ec_credit : array(
			'maxcreditspermonth' => '6',
			'rank' => $defaultrank
		);

		shownav('extended', 'nav_ec');
		showsubmenu('nav_ec', array(
			array('nav_ec_config', 'setting&operation=ec', 0),
			array('nav_ec_tenpay', 'ec&operation=tenpay', 0),
			array('nav_ec_alipay', 'ec&operation=alipay', 0),
			array('nav_ec_credit', 'ec&operation=credit', 1),
			array('nav_ec_orders', 'ec&operation=orders', 0),
			array('nav_ec_tradelog', 'tradelog', 0),
			array('nav_ec_inviteorders', 'ec&operation=inviteorders', 0)
		));

		showtips('ec_credit_tips');
		showformheader('ec&operation=credit');
		showtableheader('ec_credit', 'nobottom');
		showsetting('ec_credit_maxcreditspermonth', 'ec_creditnew[maxcreditspermonth]', $ec_credit['maxcreditspermonth'], 'text');
		showtablefooter('</tbody>');

		showtableheader('ec_credit_rank', 'notop fixpadding');
		showsubtitle(array('ec_credit_rank', 'ec_credit_between', 'ec_credit_sellericon', 'ec_credit_buyericon'));

		foreach($ec_credit['rank'] as $rank => $mincredits) {
			showtablerow('', '', array(
				$rank,
				'<input type="text" class="txt" size="6" name="ec_creditnew[rank]['.$rank.']" value="'.$mincredits.'" /> ~ '.$ec_credit[rank][$rank + 1],
				"<img src=\"static/image/traderank/seller/$rank.gif\" border=\"0\">",
				"<img src=\"static/image/traderank/buyer/$rank.gif\" border=\"0\">"
			));
		}
		showsubmit('creditsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$ec_creditnew = $_GET['ec_creditnew'];
		$ec_creditnew['maxcreditspermonth'] = intval($ec_creditnew['maxcreditspermonth']);

		if(is_array($ec_creditnew['rank'])) {
			foreach($ec_creditnew['rank'] as $rank => $mincredits) {
				$mincredits = intval($mincredits);
				if($rank == 1 && $mincredits <= 0) {
					cpmsg('ecommerce_invalidcredit', '', 'error');
				} elseif($rank > 1 && $mincredits <= $ec_creditnew['rank'][$rank - 1]) {
					cpmsg('ecommerce_must_larger', '', 'error', array('rank' => $rank));
				}
				$ec_creditnew['rank'][$rank] = $mincredits;
			}
		} else {
			$ec_creditnew['rank'] = $defaultrank;
		}

		C::t('common_setting')->update('ec_credit', $ec_creditnew);
		updatecache('setting');

		cpmsg('ec_credit_succeed', 'action=ec&operation=credit', 'succeed');

	}
} elseif($operation == 'inviteorders') {
	if(!submitcheck('ordersubmit')) {
		$start_limit = ($page - 1) * $_G['tpp'];
		$orderurl = array(
			'alipay' => 'https://www.alipay.com/trade/query_trade_detail.htm?trade_no=',
			'tenpay' => 'https://www.tenpay.com/med/tradeDetail.shtml?trans_id=',
		);
		shownav('extended', 'nav_ec');
		showsubmenu('nav_ec', array(
			array('nav_ec_config', 'setting&operation=ec', 0),
			array('nav_ec_tenpay', 'ec&operation=tenpay', 0),
			array('nav_ec_alipay', 'ec&operation=alipay', 0),
			array('nav_ec_credit', 'ec&operation=credit', 0),
			array('nav_ec_orders', 'ec&operation=orders', 0),
			array('nav_ec_tradelog', 'tradelog', 0),
			array('nav_ec_inviteorders', 'ec&operation=inviteorders', 1)
		));

		$ordercount = C::t('forum_order')->count_by_search(0, $_GET['orderstatus'], $_GET['orderid'], $_GET['email']);
		$multipage = multi($ordercount, $_G['tpp'], $page, ADMINSCRIPT."?action=ec&operation=inviteorders&orderstatus={$_GET['orderstatus']}&orderid={$_GET['orderid']}&email={$_GET['email']}");

		showtagheader('div', 'orderlist', TRUE);
		showformheader('ec&operation=inviteorders');
		showtableheader('ec_inviteorders_search');
		$_G['showsetting_multirow'] = 1;
		showsetting('ec_orders_search_status', array('orderstatus', array(
			array('', $lang['ec_orders_search_status_all']),
			array(1, $lang['ec_orders_search_status_pending']),
			array(2, $lang['ec_orders_search_status_auto_finished'])
		)), intval($_GET['orderstatus']), 'select');
		showsetting('ec_orders_search_id', 'orderid', $_GET['orderid'], 'text');
		showsetting('ec_orders_search_email', 'email', $_GET['email'], 'text');
		showsubmit('searchsubmit', 'submit');
		showtablefooter();
		showtableheader('result');
		showsubtitle(array('', 'ec_orders_id', 'ec_inviteorders_status', 'ec_inviteorders_buyer', 'ec_orders_amount', 'ec_orders_price', 'ec_orders_submitdate', 'ec_orders_confirmdate'));

		foreach(C::t('forum_order')->fetch_all_by_search(0, $_GET['orderstatus'], $_GET['orderid'], $_GET['email'], null, null, null, null, null, null, null, $start_limit, $_G['tpp']) as $order) {
			switch($order['status']) {
				case 1: $order['orderstatus'] = $lang['ec_orders_search_status_pending']; break;
				case 2: $order['orderstatus'] = '<b>'.$lang['ec_orders_search_status_auto_finished'].'</b>'; break;
				case 3: $order['orderstatus'] = '<b>'.$lang['ec_orders_search_status_manual_finished'].'</b><br />(<a href="home.php?mod=space&username='.rawurlencode($order['admin']).'" target="_blank">'.$order['admin'].'</a>)'; break;
			}
			$order['submitdate'] = dgmdate($order['submitdate']);
			$order['confirmdate'] = $order['confirmdate'] ? dgmdate($order['confirmdate']) : 'N/A';

			list($orderid, $apitype) = explode("\t", $order['buyer']);
			$apitype = $apitype ? $apitype : 'alipay';
			$orderid = '<a href="'.$orderurl[$apitype].$orderid.'" target="_blank">'.$orderid.'</a>';
			showtablerow('', '', array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"validate[]\" value=\"$order[orderid]\" ".($order['status'] != 1 ? 'disabled' : '').">",
				"$order[orderid]<br />$orderid",
				$order['orderstatus'],
				"$order[email]<br>$order[ip]",
				$order['amount'],
				"$lang[rmb] $order[price] $lang[rmb_yuan]",
				$order['submitdate'],
				$order['confirmdate']
			));
		}
		showtablerow('', array('colspan="7"'), array($multipage));
		showsubmit('ordersubmit', 'ec_orders_validate', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'validate\')" />');
		showtablefooter();
		showformfooter();
		showtagfooter('div');
	} else {
		if($_GET['validate']) {
			if(C::t('forum_order')->fetch_all($_GET['validate'], '1')) {
				C::t('forum_order')->update($_GET['validate'], array('status' => '3', 'admin' => $_G['username'], 'confirmdate' => $_G['timestamp']));
			}
		}
		cpmsg('orders_validate_succeed', "action=ec&operation=inviteorders&orderstatus={$_GET['orderstatus']}&orderid={$_GET['orderid']}&email={$_GET['email']}", 'succeed');
	}
}

?>