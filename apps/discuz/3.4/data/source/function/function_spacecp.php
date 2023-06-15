<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_spacecp.php 36294 2016-12-14 03:11:30Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function album_creat_by_id($albumid, $catid = 0) {
	global $_G, $space;

	if(!$_G['uid']) {
		return 0;
	}
	preg_match("/^new\:(.+)$/i", $albumid, $matchs);
	if(!empty($matchs[1])) {
		$albumname = dhtmlspecialchars(trim($matchs[1]));
		if(empty($albumname)) $albumname = dgmdate($_G['timestamp'],'Ymd');
		$albumarr = array('albumname' => $albumname);
		if($catid) {
			$albumarr['catid'] = $catid;
		}
		$albumid = album_creat($albumarr);
	} else {
		$albumid = intval($albumid);
		if($albumid) {
			$value = C::t('home_album')->fetch_all_by_uid($_G['uid'], false, 0, 0, $albumid);
			if($value = $value[0]) {
				$albumname = addslashes($value['albumname']);
				$albumfriend = $value['friend'];
			} else {
				$albumname = dgmdate($_G['timestamp'],'Ymd');
				$albumarr = array('albumname' => $albumname);
				if($catid) {
					$albumarr['catid'] = $catid;
				}
				$albumid = album_creat($albumarr);
			}
		}
	}
	return $albumid;
}

function album_update_pic($albumid, $picid=0) {
	global $_G;

	$setarr = array();
	if(!$picid) {
		$piccount = C::t('home_pic')->check_albumpic($albumid, 0);
		if(empty($piccount) && C::t('home_pic')->check_albumpic($albumid) == 0) {
			C::t('home_album')->delete($albumid);
			return false;
		} else {
			$setarr['picnum'] = $piccount;
		}
	}
	$query = C::t('home_pic')->fetch_all_by_albumid($albumid, 0, 1, $picid, 1);
	if(!$pic = $query[0]) {
		return false;
	}
	$from = $pic['remote'];
	$pic['remote'] = $pic['remote'] > 1 ? $pic['remote'] - 2 : $pic['remote'];
	$basedir = !getglobal('setting/attachdir') ? (DISCUZ_ROOT.'./data/attachment/') : getglobal('setting/attachdir');
	$picdir = 'cover/'.substr(md5($albumid), 0, 2).'/';
	dmkdir($basedir.'./album/'.$picdir);
	if($pic['remote']) {
		$picsource = pic_get($pic['filepath'], $from > 1 ? 'forum' : 'album', $pic['thumb'], $pic['remote'], 0);
	} else {
		$picsource = $basedir.'./'.($from > 1 ? 'forum' : 'album').'/'.$pic['filepath'];
	}
	require_once libfile('class/image');
	$image = new image();
	if($image->Thumb($picsource, 'album/'.$picdir.$albumid.'.jpg', 300, 300, 2)) {
		$setarr['pic'] = $picdir.$albumid.'.jpg';
		$setarr['picflag'] = 1;
		if(ftpperm('jpg', filesize($_G['setting']['attachdir'].'album/'.$picdir.$albumid.'.jpg'))) {
			if(ftpcmd('upload', 'album/'.$picdir.$albumid.'.jpg')) {
				$setarr['picflag'] = 2;
				@unlink($_G['setting']['attachdir'].'album/'.$picdir.$albumid.'.jpg');
			}
		}
	} else {
		if($pic['status'] == 0) {
			$setarr['pic'] = $pic['thumb'] ? getimgthumbname($pic['filepath']) : $pic['filepath'];
		}
		if($from > 1) {
			$setarr['picflag'] = $pic['remote'] ? 4:3;
		} else {
			$setarr['picflag'] = $pic['remote'] ? 2:1;
		}
	}
	$setarr['updatetime'] = $_G['timestamp'];
	C::t('home_album')->update($albumid, $setarr);
	return true;
}

function pic_save($FILE, $albumid, $title, $iswatermark = true, $catid = 0) {
	global $_G, $space;

	if($albumid<0) $albumid = 0;

	$allowpictype = array('jpg','jpeg','gif','png');

	$upload = new discuz_upload();
	$upload->init($FILE, 'album');

	if($upload->error()) {
		return lang('spacecp', 'lack_of_access_to_upload_file_size');
	}

	if(!$upload->attach['isimage']) {
		return lang('spacecp', 'only_allows_upload_file_types');
	}
	$oldgid = $_G['groupid'];
	if(empty($space)) {
		$_G['member'] = $space = getuserbyuid($_G['uid']);
		$_G['username'] = $space['username'];
		$_G['groupid'] = $space['groupid'];
	}
	$_G['member'] = $space;

	loadcache('usergroup_'.$space['groupid'], $oldgid != $_G['groupid'] ? true : false);
	$_G['group'] = $_G['cache']['usergroup_'.$space['groupid']];

	if(!checkperm('allowupload')) {
		return lang('spacecp', 'not_allow_upload');
	}

	if(!cknewuser(1)) {
		if($_G['setting']['newbiespan'] && $_G['timestamp'] - $_G['member']['regdate'] < $_G['setting']['newbiespan'] * 60) {
			return lang('message', 'no_privilege_newbiespan', array('newbiespan' => $_G['setting']['newbiespan']));
		}

		if($_G['setting']['need_avatar'] && empty($_G['member']['avatarstatus'])) {
			return lang('message', 'no_privilege_avatar');
		}

		if($_G['setting']['need_email'] && empty($_G['member']['emailstatus'])) {
			return lang('message', 'no_privilege_email');
		}

		if($_G['setting']['need_friendnum']) {
			space_merge($_G['member'], 'count');
			if($_G['member']['friends'] < $_G['setting']['need_friendnum']) {
				return lang('message', 'no_privilege_friendnum', array('friendnum' => $_G['setting']['need_friendnum']));
			}
		}
	}
	if($_G['group']['maximagesize'] && $upload->attach['size'] > $_G['group']['maximagesize']) {
		return lang('spacecp', 'files_can_not_exceed_size', array('extend' => $upload->attach['ext'], 'size' => sizecount($_G['group']['maximagesize'])));
	}

	$maxspacesize = checkperm('maxspacesize');
	if($maxspacesize) {
		space_merge($space, 'count');
		space_merge($space, 'field_home');
		if($space['attachsize'] + $upload->attach['size'] > $maxspacesize + $space['addsize'] * 1024 * 1024) {
			return lang('spacecp', 'inadequate_capacity_space');
		}
	}

	$showtip = true;
	$albumfriend = 0;
	if($albumid) {
		$catid = intval($catid);
		$albumid = album_creat_by_id($albumid, $catid);
	} else {
		$albumid = 0;
		$showtip = false;
	}

	$upload->save();
	if($upload->error()) {
		return lang('spacecp', 'mobile_picture_temporary_failure');
	}
	if(!$upload->attach['imageinfo'] || !in_array($upload->attach['imageinfo']['2'], array(1,2,3,6))) {
		@unlink($upload->attach['target']);
		return lang('spacecp', 'only_allows_upload_file_types');
	}

	$new_name = $upload->attach['target'];

	require_once libfile('class/image');
	$image = new image();
	$result = $image->Thumb($new_name, '', 140, 140, 1);
	$thumb = empty($result)?0:1;

	if($_G['setting']['maxthumbwidth'] && $_G['setting']['maxthumbheight']) {
		if($_G['setting']['maxthumbwidth'] < 300) $_G['setting']['maxthumbwidth'] = 300;
		if($_G['setting']['maxthumbheight'] < 300) $_G['setting']['maxthumbheight'] = 300;
		$image->Thumb($new_name, '', $_G['setting']['maxthumbwidth'], $_G['setting']['maxthumbheight'], 1, 1);
	}

	if ($iswatermark) {
		$image->Watermark($new_name, '', 'album');
	}
	$pic_remote = 0;
	$album_picflag = 1;

	if(ftpperm($upload->attach['ext'], $upload->attach['size'])) {
		$ftpresult_thumb = 0;
		$ftpresult = ftpcmd('upload', 'album/'.$upload->attach['attachment']);
		if($ftpresult) {
			@unlink($_G['setting']['attachdir'].'album/'.$upload->attach['attachment']);
			if($thumb) {
				$thumbpath = getimgthumbname($upload->attach['attachment']);
				ftpcmd('upload', 'album/'.$thumbpath);
				@unlink($_G['setting']['attachdir'].'album/'.$thumbpath);
			}
			$pic_remote = 1;
			$album_picflag = 2;
		} else {
			if(getglobal('setting/ftp/mirror')) {
				@unlink($upload->attach['target']);
				@unlink(getimgthumbname($upload->attach['target']));
				return lang('spacecp', 'ftp_upload_file_size');
			}
		}
	}

	$title = getstr($title, 200);
	$title = censor($title, NULL, TRUE, FALSE);
	if(is_array($title)) {
		return lang('message', 'word_banned');
	}
	if(censormod($title) || $_G['group']['allowuploadmod']) {
		$pic_status = 1;
	} else {
		$pic_status = 0;
	}

	$setarr = array(
		'albumid' => $albumid,
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'dateline' => $_G['timestamp'],
		'filename' => addslashes($upload->attach['name']),
		'postip' => $_G['clientip'],
		'port' => $_G['remoteport'],
		'title' => $title,
		'type' => addslashes($upload->attach['ext']),
		'size' => $upload->attach['size'],
		'filepath' => $upload->attach['attachment'],
		'thumb' => $thumb,
		'remote' => $pic_remote,
		'status' => $pic_status,
	);
	$setarr['picid'] = C::t('home_pic')->insert($setarr, 1);

	C::t('common_member_count')->increase($_G['uid'], array('attachsize' => $upload->attach['size']));

	include_once libfile('function/stat');
	if($pic_status) {
		updatemoderate('picid', $setarr['picid']);
	}
	updatestat('pic');

	return $setarr;
}

function stream_save($strdata, $albumid = 0, $fileext = 'jpg', $name='', $title='', $delsize=0, $from = false) {
	global $_G, $space;

	if($albumid<0) $albumid = 0;
	$allowPicType = array('jpg','jpeg','gif','png');
	if(!in_array($fileext, $allowPicType)) {
		return -3;
	}
	$setarr = array();

	$upload = new discuz_upload();

	$filepath = $upload->get_target_dir('album').$upload->get_target_filename('album').'.'.$fileext;
	$newfilename = $_G['setting']['attachdir'].'./album/'.$filepath;

	if($handle = fopen($newfilename, 'wb')) {
		if(fwrite($handle, $strdata) !== FALSE) {
			fclose($handle);

			$size = filesize($newfilename);

			if(empty($space)) {
				$_G['member'] = $space = getuserbyuid($_G['uid']);
				$_G['username'] = $space['username'];
			}
			$_G['member'] = $space;
			loadcache('usergroup_'.$space['groupid']);
			$_G['group'] = $_G['cache']['usergroup_'.$space['groupid']];

			$maxspacesize = checkperm('maxspacesize');
			if($maxspacesize) {

				space_merge($space, 'count');
				space_merge($space, 'field_home');

				if($space['attachsize'] + $size - $delsize > $maxspacesize + $space['addsize'] * 1024 * 1024) {
					@unlink($newfilename);
					return -1;
				}
			}

			if(!$upload->get_image_info($newfilename)) {
				@unlink($newfilename);
				return -2;
			}

			require_once libfile('class/image');
			$image = new image();
			$result = $image->Thumb($newfilename, NULL, 140, 140, 1);
			$thumb = empty($result)?0:1;

			$image->Watermark($newfilename);

			$pic_remote = 0;
			$album_picflag = 1;

			if(ftpperm($fileext, filesize($_G['setting']['attachdir'].'album/'.$filepath))) {
				$ftpresult_thumb = 0;
				$ftpresult = ftpcmd('upload', 'album/'.$filepath);
				if($ftpresult) {
					@unlink($_G['setting']['attachdir'].'album/'.$filepath);
					if($thumb) {
						$thumbpath = getimgthumbname($filepath);
						ftpcmd('upload', 'album/'.$thumbpath);
						@unlink($_G['setting']['attachdir'].'album/'.$thumbpath);
					}
					$pic_remote = 1;
					$album_picflag = 2;
				} else {
					if(getglobal('setting/ftp/mirror')) {
						@unlink($newfilename);
						@unlink(getimgthumbname($newfilename));
						return -3;
					}
				}
			}

			$filename = $name ? $name : substr(strrchr($filepath, '/'), 1);
			$title = getstr($title, 200);
			$title = censor($title, NULL, TRUE, FALSE);
			if(is_array($title)) {
				return lang('message', 'word_banned');
			}
			if(censormod($title) || $_G['group']['allowuploadmod']) {
				$pic_status = 1;
			} else {
				$pic_status = 0;
			}

			if($albumid) {
				$albumid = album_creat_by_id($albumid);
			} else {
				$albumid = 0;
			}

			$setarr = array(
				'albumid' => $albumid,
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'dateline' => $_G['timestamp'],
				'filename' => $filename,
				'postip' => $_G['clientip'],
				'port' => $_G['remoteport'],
				'title' => $title,
				'type' => $fileext,
				'size' => $size,
				'filepath' => $filepath,
				'thumb' => $thumb,
				'remote' => $pic_remote,
				'status' => $pic_status,
			);
			$setarr['picid'] = C::t('home_pic')->insert($setarr, 1);

			C::t('common_member_count')->increase($_G['uid'], array('attachsize' => $size));

			include_once libfile('function/stat');
			updatestat('pic');

			return $setarr;
		} else {
			fclose($handle);
		}
	}
	return -3;
}

function album_creat($arr) {
	global $_G;

	$albumid = C::t('home_album')->fetch_albumid_by_albumname_uid($arr['albumname'], $_G['uid']);
	if($albumid) {
		return $albumid;
	} else {
		$arr['uid'] = $_G['uid'];
		$arr['username'] = $_G['username'];
		$arr['dateline'] = $arr['updatetime'] = $_G['timestamp'];
		$albumid = C::t('home_album')->insert($arr, TRUE);

		C::t('common_member_count')->increase($_G['uid'], array('albums' => 1));
		if(isset($arr['catid']) && $arr['catid']) {
			C::t('home_album_category')->update_num_by_catid('1', $arr['catid']);
		}

		return $albumid;
	}
}

function getfilepath($fileext, $mkdir=false) {
	global $_G;

	$filepath = "{$_G['uid']}_{$_G['timestamp']}".random(4).".$fileext";
	$name1 = gmdate('Ym');
	$name2 = gmdate('j');

	if($mkdir) {
		$newfilename = $_G['setting']['attachdir'].'./album/'.$name1;
		if(!is_dir($newfilename)) {
			if(!@mkdir($newfilename)) {
				runlog('error', "DIR: $newfilename can not make");
				return $filepath;
			}
		}
		$newfilename .= '/'.$name2;
		if(!is_dir($newfilename)) {
			if(!@mkdir($newfilename)) {
				runlog('error', "DIR: $newfilename can not make");
				return $name1.'/'.$filepath;
			}
		}
	}
	return $name1.'/'.$name2.'/'.$filepath;
}

function getalbumpic($uid, $id) {
	global $_G;

	$pic = C::t('home_pic')->fetch_album_pic($id, $uid);
	if($pic) {
		return $pic['thumb'] ? getimgthumbname($pic['filepath']) : $pic['filepath'];
	} else {
		return '';
	}
}

function getclassarr($uid) {
	global $_G;

	$classarr = array();
	$query = C::t('home_class')->fetch_all_by_uid($uid);
	foreach($query as $value) {
		$classarr[$value['classid']] = $value;
	}
	return $classarr;
}

function getalbums($uid) {
	global $_G;

	$albums = array();
	$query = C::t('home_album')->fetch_all_by_uid($uid, 'albumid');
	foreach($query as $value) {
		$albums[$value['albumid']] = $value;
	}
	return $albums;
}

function hot_update($idtype, $id, $hotuser) {
	global $_G;

	$hotusers = empty($hotuser)?array():explode(',', $hotuser);
	if($hotusers && in_array($_G['uid'], $hotusers)) {
		return false;
	} else {
		$hotusers[] = $_G['uid'];
		$hotuser = implode(',', $hotusers);
	}
	$hotuser = daddslashes($hotuser);
	$newhot = count($hotusers)+1;
	if($newhot == $_G['setting']['feedhotmin']) {
		$tablename = gettablebyidtype($idtype);
		if($tablename) {
			$item = C::t($tablename)->fetch_by_id_idtype($id);
			$itemuid = $item['uid'];
			updatecreditbyaction('hotinfo', $itemuid);
		}
	}

	switch ($idtype) {
		case 'blogid':
			C::t('home_blogfield')->update($id, array('hotuser' => $hotuser));
			C::t('home_blog')->increase($id, 0, array('hot' => 1));
			break;
		case 'picid':
			C::t('home_picfield')->insert(array('picid' => $id, 'hotuser' => $hotuser), 0, 1);
			C::t('home_pic')->update_hot($id);
			break;
		case 'sid':
			C::t('home_share')->update_hot_by_sid($id, $hotuser);
			break;
		default:
			return false;
	}
	if($feed = C::t('home_feed')->fetch($id, $idtype)) {
		if(empty($feed['friend'])) {
			C::t('home_feed')->update_hot_by_feedid($feed['feedid'], 1);
		}
	} elseif($idtype == 'picid') {
		require_once libfile('function/feed');
		feed_publish($id, $idtype);
	}

	return true;
}

function gettablebyidtype($idtype) {
	$tablename = '';
	if($idtype == 'blogid') {
		$tablename = 'home_blog';
	} elseif($idtype == 'picid') {
		$tablename = 'home_pic';
	} elseif($idtype == 'sid') {
		$tablename = 'home_share';
	}
	return $tablename;
}

function privacy_update() {
	global $_G, $space;

	C::t('common_member_field_home')->update($_G['uid'], array('privacy'=>serialize($space['privacy'])));
}

function ckrealname($return=0) {
	global $_G;

	$result = true;
	if($_G['adminid'] != 1 && $_G['setting']['verify'][6]['available'] && empty($_G['setting']['verify'][6]['viewrealname'])) {
		space_merge($_G['member'], 'profile');
		space_merge($_G['member'], 'verify');
		if(empty($_G['member']['realname']) || !$_G['member']['verify6']) {
			if(empty($return)) showmessage('no_privilege_realname', '', array(), array('return' => true));
			$result = false;
		}
	}
	return $result;
}

function ckvideophoto($tospace=array(), $return=0) {
	global $_G;

	if($_G['adminid'] != 1 && empty($_G['setting']['verify'][7]['available']) || $_G['member']['videophotostatus']) {
		return true;
	}

	space_merge($tospace, 'field_home');

	$result = true;
	if(empty($tospace) || empty($tospace['privacy']['view']['videoviewphoto'])) {
		if(!checkperm('videophotoignore') && empty($_G['setting']['verify'][7]['viewvideophoto']) && !checkperm('allowviewvideophoto')) {
			$result = false;
		}
	} elseif ($tospace['privacy']['view']['videoviewphoto'] == 2) {
		$result = false;
	}
	if($return) {
		return $result;
	} elseif(!$result) {
		showmessage('no_privilege_videophoto', '', array(), array('return' => true));
	}
}

function getvideophoto($filename) {
	$dir1 = substr($filename, 0, 1);
	$dir2 = substr($filename, 1, 1);
	return 'data/avatar/'.$dir1.'/'.$dir2.'/'.$filename.".jpg";
}

function videophoto_upload($FILE, $uid) {
	if($FILE['size']) {
		$newfilename = md5(substr($_G['timestamp'], 0, 7).$uid);
		$dir1 = substr($newfilename, 0, 1);
		$dir2 = substr($newfilename, 1, 1);
		if(!is_dir(DISCUZ_ROOT.'./data/avatar/'.$dir1)) {
			if(!mkdir(DISCUZ_ROOT.'./data/avatar/'.$dir1)) return '';
		}
		if(!is_dir(DISCUZ_ROOT.'./data/avatar/'.$dir1.'/'.$dir2)) {
			if(!mkdir(DISCUZ_ROOT.'./data/avatar/'.$dir1.'/'.$dir2)) return '';
		}
		$new_name = DISCUZ_ROOT.'./'.getvideophoto($newfilename);
		$tmp_name = $FILE['tmp_name'];
		if(@copy($tmp_name, $new_name)) {
			@unlink($tmp_name);
		} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmp_name, $new_name))) {
		} elseif(@rename($tmp_name, $new_name)) {
		} else {
			return '';
		}
		return $newfilename;
	} else {
		return '';
	}
}

function isblacklist($touid) {
	global $_G;

	return C::t('home_blacklist')->count_by_uid_buid($touid, $_G['uid']);
}

function emailcheck_send($uid, $email) {
	global $_G;

	if($uid && $email) {
		$memberauthstr = C::t('common_member_field_forum')->fetch($uid);
		if(!empty($memberauthstr['authstr'])) {
			list($dateline) = explode("\t", $memberauthstr['authstr']);
			$interval = $_G['setting']['mailinterval'] > 0 ? (int)$_G['setting']['mailinterval'] : 300;
			if($dateline && $dateline > TIMESTAMP - $interval) {
				return false;
			}
		}
		$timestamp = $_G['timestamp'];
		$idstring = substr(md5($email), 0, 6);
		C::t('common_member_field_forum')->update($uid, array('authstr' => "$timestamp\t3\t$idstring"));

		$hash = authcode("$uid\t$email\t$timestamp", 'ENCODE', md5(substr(md5($_G['config']['security']['authkey']), 0, 16)));
		$verifyurl = $_G['setting']['securesiteurl'].'home.php?mod=misc&amp;ac=emailcheck&amp;hash='.urlencode($hash);
		$mailsubject = lang('email', 'email_verify_subject');
		$mailmessage = lang('email', 'email_verify_message', array(
			'username' => $_G['member']['username'],
			'bbname' => $_G['setting']['bbname'],
			'siteurl' => $_G['setting']['securesiteurl'],
			'url' => $verifyurl
		));

		require_once libfile('function/mail');
		if(!sendmail($email, $mailsubject, $mailmessage)) {
			runlog('sendmail', "$email sendmail failed.");
		}
	}
	return true;
}

function picurl_get($picurl, $maxlenth='200') {
	$picurl = dhtmlspecialchars(trim($picurl));
	if($picurl) {
		if(preg_match("/^http\:\/\/.{5,$maxlenth}\.(jpg|gif|png)$/i", $picurl)) return $picurl;
	}
	return '';
}

function avatar_file($uid, $size) {
	global $_G;

	$var = "home_avatarfile_{$uid}_{$size}";
	if(empty($_G[$var])) {
		$uid = abs(intval($uid));
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$_G[$var] = $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2)."_avatar_$size.jpg";
	}
	return $_G[$var];
}

function makepokeaction($iconid) {
	global $_G;
	$icons = array(
		0 => lang('home/template', 'say_hi'),
		1 => '<img alt="cyx" src="'.STATICURL.'image/poke/cyx.gif" class="vm" /> '.lang('home/template', 'poke_1'),
		2 => '<img alt="wgs" src="'.STATICURL.'image/poke/wgs.gif" class="vm" /> '.lang('home/template', 'poke_2'),
		3 => '<img alt="wx" src="'.STATICURL.'image/poke/wx.gif" class="vm" /> '.lang('home/template', 'poke_3'),
		4 => '<img alt="jy" src="'.STATICURL.'image/poke/jy.gif" class="vm" /> '.lang('home/template', 'poke_4'),
		5 => '<img alt="pmy" src="'.STATICURL.'image/poke/pmy.gif" class="vm" /> '.lang('home/template', 'poke_5'),
		6 => '<img alt="yb" src="'.STATICURL.'image/poke/yb.gif" class="vm" /> '.lang('home/template', 'poke_6'),
		7 => '<img alt="fw" src="'.STATICURL.'image/poke/fw.gif" class="vm" /> '.lang('home/template', 'poke_7'),
		8 => '<img alt="nyy" src="'.STATICURL.'image/poke/nyy.gif" class="vm" /> '.lang('home/template', 'poke_8'),
		9 => '<img alt="gyq" src="'.STATICURL.'image/poke/gyq.gif" class="vm" /> '.lang('home/template', 'poke_9'),
		10 => '<img alt="dyx" src="'.STATICURL.'image/poke/dyx.gif" class="vm" /> '.lang('home/template', 'poke_10'),
		11 => '<img alt="yw" src="'.STATICURL.'image/poke/yw.gif" class="vm" /> '.lang('home/template', 'poke_11'),
		12 => '<img alt="ppjb" src="'.STATICURL.'image/poke/ppjb.gif" class="vm" /> '.lang('home/template', 'poke_12'),
		13 => '<img alt="yyk" src="'.STATICURL.'image/poke/yyk.gif" class="vm" /> '.lang('home/template', 'poke_13')
	);
	return isset($icons[$iconid]) ? $icons[$iconid] : $icons[0];
}

function interval_check($type) {
	global $_G;

	$waittime = 0;
	if(checkperm('disablepostctrl')) {
		return $waittime;
	}
	if($_G['setting']['floodctrl']) {
		space_merge($_G['member'], 'status');
		getuserprofile('lastpost');
		$waittime = $_G['setting']['floodctrl'] - ($_G['timestamp'] - $_G['member']['lastpost']);
	}
	return $waittime;
}

function geturltitle($link, $charset = '') {
	$title = $linkcharset = '';
	$linkstr = gzfile($link);
	$linkstr = implode('', $linkstr);
	if(!$charset) {
		preg_match('/<meta [^>]*charset="?(.*)"/i', $linkstr, $linkcharset);
		$charset = strtolower($linkcharset[1]);
	}
	if(!$charset) {
		return $title;
	}
	if($charset != strtolower(CHARSET)) {
		$linkstr = diconv($linkstr, $charset);
	}
	if(!empty($linkstr) && preg_match('/\<title\>(.*)\<\/title\>/i', $linkstr, $title)) {
		$tmptitle = explode('_', $title[1]);
		if($title[1] == $tmptitle[0]) {
			$tmptitle = explode('-', $title[1]);
		}
		$title = trim($tmptitle[0]);
	}
	return $title;
}

function allowverify($vid = 0) {
	global $_G;

	if(empty($_G['setting']['verify'])) {
		loadcache('setting');
	}
	$allow = false;
	$vid = 0 < $vid && $vid < 8 ? intval($vid) : 0;
	if($vid) {
		$setting = $_G['setting']['verify'][$vid];
		if($setting['available'] && (empty($setting['groupid']) || in_array($_G['groupid'], $setting['groupid']))) {
			$allow = true;
		}
	} else {
		foreach($_G['setting']['verify'] as $key => $setting) {
			if($setting['available'] && (empty($setting['groupid']) || in_array($_G['groupid'], $setting['groupid']))) {
				$allow = true;
				break;
			}
		}
	}
	return $allow;
}
?>