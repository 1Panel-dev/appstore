<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_ajax.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

if($_GET['action'] == 'checkusername') {


	$username = trim($_GET['username']);
	$usernamelen = dstrlen($username);
	if($usernamelen < 3) {
		showmessage('profile_username_tooshort', '', array(), array('handle' => false));
	} elseif($usernamelen > 15) {
		showmessage('profile_username_toolong', '', array(), array('handle' => false));
	}

	loaducenter();
	$ucresult = uc_user_checkname($username);

	if($ucresult == -1) {
		showmessage('profile_username_illegal', '', array(), array('handle' => false));
	} elseif($ucresult == -2) {
		showmessage('profile_username_protect', '', array(), array('handle' => false));
	} elseif($ucresult == -3) {
		if(C::t('common_member')->fetch_by_username($username) || C::t('common_member_archive')->fetch_by_username($username)) {
			showmessage('register_check_found', '', array(), array('handle' => false));
		} else {
			showmessage('register_activation', '', array(), array('handle' => false));
		}
	}

	$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')).')$/i';
	if($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
		showmessage('profile_username_protect', '', array(), array('handle' => false));
	}

} elseif($_GET['action'] == 'checkemail') {

	require_once libfile('function/member');
	checkemail($_GET['email']);

} elseif($_GET['action'] == 'checkinvitecode') {

	$invitecode = trim($_GET['invitecode']);
	if(!$invitecode) {
		showmessage('no_invitation_code', '', array(), array('handle' => false));
	}
	$result = array();
	if($invite = C::t('common_invite')->fetch_by_code($invitecode)) {
		if(empty($invite['fuid']) && (empty($invite['endtime']) || $_G['timestamp'] < $invite['endtime'])) {
			$result['uid'] = $invite['uid'];
			$result['id'] = $invite['id'];
			$result['appid'] = $invite['appid'];
		}
	}
	if(empty($result)) {
		showmessage('wrong_invitation_code', '', array(), array('handle' => false));
	}

} elseif($_GET['action'] == 'checkuserexists') {

	if(C::t('common_member')->fetch_by_username(trim($_GET['username'])) || C::t('common_member_archive')->fetch_by_username(trim($_GET['username']))) {
		showmessage('<img src="'.$_G['style']['imgdir'].'/check_right.gif" width="13" height="13">', '', array(), array('msgtype' => 3));
	} else {
		showmessage('username_nonexistence', '', array(), array('msgtype' => 3));
	}

} elseif($_GET['action'] == 'attachlist') {

	require_once libfile('function/post');
	loadcache('groupreadaccess');
	$attachlist = getattach($_GET['pid'], intval($_GET['posttime']), $_GET['aids']);
	$attachlist = $attachlist['attachs']['unused'];
	$_G['group']['maxprice'] = isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]) ? $_G['group']['maxprice'] : 0;

	include template('common/header_ajax');
	include template('forum/ajax_attachlist');
	include template('common/footer_ajax');
	dexit();
} elseif($_GET['action'] == 'imagelist') {

	require_once libfile('function/post');
	$attachlist = getattach($_GET['pid'], intval($_GET['posttime']), $_GET['aids']);
	$imagelist = $attachlist['imgattachs']['unused'];

	include template('common/header_ajax');
	include template('forum/ajax_imagelist');
	include template('common/footer_ajax');
	dexit();

} elseif($_GET['action'] == 'get_rushreply_membernum') {
	$tid = intval($_GET['tid']);
	if($tid) {
		$membernum = C::t('forum_post')->count_author_by_tid($tid);
		showmessage('thread_reshreply_membernum', '', array('membernum' => intval($membernum - 1)), array('alert' => 'info'));
	}
	dexit();
} elseif($_GET['action'] == 'deleteattach') {

	$count = 0;
	if(isset($_GET['aids']) && isset($_GET['formhash']) && formhash() == $_GET['formhash']) {
		foreach($_GET['aids'] as $aid) {
			$attach = C::t('forum_attachment_n')->fetch('aid:'.$aid, $aid);
			if($attach && ($attach['pid'] && $attach['pid'] == $_GET['pid'] && $_G['uid'] == $attach['uid'])) {
				updatecreditbyaction('postattach', $attach['uid'], array(), '', -1, 1, $_G['fid']);
			}
			if($attach && ($attach['pid'] && $attach['pid'] == $_GET['pid'] && $_G['uid'] == $attach['uid'] || $_G['forum']['ismoderator'] || !$attach['pid'] && $_G['uid'] == $attach['uid'])) {
				C::t('forum_attachment_n')->delete('aid:'.$aid, $aid);
				C::t('forum_attachment')->delete($aid);
				dunlink($attach);
				$count++;
			}
		}
	}
	include template('common/header_ajax');
	echo $count;
	include template('common/footer_ajax');
	dexit();

} elseif($_GET['action'] == 'secondgroup') {

	require_once libfile('function/group');
	$groupselect = get_groupselect($_GET['fupid'], $_GET['groupid']);
	include template('common/header_ajax');
	include template('forum/ajax_secondgroup');
	include template('common/footer_ajax');
	dexit();

} elseif($_GET['action'] == 'displaysearch_adv') {
	$display = $_GET['display'] == 1 ? 1 : '';
	dsetcookie('displaysearch_adv', $display);
} elseif($_GET['action'] == 'checkgroupname') {
	$groupname = trim($_GET['groupname']);
	if(empty($groupname)) {
		showmessage('group_name_empty', '', array(), array('msgtype' => 3));
	}
	$tmpname = cutstr($groupname, 20, '');
	if($tmpname != $groupname) {
		showmessage('group_name_oversize', '', array(), array('msgtype' => 3));
	}
	if(C::t('forum_forum')->fetch_fid_by_name($groupname)) {
		showmessage('group_name_exist', '', array(), array('msgtype' => 3));
	}
	showmessage('', '', array(), array('msgtype' => 3));
	include template('common/header_ajax');
	include template('common/footer_ajax');
	dexit();
} elseif($_GET['action'] == 'getthreadtypes') {
	include template('common/header_ajax');
	if(empty($_GET['selectname'])) $_GET['selectname'] = 'threadtypeid';
	echo '<select name="'.$_GET['selectname'].'">';
	if(!empty($_G['forum']['threadtypes']['types'])) {
		if(!$_G['forum']['threadtypes']['required']) {
			echo '<option value="0"></option>';
		}
		foreach($_G['forum']['threadtypes']['types'] as $typeid => $typename) {
			if($_G['forum']['threadtypes']['moderators'][$typeid] && $_G['forum'] && !$_G['forum']['ismoderator']) {
				continue;
			}
			echo '<option value="'.$typeid.'">'.$typename.'</option>';
		}
	} else {
		echo '<option value="0" /></option>';
	}
	echo '</select>';
	include template('common/footer_ajax');
} elseif($_GET['action'] == 'getimage') {
	$_GET['aid'] = intval($_GET['aid']);
	$image = C::t('forum_attachment_n')->fetch('aid:'.$_GET['aid'], $_GET['aid'], 1);
	include template('common/header_ajax');
	if($image['aid']) {
		echo '<img src="'.getforumimg($image['aid'], 1, 300, 300, 'fixnone').'" id="image_'.$image['aid'].'" onclick="insertAttachimgTag(\''.$image['aid'].'\')" width="'.($image['width'] < 110 ? $image['width'] : 110).'" cwidth="'.($image['width'] < 300 ? $image['width'] : 300).'" />';
	}
	include template('common/footer_ajax');
	dexit();
} elseif($_GET['action'] == 'setthreadcover') {
	$aid = intval($_GET['aid']);
	$imgurl = $_GET['imgurl'];
	require_once libfile('function/post');
	if($_G['forum'] && ($aid || $imgurl)) {
		if($imgurl) {
			$tid = intval($_GET['tid']);
			$pid = intval($_GET['pid']);
		} else {
			$threadimage = C::t('forum_attachment_n')->fetch('aid:'.$aid, $aid);
			$tid = $threadimage['tid'];
			$pid = $threadimage['pid'];
		}

		if($tid && $pid) {
			$thread =get_thread_by_tid($tid);
		} else {
			$thread = array();
		}
		if(empty($thread) || (!$_G['forum']['ismoderator'] && $_G['uid'] != $thread['authorid'])) {
			if($_GET['newthread']) {
				showmessage('set_cover_faild', '', array(), array('msgtype' => 3));
			} else {
				showmessage('set_cover_faild', '', array(), array('closetime' => 3));
			}
		}
		if(setthreadcover($pid, $tid, $aid, 0, $imgurl)) {
			if(empty($imgurl)) {
				C::t('forum_threadimage')->delete_by_tid($threadimage['tid']);
				C::t('forum_threadimage')->insert(array(
					'tid' => $threadimage['tid'],
					'attachment' => $threadimage['attachment'],
					'remote' => $threadimage['remote'],
				));
			}
			if($_GET['newthread']) {
				showmessage('set_cover_succeed', '', array(), array('msgtype' => 3));
			} else {
				showmessage('set_cover_succeed', '', array(), array('alert' => 'right', 'closetime' => 1));
			}
		}
	}
	if($_GET['newthread']) {
		showmessage('set_cover_faild', '', array(), array('msgtype' => 3));
	} else {
		showmessage('set_cover_faild', '', array(), array('closetime' => 3));
	}

} elseif($_GET['action'] == 'updateattachlimit') {

	$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
	$_G['group']['allowpostattach'] = $_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm'])));
	$_G['forum']['allowpostimage'] = isset($_G['forum']['allowpostimage']) ? $_G['forum']['allowpostimage'] : '';
	$_G['group']['allowpostimage'] = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostimage']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));

	$allowuploadnum = $allowuploadtoday = TRUE;
	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		if($_G['group']['maxattachnum']) {
			$allowuploadnum = $_G['group']['maxattachnum'] - getuserprofile('todayattachs');
			$allowuploadnum = $allowuploadnum < 0 ? 0 : $allowuploadnum;
			if(!$allowuploadnum) {
				$allowuploadtoday = false;
			}
		}
		if($_G['group']['maxsizeperday']) {
			$allowuploadsize = $_G['group']['maxsizeperday'] - getuserprofile('todayattachsize');
			$allowuploadsize = $allowuploadsize < 0 ? 0 : $allowuploadsize;
			if(!$allowuploadsize) {
				$allowuploadtoday = false;
			}
			$allowuploadsize = $allowuploadsize / 1048576 >= 1 ? round(($allowuploadsize / 1048576), 1).'MB' : round(($allowuploadsize / 1024)).'KB';
		}
	}
	include template('common/header_ajax');
	include template('forum/post_attachlimit');
	include template('common/footer_ajax');
	exit;

} elseif($_GET['action'] == 'forumchecknew' && !empty($_GET['fid']) && !empty($_GET['time'])) {
	$fid = intval($_GET['fid']);
	$time = intval($_GET['time']);

	if(!$_GET['uncheck']) {
		$foruminfo = C::t('forum_forum')->fetch($fid);
		$lastpost_str = $foruminfo['lastpost'];
		if($lastpost_str) {
			$lastpost = explode("\t", $lastpost_str);
			unset($lastpost_str);
		}
		include template('common/header_ajax');
		echo $lastpost['2'] > $time ? 1 : 0 ;
		include template('common/footer_ajax');
		exit;
	} else {
		$_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
		$query = C::t('forum_forumfield')->fetch($fid);
		$forum_field['threadtypes'] = dunserialize($query['threadtypes']);
		$forum_field['threadsorts'] = dunserialize($query['threadsorts']);

		if($forum_field['threadtypes']['types']) {
			safefilter($forum_field['threadtypes']['types']);
		}
		if($forum_field['threadtypes']['options']['name']) {
			safefilter($forum_field['threadtypes']['options']['name']);
		}
		if($forum_field['threadsorts']['types']) {
			safefilter($forum_field['threadsorts']['types']);
		}

		unset($query);
		$forum_field = daddslashes($forum_field);
		$todaytime = strtotime(dgmdate(TIMESTAMP, 'Ymd'));
		foreach(C::t('forum_thread')->fetch_all_by_fid_lastpost($fid, $time, TIMESTAMP) as $thread) {
			$thread['icontid'] = $thread['forumstick'] || !$thread['moved'] && $thread['isgroup'] != 1 ? $thread['tid'] : $thread['closed'];
			if(!$thread['forumstick'] && ($thread['isgroup'] == 1 || $thread['fid'] != $_G['fid'])) {
				$thread['icontid'] = $thread['closed'] > 1 ? $thread['closed'] : $thread['tid'];
			}
			list($thread['subject'], $thread['author'], $thread['lastposter']) = daddslashes(array($thread['subject'], $thread['author'], $thread['lastposter']));
			$thread['dateline'] = $thread['dateline'] > $todaytime ? "<span class=\"xi1\">".dgmdate($thread['dateline'], 'd')."</span>" : "<span>".dgmdate($thread['dateline'], 'd')."</span>";
			$thread['lastpost'] = dgmdate($thread['lastpost']);
			if($forum_field['threadtypes']['prefix']) {
				if($forum_field['threadtypes']['prefix'] == 1) {
					$thread['threadtype'] = $forum_field['threadtypes']['types'][$thread['typeid']] ? '<em>[<a href="forum.php?mod=forumdisplay&fid='.$fid.'&filter=typeid&typeid='.$thread['typeid'].'">'.$forum_field['threadtypes']['types'][$thread['typeid']].'</a>]</em> ' : '' ;
				} elseif($forum_field['threadtypes']['prefix'] == 2) {
					$thread['threadtype'] = $forum_field['threadtypes']['icons'][$thread['typeid']] ? '<em><a href="forum.php?mod=forumdisplay&fid='.$fid.'&filter=typeid&typeid='.$thread['typeid'].'"><img src="'.$forum_field['threadtypes']['icons'][$thread['typeid']].'"/></a></em> ' : '' ;
				}
			}
			if($forum_field['threadsorts']['prefix']) {
				$thread['threadsort'] = $forum_field['threadsorts']['types'][$thread['sortid']] ? '<em>[<a href="forum.php?mod=forumdisplay&fid='.$fid.'&filter=sortid&typeid='.$thread['sortid'].'">'.$forum_field['threadsorts']['types'][$thread['sortid']].'</a>]</em>' : '' ;
			}
			if($thread['highlight']) {
				$string = sprintf('%02d', $thread['highlight']);
				$stylestr = sprintf('%03b', $string[0]);

				$thread['highlight'] = ' style="';
				$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
				$thread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
				$thread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
				$thread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]].';' : '';
				if($thread['bgcolor']) {
					$thread['highlight'] .= "background-color: $thread[bgcolor];";
				}
				$thread['highlight'] .= '"';
			} else {
				$thread['highlight'] = '';
			}
			$target = $thread['isgroup'] == 1 || $thread['forumstick'] ? ' target="_blank"' : ' onclick="atarget(this)"';
			if(in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
				$thread['threadurl'] = '<a href="'.rewriteoutput('forum_viewthread', 1, '', $thread['tid'], 1, '', '').'"'.$thread['highlight'].$target.'class="s xst">'.$thread['subject'].'</a>';
			} else {
				$thread['threadurl'] = '<a href="forum.php?mod=viewthread&amp;tid='.$thread['tid'].'"'.$thread['highlight'].$target.'class="s xst">'.$thread['subject'].'</a>';
			}
			if(in_array($thread['displayorder'], array(1, 2, 3, 4))) {
				$thread['id'] = 'stickthread_'.$thread['tid'];
			} else {
				$thread['id'] = 'normalthread_'.$thread['tid'];
			}
			$thread['threadurl'] = $thread['threadtype'].$thread['threadsort'].$thread['threadurl'];
			if(in_array('home_space', $_G['setting']['rewritestatus'])) {
				$thread['authorurl'] = '<a href="'.rewriteoutput('home_space', 1, '', $thread['authorid'], '', '').'">'.$thread['author'].'</a>';
				$thread['lastposterurl'] = '<a href="'.rewriteoutput('home_space', 1, '', '', rawurlencode($thread['lastposter']), '').'">'.$thread['lastposter'].'</a>';
			} else {
				$thread['authorurl'] = '<a href="home.php?mod=space&uid='.$thread['authorid'].'">'.$thread['author'].'</a>';
				$thread['lastposterurl'] = '<a href="home.php?mod=space&username='.rawurlencode($thread['lastposter']).'">'.$thread['lastposter'].'</a>';
			}
			$threadlist[] = $thread;
		}
		if($threadlist) {
			krsort($threadlist);
		}
		include template('forum/ajax_threadlist');

	}
} elseif($_GET['action'] == 'downremoteimg') {
	if(!$_G['group']['allowdownremoteimg']) {
		dexit();
	}
	$_GET['message'] = str_replace(array("\r", "\n"), array($_GET['wysiwyg'] ? '<br />' : '', "\\n"), $_GET['message']);
	preg_match_all("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[img=\d{1,4}[x|\,]\d{1,4}\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", $_GET['message'], $image1, PREG_SET_ORDER);
	preg_match_all("/\<img.+\bsrc\b\s*=('|\"|)(.*)('|\"|)([\s].*)?\>/ismU", $_GET['message'], $image2, PREG_SET_ORDER);
	$temp = $aids = $existentimg = array();
	if(is_array($image1) && !empty($image1)) {
		foreach($image1 as $value) {
			$temp[] = array(
				'0' => $value[0],
				'1' => trim(!empty($value[1]) ? $value[1] : $value[2])
			);
		}
	}
	if(is_array($image2) && !empty($image2)) {
		foreach($image2 as $value) {
			$temp[] = array(
				'0' => $value[0],
				'1' => trim($value[2])
			);
		}
	}
	require_once libfile('class/image');
	if(is_array($temp) && !empty($temp)) {
		$upload = new discuz_upload();
		$attachaids = array();

		foreach($temp as $value) {
			$imageurl = $value[1];
			$hash = md5($imageurl);
			if(strlen($imageurl)) {
				$imagereplace['oldimageurl'][] = $value[0];
				if(!isset($existentimg[$hash])) {
					$existentimg[$hash] = $imageurl;
					$attach['ext'] = $upload->fileext($imageurl);
					if(!$upload->is_image_ext($attach['ext'])) {
						continue;
					}
					$content = '';
					if(preg_match('/^(http(s?):\/\/|\.)/i', $imageurl)) {
						$content = dfsockopen($imageurl);
					} elseif(preg_match('/^('.preg_quote(getglobal('setting/attachurl'), '/').')/i', $imageurl)) {
						$imagereplace['newimageurl'][] = $value[0];
					}
					if(empty($content)) continue;
					$patharr = explode('/', $imageurl);
					$attach['name'] =  trim($patharr[count($patharr)-1]);
					$attach['thumb'] = '';

					$attach['isimage'] = $upload -> is_image_ext($attach['ext']);
					$attach['extension'] = $upload -> get_target_extension($attach['ext']);
					$attach['attachdir'] = $upload -> get_target_dir('forum');
					$attach['attachment'] = $attach['attachdir'] . $upload->get_target_filename('forum').'.'.$attach['extension'];
					$attach['target'] = getglobal('setting/attachdir').'./forum/'.$attach['attachment'];

					if(!@$fp = fopen($attach['target'], 'wb')) {
						continue;
					} else {
						flock($fp, 2);
						fwrite($fp, $content);
						fclose($fp);
					}
					if(!$upload->get_image_info($attach['target'])) {
						@unlink($attach['target']);
						continue;
					}
					$attach['size'] = filesize($attach['target']);
					$upload->attach = $attach;
					$thumb = $width = 0;
					if($upload->attach['isimage']) {
						if($_G['setting']['thumbsource'] && $_G['setting']['sourcewidth'] && $_G['setting']['sourceheight']) {
							$image = new image();
							$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['sourcewidth'], $_G['setting']['sourceheight'], 1, 1) ? 1 : 0;
							$width = $image->imginfo['width'];
							$upload->attach['size'] = $image->imginfo['size'];
						}
						if($_G['setting']['thumbstatus']) {
							$image = new image();
							$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], $_G['setting']['thumbstatus'], 0) ? 1 : 0;
							$width = $image->imginfo['width'];
						}
						if($_G['setting']['thumbsource'] || !$_G['setting']['thumbstatus']) {
							list($width) = @getimagesize($upload->attach['target']);
						}
						if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {
							$image = new image();
							$image->Watermark($attach['target'], '', 'forum');
							$upload->attach['size'] = $image->imginfo['size'];
						}
					}
					$aids[] = $aid = getattachnewaid();
					$setarr = array(
						'aid' => $aid,
						'dateline' => $_G['timestamp'],
						'filename' => strip_tags(str_replace('"', '', $upload->attach['name'])),
						'filesize' => $upload->attach['size'],
						'attachment' => $upload->attach['attachment'],
						'isimage' => $upload->attach['isimage'],
						'uid' => $_G['uid'],
						'thumb' => $thumb,
						'remote' => '0',
						'width' => $width
					);
					C::t("forum_attachment_unused")->insert($setarr);
					$attachaids[$hash] = $imagereplace['newimageurl'][] = '[attachimg]'.$aid.'[/attachimg]';

				} else {
					$imagereplace['newimageurl'][] = $attachaids[$hash];
				}
			}
		}
		if(!empty($aids)) {
			require_once libfile('function/post');
		}
		$_GET['message'] = str_replace($imagereplace['oldimageurl'], $imagereplace['newimageurl'], $_GET['message']);
	}
	$_GET['message'] = addcslashes($_GET['message'], '/"\'');
	print <<<EOF
		<script type="text/javascript">
			parent.ATTACHORIMAGE = 1;
			parent.updateDownImageList('$_GET[message]');
		</script>
EOF;
	dexit();
} elseif($_GET['action'] == 'exif') {
	$exif = C::t('forum_attachment_exif')->fetch($_GET['aid']);
	$s = $exif['exif'];
	if(!$s) {
		require_once libfile('function/attachment');
		$s = getattachexif($_GET['aid']);
		C::t('forum_attachment_exif')->insert($_GET['aid'], $s);
	}
	include template('common/header_ajax');
	echo $s;
	include template('common/footer_ajax');
	exit;
} elseif($_GET['action'] == 'getthreadclass') {
	$fid = intval($_GET['fid']);
	$threadclass = '';
	if($fid) {
		$option = array();
		$forumfield = C::t('forum_forumfield')->fetch($fid);
		if(!empty($forumfield['threadtypes'])) {
			foreach(C::t('forum_threadclass')->fetch_all_by_fid($fid) as $tc) {
				$option[] = '<option value="'.$tc['typeid'].'">'.$tc['name'].'</option>';
			}
			if(!empty($option)) {
				$threadclass .= '<option value="">'.lang('forum/template', 'modcp_select_threadclass').'</option>';
				$threadclass .= implode('', $option);
			}
		}
	}

	if(!empty($threadclass)) {
		$threadclass = '<select name="typeid" id="typeid" width="168" class="ps">'.$threadclass.'</select>';
	}
	include template('common/header_ajax');
	echo $threadclass;
	include template('common/footer_ajax');
	exit;

} elseif($_GET['action'] == 'forumjump') {
	require_once libfile('function/forumlist');
	$favforums = C::t('home_favorite')->fetch_all_by_uid_idtype($_G['uid'], 'fid');
	$visitedforums = array();
	if($_G['cookie']['visitedfid']) {
		loadcache('forums');
		foreach(explode('D', $_G['cookie']['visitedfid']) as $fid) {
			$fid = intval($fid);
			$visitedforums[$fid] = $_G['cache']['forums'][$fid]['name'];
		}
	}
	$forumlist = forumselect(FALSE, 1);
	include template('forum/ajax_forumlist');
} elseif($_GET['action'] == 'quickreply') {
	$tid = intval($_GET['tid']);
	$fid = intval($_GET['fid']);
	if($tid) {
		$thread = C::t('forum_thread')->fetch($tid);
		if($thread && !getstatus($thread['status'], 2)) {
			$list = C::t('forum_post')->fetch_all_by_tid('tid:'.$tid, $tid, true, 'DESC', 0, 10, null, 0);
			loadcache('smilies');
			foreach($list as $pid => $post) {
				if($post['first']) {
					unset($list[$pid]);
				} else {
					$post['message'] = preg_replace($_G['cache']['smilies']['searcharray'], '', $post['message']);
					$post['message'] = preg_replace("/\{\:soso_((e\d+)|(_\d+_\d))\:\}/", '', $post['message']);
					$list[$pid]['message'] = cutstr(preg_replace("/\[.+?\]/is", '', dhtmlspecialchars($post['message'])), 300) ;
				}
			}
			krsort($list);
		}
	}
	list($seccodecheck, $secqaacheck) = seccheck('post', 'reply');
	include template('forum/ajax_quickreply');
} elseif($_GET['action'] == 'getpost') {
	$tid = intval($_GET['tid']);
	$fid = intval($_GET['fid']);
	$pid = intval($_GET['pid']);
	$thread = C::t('forum_thread')->fetch($tid);
	$post = C::t('forum_post')->fetch($thread['posttableid'], $pid);
	if($_G['uid'] != $post['authorid']) {
		showmessage('quickclear_noperm');
	}
	include template('forum/ajax_followpost');
} elseif($_GET['action'] == 'quickclear') {
	$uid = intval($_GET['uid']);
	if($_G['adminid'] != 1) {
		showmessage('quickclear_noperm');
	}
	include_once libfile('function/misc');
	include_once libfile('function/member');

	if(!submitcheck('qclearsubmit')) {
		$crimenum_avatar = crime('getcount', $uid, 'crime_avatar');
		$crimenum_sightml = crime('getcount', $uid, 'crime_sightml');
		$crimenum_customstatus = crime('getcount', $uid, 'crime_customstatus');
		$crimeauthor = getuserbyuid($uid);
		$crimeauthor = $crimeauthor['username'];

		include template('forum/ajax');
	} else {
		if(empty($_GET['operations'])) {
			showmessage('quickclear_need_operation');
		}
		$reason = checkreasonpm();
		$allowop = array('avatar', 'sightml', 'customstatus');
		$cleartype = array();
		if(in_array('avatar', $_GET['operations'])) {
			C::t('common_member')->update($uid, array('avatarstatus'=>0));
			loaducenter();
			uc_user_deleteavatar($uid);
			$cleartype[] = lang('forum/misc', 'avatar');
			crime('recordaction', $uid, 'crime_avatar', lang('forum/misc', 'crime_reason', array('reason' => $reason)));
		}
		if(in_array('sightml', $_GET['operations'])) {
			C::t('common_member_field_forum')->update($uid, array('sightml' => ''), 'UNBUFFERED');
			$cleartype[] = lang('forum/misc', 'signature');
			crime('recordaction', $uid, 'crime_sightml', lang('forum/misc', 'crime_reason', array('reason' => $reason)));
		}
		if(in_array('customstatus', $_GET['operations'])) {
			C::t('common_member_field_forum')->update($uid, array('customstatus' => ''), 'UNBUFFERED');
			$cleartype[] = lang('forum/misc', 'custom_title');
			crime('recordaction', $uid, 'crime_customstatus', lang('forum/misc', 'crime_reason', array('reason' => $reason)));
		}
		if(($_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3) || !empty($_GET['sendreasonpm'])) {
			sendreasonpm(array('authorid' => $uid), 'reason_quickclear', array(
				'cleartype' => implode(',', $cleartype),
				'reason' => $reason,
				'from_id' => 0,
				'from_idtype' => 'quickclear'
			));
		}
		showmessage('quickclear_success', $_POST['redirect'], array(), array('showdialog'=>1, 'closetime' => true, 'msgtype' => 2, 'locationtime' => 1));
	}
} elseif($_GET['action'] == 'getpostfeed') {
	$tid = intval($_GET['tid']);
	$pid = intval($_GET['pid']);
	$flag = intval($_GET['flag']);
	$feed = $thread = array();
	if($tid) {
		$thread = C::t('forum_thread')->fetch($tid);
		if(empty($_G['setting']['followforumid']) || $thread['fid'] != $_G['setting']['followforumid']) {
			$flag = 0;
		}
		if($flag) {
			$post = C::t('forum_post')->fetch($thread['posttableid'], $pid);
			if($thread['tid'] != $post['tid']) {
				showmessage('quickclear_noperm');
			}
			require_once libfile('function/discuzcode');
			require_once libfile('function/followcode');
			$post['message'] = followcode($post['message'], $tid, $pid);
		} else {
			if(!isset($_G['cache']['forums'])) {
				loadcache('forums');
			}
			$feedid = intval($_GET['feedid']);
			$feed = C::t('forum_threadpreview')->fetch($tid);
			if($feedid) {
				$feed = array_merge($feed, C::t('home_follow_feed')->fetch_by_feedid($feedid));
			}
			$post['message'] = $feed['content'];
		}
	}
	include template('forum/ajax_followpost');

} elseif($_GET['action'] == 'setnav') {
	if($_G['adminid'] != 1) {
		showmessage('quickclear_noperm');
	}
	$allowfuntype = array('portal', 'group', 'follow', 'collection', 'guide', 'feed', 'blog', 'doing', 'album', 'share', 'wall', 'homepage', 'ranklist');
	$type = in_array($_GET['type'], $allowfuntype) ? trim($_GET['type']) : '';
	$do = in_array($_GET['do'], array('open', 'close')) ? $_GET['do'] : 'close';
	if(!submitcheck('funcsubmit')) {
		$navtitle = lang('spacecp', $do == 'open' ? 'select_the_navigation_position' : 'close_module', array('type' => lang('spacecp', $type)));
		$closeprompt = lang('spacecp', 'close_module', array('type' => lang('spacecp', $type)));
		include template('forum/ajax');
	} else {
		if(!empty($type)) {
			$funkey = $type.'status';
			$funstatus = $do == 'open' ? 1 : 0;
			if($type != 'homepage') {
				$identifier = array('portal' => 1, 'group' => 3, 'feed' => 4, 'ranklist' => 8, 'follow' => 9, 'guide' => 10, 'collection' => 11, 'blog' => 12, 'album' => 13, 'share' => 14, 'doing' => 15);
				$navdata = array('available' => -1);
				$navtype = $do == 'open' ? array() : array(0, 3);
				if(in_array($type, array('blog', 'album', 'share', 'doing', 'follow'))) {
					$navtype[] = 2;
				}
				if($do == 'open') {
					if($_GET['location']['header']) {
						$navtype[] = 0;
						$navdata['available'] = 1;
					}
					if($_GET['location']['quick']) {
						$navtype[] = 3;
						$navdata['available'] = 1;
					}
					$navdata['available'] = $navdata['available'] == 1 ? 1 : 0;
					if(empty($_GET['location']['header']) || empty($_GET['location']['quick'])) {
						C::t('common_nav')->update_by_navtype_type_identifier(array(0, 2, 3), 0, array("$type", "$identifier[$type]"), array('available' => 0));
					}
				}
				if($navtype) {
					C::t('common_nav')->update_by_navtype_type_identifier($navtype, 0, array("$type", "$identifier[$type]"), $navdata);
					if(in_array($type, array('blog', 'album', 'share', 'doing', 'follow')) && !$navdata['available']) {
						C::t('common_nav')->update_by_navtype_type_identifier(array(2), 0, array("$type"), array('available' => 1));
					}
				}
			}
			C::t('common_setting')->update($funkey, $funstatus);

			$setting[$funkey] = $funstatus;
			if(!function_exists('updatecache')) {
				include libfile('function/cache');
			}
			updatecache('setting');
		}
		showmessage('do_success', dreferer(), array(), array('header'=>true));
	}
	exit;
} elseif($_GET['action'] == 'checkpostrule') {
	require_once libfile('function/post');
	include template('common/header_ajax');
	$_POST = array('action' => $_GET['ac']);
	list($seccodecheck, $secqaacheck) = seccheck('post', $_GET['ac']);
	if($seccodecheck || $secqaacheck) {
		include template('forum/seccheck_post');
	}
	include template('common/footer_ajax');
	exit;
}

showmessage('succeed', '', array(), array('handle' => false));

?>