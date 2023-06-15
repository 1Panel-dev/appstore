<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_album.php 33249 2013-05-09 07:27:16Z kamichen $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:intval($_G['setting']['feedhotmin']);
$id = empty($_GET['id'])?0:intval($_GET['id']);
$picid = empty($_GET['picid'])?0:intval($_GET['picid']);

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;

if($id) {

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page-1)*$perpage;

	ckstart($start, $perpage);

	if($id > 0) {
		$album = C::t('home_album')->fetch($id, $space['uid']);
		if(empty($album)) {
			showmessage('to_view_the_photo_does_not_exist');
		}

		ckfriend_album($album);


		$album['picnum'] = $count = C::t('home_pic')->check_albumpic($id);

		if(empty($count) && !$space['self']) {
			C::t('home_album')->delete($id);
			showmessage('to_view_the_photo_does_not_exist', "home.php?mod=space&uid=$album[uid]&do=album&view=me");
		}

		if($album['catid']) {
			$album['catname'] = C::t('home_album_category')->fetch_catname_by_catid($album['catid']);
			$album['catname'] = dhtmlspecialchars($album['catname']);
		}

	} else {
		$count = C::t('home_pic')->check_albumpic(0, NULL, $space['uid']);

		$album = array(
			'uid' => $space['uid'],
			'albumid' => -1,
			'albumname' => lang('space', 'default_albumname'),
			'picnum' => $count
		);
	}

	$albumlist = array();
	$maxalbum = $nowalbum = $key = 0;
	$query = C::t('home_album')->fetch_all_by_uid($space['uid'], 'updatetime', 0, 100);
	foreach($query as $value) {
		if($value['friend'] != 4 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
			$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
		} elseif ($value['picnum']) {
			$value['pic'] = STATICURL.'image/common/nopublish.gif';
		} else {
			$value['pic'] = '';
		}
		$albumlist[$key][$value['albumid']] = $value;
		$key = count($albumlist[$key]) == 5 ? ++$key : $key;
	}
	$maxalbum = count($albumlist);

	$list = array();
	$pricount = 0;
	if($count) {
		$query = C::t('home_pic')->fetch_all_by_albumid($id, $start, $perpage, 0, 0, 1, $space['uid']);
		foreach($query as $value) {
			if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
				$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
				$list[] = $value;
			} else {
				$pricount++;
			}
		}
	}
	$multi = multi($count, $perpage, $page, "home.php?mod=space&uid=$album[uid]&do=$do&id=$id#comment");

	$actives = array('me' =>' class="a"');

	$_G['home_css'] = 'album';

	$diymode = intval($_G['cookie']['home_diymode']);

	$seodata = array('album' => $album['albumname'], 'user' => $album['username'], 'depict' => $album['depict']);
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('album', $seodata);
	if(empty($navtitle)) {
		$navtitle = (empty($album['albumname']) ? '' : $album['albumname'].' - ').lang('space', 'sb_album', array('who' => $album['username']));
		$nobbname = false;
	} else {
		$nobbname = true;
	}
	if(empty($metakeywords)) {
		$metakeywords = $album['albumname'];
	}
	if(empty($metadescription)) {
		$metadescription = $album['albumname'];
	}

	include_once template("diy:home/space_album_view");

} elseif ($picid) {
	$query = C::t('home_pic')->fetch_all_by_uid($space['uid'], 0, 1, $picid);
	$pic = $query[0];
	if(!$pic || ($pic['status'] == 1 && $pic['uid'] != $_G['uid'] && $_G['adminid'] != 1 && $_GET['modpickey'] != modauthkey($pic['picid']))) {
		showmessage('view_images_do_not_exist');
	}

	$picid = $pic['picid'];
	$theurl = "home.php?mod=space&uid=$pic[uid]&do=$do&picid=$picid";

	$album = array();
	if($pic['albumid']) {
		$album = C::t('home_album')->fetch($pic['albumid']);
		if(!$album) {
			C::t('home_pic')->update_for_albumid($pic['albumid'], array('albumid' => 0));
		}
	}

	if($album) {
		ckfriend_album($album);
	} else {
		$album['picnum'] = C::t('home_pic')->check_albumpic(0, NULL, $pic['uid']);
		$album['albumid'] = $pic['albumid'] = '-1';
	}

	$piclist = $list = $keys = array();
	$keycount = 0;
	$query = C::t('home_pic')->fetch_all_by_albumid($pic['albumid'], 0, 0, 0, 0, 1, $space['uid']);
	foreach($query as $value) {
		if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
			$keys[$value['picid']] = $keycount;
			$list[$keycount] = $value;
			$keycount++;
		}
	}

	$upid = $nextid = 0;
	$nowkey = $keys[$picid];
	$endkey = $keycount - 1;
	if($endkey>4) {
		$newkeys = array($nowkey-2, $nowkey-1, $nowkey, $nowkey+1, $nowkey+2);
		if($newkeys[1] < 0) {
			$newkeys[0] = $endkey-1;
			$newkeys[1] = $endkey;
		} elseif($newkeys[0] < 0) {
			$newkeys[0] = $endkey;
		}
		if($newkeys[3] > $endkey) {
			$newkeys[3] = 0;
			$newkeys[4] = 1;
		} elseif($newkeys[4] > $endkey) {
			$newkeys[4] = 0;
		}
		$upid = $list[$newkeys[1]]['picid'];
		$nextid = $list[$newkeys[3]]['picid'];

		foreach ($newkeys as $nkey) {
			$piclist[$nkey] = $list[$nkey];
		}
	} else {
		$newkeys = array($nowkey-1, $nowkey, $nowkey+1);
		if($newkeys[0] < 0) {
			$newkeys[0] = $endkey;
		}
		if($newkeys[2] > $endkey) {
			$newkeys[2] = 0;
		}
		$upid = $list[$newkeys[0]]['picid'];
		$nextid = $list[$newkeys[2]]['picid'];

		$piclist = $list;
	}
	foreach ($piclist as $key => $value) {
		$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
		$piclist[$key] = $value;
	}

	$pic['pic'] = pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote'], 0);
	$pic['size'] = formatsize($pic['size']);

	$exifs = array();
	$allowexif = function_exists('exif_read_data');
	if(isset($_GET['exif']) && $allowexif) {
		require_once libfile('function/exif');
		$exifs = getexif($pic['pic']);
	}

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page-1)*$perpage;

	ckstart($start, $perpage);

	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);

	$siteurl = getsiteurl();
	$list = array();
	$count = C::t('home_comment')->count_by_id_idtype($pic['picid'], 'picid', $cid);
	if($count) {
		$query = C::t('home_comment')->fetch_all_by_id_idtype($pic['picid'], 'picid', $start, $perpage, $cid);
		foreach($query as $value) {
			$list[] = $value;
		}
	}

	$multi = multi($count, $perpage, $page, $theurl);

	if(empty($album['albumname'])) $album['albumname'] = lang('space', 'default_albumname');

	$pic_url = $pic['pic'];
	if(!preg_match("/^(http|https)\:\/\/.+?/i", $pic['pic'])) {
		$pic_url = getsiteurl().$pic['pic'];
	}
	$pic_url2 = rawurlencode($pic['pic']);

	$hash = md5($pic['uid']."\t".$pic['dateline']);
	$id = $pic['picid'];
	$idtype = 'picid';

	$maxclicknum = 0;
	loadcache('click');
	$clicks = empty($_G['cache']['click']['picid'])?array():$_G['cache']['click']['picid'];
	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $pic["click{$key}"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	$clickuserlist = array();
	foreach(C::t('home_clickuser')->fetch_all_by_id_idtype($id, $idtype, 0, 20) as $value) {
		$value['clickname'] = $clicks[$value['clickid']]['name'];
		$clickuserlist[] = $value;
	}

	$actives = array('me' =>' class="a"');

	if($album['picnum']) {
		$sequence = $nowkey + 1;
	}

	$diymode = intval($_G['cookie']['home_diymode']);

	$navtitle = $album['albumname'];
	if($pic['title']) {
		$navtitle = $pic['title'].' - '.$navtitle;
	}
	$metakeywords = $pic['title'] ? $pic['title'] : $album['albumname'];
	$metadescription = $pic['title'] ? $pic['title'] : $album['albumname'];

	include_once template("diy:home/space_album_pic");

} else {

	loadcache('albumcategory');
	$category = $_G['cache']['albumcategory'];

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page-1)*$perpage;

	ckstart($start, $perpage);

	$_GET['friend'] = intval($_GET['friend']);

	$default = array();
	$f_index = '';
	$list = array();
	$pricount = 0;
	$picmode = 0;

	if(empty($_GET['view'])) {
		$_GET['view'] = 'we';
	}

	$gets = array(
		'mod' => 'space',
		'uid' => $space['uid'],
		'do' => 'album',
		'view' => $_GET['view'],
		'catid' => $_GET['catid'],
		'order' => $_GET['order'],
		'fuid' => $_GET['fuid'],
		'searchkey' => $_GET['searchkey'],
		'from' => $_GET['from']
	);
	$theurl = 'home.php?'.url_implode($gets);
	$actives = array($_GET['view'] =>' class="a"');

	$need_count = true;

	if($_GET['view'] == 'all') {

		$wheresql = '1';

		if($_GET['order'] == 'hot') {
			$orderactives = array('hot' => ' class="a"');
			$picmode = 1;
			$need_count = false;

			$ordersql = 'p.dateline';

			$count = C::t('home_pic')->fetch_all_by_sql('p.'.DB::field('hot', $minhot, '>='), '', 0, 0, 1);
			if($count) {
				$query = C::t('home_pic')->fetch_all_by_sql('p.'.DB::field('hot', $minhot, '>='), 'p.dateline DESC', $start, $perpage);
				foreach($query as $value) {
					if($value['friend'] != 4 && ckfriend($value['uid'], $value['friend'], $value['target_ids']) && ($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1)) {
						$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
						$list[] = $value;
					} else {
						$pricount++;
					}
				}
			}

		} else {
			$orderactives = array('dateline' => ' class="a"');
		}

	} elseif($_GET['view'] == 'we') {

		space_merge($space, 'field_home');

		$uids = array();

		if($space['feedfriend']) {

			$uids = explode(',', $space['feedfriend']);
			$f_index = 'updatetime';

			$fuid_actives = array();

			require_once libfile('function/friend');
			$fuid = intval($_GET['fuid']);
			if($fuid && friend_check($fuid)) {
				$uids = array($fuid);
				$f_index = '';
				$fuid_actives = array($fuid=>' selected');
			}

			$query = C::t('home_friend')->fetch_all_by_uid($space['uid'], 0, 500, true);
			foreach($query as $value) {
				$userlist[] = $value;
			}
		} else {
			$need_count = false;
		}

	} else {

		if($_GET['from'] == 'space') $diymode = 1;

		$uids = array($space['uid']);
	}

	if($need_count) {

		if($searchkey = stripsearchkey($_GET['searchkey'])) {
			$sqlSearchKey = $searchkey;
			$searchkey = dhtmlspecialchars($searchkey);
		}

		$catid = empty($_GET['catid'])?0:intval($_GET['catid']);

		$count = C::t('home_album')->fetch_all_by_search(3, $uids, $sqlSearchKey, true, $catid, 0, 0, '');

		if($count) {
			$query = C::t('home_album')->fetch_all_by_search(1, $uids, $sqlSearchKey, true, $catid, 0, 0, '', '', 'updatetime', 'DESC', $start, $perpage, $f_index);
			foreach($query as $value) {
				if($value['friend'] != 4 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
					$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
				} elseif ($value['picnum']) {
					$value['pic'] = STATICURL.'image/common/nopublish.gif';
				} else {
					$value['pic'] = '';
				}
				$list[] = $value;
			}
		}
	}

	$multi = multi($count, $perpage, $page, $theurl);

	dsetcookie('home_diymode', $diymode);

	if($_G['uid']) {
		if($_GET['view'] == 'all') {
			$navtitle = lang('core', 'title_view_all').lang('core', 'title_album');
		} elseif($_GET['view'] == 'me') {
			$navtitle = lang('core', 'title_my_album');
		} else {
			$navtitle = lang('core', 'title_friend_album');
		}
	} else {
		if($_GET['order'] == 'hot') {
			$navtitle = lang('core', 'title_hot_pic_recommend');
		} else {
			$navtitle = lang('core', 'title_newest_update_album');
		}
	}
	if($space['username']) {
		$navtitle = lang('space', 'sb_album', array('who' => $space['username']));
	}

	$metakeywords = $navtitle;
	$metadescription = $navtitle;
	include_once template("diy:home/space_album_list");
}

function ckfriend_album($album) {
	global $_G, $space;

	if($_G['adminid'] != 1) {
		if(!ckfriend($album['uid'], $album['friend'], $album['target_ids'])) {
			if(empty($_G['uid'])) {
				showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
			}
			require_once libfile('function/friend');
			$isfriend = friend_check($album['uid']);
			space_merge($space, 'count');
			space_merge($space, 'profile');
			$_G['privacy'] = 1;
			require_once libfile('space/profile', 'include');
			include template('home/space_privacy');
			exit();
		} elseif(!$space['self'] && $album['friend'] == 4) {
			$cookiename = "view_pwd_album_$album[albumid]";
			$cookievalue = empty($_G['cookie'][$cookiename])?'':$_G['cookie'][$cookiename];
			if($cookievalue != md5(md5($album['password']))) {
				$invalue = $album;
				include template('home/misc_inputpwd');
				exit();
			}
		}
	}
}

?>