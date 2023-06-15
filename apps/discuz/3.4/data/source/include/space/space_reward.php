<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_reward.php 33848 2013-08-21 06:24:53Z hypowang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$_GET['flag'] = empty($_GET['flag']) ? 0 : intval($_GET['flag']);
$_GET['fuid'] = empty($_GET['fuid']) ? 0 : intval($_GET['fuid']);
$opactives['reward'] = 'class="a"';

$_GET['view'] = in_array($_GET['view'], array('we', 'me', 'all')) ? $_GET['view'] : 'we';

$_GET['order'] = preg_replace("/[^\[A-Za-z0-9_\]]/", '', $_GET['order']);

$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = $userlist = array();
$hiddennum = $count = $pricount = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'reward',
	'view' => $_GET['view'],
	'order' => $_GET['order'],
	'flag' => $_GET['flag'],
	'type' => $_GET['type'],
	'fuid' => $_GET['fuid'],
	'searchkey' => $_GET['searchkey']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

$conditions['special'] = 3;
$conditions['specialthread'] = 1;

$f_index = '';
$ordersql = 't.dateline DESC';
$need_count = true;
require_once libfile('function/misc');
if($_GET['view'] == 'me') {
	$conditions = array('authorid' => $space['uid'], 'special' => 3, 'specialthread' => 1);
} else {

	space_merge($space, 'field_home');
	if($space['feedfriend']) {
		$fuid_actives = array();
		require_once libfile('function/friend');
		$fuid = intval($_GET['fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$conditions = array('authorid' => $fuid, 'special' => 3, 'specialthread' => 1);
			$fuid_actives = array($fuid=>' selected');
		} else {
			$conditions['authorid'] = explode(',', $space['feedfriend']);
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

	if($_GET['view'] != 'me') {
		$conditions['sticky'] = 0;
	}
	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$conditions['keywords'] = $searchkey;
		$searchkey = dhtmlspecialchars($searchkey);
	}

	if($_GET['flag'] < 0) {
		$wheresql .= " AND t.price < '0'";
		$conditions['pricesless'] = 0;
		$alltype .= '1';
	} elseif($_GET['flag'] > 0) {
		$wheresql .= " AND t.price > '0'";
		$conditions['pricemore'] = 0;
		$alltype .= '0';
	}


	$count = C::t('forum_thread')->count_search($conditions);
	if($count) {

		foreach(C::t('forum_thread')->fetch_all_search($conditions, 0, $start, $perpage, 'dateline') as $value) {
			if(empty($value['author']) && $value['authorid'] != $_G['uid']) {
				$hiddennum++;
				continue;
			}
			$list[] = procthread($value);
		}
		$multi = multi($count, $perpage, $page, $theurl);

	}

}

$creditid = 0;
if($_G['setting']['creditstransextra'][2]) {
	$creditid = intval($_G['setting']['creditstransextra'][2]);
} elseif ($_G['setting']['creditstrans']) {
	$creditid = intval($_G['setting']['creditstrans']);
}

if($_G['uid']) {
	$_GET['view'] = !$_GET['view'] ? 'we' : $_GET['view'];
	$navtitle = lang('core', 'title_'.$_GET['view'].'_reward');
} else {
	$_GET['order'] = !$_GET['order'] ? 'dateline' : $_GET['order'];
	$navtitle = lang('core', 'title_'.$_GET['order'].'_reward');
}

include_once template("diy:home/space_reward");

?>