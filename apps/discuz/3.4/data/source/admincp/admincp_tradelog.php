<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_tradelog.php 27054 2011-12-31 06:04:21Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/trade');
$language = lang('forum/misc');

cpheader();

$ppp = 20;

$start_limit = ($page - 1) * $ppp;

$filter = !isset($_GET['filter']) ? -1 : $_GET['filter'];
$count = C::t('forum_tradelog')->count_by_status($filter);

$multipage = multi($count['num'], $ppp, $page, ADMINSCRIPT."?action=tradelog&filter=$filter");


shownav('extended', 'nav_ec');
showsubmenu('nav_ec', array(
	array('nav_ec_config', 'setting&operation=ec&mod=forum', 0),
	array('nav_ec_tenpay', 'ec&operation=tenpay&mod=forum', 0),
	array('nav_ec_alipay', 'ec&operation=alipay&mod=forum', 0),
	array('nav_ec_credit', 'ec&operation=credit&mod=forum', 0),
	array('nav_ec_orders', 'ec&operation=orders&mod=forum', 0),
	array('nav_ec_tradelog', 'tradelog&mod=forum', 1),
	array('nav_ec_inviteorders', 'ec&operation=inviteorders', 0)
));
showtableheader();
showsubtitle(array('tradelog_trade_no', 'tradelog_trade_name', 'tradelog_buyer', 'tradelog_seller', 'tradelog_money', $lang['tradelog_credit']."({$_G[setting][extcredits][$_G['setting']['creditstransextra'][5]][title]})", 'tradelog_fee', 'tradelog_order_status'));

foreach(C::t('forum_tradelog')->fetch_all_by_status($filter, $start_limit, $ppp) as $tradelog) {
	$tradelog['status'] = trade_getstatus($tradelog['status']);
	$tradelog['lastupdate'] = dgmdate($tradelog['lastupdate']);
	$tradelog['tradeno'] = $tradelog['offline'] ? $lang['tradelog_offline'] : $tradelog['tradeno'];
	showtablerow('', '', array(
		$tradelog['tradeno'],
		'<a target="_blank" href="forum.php?mod=viewthread&do=tradeinfo&tid='.$tradelog['tid'].'&pid='.$tradelog['pid'].'">'.$tradelog['subject'].'</a>',
		'<a target="_blank" href="home.php?mod=space&uid='.$tradelog['buyerid'].'">'.$tradelog['buyer'].'</a>',
		'<a target="_blank" href="home.php?mod=space&uid='.$tradelog['sellerid'].'">'.$tradelog['seller'].'</a>',
		$tradelog['price'],
		$tradelog['credit'],
		$tradelog['tax'],
		'<a target="_blank" href="forum.php?mod=trade&orderid='.$tradelog['orderid'].'&tid='.$tradelog['tid'].'&modthreadkey='.modauthkey($tradelog['tid']).'">'.$tradelog['status'].'<br />'.$tradelog['lastupdate']
	));
}

$statusselect = $lang['tradelog_order_status'].': <select onchange="location.href=\''.ADMINSCRIPT.'?action=tradelog&filter=\' + this.value"><option value="-1">'.$lang['tradelog_all_order'].'</option>';
$statuss = trade_getstatus(0, -1);
foreach($statuss as $key => $value) {
	$statusselect .= "<option value=\"$key\" ".($filter == $key ? 'selected' : '').">$value</option>";
}
$statusselect .= '</select>';

showsubmit('', '', "$lang[tradelog_order_count] $count[num], $lang[tradelog_trade_total] ".intval($count['pricesum'])." $lang[rmb_yuan], $lang[tradelog_trade_totalcredit] {$_G[setting][extcredits][$_G['setting']['creditstransextra'][5]][title]} $count[creditsum] {$_G[setting][extcredits][$_G['setting']['creditstransextra'][5]][unit]}, $lang[tradelog_fee_total] ".intval($count['taxsum'])." $lang[rmb_yuan]", '', $multipage.$statusselect);
showtablefooter();

?>