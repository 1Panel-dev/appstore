<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_members.php 35200 2015-02-04 03:50:59Z hypowang $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@set_time_limit(600);
if($operation != 'export') {
	cpheader();
}

require_once libfile('function/delete');

$_G['setting']['memberperpage'] = 20;
$page = max(1, $_G['page']);
$start_limit = ($page - 1) * $_G['setting']['memberperpage'];
$search_condition = array_merge($_GET, $_POST);

if(!is_array($search_condition['groupid']) && $search_condition['groupid']) {
	$search_condition['groupid'][0] = $search_condition['groupid'];
}
foreach($search_condition as $k => $v) {
	if(in_array($k, array('action', 'operation', 'formhash', 'confirmed', 'submit', 'page', 'deletestart', 'allnum', 'includeuc','includepost','current','pertask','lastprocess','deleteitem')) || $v === '') {
		unset($search_condition[$k]);
	}
}
$search_condition = searchcondition($search_condition);
$tmpsearch_condition = $search_condition;
unset($tmpsearch_condition['tablename']);
$member = array();
$tableext = '';
if(in_array($operation, array('ban', 'edit', 'group', 'credit', 'medal', 'access'), true)) {
	if(empty($_GET['uid']) && empty($_GET['username'])) {
		cpmsg('members_nonexistence', 'action=members&operation='.$operation.(!empty($_GET['highlight']) ? "&highlight={$_GET['highlight']}" : ''), 'form', array(), '<input type="text" name="username" value="" class="txt" />');
	}
	$member = !empty($_GET['uid']) ? C::t('common_member')->fetch($_GET['uid'], false, 1) : C::t('common_member')->fetch_by_username($_GET['username'], 1);
	if(!$member) {
		cpmsg('members_edit_nonexistence', '', 'error');
	}
	$tableext = isset($member['_inarchive']) ? '_archive' : '';
}

if($operation == 'search') {

	if(!submitcheck('submit', 1)) {

		shownav('user', 'nav_members');
		showsubmenu('nav_members', array(
			array('search', 'members&operation=search', 1),
			array('clean', 'members&operation=clean', 0),
			array('nav_repeat', 'members&operation=repeat', 0),
		));
		showtips('members_admin_tips');
		if(!empty($_GET['vid']) && ($_GET['vid'] > 0 && $_GET['vid'] < 8)) {
			$_GET['verify'] = array('verify'.intval($_GET['vid']));
		}
		showsearchform('search');
		if($_GET['more']) {
			print <<<EOF
		<script type="text/javascript">
			$('btn_more').click();
		</script>

EOF;
		}
	} else {

		$membernum = countmembers($search_condition, $urladd);

		$members = '';
		if($membernum > 0) {
			$multipage = multi($membernum, $_G['setting']['memberperpage'], $page, ADMINSCRIPT."?action=members&operation=search&submit=yes".$urladd);

			$usergroups = array();
			foreach(C::t('common_usergroup')->range() as $group) {
				switch($group['type']) {
					case 'system': $group['grouptitle'] = '<b>'.$group['grouptitle'].'</b>'; break;
					case 'special': $group['grouptitle'] = '<i>'.$group['grouptitle'].'</i>'; break;
				}
				$usergroups[$group['groupid']] = $group;
			}

			$uids = searchmembers($search_condition, $_G['setting']['memberperpage'], $start_limit);
			if($uids) {
				$allmember = C::t('common_member')->fetch_all($uids);
				$allcount = C::t('common_member_count')->fetch_all($uids);
				foreach($allmember as $uid=>$member) {
					$member = array_merge($member, (array)$allcount[$uid]);
					$memberextcredits = array();
					if($_G['setting']['extcredits']) {
						foreach($_G['setting']['extcredits'] as $id => $credit) {
							$memberextcredits[] = $_G['setting']['extcredits'][$id]['title'].': '.$member['extcredits'.$id].' ';
						}
					}
					$lockshow = $member['status'] == '-1' ? '<em class="lightnum">['.cplang('lock').']</em>' : '';
					$freezeshow = $member['freeze'] ? '<em class="lightnum">['.cplang('freeze').']</em>' : '';
					$members .= showtablerow('', array('class="td25"', '', 'title="'.implode("\n", $memberextcredits).'"'), array(
						"<input type=\"checkbox\" name=\"uidarray[]\" value=\"$member[uid]\"".($member['adminid'] == 1 ? 'disabled' : '')." class=\"checkbox\">",
						($_G['setting']['connect']['allow'] && $member['conisbind'] ? '<img class="vmiddle" src="static/image/common/connect_qq.gif" /> ' : '')."<a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a>",
						$member['credits'],
						$member['posts'],
						$usergroups[$member['adminid']]['grouptitle'],
						$usergroups[$member['groupid']]['grouptitle'].$lockshow.$freezeshow,
						"<a href=\"".ADMINSCRIPT."?action=members&operation=group&uid=$member[uid]\" class=\"act\">$lang[usergroup]</a><a href=\"".ADMINSCRIPT."?action=members&operation=access&uid=$member[uid]\" class=\"act\">$lang[members_access]</a>".
						($_G['setting']['extcredits'] ? "<a href=\"".ADMINSCRIPT."?action=members&operation=credit&uid=$member[uid]\" class=\"act\">$lang[credits]</a>" : "<span disabled>$lang[edit]</span>").
						"<a href=\"".ADMINSCRIPT."?action=members&operation=medal&uid=$member[uid]\" class=\"act\">$lang[medals]</a>".
						"<a href=\"".ADMINSCRIPT."?action=members&operation=repeat&uid=$member[uid]\" class=\"act\">$lang[members_repeat]</a>".
						"<a href=\"".ADMINSCRIPT."?action=members&operation=edit&uid=$member[uid]\" class=\"act\">$lang[detail]</a>".
						"<a href=\"".ADMINSCRIPT."?action=members&operation=ban&uid=$member[uid]\" class=\"act\">$lang[members_ban]</a>"
					), TRUE);
				}
			}
		}

		shownav('user', 'nav_members');
		showsubmenu('nav_members');
		showtips('members_export_tips');
		foreach($search_condition as $k => $v) {
			if($k == 'username') {
				$v = explode(',', $v);
				$tmpv = array();
				foreach($v as $subvalue) {
					$tmpv[] = rawurlencode($subvalue);
				}
				$v = implode(',', $tmpv);
			}
			if(is_array($v)) {
				foreach($v as $value ) {
					$condition_str .= '&'.$k.'[]='.$value;
				}
			} else {
				$condition_str .= '&'.$k.'='.$v;
			}
		}
		showformheader("members&operation=clean".$condition_str);
		showtableheader(cplang('members_search_result', array('membernum' => $membernum)).'<a href="'.ADMINSCRIPT.'?action=members&operation=search" class="act lightlink normal">'.cplang('research').'</a>&nbsp;&nbsp;&nbsp;<a href='.ADMINSCRIPT.'?action=members&operation=export'.$condition_str.'>'.$lang['members_search_export'].'</a>');

		if($membernum) {
			showsubtitle(array('', 'username', 'credits', 'posts', 'admingroup', 'usergroup', ''));
			echo $members;
			$condition_str = str_replace('&tablename=master', '', $condition_str);
			showsubmit('deletesubmit', cplang('delete'), ($tmpsearch_condition ? '<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'uidarray\');if(this.checked){$(\'deleteallinput\').style.display=\'\';}else{$(\'deleteall\').checked = false;$(\'deleteallinput\').style.display=\'none\';}" class="checkbox">'.cplang('select_all') : ''), ' &nbsp;&nbsp;&nbsp;<span id="deleteallinput" style="display:none"><input id="deleteall" type="checkbox" name="deleteall" class="checkbox">'.cplang('members_search_deleteall', array('membernum' => $membernum)).'</span>', $multipage);
		}
		showtablefooter();
		showformfooter();

	}

} elseif($operation == 'export') {
	$uids = searchmembers($search_condition, 10000);
	$detail = '';
	if($uids && is_array($uids)) {

		$allprofile = C::t('common_member_profile')->fetch_all($uids);
		$allusername = C::t('common_member')->fetch_all_username_by_uid($uids);
		foreach($allprofile as $uid=>$profile) {
			unset($profile['uid']);
			$profile = array_merge(array('uid'=>$uid, 'username'=>$allusername[$uid]),$profile);
			foreach($profile as $key => $value) {
				$value = preg_replace('/\s+/', ' ', $value);
				if($key == 'gender') $value = lang('space', 'gender_'.$value);
				$detail .= strlen($value) > 11 && is_numeric($value) ? '['.$value.'],' : $value.',';
			}
			$detail = $detail."\n";
		}
	}
	$title = array('realname' => '', 'gender' => '', 'birthyear' => '', 'birthmonth' => '', 'birthday' => '', 'constellation' => '',
		'zodiac' => '', 'telephone' => '', 'mobile' => '', 'idcardtype' => '', 'idcard' => '', 'address' => '', 'zipcode' => '','nationality' => '',
		'birthprovince' => '', 'birthcity' => '', 'birthdist' => '', 'birthcommunity' => '', 'resideprovince' => '', 'residecity' => '', 'residedist' => '',
		'residecommunity' => '', 'residesuite' => '', 'graduateschool' => '', 'education' => '', 'company' => '', 'occupation' => '',
		'position' => '', 'revenue' => '', 'affectivestatus' => '', 'lookingfor' => '', 'bloodtype' => '', 'height' => '', 'weight' => '',
		'alipay' => '', 'icq' => '', 'qq' => '', 'yahoo' => '', 'msn' => '', 'taobao' => '', 'site' => '', 'bio' => '', 'interest' => '',
		'field1' => '', 'field2' => '', 'field3' => '', 'field4' => '', 'field5' => '', 'field6' => '', 'field7' => '', 'field8' => '');
	foreach(C::t('common_member_profile_setting')->range() as $value) {
		if(isset($title[$value['fieldid']])) {
			$title[$value['fieldid']] = $value['title'];
		}
	}
	foreach($title as $k => $v) {
		$subject .= ($v ? $v : $k).",";
	}
	$detail = "UID,".$lang['username'].",".$subject."\n".$detail;
	$filename = date('Ymd', TIMESTAMP).'.csv';

	ob_end_clean();
	header('Content-Encoding: none');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$filename);
	header('Pragma: no-cache');
	header('Expires: 0');
	if($_G['charset'] != 'gbk') {
		$detail = diconv($detail, $_G['charset'], 'GBK');
	}
	echo $detail;
	exit();

} elseif($operation == 'repeat') {

	if(empty($_GET['uid']) && empty($_GET['username']) && empty($_GET['ip'])) {

		shownav('user', 'nav_members');
		showsubmenu('nav_members', array(
			array('search', 'members&operation=search', 0),
			array('clean', 'members&operation=clean', 0),
			array('nav_repeat', 'members&operation=repeat', 1),
		));

		showformheader("members&operation=repeat");
		showtableheader();
		showsetting('members_search_repeatuser', 'username', '', 'text');
		showsetting('members_search_uid', 'uid', '', 'text');
		showsetting('members_search_repeatip', 'ip', $_GET['inputip'], 'text');
		showsubmit('submit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$ips = array();
		$urladd = '';
		if(!empty($_GET['username'])) {
			$uid = C::t('common_member')->fetch_uid_by_username($_GET['username']);
			$searchmember = $uid ? C::t('common_member_status')->fetch($uid) : '';
			$searchmember['username'] = $_GET['username'];
			$urladd .= '&username='.$_GET['username'];
		} elseif(!empty($_GET['uid'])) {
			$searchmember = C::t('common_member_status')->fetch($_GET['uid']);
			$themember = C::t('common_member')->fetch($_GET['uid']);
			$searchmember['username'] = $themember['username'];
			$urladd .= '&uid='.$_GET['uid'];
			unset($_GET['uid']);
		} elseif(!empty($_GET['ip'])) {
			$regip = $lastip = $_GET['ip'];
			$ips[] = $_GET['ip'];
			$search_condition['lastip'] = $_GET['ip'];
			$urladd .= '&ip='.$_GET['ip'];
		}

		if($searchmember) {
			$ips = array();
			foreach(array('regip', 'lastip') as $iptype) {
				if($searchmember[$iptype] != '' && $searchmember[$iptype] != 'hidden' && $searchmember[$iptype] != 'Manual Acting') {
					$ips[] = $searchmember[$iptype];
				}
			}
			$ips = !empty($ips) ? array_unique($ips) : array('unknown');
		}
		$searchmember['username'] .= ' (IP '.implode(',',dhtmlspecialchars($ips)).')';
		$membernum = !empty($ips) && $ips[0] != "unknown" ? C::t('common_member_status')->count_by_ip($ips) : C::t('common_member_status')->count();

		$members = '';
		if($membernum) {
			$usergroups = array();
			foreach(C::t('common_usergroup')->range() as $group) {
				switch($group['type']) {
					case 'system': $group['grouptitle'] = '<b>'.$group['grouptitle'].'</b>'; break;
					case 'special': $group['grouptitle'] = '<i>'.$group['grouptitle'].'</i>'; break;
				}
				$usergroups[$group['groupid']] = $group;
			}
			$_G['setting']['memberperpage'] = 100;
			$start_limit = ($page - 1) * $_G['setting']['memberperpage'];
			$multipage = multi($membernum, $_G['setting']['memberperpage'], $page, ADMINSCRIPT."?action=members&operation=repeat&submit=yes".$urladd);
			$allstatus = !empty($ips) && $ips[0] != "unknown" ? C::t('common_member_status')->fetch_all_by_ip($ips, $start_limit, $_G['setting']['memberperpage'])
					: C::t('common_member_status')->range($start_limit, $_G['setting']['memberperpage']);
			$allcount = C::t('common_member_count')->fetch_all(array_keys($allstatus));
			$allmember = C::t('common_member')->fetch_all(array_keys($allstatus));
			foreach($allstatus as $uid => $member) {
				$member = array_merge($member, (array)$allcount[$uid], (array)$allmember[$uid]);
				$memberextcredits = array();
				foreach($_G['setting']['extcredits'] as $id => $credit) {
					$memberextcredits[] = $_G['setting']['extcredits'][$id]['title'].': '.$member['extcredits'.$id];
				}
				$members .= showtablerow('', array('class="td25"', '', 'title="'.implode("\n", $memberextcredits).'"'), array(
					"<input type=\"checkbox\" name=\"uidarray[]\" value=\"$member[uid]\"".($member['adminid'] == 1 ? 'disabled' : '')." class=\"checkbox\">",
					"<a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a>",
					$member['credits'],
					$member['posts'],
					$usergroups[$member['adminid']]['grouptitle'],
					$usergroups[$member['groupid']]['grouptitle'],
					"<a href=\"".ADMINSCRIPT."?action=members&operation=group&uid=$member[uid]\" class=\"act\">$lang[usergroup]</a><a href=\"".ADMINSCRIPT."?action=members&operation=access&uid=$member[uid]\" class=\"act\">$lang[members_access]</a>".
					($_G['setting']['extcredits'] ? "<a href=\"".ADMINSCRIPT."?action=members&operation=credit&uid=$member[uid]\" class=\"act\">$lang[credits]</a>" : "<span disabled>$lang[edit]</span>").
					"<a href=\"".ADMINSCRIPT."?action=members&operation=medal&uid=$member[uid]\" class=\"act\">$lang[medals]</a>".
					"<a href=\"".ADMINSCRIPT."?action=members&operation=repeat&uid=$member[uid]\" class=\"act\">$lang[members_repeat]</a>".
					"<a href=\"".ADMINSCRIPT."?action=members&operation=edit&uid=$member[uid]\" class=\"act\">$lang[detail]</a>"
				), TRUE);
			}
		}

		shownav('user', 'nav_repeat');
		showsubmenu($lang['nav_repeat'].' - '.$searchmember['username']);
		showformheader("members&operation=clean");
		$searchadd = '';
		if(is_array($ips) && $ips[0] != "unknown") {
			foreach($ips as $ip) {
				$searchadd .= '<a href="'.ADMINSCRIPT.'?action=members&operation=repeat&inputip='.rawurlencode($ip).'" class="act lightlink normal">'.cplang('search').'IP '.dhtmlspecialchars($ip).'</a>';
			}
		}
		showtableheader(cplang('members_search_result', array('membernum' => $membernum)).'<a href="'.ADMINSCRIPT.'?action=members&operation=repeat" class="act lightlink normal">'.cplang('research').'</a>'.$searchadd);
		showsubtitle(array('', 'username', 'credits', 'posts', 'admingroup', 'usergroup', ''));
		echo $members;
		showtablerow('', array('class="td25"', 'class="lineheight" colspan="7"'), array('', cplang('members_admin_comment')));
		showsubmit('submit', 'submit', '<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'uidarray\')" class="checkbox">'.cplang('del'), '', $multipage);
		showtablefooter();
		showformfooter();

	}

} elseif($operation == 'clean') {

	if(!submitcheck('submit', 1) && !submitcheck('deletesubmit', 1)) {

		shownav('user', 'nav_members');
		showsubmenu('nav_members', array(
			array('search', 'members&operation=search', 0),
			array('clean', 'members&operation=clean', 1),
			array('nav_repeat', 'members&operation=repeat', 0),
		));

		showsearchform('clean');

	} else {

		if((!$tmpsearch_condition && empty($_GET['uidarray'])) || (submitcheck('deletesubmit', 1) && empty($_GET['uidarray']))) {
			cpmsg('members_no_find_deluser', '', 'error');
		}
		if(!empty($_GET['deleteall'])) {
			unset($search_condition['uidarray']);
			$_GET['uidarray'] = '';
		}
		$uids = 0;
		$extra = '';
		$delmemberlimit = 300;
		$deletestart = intval($_GET['deletestart']);

		if(!empty($_GET['uidarray'])) {
			$uids = array();
			$allmember = C::t('common_member')->fetch_all($_GET['uidarray']);

			$membernum = 0;
			foreach($allmember as $uid => $member) {
				if($member['adminid'] !== 1 && $member['groupid'] !== 1) {
					if($membernum < 2000 ) {
						$extra .= '<input type="hidden" name="uidarray[]" value="'.$member['uid'].'" />';
					}
					$uids[] = $member['uid'];
					$membernum ++;
				}
			}
		} elseif($tmpsearch_condition) {
			$membernum = countmembers($search_condition, $urladd);
			$uids = searchmembers($search_condition, $delmemberlimit, 0);
		}
		$allnum = intval($_GET['allnum']);


		if((empty($membernum) || empty($uids))) {
			if($deletestart) {
				cpmsg('members_delete_succeed', '', 'succeed', array('numdeleted' => $allnum));
			}
			cpmsg('members_no_find_deluser', '', 'error');
		}
		if(!submitcheck('confirmed')) {

			cpmsg('members_delete_confirm', "action=members&operation=clean&submit=yes&confirmed=yes".$urladd, 'form', array('membernum' => $membernum), $extra.'<br /><label><input type="checkbox" name="includepost" value="1" class="checkbox" />'.$lang['members_delete_all'].'</label>'.($isfounder ? '&nbsp;<label><input type="checkbox" name="includeuc" value="1" class="checkbox" />'.$lang['members_delete_ucdata'].'</label>' : ''), '');

		} else {

			if(!submitcheck('includepost')) {

				require_once libfile('function/delete');
				$numdeleted = deletemember($uids, 0);

				if($isfounder && !empty($_GET['includeuc'])) {
					loaducenter();
					uc_user_delete($uids);
					$_GET['includeuc'] = 1;
				} else {
					$_GET['includeuc'] = 0;
				}
				if($_GET['uidarray']) {
					cpmsg('members_delete_succeed', '', 'succeed', array('numdeleted' => $numdeleted));
				} else {
					$allnum += $membernum < $delmemberlimit ? $membernum : $delmemberlimit;
					$nextlink = "action=members&operation=clean&confirmed=yes&submit=yes".(!empty($_GET['includeuc']) ? '&includeuc=yes' : '')."&allnum=$allnum&deletestart=".($deletestart+$delmemberlimit).$urladd;
					cpmsg(cplang('members_delete_user_processing_next', array('deletestart' => $deletestart, 'nextdeletestart' => $deletestart+$delmemberlimit)), $nextlink, 'loadingform', array());
				}

			} else {



				$pertask = 1000;
				$current = $_GET['current'] ? intval($_GET['current']) : 0;
				$deleteitem = $_GET['deleteitem'] ? trim($_GET['deleteitem']) : 'post';
				$nextdeleteitem = $deleteitem;

				$next = $current + $pertask;

				if($deleteitem == 'post') {
					$threads = $fids = $threadsarray = array();
					foreach(C::t('forum_thread')->fetch_all_by_authorid($uids, $pertask) as $thread) {
						$threads[$thread['fid']][] = $thread['tid'];
					}

					if($threads) {
						require_once libfile('function/post');
						foreach($threads as $fid => $tids) {
							deletethread($tids);
						}
						if($_G['setting']['globalstick']) {
							require_once libfile('function/cache');
							updatecache('globalstick');
						}
					} else {
						$next = 0;
						$nextdeleteitem = 'blog';
					}
				}

				if($deleteitem == 'blog') {
					$blogs = array();
					$query = C::t('home_blog')->fetch_blogid_by_uid($uids, 0, $pertask);
					foreach($query as $blog) {
						$blogs[] = $blog['blogid'];
					}

					if($blogs) {
						deleteblogs($blogs);
					} else {
						$next = 0;
						$nextdeleteitem = 'pic';
					}
				}

				if($deleteitem == 'pic') {
					$pics = array();
					$query = C::t('home_pic')->fetch_all_by_uid($uids, 0, $pertask);
					foreach($query as $pic) {
						$pics[] = $pic['picid'];
					}

					if($pics) {
						deletepics($pics);
					} else {
						$next = 0;
						$nextdeleteitem = 'doing';
					}
				}

				if($deleteitem == 'doing') {
					$doings = array();
					$query = C::t('home_doing')->fetch_all_by_uid_doid($uids, '', '', 0, $pertask);
					foreach ($query as $doing) {
						$doings[] = $doing['doid'];
					}

					if($doings) {
						deletedoings($doings);
					} else {
						$next = 0;
						$nextdeleteitem = 'share';
					}
				}

				if($deleteitem == 'share') {
					$shares = array();
					foreach(C::t('home_share')->fetch_all_by_uid($uids, $pertask) as $share) {
						$shares[] = $share['sid'];
					}

					if($shares) {
						deleteshares($shares);
					} else {
						$next = 0;
						$nextdeleteitem = 'feed';
					}
				}

				if($deleteitem == 'feed') {
					C::t('home_follow_feed')->delete_by_uid($uids);
					$nextdeleteitem = 'comment';
				}

				if($deleteitem == 'comment') {
					$comments = array();
					$query = C::t('home_comment')->fetch_all_by_uid($uids, 0, $pertask);
					foreach($query as $comment) {
						$comments[] = $comment['cid'];
					}

					if($comments) {
						deletecomments($comments);
					} else {
						$next = 0;
						$nextdeleteitem = 'allitem';
					}
				}

				if($deleteitem == 'allitem') {
					require_once libfile('function/delete');
					$numdeleted = deletemember($uids);

					if($isfounder && !empty($_GET['includeuc'])) {
						loaducenter();
						uc_user_delete($uids);
					}
					if(!empty($_GET['uidarray'])) {
						cpmsg('members_delete_succeed', '', 'succeed', array('numdeleted' => $numdeleted));
					} else {
						$allnum += $membernum < $delmemberlimit ? $membernum : $delmemberlimit;
						$nextlink = "action=members&operation=clean&confirmed=yes&submit=yes&includepost=yes".(!empty($_GET['includeuc']) ? '&includeuc=yes' : '')."&allnum=$allnum&deletestart=".($deletestart+$delmemberlimit).$urladd;
						cpmsg(cplang('members_delete_user_processing_next', array('deletestart' => $deletestart, 'nextdeletestart' => $deletestart+$delmemberlimit)), $nextlink, 'loadingform', array());
					}
				}
				$nextlink = "action=members&operation=clean&confirmed=yes&submit=yes&includepost=yes".(!empty($_GET['includeuc']) ? '&includeuc=yes' : '')."&current=$next&pertask=$pertask&lastprocess=$processed&allnum=$allnum&deletestart=$deletestart".$urladd;
				if(empty($_GET['uidarray'])) {
					$deladdmsg = cplang('members_delete_user_processing', array('deletestart' => $deletestart, 'nextdeletestart' => $deletestart+$delmemberlimit)).'<br>';
				} else {
					$deladdmsg = '';
				}
				if($nextdeleteitem != $deleteitem) {
					$nextlink .= "&deleteitem=$nextdeleteitem";
					cpmsg(cplang('members_delete_processing_next', array('deladdmsg' => $deladdmsg, 'item' => cplang('members_delete_'.$deleteitem), 'nextitem' => cplang('members_delete_'.$nextdeleteitem))), $nextlink, 'loadingform', array(), $extra);
				} else {
					$nextlink .= "&deleteitem=$deleteitem";
					cpmsg(cplang('members_delete_processing', array('deladdmsg' => $deladdmsg, 'item' => cplang('members_delete_'.$deleteitem), 'current' => $current, 'next' => $next)), $nextlink, 'loadingform', array(), $extra);
				}
			}
		}
	}

} elseif($operation == 'newsletter') {

	if(!submitcheck('newslettersubmit')) {
		loadcache('newsletter_detail');
		$newletter_detail = get_newsletter('newsletter_detail');
		$newletter_detail = dunserialize($newletter_detail);
		if($newletter_detail && $newletter_detail['uid'] == $_G['uid']) {
			if($_GET['goon'] == 'yes') {
				cpmsg("$lang[members_newsletter_send]: ".cplang('members_newsletter_processing', array('current' => $newletter_detail['current'], 'next' => $newletter_detail['next'], 'search_condition' => $newletter_detail['search_condition'])), $newletter_detail['action'], 'loadingform');
			} elseif($_GET['goon'] == 'no') {
				del_newsletter('newsletter_detail');
			} else {
				cpmsg('members_edit_continue', '', '', '', '<input type="button" class="btn" value="'.$lang[ok].'" onclick="location.href=\''.ADMINSCRIPT.'?action=members&operation=newsletter&goon=yes\'">&nbsp;&nbsp;<input type="button" class="btn" value="'.$lang[cancel].'" onclick="location.href=\''.ADMINSCRIPT.'?action=members&operation=newsletter&goon=no\';">');
				exit;
			}
		}
		if($_GET['do'] == 'mobile') {
			shownav('user', 'nav_members_newsletter_mobile');
			showsubmenusteps('nav_members_newsletter_mobile', array(
				array('nav_members_select', !$_GET['submit']),
				array('nav_members_notify', $_GET['submit']),
			));
			showtips('members_newsletter_mobile_tips');
		} else {
			shownav('user', 'nav_members_newsletter');
			showsubmenusteps('nav_members_newsletter', array(
				array('nav_members_select', !$_GET['submit']),
				array('nav_members_notify', $_GET['submit']),
			), array(), array(array('members_grouppmlist', 'members&operation=grouppmlist', 0)));
		}
		showsearchform('newsletter');

		if(submitcheck('submit', 1)) {
			$dostr = '';
			if($_GET['do'] == 'mobile') {
				$search_condition['token_noempty'] = 'token';
				$dostr = '&do=mobile';
			}
			$membernum = countmembers($search_condition, $urladd);

			showtagheader('div', 'newsletter', TRUE);
			showformheader('members&operation=newsletter'.$urladd.$dostr);
			showhiddenfields(array('notifymember' => 1));
			echo '<table class="tb tb1">';

			if(!$membernum) {
				showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
			} else {
				showtablerow('class="first"', array('class="th11"'), array(
					cplang('members_newsletter_members'),
					cplang('members_search_result', array('membernum' => $membernum))."<a href=\"###\" onclick=\"$('searchmembers').style.display='';$('newsletter').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">$lang[research]</a>"
				));
				showtablefooter();

				shownewsletter();

				$search_condition = serialize($search_condition);
				showsubmit('newslettersubmit', 'submit', 'td', '<input type="hidden" name="conditions" value=\''.$search_condition.'\' />');

			}

			showtablefooter();
			showformfooter();
			showtagfooter('div');

		}

	} else {

		$search_condition = dunserialize($_POST['conditions']);
		$membernum = countmembers($search_condition, $urladd);
		notifymembers('newsletter', 'newsletter');

	}

} elseif($operation == 'grouppmlist') {

	if(!empty($_GET['delete']) && ($isfounder || C::t('common_grouppm')->count_by_id_authorid($_GET['delete'], $_G['uid']))) {
		if(!empty($_GET['confirm'])) {
			C::t('common_grouppm')->delete($_GET['delete']);
			C::t('common_member_grouppm')->delete_by_gpmid($_GET['delete']);
		} else {
			cpmsg('members_grouppm_delete_confirm', 'action=members&operation=grouppmlist&delete='.intval($_GET['delete']).'&confirm=yes', 'form');
		}
	}
	shownav('user', 'nav_members_newsletter');
	showsubmenu('nav_members_newsletter', array(
		array('members_grouppmlist_newsletter', 'members&operation=newsletter', 0),
		array('members_grouppmlist', 'members&operation=grouppmlist', 1)
	));
	if($do) {
		$unreads = C::t('common_member_grouppm')->count_by_gpmid($do, 0);
	}

	showtableheader();
	$id = empty($do) ? 0 : $do;
	$authorid = $isfounder ? 0 : $_G['uid'];
	$grouppms = C::t('common_grouppm')->fetch_all_by_id_authorid($id, $authorid);
	if(!empty($grouppms)) {
		$users = C::t('common_member')->fetch_all(C::t('common_grouppm')->get_uids());
		foreach($grouppms as $grouppm) {
			showtablerow('', array('valign="top" class="td25"', 'valign="top"'), array(
			    '<a href="home.php?mod=space&uid='.$grouppm['authorid'].'" target="_blank">'.avatar($grouppm['authorid'], 'small').'</a>',
			    '<a href="home.php?mod=space&uid='.$grouppm['authorid'].'" target="_blank"><b>'.$users[$grouppm['authorid']]['username'].'</b></a> ('.dgmdate($grouppm['dateline']).'):<br />'.
			    $grouppm['message'].'<br /><br />'.
			    (!$do ?
				'<a href="'.ADMINSCRIPT.'?action=members&operation=grouppmlist&do='.$grouppm['id'].'">'.cplang('members_grouppmlist_view', array('number' => $grouppm['numbers'])).'</a>' :
				'<a href="'.ADMINSCRIPT.'?action=members&operation=grouppmlist&do='.$grouppm['id'].'&filter=unread">'.cplang('members_grouppmlist_view_unread').'</a>('.$unreads.') &nbsp; '.
				'<a href="'.ADMINSCRIPT.'?action=members&operation=grouppmlist&do='.$grouppm['id'].'&filter=read">'.cplang('members_grouppmlist_view_read').'</a>('.($grouppm['numbers'] - $unreads).')'),
				'<a href="'.ADMINSCRIPT.'?action=members&operation=grouppmlist&delete='.$grouppm['id'].'">'.cplang('delete').'</a>'
			));
		}
	} else {
		showtablerow('', '', cplang('members_newsletter_empty'));
	}
	showtablefooter();
	if($do) {
		$_GET['filter'] = in_array($_GET['filter'], array('read', 'unread')) ? $_GET['filter'] : '';
		$filteradd = $_GET['filter'] ? '&filter='.$_GET['filter'] : '';
		$ppp = 100;
		$start_limit = ($page - 1) * $ppp;
		if($_GET['filter'] != 'unread') {
			$count = C::t('common_member_grouppm')->count_by_gpmid($do, 1);
		} else {
			$count = $unreads;
		}
		$multipage = multi($count, $ppp, $page, ADMINSCRIPT."?action=members&operation=grouppmlist&do=$do".$filteradd);
		$alldata = C::t('common_member_grouppm')->fetch_all_by_gpmid($do, $_GET['filter'] == 'read' ? 1 : 0, $start_limit, $ppp);
		$allmember = $alldata ? C::t('common_member')->fetch_all_username_by_uid(array_keys($alldata)) : array();
		foreach($alldata as $uid => $gpmuser) {
			echo '<div style="margin-bottom:5px;float:left;width:24%"><b><a href="home.php?mod=space&uid='.$uid.'" target="_blank">'.$allmember[$uid].'</a></b><br />&nbsp;';
			if($gpmuser['status'] == 0) {
				echo '<span class="lightfont">'.cplang('members_grouppmlist_status_0').'</span>';
			} else {
				echo dgmdate($gpmuser['dateline'], 'u').' '.cplang('members_grouppmlist_status_1');
				if($gpmuser['status'] == -1) {
					echo ', <span class="error">'.cplang('members_grouppmlist_status_-1').'</span>';
				}
			}
			echo '</div>';
		}
		echo $multipage;
	}

} elseif($operation == 'reward') {

	if(!submitcheck('rewardsubmit')) {

		shownav('user', 'nav_members_reward');
		showsubmenusteps('nav_members_reward', array(
			array('nav_members_select', !$_GET['submit']),
			array('nav_members_reward', $_GET['submit']),
		));

		showsearchform('reward');

		if(submitcheck('submit', 1)) {

			$membernum = countmembers($search_condition, $urladd);
			showtagheader('div', 'reward', TRUE);
			showformheader('members&operation=reward'.$urladd);
			echo '<table class="tb tb1">';

			if(!$membernum) {
				showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
				showtablefooter();
			} else {

				$creditscols = array('credits_title');
				$creditsvalue = $resetcredits = array();
				$js_extcreditids = '';
				for($i=1; $i<=8; $i++) {
					$js_extcreditids .= (isset($_G['setting']['extcredits'][$i]) ? ($js_extcreditids ? ',' : '').$i : '');
					$creditscols[] = isset($_G['setting']['extcredits'][$i]) ? $_G['setting']['extcredits'][$i]['title'] : 'extcredits'.$i;
					$creditsvalue[] = isset($_G['setting']['extcredits'][$i]) ? '<input type="text" class="txt" size="3" id="addextcredits['.$i.']" name="addextcredits['.$i.']" value="0"> '.$_G['setting']['extcredits'][$i]['unit'] : '<input type="text" class="txt" size="3" value="N/A" disabled>';
					$resetcredits[] = isset($_G['setting']['extcredits'][$i]) ? '<input type="checkbox" id="resetextcredits['.$i.']" name="resetextcredits['.$i.']" value="1" class="radio" disabled> '.$_G['setting']['extcredits'][$i]['unit'] : '<input type="checkbox" disabled  class="radio">';
				}
				$creditsvalue = array_merge(array('<input type="radio" name="updatecredittype" id="updatecredittype0" value="0" class="radio" onclick="var extcredits = new Array('.$js_extcreditids.'); for(k in extcredits) {$(\'resetextcredits[\'+extcredits[k]+\']\').disabled = true; $(\'addextcredits[\'+extcredits[k]+\']\').disabled = false;}" checked="checked" /><label for="updatecredittype0">'.$lang['members_reward_value'].'</label>'), $creditsvalue);
				$resetcredits = array_merge(array('<input type="radio" name="updatecredittype" id="updatecredittype1" value="1" class="radio" onclick="var extcredits = new Array('.$js_extcreditids.'); for(k in extcredits) {$(\'addextcredits[\'+extcredits[k]+\']\').disabled = true; $(\'resetextcredits[\'+extcredits[k]+\']\').disabled = false;}" /><label for="updatecredittype1">'.$lang['members_reward_clean'].'</label>'), $resetcredits);

				showtablerow('class="first"', array('class="th11"'), array(
					cplang('members_reward_members'),
					cplang('members_search_result', array('membernum' => $membernum))."<a href=\"###\" onclick=\"$('searchmembers').style.display='';$('reward').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">$lang[research]</a>"
				));

				echo '<tr><td class="th12">'.cplang('nav_members_reward').'</td><td>';
				showtableheader('', 'noborder');
				showsubtitle($creditscols);
				showtablerow('', array('class="td23"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), $creditsvalue);
				showtablerow('', array('class="td23"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), $resetcredits);
				showtablefooter();
				showtablefooter();

				showtagheader('div', 'messagebody');
				shownewsletter();
				showtagfooter('div');
				showsubmit('rewardsubmit', 'submit', 'td', '<input class="checkbox" type="checkbox" name="notifymember" value="1" onclick="$(\'messagebody\').style.display = this.checked ? \'\' : \'none\'" id="credits_notify" /><label for="credits_notify">'.cplang('members_reward_notify').'</label>');

			}

			showtablefooter();
			showformfooter();
			showtagfooter('div');

		}

	} else {
		if(!empty($_POST['conditions'])) $search_condition = dunserialize($_POST['conditions']);
		$membernum = countmembers($search_condition, $urladd);
		notifymembers('reward', 'creditsnotify');

	}

} elseif($operation == 'confermedal') {

	$medals = '';
	foreach(C::t('forum_medal')->fetch_all_data(1) as $medal) {
		$medals .= showtablerow('', array('class="td25"', 'class="td23"'), array(
			"<input class=\"checkbox\" type=\"checkbox\" name=\"medals[$medal[medalid]]\" value=\"1\" />",
			"<img src=\"static/image/common/$medal[image]\" />",
			$medal['name']
		), TRUE);
	}

	if(!$medals) {
		cpmsg('members_edit_medals_nonexistence', 'action=medals', 'error');
	}

	if(!submitcheck('confermedalsubmit')) {

		shownav('extended', 'nav_medals', 'nav_members_confermedal');
		showsubmenusteps('nav_members_confermedal', array(
			array('nav_members_select', !$_GET['submit']),
			array('nav_members_confermedal', $_GET['submit']),
		), array(
			array('admin', 'medals', 0),
			array('nav_medals_confer', 'members&operation=confermedal', 1),
			array('nav_medals_mod', 'medals&operation=mod', 0)
		));

		showsearchform('confermedal');

		if(submitcheck('submit', 1)) {

			$membernum = countmembers($search_condition, $urladd);

			showtagheader('div', 'confermedal', TRUE);
			showformheader('members&operation=confermedal'.$urladd);
			echo '<table class="tb tb1">';

			if(!$membernum) {
				showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
				showtablefooter();
			} else {

				showtablerow('class="first"', array('class="th11"'), array(
					cplang('members_confermedal_members'),
					cplang('members_search_result', array('membernum' => $membernum))."<a href=\"###\" onclick=\"$('searchmembers').style.display='';$('confermedal').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">$lang[research]</a>"
				));

				echo '<tr><td class="th12">'.cplang('members_confermedal').'</td><td>';
				showtableheader('', 'noborder');
				showsubtitle(array('medals_grant', 'medals_image', 'name'));
				echo $medals;
				showtablefooter();
				showtablefooter();

				showtagheader('div', 'messagebody');
				shownewsletter();
				showtagfooter('div');
				showsubmit('confermedalsubmit', 'submit', 'td', '<input class="checkbox" type="checkbox" name="notifymember" value="1" onclick="$(\'messagebody\').style.display = this.checked ? \'\' : \'none\'" id="grant_notify"/><label for="grant_notify">'.cplang('medals_grant_notify').'</label>');

			}

			showtablefooter();
			showformfooter();
			showtagfooter('div');

		}

	} else {
		if(!empty($_POST['conditions'])) $search_condition = dunserialize($_POST['conditions']);
		$membernum = countmembers($search_condition, $urladd);
		notifymembers('confermedal', 'medalletter');

	}
} elseif($operation == 'confermagic') {

	$magics = '';
	foreach(C::t('common_magic')->fetch_all_data(1) as $magic) {
		$magics .= showtablerow('', array('class="td25"', 'class="td23"', 'class="td25"', ''), array(
			"<input class=\"checkbox\" type=\"checkbox\" name=\"magic[]\" value=\"$magic[magicid]\" />",
			"<img src=\"static/image/magic/$magic[identifier].gif\" />",
			$magic['name'],
			'<input class="txt" type="text" name="magicnum['.$magic['magicid'].']" value="1" size="3">'
		), TRUE);
	}

	if(!$magics) {
		cpmsg('members_edit_magics_nonexistence', 'action=magics', 'error');
	}

	if(!submitcheck('confermagicsubmit')) {

		shownav('extended', 'nav_magics', 'nav_members_confermagic');
		showsubmenusteps('nav_members_confermagic', array(
			array('nav_members_select', !$_GET['submit']),
			array('nav_members_confermagic', $_GET['submit']),
		), array(
			array('admin', 'magics&operation=admin', 0),
			array('nav_magics_confer', 'members&operation=confermagic', 1)
		));

		showsearchform('confermagic');

		if(submitcheck('submit', 1)) {

			$membernum = countmembers($search_condition, $urladd);

			showtagheader('div', 'confermedal', TRUE);
			showformheader('members&operation=confermagic'.$urladd);
			echo '<table class="tb tb1">';

			if(!$membernum) {
				showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
				showtablefooter();
			} else {

				showtablerow('class="first"', array('class="th11"'), array(
					cplang('members_confermagic_members'),
					cplang('members_search_result', array('membernum' => $membernum))."<a href=\"###\" onclick=\"$('searchmembers').style.display='';$('confermedal').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">$lang[research]</a>"
				));

				echo '<tr><td class="th12">'.cplang('members_confermagic').'</td><td>';
				showtableheader('', 'noborder');
				showsubtitle(array('nav_magics_confer', 'nav_magics_image', 'nav_magics_name', 'nav_magics_num'));
				echo $magics;
				showtablefooter();
				showtablefooter();

				showtagheader('div', 'messagebody');
				shownewsletter();
				showtagfooter('div');
				showsubmit('confermagicsubmit', 'submit', 'td', '<input class="checkbox" type="checkbox" name="notifymember" value="1" onclick="$(\'messagebody\').style.display = this.checked ? \'\' : \'none\'" id="grant_notify"/><label for="grant_notify">'.cplang('magics_grant_notify').'</label>');

			}

			showtablefooter();
			showformfooter();
			showtagfooter('div');

		}

	} else {
		if(!empty($_POST['conditions'])) $search_condition = dunserialize($_POST['conditions']);
		$membernum = countmembers($search_condition, $urladd);
		notifymembers('confermagic', 'magicletter');
	}
} elseif($operation == 'add') {

	if(!submitcheck('addsubmit')) {

		$groupselect = array();
		$query = C::t('common_usergroup')->fetch_all_by_not_groupid(array(5, 6, 7));
		foreach($query as $group) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			if($group['type'] == 'member' && $group['creditshigher'] == 0) {
				$groupselect[$group['type']] .= "<option value=\"$group[groupid]\" selected>$group[grouptitle]</option>\n";
			} else {
				$groupselect[$group['type']] .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
			}
		}
		$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';
		shownav('user', 'nav_members_add');
		showsubmenu('members_add');
		showformheader('members&operation=add');
		showtableheader();
		showsetting('username', 'newusername', '', 'text');
		showsetting('password', 'newpassword', '', 'text');
		showsetting('email', 'newemail', '', 'text');
		showsetting('usergroup', '', '', '<select name="newgroupid">'.$groupselect.'</select>');
		showsetting('members_add_email_notify', 'emailnotify', '', 'radio');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$newusername = trim($_GET['newusername']);
		$newpassword = trim($_GET['newpassword']);
		$newemail = strtolower(trim($_GET['newemail']));

		if(!$newusername || !isset($_GET['confirmed']) && !$newpassword || !isset($_GET['confirmed']) && !$newemail) {
			cpmsg('members_add_invalid', '', 'error');
		}

		$usernamelen = dstrlen($newusername);
		if($usernamelen < 3) {
			cpmsg('members_add_username_tooshort', '', 'error');
		} elseif($usernamelen > 15) {
			cpmsg('members_add_username_toolong', '', 'error');
		}

		if(C::t('common_member')->fetch_uid_by_username($newusername) || C::t('common_member_archive')->fetch_uid_by_username($newusername)) {
			cpmsg('members_add_username_duplicate', '', 'error');
		}

		loaducenter();

		$uid = uc_user_register(addslashes($newusername), $newpassword, $newemail);
		if($uid <= 0) {
			if($uid == -1) {
				cpmsg('members_add_illegal', '', 'error');
			} elseif($uid == -2) {
				cpmsg('members_username_protect', '', 'error');
			} elseif($uid == -3) {
				if(empty($_GET['confirmed'])) {
					cpmsg('members_add_username_activation', 'action=members&operation=add&addsubmit=yes&newgroupid='.$_GET['newgroupid'].'&newusername='.rawurlencode($newusername), 'form');
				} else {
					list($uid,, $newemail) = uc_get_user(addslashes($newusername));
				}
			} elseif($uid == -4) {
				cpmsg('members_email_illegal', '', 'error');
			} elseif($uid == -5) {
				cpmsg('members_email_domain_illegal', '', 'error');
			} elseif($uid == -6) {
				cpmsg('members_email_duplicate', '', 'error');
			}
		}

		$group = C::t('common_usergroup')->fetch($_GET['newgroupid']);
		$newadminid = in_array($group['radminid'], array(1, 2, 3)) ? $group['radminid'] : ($group['type'] == 'special' ? -1 : 0);
		if($group['radminid'] == 1) {
			cpmsg('members_add_admin_none', '', 'error');
		}
		if(in_array($group['groupid'], array(5, 6, 7))) {
			cpmsg('members_add_ban_all_none', '', 'error');
		}

		$profile = $verifyarr = array();
		loadcache('fields_register');
		$init_arr = explode(',', $_G['setting']['initcredits']);
		$password = md5(random(10));
		C::t('common_member')->insert($uid, $newusername, $password, $newemail, 'Manual Acting', $_GET['newgroupid'], $init_arr, $newadminid);
		if($_GET['emailnotify']) {
			if(!function_exists('sendmail')) {
				include libfile('function/mail');
			}
			$add_member_subject = lang('email', 'add_member_subject');
			$add_member_message = lang('email', 'add_member_message', array(
				'newusername' => $newusername,
				'bbname' => $_G['setting']['bbname'],
				'adminusername' => $_G['member']['username'],
				'siteurl' => $_G['siteurl'],
				'newpassword' => $newpassword,
			));
			if(!sendmail("$newusername <$newemail>", $add_member_subject, $add_member_message)) {
				runlog('sendmail', "$newemail sendmail failed.");
			}
		}

		updatecache('setting');
		cpmsg('members_add_succeed', '', 'succeed', array('username' => $newusername, 'uid' => $uid));

	}

} elseif($operation == 'group') {
	$membermf = C::t('common_member_field_forum'.$tableext)->fetch($_GET['uid']);
	$membergroup = C::t('common_usergroup')->fetch($member['groupid']);
	$member = array_merge($member, (array)$membermf, $membergroup);

	if(!submitcheck('editsubmit')) {

		$checkadminid = array(($member['adminid'] >= 0 ? $member['adminid'] : 0) => 'checked');

		$member['groupterms'] = dunserialize($member['groupterms']);

		if($member['groupterms']['main']) {
			$expirydate = dgmdate($member['groupterms']['main']['time'], 'Y-n-j');
			$expirydays = ceil(($member['groupterms']['main']['time'] - TIMESTAMP) / 86400);
			$selecteaid = array($member['groupterms']['main']['adminid'] => 'selected');
			$selectegid = array($member['groupterms']['main']['groupid'] => 'selected');
		} else {
			$expirydate = $expirydays = '';
			$selecteaid = array($member['adminid'] => 'selected');
			$selectegid = array(($member['type'] == 'member' ? 0 : $member['groupid']) => 'selected');
		}

		$extgroups = $expgroups = '';
		$radmingids = 0;
		$extgrouparray = explode("\t", $member['extgroupids']);
		$groups = array('system' => '', 'special' => '', 'member' => '');
		$group = array('groupid' => 0, 'radminid' => 0, 'type' => '', 'grouptitle' => $lang['usergroups_system_0'], 'creditshigher' => 0, 'creditslower' => '0');
		$query = array_merge(array($group), (array)C::t('common_usergroup')->fetch_all_not(array(6, 7)));
		foreach($query as $group) {
			if($group['groupid'] && !in_array($group['groupid'], array(4, 5, 6, 7, 8)) && ($group['type'] == 'system' || $group['type'] == 'special')) {
				$extgroups .= showtablerow('', array('class="td27"', 'style="width:70%"'), array(
					'<input class="checkbox" type="checkbox" name="extgroupidsnew[]" value="'.$group['groupid'].'" '.(in_array($group['groupid'], $extgrouparray) ? 'checked' : '').' id="extgid_'.$group['groupid'].'" /><label for="extgid_'.$group['groupid'].'"> '.$group['grouptitle'].'</label>',
					'<input type="text" class="txt" size="9" name="extgroupexpirynew['.$group['groupid'].']" value="'.(in_array($group['groupid'], $extgrouparray) && !empty($member['groupterms']['ext'][$group['groupid']]) ? dgmdate($member['groupterms']['ext'][$group['groupid']], 'Y-n-j') : '').'" onclick="showcalendar(event, this)" />'
				), TRUE);
			}
			if($group['groupid'] && $group['type'] == 'member' && !($member['credits'] >= $group['creditshigher'] && $member['credits'] < $group['creditslower']) && $member['groupid'] != $group['groupid']) {
				continue;
			}

			$expgroups .= '<option name="expgroupidnew" value="'.$group['groupid'].'" '.$selectegid[$group['groupid']].'>'.$group['grouptitle'].'</option>';

			if($group['groupid'] != 0) {
				$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
				$groups[$group['type']] .= '<option value="'.$group['groupid'].'"'.($member['groupid'] == $group['groupid'] ? 'selected="selected"' : '').' gtype="'.$group['type'].'">'.$group['grouptitle'].'</option>';
				if($group['type'] == 'special' && !$group['radminid']) {
					$radmingids .= ','.$group['groupid'];
				}
			}

		}

		if(!$groups['member']) {
			$group = C::t('common_usergroup')->fetch_new_groupid(true);
			$groups['member'] = '<option value="'.$group['groupid'].'" gtype="member">'.$group['grouptitle'].'</option>';
		}

		shownav('user', 'members_group');
		showsubmenu('members_group_member', array(), '', array('username' => $member['username']));
		echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
		showformheader("members&operation=group&uid=$member[uid]");
		showtableheader('usergroup', 'nobottom');
		showsetting('members_group_group', '', '', '<select name="groupidnew" onchange="if(in_array(this.value, ['.$radmingids.'])) {$(\'relatedadminid\').style.display = \'\';$(\'adminidnew\').name=\'adminidnew[\' + this.value + \']\';} else {$(\'relatedadminid\').style.display = \'none\';$(\'adminidnew\').name=\'adminidnew[0]\';}"><optgroup label="'.$lang['usergroups_system'].'">'.$groups['system'].'<optgroup label="'.$lang['usergroups_special'].'">'.$groups['special'].'<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groups['specialadmin'].'<optgroup label="'.$lang['usergroups_member'].'">'.$groups['member'].'</select>');
		showtagheader('tbody', 'relatedadminid', $member['type'] == 'special' && !$member['radminid'], 'sub');
		showsetting('members_group_related_adminid', '', '', '<select id="adminidnew" name="adminidnew['.$member['groupid'].']"><option value="0"'.($member['adminid'] == 0 ? ' selected' : '').'>'.$lang['none'].'</option><option value="3"'.($member['adminid'] == 3 ? ' selected' : '').'>'.$lang['usergroups_system_3'].'</option><option value="2"'.($member['adminid'] == 2 ? ' selected' : '').'>'.$lang['usergroups_system_2'].'</option><option value="1"'.($member['adminid'] == 1 ? ' selected' : '').'>'.$lang['usergroups_system_1'].'</option></select>');
		showtagfooter('tbody');
		showsetting('members_group_validity', 'expirydatenew', $expirydate, 'calendar');
		showsetting('members_group_orig_adminid', '', '', '<select name="expgroupidnew">'.$expgroups.'</select>');
		showsetting('members_group_orig_groupid', '', '', '<select name="expadminidnew"><option value="0" '.$selecteaid[0].'>'.$lang['usergroups_system_0'].'</option><option value="1" '.$selecteaid[1].'>'.$lang['usergroups_system_1'].'</option><option value="2" '.$selecteaid[2].'>'.$lang['usergroups_system_2'].'</option><option value="3" '.$selecteaid[3].'>'.$lang['usergroups_system_3'].'</option></select>');
		showtablefooter();

		showtableheader('members_group_extended', 'noborder fixpadding');
		showsubtitle(array('usergroup', 'validity'));
		echo $extgroups;
		showtablerow('', 'colspan="2"', cplang('members_group_extended_comment'));
		showtablefooter();

		showtableheader('members_edit_reason', 'notop');
		showsetting('members_group_ban_reason', 'reason', '', 'textarea');
		showsubmit('editsubmit');
		showtablefooter();

		showformfooter();

	} else {

		$group = C::t('common_usergroup')->fetch($_GET['groupidnew']);
		if(!$group) {
			cpmsg('undefined_action', '', 'error');
		}

		if(strlen(is_array($_GET['extgroupidsnew']) ? implode("\t", $_GET['extgroupidsnew']) : '') > 30) {
			cpmsg('members_edit_groups_toomany', '', 'error');
		}

		if($member['groupid'] != $_GET['groupidnew'] && isfounder($member)) {
			cpmsg('members_edit_groups_isfounder', '', 'error');
		}

		$_GET['adminidnew'] = $_GET['adminidnew'][$_GET['groupidnew']];
		switch($group['type']) {
			case 'member':
				$_GET['groupidnew'] = in_array($_GET['adminidnew'], array(1, 2, 3)) ? $_GET['adminidnew'] : $_GET['groupidnew'];
				break;
			case 'special':
				if($group['radminid']) {
					$_GET['adminidnew'] = $group['radminid'];
				} elseif(!in_array($_GET['adminidnew'], array(1, 2, 3))) {
					$_GET['adminidnew'] = -1;
				}
				break;
			case 'system':
				$_GET['adminidnew'] = in_array($_GET['groupidnew'], array(1, 2, 3)) ? $_GET['groupidnew'] : -1;
				break;
		}

		$groupterms = array();

		if($_GET['expirydatenew']) {

			$maingroupexpirynew = strtotime($_GET['expirydatenew']);

			$group = C::t('common_usergroup')->fetch($_GET['expgroupidnew']);
			if(!$group) {
				$_GET['expgroupidnew'] = in_array($_GET['expadminidnew'], array(1, 2, 3)) ? $_GET['expadminidnew'] : $_GET['expgroupidnew'];
			} else {
				switch($group['type']) {
					case 'special':
						if($group['radminid']) {
							$_GET['expadminidnew'] = $group['radminid'];
						} elseif(!in_array($_GET['expadminidnew'], array(1, 2, 3))) {
							$_GET['expadminidnew'] = -1;
						}
						break;
					case 'system':
						$_GET['expadminidnew'] = in_array($_GET['expgroupidnew'], array(1, 2, 3)) ? $_GET['expgroupidnew'] : -1;
						break;
				}
			}

			if($_GET['expgroupidnew'] == $_GET['groupidnew']) {
				cpmsg('members_edit_groups_illegal', '', 'error');
			} elseif($maingroupexpirynew > TIMESTAMP) {
				if($_GET['expgroupidnew'] || $_GET['expadminidnew']) {
					$groupterms['main'] = array('time' => $maingroupexpirynew, 'adminid' => $_GET['expadminidnew'], 'groupid' => $_GET['expgroupidnew']);
				} else {
					$groupterms['main'] = array('time' => $maingroupexpirynew);
				}
				$groupterms['ext'][$_GET['groupidnew']] = $maingroupexpirynew;
			}

		}

		if(is_array($_GET['extgroupexpirynew'])) {
			foreach($_GET['extgroupexpirynew'] as $extgroupid => $expiry) {
				if(is_array($_GET['extgroupidsnew']) && in_array($extgroupid, $_GET['extgroupidsnew']) && !isset($groupterms['ext'][$extgroupid]) && $expiry && ($expiry = strtotime($expiry)) > TIMESTAMP) {
					$groupterms['ext'][$extgroupid] = $expiry;
				}
			}
		}

		$grouptermsnew = serialize($groupterms);
		$groupexpirynew = groupexpiry($groupterms);
		$extgroupidsnew = $_GET['extgroupidsnew'] && is_array($_GET['extgroupidsnew']) ? implode("\t", $_GET['extgroupidsnew']) : '';

		C::t('common_member'.$tableext)->update($member['uid'], array('groupid'=>$_GET['groupidnew'], 'adminid'=>$_GET['adminidnew'], 'extgroupids'=>$extgroupidsnew, 'groupexpiry'=>$groupexpirynew));
		if(C::t('common_member_field_forum'.$tableext)->fetch($member['uid'])) {
			C::t('common_member_field_forum'.$tableext)->update($member['uid'], array('groupterms' => $grouptermsnew));
		} else {
			C::t('common_member_field_forum'.$tableext)->insert(array('uid' => $member['uid'], 'groupterms' => $grouptermsnew));
		}

		if($_GET['groupidnew'] != $member['groupid'] && (in_array($_GET['groupidnew'], array(4, 5)) || in_array($member['groupid'], array(4, 5)))) {
			$my_opt = in_array($_GET['groupidnew'], array(4, 5)) ? 'banuser' : 'unbanuser';
			banlog($member['username'], $member['groupid'], $_GET['groupidnew'], $groupexpirynew, $_GET['reason']);
		}

		cpmsg('members_edit_groups_succeed', "action=members&operation=group&uid=$member[uid]", 'succeed');

	}

} elseif($operation == 'credit' && $_G['setting']['extcredits']) {

	if($tableext) {
		cpmsg('members_edit_credits_failure', '', 'error');
	}
	$membercount = C::t('common_member_count'.$tableext)->fetch($member['uid']);
	$membergroup = C::t('common_usergroup')->fetch($member['groupid']);
	$member = array_merge($member, $membercount, $membergroup);

	if(!submitcheck('creditsubmit')) {

		eval("\$membercredit = @round({$_G[setting][creditsformula]});");

		if(($jscreditsformula = C::t('common_setting')->fetch('creditsformula'))) {
			$jscreditsformula = str_replace(array('digestposts', 'posts', 'threads'), array($member['digestposts'], $member['posts'],$member['threads']), $jscreditsformula);
		}

		$creditscols = array('members_credit_ranges', 'credits');
		$creditsvalue = array($member['type'] == 'member' ? "$member[creditshigher]~$member[creditslower]" : 'N/A', '<input type="text" class="txt" name="jscredits" id="jscredits" value="'.$membercredit.'" size="6" disabled style="padding:0;width:6em;border:none; background-color:transparent">');
		for($i = 1; $i <= 8; $i++) {
			$jscreditsformula = str_replace('extcredits'.$i, "extcredits[$i]", $jscreditsformula);
			$creditscols[] = isset($_G['setting']['extcredits'][$i]) ? $_G['setting']['extcredits'][$i]['title'] : 'extcredits'.$i;
			$creditsvalue[] = isset($_G['setting']['extcredits'][$i]) ? '<input type="text" class="txt" size="3" name="extcreditsnew['.$i.']" id="extcreditsnew['.$i.']" value="'.$member['extcredits'.$i].'" onkeyup="membercredits()"> '.$_G['setting']['extcredits'][$i]['unit'] : '<input type="text" class="txt" size="3" value="N/A" disabled>';
		}

		echo <<<EOT
<script language="JavaScript">
	var extcredits = new Array();
	function membercredits() {
		var credits = 0;
		for(var i = 1; i <= 8; i++) {
			e = $('extcreditsnew['+i+']');
			if(e && parseInt(e.value)) {
				extcredits[i] = parseInt(e.value);
			} else {
				extcredits[i] = 0;
			}
		}
		$('jscredits').value = Math.round($jscreditsformula);
	}
</script>
EOT;
		shownav('user', 'members_credit');
		showsubmenu('members_credit');
		showtips('members_credit_tips');
		showformheader("members&operation=credit&uid={$_GET['uid']}");
		showtableheader('<em class="right"><a href="'.ADMINSCRIPT.'?action=logs&operation=credit&srch_uid='.$_GET['uid'].'&frame=yes" target="_blank">'.cplang('members_credit_logs').'</a></em>'.cplang('members_credit').' - '.$member['username'].'('.$member['grouptitle'].')', 'nobottom');
		showsubtitle($creditscols);
		showtablerow('', array('', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), $creditsvalue);
		showtablefooter();
		showtableheader('', 'notop');
		showtitle('members_edit_reason');
		showsetting('members_credit_reason', 'reason', '', 'textarea');
		showsubmit('creditsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$diffarray = array();
		$sql = $comma = '';
		if(is_array($_GET['extcreditsnew'])) {
			foreach($_GET['extcreditsnew'] as $id => $value) {
				if($member['extcredits'.$id] != ($value = intval($value))) {
					$diffarray[$id] = $value - $member['extcredits'.$id];
					$sql .= $comma."extcredits$id='$value'";
					$comma = ', ';
				}
			}
		}

		if($diffarray) {
			foreach($diffarray as $id => $diff) {
				$logs[] = dhtmlspecialchars("$_G[timestamp]\t{$_G[member][username]}\t$_G[adminid]\t$member[username]\t$id\t$diff\t0\t\t{$_GET['reason']}");
			}
			updatemembercount($_GET['uid'], $diffarray);
			writelog('ratelog', $logs);
		}

		cpmsg('members_edit_credits_succeed', "action=members&operation=credit&uid={$_GET['uid']}", 'succeed');

	}

} elseif($operation == 'medal') {

	$membermf = C::t('common_member_field_forum'.$tableext)->fetch($_GET['uid']);
	$member = array_merge($member, $membermf);

	if(!submitcheck('medalsubmit')) {

		$medals = '';
		$membermedals = array();
		loadcache('medals');
		foreach (explode("\t", $member['medals']) as $key => $membermedal) {
			list($medalid, $medalexpiration) = explode("|", $membermedal);
			if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
				$membermedals[$key] = $medalid;
			} else {
				unset($membermedals[$key]);
			}
		}

		foreach(C::t('forum_medal')->fetch_all_data(1) as $medal) {
			$medals .= showtablerow('', array('class="td25"', 'class="td23"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"medals[$medal[medalid]]\" value=\"1\" ".(in_array($medal['medalid'], $membermedals) ? 'checked' : '')." />",
				"<img src=\"static/image/common/$medal[image]\" />",
				$medal['name']

			), TRUE);
		}

		if(!$medals) {
			cpmsg('members_edit_medals_nonexistence', '', 'error');
		}

		shownav('user', 'nav_members_confermedal');
		showsubmenu('nav_members_confermedal');
		showformheader("members&operation=medal&uid={$_GET['uid']}");
		showtableheader("$lang[members_confermedal_to] <a href='home.php?mod=space&uid={$_GET['uid']}' target='_blank'>$member[username]</a>", 'fixpadding');
		showsubtitle(array('medals_grant', 'medals_image', 'name'));
		echo $medals;
		showsubmit('medalsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$medalsdel = $medalsadd = $medalsnew = $origmedalsarray = $medalsarray = array();
		if(is_array($_GET['medals'])) {
			foreach($_GET['medals'] as $medalid => $newgranted) {
				if($newgranted) {
					$medalsarray[] = $medalid;
				}
			}
		}
		loadcache('medals');
		foreach($member['medals'] = explode("\t", $member['medals']) as $key => $modmedalid) {
			list($medalid, $medalexpiration) = explode("|", $modmedalid);
			if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
				$origmedalsarray[] = $medalid;
			}
		}
		foreach(array_unique(array_merge($origmedalsarray, $medalsarray)) as $medalid) {
			if($medalid) {
				$orig = in_array($medalid, $origmedalsarray);
				$new = in_array($medalid, $medalsarray);
				if($orig != $new) {
					if($orig && !$new) {
						$medalsdel[] = $medalid;
					} elseif(!$orig && $new) {
						$medalsadd[] = $medalid;
					}
				}
			}
		}
		if(!empty($medalsarray)) {
			foreach(C::t('forum_medal')->fetch_all_by_id($medalsarray) as $modmedal) {
				if(empty($modmedal['expiration'])) {
					$medalsnew[] = $modmedal[medalid];
					$medalstatus = 0;
				} else {
					$modmedal['expiration'] = TIMESTAMP + $modmedal['expiration'] * 86400;
					$medalsnew[] = $modmedal[medalid].'|'.$modmedal['expiration'];
					$medalstatus = 1;
				}
				if(in_array($modmedal['medalid'], $medalsadd)) {
					$data = array(
						'uid' => $_GET['uid'],
						'medalid' => $modmedal['medalid'],
						'type' => 0,
						'dateline' => $_G['timestamp'],
						'expiration' => $modmedal['expiration'],
						'status' => $medalstatus,
					);
					C::t('forum_medallog')->insert($data);
					C::t('common_member_medal')->insert(array('uid' => $_GET['uid'], 'medalid' => $modmedal['medalid']), 0, 1);
				}
			}
		}
		if(!empty($medalsdel)) {
			C::t('forum_medallog')->update_type_by_uid_medalid(4, $_GET['uid'], $medalsdel);
			C::t('common_member_medal')->delete_by_uid_medalid($_GET['uid'], $medalsdel);
		}
		$medalsnew = implode("\t", $medalsnew);

		C::t('common_member_field_forum'.$tableext)->update($_GET['uid'], array('medals' => $medalsnew));

		cpmsg('members_edit_medals_succeed', "action=members&operation=medal&uid={$_GET['uid']}", 'succeed');

	}

} elseif($operation == 'ban') {

	$membermf = C::t('common_member_field_forum'.$tableext)->fetch($_GET['uid']);
	$membergroup = C::t('common_usergroup')->fetch($member['groupid']);
	$membergroupfield = C::t('common_usergroup_field')->fetch($member['groupid']);
	$member = array_merge($member, $membermf, $membergroup, $membergroupfield);

	if(($member['type'] == 'system' && in_array($member['groupid'], array(1, 2, 3, 6, 7, 8))) || $member['type'] == 'special') {
		cpmsg('members_edit_illegal', '', 'error', array('grouptitle' => $member['grouptitle'], 'uid' => $member['uid']));
	}

	if($member['allowadmincp']) {
		cpmsg('members_edit_illegal_portal', '', 'error',array('uid' => $member['uid']));
	}

	$member['groupterms'] = dunserialize($member['groupterms']);
	$member['banexpiry'] = !empty($member['groupterms']['main']['time']) && ($member['groupid'] == 4 || $member['groupid'] == 5) ? dgmdate($member['groupterms']['main']['time'], 'Y-n-j') : '';

	if(!submitcheck('bansubmit')) {

		echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
		shownav('user', 'members_ban_user');
		showsubmenu($lang['members_ban_user'].($member['username'] ? ' - '.$member['username'] : ''));
		showtips('members_ban_tips');
		showformheader('members&operation=ban');
		showtableheader();
		showsetting('members_ban_username', 'username', $member['username'], 'text', null, null, '<input type="button" id="crimebtn" class="btn" style="margin-top:-1px;display:none;" onclick="getcrimerecord();" value="'.$lang['crime_checkrecord'].'" />', 'onkeyup="showcrimebtn(this);" id="banusername"');
		if($member) {

			showtagheader('tbody', 'member_status', 1);
			showtablerow('', 'class="td27" colspan="2"', cplang('members_edit_current_status').'<span class="normal">: '.($member['groupid'] == 4 ? $lang['members_ban_post'] : ($member['groupid'] == 5 ? $lang['members_ban_visit'] : ($member['status'] == -1 ? $lang['members_ban_status'] : $lang['members_ban_none']))).'</span>');

			include_once libfile('function/member');
			$clist = crime('getactionlist', $member['uid']);

			if($clist) {
				echo '<tr><td class="td27" colspan="2">'.$lang[members_ban_crime_record].':</td></tr>';
				echo '<tr><td colspan="2" style="padding:0 !important;border-top:none;"><table style="width:100%;">';
				showtablerow('class="partition"', array('width="15%"', 'width="10%"', 'width="20%"', '', 'width="15%"'), array($lang['crime_user'], $lang['crime_action'], $lang['crime_dateline'], $lang['crime_reason'], $lang['crime_operator']));
				foreach($clist as $crime) {
					showtablerow('', '', array('<a href="home.php?mod=space&uid='.$member['uid'].'">'.$member['username'], $lang[$crime['action']], date('Y-m-d H:i:s', $crime['dateline']), $crime['reason'], '<a href="home.php?mod=space&uid='.$crime['operatorid'].'" target="_blank">'.$crime['operator'].'</a>'));
				}
				echo '</table></td></tr>';
			}
			showtagfooter('tbody');
		}
		showsetting('members_ban_type', array('bannew', array(
			array('', $lang['members_ban_none'], array('validity' => 'none')),
			array('post', $lang['members_ban_post'], array('validity' => '')),
			array('visit', $lang['members_ban_visit'], array('validity' => '')),
			array('status', $lang['members_ban_status'], array('validity' => 'none'))
		)), '', 'mradio');
		showtagheader('tbody', 'validity', false, 'sub');
		showsetting('members_ban_validity', '', '', selectday('banexpirynew', array(0, 1, 3, 5, 7, 14, 30, 60, 90, 180, 365)));
		showtagfooter('tbody');
		print <<<EOF
			<tr>
				<td class="td27" colspan="2">$lang[members_ban_clear_content]:</td>
			</tr>
			<tr>
				<td colspan="2">
					<ul class="dblist" onmouseover="altStyle(this);">
						<li style="width: 100%;"><input type="checkbox" name="chkall" onclick="checkAll('prefix', this.form, 'clear', 'chkall', true)" class="checkbox">&nbsp;$lang[select_all]</li>
						<li style="width: 8%;"><input type="checkbox" value="post" name="clear[post]" class="checkbox">&nbsp;$lang[members_ban_delpost]</li>
						<li style="width: 8%;"><input type="checkbox" value="follow" name="clear[follow]" class="checkbox">&nbsp;$lang[members_ban_delfollow]</li>
						<li style="width: 8%;"><input type="checkbox" value="postcomment" name="clear[postcomment]" class="checkbox">&nbsp;$lang[members_ban_postcomment]</li>
						<li style="width: 8%;"><input type="checkbox" value="doing" name="clear[doing]" class="checkbox">&nbsp;$lang[members_ban_deldoing]</li>
						<li style="width: 8%;"><input type="checkbox" value="blog" name="clear[blog]" class="checkbox">&nbsp;$lang[members_ban_delblog]</li>
						<li style="width: 8%;"><input type="checkbox" value="album" name="clear[album]" class="checkbox">&nbsp;$lang[members_ban_delalbum]</li>
						<li style="width: 8%;"><input type="checkbox" value="share" name="clear[share]" class="checkbox">&nbsp;$lang[members_ban_delshare]</li>
						<li style="width: 8%;"><input type="checkbox" value="avatar" name="clear[avatar]" class="checkbox">&nbsp;$lang[members_ban_delavatar]</li>
						<li style="width: 8%;"><input type="checkbox" value="comment" name="clear[comment]" class="checkbox">&nbsp;$lang[members_ban_delcomment]</li>
						<li style="width: 8%;"><input type="checkbox" value="others" name="clear[others]" class="checkbox">&nbsp;$lang[members_ban_delothers]</li>
						<li style="width: 8%;"><input type="checkbox" value="profile" name="clear[profile]" class="checkbox">&nbsp;$lang[members_ban_delprofile]</li>
					</ul>
				</td>
			</tr>
EOF;

		showsetting('members_ban_reason', 'reason', '', 'textarea');
		showsubmit('bansubmit');
		showtablefooter();
		showformfooter();
		$basescript = ADMINSCRIPT;
		print <<<EOF
			<script type="text/javascript">
				var oldbanusername = '$member[username]';
				function showcrimebtn(obj) {
					if(oldbanusername == obj.value) {
						return;
					}
					oldbanusername = obj.value;
					$('crimebtn').style.display = '';
					if($('member_status')) {
						$('member_status').style.display = 'none';
					}
				}
				function getcrimerecord() {
					if($('banusername').value) {
						window.location.href = '$basescript?action=members&operation=ban&username=' + $('banusername').value;
					}
				}
			</script>
EOF;

	} else {

		if(empty($member)) {
			cpmsg('members_edit_nonexistence');
		}

		$setarr = array();
		$reason = trim($_GET['reason']);
		if(!$reason && ($_G['group']['reasonpm'] == 1 || $_G['group']['reasonpm'] == 3)) {
			cpmsg('members_edit_reason_invalid', '', 'error');
		}
		$my_data = array();
		$mylogtype = '';
		if(in_array($_GET['bannew'], array('post', 'visit', 'status'))) {
			$my_data = array('uid' => $member['uid']);
			if($_GET['delpost']) {
				$my_data['otherid'] = 1;
			}
			$mylogtype = 'banuser';
		} elseif($member['groupid'] == 4 || $member['groupid'] == 5 || $member['status'] == '-1') {
			$my_data = array('uid' => $member['uid']);
			$mylogtype = 'unbanuser';
		}
		if($_GET['bannew'] == 'post' || $_GET['bannew'] == 'visit') {
			$groupidnew = $_GET['bannew'] == 'post' ? 4 : 5;
			$_GET['banexpirynew'] = !empty($_GET['banexpirynew']) ? TIMESTAMP + $_GET['banexpirynew'] * 86400 : 0;
			$_GET['banexpirynew'] = $_GET['banexpirynew'] > TIMESTAMP ? $_GET['banexpirynew'] : 0;
			if($_GET['banexpirynew']) {
				if($member['groupid'] == 4 || $member['groupid'] == 5) {
					$member['groupterms']['main']['time'] = $_GET['banexpirynew'];
					if (empty($member['groupterms']['main']['groupid'])) {
						$groupnew = C::t('common_usergroup')->fetch_by_credits($member['credits']);
						$member['groupterms']['main']['groupid'] = $groupnew['groupid'];
					}
					if (!isset($member['groupterms']['main']['adminid'])) {
						$member['groupterms']['main']['adminid'] = $member['adminid'];
					}
				}else{
					$member['groupterms']['main'] = array('time' => $_GET['banexpirynew'], 'adminid' => $member['adminid'], 'groupid' => $member['groupid']);
				}
				$member['groupterms']['ext'][$groupidnew] = $_GET['banexpirynew'];
				$setarr['groupexpiry'] = groupexpiry($member['groupterms']);
			} else {
				$setarr['groupexpiry'] = 0;
			}
			$adminidnew = -1;
			$my_data['expiry'] = groupexpiry($member['groupterms']);
			$postcomment_cache_pid = array();
			foreach(C::t('forum_postcomment')->fetch_all_by_authorid($member['uid']) as $postcomment) {
				$postcomment_cache_pid[$postcomment['pid']] = $postcomment['pid'];
			}
			C::t('forum_postcomment')->delete_by_authorid($member['uid'], false, true);
			if($postcomment_cache_pid) {
				C::t('forum_postcache')->delete($postcomment_cache_pid);
			}
			if(in_array($member['adminid'], array(0, -1))) {
				$member_status = C::t('common_member_status')->fetch($member['uid']);
			}
		} elseif($member['groupid'] == 4 || $member['groupid'] == 5) {
			if(!empty($member['groupterms']['main']['groupid'])) {
				$groupidnew = $member['groupterms']['main']['groupid'];
				$adminidnew = $member['groupterms']['main']['adminid'];
				unset($member['groupterms']['main']);
				unset($member['groupterms']['ext'][$member['groupid']]);
				$setarr['groupexpiry'] = groupexpiry($member['groupterms']);
			}
			$groupnew = C::t('common_usergroup')->fetch_by_credits($member['credits']);
			$groupidnew = $groupnew['groupid'];
			$adminidnew = 0;
		} else {
			$update = false;
			$groupidnew = $member['groupid'];
			$adminidnew = $member['adminid'];
			if(in_array('avatar', $_GET['clear'])) {
				$setarr['avatarstatus'] = 0;
				loaducenter();
				uc_user_deleteavatar($member['uid']);
			}
		}

		$setarr['adminid'] = $adminidnew;
		$setarr['groupid'] = $groupidnew;
		$setarr['status'] = $_GET['bannew'] == 'status' ? -1 : 0;
		C::t('common_member'.$tableext)->update($member['uid'], $setarr);

		if($_G['group']['allowbanuser'] && (DB::affected_rows())) {
			banlog($member['username'], $member['groupid'], $groupidnew, $_GET['banexpirynew'], $reason, $_GET['bannew'] == 'status' ? -1 : 0);
		}

		C::t('common_member_field_forum'.$tableext)->update($member['uid'],array('groupterms' => ($member['groupterms'] ? serialize($member['groupterms']) : '')));

		$crimeaction = $noticekey = '';
		include_once libfile('function/member');
		if($_GET['bannew'] == 'post') {
			$crimeaction = 'crime_banspeak';
			$noticekey = 'member_ban_speak';
			$from_idtype = 'banspeak';
		} elseif($_GET['bannew'] == 'visit') {
			$crimeaction = 'crime_banvisit';
			$noticekey = 'member_ban_visit';
			$from_idtype = 'banvisit';
		} elseif($_GET['bannew'] == 'status') {
			$crimeaction = 'crime_banstatus';
			$noticekey = 'member_ban_status';
			$from_idtype = 'banstatus';
		}
		if($crimeaction) {
			crime('recordaction', $member['uid'], $crimeaction, lang('forum/misc', 'crime_reason', array('reason' => $reason)));
		}
		if($noticekey) {
			$notearr = array(
				'user' => "<a href=\"home.php?mod=space&uid=$_G[uid]\">$_G[username]</a>",
				'day' => intval($_POST['banexpirynew']),
				'reason' => $reason,
				'from_id' => 0,
				'from_idtype' => $from_idtype
			);
			notification_add($member['uid'], 'system', $noticekey, $notearr, 1);
		}

		if($_G['adminid'] == 1 && !empty($_GET['clear']) && is_array($_GET['clear'])) {
			require_once libfile('function/delete');
			$membercount = array();
			if(in_array('post', $_GET['clear'])) {
				if($member['uid']) {
					require_once libfile('function/post');

					$tidsdelete = array();
					loadcache('posttableids');
					$posttables = empty($_G['cache']['posttableids']) ? array(0) : $_G['cache']['posttableids'];
					foreach($posttables as $posttableid) {
						$pidsthread = $pidsdelete = array();
						$postlist = C::t('forum_post')->fetch_all_by_authorid($posttableid, $member['uid'], false);
						if($postlist) {
							foreach($postlist as $post) {
								$prune['forums'][] = $post['fid'];
								$prune['thread'][$post['tid']]++;
								if($post['first']) {
									$tidsdelete[] = $post['tid'];
								}
								$pidsdelete[] = $post['pid'];
								$pidsthread[$post['pid']] = $post['tid'];
							}
							foreach($pidsdelete as $key=>$pid) {
								if(in_array($pidsthread[$pid], $tidsdelete)) {
									unset($pidsdelete[$key]);
									unset($prune['thread'][$pidsthread[$pid]]);
									updatemodlog($pidsthread[$pid], 'DEL');
								} else {
									updatemodlog($pidsthread[$pid], 'DLP');
								}
							}
						}
						deletepost($pidsdelete, 'pid', false, $posttableid, true);
					}
					unset($postlist);
					if($tidsdelete) {
						deletethread($tidsdelete, true, true, true);
					}
					if(!empty($prune)) {
						foreach($prune['thread'] as $tid => $decrease) {
							updatethreadcount($tid);
						}
						foreach(array_unique($prune['forums']) as $fid) {
						}
					}

					if($_G['setting']['globalstick']) {
						updatecache('globalstick');
					}
				}
				$membercount['posts'] = 0;
				$membercount['threads'] = 0;
			}
			if(in_array('follow', $_GET['clear'])) {
				C::t('home_follow_feed')->delete_by_uid($member['uid']);
				$membercount['feeds'] = 0;
			}
			if(in_array('blog', $_GET['clear'])) {
				$blogids = array();
				$query = C::t('home_blog')->fetch_blogid_by_uid($member['uid']);
				foreach($query as $value) {
					$blogids[] = $value['blogid'];
				}
				if(!empty($blogids)) {
					C::t('common_moderate')->delete($blogids, 'blogid');
				}
				C::t('home_blog')->delete_by_uid($member['uid']);
				C::t('home_blogfield')->delete_by_uid($member['uid']);
				C::t('home_feed')->delete_by_uid_idtype($member['uid'], 'blogid');

				$membercount['blogs'] = 0;
			}
			if(in_array('album', $_GET['clear'])) {
				C::t('home_album')->delete_by_uid($member['uid']);
				$picids = array();
				$query = C::t('home_pic')->fetch_all_by_uid($member['uid']);
				foreach($query as $value) {
					$picids[] = $value['picid'];
					deletepicfiles($value);
				}
				if(!empty($picids)) {
					C::t('common_moderate')->delete($picids, 'picid');
				}
				C::t('home_pic')->delete_by_uid($member['uid']);
				C::t('home_feed')->delete_by_uid_idtype($member['uid'], 'albumid');

				$membercount['albums'] = 0;
			}
			if(in_array('share', $_GET['clear'])) {
				$shareids = array();
				foreach(C::t('home_share')->fetch_all_by_uid($member['uid']) as $value) {
					$shareids[] = $value['sid'];
				}
				if(!empty($shareids)) {
					C::t('common_moderate')->delete($shareids, 'sid');
				}
				C::t('home_share')->delete_by_uid($member['uid']);
				C::t('home_feed')->delete_by_uid_idtype($member['uid'], 'sid');

				$membercount['sharings'] = 0;
			}

			if(in_array('doing', $_GET['clear'])) {
				$doids = array();
				$query = C::t('home_doing')->fetch_all_by_uid_doid(array($member['uid']));
				foreach ($query as $value) {
					$doids[$value['doid']] = $value['doid'];
				}
				if(!empty($doids)) {
					C::t('common_moderate')->delete($doids, 'doid');
				}
				C::t('home_doing')->delete_by_uid($member['uid']);
				C::t('common_member_field_home')->update($member['uid'], array('recentnote' => '', 'spacenote' => ''));

				C::t('home_docomment')->delete_by_doid_uid(($doids ? $doids : null), $member['uid']);
				C::t('home_feed')->delete_by_uid_idtype($member['uid'], 'doid');

				$membercount['doings'] = 0;
			}
			if(in_array('comment', $_GET['clear'])) {
				$delcids = array();
				$query = C::t('home_comment')->fetch_all_by_uid($member['uid'], 0, 1);
				foreach($query as $value) {
					$key = $value['idtype'].'_cid';
					$delcids[$key] = $value['cid'];
				}
				if(!empty($delcids)) {
					foreach($delcids as $key => $ids) {
						C::t('common_moderate')->delete($ids, $key);
					}
				}
				C::t('home_comment')->delete_by_uid_idtype($member['uid']);
			}
			if(in_array('postcomment', $_GET['clear'])) {
				$postcomment_cache_pid = array();
				foreach(C::t('forum_postcomment')->fetch_all_by_authorid($member['uid']) as $postcomment) {
					$postcomment_cache_pid[$postcomment['pid']] = $postcomment['pid'];
				}
				C::t('forum_postcomment')->delete_by_authorid($member['uid']);
				if($postcomment_cache_pid) {
					C::t('forum_postcache')->delete($postcomment_cache_pid);
				}
			}

			if(in_array('profile', $_GET['clear'])) {
				C::t('common_member_profile'.$tableext)->delete($member['uid']);
				C::t('common_member_profile'.$tableext)->insert(array('uid' => $member['uid']));
				C::t('common_member_field_forum'.$tableext)->update($member['uid'], array('customstatus' => '', 'sightml' => ''));
				C::t('common_member_field_home'.$tableext)->update($member['uid'], array('spacename' => '', 'spacedescription' => ''));
			}

			if(in_array('others', $_GET['clear'])) {
				C::t('home_clickuser')->delete_by_uid($member['uid']);
				C::t('home_visitor')->delete_by_uid_or_vuid($member['uid']);
				C::t('home_follow')->delete_by_uid($member['uid']);
				C::t('home_follow')->delete_by_followuid($member['uid']);
				C::t('home_friend')->delete_by_uid_fuid($member['uid']);
				C::t('home_friend_request')->delete_by_uid_or_fuid($member['uid']);
				C::t('home_feed')->delete_by_uid($member['uid']);
				C::t('home_notification')->delete_by_uid($member['uid']);
				C::t('home_poke')->delete_by_uid_or_fromuid($member['uid']);
				C::t('home_pokearchive')->delete_by_uid_or_fromuid($member['uid']);
				C::t('forum_promotion')->delete_by_uid($member['uid']);
			}

			if($membercount) {
				DB::update('common_member_count'.$tableext, $membercount, "uid='$member[uid]'");
			}

		}

		cpmsg('members_edit_succeed', 'action=members&operation=ban&uid='.$member['uid'], 'succeed');

	}

} elseif($operation == 'access') {

	require_once libfile('function/forumlist');
	$forumlist = '<SELECT name="addfid">'.forumselect(FALSE, 0, 0, TRUE).'</select>';

	loadcache('forums');

	if(!submitcheck('accesssubmit')) {

		shownav('user', 'members_access_edit');
		showsubmenu('members_access_edit');
		showtips('members_access_tips');
		showtableheader(cplang('members_access_now').' - '.$member['username'], 'nobottom fixpadding');
		showsubtitle(array('forum', 'members_access_view', 'members_access_post', 'members_access_reply', 'members_access_getattach', 'members_access_getimage', 'members_access_postattach', 'members_access_postimage', 'members_access_adminuser', 'members_access_dateline'));


		$accessmasks = C::t('forum_access')->fetch_all_by_uid($_GET['uid']);
		foreach ($accessmasks as $id => $access) {
			$adminuser = C::t('common_member'.$tableext)->fetch($access['adminuser']);
			$access['dateline'] = $access['dateline'] ? dgmdate($access['dateline']) : '';
			$forum = $_G['cache']['forums'][$id];
			showtablerow('', '', array(
					($forum['type'] == 'forum' ? '' : '|-----')."&nbsp;<a href=\"".ADMINSCRIPT."?action=forums&operation=edit&fid=$forum[fid]&anchor=perm\">$forum[name]</a>",
					accessimg($access['allowview']),
					accessimg($access['allowpost']),
					accessimg($access['allowreply']),
					accessimg($access['allowgetattach']),
					accessimg($access['allowgetimage']),
					accessimg($access['allowpostattach']),
					accessimg($access['allowpostimage']),
					$adminuser['username'],
					$access['dateline'],
			));
		}

		if(empty($accessmasks)) {
			showtablerow('', '', array(
					'-',
					'-',
					'-',
					'-',
					'-',
					'-',
					'-',
					'-',
					'-',
					'-',
			));
		}

		showtablefooter();
		showformheader("members&operation=access&uid={$_GET['uid']}");
		showtableheader(cplang('members_access_add'), 'notop fixpadding');
		showsetting('members_access_add_forum', '', '', $forumlist);
		foreach(array('view', 'post', 'reply', 'getattach', 'getimage', 'postattach', 'postimage') as $perm) {
			showsetting('members_access_add_'.$perm, array('allow'.$perm.'new', array(
				array(0, cplang('default')),
				array(1, cplang('members_access_allowed')),
				array(-1, cplang('members_access_disallowed')),
			), TRUE), 0, 'mradio');
		}
		showsubmit('accesssubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$addfid = intval($_GET['addfid']);
		if($addfid && $_G['cache']['forums'][$addfid]) {
			$allowviewnew = !$_GET['allowviewnew'] ? 0 : ($_GET['allowviewnew'] > 0 ? 1 : -1);
			$allowpostnew = !$_GET['allowpostnew'] ? 0 : ($_GET['allowpostnew'] > 0 ? 1 : -1);
			$allowreplynew = !$_GET['allowreplynew'] ? 0 : ($_GET['allowreplynew'] > 0 ? 1 : -1);
			$allowgetattachnew = !$_GET['allowgetattachnew'] ? 0 : ($_GET['allowgetattachnew'] > 0 ? 1 : -1);
			$allowgetimagenew = !$_GET['allowgetimagenew'] ? 0 : ($_GET['allowgetimagenew'] > 0 ? 1 : -1);
			$allowpostattachnew = !$_GET['allowpostattachnew'] ? 0 : ($_GET['allowpostattachnew'] > 0 ? 1 : -1);
			$allowpostimagenew = !$_GET['allowpostimagenew'] ? 0 : ($_GET['allowpostimagenew'] > 0 ? 1 : -1);

			if($allowviewnew == -1) {
				$allowpostnew = $allowreplynew = $allowgetattachnew = $allowgetimagenew = $allowpostattachnew = $allowpostimagenew = -1;
			} elseif($allowpostnew == 1 || $allowreplynew == 1 || $allowgetattachnew == 1 || $allowgetimagenew == 1 || $allowpostattachnew == 1 || $allowpostimagenew == 1) {
				$allowviewnew = 1;
			}

			if(!$allowviewnew && !$allowpostnew && !$allowreplynew && !$allowgetattachnew && !$allowgetimagenew && !$allowpostattachnew && !$allowpostimagenew) {
				C::t('forum_access')->delete_by_fid($addfid, $_GET['uid']);
				if(!C::t('forum_access')->count_by_uid($_GET['uid'])) {
					C::t('common_member'.$tableext)->update($_GET['uid'], array('accessmasks'=>0));
				}
			} else {
				$data = array('uid' => $_GET['uid'], 'fid' => $addfid, 'allowview' => $allowviewnew, 'allowpost' => $allowpostnew, 'allowreply' => $allowreplynew, 'allowgetattach' => $allowgetattachnew, 'allowgetimage' => $allowgetimagenew, 'allowpostattach' => $allowpostattachnew, 'allowpostimage' => $allowpostimagenew, 'adminuser' => $_G['uid'], 'dateline' => $_G['timestamp']);
				C::t('forum_access')->insert($data, 0, 1);
				C::t('common_member'.$tableext)->update($_GET['uid'], array('accessmasks'=>1));
			}
			updatecache('forums');

		}
		cpmsg('members_access_succeed', 'action=members&operation=access&uid='.$_GET['uid'], 'succeed');

	}

} elseif($operation == 'edit') {
	echo '<script type="text/javascript" src="static/js/home.js"></script>';
	$uid = $member['uid'];
	if(!empty($_G['setting']['connect']['allow']) && $do == 'bindlog') {
		$member = array_merge($member, C::t('#qqconnect#common_member_connect')->fetch($uid));
		showsubmenu("$lang[members_edit] - $member[username]", array(
			array('connect_member_info', 'members&operation=edit&uid='.$uid,  0),
			array('connect_member_bindlog', 'members&operation=edit&do=bindlog&uid='.$uid,  1),
		));
		if($member['conopenid']) {
			showtableheader();
			showtitle('connect_member_bindlog_uin');
			showsubtitle(array('connect_member_bindlog_username', 'connect_member_bindlog_date', 'connect_member_bindlog_type'));
			$bindlogs = $bindloguids = $usernames = array();
			foreach(C::t('#qqconnect#connect_memberbindlog')->fetch_all_by_openids($member['conopenid']) as $bindlog) {
				$bindlogs[$bindlog['dateline']] = $bindlog;
				$bindloguids[] = $bindlog['uid'];
			}
			$usernames = C::t('common_member')->fetch_all_username_by_uid($bindloguids);
			foreach($bindlogs as $k => $v) {
				showtablerow('', array(), array(
					$usernames[$v['uid']],
					dgmdate($k),
					cplang('connect_member_bindlog_type_'.$v['type']),
				));
			}
			showtablefooter();
		}

		showtableheader();
		showtitle('connect_member_bindlog_uid');
		showsubtitle(array('connect_member_bindlog_date', 'connect_member_bindlog_type'));
		foreach(C::t('#qqconnect#connect_memberbindlog')->fetch_all_by_uids($member['uid']) as $bindlog) {
			showtablerow('', array(), array(
				dgmdate($bindlog['dateline']),
				cplang('connect_member_bindlog_type_'.$bindlog['type']),
			));
		}
		showtablefooter();
		exit;
	}
	$member = array_merge($member, C::t('common_member_field_forum'.$tableext)->fetch($uid),
			C::t('common_member_field_home'.$tableext)->fetch($uid),
			C::t('common_member_count'.$tableext)->fetch($uid),
			C::t('common_member_status'.$tableext)->fetch($uid),
			C::t('common_member_profile'.$tableext)->fetch($uid),
			C::t('common_usergroup')->fetch($member['groupid']),
			C::t('common_usergroup_field')->fetch($member['groupid']));
	if(!empty($_G['setting']['connect']['allow'])) {
		$member = array_merge($member, C::t('#qqconnect#common_member_connect')->fetch($uid));
		$uin = C::t('common_uin_black')->fetch_by_uid($uid);
		$member = array_merge($member, array('uinblack'=>$uin['uin']));
	}
	loadcache(array('profilesetting'));
	$fields = array();
	foreach($_G['cache']['profilesetting'] as $fieldid=>$field) {
		if($field['available']) {
			$_G['cache']['profilesetting'][$fieldid]['unchangeable'] = 0;
			$fields[$fieldid] = $field['title'];
		}
	}

	if(!submitcheck('editsubmit')) {

		require_once libfile('function/editor');

		$styleselect = "<select name=\"styleidnew\">\n<option value=\"\">$lang[use_default]</option>";
		foreach(C::t('common_style')->fetch_all_data() as $style) {
			$styleselect .= "<option value=\"$style[styleid]\" ".($style['styleid'] == $member['styleid'] ? 'selected="selected"' : '').">$style[name]</option>\n";
		}
		$styleselect .= '</select>';

		$tfcheck = array($member['timeformat'] => 'checked');
		$gendercheck = array($member['gender'] => 'checked');
		$pscheck = array($member['pmsound'] => 'checked');

		$member['regdate'] = dgmdate($member['regdate'], 'Y-n-j h:i A');
		$member['lastvisit'] = dgmdate($member['lastvisit'], 'Y-n-j h:i A');

		$member['bio'] = html2bbcode($member['bio']);
		$member['signature'] = html2bbcode($member['sightml']);

		shownav('user', 'members_edit');
		showsubmenu("$lang[members_edit] - $member[username]", array(
			array('connect_member_info', 'members&operation=edit&uid='.$uid,  1),
			!empty($_G['setting']['connect']['allow']) ? array('connect_member_bindlog', 'members&operation=edit&do=bindlog&uid='.$uid,  0) : array(),
		));
		showformheader("members&operation=edit&uid=$uid", 'enctype');
		showtableheader();
		$status = array($member['status'] => ' checked');
		$freeze = array($member['freeze'] => ' checked');
		showsetting('members_edit_username', '', '', ($_G['setting']['connect']['allow'] && $member['conisbind'] ? ' <img class="vmiddle" src="static/image/common/connect_qq.gif" />' : '').' '.$member['username']);
		showsetting('members_edit_avatar', '', '', ' <img src="'.avatar($uid, 'middle', true, false, true).'?random='.random(2).'" onerror="this.onerror=null;this.src=\''.$_G['setting']['ucenterurl'].'/images/noavatar_middle.gif\'" /><br /><br /><input name="clearavatar" class="checkbox" type="checkbox" value="1" /> '.$lang['members_edit_avatar_clear']);
		$hrefext = "&detail=1&users=$member[username]&searchsubmit=1&perpage=50&fromumanage=1";
		showsetting('members_edit_statistics', '', '', "<a href=\"".ADMINSCRIPT."?action=prune$hrefext\" class=\"act\">$lang[posts]($member[posts])</a>".
				"<a href=\"".ADMINSCRIPT."?action=doing$hrefext\" class=\"act\">$lang[doings]($member[doings])</a>".
				"<a href=\"".ADMINSCRIPT."?action=blog$hrefext\" class=\"act\">$lang[blogs]($member[blogs])</a>".
				"<a href=\"".ADMINSCRIPT."?action=album$hrefext\" class=\"act\">$lang[albums]($member[albums])</a>".
				"<a href=\"".ADMINSCRIPT."?action=share$hrefext\" class=\"act\">$lang[shares]($member[sharings])</a> <br>&nbsp;$lang[setting_styles_viewthread_userinfo_oltime]: $member[oltime]$lang[hourtime]");
		showsetting('members_edit_password', 'passwordnew', '', 'text');
		if(!empty($_G['setting']['connect']['allow']) && (!empty($member['conopenid']) || !empty($member['uinblack']))) {
			if($member['conisbind'] && !$member['conisregister']) {
				showsetting('members_edit_unbind', 'connectunbind', 0, 'radio');
			}
			showsetting('members_edit_uinblack', 'uinblack', $member['uinblack'], 'radio', '', 0, cplang('members_edit_uinblack_comment').($member['conisregister'] ? cplang('members_edit_uinblack_notice') : ''));
		}
		showsetting('members_edit_clearquestion', 'clearquestion', 0, 'radio');
		showsetting('members_edit_status', 'statusnew', $member['status'], 'radio');
		showsetting('members_edit_freeze', array('freezenew', array(
			array(0, $lang['members_edit_freeze_false']),
			array(1, $lang['members_edit_freeze_password']),
			array(-1, $lang['members_edit_freeze_admincp']),
			array(2, $lang['members_edit_freeze_email']))), $member['freeze'], 'mradio');
		showsetting('members_edit_email', 'emailnew', $member['email'], 'text');
		showsetting('members_edit_email_emailstatus', 'emailstatusnew', $member['emailstatus'], 'radio');
		showsetting('members_edit_posts', 'postsnew', $member['posts'], 'text');
		showsetting('members_edit_digestposts', 'digestpostsnew', $member['digestposts'], 'text');
		showsetting('members_edit_regip', 'regipnew', $member['regip'], 'text');
		showsetting('members_edit_regdate', 'regdatenew', $member['regdate'], 'text');
		showsetting('members_edit_lastvisit', 'lastvisitnew', $member['lastvisit'], 'text');
		showsetting('members_edit_lastip', 'lastipnew', $member['lastip'], 'text');
		showsetting('members_edit_addsize', 'addsizenew', $member['addsize'], 'text');
		showsetting('members_edit_addfriend', 'addfriendnew', $member['addfriend'], 'text');

		showsetting('members_edit_timeoffset', 'timeoffsetnew', $member['timeoffset'], 'text');
		showsetting('members_edit_invisible', 'invisiblenew', $member['invisible'], 'radio');

		showtitle('members_edit_option');
		showsetting('members_edit_cstatus', 'cstatusnew', $member['customstatus'], 'text');
		showsetting('members_edit_signature', 'signaturenew', $member['signature'], 'textarea');

		if($fields) {
			showtitle('members_profile');
			include_once libfile('function/profile');
			foreach($fields as $fieldid=>$fieldtitle) {
				$html = profile_setting($fieldid, $member);
				if($html) {
					showsetting($fieldtitle, '', '', $html);
				}
			}
		}

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		loaducenter();
		require_once libfile('function/discuzcode');

		$questionid = $_GET['clearquestion'] ? 0 : '';
		$ucresult = uc_user_edit(addslashes($member['username']), $_GET['passwordnew'], $_GET['passwordnew'], addslashes(strtolower(trim($_GET['emailnew']))), 1, $questionid);
		if($ucresult < 0) {
			if($ucresult == -4) {
				cpmsg('members_email_illegal', '', 'error');
			} elseif($ucresult == -5) {
				cpmsg('members_email_domain_illegal', '', 'error');
			} elseif($ucresult == -6) {
				cpmsg('members_email_duplicate', '', 'error');
			}
		}

		if($_GET['clearavatar']) {
			C::t('common_member'.$tableext)->update($_GET['uid'], array('avatarstatus'=>0));
			uc_user_deleteavatar($uid);
		}

		$creditsnew = intval($creditsnew);

		$regdatenew = strtotime($_GET['regdatenew']);
		$lastvisitnew = strtotime($_GET['lastvisitnew']);

		$secquesadd = $_GET['clearquestion'] ? ", secques=''" : '';

		$signaturenew = censor($_GET['signaturenew']);
		$sigstatusnew = $signaturenew ? 1 : 0;
		$sightmlnew = discuzcode($signaturenew, 1, 0, 0, 0, ($member['allowsigbbcode'] ? ($member['allowcusbbcode'] ? 2 : 1) : 0), $member['allowsigimgcode'], 0);

		$oltimenew = round($_GET['totalnew'] / 60);

		$fieldadd = '';
		$fieldarr = array();
		include_once libfile('function/profile');
		foreach($_POST as $field_key=>$field_val) {
			if(isset($fields[$field_key]) && (profile_check($field_key, $field_val) || $_G['adminid'] == 1)) {
				$fieldarr[$field_key] = $field_val;
			}
		}
		if($_GET['deletefile'] && is_array($_GET['deletefile'])) {
			foreach($_GET['deletefile'] as $key => $value) {
				if(isset($fields[$key]) && $_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
					@unlink(getglobal('setting/attachdir').'./profile/'.$member[$key]);
					$fieldarr[$key] = '';
				}
			}
		}

		if($_FILES) {
			$upload = new discuz_upload();

			foreach($_FILES as $key => $file) {
				if(isset($fields[$key])) {
					$upload->init($file, 'profile');
					$attach = $upload->attach;

					if(!$upload->error()) {
						$upload->save();

						if(!$upload->get_image_info($attach['target'])) {
							@unlink($attach['target']);
							continue;
						}
						$attach['attachment'] = dhtmlspecialchars(trim($attach['attachment']));
						@unlink(getglobal('setting/attachdir').'./profile/'.$member[$key]);
						$fieldarr[$key] = $attach['attachment'];
					}
				}
			}
		}

		$memberupdate = array();
		if($ucresult >= 0) {
			$memberupdate['email'] = strtolower(trim($_GET['emailnew']));
		}
		if($ucresult >= 0 && !empty($_GET['passwordnew'])) {
			$memberupdate['password'] = md5(random(10));
		}
		$addsize = intval($_GET['addsizenew']);
		$addfriend = intval($_GET['addfriendnew']);
		$status = intval($_GET['statusnew']) ? -1 : 0;
		if($status == -1 && $member['groupid'] == 1) {
			cpmsg('members_edit_lock', '', 'error', array('grouptitle' => $member['grouptitle'], 'uid' => $member['uid']));
		}
		$freeze = in_array($_GET['freezenew'], array(-1, 0, 1, 2)) ? $_GET['freezenew'] : 0;
		$emailstatusnew = intval($_GET['emailstatusnew']);
		if(!empty($_G['setting']['connect']['allow'])) {
			if($member['uinblack'] && empty($_GET['uinblack'])) {
				C::t('common_uin_black')->delete($member['uinblack']);
				updatecache('connect_blacklist');
			} elseif(!$member['uinblack'] && !empty($_GET['uinblack'])) {
				connectunbind($member);
				C::t('common_uin_black')->insert(array('uin' => $member['conopenid'], 'uid' => $uid, 'dateline' => TIMESTAMP), false, true);
				updatecache('connect_blacklist');
			}
			if($member['conisbind'] && !$member['conisregister'] && !empty($_GET['connectunbind'])) {
				connectunbind($member);
			}
		}
		$memberupdate = array_merge($memberupdate, array('regdate'=>$regdatenew, 'emailstatus'=>$emailstatusnew, 'status'=>$status, 'freeze'=>$freeze, 'timeoffset'=>$_GET['timeoffsetnew']));
		C::t('common_member'.$tableext)->update($uid, $memberupdate);
		C::t('common_member_field_home'.$tableext)->update($uid, array('addsize' => $addsize, 'addfriend' => $addfriend));
		C::t('common_member_count'.$tableext)->update($uid, array('posts' => $_GET['postsnew'], 'digestposts' => $_GET['digestpostsnew']));
		C::t('common_member_status'.$tableext)->update($uid, array('regip' => $_GET['regipnew'], 'lastvisit' => $lastvisitnew, 'lastip' => $_GET['lastipnew'], 'invisible' => $_GET['invisiblenew']));
		C::t('common_member_field_forum'.$tableext)->update($uid, array('customstatus' => $_GET['cstatusnew'], 'sightml' => $sightmlnew));
		if(!empty($fieldarr)) {
			C::t('common_member_profile'.$tableext)->update($uid, $fieldarr);
		}
		if($freeze == 0 && C::t('common_member_validate')->fetch($uid)) {
			C::t('common_member_validate')->delete($uid);
		}

		manyoulog('user', $uid, 'update');
		cpmsg('members_edit_succeed', 'action=members&operation=edit&uid='.$uid, 'succeed');

	}

} elseif($operation == 'ipban') {

	if(!$_GET['ipact']) {
		if(!submitcheck('ipbansubmit')) {

			require_once libfile('function/misc');

			$iptoban = explode('.', getgpc('ip'));

			$ipbanned = '';
			foreach(C::t('common_banned')->fetch_all_order_dateline() as $banned) {
				for($i = 1; $i <= 4; $i++) {
					if($banned["ip$i"] == -1) {
						$banned["ip$i"] = '*';
					}
				}
				$disabled = $_G['adminid'] != 1 && $banned['admin'] != $_G['member']['username'] ? 'disabled' : '';
				$banned['dateline'] = dgmdate($banned['dateline'], 'Y-m-d');
				$banned['expiration'] = dgmdate($banned['expiration'], 'Y-m-d');
				$theip = "$banned[ip1].$banned[ip2].$banned[ip3].$banned[ip4]";
				$ipbanned .= showtablerow('', array('class="td25"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$banned[id]]\" value=\"$banned[id]\" $disabled />",
					$theip,
					convertip($theip),
					$banned[admin],
					$banned[dateline],
					"<input type=\"text\" class=\"txt\" size=\"10\" name=\"expirationnew[$banned[id]]\" value=\"$banned[expiration]\" $disabled />"
				), TRUE);
			}
			shownav('user', 'nav_members_ipban');
			showsubmenu('nav_members_ipban', array(
				array('nav_members_ipban', 'members&operation=ipban', 1),
				array('nav_members_ipban_output', 'members&operation=ipban&ipact=input', 0)
			));
			showtips('members_ipban_tips');
			showformheader('members&operation=ipban');
			showtableheader();
			showsubtitle(array('', 'ip', 'members_ipban_location', 'operator', 'start_time', 'end_time'));
			echo $ipbanned;
			showtablerow('', array('', 'class="td28" colspan="3"', 'class="td28" colspan="2"'), array(
				$lang['add_new'],
				'<input type="text" class="txt" name="ip1new" value="'.$iptoban[0].'" size="3" maxlength="3">.<input type="text" class="txt" name="ip2new" value="'.$iptoban[1].'" size="3" maxlength="3">.<input type="text" class="txt" name="ip3new" value="'.$iptoban[2].'" size="3" maxlength="3">.<input type="text" class="txt" name="ip4new" value="'.$iptoban[3].'" size="3" maxlength="3">',
				$lang['validity'].': <input type="text" class="txt" name="validitynew" value="30" size="3"> '.$lang['days']
			));
			showsubmit('ipbansubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

		} else {

			if(!empty($_GET['delete'])) {
				C::t('common_banned')->delete_by_id($_GET['delete'], $_G['adminid'], $_G['username']);
			}

			if($_GET['ip1new'] != '' && $_GET['ip2new'] != '' && $_GET['ip3new'] != '' && $_GET['ip4new'] != '') {
				$own = 0;
				$ip = explode('.', $_G['clientip']);
				for($i = 1; $i <= 4; $i++) {
					if(!is_numeric($_GET['ip'.$i.'new']) || $_GET['ip'.$i.'new'] < 0) {
						if($_G['adminid'] != 1) {
							cpmsg('members_ipban_nopermission', '', 'error');
						}
						$_GET['ip'.$i.'new'] = -1;
						$own++;
					} elseif($_GET['ip'.$i.'new'] == $ip[$i - 1]) {
						$own++;
					}
					$_GET['ip'.$i.'new'] = intval($_GET['ip'.$i.'new']);
				}

				if($own == 4) {
					cpmsg('members_ipban_illegal', '', 'error');
				}

				foreach(C::t('common_banned')->fetch_all_order_dateline() as $banned) {
					$exists = 0;
					for($i = 1; $i <= 4; $i++) {
						if($banned["ip$i"] == -1) {
							$exists++;
						} elseif($banned["ip$i"] == ${"ip".$i."new"}) {
							$exists++;
						}
					}
					if($exists == 4) {
						cpmsg('members_ipban_invalid', '', 'error');
					}
				}

				$expiration = TIMESTAMP + $_GET['validitynew'] * 86400;

				C::app()->session->update_by_ipban($_GET['ip1new'], $_GET['ip2new'], $_GET['ip3new'], $_GET['ip4new']);
				$data = array(
					'ip1' => $_GET['ip1new'],
					'ip2' => $_GET['ip2new'],
					'ip3' => $_GET['ip3new'],
					'ip4' => $_GET['ip4new'],
					'admin' => $_G['username'],
					'dateline' => $_G['timestamp'],
					'expiration' => $expiration,
				);
				C::t('common_banned')->insert($data);
			}

			if(is_array($_GET['expirationnew'])) {
				foreach($_GET['expirationnew'] as $id => $expiration) {
					C::t('common_banned')->update_expiration_by_id($id, strtotime($expiration), $_G['adminid'], $_G['username']);
				}
			}

			updatecache('ipbanned');
			cpmsg('members_ipban_succeed', 'action=members&operation=ipban', 'succeed');

		}
	} elseif($_GET['ipact'] == 'input') {
		if($_G['adminid'] != 1) {
			cpmsg('members_ipban_nopermission', '', 'error');
		}
		if(!submitcheck('ipbansubmit')) {
			shownav('user', 'nav_members_ipban');
			showsubmenu('nav_members_ipban', array(
				array('nav_members_ipban', 'members&operation=ipban', 0),
				array('nav_members_ipban_output', 'members&operation=ipban&ipact=input', 1)
			));
			showtips('members_ipban_input_tips');
			showformheader('members&operation=ipban&ipact=input');
			showtableheader();
			showsetting('members_ipban_input', 'inputipbanlist', '', 'textarea');
			showsubmit('ipbansubmit', 'submit');
			showtablefooter();
			showformfooter();
		} else {
			$iplist = explode("\n", $_GET['inputipbanlist']);
			foreach($iplist as $banip) {
				if(strpos($banip, ',') !== false) {
					list($banipaddr, $expiration) = explode(',', $banip);
					$expiration = strtotime($expiration);
				} else {
					list($banipaddr, $expiration) = explode(';', $banip);
					$expiration = TIMESTAMP + ($expiration ? $expiration : 30) * 86400;
				}
				if(!trim($banipaddr)) {
					continue;
				}

				$ipnew = explode('.', $banipaddr);
				for($i = 0; $i < 4; $i++) {
					if(strpos($ipnew[$i], '*') !== false) {
						$ipnew[$i] = -1;
					} else {
						$ipnew[$i] = intval($ipnew[$i]);
					}
				}
				$checkexists = C::t('common_banned')->fetch_by_ip($ipnew[0], $ipnew[1], $ipnew[2], $ipnew[3]);
				if($checkexists) {
					continue;
				}

				C::app()->session->update_by_ipban($ipnew[0], $ipnew[1], $ipnew[2], $ipnew[3]);
				$data = array(
					'ip1' => $ipnew[0],
					'ip2' => $ipnew[1],
					'ip3' => $ipnew[2],
					'ip4' => $ipnew[3],
					'admin' => $_G['username'],
					'dateline' => $_G['timestamp'],
					'expiration' => $expiration,
				);
				C::t('common_banned')->insert($data, false, true);
			}

			updatecache('ipbanned');
			cpmsg('members_ipban_succeed', 'action=members&operation=ipban&ipact=input', 'succeed');
		}
	} elseif($_GET['ipact'] == 'output') {
		ob_end_clean();
		dheader('Cache-control: max-age=0');
		dheader('Expires: '.gmdate('D, d M Y H:i:s', TIMESTAMP - 31536000).' GMT');
		dheader('Content-Encoding: none');
		dheader('Content-Disposition: attachment; filename=IPBan.csv');
		dheader('Content-Type: text/plain');
		foreach(C::t('common_banned')->fetch_all_order_dateline() as $banned) {
			for($i = 1; $i <= 4; $i++) {
				$banned['ip'.$i] = $banned['ip'.$i] < 0 ? '*' : $banned['ip'.$i];
			}
			$banned['expiration'] = dgmdate($banned['expiration']);
			echo "$banned[ip1].$banned[ip2].$banned[ip3].$banned[ip4],$banned[expiration]\n";
		}
		define('FOOTERDISABLED' , 1);
		exit();
	}

} elseif($operation == 'profile') {

	$fieldid = $_GET['fieldid'] ? $_GET['fieldid'] : '';
	shownav('user', 'nav_members_profile');
	if($fieldid) {
		$_G['setting']['privacy'] = !empty($_G['setting']['privacy']) ? $_G['setting']['privacy'] : array();
		$_G['setting']['privacy'] = is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : dunserialize($_G['setting']['privacy']);

		$field = C::t('common_member_profile_setting')->fetch($fieldid);
		$fixedfields1 = array('uid', 'constellation', 'zodiac');
		$fixedfields2 = array('gender', 'birthday', 'birthcity', 'residecity');
		$field['isfixed1'] = in_array($fieldid, $fixedfields1);
		$field['isfixed2'] = $field['isfixed1'] || in_array($fieldid, $fixedfields2);
		$field['customable'] = preg_match('/^field[1-8]$/i', $fieldid);
		$profilegroup = C::t('common_setting')->fetch('profilegroup', true);
		$profilevalidate = array();
		include libfile('spacecp/profilevalidate', 'include');
		$field['validate'] = $field['validate'] ? $field['validate'] : ($profilevalidate[$fieldid] ? $profilevalidate[$fieldid] : '');
		if(!submitcheck('editsubmit')) {
			showsubmenu($lang['members_profile'].'-'.$field['title'], array(
				array('members_profile_list', 'members&operation=profile', 0),
				array($lang['edit'], 'members&operation=profile&fieldid='.$_GET['fieldid'], 1)
			));
			showformheader('members&operation=profile&fieldid='.$fieldid);
			showtableheader();
			if($field['customable']) {
				showsetting('members_profile_edit_name', 'title', $field['title'], 'text');
				showsetting('members_profile_edit_desc', 'description', $field['description'], 'text');
			} else {
				showsetting('members_profile_edit_name', '', '', ' '.$field['title']);
				showsetting('members_profile_edit_desc', '', '', ' '.$field['description']);
			}
			if(!$field['isfixed2']) {
				if($field['fieldid'] == 'realname') {
					showsetting('members_profile_edit_form_type', array('formtype', array(
						array('text', $lang['members_profile_edit_text'], array('valuenumber' => '', 'fieldchoices' => 'none', 'fieldvalidate'=>''))
					)), $field['formtype'], 'mradio');
				} else {
					showsetting('members_profile_edit_form_type', array('formtype', array(
							array('text', $lang['members_profile_edit_text'], array('valuenumber' => '', 'fieldchoices' => 'none', 'fieldvalidate'=>'')),
							array('textarea', $lang['members_profile_edit_textarea'], array('valuenumber' => '', 'fieldchoices' => 'none', 'fieldvalidate'=>'')),
							array('radio', $lang['members_profile_edit_radio'], array('valuenumber' => 'none', 'fieldchoices' => '', 'fieldvalidate'=>'none')),
							array('checkbox', $lang['members_profile_edit_checkbox'], array('valuenumber' => '', 'fieldchoices' => '', 'fieldvalidate'=>'none')),
							array('select', $lang['members_profile_edit_select'], array('valuenumber' => 'none', 'fieldchoices' => '', 'fieldvalidate'=>'none')),
							array('list', $lang['members_profile_edit_list'], array('valuenumber' => '', 'fieldchoices' => '')),
							array('file', $lang['members_profile_edit_file'], array('valuenumber' => '', 'fieldchoices' => 'none', 'fieldvalidate'=>'none'))
						)), $field['formtype'], 'mradio');
				}
				showtagheader('tbody', 'valuenumber', !in_array($field['formtype'], array('radio', 'select')), 'sub');
				showsetting('members_profile_edit_value_number', 'size', $field['size'], 'text');
				showtagfooter('tbody');

				showtagheader('tbody', 'fieldchoices', !in_array($field['formtype'], array('file','text', 'textarea')), 'sub');
				showsetting('members_profile_edit_choices', 'choices', $field['choices'], 'textarea');
				showtagfooter('tbody');

				showtagheader('tbody', 'fieldvalidate', in_array($field['formtype'], array('text', 'textarea')), 'sub');
				showsetting('members_profile_edit_validate', 'validate', $field['validate'], 'text');
				showtagfooter('tbody');
			}
			if(!$field['isfixed1']) {
				showsetting('members_profile_edit_available', 'available', $field['available'], 'radio');
				showsetting('members_profile_edit_unchangeable', 'unchangeable', $field['unchangeable'], 'radio');
				showsetting('members_profile_edit_needverify', 'needverify', $field['needverify'], 'radio');
				showsetting('members_profile_edit_required', 'required', $field['required'], 'radio');
			}
			showsetting('members_profile_edit_invisible', 'invisible', $field['invisible'], 'radio');
			$privacyselect = array(
				array('0', cplang('members_profile_edit_privacy_public')),
				array('1', cplang('members_profile_edit_privacy_friend')),
				array('3', cplang('members_profile_edit_privacy_secret'))
			);
			showsetting('members_profile_edit_default_privacy', array('privacy', $privacyselect), $_G['setting']['privacy']['profile'][$fieldid], 'select');
			showsetting('members_profile_edit_showincard', 'showincard', $field['showincard'], 'radio');
			showsetting('members_profile_edit_showinregister', 'showinregister', $field['showinregister'], 'radio');
			showsetting('members_profile_edit_allowsearch', 'allowsearch', $field['allowsearch'], 'radio');
			if(!empty($profilegroup)) {
				$groupstr = '';
				foreach($profilegroup as $key => $value) {
					if($value['available']) {
						if(in_array($fieldid, $value['field'])) {
							$checked = ' checked="checked" ';
							$class = ' class="checked" ';
						} else {
							$class = $checked = '';
						}
						$groupstr .= "<li $class style=\"float: left; width: 10%;\"><input type=\"checkbox\" value=\"$key\" name=\"profilegroup[$key]\" class=\"checkbox\" $checked>&nbsp;$value[title]</li>";
					}
				}
				if(!empty($groupstr)) {
					print <<<EOF
						<tr>
							<td class="td27" colspan="2">$lang[setting_profile_group]:</td>
						</tr>
						<tr>
							<td colspan="2">
								<ul class="dblist" onmouseover="altStyle(this);">
									<li style="width: 100%;"><input type="checkbox" name="chkall" onclick="checkAll('prefix', this.form, 'profilegroup')" class="checkbox">&nbsp;$lang[select_all]</li>
									$groupstr
								</ul>
							</td>
						</tr>
EOF;
				}
			}

			showsetting('members_profile_edit_display_order', 'displayorder', $field['displayorder'], 'text');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$setarr = array(
				'invisible' => intval($_POST['invisible']),
				'showincard' => intval($_POST['showincard']),
				'showinregister' => intval($_POST['showinregister']),
				'allowsearch' => intval($_POST['allowsearch']),
				'displayorder' => intval($_POST['displayorder'])
			);
			if($field['customable']) {
				$_POST['title'] = dhtmlspecialchars(trim($_POST['title']));
				if(empty($_POST['title'])) {
					cpmsg('members_profile_edit_title_empty_error', 'action=members&operation=profile&fieldid='.$fieldid, 'error');
				}
				$setarr['title'] = $_POST['title'];
				$setarr['description'] = dhtmlspecialchars(trim($_POST['description']));
			}
			if(!$field['isfixed1']) {
				$setarr['required'] = intval($_POST['required']);
				$setarr['available'] = intval($_POST['available']);
				$setarr['unchangeable'] = intval($_POST['unchangeable']);
				$setarr['needverify'] = intval($_POST['needverify']);
			}
			if(!$field['isfixed2']) {
				$setarr['formtype'] = $fieldid == 'realname' ? 'text' : strtolower(trim($_POST['formtype']));
				$setarr['size'] = intval($_POST['size']);
				if($_POST['choices']) {
					$_POST['choices'] = trim($_POST['choices']);
					$ops = explode("\n", $_POST['choices']);
					$parts = array();
					foreach ($ops as $op) {
						$parts[] = dhtmlspecialchars(trim($op));
					}
					$_POST['choices'] = implode("\n", $parts);
				}
				$setarr['choices'] = $_POST['choices'];
				if($_POST['validate'] && $_POST['validate'] != $profilevalidate[$fieldid]) {
					$setarr['validate'] = $_POST['validate'];
				} elseif(empty($_POST['validate'])) {
					$setarr['validate'] = '';
				}
			}
			C::t('common_member_profile_setting')->update($fieldid, $setarr);
			if($_GET['fieldid'] == 'birthday') {
				C::t('common_member_profile_setting')->update('birthmonth', $setarr);
				C::t('common_member_profile_setting')->update('birthyear', $setarr);
			} elseif($_GET['fieldid'] == 'birthcity') {
				C::t('common_member_profile_setting')->update('birthprovince', $setarr);
				$setarr['required'] = 0;
				C::t('common_member_profile_setting')->update('birthdist', $setarr);
				C::t('common_member_profile_setting')->update('birthcommunity', $setarr);
			} elseif($_GET['fieldid'] == 'residecity') {
				C::t('common_member_profile_setting')->update('resideprovince', $setarr);
				$setarr['required'] = 0;
				C::t('common_member_profile_setting')->update('residedist', $setarr);
				C::t('common_member_profile_setting')->update('residecommunity', $setarr);
			} elseif($_GET['fieldid'] == 'idcard') {
				C::t('common_member_profile_setting')->update('idcardtype', $setarr);
			}

			foreach($profilegroup as $type => $pgroup) {
				if(is_array($_GET['profilegroup']) && in_array($type, $_GET['profilegroup'])) {
					$profilegroup[$type]['field'][$fieldid] = $fieldid;
				} else {
					unset($profilegroup[$type]['field'][$fieldid]);
				}
			}
			C::t('common_setting')->update('profilegroup', $profilegroup);
			require_once libfile('function/cache');
			if(!isset($_G['setting']['privacy']['profile']) || $_G['setting']['privacy']['profile'][$fieldid] != $_POST['privacy']) {
				$_G['setting']['privacy']['profile'][$fieldid] = $_POST['privacy'];
				C::t('common_setting')->update('privacy', $_G['setting']['privacy']);
			}
			updatecache(array('profilesetting','fields_required', 'fields_optional', 'fields_register', 'setting'));
			include_once libfile('function/block');
			loadcache('profilesetting', true);
			blockclass_cache();
			cpmsg('members_profile_edit_succeed', 'action=members&operation=profile', 'succeed');
		}
	} else {

		$list = array();
		foreach(C::t('common_member_profile_setting')->range() as $fieldid => $value) {
			$list[$fieldid] = array(
				'title'=>$value['title'],
				'displayorder'=>$value['displayorder'],
				'available'=>$value['available'],
				'invisible'=>$value['invisible'],
				'showincard'=>$value['showincard'],
				'showinregister'=>$value['showinregister']);
		}

		unset($list['birthyear']);
		unset($list['birthmonth']);
		unset($list['birthprovince']);
		unset($list['birthdist']);
		unset($list['birthcommunity']);
		unset($list['resideprovince']);
		unset($list['residedist']);
		unset($list['residecommunity']);
		unset($list['idcardtype']);

		if(!submitcheck('ordersubmit')) {
			$_GET['anchor'] = in_array($_GET['action'], array('members', 'setting')) ? $_GET['action'] : 'members';
			$current = array($_GET['anchor'] => 1);
			$profilenav = array(
					array('members_profile_list', 'members&operation=profile', $current['members']),
					array('members_profile_group', 'setting&operation=profile', $current['setting']),
				);
			showsubmenu($lang['members_profile'], $profilenav);
			showtips('members_profile_tips');
			showformheader('members&operation=profile');
			showtableheader('', '', 'id="profiletable_header"');
			$tdstyle = array('class="td22"', 'class="td28" width="100"', 'class="td28" width="100"', 'class="td28" width="100"', 'class="td28" width="100"', 'class="td28"', 'class="td28"');
			showsubtitle(array('members_profile_edit_name', 'members_profile_edit_display_order', 'members_profile_edit_available', 'members_profile_edit_profile_view', 'members_profile_edit_card_view', 'members_profile_edit_reg_view', ''), 'header tbm', $tdstyle);
			showtablefooter();
			echo '<script type="text/javascript">floatbottom(\'profiletable_header\');</script>';
			showtableheader('members_profile', 'nobottom', 'id="porfiletable"');
			showsubtitle(array('members_profile_edit_name', 'members_profile_edit_display_order', 'members_profile_edit_available', 'members_profile_edit_profile_view', 'members_profile_edit_card_view', 'members_profile_edit_reg_view', ''), 'header', $tdstyle);
			foreach($list as $fieldid => $value) {
				$value['available'] = '<input type="checkbox" class="checkbox" name="available['.$fieldid.']" '.($value['available'] ? 'checked="checked" ' : '').'value="1">';
				$value['invisible'] = '<input type="checkbox" class="checkbox" name="invisible['.$fieldid.']" '.(!$value['invisible'] ? 'checked="checked" ' : '').'value="1">';
				$value['showincard'] = '<input type="checkbox" class="checkbox" name="showincard['.$fieldid.']" '.($value['showincard'] ? 'checked="checked" ' : '').'value="1">';
				$value['showinregister'] = '<input type="checkbox" class="checkbox" name="showinregister['.$fieldid.']" '.($value['showinregister'] ? 'checked="checked" ' : '').'value="1">';
				$value['displayorder'] = '<input type="text" name="displayorder['.$fieldid.']" value="'.$value['displayorder'].'" size="5">';
				$value['edit'] = '<a href="'.ADMINSCRIPT.'?action=members&operation=profile&fieldid='.$fieldid.'" title="" class="act">'.$lang[edit].'</a>';
				showtablerow('', array(), $value);
			}
			showsubmit('ordersubmit');
			showtablefooter();
			showformfooter();
		} else {
			foreach($_GET['displayorder'] as $fieldid => $value) {
				$setarr = array(
					'displayorder' => intval($value),
					'invisible' => intval($_GET['invisible'][$fieldid]) ? 0 : 1,
					'available' => intval($_GET['available'][$fieldid]),
					'showincard' => intval($_GET['showincard'][$fieldid]),
					'showinregister' => intval($_GET['showinregister'][$fieldid]),
				);
				C::t('common_member_profile_setting')->update($fieldid, $setarr);

				if($fieldid == 'birthday') {
					C::t('common_member_profile_setting')->update('birthmonth', $setarr);
					C::t('common_member_profile_setting')->update('birthyear', $setarr);
				} elseif($fieldid == 'birthcity') {
					C::t('common_member_profile_setting')->update('birthprovince', $setarr);
					$setarr['required'] = 0;
					C::t('common_member_profile_setting')->update('birthdist', $setarr);
					C::t('common_member_profile_setting')->update('birthcommunity', $setarr);
				} elseif($fieldid == 'residecity') {
					C::t('common_member_profile_setting')->update('resideprovince', $setarr);
					$setarr['required'] = 0;
					C::t('common_member_profile_setting')->update('residedist', $setarr);
					C::t('common_member_profile_setting')->update('residecommunity', $setarr);
				} elseif($fieldid == 'idcard') {
					C::t('common_member_profile_setting')->update('idcardtype', $setarr);
				}

			}
			require_once libfile('function/cache');
			updatecache(array('profilesetting', 'fields_required', 'fields_optional', 'fields_register', 'setting'));
			include_once libfile('function/block');
			loadcache('profilesetting', true);
			blockclass_cache();
			cpmsg('members_profile_edit_succeed', 'action=members&operation=profile', 'succeed');
		}
	}

} elseif($operation == 'stat') {

	if($_GET['do'] == 'stepstat' && $_GET['t'] > 0 && $_GET['i'] > 0) {
		$t = intval($_GET['t']);
		$i = intval($_GET['i']);
		$o = $i - 1;
		$value = C::t('common_member_stat_field')->fetch_all_by_fieldid($_GET['fieldid'], $o, 1);
		if($value) {
			$optionid = intval($value[0]['optionid']);
			$fieldvalue = $value[0]['fieldvalue'];
		} else {
			$optionid = 0;
			$fieldvalue = '';
		}
		$cnt = ($_GET['fieldid'] === 'groupid') ? C::t('common_member')->count_by_groupid($fieldvalue) : C::t('common_member_profile')->count_by_field($_GET['fieldid'], $fieldvalue);
		C::t('common_member_stat_field')->update($optionid, array('users'=>$cnt, 'updatetime'=>TIMESTAMP));
		if($i < $t) {
			cpmsg('members_stat_do_stepstat', 'action=members&operation=stat&fieldid='.$_GET['fieldid'].'&do=stepstat&t='.$t.'&i='.($i+1), '', array('t'=>$t, 'i'=>$i));
		} else {
			cpmsg('members_stat_update_data_succeed', 'action=members&operation=stat&fieldid='.$_GET['fieldid'], 'succeed');
		}
	}

	$options = array('groupid'=>cplang('usergroup'));
	$fieldids = array('gender', 'birthyear', 'birthmonth', 'constellation', 'zodiac','birthprovince', 'resideprovince');
	loadcache('profilesetting');
	foreach($_G['cache']['profilesetting'] as $fieldid=>$value) {
		if($value['formtype']=='select'||$value['formtype']=='radio'||in_array($fieldid,$fieldids)) {
			$options[$fieldid] = $value['title'];
		}
	}

	if(!empty($_GET['fieldid']) && !isset($options[$_GET['fieldid']])) {
		cpmsg('members_stat_bad_fieldid', 'action=members&operation=stat', 'error');
	}

	if(!empty($_GET['fieldid']) && $_GET['fieldid'] == 'groupid') {
		$usergroups = array();
		foreach(C::t('common_usergroup')->range() as $value) {
			$usergroups[$value['groupid']] = $value['grouptitle'];
		}
	}

	if(!submitcheck('statsubmit')) {

		shownav('user', 'nav_members_stat');
		showsubmenu('nav_members_stat');
		showtips('members_stat_tips');

		showformheader('members&operation=stat&fieldid='.$_GET['fieldid']);
		showtableheader('members_stat_options');
		$option_html = '<ul>';
		foreach($options as $key=>$value) {
			$extra_style = $_GET['fieldid'] == $key ? ' font-weight: 900;' : '';
			$option_html .= ""
				."<li style=\"float: left; width: 160px;$extra_style\">"
				. "<a href=\"".ADMINSCRIPT."?action=members&operation=stat&fieldid=$key\">$value</a>"
				. "</li>";
		}
		$option_html .= '</ul><br style="clear: both;" />';
		showtablerow('', array('colspan="5"'), array($option_html));

		if($_GET['fieldid']) {

			$list = array();
			$total = 0;
			foreach(($list = C::t('common_member_stat_field')->fetch_all_by_fieldid($_GET['fieldid'])) as $value) {
				$total += $value['users'];
			}
			for($i=0, $L=count($list); $i<$L; $i++) {
				if($total) {
					$list[$i]['percent'] = intval(10000 * $list[$i]['users'] / $total) / 100;
				} else {
					$list[$i]['percent'] = 0;
				}
				$list[$i]['width'] = $list[$i]['percent'] ? intval($list[$i]['percent'] * 2) : 1;
			}
			showtablerow('', array('colspan="4"'), array(cplang('members_stat_current_field').$options[$_GET['fieldid']].'; '.cplang('members_stat_members').$total));

			showtablerow('', array('width="200"', '', 'width="160"', 'width="160"'),array(
					cplang('members_stat_option'),
					cplang('members_stat_view'),
					cplang('members_stat_option_members'),
					cplang('members_stat_updatetime')
				));
			foreach($list as $value) {
				if($_GET['fieldid']=='groupid') {
					$value['fieldvalue'] = $usergroups[$value['fieldvalue']];
				} elseif($_GET['fieldid']=='gender') {
					$value['fieldvalue'] = lang('space', 'gender_'.$value['fieldvalue']);
				} elseif(empty($value['fieldvalue'])) {
					$value['fieldvalue'] = cplang('members_stat_null_fieldvalue');
				}
				showtablerow('', array('width="200"', '', 'width="160"', 'width="160"'),array(
					$value['fieldvalue'],
					'<div style="background-color: yellow; width: 200px; height: 20px;"><div style="background-color: red; height: 20px; width: '.$value['width'].'px;"></div></div>',
					$value['users'].' ('.$value['percent'].'%)',
					!empty($value['updatetime']) ? dgmdate($value['updatetime'], 'u') : 'N/A'
				));
			}

			showtablefooter();
			$optype_html = '<input type="radio" class="radio" name="optype" id="optype_option" value="option" /><label for="optype_option">'.cplang('members_stat_update_option').'</label>&nbsp;&nbsp;'
					.'<input type="radio" class="radio" name="optype" id="optype_data" value="data" /><label for="optype_data">'.cplang('members_stat_update_data').'</label>';
			showsubmit('statsubmit', 'submit', $optype_html);
			showformfooter();

		} else {
			showtablefooter();
			showformfooter();
		}

	} else {

		if($_POST['optype'] == 'option') {

			$options = $inserts = $hits = $deletes = array();
			foreach(C::t('common_member_stat_field')->fetch_all_by_fieldid($_GET['fieldid']) as $value) {
				$options[$value['optionid']] = $value['fieldvalue'];
				$hits[$value['optionid']] = false;
			}

			$alldata = $_GET['fieldid'] === 'groupid' ? C::t('common_member')->fetch_all_groupid() : C::t('common_member_profile')->fetch_all_field_value($_GET['fieldid']);
			foreach($alldata as $value) {
				$fieldvalue = $value[$_GET[fieldid]];
				$optionid = array_search($fieldvalue, $options);
				if($optionid) {
					$hits[$optionid] = true;
				} else {
					$inserts[] = array('fieldid'=>$_GET['fieldid'], 'fieldvalue'=>$fieldvalue);
				}
			}
			foreach ($hits as $key=>$value) {
				if($value == false) {
					$deletes[] = $key;
				}
			}
			if($deletes) {
				C::t('common_member_stat_field')->delete($deletes);

			}
			if($inserts) {
				C::t('common_member_stat_field')->insert_batch($inserts);
			}

			cpmsg('members_stat_update_option_succeed', 'action=members&operation=stat&fieldid='.$_GET['fieldid'], 'succeed');

		} elseif($_POST['optype'] == 'data') {

			if(($t = C::t('common_member_stat_field')->count_by_fieldid($_GET['fieldid'])) > 0) {
				cpmsg('members_stat_do_stepstat_prepared', 'action=members&operation=stat&fieldid='.$_GET['fieldid'].'&do=stepstat&t='.$t.'&i=1', '', array('t'=>$t));
			} else {
				cpmsg('members_stat_update_data_succeed', 'action=members&operation=stat&fieldid='.$_GET['fieldid'], 'succeed');
			}

		} else {
			cpmsg('members_stat_null_operation', 'action=members&operation=stat', 'error');
		}
	}
}

function showsearchform($operation = '') {
	global $_G, $lang;

	$groupselect = array();
	$usergroupid = isset($_GET['usergroupid']) && is_array($_GET['usergroupid']) ? $_GET['usergroupid'] : array();
	$medals = isset($_GET['medalid']) && is_array($_GET['medalid']) ? $_GET['medalid'] : array();
	$tagid = isset($_GET['tagid']) && is_array($_GET['tagid']) ? $_GET['tagid'] : array();
	$query = C::t('common_usergroup')->fetch_all_not(array(6, 7), true);
	foreach($query as $group) {
		$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
		$groupselect[$group['type']] .= "<option value=\"$group[groupid]\" ".(in_array($group['groupid'], $usergroupid) ? 'selected' : '').">$group[grouptitle]</option>\n";
	}
	$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
		($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
		($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
		'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';
	$medalselect = $usertagselect = '';
	foreach(C::t('forum_medal')->fetch_all_data(1) as $medal) {
		$medalselect .= "<option value=\"$medal[medalid]\" ".(in_array($medal['medalid'], $medals) ? 'selected' : '').">$medal[name]</option>\n";
	}
	$query = C::t('common_tag')->fetch_all_by_status(3);
	foreach($query as $row) {
		$usertagselect .= "<option value=\"$row[tagid]\" ".(in_array($row['tagid'], $tagid) ? 'selected' : '').">$row[tagname]</option>\n";
	}

	showtagheader('div', 'searchmembers', !$_GET['submit']);
	echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
	echo '<style type="text/css">#residedistrictbox select, #birthdistrictbox select{width: auto;}</style>';
	$formurl = "members&operation=$operation".($_GET['do'] == 'mobile' ? '&do=mobile' : '');
	showformheader($formurl, "onSubmit=\"if($('updatecredittype1') && $('updatecredittype1').checked && !window.confirm('$lang[members_reward_clean_alarm]')){return false;} else {return true;}\"");
	showtableheader();
	if(isset($_G['setting']['membersplit'])) {
		showsetting('members_search_table', '', '', '<select name="tablename" ><option value="master">'.$lang['members_search_table_master'].'</option><option value="archive">'.$lang['members_search_table_archive'].'</option></select>');
	}
	showsetting('members_search_user', 'username', $_GET['username'], 'text');
	showsetting('members_search_uid', 'uid', $_GET['uid'], 'text');
	showsetting('members_search_group', '', '', '<select name="groupid[]" multiple="multiple" size="10">'.$groupselect.'</select>');
	showtablefooter();

	showtableheader();
	showtagheader('tbody', 'advanceoption');
	$_G['showsetting_multirow'] = 1;
	if(empty($medalselect)) {
		$medalselect = '<option value="">'.cplang('members_search_nonemedal').'</option>';
	}
	if(empty($usertagselect)) {
		$usertagselect = '<option value="">'.cplang('members_search_noneusertags').'</option>';
	}
	showsetting('members_search_medal', '', '', '<select name="medalid[]" multiple="multiple" size="10">'.$medalselect.'</select>');
	showsetting('members_search_usertag', '', '', '<select name="tagid[]" multiple="multiple" size="10">'.$usertagselect.'</select>');
	if(!empty($_G['setting']['connect']['allow'])) {
		showsetting('members_search_conisbind', array('conisbind', array(
			array(1, $lang['yes']),
			array(0, $lang['no']),
		), 1), $_GET['conisbind'], 'mradio');
		showsetting('members_search_uinblacklist', array('uin_low', array(
			array(1, $lang['yes']),
			array(0, $lang['no']),
		), 1), $_GET['uin_low'], 'mradio');
	}
	showsetting('members_search_online', array('sid_noempty', array(
		array(1, $lang['yes']),
		array(0, $lang['no']),
	), 1), $_GET['online'], 'mradio');
	showsetting('members_search_lockstatus', array('status', array(
		array(-1, $lang['yes']),
		array(0, $lang['no']),
	), 1), $_GET['status'], 'mradio');
	showsetting('members_search_freezestatus', array('freeze', array(
		array(2, $lang['members_edit_freeze_email']),
		array(-1, $lang['members_edit_freeze_admincp']),
		array(1, $lang['members_edit_freeze_password']),
		array(0, $lang['members_edit_freeze_false']),
	), 1), $_GET['freeze'], 'mradio');
	showsetting('members_search_emailstatus', array('emailstatus', array(
		array(1, $lang['yes']),
		array(0, $lang['no']),
	), 1), $_GET['emailstatus'], 'mradio');
	showsetting('members_search_avatarstatus', array('avatarstatus', array(
		array(1, $lang['yes']),
		array(0, $lang['no']),
	), 1), $_GET['avatarstatus'], 'mradio');
	showsetting('members_search_email', 'email', $_GET['email'], 'text');
	showsetting("$lang[credits] $lang[members_search_between]", array("credits_low", "credits_high"), array($_GET['credits_low'], $_GET['credtis_high']), 'range');

	if(!empty($_G['setting']['extcredits'])) {
		foreach($_G['setting']['extcredits'] as $id => $credit) {
			showsetting("$credit[title] $lang[members_search_between]", array("extcredits$id"."_low", "extcredits$id"."_high"), array($_GET['extcredits'.$id.'_low'], $_GET['extcredits'.$id.'_high']), 'range');
		}
	}

	showsetting('members_search_friendsrange', array('friends_low', 'friends_high'), array($_GET['friends_low'], $_GET['friends_high']), 'range');
	showsetting('members_search_postsrange', array('posts_low', 'posts_high'), array($_GET['posts_low'], $_GET['posts_high']), 'range');
	showsetting('members_search_regip', 'regip', $_GET['regip'], 'text');
	showsetting('members_search_lastip', 'lastip', $_GET['lastip'], 'text');
	showsetting('members_search_oltimerange', array('oltime_low', 'oltime_high'), array($_GET['oltime_low'], $_GET['oltime_high']), 'range');
	showsetting('members_search_regdaterange', array('regdate_after', 'regdate_before'), array($_GET['regdate_after'], $_GET['regdate_before']), 'daterange');
	showsetting('members_search_lastvisitrange', array('lastvisit_after', 'lastvisit_before'), array($_GET['lastvisit_after'], $_GET['lastvisit_before']), 'daterange');
	showsetting('members_search_lastpostrange', array('lastpost_after', 'lastpost_before'), array($_GET['lastpost_after'], $_GET['lastpost_before']), 'daterange');
	showsetting('members_search_group_fid', 'fid', $_GET['fid'], 'text');
	if($_G['setting']['verify']) {
		$verifydata = array();
		foreach($_G['setting']['verify'] as $key => $value) {
			if($value['available']) {
				$verifydata[] = array('verify'.$key, $value['title']);
			}
		}
		if(!empty($verifydata)) {
			showsetting('members_search_verify', array('verify', $verifydata), $_GET['verify'], 'mcheckbox');
		}
	}
	$yearselect = $monthselect = $dayselect = "<option value=\"\">".cplang('nolimit')."</option>\n";
	$yy=dgmdate(TIMESTAMP, 'Y');
	for($y=$yy; $y>=$yy-100; $y--) {
		$y = sprintf("%04d", $y);
		$yearselect .= "<option value=\"$y\" ".($_GET['birthyear'] == $y ? 'selected' : '').">$y</option>\n";
	}
	for($m=1; $m<=12; $m++) {
		$m = sprintf("%02d", $m);
		$monthselect .= "<option value=\"$m\" ".($_GET['birthmonth'] == $m ? 'selected' : '').">$m</option>\n";
	}
	for($d=1; $d<=31; $d++) {
		$d = sprintf("%02d", $d);
		$dayselect .= "<option value=\"$d\" ".($_GET['birthday'] == $d ? 'selected' : '').">$d</option>\n";
	}
	showsetting('members_search_birthday', '', '', '<select class="txt" name="birthyear" style="width:75px; margin-right:0">'.$yearselect.'</select> '.$lang['year'].' <select class="txt" name="birthmonth" style="width:75px; margin-right:0">'.$monthselect.'</select> '.$lang['month'].' <select class="txt" name="birthday" style="width:75px; margin-right:0">'.$dayselect.'</select> '.$lang['day']);

	loadcache('profilesetting');
	unset($_G['cache']['profilesetting']['uid']);
	unset($_G['cache']['profilesetting']['birthyear']);
	unset($_G['cache']['profilesetting']['birthmonth']);
	unset($_G['cache']['profilesetting']['birthday']);
	require_once libfile('function/profile');
	foreach($_G['cache']['profilesetting'] as $fieldid=>$value) {
		if(!$value['available'] || in_array($fieldid, array('birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
			continue;
		}
		if($fieldid == 'gender') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			$select .= "<option value=\"0\">".cplang('members_edit_gender_secret')."</option>\n";
			$select .= "<option value=\"1\">".cplang('members_edit_gender_male')."</option>\n";
			$select .= "<option value=\"2\">".cplang('members_edit_gender_female')."</option>\n";
			showsetting($value['title'], '', '', '<select class="txt" name="gender">'.$select.'</select>');
		} elseif($fieldid == 'birthcity') {
			$elems = array('birthprovince', 'birthcity', 'birthdist', 'birthcommunity');
			showsetting($value['title'], '', '', '<div id="birthdistrictbox">'.showdistrict(array(0,0,0,0), $elems, 'birthdistrictbox', 1, 'birth').'</div>');
		} elseif($fieldid == 'residecity') {
			$elems = array('resideprovince', 'residecity', 'residedist', 'residecommunity');
			showsetting($value['title'], '', '', '<div id="residedistrictbox">'.showdistrict(array(0,0,0,0), $elems, 'residedistrictbox', 1, 'reside').'</div>');
		} elseif($fieldid == 'constellation') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			for($i=1; $i<=12; $i++) {
				$name = lang('space', 'constellation_'.$i);
				$select .= "<option value=\"$name\">$name</option>\n";
			}
			showsetting($value['title'], '', '', '<select class="txt" name="constellation">'.$select.'</select>');
		} elseif($fieldid == 'zodiac') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			for($i=1; $i<=12; $i++) {
				$option = lang('space', 'zodiac_'.$i);
				$select .= "<option value=\"$option\">$option</option>\n";
			}
			showsetting($value['title'], '', '', '<select class="txt" name="zodiac">'.$select.'</select>');
		} elseif($value['formtype'] == 'select' || $value['formtype'] == 'list') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			$value['choices'] = explode("\n",$value['choices']);
			foreach($value['choices'] as $option) {
				$option = trim($option);
				$select .= "<option value=\"$option\">$option</option>\n";
			}
			showsetting($value['title'], '', '', '<select class="txt" name="'.$fieldid.'">'.$select.'</select>');
		} else {
			showsetting($value['title'], '', '', '<input class="txt" name="'.$fieldid.'" />');
		}
	}
	showtagfooter('tbody');
	$_G['showsetting_multirow'] = 0;
	showsubmit('submit', $operation == 'clean' ? 'members_delete' : 'search', '', 'more_options');
	showtablefooter();
	showformfooter();
	showtagfooter('div');
}

function searchcondition($condition) {
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->filtercondition($condition);
}

function searchmembers($condition, $limit=2000, $start=0) {
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->search($condition, $limit, $start);
}

function countmembers($condition, &$urladd) {

	$urladd = '';
	foreach($condition as $k => $v) {
		if(in_array($k, array('formhash', 'submit', 'page')) || $v === '') {
			continue;
		}
		if(is_array($v)) {
			foreach($v as $vk => $vv) {
				if($vv === '') {
					continue;
				}
				$urladd .= '&'.$k.'['.$vk.']='.rawurlencode($vv);
			}
		} else {
			$urladd .= '&'.$k.'='.rawurlencode($v);
		}
	}
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->getcount($condition);
}

function shownewsletter() {
	global $lang;

	showtableheader();
	showsetting('members_newsletter_subject', 'subject', '', 'text');
	showsetting('members_newsletter_message', 'message', '', 'textarea');
	if($_GET['do'] == 'mobile') {
		showsetting('members_newsletter_system', 'system', 0, 'radio');
		showhiddenfields(array('notifymembers' => 'mobile'));
	} else {
		showsetting('members_newsletter_method', array('notifymembers', array(
			    array('email', $lang['email'], array('pmextra' => 'none', 'posttype' => '')),
			    array('notice', $lang['notice'], array('pmextra' => 'none', 'posttype' => '')),
			    array('pm', $lang['grouppm'], array('pmextra' => '', 'posttype' => 'none'))
			)), 'pm', 'mradio');
		showtagheader('tbody', 'posttype', '', 'sub');
		showsetting('members_newsletter_posttype', array('posttype', array(
				array(0, cplang('members_newsletter_posttype_text')),
				array(1, cplang('members_newsletter_posttype_html')),
			), TRUE), '0', 'mradio');
		showtagfooter('tbody');
		showtagheader('tbody', 'pmextra', true, 'sub');
		showsetting('members_newsletter_system', 'system', 0, 'radio');
		showtagfooter('tbody');
	}
	showsetting('members_newsletter_num', 'pertask', 100, 'text');
	showtablefooter();

}

function notifymembers($operation, $variable) {
	global $_G, $lang, $urladd, $conditions, $search_condition;

	if(!empty($_GET['current'])) {

		$subject = $message = '';
		if($settings = C::t('common_setting')->fetch($variable, true)) {
			$subject = $settings['subject'];
			$message = $settings['message'];
		}

		$setarr = array();
		foreach($_G['setting']['extcredits'] as $id => $value) {
			if(isset($_GET['extcredits'.$id])) {
				if($_GET['updatecredittype'] == 0) {
					$setarr['extcredits'.$id] = $_GET['extcredits'.$id];
				} else {
					$setarr[] = 'extcredits'.$id;
				}
			}
		}

	} else {

		$current = 0;
		$subject = $_GET['subject'];
		$message = $_GET['message'];
		$subject = dhtmlspecialchars(trim($subject));
		$message = trim(str_replace("\t", ' ', $message));
		$addmsg = '';
		if(($_GET['notifymembers'] && $_GET['notifymember']) && !($subject && $message)) {
			cpmsg('members_newsletter_sm_invalid', '', 'error');
		}

		if($operation == 'reward') {

			$serarr = array();
			if($_GET['updatecredittype'] == 0) {
				if(is_array($_GET['addextcredits']) && !empty($_GET['addextcredits'])) {
					foreach($_GET['addextcredits'] as $key => $value) {
						$value = intval($value);
						if(isset($_G['setting']['extcredits'][$key]) && !empty($value)) {
							$setarr['extcredits'.$key] = $value;
							$addmsg .= $_G['setting']['extcredits'][$key]['title'].": ".($value > 0 ? '<em class="xi1">+' : '<em class="xg1">')."$value</em> ".$_G['setting']['extcredits'][$key]['unit'].' &nbsp; ';
						}
					}
				}
			} else {
				if(is_array($_GET['resetextcredits']) && !empty($_GET['resetextcredits'])) {
					foreach($_GET['resetextcredits'] as $key => $value) {
						$value = intval($value);
						if(isset($_G['setting']['extcredits'][$key]) && !empty($value)) {
							$setarr[] = 'extcredits'.$key;
							$addmsg .= $_G['setting']['extcredits'][$key]['title'].': <em class="xg1">'.cplang('members_reward_clean').'</em> &nbsp; ';
						}
					}
				}
			}
			if($addmsg) {
				$addmsg  = ' &nbsp; <br /><br /><b>'.cplang('members_reward_affect').':</b><br \>'.$addmsg;
			}

			if(!empty($setarr)) {
				$limit = 2000;
				set_time_limit(0);
				$i = 0;
				while(true) {
					$uids = searchmembers($search_condition, $limit, $i*$limit);
					$allcount = C::t('common_member_count')->fetch_all($uids);
					$insertmember = array_diff($uids, array_keys($allcount));
					foreach($insertmember as $uid) {
						C::t('common_member_count')->insert(array('uid' => $uid));
					}
					if($_GET['updatecredittype'] == 0) {
						C::t('common_member_count')->increase($uids, $setarr);
					} else {
						C::t('common_member_count')->clear_extcredits($uids, $setarr);
					}
					if(count($uids) < $limit) break;
					$i++;
				}
			} else {
				cpmsg('members_reward_invalid', '', 'error');
			}

			if(!$_GET['notifymembers']) {
				cpmsg('members_reward_succeed', '', 'succeed');
			}

		} elseif ($operation == 'confermedal') {

			$medals = $_GET['medals'];
			if(!empty($medals)) {
				$medalids = array();
				foreach($medals as $key => $medalid) {
					$medalids[] = $key;
				}

				$medalsnew = $comma = '';
				$medalsnewarray = $medalidarray = array();
				foreach(C::t('forum_medal')->fetch_all_by_id($medalids) as $medal) {
					$medal['status'] = empty($medal['expiration']) ? 0 : 1;
					$medal['expiration'] = empty($medal['expiration'])? 0 : TIMESTAMP + $medal['expiration'] * 86400;
					$medal['medal'] = $medal['medalid'].(empty($medal['expiration']) ? '' : '|'.$medal['expiration']);
					$medalsnew .= $comma.$medal['medal'];
					$medalsnewarray[] = $medal;
					$medalidarray[] = $medal['medalid'];
					$comma = "\t";
				}

				$uids = searchmembers($search_condition);
				if($uids) {
					foreach(C::t('common_member_field_forum')->fetch_all($uids) as $uid => $medalnew) {
						$usermedal = array();
						$addmedalnew = '';
						if(empty($medalnew['medals'])) {
							$addmedalnew = $medalsnew;
						} else {
							foreach($medalidarray as $medalid) {
								$usermedal_arr = explode("\t", $medalnew['medals']);
								foreach($usermedal_arr AS $key => $medalval) {
									list($usermedalid,) = explode("|", $medalval);
									$usermedal[] = $usermedalid;
								}
								if(!in_array($medalid, $usermedal)){
									$addmedalnew .= $medalid."\t";
								}
							}
							$addmedalnew .= $medalnew['medals'];
						}
						C::t('common_member_field_forum')->update($medalnew['uid'], array('medals' => $addmedalnew), true);
						foreach($medalsnewarray as $medalnewarray) {
							$data = array(
								'uid' => $medalnew['uid'],
								'medalid' => $medalnewarray['medalid'],
								'type' => 0,
								'dateline' => $_G['timestamp'],
								'expiration' => $medalnewarray['expiration'],
								'status' => $medalnewarray['status'],
							);
							C::t('forum_medallog')->insert($data);
							C::t('common_member_medal')->insert(array('uid' => $medalnew['uid'], 'medalid' => $medalnewarray['medalid']), 0, 1);
						}
					}
				}
			}

			if(!$_GET['notifymember']) {
				cpmsg('members_confermedal_succeed', '', 'succeed');
			}
		} elseif ($operation == 'confermagic') {
			$magics = $_GET['magic'];
			$magicnum = $_GET['magicnum'];
			if($magics) {
				require_once libfile('function/magic');
				$limit = 200;
				set_time_limit(0);
				for($i=0; $i > -1; $i++) {
					$uids = searchmembers($search_condition, $limit, $i*$limit);

					foreach($magics as $magicid) {
						$uparray = $insarray = array();
						if(empty($magicnum[$magicid])) {
							continue;
						}
						$query = C::t('common_member_magic')->fetch_all($uids ? $uids : -1, $magicid);
						foreach($query as $row) {
							$uparray[] = $row['uid'];
						}
						if($uparray) {
							C::t('common_member_magic')->increase($uparray, $magicid, array('num' => $magicnum[$magicid]));
						}
						$insarray = array_diff($uids, $uparray);
						if($insarray) {
							$sqls = array();
							foreach($insarray as $uid) {
								C::t('common_member_magic')->insert(array(
									'uid' => $uid,
									'magicid' => $magicid,
									'num' => $magicnum[$magicid]
								));
							}
						}
						foreach($uids as $uid) {
							updatemagiclog($magicid, '3', $magicnum[$magicid], '', $uid);
						}
					}
					if(count($uids) < $limit) break;
				}
			}
		}

		C::t('common_setting')->update($variable, array('subject' => $subject, 'message' => $message));
	}

	$pertask = intval($_GET['pertask']);
	$current = $_GET['current'] ? intval($_GET['current']) : 0;
	$continue = FALSE;

	if(!function_exists('sendmail')) {
		include libfile('function/mail');
	}
	if($_GET['notifymember'] && in_array($_GET['notifymembers'], array('pm', 'notice', 'email'))) {
		$uids = searchmembers($search_condition, $pertask, $current);

		require_once libfile('function/discuzcode');
		$message = in_array($_GET['notifymembers'], array('email','notice')) && $_GET['posttype'] ? discuzcode($message, 1, 0, 1, '', '' ,'' ,1) : discuzcode($message, 1, 0);
		$pmuids = array();
		if($_GET['notifymembers'] == 'pm') {
			$membernum = countmembers($search_condition, $urladd);
			$gpmid = $_GET['gpmid'];
			if(!$gpmid) {
				$pmdata = array(
						'authorid' => $_G['uid'],
						'author' => !$_GET['system'] ? $_G['member']['username'] : '',
						'dateline' => TIMESTAMP,
						'message' => ($subject ? '<b>'.$subject.'</b><br /> &nbsp; ' : '').$message.$addmsg,
						'numbers' => $membernum
					);
				$gpmid = C::t('common_grouppm')->insert($pmdata, true);
			}
			$urladd .= '&gpmid='.$gpmid;
		}
		$members = C::t('common_member')->fetch_all($uids);
		foreach($members as $member) {
			if($_GET['notifymembers'] == 'pm') {
				C::t('common_member_grouppm')->insert(array(
					'uid' => $member['uid'],
					'gpmid' => $gpmid,
					'status' => 0
				), false, true);
				$newpm = setstatus(2, 1, $member['newpm']);
				C::t('common_member')->update($member['uid'], array('newpm'=>$newpm));
			} elseif($_GET['notifymembers'] == 'notice') {
				notification_add($member['uid'], 'system', 'system_notice', array('subject' => $subject, 'message' => $message.$addmsg, 'from_id' => 0, 'from_idtype' => 'sendnotice'), 1);
			} elseif($_GET['notifymembers'] == 'email') {
				if(!sendmail("$member[username] <$member[email]>", $subject, $message.$addmsg)) {
					runlog('sendmail', "$member[email] sendmail failed.");
				}
			}

			$log = array();
			if($_GET['updatecredittype'] == 0) {
				foreach($setarr as $key => $val) {
					if(empty($val)) continue;
					$val = intval($val);
					$id = intval($key);
					$id = !$id && substr($key, 0, -1) == 'extcredits' ? intval(substr($key, -1, 1)) : $id;
					if(0 < $id && $id < 9) {
							$log['extcredits'.$id] = $val;
					}
				}
				$logtype = 'RPR';
			} else {
				foreach($setarr as $val) {
					if(empty($val)) continue;
					$id = intval($val);
					$id = !$id && substr($val, 0, -1) == 'extcredits' ? intval(substr($val, -1, 1)) : $id;
					if(0 < $id && $id < 9) {
						$log['extcredits'.$id] = '-1';
					}
				}
				$logtype = 'RPZ';
			}
			include_once libfile('function/credit');
			credit_log($member['uid'], $logtype, $member['uid'], $log);

			$continue = TRUE;
		}
	}

	$newsletter_detail = array();
	if($continue) {
		$next = $current + $pertask;
		$newsletter_detail = array(
			'uid' => $_G['uid'],
			'current' => $current,
			'next' => $next,
			'search_condition' => serialize($search_condition),
			'action' => "action=members&operation=$operation&{$operation}submit=yes&current=$next&pertask=$pertask&system={$_GET['system']}&posttype={$_GET['posttype']}&notifymember={$_GET['notifymember']}&notifymembers=".rawurlencode($_GET['notifymembers']).$urladd
		);
		save_newsletter('newsletter_detail', $newsletter_detail);

		$logaddurl = '';
		foreach($setarr as $k => $v) {
			if($_GET['updatecredittype'] == 0) {
				$logaddurl .= '&'.$k.'='.$v;
			} else {
				$logaddurl .= '&'.$v.'=-1';
			}
		}
		$logaddurl .= '&updatecredittype='.$_GET['updatecredittype'];

		cpmsg("$lang[members_newsletter_send]: ".cplang('members_newsletter_processing', array('current' => $current, 'next' => $next, 'search_condition' => serialize($search_condition))), "action=members&operation=$operation&{$operation}submit=yes&current=$next&pertask=$pertask&system={$_GET['system']}&posttype={$_GET['posttype']}&notifymember={$_GET['notifymember']}&notifymembers=".rawurlencode($_GET['notifymembers']).$urladd.$logaddurl, 'loadingform');
	} else {
		del_newsletter('newsletter_detail');

		if($operation == 'reward' && $_GET['notifymembers'] == 'pm') {
			$message = '';
		} else {
			$message = '_notify';
		}
		cpmsg('members'.($operation ? '_'.$operation : '').$message.'_succeed', '', 'succeed');
	}

}

function banlog($username, $origgroupid, $newgroupid, $expiration, $reason, $status = 0) {
	global $_G, $_POST;
	$cloud_apps = dunserialize($_G['setting']['cloud_apps']);
	writelog('banlog', dhtmlspecialchars("$_G[timestamp]\t{$_G[member][username]}\t$_G[groupid]\t$_G[clientip]\t$username\t$origgroupid\t$newgroupid\t$expiration\t$reason\t$status"));
}

function selectday($varname, $dayarray) {
	global $lang;
	$selectday = '<select name="'.$varname.'">';
	if($dayarray && is_array($dayarray)) {
		foreach($dayarray as $day) {
			$langday = $day.'_day';
			$daydate = $day ? '('.dgmdate(TIMESTAMP + $day * 86400).')' : '';
			$selectday .= '<option value='.$day.'>'.$lang[$langday].'&nbsp;'.$daydate.'</option>';
		}
	}
	$selectday .= '</select>';

	return $selectday;
}

function accessimg($access) {
	return $access == -1 ? '<img src="static/image/common/access_disallow.gif" />' :
		($access == 1 ? '<img src="static/image/common/access_allow.gif" />' : '<img src="static/image/common/access_normal.gif" />');
}

function connectunbind($member) {
	global $_G;
	if(!$member['conopenid']) {
		return;
	}
	$_G['member'] = array_merge($_G['member'], $member);

	C::t('#qqconnect#connect_memberbindlog')->insert(array('uid' => $member['uid'], 'uin' => $member['conopenid'], 'type' => '2', 'dateline' => $_G['timestamp']));
	C::t('common_member')->update($member['uid'], array('conisbind'=>0));
	C::t('#qqconnect#common_member_connect')->delete($member['uid']);
}

function save_newsletter($cachename, $data) {
	C::t('common_cache')->insert(array('cachekey' => $cachename, 'cachevalue' => serialize($data), 'dateline' => TIMESTAMP), false, true);
}

function del_newsletter($cachename) {
	C::t('common_cache')->delete($cachename);
}

function get_newsletter($cachename) {
	foreach(C::t('common_cache')->fetch_all($cachename) as $result) {
		$data = $result['cachevalue'];
	}
	return $data;
}

?>