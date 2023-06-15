<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_moderate.php 32077 2012-11-07 04:38:04Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}


$modact = empty($_GET['modact']) || !in_array($_GET['modact'] , array('delete', 'ignore', 'validate')) ? 'ignore' : $_GET['modact'];

if($op == 'members') {

	$filter = isset($_GET['filter']) ? intval($_GET['filter']) : 0;
	$filtercheck = array('', '', '');
	$filtercheck[$filter] = 'selected';

	if(submitcheck('dosubmit', 1) || submitcheck('modsubmit')) {

		if(empty($modact) || !in_array($modact, array('ignore', 'validate', 'delete'))) {
			showmessage('modcp_noaction');
		}

		$list = array();
		if($_GET['moderate'] && is_array($_GET['moderate'])) {
			foreach($_GET['moderate'] as $val) {
				if(is_numeric($val) && $val) {
					$list[] = $val;
				}
			}
		}

		if(submitcheck('dosubmit', 1)) {

			$_GET['handlekey'] = 'mods';
			include template('forum/modcp_moderate_float');
			dexit();

		} elseif ($uids = $list) {

			$members = $uidarray = array();


			$member_validate = C::t('common_member_validate')->fetch_all($uids);
			foreach(C::t('common_member')->fetch_all($uids, false, 0) as $uid => $member) {
				if(($member['groupid'] == 8 || (in_array($member['freeze'], array(-1, 2)) && $modact != 'delete')) && $member['status'] == $filter) {
					$members[$uid] = array_merge((array)$member_validate[$uid], $member);
				}
			}
			if(($uids = array_keys($members))) {

				$reason = dhtmlspecialchars(trim($_GET['reason']));

				if($_GET['modact'] == 'delete') {
					C::t('common_member')->delete_no_validate($uids);
				}

				if($_GET['modact'] == 'validate') {
					C::t('common_member')->update($uids, array('adminid' => '0', 'groupid' => $_G['setting']['newusergroupid'], 'freeze' => 0));
					C::t('common_member_validate')->delete($uids);
				}

				if($_GET['modact'] == 'ignore') {
					C::t('common_member_validate')->update($uids, array('moddate' => $_G['timestamp'], 'admin' => $_G['username'], 'status' => '1', 'remark' => $reason));
				}

				$sendemail = isset($_GET['sendemail']) ? $_GET['sendemail'] : 0;
				if($sendemail) {
					if(!function_exists('sendmail')) {
						include libfile('function/mail');
					}
					foreach($members as $uid => $member) {
						$member['regdate'] = dgmdate($member['regdate']);
						$member['submitdate'] = dgmdate($member['submitdate']);
						$member['moddate'] = dgmdate(TIMESTAMP);
						$member['operation'] = $_GET['modact'];
						$member['remark'] = $reason ? $reason : 'N/A';
						$moderate_member_message = lang('email', 'moderate_member_message', array(
							'username' => $member['username'],
							'bbname' => $_G['setting']['bbname'],
							'regdate' => $member['regdate'],
							'submitdate' => $member['submitdate'],
							'submittimes' => $member['submittimes'],
							'message' => $member['message'],
							'modresult' => lang('email', 'moderate_member_'.$member['operation']),
							'moddate' => $member['moddate'],
							'adminusername' => $_G['member']['username'],
							'remark' => $member['remark'],
							'siteurl' => $_G['siteurl'],
						));
						if(!sendmail("$member[username] <$member[email]>", lang('email', 'moderate_member_subject'), $moderate_member_message)) {
							runlog('sendmail', "$member[email] sendmail failed.");
						}
					}
				}
			} else {
				showmessage('modcp_moduser_invalid');
			}

			showmessage('modcp_mod_succeed', "{$cpscript}?mod=modcp&action=$_GET[action]&op=$op&filter=$filter");

		} else {
			showmessage('modcp_moduser_invalid');
		}

	} else {
		$count = C::t('common_member_validate')->fetch_all_status_by_count();

		$page = max(1, intval($_G['page']));
		$_G['setting']['memberperpage'] = 20;
		$start_limit = ($page - 1) * $_G['setting']['memberperpage'];

		$multipage = multi($count[$filter], $_G['setting']['memberperpage'], $page, "{$cpscript}?mod=modcp&action=$_GET[action]&op=$op&fid=$_G[fid]&filter=$filter");

		$vuids = array();
		$memberlist = $member_validate = $common_member = $member_status = array();
		if(($member_validate = C::t('common_member_validate')->fetch_all_by_status($filter, $start_limit, $_G['setting']['memberperpage']))) {
			$uids = array_keys($member_validate);
			$common_member = C::t('common_member')->fetch_all($uids, false, 0);
			$member_status = C::t('common_member_status')->fetch_all($uids, false, 0);
		}
		foreach($member_validate as $uid => $member) {
			$member = array_merge($member, $common_member[$uid], $member_status[$uid]);
			if($member['groupid'] != 8 && !in_array($member['freeze'], array(-1, 2))) {
				$vuids[] = $member['uid'];
				continue;
			}
			$member['regdate'] = dgmdate($member['regdate']);
			$member['submitdate'] = dgmdate($member['submitdate']);
			$member['moddate'] = $member['moddate'] ? dgmdate($member['moddate']) : $lang['none'];
			$member['message'] = dhtmlspecialchars($member['message']);
			$member['admin'] = $member['admin'] ? "<a href=\"home.php?mod=space&username=".rawurlencode($member['admin'])."\" target=\"_blank\">$member[admin]</a>" : $lang['none'];
			$memberlist[] = $member;
		}
		if($vuids) {
			C::t('common_member_validate')->delete($vuids, 'UNBUFFERED');
		}

		return true;
	}
}

if(empty($modforums['fids'])) {
	return false;
} elseif($_G['fid'] && ($_G['forum']['type'] == 'group' || !$_G['forum']['ismoderator'])) {
	return false;
} else {
	$modfids = "";
	if($_G['fid']) {
		$modfids = $_G['fid'];
		$modfidsadd = "fid='$_G[fid]'";
	} elseif($_G['adminid'] == 1) {
		$modfidsadd = "";
	} else {
		$modfids = $modforums['fids'];
		$modfidsadd = "fid in ($modforums[fids])";
	}
}

$updatestat = false;

$op = !in_array($op , array('replies', 'threads')) ? 'threads' : $op;

$filter = !empty($_GET['filter']) ? -3 : 0;
$filtercheck = array(0 => '', '-3' => '');
$filtercheck[$filter] = 'selected="selected"';

$pstat = $filter == -3 ? -3 : -2;
$moderatestatus = $filter == -3 ? 1 : 0;

$tpp = 10;
$page = max(1, intval($_G['page']));
$start_limit = ($page - 1) * $tpp;

$postlist = array();
$posttableselect = '';

$modpost = array('validate' => 0, 'delete' => 0, 'ignore' => 0);
$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());

require_once libfile('function/post');

if(submitcheck('dosubmit', 1) || submitcheck('modsubmit')) {

	$list = array();
	if($_GET['moderate'] && is_array($_GET['moderate'])) {
		foreach($_GET['moderate'] as $val) {
			if(is_numeric($val) && $val) {
				$moderation[$modact][] = $val;
			}
		}
	}

	if(submitcheck('modsubmit')) {

		$updatestat = $op == 'replies' ? 1 : 2;
		$modpost = array(
			'ignore' => count($moderation['ignore']),
			'delete' => count($moderation['delete']),
			'validate' => count($moderation['validate'])
		);
	} elseif(submitcheck('dosubmit', 1)) {
		$_GET['handlekey'] = 'mods';
		$list = $moderation[$modact];
		include template('forum/modcp_moderate_float');
		dexit();

	}
}

if($op == 'replies') {
	$posttableid = intval($_GET['posttableid']);
	$posttable = getposttable($posttableid);

	$posttableselect = getposttableselect();

	if(submitcheck('modsubmit')) {

		$pmlist = array();
		if($ignorepids = dimplode($moderation['ignore'])) {
			C::t('forum_post')->update($posttableid, $moderation['ignore'], array('invisible' => -3), true, false, 0, -2, ($modfids ? explode(',', $modfids) : null));
			updatemoderate('pid', $moderation['ignore'], 1);
		}

		if($deletepids = dimplode($moderation['delete'])) {
			$recyclebinpids = array();
			$pids = array();
			foreach(C::t('forum_post')->fetch_all($posttableid, $moderation['delete']) as $post) {
				if($post['invisible'] != $pstat || $post['first'] != 0 || ($modfids ? !in_array($post['fid'], explode(',', $modfids)) : 0)) {
					continue;
				}
				if($modforums['recyclebins'][$post['fid']]) {
					$recyclebinpids[] = $post['pid'];
				} else {
					$pids[] = $post['pid'];
				}
				if($post['authorid'] && $post['authorid'] != $_G['uid']) {
					$pmlist[] = array(
						'act' => $_GET['reason'] ? 'modreplies_delete_reason' : 'modreplies_delete',
						'notevar' => array('reason' => dhtmlspecialchars($_GET['reason']), 'post' => messagecutstr($post['message'], 30)),
						'authorid' => $post['authorid'],
					);
				}
			}

			if($recyclebinpids) {
				C::t('forum_post')->update($posttableid, $recyclebinpids, array('invisible' => '-5'), true);
			}

			if($pids) {
				require_once libfile('function/delete');
				deletepost($pids, 'pid', false, $posttableid);
			}
			updatemodworks('DLP', count($moderation['delete']));
			updatemoderate('pid', $moderation['delete'], 2);
		}

		$repliesmod = 0;
		if($validatepids = dimplode($moderation['validate'])) {

			$threads = $lastpost = $attachments = $pidarray = array();
			$postlist = $tids = array();
			foreach(C::t('forum_post')->fetch_all($posttableid, $moderation['validate']) as $post) {
				if($post['invisible'] != $pstat || $post['first'] != '0' || ($modfids ? !in_array($post['fid'], explode(',', $modfids)) : 0)) {
					continue;
				}
				$tids[$post['tid']] = $post['tid'];
				$postlist[] = $post;
			}
			$threadlist = C::t('forum_thread')->fetch_all($tids);
			foreach($postlist as $post) {
				$post['lastpost'] = $threadlist[$post['tid']]['lastpost'];
				$repliesmod ++;
				$pidarray[] = $post['pid'];
				if(getstatus($post['status'], 3) == 0) {
					updatepostcredits('+', $post['authorid'], 'reply', $post['fid']);
					$attachcount = C::t('forum_attachment_n')->count_by_id('tid:'.$post['tid'], 'pid', $post['pid']);
					updatecreditbyaction('postattach', $post['authorid'], array(), '', $attachcount, 1, $post['fid']);
				}

				$threads[$post['tid']]['posts']++;

				if($post['dateline'] > $post['lastpost'] && $post['dateline'] > $lastpost[$post['tid']]) {
					$threads[$post['tid']]['lastpost'] = $post['dateline'];
					$threads[$post['tid']]['lastposter'] = $post['anonymous'] && $post['dateline'] != $post['lastpost'] ? '' : addslashes($post[author]);
				}
				if($threads[$post['tid']]['attachadd'] || $post['attachment']) {
					$threads[$post['tid']]['attachment'] = 1;
				}

				$pm = 'pm_'.$post['pid'];
				if($post['authorid'] && $post['authorid'] != $_G['uid']) {
					$pmlist[] = array(
						'act' => 'modreplies_validate',
						'notevar' => array('reason' => dhtmlspecialchars($_GET['reason']), 'pid' => $post['pid'], 'tid' => $post['tid'], 'post' => messagecutstr($post['message'], 30), 'from_id' => 0, 'from_idtype' => 'modreplies'),
						'authorid' => $post['authorid'],
					);
				}
			}
			unset($postlist, $tids, $threadlist);
			foreach($threads as $tid => $thread) {
				$updatedata = array('replies'=>$thread['posts']);
				if(isset($thread['lastpost'])) {
					$updatedata['lastpost'] = array($thread['lastpost']);
					$updatedata['lastposter'] = array($thread['lastposter']);
				}
				if(isset($thread['attachment'])) {
					$updatedata['attachment'] = $thread['attachment'];
				}
				C::t('forum_thread')->increase($tid, $updatedata);
			}
			if($_G['fid']) {
				updateforumcount($_G['fid']);
			} else {
				$fids = array_keys($modforums['list']);
				foreach($fids as $f) {
					updateforumcount($f);
				}
			}

			if(!empty($pidarray)) {
				$pidarray[] = 0;
				$repliesmod = C::t('forum_post')->update($posttableid, $pidarray, array('invisible' => '0'), true);
				updatemodworks('MOD', $repliesmod);
				updatemoderate('pid', $pidarray, 2);
			} else {
				updatemodworks('MOD', 1);
			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$post = $pm['post'];
				$_G['tid'] = intval($pm['tid']);
				notification_add($pm['authorid'], 'system', $pm['act'], $pm['notevar'], 1);
			}
		}

		showmessage('modcp_mod_succeed', "{$cpscript}?mod=modcp&action=$_GET[action]&op=$op&filter=$filter&fid=$_G[fid]");
	}

	$attachlist = array();

	require_once libfile('function/discuzcode');
	require_once libfile('function/attachment');

	$ppp = 10;
	$page = max(1, intval($_G['page']));
	$start_limit = ($page - 1) * $ppp;

	$modcount = C::t('common_moderate')->count_by_search_for_post($posttable, $moderatestatus, 0, ($modfids ? explode(',', $modfids) : null));
	$multipage = multi($modcount, $ppp, $page, "{$cpscript}?mod=modcp&action=$_GET[action]&op=$op&filter=$filter&fid=$_G[fid]");

	if($modcount) {

		$attachtablearr = array();
		$_fids = array();
		foreach(C::t('common_moderate')->fetch_all_by_search_for_post($posttable, $moderatestatus, 0, ($modfids ? explode(',', $modfids) : null), null, null, null, $start_limit, $ppp) as $post) {
			$_fids[$post['fid']] = $post['fid'];
			$_tids[$post['tid']] = $post['tid'];
			$post['id'] = $post['pid'];
			$post['dateline'] = dgmdate($post['dateline']);
			$post['subject'] = $post['subject'] ? '<b>'.$post['subject'].'</b>' : '';
			$post['message'] = nl2br(dhtmlspecialchars($post['message']));

			if($post['attachment']) {
				$attachtable = getattachtableid($post['tid']);
				$attachtablearr[$attachtable][$post['pid']] = $post['pid'];
			}
			$postlist[$post['pid']] = $post;
		}
		$_threads = $_forums = array();
		if($_fids) {
			$_forums = C::t('forum_forum')->fetch_all($_fids);
			foreach($postlist as &$_post) {
				$_forum = $_forums[$_post['fid']];
				$_arr = array(
					'forumname' => $_forum['name'],
					'allowsmilies' => $_forum['allowsmilies'],
					'allowhtml' => $_forum['allowhtml'],
					'allowbbcode' => $_forum['allowbbcode'],
					'allowimgcode' => $_forum['allowimgcode'],
				);
				$_post = array_merge($_post, $_arr);
			}
		}
		if($_tids) {
			$_threads = C::t('forum_thread')->fetch_all($_tids);
			foreach($postlist as &$_post) {
				$_post['tsubject'] = $_threads[$_post['tid']]['subject'];
			}
		}

		if(!empty($attachtablearr)) {
			foreach($attachtablearr as $attachtable => $pids) {
				foreach(C::t('forum_attachment_n')->fetch_all_by_id($attachtable, 'pid', $pids) as $attach) {
					$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
					$attach['url'] = $attach['isimage']
					? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"{$_G[setting][attachurl]}forum/$attach[attachment]\" onload=\"if(this.width > 100) {this.resized=true; this.width=100;}\">"
					: "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$postlist[$attach['pid']]['message'] .= "<br /><br />File: ".attachtype(fileext($attach['filename'])."\t").$attach['url'];
				}
			}
		}
	}


} else {

	if(submitcheck('modsubmit')) {
		if(!empty($moderation['ignore'])) {
			C::t('forum_thread')->update_by_tid_displayorder($moderation['ignore'], -2, array('displayorder'=>-3), ($modfids ? explode(',', $modfids) : null));
			updatemoderate('tid', $moderation['ignore'], 1);
		}
		$threadsmod = 0;
		$pmlist = array();
		$reason = trim($_GET['reason']);

		if(!empty($moderation['delete'])) {
			$deletetids = array();
			$recyclebintids = '0';
			foreach(C::t('forum_thread')->fetch_all_by_tid_displayorder($moderation['delete'], $pstat, '=', ($modfids ? explode(',', $modfids) : null)) as $thread) {
				if($modforums['recyclebins'][$thread['fid']]) {
					$recyclebintids .= ','.$thread['tid'];
				} else {
					$deletetids[] = $thread['tid'];
				}

				if($thread['authorid'] && $thread['authorid'] != $_G['uid']) {
					$pmlist[] = array(
						'act' => $_GET['reason'] ? 'modthreads_delete_reason' : 'modthreads_delete',
						'notevar' => array('reason' => dhtmlspecialchars($_GET['reason']), 'threadsubject' => $thread['subject']),
						'authorid' => $thread['authorid'],
					);
				}
			}

			if($recyclebintids) {
				$rows = C::t('forum_thread')->update(explode(',', $recyclebintids), array('displayorder' => -1, 'moderated' => 1));
				updatemodworks('MOD', $rows);

				C::t('forum_post')->update_by_tid(0, explode(',', $recyclebintids), array('invisible' => -1), true);
				updatemodlog($recyclebintids, 'DEL');
			}

			require_once libfile('function/delete');
			deletethread($deletetids);
			updatemoderate('tid', $moderation['delete'], 2);
		}

		if($validatetids = dimplode($moderation['validate'])) {

			$tids = $moderatedthread = array();
			foreach(C::t('forum_thread')->fetch_all_by_tid_displayorder($moderation['validate'], $pstat, '=', ($modfids ? explode(',', $modfids) : null)) as $thread) {
				$tids[] = $thread['tid'];
				$poststatus = C::t('forum_post')->fetch_threadpost_by_tid_invisible($thread['tid']);
				$poststatus = $poststatus['status'];
				if(getstatus($poststatus, 3) == 0) {
					updatepostcredits('+', $thread['authorid'], 'post', $thread['fid']);
					$attachcount = C::t('forum_attachment_n')->count_by_id('tid:'.$thread['tid'], 'tid', $thread['tid']);
					updatecreditbyaction('postattach', $thread['authorid'], array(), '', $attachcount, 1, $thread['fid']);
				}
				$validatedthreads[] = $thread;

				if($thread['authorid'] && $thread['authorid'] != $_G['uid']) {
					$pmlist[] = array(
						'act' => 'modthreads_validate',
						'notevar' => array('reason' => dhtmlspecialchars($_GET['reason']), 'tid' => $thread['tid'], 'threadsubject' => $thread['subject'], 'from_id' => 0, 'from_idtype' => 'modthreads'),
						'authorid' => $thread['authorid'],
					);
				}
			}

			if($tids) {

				$tidstr = dimplode($tids);
				C::t('forum_post')->update_by_tid(0, $tids, array('invisible' => 0), true, false, 1);
				C::t('forum_thread')->update($tids, array('displayorder'=>0, 'moderated'=>1));
				$threadsmod = DB::affected_rows();

				if($_G['fid']) {
					updateforumcount($_G['fid']);
				} else {
					$fids = array_keys($modforums['list']);
					foreach($fids as $f) {
						updateforumcount($f);
					}
				}
				updatemodworks('MOD', $threadsmod);
				updatemodlog($tidstr, 'MOD');
				updatemoderate('tid', $tids, 2);

			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$threadsubject = $pm['thread'];
				$_G['tid'] = intval($pm['tid']);
				notification_add($pm['authorid'], 'system', $pm['act'], $pm['notevar'], 1);
			}
		}

		showmessage('modcp_mod_succeed', "{$cpscript}?mod=modcp&action=$_GET[action]&op=$op&filter=$filter&fid=$_G[fid]");

	}

	$modcount = C::t('common_moderate')->count_by_seach_for_thread($moderatestatus, ($modfids ? explode(',', $modfids) : null));
	$multipage = multi($modcount, $_G['tpp'], $page, "{$cpscript}?mod=modcp&action=$_GET[action]&op=$op&filter=$filter&fid=$_G[fid]");

	if($modcount) {
		$posttablearr = array();

		foreach(C::t('common_moderate')->fetch_all_by_search_for_thread($moderatestatus, ($modfids ? explode(',', $modfids) : null), $start_limit, $_G['tpp']) as $thread) {

			$thread['id'] = $thread['tid'];

			if($thread['authorid'] && $thread['author'] != '') {
				$thread['author'] = "<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>";
			} elseif($thread['authorid']) {
				$thread['author'] = "<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">UID $thread[uid]</a>";
			} else {
				$thread['author'] = 'guest';
			}

			$thread['dateline'] = dgmdate($thread['dateline']);
			$posttable = $thread['posttableid'] ? (string)$thread['posttableid'] : '0';
			$posttablearr[$posttable][$thread['tid']] = $thread['tid'];
			$postlist[$thread['tid']] = $thread;
		}

		$attachtablearr = array();

		foreach($posttablearr as $posttable => $tids) {
			foreach(C::t('forum_post')->fetch_all_by_tid($posttable, $tids, true, '', 0, 0, 1) as $post) {
				$thread = $postlist[$post['tid']] + $post;
				$thread['message'] = nl2br(dhtmlspecialchars($thread['message']));

				if($thread['attachment']) {
					$attachtable = getattachtableid($thread['tid']);
					$attachtablearr[$attachtable][$thread['tid']] = $thread['tid'];
				} else {
					$thread['attach'] = '';
				}

				if($thread['sortid']) {
					require_once libfile('function/threadsort');
					$threadsortshow = threadsortshow($thread['sortid'], $thread['tid']);

					foreach($threadsortshow['optionlist'] as $option) {
						$thread['sortinfo'] .= $option['title'].' '.$option['value']."<br />";
					}
				} else {
					$thread['sortinfo'] = '';
				}

				$postlist[$post['tid']] = $thread;
			}
		}

		if(!empty($attachtablearr)) {
			require_once libfile('function/attachment');
			foreach($attachtablearr as $attachtable => $tids) {
				foreach(C::t('forum_attachment_n')->fetch_all_by_id($attachtable, 'tid', $tids) as $attach) {
					$tid = $attach['tid'];
					$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
					$attach['url'] = $attach['isimage']
					? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 100) {this.resized=true; this.width=100;}\">"
					: "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$postlist[$tid]['attach'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])."\t").$attach['url'];
				}
			}
		}
	}

}
?>