<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_home.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getstr($string, $length = 0, $in_slashes=0, $out_slashes=0, $bbcode=0, $html=0) {
	global $_G;

	$string = trim($string);
	$sppos = strpos($string, chr(0).chr(0).chr(0));
	if($sppos !== false) {
		$string = substr($string, 0, $sppos);
	}
	if($in_slashes) {
		$string = dstripslashes($string);
	}
	$string = preg_replace("/\[hide=?\d*\](.*?)\[\/hide\]/is", '', $string);
	if($html < 0) {
		$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
	} elseif ($html == 0) {
		$string = dhtmlspecialchars($string);
	}

	if($length) {
		$string = cutstr($string, $length);
	}

	if($bbcode) {
		require_once DISCUZ_ROOT.'./source/class/class_bbcode.php';
		$bb = & bbcode::instance();
		$string = $bb->bbcode2html($string, $bbcode);
	}
	if($out_slashes) {
		$string = daddslashes($string);
	}
	return trim($string);
}

function obclean() {
	ob_end_clean();
	if (getglobal('config/output/gzip') && function_exists('ob_gzhandler')) {
		ob_start('ob_gzhandler');
	} else {
		ob_start();
	}
}

function dreaddir($dir, $extarr=array()) {
	$dirs = array();
	if($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if(!empty($extarr) && is_array($extarr)) {
				if(in_array(strtolower(fileext($file)), $extarr)) {
					$dirs[] = $file;
				}
			} else if($file != '.' && $file != '..') {
				$dirs[] = $file;
			}
		}
		closedir($dh);
	}
	return $dirs;
}

function url_implode($gets) {
	$arr = array();
	foreach ($gets as $key => $value) {
		if($value) {
			$arr[] = $key.'='.urlencode($value);
		}
	}
	return implode('&', $arr);
}

function ckstart($start, $perpage) {
	global $_G;

	$_G['setting']['maxpage'] = $_G['setting']['maxpage'] ? $_G['setting']['maxpage'] : 100;
	$maxstart = $perpage*intval($_G['setting']['maxpage']);
	if($start < 0 || ($maxstart > 0 && $start >= $maxstart)) {
		showmessage('length_is_not_within_the_scope_of');
	}
}


function get_my_app() {
}

function get_my_userapp() {
}

function getspace($uid) {
	return getuserbyuid($uid);
}

function ckprivacy($key, $privace_type) {
	global $_G, $space;

	$var = "home_ckprivacy_{$key}_{$privace_type}";
	if(isset($_G[$var])) {
		return $_G[$var];
	}
	space_merge($space, 'field_home');
	$result = false;
	if($_G['adminid'] == 1) {
		$result = true;
	} else {
		if($privace_type == 'feed') {
			if(!empty($space['privacy'][$privace_type][$key])) {
				$result = true;
			}
		} elseif($space['self']){
			$result = true;
		} else {
			if(empty($space['privacy'][$privace_type][$key])) {
				$result = true;
			} elseif ($space['privacy'][$privace_type][$key] == 1) {
				include_once libfile('function/friend');
				if(friend_check($space['uid'])) {
					$result = true;
				}
			} elseif ($space['privacy'][$privace_type][$key] == 3) {
				$result = in_array($_G['groupid'], array(4, 5, 6, 7)) ? false : true;
			}
		}
	}
	$_G[$var] = $result;
	return $result;
}

function app_ckprivacy($privacy) {
	global $_G, $space;

	$var = "home_app_ckprivacy_{$privacy}";
	if(isset($_G[$var])) {
		return $_G[$var];
	}
	$result = false;
	switch ($privacy) {
		case 0:
			$result = true;
			break;
		case 1:
			include_once libfile('function/friend');
			if(friend_check($space['uid'])) {
				$result = true;
			}
			break;
		case 2:
			break;
		case 3:
			if($space['self']) {
				$result = true;
			}
			break;
		case 4:
			break;
		case 5:
			break;
		default:
			$result = true;
			break;
	}
	$_G[$var] = $result;
	return $result;
}

function formatsize($size) {
	$prec=3;
	$size = round(abs($size));
	$units = array(0=>" B ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" TB");
	if ($size==0) return str_repeat(" ", $prec)."0$units[0]";
	$unit = min(4, floor(log($size)/log(2)/10));
	$size = $size * pow(2, -10*$unit);
	$digi = $prec - 1 - floor(log($size)/log(10));
	$size = round($size * pow(10, $digi)) * pow(10, -$digi);
	return $size.$units[$unit];
}

function ckfriend($touid, $friend, $target_ids='') {
	global $_G;

	if(empty($_G['uid'])) return $friend?false:true;
	if($touid == $_G['uid'] || $_G['adminid'] == 1) return true;

	$var = 'home_ckfriend_'.md5($touid.'_'.$friend.'_'.$target_ids);
	if(isset($_G[$var])) return $_G[$var];

	$_G[$var] = false;
	switch ($friend) {
		case 0:
			$_G[$var] = true;
			break;
		case 1:
			include_once libfile('function/friend');
			if(friend_check($touid)) {
				$_G[$var] = true;
			}
			break;
		case 2:
			if($target_ids) {
				$target_ids = explode(',', $target_ids);
				if(in_array($_G['uid'], $target_ids)) $_G[$var] = true;
			}
			break;
		case 3:
			break;
		case 4:
			$_G[$var] = true;
			break;
		default:
			break;
	}
	return $_G[$var];
}
function ckfollow($followuid) {
	global $_G;

	if(empty($_G['uid'])) return false;

	$var = 'home_follow_'.$_G['uid'].'_'.$followuid;
	if(isset($_G[$var])) return $_G[$var];

	$_G[$var] = false;
	$follow = C::t('home_follow')->fetch_status_by_uid_followuid($_G['uid'], $followuid);
	if(isset($follow[$_G['uid']])) {
		$_G[$var] = true;
	}
	return $_G[$var];
}

function sub_url($url, $length) {
	if(strlen($url) > $length) {
		$url = str_replace(array('%3A', '%2F'), array(':', '/'), rawurlencode($url));
		$url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
	}
	return $url;
}

function space_domain($space) {
	global $_G;

	if($_G['setting']['allowspacedomain'] && $_G['setting']['domain']['root']['home']) {
		space_merge($space, 'field_home');
		if($space['domain']) {
			$space['domainurl'] = $_G['scheme'].'://'.$space['domain'].'.'.$_G['setting']['domain']['root']['home'];
		}
	}
	if(!empty($_G['setting']['domain']['app']['home'])) {
		$space['domainurl'] = $_G['scheme'].'://'.$_G['setting']['domain']['app']['home'].'/?'.$space['uid'];
	} elseif(empty($space['domainurl'])) {
		$space['domainurl'] = $_G['siteurl'].'?'.$space['uid'];
	}
	return $space['domainurl'];
}

function g_name($groupid) {
	global $_G;
	echo $_G['cache']['usergroups'][$groupid]['grouptitle'];
}

function g_color($groupid) {
	global $_G;
	if(empty($_G['cache']['usergroups'][$groupid]['color'])) {
		echo '';
	} else {
		echo ' style="color:'.$_G['cache']['usergroups'][$groupid]['color'].';"';
	}
}

function mob_perpage($perpage) {
	global $_G;

	$newperpage = isset($_GET['perpage'])?intval($_GET['perpage']):0;
	if($_G['mobile'] && $newperpage>0 && $newperpage<500) {
		$perpage = $newperpage;
	}
	return $perpage;
}

function ckicon_uid($feed) {
	global $_G, $space;

	space_merge($space, 'field_home');
	$filter_icon = empty($space['privacy']['filter_icon'])?array():array_keys($space['privacy']['filter_icon']);
	if($filter_icon && (in_array($feed['icon'].'|0', $filter_icon) || in_array($feed['icon'].'|'.$feed['uid'], $filter_icon))) {
		return false;
	}
	return true;
}

function sarray_rand($arr, $num=1) {
	$r_values = array();
	if($arr && count($arr) > $num) {
		if($num > 1) {
			$r_keys = array_rand($arr, $num);
			foreach ($r_keys as $key) {
				$r_values[$key] = $arr[$key];
			}
		} else {
			$r_key = array_rand($arr, 1);
			$r_values[$r_key] = $arr[$r_key];
		}
	} else {
		$r_values = $arr;
	}
	return $r_values;
}

function my_showgift() {
}

function getsiteurl() {
	global $_G;
	return $_G['siteurl'];
}

function pic_get($filepath, $type, $thumb, $remote, $return_thumb=1, $hastype = '') {
	global $_G;

	$url = $filepath;
	if($return_thumb && $thumb) $url = getimgthumbname($url);
	if($remote > 1 && $type == 'album') {
		$remote -= 2;
		$type = 'forum';
	}
	$type = $hastype ? '' : $type.'/';
	return ($remote?$_G['setting']['ftp']['attachurl']:$_G['setting']['attachurl']).$type.$url;
}

function pic_cover_get($pic, $picflag) {
	global $_G;

	$type = 'album';
	if($picflag > 2) {
		$picflag = $picflag - 2;
		$type = 'forum';
	}
	if($picflag == 1) {
		$url = $_G['setting']['attachurl'].$type.'/'.$pic;
	} elseif ($picflag == 2) {
		$url = $_G['setting']['ftp']['attachurl'].$type.'/'.$pic;
	} else {
		$url = $pic;
	}
	return $url;
}

function pic_delete($pic, $type, $thumb, $remote) {
	global $_G;

	if($remote > 1 && $type == 'album') {
		$remote -= 2;
		$type = 'forum';
		return true;
	}

	if($remote) {
		ftpcmd('delete', $type.'/'.$pic);
		if($thumb) {
			ftpcmd('delete', $type.'/'.getimgthumbname($pic));
		}
		ftpcmd('close');
	} else {
		@unlink($_G['setting']['attachdir'].'/'.$type.'/'.$pic);
		if($thumb) {
			@unlink($_G['setting']['attachdir'].'/'.$type.'/'.getimgthumbname($pic));
		}
	}
	return true;
}

function pic_upload($FILES, $type='album', $thumb_width=0, $thumb_height=0, $thumb_type=2) {
	$upload = new discuz_upload();

	$result = array('pic'=>'', 'thumb'=>0, 'remote'=>0);

	$upload->init($FILES, $type);
	if($upload->error()) {
		return array();
	}

	$upload->save();
	if($upload->error()) {
		return array();
	}

	$result['pic'] = $upload->attach['attachment'];

	if($thumb_width && $thumb_height) {
		require_once libfile('class/image');
		$image = new image();
		if($image->Thumb($upload->attach['target'], '', $thumb_width, $thumb_height, $thumb_type)) {
			$result['thumb'] = 1;
		}
	}

	if(ftpperm($upload->attach['ext'], $upload->attach['size'])) {
		if(ftpcmd('upload', $type.'/'.$upload->attach['attachment'])) {
			if($result['thumb']) {
				ftpcmd('upload', $type.'/'.getimgthumbname($upload->attach['attachment']));
			}
			ftpcmd('close');
			$result['remote'] = 1;
		} else {
			if(getglobal('setting/ftp/mirror')) {
				@unlink($upload->attach['target']);
				@unlink(getimgthumbname($upload->attach['target']));
				return array();
			}
		}
	}

	return $result;
}

function member_count_update($uid, $counts) {
	global $_G;

	$setsqls = array();
	foreach ($counts as $key => $value) {
		if($key == 'credit') {
			if($_G['setting']['creditstransextra'][6]) {
				$key = 'extcredits'.intval($_G['setting']['creditstransextra'][6]);
			} elseif ($_G['setting']['creditstrans']) {
				$key = 'extcredits'.intval($_G['setting']['creditstrans']);
			} else {
				continue;
			}
		}
		$setsqls[$key] = $value;
	}
	if($setsqls) {
		updatemembercount($uid, $setsqls);
	}
}


function getdefaultdoing() {
	global $_G;

	$result = array();
	$key = 0;

	if(($result = C::t('common_setting')->fetch('defaultdoing'))) {
		$_G['setting']['defaultdoing'] = explode("\r\n", $result);
		$key = rand(0, count($_G['setting']['defaultdoing'])-1);
	} else {
		$_G['setting']['defaultdoing'] = array(lang('space', 'doing_you_can'));
	}
	return $_G['setting']['defaultdoing'][$key];
}

function getuserdiydata($space) {
	global $_G;
	if(empty($_G['blockposition'])) {
		$userdiy = getuserdefaultdiy();
		if (!empty($space['blockposition'])) {
			$blockdata = dunserialize($space['blockposition']);
			foreach ((array)$blockdata as $key => $value) {
				if ($key == 'parameters') {
					foreach ((array)$value as $k=>$v) {
						if (!empty($v)) $userdiy[$key][$k] = $v;
					}
				} else {
					if (!empty($value)) $userdiy[$key] = $value;
				}
			}
		}
		$_G['blockposition'] = $userdiy;
	}
	return $_G['blockposition'];
}


function getuserdefaultdiy() {
	$defaultdiy = array(
			'currentlayout' => '1:2:1',
			'block' => array(
					'frame`frame1' => array(
							'attr' => array('name'=>'frame1'),
							'column`frame1_left' => array(
									'block`profile' => array('attr' => array('name'=>'profile')),
									'block`statistic' => array('attr' => array('name'=>'statistic')),
									'block`album' => array('attr' => array('name'=>'album')),
									'block`doing' => array('attr' => array('name'=>'doing'))
							),
							'column`frame1_center' => array(
									'block`personalinfo' => array('attr' => array('name'=>'personalinfo')),
									'block`feed' => array('attr' => array('name'=>'feed')),
									'block`share' => array('attr' => array('name'=>'share')),
									'block`blog' => array('attr' => array('name'=>'blog')),
									'block`thread' => array('attr' => array('name'=>'thread')),
									'block`wall' => array('attr' => array('name'=>'wall'))
							),
							'column`frame1_right' => array(
									'block`myapp' => array('attr' => array('name'=>'myapp')),
									'block`friend' => array('attr' => array('name'=>'friend')),
									'block`visitor' => array('attr' => array('name'=>'visitor')),
									'block`group' => array('attr' => array('name'=>'group'))
							)
					)
			),
			'parameters' => array(
					'blog' => array('showmessage' => 150, 'shownum' => 6),
					'doing' => array('shownum' => 15),
					'album' => array('shownum' => 8),
					'thread' => array('shownum' => 10),
					'share' => array('shownum' => 10),
					'friend' => array('shownum' => 18),
					'group' => array('shownum' => 12),
					'visitor' => array('shownum' => 18),
					'wall' => array('shownum' => 16),
					'feed' => array('shownum' => 16),
					'myapp' => array('shownum' => 9, 'logotype'=> 'logo'),
			),
		'nv' => array(
			'nvhidden' => 0,
			'items' => array(),
			'banitems' => array(),
		),
	);
	return $defaultdiy;
}

function getonlinemember($uids) {
	global $_G;
	if ($uids && is_array($uids) && empty($_G['ols'])) {
		$_G['ols'] = array();
		foreach(C::app()->session->fetch_all_by_uid($uids) as $value) {
			if(!$value['invisible']) {
				$_G['ols'][$value['uid']] = $value['lastactivity'];
			}
		}
	}
}
function getfollowfeed($uid, $viewtype, $archiver = false, $start = 0, $perpage = 0) {
	global $_G;

	$list = array();
	if(isset($_G['follwusers'][$uid])) {
		$list['user'] = $_G['follwusers'][$uid];
	} else {
		if($viewtype == 'follow') {
			$list['user'] = C::t('home_follow')->fetch_all_following_by_uid($uid);
			$list['user'][$uid] = array('uid' => $uid);
		} elseif($viewtype == 'special') {
			$list['user'] = C::t('home_follow')->fetch_all_following_by_uid($uid, 1);
		}
		if(!empty($list['user'])) {
			$_G['follwusers'][$uid] = $list['user'];
		}
	}
	$uids = in_array($viewtype, array('other', 'self')) ? $uid : array_keys($list['user']);
	if(!empty($uids) || in_array($viewtype, array('other', 'self'))) {
		$list['feed'] = C::t('home_follow_feed')->fetch_all_by_uid($uids, $archiver, $start, $perpage);
		if($list['feed']) {
			$list['content'] = C::t('forum_threadpreview')->fetch_all(C::t('home_follow_feed')->get_tids());
			if(!$_G['group']['allowgetattach'] || !$_G['group']['allowgetimage']) {
				foreach($list['content'] as $key => $feed) {
					if(!$_G['group']['allowgetimage']) {
						$list['content'][$key]['content'] = preg_replace("/[ \t]*\<li\>\<img id=\"aimg_(.+?)\".*?\>[ \t]*\<\/li\>/is", '', $feed['content']);
					}
					if(!$_G['group']['allowgetattach']) {
						$list['content'][$key]['content'] = preg_replace("/[ \t]*\<li\>\<a href=\"(.+?)\" id=\"attach_(.+?)\".*?\>.*?\<\/a\>[ \t]*\<\/li\>/is", '', $feed['content']);
					}
				}
			}
			$list['threads'] = C::t('forum_thread')->fetch_all_by_tid(C::t('home_follow_feed')->get_tids());
			if (!empty($list['threads']) && is_array($list['threads'])) {
				foreach ($list['threads'] as $key => $thread) {
					if (empty($_G['setting']['followforumid']) || $thread['fid'] != $_G['setting']['followforumid']) {
						if (!empty($list['content'][$key]['content'])) {
							$list['content'][$key]['content'] = preg_replace('#onclick="changefeed\([^"]+\)"\s*style="cursor:\s*pointer;"#is', '', $list['content'][$key]['content']);
						}
					}
				}
			}
		}
	}
	return $list;
}

function getthread() {
	$threads = array();
	foreach(C::t('home_follow_feed')->get_ids() as $idtype => $ids) {
		if($idtype == 'thread') {
			$threads = C::t('forum_thread')->fetch_all_by_tid($ids);
		}
	}
	return $threads;
}

function show_view() {
	global $_G, $space;

	if(!$space['self'] && $_G['uid']) {
		$visitor = C::t('home_visitor')->fetch_by_uid_vuid($space['uid'], $_G['uid']);
		$is_anonymous = empty($_G['cookie']['anonymous_visit_'.$_G['uid'].'_'.$space['uid']]) ? 0 : 1;
		if(empty($visitor['dateline'])) {
			$setarr = array(
				'uid' => $space['uid'],
				'vuid' => $_G['uid'],
				'vusername' => $is_anonymous ? '' : $_G['username'],
				'dateline' => $_G['timestamp']
			);
			C::t('home_visitor')->insert($setarr, false, true);
			show_credit();
		} else {
			if($_G['timestamp'] - $visitor['dateline'] >= 300) {
				C::t('home_visitor')->update_by_uid_vuid($space['uid'], $_G['uid'], array('dateline'=>$_G['timestamp'], 'vusername'=>$is_anonymous ? '' : $_G['username']));
			}
			if($_G['timestamp'] - $visitor['dateline'] >= 3600) {
				show_credit();
			}
		}
		updatecreditbyaction('visit', 0, array(), $space['uid']);
	}
}

function show_credit() {
	global $_G, $space;

	$showinfo = C::t('home_show')->fetch($space['uid']);
	if($showinfo['credit'] > 0) {
		$showinfo['unitprice'] = intval($showinfo['unitprice']);
		if($showinfo['credit'] <= $showinfo['unitprice']) {
			notification_add($space['uid'], 'show', 'show_out');
			C::t('home_show')->delete($space['uid']);
		} else {
			C::t('home_show')->update_credit_by_uid($space['uid'], -$showinfo['unitprice']);
		}
	}
}

?>