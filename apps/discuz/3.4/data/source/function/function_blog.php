<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_blog.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function blog_check_url($url) {
	$url = durlencode(trim($url));

	if(preg_match("/^(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\//i", $url)) {
		$return = '<a href="'.$url.'" target="_blank">';
	} else {
		$return = '<a href="'.(!empty($GLOBALS['_G']['siteurl']) ? $GLOBALS['_G']['siteurl'] : 'http://').$url.'" target="_blank">';
	}
	return $return;
}
function blog_post($POST, $olds=array()) {
	global $_G, $space;

	$isself = 1;
	if(!empty($olds['uid']) && $olds['uid'] != $_G['uid']) {
		$isself = 0;
		$__G = $_G;
		$_G['uid'] = $olds['uid'];
		$_G['username'] = addslashes($olds['username']);
	}

	$POST['subject'] = getstr(trim($POST['subject']), 80);
	$POST['subject'] = censor($POST['subject'], NULL, FALSE, FALSE);
	if(strlen($POST['subject'])<1) $POST['subject'] = dgmdate($_G['timestamp'], 'Y-m-d');
	$POST['friend'] = intval($POST['friend']);

	$POST['target_ids'] = '';
	if($POST['friend'] == 2) {
		$uids = array();
		$names = empty($_POST['target_names'])?array():explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_names']));
		if($names) {
			$uids = C::t('common_member')->fetch_all_uid_by_username($names);
		}
		if(empty($uids)) {
			$POST['friend'] = 3;
		} else {
			$POST['target_ids'] = implode(',', $uids);
		}
	} elseif($POST['friend'] == 4) {
		$POST['password'] = trim($POST['password']);
		if($POST['password'] == '') $POST['friend'] = 0;
	}
	if($POST['friend'] !== 2) {
		$POST['target_ids'] = '';
	}
	if($POST['friend'] !== 4) {
		$POST['password'] = '';
	}

	$POST['tag'] = dhtmlspecialchars(trim($POST['tag']));
	$POST['tag'] = getstr($POST['tag'], 500);
	$POST['tag'] = censor($POST['tag']);

	$POST['message'] = preg_replace("/\<div\>\<\/div\>/i", '', $POST['message']);
	$POST['message'] = checkhtml($POST['message']);
		$POST['message'] = getstr($POST['message'], 0, 0, 0, 0, 1);
		$POST['message'] = censor($POST['message'], NULL, FALSE, FALSE);
		$POST['message'] = preg_replace_callback("/<a .*?href=\"(.*?)\".*?>/is", 'blog_post_callback_blog_check_url_1', $POST['message']);

	$message = $POST['message'];
	if(censormod($message) || censormod($POST['subject']) || $_G['group']['allowblogmod']) {
		$blog_status = 1;
	} else {
		$blog_status = 0;
	}

	if(empty($olds['classid']) || $POST['classid'] != $olds['classid']) {
		if(!empty($POST['classid']) && substr($POST['classid'], 0, 4) == 'new:') {
			$classname = dhtmlspecialchars(trim(substr($POST['classid'], 4)));
			$classname = getstr($classname);
			$classname = censor($classname);
			if(empty($classname)) {
				$classid = 0;
			} else {
				$classid = C::t('home_class')->fetch_classid_by_uid_classname($_G['uid'], $classname);
				if(empty($classid)) {
					$setarr = array(
						'classname' => $classname,
						'uid' => $_G['uid'],
						'dateline' => $_G['timestamp']
					);
					$classid = C::t('home_class')->insert($setarr, true);
				}
			}
		} else {
			$classid = intval($POST['classid']);

		}
	} else {
		$classid = $olds['classid'];
	}
	if($classid && empty($classname)) {
		$query = C::t('home_class')->fetch($classid);
		$classname = ($query['uid'] == $_G['uid']) ? $query['classname'] : '';
		if(empty($classname)) $classid = 0;
	}

	$blogarr = array(
		'subject' => $POST['subject'],
		'classid' => $classid,
		'friend' => $POST['friend'],
		'password' => $POST['password'],
		'noreply' => empty($POST['noreply'])?0:1,
		'catid' => intval($POST['catid']),
		'status' => $blog_status,
	);

	$titlepic = '';

	$uploads = array();
	if(!empty($POST['picids'])) {
		$picids = array_keys($POST['picids']);
		$query = C::t('home_pic')->fetch_all_by_uid($_G['uid'], 0, 0, $picids);
		foreach($query as $value) {
			if(empty($titlepic) && $value['thumb']) {
				$titlepic = getimgthumbname($value['filepath']);
				$blogarr['picflag'] = $value['remote']?2:1;
			}
			$picurl = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote'], 0);
			$uploads[md5($picurl)] = $value;
		}
		if(empty($titlepic) && $value) {
			$titlepic = $value['filepath'];
			$blogarr['picflag'] = $value['remote']?2:1;
		}
	}

	if($uploads) {
		$albumid = 0;
		if($POST['savealbumid'] < 0 && !empty($POST['newalbum'])) {
			$albumname = addslashes(dhtmlspecialchars(trim($POST['newalbum'])));
			if(empty($albumname)) $albumname = dgmdate($_G['timestamp'],'Ymd');
			$albumarr = array('albumname' => $albumname);
			$albumid = album_creat($albumarr);
		} else {
			$albumid = $POST['savealbumid'] < 0 ? 0 : intval($POST['savealbumid']);
			$albuminfo = C::t('home_album')->fetch($albumid, $_G['uid']);
			if(empty($albuminfo)) {
				$albumid = 0;
			}
		}
		if($albumid) {
			C::t('home_pic')->update_for_uid($_G['uid'], $picids, array('albumid' => $albumid));
			album_update_pic($albumid);
		}
		preg_match_all("/\s*\<img src=\"(.+?)\".*?\>\s*/is", $message, $mathes);
		if(!empty($mathes[1])) {
			foreach ($mathes[1] as $key => $value) {
				$urlmd5 = md5($value);
				if(!empty($uploads[$urlmd5])) {
					unset($uploads[$urlmd5]);
				}
			}
		}
		foreach ($uploads as $value) {
			$picurl = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote'], 0);
			$message .= "<div class=\"uchome-message-pic\"><img src=\"$picurl\"><p>$value[title]</p></div>";
		}
	}

	$ckmessage = preg_replace("/(\<div\>|\<\/div\>|\s|\&nbsp\;|\<br\>|\<p\>|\<\/p\>)+/is", '', $message);
	if(empty($ckmessage)) {
		return false;
	}


	if(checkperm('manageblog')) {
		$blogarr['hot'] = intval($POST['hot']);
	}

	if($olds['blogid']) {

		if($blogarr['catid'] != $olds['catid']) {
			if($olds['catid']) {
				C::t('home_blog_category')->update_num_by_catid(-1, $olds['catid'], true, true);
			}
			if($blogarr['catid']) {
				C::t('home_blog_category')->update_num_by_catid(1, $blogarr['catid']);
			}
		}

		$blogid = $olds['blogid'];
		C::t('home_blog')->update($blogid, $blogarr);

		$fuids = array();

		$blogarr['uid'] = $olds['uid'];
		$blogarr['username'] = $olds['username'];
	} else {

		if($blogarr['catid']) {
			C::t('home_blog_category')->update_num_by_catid(1, $blogarr['catid']);
		}

		$blogarr['uid'] = $_G['uid'];
		$blogarr['username'] = $_G['username'];
		$blogarr['dateline'] = empty($POST['dateline'])?$_G['timestamp']:$POST['dateline'];
		$blogid = C::t('home_blog')->insert($blogarr, true);

		C::t('common_member_status')->update($_G['uid'], array('lastpost' => $_G['timestamp']));
		C::t('common_member_field_home')->update($_G['uid'], array('recentnote'=>$POST['subject']));
	}

	$blogarr['blogid'] = $blogid;
	$class_tag = new tag();
	$POST['tag'] = $olds ? $class_tag->update_field($POST['tag'], $blogid, 'blogid') : $class_tag->add_tag($POST['tag'], $blogid, 'blogid');
	$fieldarr = array(
		'message' => $message,
		'postip' => $_G['clientip'],
		'port' => $_G['remoteport'],
		'target_ids' => $POST['target_ids'],
		'tag' => $POST['tag']
	);

	if(!empty($titlepic)) {
		$fieldarr['pic'] = $titlepic;
	}

	if($olds) {
		C::t('home_blogfield')->update($blogid, $fieldarr);
	} else {
		$fieldarr['blogid'] = $blogid;
		$fieldarr['uid'] = $blogarr['uid'];
		C::t('home_blogfield')->insert($fieldarr);
	}

	if($isself && !$olds && $blog_status == 0) {
		updatecreditbyaction('publishblog', 0, array('blogs' => 1));

		include_once libfile('function/stat');
		updatestat('blog');
	}

	if($olds['blogid'] && $blog_status == 1) {
		updatecreditbyaction('publishblog', 0, array('blogs' => -1), '', -1);
		include_once libfile('function/stat');
		updatestat('blog');
	}

	if($POST['makefeed'] && $blog_status == 0) {
		include_once libfile('function/feed');
		feed_publish($blogid, 'blogid', $olds?0:1);
	}

	if(!empty($__G)) $_G = $__G;
	if($blog_status == 1) {
		updatemoderate('blogid', $blogid);
		manage_addnotify('verifyblog');
	}
	return $blogarr;
}

function blog_post_callback_blog_check_url_1($matches) {
	return blog_check_url($matches[1]);
}

function checkhtml($html) {
	if(!checkperm('allowhtml')) {

		preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

		$searchs[] = '<';
		$replaces[] = '&lt;';
		$searchs[] = '>';
		$replaces[] = '&gt;';

		if($ms[1]) {
			$allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote';
			$ms[1] = array_unique($ms[1]);
			foreach ($ms[1] as $value) {
				$searchs[] = "&lt;".$value."&gt;";

				$value = str_replace('&amp;', '_uch_tmp_str_', $value);
				$value = dhtmlspecialchars($value);
				$value = str_replace('_uch_tmp_str_', '&amp;', $value);

				$value = str_replace(array('\\','/*'), array('.','/.'), $value);
				$skipkeys = array('onabort','onactivate','onafterprint','onafterupdate','onbeforeactivate','onbeforecopy','onbeforecut','onbeforedeactivate',
						'onbeforeeditfocus','onbeforepaste','onbeforeprint','onbeforeunload','onbeforeupdate','onblur','onbounce','oncellchange','onchange',
						'onclick','oncontextmenu','oncontrolselect','oncopy','oncut','ondataavailable','ondatasetchanged','ondatasetcomplete','ondblclick',
						'ondeactivate','ondrag','ondragend','ondragenter','ondragleave','ondragover','ondragstart','ondrop','onerror','onerrorupdate',
						'onfilterchange','onfinish','onfocus','onfocusin','onfocusout','onhelp','onkeydown','onkeypress','onkeyup','onlayoutcomplete',
						'onload','onlosecapture','onmousedown','onmouseenter','onmouseleave','onmousemove','onmouseout','onmouseover','onmouseup','onmousewheel',
						'onmove','onmoveend','onmovestart','onpaste','onpropertychange','onreadystatechange','onreset','onresize','onresizeend','onresizestart',
						'onrowenter','onrowexit','onrowsdelete','onrowsinserted','onscroll','onselect','onselectionchange','onselectstart','onstart','onstop',
						'onsubmit','onunload','javascript','script','eval','behaviour','expression','style','class');
				$skipstr = implode('|', $skipkeys);
				$value = preg_replace(array("/($skipstr)/i"), '.', $value);
				if(!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
					$value = '';
				}
				$replaces[] = empty($value)?'':"<".str_replace('&quot;', '"', $value).">";
			}
		}
		$html = str_replace($searchs, $replaces, $html);
	}

	return $html;
}

function blog_bbcode($message) {
	$message = preg_replace_callback("/\[flash\=?(media|real|mp3)*\](.+?)\[\/flash\]/i", 'blog_bbcode_callback_blog_flash_21', $message);
	return $message;
}

function blog_bbcode_callback_blog_flash_21($matches) {
	return blog_flash($matches[2], $matches[1]);
}

function blog_flash($url, $type='') {
	$width = '520';
	$height = '390';
	preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^\[\"']+/i", $url, $matches);
	$url = $matches[0];
	require_once libfile('function/discuzcode');
	if($flv = parseflv($url, $width, $height)) {
		return $flv;
	}
	$type = fileext($url);
	$randomid = random(3);
	return '<ignore_js_op><div id="'.$type.'_'.$randomid.'" class="media"><div id="'.$type.'_'.$randomid.'_container" class="media_container"></div><div id="'.$type.'_'.$randomid.'_tips" class="media_tips"><a href="'.$url.'" target="_blank">'.lang('template', 'parse_av_tips').'</a></div></div><script type="text/javascript">detectPlayer("'.$type.'_'.$randomid.'", "'.$type.'", "'.$url.'", "'.$width.'", "'.$height.'");</script></ignore_js_op>';
}
?>