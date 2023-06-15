<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_home.php 30780 2012-06-19 06:01:52Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid'] && $_G['setting']['privacy']['view']['home']) {
	showmessage('home_no_privilege', '', array(), array('login' => true));
}
require_once libfile('function/feed');

if(empty($_G['setting']['feedhotday'])) {
	$_G['setting']['feedhotday'] = 2;
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];

space_merge($space, 'count');

if(empty($_GET['view'])) {
	if($space['self']) {
		if($_G['setting']['showallfriendnum'] && $space['friends'] < $_G['setting']['showallfriendnum']) {
			$_GET['view'] = 'all';
		} else {
			$_GET['view'] = 'we';
		}
	} else {
		$_GET['view'] = 'all';
	}
} elseif(!in_array($_GET['view'], array('we', 'me', 'all', 'app'))) {
	$_GET['view'] = 'all';
}
if(empty($_GET['order'])) {
	$_GET['order'] = 'dateline';
}

$perpage = $_G['setting']['feedmaxnum']<20?20:$_G['setting']['feedmaxnum'];
$perpage = mob_perpage($perpage);

if($_GET['view'] == 'all' && $_GET['order'] == 'hot') {
	$perpage = 50;
}

$page = intval($_GET['page']);
if($page < 1) $page = 1;
$start = ($page-1)*$perpage;

ckstart($start, $perpage);

$_G['home_today'] = $_G['timestamp'] - ($_G['timestamp'] + $_G['setting']['timeoffset'] * 3600) % 86400;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'home',
	'view' => $_GET['view'],
	'order' => $_GET['order'],
	'appid' => $_GET['appid'],
	'type' => $_GET['type'],
	'icon' => $_GET['icon']
);
$theurl = 'home.php?'.url_implode($gets);
$hotlist = array();
if(!IS_ROBOT) {
	$feed_users = $feed_list = $user_list = $filter_list  = $list = $magic = array();
	if($_GET['view'] != 'app') {
		if($space['self'] && empty($start) && $_G['setting']['feedhotnum'] > 0 && ($_GET['view'] == 'we' || $_GET['view'] == 'all')) {
			$hotlist_all = array();
			$hotstarttime = $_G['timestamp'] - $_G['setting']['feedhotday']*3600*24;
			$query = C::t('home_feed')->fetch_all_by_hot($hotstarttime);
			foreach ($query as $value) {
				if($value['hot']>0 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
					if(empty($hotlist)) {
						$hotlist[$value['feedid']] = $value;
					} else {
						$hotlist_all[$value['feedid']] = $value;
					}
				}
			}
			$nexthotnum = $_G['setting']['feedhotnum'] - 1;
			if($nexthotnum > 0) {
				if(count($hotlist_all)> $nexthotnum) {
					$hotlist_key = array_rand($hotlist_all, $nexthotnum);
					if($nexthotnum == 1) {
						$hotlist[$hotlist_key] = $hotlist_all[$hotlist_key];
					} else {
						foreach ($hotlist_key as $key) {
							$hotlist[$key] = $hotlist_all[$key];
						}
					}
				} else {
					$hotlist = array_merge($hotlist, $hotlist_all);
				}
			}
		}
	}

	$need_count = true;
	$uids = array();
	$multi = $hot = '';

	if($_GET['view'] == 'all') {

		if($_GET['order'] == 'dateline') {
			$ordersql = "dateline DESC";
			$f_index = '';
			$findex = '';
			$orderactives = array('dateline' => ' class="a"');
		} else {
			$hot = $minhot;
			$ordersql = "dateline DESC";
			$f_index = '';
			$findex = '';
			$orderactives = array('hot' => ' class="a"');
		}

	} elseif($_GET['view'] == 'me') {

		$uids = array($space['uid']);
		$ordersql = "dateline DESC";
		$f_index = '';
		$findex = '';

		$diymode = 1;
		if($space['self'] && $_GET['from'] != 'space') $diymode = 0;

	} elseif($_GET['view'] == 'app' && $_G['setting']['my_app_status']) {

		$uids = null;
		if ($_GET['type'] == 'all') {

			$ordersql = "dateline DESC";
			$f_index = '';
			$findex = '';

		} else {


			if($_GET['type'] == 'me') {
				$uids = $_G['uid'];
				$ordersql = "dateline DESC";
				$f_index = '';
				$findex = '';

			} else {
				$uids = array_merge(explode(',', $space['feedfriend']), 0);
				$ordersql = "dateline DESC";
				$f_index = 'USE INDEX(dateline)';
				$findex = 'dateline';
				$_GET['type'] = 'we';
				$_G['home_tpl_hidden_time'] = 1;
			}
		}

		$icon = empty($_GET['icon'])?'':trim($_GET['icon']);

		$feed_list = $appfeed_list = $hiddenfeed_list = $filter_list = $hiddenfeed_num = $icon_num = array();
		$count = $filtercount = 0;
		foreach(C::t('home_feed_app')->fetch_all_by_uid_icon($uids, $icon, $start, $perpage) as $value) {
			$feed_list[$value['icon']][] = $value;
			$count++;
		}
		$multi = simplepage($count, $perpage, $page, $theurl);
		require_once libfile('function/feed');

		$list = array();
		foreach ($feed_list as $key => $values) {
			$nowcount = 0;
			foreach ($values as $value) {
				$value = mkfeed($value);
				$nowcount++;
				if($nowcount>5 && empty($icon)) {
					break;
				}
				$list[$key][] = $value;
			}
		}
		$need_count = false;
		$typeactives = array($_GET['type'] => ' class="a"');

	} else {

		space_merge($space, 'field_home');

		if(empty($space['feedfriend'])) {
			$need_count = false;
		} else {
			$uids = array_merge(explode(',', $space['feedfriend']), array(0));
			$ordersql = "dateline DESC";
			$f_index = 'USE INDEX(dateline)';
			$findex = 'dateline';
		}
	}

	$appid = empty($_GET['appid'])?0:intval($_GET['appid']);
	$icon = empty($_GET['icon'])?'':trim($_GET['icon']);
	$gid = !isset($_GET['gid'])?'-1':intval($_GET['gid']);
	if($gid>=0) {
		$fuids = array();
		$query = C::t('home_friend')->fetch_all_by_uid_gid($_G['uid'], $gid);
		foreach($query as $value) {
			$fuids[] = $value['fuid'];
		}
		if(empty($fuids)) {
			$need_count = false;
		} else {
			$uids = $fuids;
		}
	}
	$gidactives[$gid] = ' class="a"';

	$count = $filtercount = 0;

	if($need_count) {

		$query = C::t('home_feed')->fetch_all_by_search(1, $uids, $icon, '', '', '', $hot, '', $start, $perpage, $findex, $appid);

		if($_GET['view'] == 'me') {
			foreach ($query as $value) {
				if(!isset($hotlist[$value['feedid']]) && !isset($hotlist_all[$value['feedid']]) && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
					$value = mkfeed($value);

					if($value['dateline']>=$_G['home_today']) {
						$list['today'][] = $value;
					} elseif ($value['dateline']>=$_G['home_today']-3600*24) {
						$list['yesterday'][] = $value;
					} else {
						$theday = dgmdate($value['dateline'], 'Y-m-d');
						$list[$theday][] = $value;
					}
				}
				$count++;
			}
		} else {
			$hash_datas = array();
			$more_list = array();
			$uid_feedcount = array();

			foreach($query as $value) {
				if(!isset($hotlist[$value['feedid']]) && !isset($hotlist_all[$value['feedid']]) && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
					$value = mkfeed($value);
					if(ckicon_uid($value)) {

						if($value['dateline']>=$_G['home_today']) {
							$dkey = 'today';
						} elseif ($value['dateline']>=$_G['home_today']-3600*24) {
							$dkey = 'yesterday';
						} else {
							$dkey = dgmdate($value['dateline'], 'Y-m-d');
						}

						$maxshownum = 3;
						if(empty($value['uid'])) $maxshownum = 10;

						if(empty($value['hash_data'])) {
							if(empty($feed_users[$dkey][$value['uid']])) $feed_users[$dkey][$value['uid']] = $value;
							if(empty($uid_feedcount[$dkey][$value['uid']])) $uid_feedcount[$dkey][$value['uid']] = 0;

							$uid_feedcount[$dkey][$value['uid']]++;

							if($uid_feedcount[$dkey][$value['uid']]>$maxshownum) {
								$more_list[$dkey][$value['uid']][] = $value;
							} else {
								$feed_list[$dkey][$value['uid']][] = $value;
							}

						} elseif(empty($hash_datas[$value['hash_data']])) {
							$hash_datas[$value['hash_data']] = 1;
							if(empty($feed_users[$dkey][$value['uid']])) $feed_users[$dkey][$value['uid']] = $value;
							if(empty($uid_feedcount[$dkey][$value['uid']])) $uid_feedcount[$dkey][$value['uid']] = 0;


							$uid_feedcount[$dkey][$value['uid']] ++;

							if($uid_feedcount[$dkey][$value['uid']]>$maxshownum) {
								$more_list[$dkey][$value['uid']][] = $value;
							} else {
								$feed_list[$dkey][$value['uid']][$value['hash_data']] = $value;
							}

						} else {
							$user_list[$value['hash_data']][] = "<a href=\"home.php?mod=space&uid=$value[uid]\">$value[username]</a>";
						}


					} else {
						$filtercount++;
						$filter_list[] = $value;
					}
				}
				$count++;
			}
		}

		$multi = simplepage($count, $perpage, $page, $theurl);
	}
}

$olfriendlist = $visitorlist = $task = $ols = $birthlist = $guidelist = array();
$oluids = array();
$groups = array();
$defaultusers = $newusers = $showusers = array();

if($space['self'] && empty($start)) {

	space_merge($space, 'field_home');
	if($_GET['view'] == 'we') {
		require_once libfile('function/friend');
		$groups = friend_group_list();
	}

	$isnewer = ($_G['timestamp']-$space['regdate'] > 3600*24*7) ?0:1;
	if($isnewer && $_G['setting']['homestyle']) {

		$friendlist = array();
		$query = C::t('home_friend')->fetch_all($space['uid']);
		foreach($query as $value) {
			$friendlist[$value['fuid']] = 1;
		}

		foreach(C::t('home_specialuser')->fetch_all_by_status(1) as $value) {
			if(empty($friendlist[$value['uid']])) {
				$defaultusers[] = $value;
				$oluids[] = $value['uid'];
			}
		}
	}

	if($space['newprompt']) {
		space_merge($space, 'status');
	}

	if($_G['setting']['homestyle']) {
		foreach(C::t('home_visitor')->fetch_all_by_uid($space['uid'], 12) as $value) {
			$visitorlist[$value['vuid']] = $value;
			$oluids[] = $value['vuid'];
		}

		if($oluids) {
			foreach(C::app()->session->fetch_all_by_uid($oluids) as $value) {
				if(!$value['invisible']) {
					$ols[$value['uid']] = 1;
				} elseif ($visitorlist[$value['uid']]) {
					unset($visitorlist[$value['uid']]);
				}
			}
		}

		$oluids = array();
		$olfcount = 0;
		if($space['feedfriend']) {
			foreach(C::app()->session->fetch_all_by_uid(explode(',', $space['feedfriend']), 15) as $value) {
				if($olfcount < 15 && !$value['invisible']) {
					$olfriendlist[$value['uid']] = $value;
					$ols[$value['uid']] = 1;
					$oluids[$value['uid']] = $value['uid'];
					$olfcount++;
				}
			}
		}
		if($olfcount < 15) {
			$query = C::t('home_friend')->fetch_all_by_uid($space['uid'], 0, 32, true);
			foreach($query as $value) {
				$value['uid'] = $value['fuid'];
				$value['username'] = $value['fusername'];
				if(empty($oluids[$value['uid']])) {
					$olfriendlist[$value['uid']] = $value;
					$olfcount++;
					if($olfcount == 15) break;
				}
			}
		}

		if($space['feedfriend']) {
			$birthdaycache = C::t('forum_spacecache')->fetch($_G['uid'], 'birthday');
			if(empty($birthdaycache) || TIMESTAMP > $birthdaycache['expiration']) {
				$birthlist = C::t('common_member_profile')->fetch_all_will_birthday_by_uid($space['feedfriend']);

				C::t('forum_spacecache')->insert(array(
					'uid' => $_G['uid'],
					'variable' => 'birthday',
					'value' => serialize($birthlist),
					'expiration' => getexpiration(),
				), false, true);
			} else {
				$birthlist = dunserialize($birthdaycache['value']);
			}
		}

		if($_G['setting']['taskon']) {
			require_once libfile('class/task');
			$tasklib = & task::instance();
			$taskarr = $tasklib->tasklist('canapply');
			$task = $taskarr[array_rand($taskarr)];
		}
		if($_G['setting']['magicstatus']) {
			loadcache('magics');
			if(!empty($_G['cache']['magics'])) {
				$magic = $_G['cache']['magics'][array_rand($_G['cache']['magics'])];
				$magic['description'] = cutstr($magic['description'], 34, '');
				$magic['pic'] = strtolower($magic['identifier']).'.gif';
			}
		}
	}
} elseif(empty($_G['uid'])) {
	$defaultusers = C::t('home_specialuser')->fetch_all_by_status(1, 12);

	$query = C::t('home_show')->fetch_all_by_credit(0, 12); //DB::query("SELECT * FROM ".DB::table('home_show')." ORDER BY credit DESC LIMIT 0,12");
	foreach($query as $value) {
		$showusers[] = $value;
	}

	foreach(C::t('common_member')->range(0, 12,'DESC') as $uid => $value) {
		$value['regdate'] = dgmdate($value['regdate'], 'u', 9999, 'm-d');
		$newusers[$uid] = $value;
	}
}

dsetcookie('home_readfeed', $_G['timestamp'], 365*24*3600);
if($_G['uid']) {
	$defaultstr = getdefaultdoing();
	space_merge($space, 'status');
	if(!$space['profileprogress']) {
		include_once libfile('function/profile');
		$space['profileprogress'] = countprofileprogress();
	}
}
$actives = array($_GET['view'] => ' class="a"');
if($_GET['from'] == 'space') {
	if($_GET['do'] == 'home') {
		$navtitle = lang('space', 'sb_feed', array('who' => $space['username']));
		$metakeywords = lang('space', 'sb_feed', array('who' => $space['username']));
		$metadescription = lang('space', 'sb_feed', array('who' => $space['username']));
	}
} else {
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('home');
	if(!$navtitle) {
		$navtitle = $_G['setting']['navs'][4]['navname'];
		$nobbname = false;
	} else {
		$nobbname = true;
	}

	if(!$metakeywords) {
		$metakeywords = $_G['setting']['navs'][4]['navname'];
	}

	if(!$metadescription) {
		$metadescription = $_G['setting']['navs'][4]['navname'];
	}
}
if(empty($cp_mode)) include_once template("diy:home/space_home");

?>