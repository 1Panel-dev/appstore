<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_index.php 26205 2011-12-05 10:09:32Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_GET['op'], array('start', 'layout', 'block', 'style', 'diy', 'image', 'getblock', 'edit', 'setmusic', 'getspaceinfo', 'savespaceinfo', 'editnv', 'getpersonalnv')) ? $_GET['op'] : 'start';

require_once libfile('function/space');
require_once libfile('function/portalcp');

if ($op == 'start') {


} elseif ($op == 'layout') {
	$layoutarr = getlayout();

} elseif ($op == 'style') {

	$themes = gettheme('space');

} elseif ($op == 'block') {
	$block = getblockdata();
} elseif ($op == 'diy' || $op == 'image') {

	$albumid = empty($_GET['albumid'])?0:intval($_GET['albumid']);
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page=1;

	$perpage = 6;
	$perpage = mob_perpage($perpage);

	$start = ($page-1)*$perpage;

	ckstart($start, $perpage);

	$albumlist = array();
	$query = C::t('home_album')->fetch_all_by_uid($space['uid'], 'updatetime');
	foreach($query as $value) {
		if (!isset($_GET['albumid']) && empty($albumid)) $albumid = $value['albumid'];

		$albumlist[$value['albumid']] = $value;
	}

	$count = C::t('home_pic')->check_albumpic(0, NULL, $space['uid']);
	$albumlist[0] = array(
		'uid' => $space['uid'],
		'albumid' => 0,
		'albumname' => lang('space', 'default_albumname'),
		'picnum' => $count
	);

	if ($albumid > 0) {
		if (!isset($albumlist[$albumid])) {
			showmessage('to_view_the_photo_does_not_exist');
		}

		$count = $albumlist[$albumid]['picnum'];
	} else {
		$wheresql = "albumid='0' AND uid='$space[uid]'";
	}

	$list = array();
	if($count) {
		$query = C::t('home_pic')->fetch_all_by_albumid($albumid, $start, $perpage, 0, 0, 1, ($albumid > 0 ? 0 : $space['uid']));
		foreach($query as $value) {
			$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
			$list[] = $value;
		}
	}

	$_GET['ajaxtarget'] = empty($_GET['ajaxtarget']) ? 'diyimages' : $_GET['ajaxtarget'];
	$multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=index&op=image&albumid=$albumid");

} elseif ($op == 'getblock') {

	$blockname = getstr($_GET['blockname'],15);
	$blockhtml = '';
	if(check_ban_block($name, $space)) {
		space_merge($space,'field_home');
		$data = getuserdiydata($space);
		$blockhtml = getblockhtml($blockname, $data['parameters'][$blockname]);
	}

} elseif ($op == 'edit') {

	$blockname = getstr($_GET['blockname'],15);
	$blockdata = lang('space','blockdata');
	if (!empty($blockdata[$blockname]) && check_ban_block($blockname, $space)) {
		space_merge($space,'field_home');
		$userdiy = getuserdiydata($space);
		$para = $userdiy['parameters'][$blockname];
		$para['title'] = !isset($para['title']) ? $blockdata[$blockname] : $para['title'];
	} else {
		showmessage('the_block_is_not_available');
	}
} elseif ($op == 'editnv') {
	space_merge($space,'field_home');
	$blockposition = dunserialize($space['blockposition']);
	$personalnv = !empty($blockposition) && isset($blockposition['nv']) ? $blockposition['nv'] : '';
} elseif ($op == 'savespaceinfo') {
	space_merge($space,'field_home');
	if (submitcheck('savespaceinfosubmit')) {

		$spacename = censor(getstr($_POST['spacename'], 30));
		$spacedescription = censor(getstr($_POST['spacedescription'], 135));

		$setarr = array();
		$setarr['spacename'] = $spacename;
		$setarr['spacedescription'] = $spacedescription;
		C::t('common_member_field_home')->update($_G['uid'], $setarr);

		$space['spacename'] = $spacename;
		$space['spacedescription'] = $spacedescription;
	}
} elseif ($op == 'getspaceinfo') {
	space_merge($space,'field_home');
} elseif ($op == 'getpersonalnv') {
	space_merge($space,'field_home');
	getuserdiydata($space);
	$personalnv = isset($_G['blockposition']['nv']) ? $_G['blockposition']['nv'] : '';
	if($personalnv && !empty($_GET['show'])) {
		$personalnv['nvhidden'] = 0;
	}
}
if (submitcheck('blocksubmit')) {

	$blockname = getstr($_GET['blockname'],15);
	if(check_ban_block($blockname, $space)) {
		space_merge($space,'field_home');
		$blockdata = dunserialize($space['blockposition']);

		$title = censor(getstr($_POST['blocktitle'],50));
		$blockdata['parameters'][$blockname]['title'] = $title;

		if (in_array($blockname, array('block1', 'block2', 'block3', 'block4', 'block5'))) {
			$content = censor(getstr($_POST['content'],1000,0,0,0,1));
			$blockdata['parameters'][$blockname]['content'] = $content;
		} elseif($blockname == 'profile') {
			$blockdata['parameters'][$blockname]['banavatar'] = in_array($_GET['avatar'], array('big', 'middle', 'small')) ? $_GET['avatar'] : 'middle';
		} elseif($blockname == 'statistic') {
			$blockdata['parameters'][$blockname]['bancredits'] = $_GET['credits'] ? 0 : 1;
			$blockdata['parameters'][$blockname]['banfriends'] = $_GET['friends'] ? 0 : 1;
			$blockdata['parameters'][$blockname]['banthreads'] = $_GET['threads'] ? 0 : 1;
			$blockdata['parameters'][$blockname]['banblogs'] = $_GET['blogs'] ? 0 : 1;
			$blockdata['parameters'][$blockname]['banalbums'] = $_GET['albums'] ? 0 : 1;
			$blockdata['parameters'][$blockname]['bansharings'] = $_GET['sharings'] ? 0 : 1;
			$blockdata['parameters'][$blockname]['banviews'] = $_GET['views'] ? 0 : 1;
		} elseif(in_array($blockname, array('personalinfo'))) {

		} else {
			$shownum = max(1,intval($_POST['shownum']));
			if ($shownum <= 20) {
				$blockdata['parameters'][$blockname]['shownum'] = $shownum;
			}
		}

		if($blockname == 'blog') {
			$blockdata['parameters'][$blockname]['showmessage'] = min(100000, abs(intval($_GET['showmessage'])));
		} elseif($blockname == 'myapp') {
			$blockdata['parameters'][$blockname]['logotype'] = in_array($_GET['logotype'], array('logo', 'icon')) ? $_GET['logotype'] : 'logo';
		}

		$setarr = array();
		$setarr['blockposition'] = serialize($blockdata);
		C::t('common_member_field_home')->update($space['uid'], $setarr);

		showmessage('do_success', 'portal.php?mod=spacecp&ac=index&op=getblock&blockname='.$blockname, array('blockname'=>$blockname));
	} else {
		showmessage('the_block_is_not_available');
	}
}

if (submitcheck('editnvsubmit')) {

	$hidden = intval($_POST['nvhidden']);
	$nv = array('index', 'feed', 'doing', 'blog', 'album', 'topic', 'share', 'friends', 'wall', 'profile', 'follow');
	space_merge($space,'field_home');
	$blockdata = dunserialize($space['blockposition']);

	$personalnv = array();
	$personalnv['nvhidden'] = $hidden;
	foreach($nv as $value) {
		$namevalue = trim($_POST[$value]);
		$personalnv['items'][$value] = getstr($namevalue,15);
		$personalnv['banitems'][$value] = empty($_POST['ban'.$value]) ? 0 : 1;
	}
	$blockdata['nv'] = $personalnv;
	$setarr = array();
	$setarr['blockposition'] = serialize($blockdata);
	C::t('common_member_field_home')->update($space['uid'], $setarr);

	showmessage('do_success', 'portal.php?mod=spacecp&ac=index&op=getnv');

}

if (submitcheck('musicsubmit')) {

	$blockname = getstr($_GET['blockname'],15);
	space_merge($space,'field_home');
	$blockdata = dunserialize($space['blockposition']);
	if ($_POST['act'] == 'config') {
		$config = array (
				'showmod' => $_POST['showmod'],
				'autorun' => $_POST['autorun'],
				'shuffle' => $_POST['shuffle'],
				'crontabcolor' => $_POST['crontabcolor'],
				'buttoncolor' => $_POST['buttoncolor'],
				'fontcolor' => $_POST['fontcolor'],
				'crontabbj' => $_POST['crontabbj'],
				'height' => min(9999,abs(intval($_POST['height']))),
			  );
		$blockdata['parameters']['music']['config'] = $config;

		$blockdata['parameters']['music']['title']= censor(getstr($_POST['blocktitle'],50));

	} elseif ($_POST['act'] == 'addmusic') {
		$mp3url = $_POST['mp3url'];
		$mp3name = $_POST['mp3name'];
		$cdbj = $_POST['cdbj'];
		$mp3list = empty($blockdata['parameters']['music']['mp3list']) ? array() : $blockdata['parameters']['music']['mp3list'];
		foreach ($mp3url as $key => $value) {
			if (!empty($value)) {
				if(empty($mp3name[$key])) $mp3name[$key] = substr($value,strrpos($value,'/')+1,strlen($value));
				$mp3list[] = array('mp3url'=>$value, 'mp3name'=>censor($mp3name[$key]), 'cdbj'=>$cdbj[$key]);
			}
		}
		$blockdata['parameters']['music']['mp3list'] = $mp3list;

	} elseif ($_POST['act'] == 'editlist') {
		$mp3url = $_POST['mp3url'];
		$mp3name = $_POST['mp3name'];
		$cdbj = $_POST['cdbj'];
		$mp3list = array();
		foreach ($mp3url as $key => $value) {
			if (!empty($value)) {
				if(empty($mp3name[$key])) $mp3name[$key] = substr($value,strrpos($value,'/')+1,strlen($value));
				$mp3list[] = array('mp3url'=>$value, 'mp3name'=>censor($mp3name[$key]), 'cdbj'=>$cdbj[$key]);
			}
		}

		$blockdata['parameters']['music']['mp3list'] = $mp3list;
	}

	if (empty($blockdata['parameters']['music']['config'])) {
		$blockdata['parameters']['music']['config'] = array (
			  'showmod' => 'default',
			  'autorun' => 'true',
			  'shuffle' => 'true',
			  'crontabcolor' => '#D2FF8C',
			  'buttoncolor' => '#1F43FF',
			  'fontcolor' => '#1F43FF',
			);
	}
	$setarr = array();
	$setarr['blockposition'] = serialize($blockdata);
	C::t('common_member_field_home')->update($space['uid'], $setarr);
	showmessage('do_success', 'home.php?mod=spacecp&ac=index&op=getblock&blockname='.$blockname, array('blockname'=>$blockname));
}

if (submitcheck('diysubmit')) {

	$blockdata = array();

	checksecurity($_POST['spacecss']);

	$spacecss = $_POST['spacecss'];
	$spacecss = preg_replace("/(\<|\>)/is", '', $spacecss);

	$currentlayout = getstr($_POST['currentlayout'],5);
	$style = empty($_POST['style'])?'':preg_replace("/[^0-9a-z]/i", '', $_POST['style']);

	$layoutdata = $_POST['layoutdata'];
	require_once libfile('class/xml');
	$layoutdata = xml2array($layoutdata);
	if (empty($layoutdata)) showmessage('space_data_format_invalid');
	$layoutdata = $layoutdata['diypage'];
	if($style && $style != 'uchomedefault') {
		$cssfile = DISCUZ_ROOT.'./static/space/'.$style.'/style.css';
		if(!file_exists($cssfile)) {
			showmessage('theme_does_not_exist');
		}
	}

	space_merge($space, 'field_home');
	$blockdata = dunserialize($space['blockposition']);
	$blockdata['block'] = $layoutdata;
	$blockdata['currentlayout'] = $currentlayout;
	$setarr = array();
	$setarr['spacecss'] = $spacecss;
	$setarr['blockposition'] = serialize($blockdata);
	$setarr['theme'] = $style;
	C::t('common_member_field_home')->update($space['uid'], $setarr);
	showmessage('do_success','home.php?mod=space'.($_G['adminid'] == 1 && $_G['setting']['allowquickviewprofile'] ? '&view=admin' : ''));
}

if (submitcheck('uploadsubmit')) {
	$albumid = $picid = 0;

	if(!checkperm('allowupload')) {
		echo "<script>";
		echo "alert(\"".lang('spacecp', 'not_allow_upload')."\")";
		echo "</script>";
		exit();
	}
	$uploadfiles = pic_save($_FILES['attach'], $_POST['albumid'], $_POST['pic_title'], false);
	if($uploadfiles && is_array($uploadfiles)) {
		$albumid = $uploadfiles['albumid'];
		$picid = $uploadfiles['picid'];
		$uploadStat = 1;
		require_once libfile('function/spacecp');
		album_update_pic($albumid);
	} else {
		$uploadStat = $uploadfiles;
	}

	$picurl = pic_get($uploadfiles['filepath'], 'album', $uploadfiles['thumb'], $uploadfiles['remote']);

	echo "<script>";
	if($uploadStat == 1) {
		echo "parent.spaceDiy.getdiy('diy', 'albumid', '$albumid');";
		echo "parent.spaceDiy.setBgImage('$picurl');";
		echo "parent.Util.toggleEle('upload');";
	} else {
		echo "parent.showDialog('$uploadStat','notice');";
	}
	echo "</script>";
	exit();
}
include_once(template('home/spacecp_index'));
?>