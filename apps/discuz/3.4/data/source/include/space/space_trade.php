<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_trade.php 28290 2012-02-27 07:15:44Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$opactives['trade'] = 'class="a"';

if(empty($_GET['view'])) $_GET['view'] = 'we';

$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = array();
$userlist = array();
$count = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'trade',
	'view' => $_GET['view'],
	'order' => $_GET['order'],
	'type' => $_GET['type'],
	'status' => $_GET['status'],
	'fuid' => $_GET['fuid'],
	'searchkey' => $_GET['searchkey']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

$wheresql = '1';
$apply_sql = '';

$f_index = '';
$ordersql = 't.dateline DESC';
$need_count = true;

if($_GET['view'] == 'me') {

	$wheresql = "t.sellerid = '$space[uid]'";

} elseif($_GET['view'] == 'tradelog') {

	$viewtype = in_array($_GET['type'], array('sell', 'buy')) ? $_GET['type'] : 'sell';
	$filter = $_GET['filter'] ? $_GET['filter'] : 'all';
	$sqlfield = $viewtype == 'sell' ? 'sellerid' : 'buyerid';
	$sqlfilter = '';
	$ratestatus = 0;
	$item = $viewtype == 'sell' ? 'selltrades' : 'buytrades';

	switch($filter) {
		case 'attention':
			$typestatus = $item; break;
		case 'eccredit'	:
			$typestatus = 'eccredittrades';
			$ratestatus = $item == 'selltrades' ? 1 : 2;
			break;
		case 'all':
			$typestatus = ''; break;
		case 'success':
			$typestatus = 'successtrades'; break;
		case 'closed'	:
			$typestatus = 'closedtrades'; break;
		case 'refund'	:
			$typestatus = 'refundtrades'; break;
		case 'unstart'	:
			$typestatus = 'unstarttrades'; break;
		default:
			$typestatus = 'tradingtrades';
			break;
	}
	require_once libfile('function/trade');

	$typestatus = $typestatus ? trade_typestatus($typestatus) : array();

	$srchkey = stripsearchkey($_GET['searchkey']);


	$tid = intval($_GET['tid']);
	$pid = intval($_GET['pid']);
	$sqltid = $tid ? 'tl.tid=\''.$tid.'\' AND '.($pid ? 'tl.pid=\''.$pid.'\' AND ' : '') : '';
	$extra .= $srchfid ? '&amp;filter='.$filter : '';
	$extratid = $tid ? "&amp;tid=$tid".($pid ? "&amp;pid=$pid" : '') : '';
	$num = C::t('forum_tradelog')->count_log($viewtype, $_G['uid'], $tid, $pid, $ratestatus, $typestatus);

	$multi = multi($num, $perpage, $page, $theurl);
	$tradeloglist = array();
	foreach(C::t('forum_tradelog')->fetch_all_log($viewtype, $_G['uid'], $tid, $pid, $ratestatus, $typestatus, $start, $perpage) as $tradelog) {
		$tradelog['lastupdate'] = dgmdate($tradelog['lastupdate'], 'u', 1);
		$tradelog['attend'] = trade_typestatus($item, $tradelog['status']);
		$tradelog['status'] = trade_getstatus($tradelog['status']);
		$tradeloglist[] = $tradelog;
	}
	$creditid = 0;
	if($_G['setting']['creditstransextra'][5]) {
		$creditid = intval($_G['setting']['creditstransextra'][5]);
	} elseif ($_G['setting']['creditstrans']) {
		$creditid = intval($_G['setting']['creditstrans']);
	}
	$extcredits = $_G['setting']['extcredits'];
	$orderactives = array($viewtype => ' class="a"');
	$need_count = false;

} elseif($_GET['view'] == 'eccredit') {

	require_once libfile('function/ec_credit');
	$uid = !empty($_GET['uid']) ? intval($_GET['uid']) : $_G['uid'];

	loadcache('usergroups');

	$member = getuserbyuid($uid);
	if(!$member) {
		showmessage('member_nonexistence', NULL, array(), array('login' => 1));
	}
	$member = array_merge($member, C::t('common_member_profile')->fetch($uid), C::t('common_member_status')->fetch($uid), C::t('common_member_field_forum')->fetch($uid));
	$member['avatar'] = '<div class="avatar">'.avatar($member['uid']);
	if($_G['cache']['usergroups'][$member['groupid']]['groupavatar']) {
		$member['avatar'] .= '<br /><img src="'.$_G['cache']['usergroups'][$member['groupid']]['groupavatar'].'" border="0" alt="" />';
	}
	$member['avatar'] .= '</div>';

	$member['taobaoas'] = str_replace("'", '', addslashes($member['taobao']));
	$member['regdate'] = dgmdate($member['regdate'], 'd');
	$member['usernameenc'] = rawurlencode($member['username']);
	$member['buyerrank'] = 0;
	if($member['buyercredit']){
		foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
			if($member['buyercredit'] <= $credit) {
				$member['buyerrank'] = $level;
				break;
			}
		}
	}
	$member['sellerrank'] = 0;
	if($member['sellercredit']){
		foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
			if($member['sellercredit'] <= $credit) {
				$member['sellerrank'] = $level;
				break;
			}
		}
	}

	$caches = array();
	foreach(C::t('forum_spacecache')->fetch_all($uid, array('buyercredit', 'sellercredit')) as $cache) {
		$caches[$cache['variable']] = dunserialize($cache['value']);
		$caches[$cache['variable']]['expiration'] = $cache['expiration'];
	}

	foreach(array('buyercredit', 'sellercredit') AS $type) {
		if(!isset($caches[$type]) || TIMESTAMP > $caches[$type]['expiration']) {
			$caches[$type] = updatecreditcache($uid, $type, 1);
		}
	}
	@$buyerpercent = $caches['buyercredit']['all']['total'] ? sprintf('%0.2f', $caches['buyercredit']['all']['good'] * 100 / $caches['buyercredit']['all']['total']) : 0;
	@$sellerpercent = $caches['sellercredit']['all']['total'] ? sprintf('%0.2f', $caches['sellercredit']['all']['good'] * 100 / $caches['sellercredit']['all']['total']) : 0;
	$need_count = false;

	include template('home/space_eccredit');
	exit;

} elseif($_GET['view'] == 'onlyuser') {
	$uid = !empty($_GET['uid']) ? intval($_GET['uid']) : $_G['uid'];
	$wheresql = "t.sellerid = '$uid'";
} else {

	space_merge($space, 'field_home');

	if($space['feedfriend']) {

		$fuid_actives = array();

		require_once libfile('function/friend');
		$fuid = intval($_GET['fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$wheresql = 't.'.DB::field('sellerid', $fuid);
			$fuid_actives = array($fuid=>' selected');
		} else {
			$wheresql = 't.'.DB::field('sellerid', $space['feedfriend']);
			$theurl = "home.php?mod=space&uid=$space[uid]&do=$do&view=we";
		}

		$query = C::t('home_friend')->fetch_all_by_uid($space['uid'], 0, 100, true);
		foreach($query as $value) {
			$userlist[] = $value;
		}

	} else {
		$need_count = false;
	}
}

$actives = array($_GET['view'] =>' class="a"');

if($need_count) {
	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$wheresql .= ' AND t.'.DB::field('subject', '%'.$searchkey.'%', 'like');
	}
	$havecache = false;

	$count = C::t('forum_trade')->fetch_all_for_space($wheresql, '', 1);
	if($count) {
		$query = C::t('forum_trade')->fetch_all_for_space($wheresql, $ordersql, 0, $start, $perpage);
		$pids = $aids = $thidden = array();
		foreach($query as $value) {
			$aids[$value['aid']] = $value['aid'];
			$value['dateline'] = dgmdate($value['dateline']);
			$pids[] = (float)$value['pid'];
			$list[$value['pid']] = $value;
		}


		$multi = multi($count, $perpage, $page, $theurl);
	}

}

if($count) {
	$emptyli = array();
	if(count($list) % 5 != 0) {
		for($i = 0; $i < 5 - count($list) % 5; $i++) {
			$emptyli[] = $i;
		}
	}
}

if($_G['uid']) {
	$_GET['view'] = !$_GET['view'] ? 'we' : $_GET['view'];
	$navtitle = lang('core', 'title_'.$_GET['view'].'_trade');
	if($navtitle == 'title_'.$_GET['view'].'_trade') {
		$navtitle = lang('core', 'title_trade');
	}
} else {
	$navtitle = lang('core', 'title_trade');
}

include_once template("diy:home/space_trade");

?>