<?php

/*
	[Discuz!] (C)2001-2099 Comsenz Inc111.
	This is NOT a freeware, use is subject to license terms

	$Id: home_magic.php 33875 2013-08-26 07:33:49Z andyzheng $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

if(!$_G['setting']['creditstransextra'][3]) {
	showmessage('credits_transaction_disabled');
} elseif(!$_G['setting']['magicstatus']) {
	showmessage('magics_close');
}

require_once libfile('function/magic');
loadcache('magics');

$_G['mnid'] = 'mn_common';
$magiclist = array();
$_G['tpp'] = 12;
$page = max(1, intval($_GET['page']));
$action = $_GET['action'];
$operation = $_GET['operation'];
$start_limit = ($page - 1) * $_G['tpp'];
$_GET['idtype'] = dhtmlspecialchars($_GET['idtype']);

$comma = $typeadd = $filteradd = $forumperm = $targetgroupperm = '';
$magicarray = is_array($_G['cache']['magics']) ? $_G['cache']['magics'] : array();

if(!$_G['uid'] && ($operation || $action == 'mybox')) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

if(!$_G['group']['allowmagics']) {
	showmessage('magics_nopermission');
}

$totalweight = getmagicweight($_G['uid'], $magicarray);
$allowweight = $_G['group']['maxmagicsweight'] - $totalweight;
$location = 0;

if(empty($action) && !empty($_GET['mid'])) {
	$_GET['magicid'] = C::t('common_member_magic')->fetch_magicid_by_identifier($_G['uid'], $_GET['mid']);
	if(!$_GET['magicid']) {
		$magic = C::t('common_magic')->fetch_by_identifier($_GET['mid']);
		if(!$magic['price'] && $magic['num']) {
			getmagic($magic['magicid'], 1, $magic['weight'], $totalweight, $_G['uid'], $_G['group']['maxmagicsweight']);
			updatemagiclog($magic['magicid'], '1', 1, $magic['price'].'|'.$magic['credit'], $_G['uid']);

			C::t('common_magic')->update_salevolume($magic['magicid'], 1);
			updatemembercount($_G['uid'], array($magic['credit'] => -0), true, 'BMC', $magic['magicid']);
			$_GET['magicid'] = $magic['magicid'];
		}
	}
	if($_GET['magicid']) {
		$action = 'mybox';
		$operation = 'use';
	} else {
		$action = 'shop';
		$operation = 'buy';
		$location = 1;
	}
}

$action = empty($action) ? 'shop' : $action;
$actives[$action] = ' class="a"';

if($action == 'shop') {

	$operation = empty($operation) ? 'index' : $operation;

	if(in_array($operation, array('index', 'hot'))) {

		$subactives[$operation] = 'class="a"';
		$filteradd = '';
		if($operation == 'index') {
			$navtitle = lang('core', 'title_magics_shop');
		} else {
			$navtitle = lang('core', 'title_magics_hot');
		}

		$magiccount = C::t('common_magic')->count_page($operation);
		$multipage = multi($magiccount, $_G['tpp'], $page, "home.php?mod=magic&action=shop&operation=$operation");

		foreach(C::t('common_magic')->fetch_all_page($operation, $start_limit, $_G['tpp']) as $magic) {
			$magic['discountprice'] = $_G['group']['magicsdiscount'] ? intval($magic['price'] * ($_G['group']['magicsdiscount'] / 10)) : intval($magic['price']);
			$eidentifier = explode(':', $magic['identifier']);
			if(count($eidentifier) > 1) {
				$magic['pic'] = 'source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.gif';
			} else {
				$magic['pic'] = STATICURL.'image/magic/'.strtolower($magic['identifier']).'.gif';
			}
			$magiclist[] = $magic;
		}

		$magiccredits = array();
		foreach($magicarray as $magic) {
			$magiccredits[$magic['credit']] = $magic['credit'];
		}

	} elseif($operation == 'buy') {

		$magic = C::t('common_magic')->fetch_by_identifier($_GET['mid']);
		if(!$magic || !$magic['available']) {
			showmessage('magics_nonexistence');
		}
		$magicperm = dunserialize($magic['magicperm']);
		$useperm = (strstr($magicperm['usergroups'], "\t$_G[groupid]\t") || empty($magicperm['usergroups'])) ? '1' : '0';
		if(!$useperm) {
			showmessage('magics_use_nopermission');
		}
		$querystring = array();
		foreach($_GET as $k => $v) {
			$querystring[] = rawurlencode($k).'='.rawurlencode($v);
		}
		$querystring = implode('&', $querystring);

		$eidentifier = explode(':', $magic['identifier']);
		if(count($eidentifier) > 1) {
			$magicfile = './source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.php';
			$magicclass = 'magic_'.$eidentifier[1];
		} else {
			$magicfile = './source/class/magic/magic_'.$magic['identifier'].'.php';
			$magicclass = 'magic_'.$magic['identifier'];
		}

		if(!@include_once DISCUZ_ROOT.$magicfile) {
			showmessage('magics_filename_nonexistence', '', array('file' => $magicfile));
		}
		$magicclass = new $magicclass;
		$magicclass->magic = $magic;
		$magicclass->parameters = $magicperm;
		if(method_exists($magicclass, 'buy')) {
			$magicclass->buy();
		}

		$magic['discountprice'] = $_G['group']['magicsdiscount'] ? intval($magic['price'] * ($_G['group']['magicsdiscount'] / 10)) : intval($magic['price']);
		if(count($eidentifier) > 1) {
			$magic['pic'] = 'source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.gif';
		} else {
			$magic['pic'] = STATICURL.'image/magic/'.strtolower($magic['identifier']).'.gif';
		}
		$magic['credit'] = $magic['credit'] ? $magic['credit'] : $_G['setting']['creditstransextra'][3];
		$useperoid = magic_peroid($magic, $_G['uid']);

		if(!submitcheck('operatesubmit')) {

			if($magicperm['targetgroups']) {
				loadcache('usergroups');
				foreach(explode("\t", $magicperm['targetgroups']) as $_G['groupid']) {
					if(isset($_G['cache']['usergroups'][$_G['groupid']])) {
						$targetgroupperm .= $comma.$_G['cache']['usergroups'][$_G['groupid']]['grouptitle'];
						$comma = '&nbsp;';
					}
				}
			}

			if($magicperm['forum']) {
				loadcache('forums');
				foreach(explode("\t", $magicperm['forum']) as $fid) {
					if(isset($_G['cache']['forums'][$fid])) {
						$forumperm .= $comma.'<a href="forum.php?mod=forumdisplay&fid='.$fid.'" target="_blank">'.$_G['cache']['forums'][$fid]['name'].'</a>';
						$comma = '&nbsp;';
					}
				}
			}

			include template('home/space_magic_shop_opreation');
			dexit();

		} else {

			$magicnum = intval($_GET['magicnum']);
			$magic['weight'] = $magic['weight'] * $magicnum;
			$totalprice = $magic['discountprice'] * $magicnum;

			if(getuserprofile('extcredits'.$magic['credit']) < $totalprice) {
				if($_G['setting']['ec_ratio'] && $_G['setting']['creditstrans'][0] == $magic['credit']) {
					showmessage('magics_credits_no_enough_and_charge', '', array('credit' => $_G['setting']['extcredits'][$magic['credit']]['title']));
				} else {
					showmessage('magics_credits_no_enough', '', array('credit' => $_G['setting']['extcredits'][$magic['credit']]['title']));
				}
			} elseif($magic['num'] < $magicnum) {
				showmessage('magics_num_no_enough');
			} elseif(!$magicnum || $magicnum < 0) {
				showmessage('magics_num_invalid');
			}

			getmagic($magic['magicid'], $magicnum, $magic['weight'], $totalweight, $_G['uid'], $_G['group']['maxmagicsweight']);
			updatemagiclog($magic['magicid'], '1', $magicnum, $magic['price'].'|'.$magic['credit'], $_G['uid']);

			C::t('common_magic')->update_salevolume($magic['magicid'], $magicnum);
			updatemembercount($_G['uid'], array($magic['credit'] => -$totalprice), true, 'BMC', $magic['magicid']);
			showmessage('magics_buy_succeed', 'home.php?mod=magic&action=mybox', array('magicname' => $magic['name'], 'num' => $magicnum, 'credit' => $totalprice.' '.$_G['setting']['extcredits'][$magic['credit']]['unit'].$_G['setting']['extcredits'][$magic['credit']]['title']));


		}

	} elseif($operation == 'give') {

		if($_G['group']['allowmagics'] < 2) {
			showmessage('magics_nopermission');
		}

		$magic = C::t('common_magic')->fetch_by_identifier($_GET['mid']);
		if(!$magic || !$magic['available']) {
			showmessage('magics_nonexistence');
		}

		$magic['discountprice'] = $_G['group']['magicsdiscount'] ? intval($magic['price'] * ($_G['group']['magicsdiscount'] / 10)) : intval($magic['price']);
		$eidentifier = explode(':', $magic['identifier']);
		if(count($eidentifier) > 1) {
			$magic['pic'] = 'source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.gif';
		} else {
			$magic['pic'] = STATICURL.'image/magic/'.strtolower($magic['identifier']).'.gif';
		}

		if(!submitcheck('operatesubmit')) {

			include libfile('function/friend');
			$buddyarray = friend_list($_G['uid'], 20);
			include template('home/space_magic_shop_opreation');
			dexit();

		} else {

			$magicnum = intval($_GET['magicnum']);
			$totalprice = $magic['price'] * $magicnum;

			if(getuserprofile('extcredits'.$magic['credit']) < $totalprice) {
				if($_G['setting']['ec_ratio'] && $_G['setting']['creditstrans'][0] == $magic['credit']) {
					showmessage('magics_credits_no_enough_and_charge', '', array('credit' => $_G['setting']['extcredits'][$magic['credit']]['title']));
				} else {
					showmessage('magics_credits_no_enough', '', array('credit' => $_G['setting']['extcredits'][$magic['credit']]['title']));
				}
			} elseif($magic['num'] < $magicnum) {
				showmessage('magics_num_no_enough');
			} elseif(!$magicnum || $magicnum < 0) {
				showmessage('magics_num_invalid');
			}

			$toname = dhtmlspecialchars(trim($_GET['tousername']));
			if(!$toname) {
				showmessage('magics_username_nonexistence');
			}

			$givemessage = dhtmlspecialchars(trim($_GET['givemessage']));
			givemagic($toname, $magic['magicid'], $magicnum, $magic['num'], $totalprice, $givemessage, $magicarray);
			C::t('common_magic')->update_salevolume($magic['magicid'], $magicnum);
			updatemembercount($_G['uid'], array($magic['credit'] => -$totalprice), true, 'BMC', $magicid);
			showmessage('magics_buygive_succeed', 'home.php?mod=magic&action=shop', array('magicname' => $magic['name'], 'toname' => $toname, 'num' => $magicnum, 'credit' => $_G['setting']['extcredits'][$magic['credit']]['title'].' '.$totalprice.' '.$_G['setting']['extcredits'][$magic['credit']]['unit']), array('locationtime' => true));

		}

	} else {
		showmessage('undefined_action');
	}

} elseif($action == 'mybox') {

	if(empty($operation)) {

		$pid = !empty($_GET['pid']) ? intval($_GET['pid']) : 0;
		$magiccount = C::t('common_member_magic')->count_by_uid($_G['uid']);

		$multipage = multi($magiccount, $_G['tpp'], $page, "home.php?mod=magic&action=mybox&pid=$pid$typeadd");
		$query = C::t('common_member_magic')->fetch_all($_G['uid'], null, $start_limit, $_G['tpp']);
		foreach($query as $value) {
			$magicids[] = $value['magicid'];
		}
		$magicm = C::t('common_magic')->fetch_all($magicids);
		foreach($query as $curmagicid => $mymagic) {
			$mymagic = $mymagic + $magicm[$mymagic['magicid']];
			$eidentifier = explode(':', $mymagic['identifier']);
			if(count($eidentifier) > 1) {
				$mymagic['pic'] = 'source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.gif';
			} else {
				$mymagic['pic'] = STATICURL.'image/magic/'.strtolower($mymagic['identifier']).'.gif';
			}
			$mymagic['weight'] = intval($mymagic['weight'] * $mymagic['num']);
			$mymagic['type'] = $mymagic['type'];
			$mymagiclist[] = $mymagic;
		}
		$navtitle = lang('core', 'title_magics_user');

	} else {

		$magicid = intval($_GET['magicid']);
		$membermagic = C::t('common_member_magic')->fetch($_G['uid'], $magicid);
		$magic = $membermagic +	C::t('common_magic')->fetch($magicid);

		if(!$membermagic) {
			showmessage('magics_nonexistence');
		} elseif(!$magic['num']) {
			C::t('common_member_magic')->delete($_G['uid'], $magic['magicid']);
			showmessage('magics_nonexistence');
		}
		$magicperm = dunserialize($magic['magicperm']);
		$eidentifier = explode(':', $magic['identifier']);
		if(count($eidentifier) > 1) {
			$magic['pic'] = 'source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.gif';
		} else {
			$magic['pic'] = STATICURL.'image/magic/'.strtolower($magic['identifier']).'.gif';
		}

		if($operation == 'use') {

			$useperm = (strstr($magicperm['usergroups'], "\t$_G[groupid]\t") || empty($magicperm['usergroups'])) ? '1' : '0';
			if(!$useperm) {
				showmessage('magics_use_nopermission');
			}

			if($magic['num'] <= 0) {
				C::t('common_member_magic')->delete($_G['uid'], $magic['magicid']);
				showmessage('magics_nopermission');
			}

			$magic['weight'] = intval($magicarray[$magic['magicid']]['weight'] * $magic['num']);

			if(count($eidentifier) > 1) {
				$magicfile = './source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.php';
				$magicclass = 'magic_'.$eidentifier[1];
			} else {
				$magicfile = './source/class/magic/magic_'.$magic['identifier'].'.php';
				$magicclass = 'magic_'.$magic['identifier'];
			}

			if(!@include_once DISCUZ_ROOT.$magicfile) {
				showmessage('magics_filename_nonexistence', '', array('file' => $magicfile));
			}
			$magicclass = new $magicclass;
			$magicclass->magic = $magic;
			$magicclass->parameters = $magicperm;
			$useperoid = magic_peroid($magic, $_G['uid']);

			if(submitcheck('usesubmit')) {
				if(discuz_process::islocked('magiclock_'.$_G['uid'].'_'.$magicid, 0, 1)) {
					showmessage('magics_locked');
				}
				if($useperoid !== true && $useperoid <= 0) {
					showmessage('magics_outofperoid_'.$magic['useperoid'], '', array('usenum' => $magic['usenum']));
				}
				if(method_exists($magicclass, 'usesubmit')) {
					$magicclass->usesubmit();
				}
				dexit();
			}

			include template('home/space_magic_mybox_opreation');
			dexit();

		} elseif($operation == 'sell') {
			$magic['price'] = $_G['group']['magicsdiscount'] ? intval($magic['price'] * ($_G['group']['magicsdiscount'] / 10)) : intval($magic['price']);
			$discountprice = floor($magic['price'] * $_G['setting']['magicdiscount'] / 100);
			if(!submitcheck('operatesubmit')) {
				include template('home/space_magic_mybox_opreation');
				dexit();
			} else {
				if(discuz_process::islocked('magiclock_'.$_G['uid'].'_'.$magicid, 0, 1)) {
					showmessage('magics_locked');
				}

				$magicnum = intval($_GET['magicnum']);

				if(!$magicnum || $magicnum < 0) {
					showmessage('magics_num_invalid');
				} elseif($magicnum > $magic['num']) {
					showmessage('magics_amount_no_enough');
				}
				usemagic($magic['magicid'], $magic['num'], $magicnum);
				updatemagiclog($magic['magicid'], '2', $magicnum, '0', 0, 'sell');
				$totalprice = $discountprice * $magicnum;
				updatemembercount($_G['uid'], array($magic['credit'] => $totalprice));
				showmessage('magics_sell_succeed', 'home.php?mod=magic&action=mybox', array('magicname' => $magic['name'], 'num' => $magicnum, 'credit' => $totalprice.' '.$_G['setting']['extcredits'][$magic['credit']]['unit'].$_G['setting']['extcredits'][$magic['credit']]['title']));
			}

		} elseif($operation == 'drop') {

			if(!submitcheck('operatesubmit')) {
				include template('home/space_magic_mybox_opreation');
				dexit();
			} else {
				$magicnum = intval($_GET['magicnum']);

				if(!$magicnum || $magicnum < 0) {
					showmessage('magics_num_invalid');
				} elseif($magicnum > $magic['num']) {
					showmessage('magics_amount_no_enough');
				}
				usemagic($magic['magicid'], $magic['num'], $magicnum);
				updatemagiclog($magic['magicid'], '2', $magicnum, '0', 0, 'drop');
				showmessage('magics_drop_succeed', 'home.php?mod=magic&action=mybox', array('magicname' => $magic['name'], 'num' => $magicnum), array('locationtime' => true));
			}

		} elseif($operation == 'give') {

			if($_G['group']['allowmagics'] < 2) {
				showmessage('magics_nopermission');
			}

			if(!submitcheck('operatesubmit')) {

				include libfile('function/friend');
				$buddyarray = friend_list($_G['uid'], 20);

				include template('home/space_magic_mybox_opreation');
				dexit();

			} else {
				if($_G['setting']['submitlock'] && discuz_process::islocked('magiclock_'.$_G['uid'].'_'.$magicid, 0, 1)) {
					showmessage('magics_locked');
				}
				$magicnum = intval($_GET['magicnum']);
				$toname = dhtmlspecialchars(trim($_GET['tousername']));
				if(!$toname) {
					showmessage('magics_username_nonexistence');
				} elseif($magicnum < 0 || $magic['num'] < $magicnum) {
					showmessage('magics_num_invalid');
				}

				$givemessage = dhtmlspecialchars(trim($_GET['givemessage']));
				givemagic($toname, $magic['magicid'], $magicnum, $magic['num'], '0', $givemessage, $magicarray);

			}

		} else {
			showmessage('undefined_action');
		}

	}

} elseif($action == 'log') {

	$subactives[$operation] = 'class="a"';
	$loglist = array();
	if($operation == 'uselog') {
		$count = C::t('common_magiclog')->count_by_uid_action($_G['uid'], 2);
		if($count) {
			$multipage = multi($count, $_G['tpp'], $page, 'home.php?mod=magic&action=log&amp;operation=uselog');

			$logs = C::t('common_magiclog')->fetch_all_by_uid_action($_G['uid'], 2, $start_limit, $_G['tpp']);
			$luids=array();
			foreach($luids as $log) {
				$luids[$log['uid']] = $log['uid'];
			}
			foreach($logs as $log) {
				$log['dateline'] = dgmdate($log['dateline'], 'u');
				$log['name'] = $magicarray[$log['magicid']]['name'];
				$loglist[] = $log;
			}
		}

	} elseif($operation == 'buylog') {
		$count = C::t('common_magiclog')->count_by_uid_action($_G['uid'], 1);
		if($count) {
			$multipage = multi($count, $_G['tpp'], $page, 'home.php?mod=magic&action=log&amp;operation=buylog');

			foreach(C::t('common_magiclog')->fetch_all_by_uid_action($_G['uid'], 1, $start_limit, $_G['tpp']) as $log) {
				$log['credit'] = $log['credit'] ? $log['credit'] : $_G['setting']['creditstransextra'][3];
				$log['dateline'] = dgmdate($log['dateline'], 'u');
				$log['name'] = $magicarray[$log['magicid']]['name'];
				$loglist[] = $log;
			}
		}

	} elseif($operation == 'givelog') {
		$count = C::t('common_magiclog')->count_by_uid_action($_G['uid'], 3);
		if($count) {
			$multipage = multi($count, $_G['tpp'], $page, 'home.php?mod=magic&action=log&amp;operation=givelog');

			$uids = null;
			$query = C::t('common_magiclog')->fetch_all_by_uid_action($_G['uid'], 3, $start_limit, $_G['tpp']);
			foreach($query as $log) {
				$uids[] = $log['targetuid'];
			}
			if($uids != null) {
				$memberdata = C::t('common_member')->fetch_all_username_by_uid($uids);
			}
			foreach($query as $log) {
				$log['username'] = $memberdata[$log['targetuid']];
				$log['dateline'] = dgmdate($log['dateline'], 'u');
				$log['name'] = $magicarray[$log['magicid']]['name'];
				$loglist[] = $log;
			}
		}

	} elseif($operation == 'receivelog') {
		$count = C::t('common_magiclog')->count_by_targetuid_action($_G['uid'], 3);
		if($count) {
			$multipage = multi($count, $_G['tpp'], $page, 'home.php?mod=magic&action=log&amp;operation=receivelog');

			$logs = C::t('common_magiclog')->fetch_all_by_targetuid_action($_G['uid'], 3, $start_limit, $_G['tpp']);
			$luids = array();
			foreach($logs as $log) {
				$luids[$log['uid']] = $log['uid'];
			}
			$members = C::t('common_member')->fetch_all_username_by_uid($luids);

			foreach($logs as $log) {
				$log['username'] = $members[$log['uid']];
				$log['dateline'] = dgmdate($log['dateline'], 'u');
				$log['name'] = $magicarray[$log['magicid']]['name'];
				$loglist[] = $log;
			}
		}
	}
	$navtitle = lang('core', 'title_magics_log');

} else {
	showmessage('undefined_action');
}

include template('home/space_magic');

?>