<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_thread.php 32501 2013-01-29 09:51:00Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_GET['fast']) {

	require_once libfile('function/discuzcode');

	$select[$_GET['tpp']] = $_GET['tpp'] ? "selected='selected'" : '';
	$tpp_options = "<option value='20' $select[20]>20</option><option value='50' $select[50]>50</option><option value='100' $select[100]>100</option>";
	$tpp = !empty($_GET['tpp']) ? $_GET['tpp'] : '20';
	$start_limit = ($page - 1) * $tpp;
	$dateline = $_GET['dateline'] ? $_GET['dateline'] : '604800';
	$dateline_options = '';
	foreach(array('all', '604800', '2592000', '7776000') as $v) {
		$selected = '';
		if($dateline == $v) {
			$selected = "selected='selected'";
		}
		$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
	}

	shownav('topic', $lang['moderate_threads']);
	showsubmenu('nav_moderate_threads', $submenu);

	showformheader("moderate&operation=threads");
	showtableheader('search');
	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
		array(
			cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_GET[username]\" />",
			cplang('moderate_title_keyword'), "<input size=\"15\" name=\"title\" type=\"text\" value=\"$_GET[title]\" />",
		)
	);
        showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
                array(
                        "$lang[perpage]",
                        "<select name=\"tpp\">$tpp_options</select><label><input name=\"showcensor\" type=\"checkbox\" class=\"checkbox\" value=\"yes\" ".($showcensor ? ' checked="checked"' : '')."/> $lang[moderate_showcensor]</label>",
                        "$lang[moderate_bound]",
                        "<select name=\"filter\">$filteroptions</select>
                        <select name=\"modfid\">$forumoptions</select>
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );
	showtablefooter();
	showtableheader();

	$title = '';
	if(!empty($_GET['title'])) {
		$title = str_replace(array('_', '%'), array('\_', '\%'), $_GET['title']);
	}
	if(!empty($dateline) && $dateline != 'all') {
		$srcdate = TIMESTAMP - $dateline;
	}


	$fids = $modfid && $modfid != -1 ? $modfid : 0;
	$isgroup = $modfid == -1 ? 1 : -1;
	$modcount = 0;
	$moderates = C::t('common_moderate')->fetch_all_by_idtype('tid', $moderatestatus, $srcdate);
	if(!empty($moderates)) {
		$modcount = C::t('forum_thread')->count_by_tid_fid(array_keys($moderates), $fids, $isgroup, $_GET['username'], $title);
	}
	if($modcount != count($moderates) && !$srcdate && !$fids && !$_GET['username'] && !$title) {
		moderateswipe('tid', array_keys($moderates));
	}

	$start_limit = ($page - 1) * $tpp;
	if($modcount) {
		$threadlist = C::t('forum_thread')->fetch_all_by_tid_fid(array_keys($moderates), $fids, $isgroup, $_GET['username'], $title, $start_limit, $tpp);
		$tids = C::t('forum_thread')->get_posttableid();
		if($tids) {
			foreach($tids as $posttableid => $tid) {
				foreach(C::t('forum_post')->fetch_all_by_tid($posttableid, $tid, true, '', 0, 0, 1) as $post) {
					$threadlist[$post['tid']] = array_merge($threadlist[$post['tid']], $post);
				}
			}
		}
		$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=threads&filter=$filter&modfid=$modfid&dateline={$_GET['dateline']}&username={$_GET['username']}&title={$_GET['title']}&tpp=$tpp&showcensor=$showcensor");
	}
	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> &nbsp;<a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a><p>';
	loadcache('forums');
	require_once libfile('function/misc');
	foreach($threadlist as $thread) {
		$threadsortinfo = '';
		$thread['useip'] = $thread['useip'] . '-' . convertip($thread['useip']);
		if($thread['authorid'] && $thread['author']) {
			$thread['author'] = "<a href=\"?action=members&operation=search&uid=$thread[authorid]&submit=yes\" target=\"_blank\">$thread[author]</a>";
		} elseif($thread['authorid'] && !$thread['author']) {
			$thread['author'] = "<a href=\"?action=members&operation=search&uid=$thread[authorid]&submit=yes\" target=\"_blank\">$lang[anonymous]</a>";
		} else {
			$thread['author'] = $lang['guest'];
		}

		$thread['dateline'] = dgmdate($thread['dateline']);
		$thread['message'] = discuzcode($thread['message'], $thread['smileyoff'], $thread['bbcodeoff']);
		$censor = & discuz_censor::instance();
		$censor->highlight = '#FF0000';
		if($showcensor) {
			$censor->check($thread['subject']);
			$censor->check($thread['message']);
		}
		$thread['modthreadkey'] = modauthkey($thread['tid']);
		$censor_words = $censor->words_found;
		if(count($censor_words) > 3) {
			$censor_words = array_slice($censor_words, 0, 3);
		}
		$thread['censorwords'] = implode(', ', $censor_words);

		if($thread['attachment']) {
			require_once libfile('function/attachment');

			foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$thread['tid'], 'tid', $thread['tid']) as $attach) {
				$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['url'] = $attach['isimage']
						? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
						 : "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
				$thread['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])."\t").$attach['url'];
			}
		}

		if($thread['sortid']) {
			require_once libfile('function/threadsort');
			$threadsortshow = threadsortshow($thread['sortid'], $thread['tid']);

			foreach($threadsortshow['optionlist'] as $option) {
				$threadsortinfo .= $option['title'].' '.$option['value']."<br />";
			}
		}

		if(count($censor_words)) {
			$thread_censor_text = "<span style=\"color: red;\">($thread[censorwords])</span>";
		} else {
			$thread_censor_text = '';
		}
		$forumname = $_G['cache']['forums'][$thread['fid']]['name'];
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$thread[tid]_row1\"", array("id=\"mod_$thread[tid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_1\" value=\"validate\" onclick=\"mod_setbg($thread[tid], 'validate');\"><label for=\"mod_$thread[tid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_2\" value=\"delete\" onclick=\"mod_setbg($thread[tid], 'delete');\"><label for=\"mod_$thread[tid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_3\" value=\"ignore\" onclick=\"mod_setbg($thread[tid], 'ignore');\"><label for=\"mod_$thread[tid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$thread[tid]');\">$thread[subject]</a> $thread_censor_text</h3><p>$thread[useip]</p>",
			"<a target=\"_blank\" href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\">$forumname</a>",
			"<p>$thread[author]</p> <p>$thread[dateline]</p>",
			"<a target=\"_blank\" href=\"forum.php?mod=viewthread&tid=$thread[tid]&modthreadkey=$thread[modthreadkey]\">$lang[view]</a>&nbsp;<a href=\"forum.php?mod=post&action=edit&fid=$thread[fid]&tid=$thread[tid]&pid=$thread[pid]&modthreadkey=$thread[modthreadkey]\" target=\"_blank\">$lang[edit]</a>",
		));
		showtablerow("id=\"mod_$thread[tid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:120px; word-break: break-all;">'.$thread['message'].'<br /><br />'.$threadsortinfo.'</div>');
		showtablerow("id=\"mod_$thread[tid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=threads&fast=1&fid=$thread[fid]&tid=$thread[tid]&moderate[$thread[tid]]=validate&page=$page&frame=no\" onclick=\"this.href = this.href + '&pm_$thread[tid]=' + encodeURI($('pm_$thread[tid]').value)\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=threads&fast=1&fid=$thread[fid]&tid=$thread[tid]&moderate[$thread[tid]]=delete&page=$page&frame=no\" onclick=\"this.href = this.href + '&pm_$thread[tid]=' + encodeURI($('pm_$thread[tid]').value)\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=threads&fast=1&fid=$thread[fid]&tid=$thread[tid]&moderate[$thread[tid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a> | <a href=\"forum.php?mod=post&action=edit&fid=$thread[fid]&tid=$thread[tid]&pid=$thread[pid]&page=1&modthreadkey=$thread[modthreadkey]\" target=\"_blank\">".$lang['moderate_edit_thread']."</a> &nbsp;&nbsp;|&nbsp;&nbsp; ".$lang['moderate_reasonpm']."&nbsp; <input type=\"text\" class=\"txt\" name=\"pm_$thread[tid]\" id=\"pm_$thread[tid]\" style=\"margin: 0px;\"> &nbsp; <select style=\"margin: 0px;\" onchange=\"$('pm_$thread[tid]').value=this.value\">$modreasonoptions</select>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a> &nbsp;<label><input class="checkbox" type="checkbox" name="apply_all" id="chk_apply_all"  value="1" disabled="disabled" />'.cplang('moderate_apply_all').'</label>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$validates = $ignores = $recycles = $deletes = 0;
	$validatedthreads = $pmlist = array();
	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());

	if(is_array($moderate)) {
		foreach($moderate as $tid => $act) {
			$moderation[$act][] = intval($tid);
		}
	}

	if($_GET['apply_all']) {
		$apply_all_action = $_GET['apply_all'];
		$author = $dateline = $isgroup = $displayorder = null;
		if($filter == 'ignore') {
			$displayorder = -3;
		} else {
			$displayorder = -2;
		}
		if($modfid == -1) {
			$isgroup = 1;
		}
		if(!empty($_GET['dateline']) && $_GET['dateline'] != 'all') {
			$dateline = $_GET['dateline'];
		}
		foreach(C::t('forum_thread')->fetch_all_moderate($modfid, $displayorder, $isgroup, $dateline, $_GET['username'], $_GET['title']) as $thread) {
			switch($apply_all_action) {
				case 'validate':
					$moderation['validate'][] = $thread['tid'];
					break;
				case 'delete':
					$moderation['delete'][] = $thread['tid'];
					break;
				case 'ignore':
					$moderation['ignore'][] = $thread['tid'];
					break;
			}
		}
	}

	if($moderation['ignore']) {
		$ignores = C::t('forum_thread')->update_displayorder_by_tid_displayorder($moderation['ignore'], -2, -3);
		updatemoderate('tid', $moderation['ignore'], 1);
	}

	if($moderation['delete']) {
		$deletetids = array();
		$recyclebintids = array();
		foreach(C::t('forum_thread')->fetch_all_by_tid_displayorder($moderation['delete'], $displayorder, '>=', $fidadd[fids]) as $thread) {
			if($recyclebins[$thread['fid']]) {
				$recyclebintids[] = $thread['tid'];
			} else {
				$deletetids[] = $thread['tid'];
			}
			$pm = 'pm_'.$thread['tid'];
			if($thread['authorid'] && $thread['authorid'] != $_G['uid']) {
				$pmlist[] = array(
					'action' =>  $_GET[$pm] ? 'modthreads_delete_reason' : 'modthreads_delete',
					'notevar' => array('threadsubject' => $thread['subject'], 'reason' => $_GET[$pm]),
					'authorid' => $thread['authorid'],
				);
			}
		}
		require_once libfile('function/delete');
		if($recyclebintids) {
			$recycles = deletethread($recyclebintids, false, false, true);
			updatemodworks('MOD', $recycles);
			updatemodlog(implode(',', $recyclebintids), 'DEL');
		}

		$deletes = deletethread($deletetids);
		updatemoderate('tid', $moderation['delete'], 2);
	}

	if($moderation['validate']) {
		require_once libfile('function/forum');
		$forums = array();

		$tids = $authoridarray = $moderatedthread = array();
		$firsttime_validatethread = array();
		$uids = array();
		foreach(C::t('forum_thread')->fetch_all_by_tid_fid($moderation['validate'], $fidadd['fids']) as $thread) {
			if($thread['displayorder'] != -2 && $thread['displayorder']!= -3) {
				continue;
			}
			$poststatus = C::t('forum_post')->fetch_threadpost_by_tid_invisible($thread['tid']);
			$thread['anonymous'] = $poststatus['anonymous'];
			$thread['message'] = $poststatus['message'];
			$poststatus = $poststatus['status'];
			$tids[] = $thread['tid'];

			if(getstatus($poststatus, 3) == 0) {
				$firsttime_validatethread[] = $thread;
				$uids[] = $thread['authorid'];
				updatepostcredits('+', $thread['authorid'], 'post', $thread['fid']);
				$attachcount = C::t('forum_attachment_n')->count_by_id('tid:'.$thread['tid'], 'tid', $thread['tid']);
				updatecreditbyaction('postattach', $thread['authorid'], array(), '', $attachcount, 1, $thread['fid']);
			}

			$forums[] = $thread['fid'];
			$validatedthreads[] = $thread;

			$pm = 'pm_'.$thread['tid'];
			if($thread['authorid'] && $thread['authorid'] != $_G['uid']) {
				$pmlist[] = array(
					'action' => 'modthreads_validate',
					'notevar' => array('tid' => $thread['tid'], 'threadsubject' => $thread['subject'], 'reason' => dhtmlspecialchars($_GET[''.$pm]), 'from_id' => 0, 'from_idtype' => 'modthreads'),
					'authorid' => $thread['authorid'],
				);
			}
		}
		if($firsttime_validatethread) {
			require_once libfile('function/post');
			require_once libfile('function/feed');
			$forumsinfo = C::t('forum_forum')->fetch_all_info_by_fids($forums);
			$users = array();
			foreach ($uids as $uid) {
				$space = array('uid'=>$uid);
				space_merge($space, 'field_home');
				$users[$uid] = $space;
			}
			foreach ($firsttime_validatethread as $thread) {
				if($forumsinfo[$thread['fid']] && $forumsinfo[$thread['fid']]['allowfeed'] && $users[$thread['authorid']]['privacy']['feed']['newthread'] && !$thread['anonymous']) {
					$feed = array(
						'icon' => 'thread',
						'title_template' => 'feed_thread_title',
						'title_data' => array(),
						'body_template' => 'feed_thread_message',
						'body_data' => array(),
						'title_data' => array(),
						'images' => array()
					);

					$message = !$thread['price'] && !$thread['readperm'] ? $thread['message'] : '';
					$message = messagesafeclear($message);
					$feed['body_data'] = array(
						'subject' => "<a href=\"forum.php?mod=viewthread&tid={$thread['tid']}\">{$thread['subject']}</a>",
						'message' => messagecutstr($message, 150)
					);
					$feed['title_data']['hash_data'] = 'tid'.$thread['tid'];
					$feed['id'] = $thread['tid'];
					$feed['idtype'] = 'tid';
					feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], '', $feed['images'], $feed['image_links'], '', '', '', 0, $feed['id'], $feed['idtype'],$thread['authorid'], $thread['author']);
				}
			}
		}
		if($tids) {

			$tidstr = dimplode($tids);
			C::t('forum_post')->update_by_tid(0, $tids, array('status' => 4), false, false, null, -2, 0);
			loadcache('posttableids');
			$posttableids = $_G['cache']['posttableids'] ? $_G['cache']['posttableids'] : array('0');
			foreach($posttableids as $id) {
				C::t('forum_post')->update_by_tid($id, $tids, array('invisible' => '0'), false, false, 1);
			}
			$validates = C::t('forum_thread')->update($tids, array('displayorder' => 0, 'moderated' => 1));

			foreach(array_unique($forums) as $fid) {
				updateforumcount($fid);
			}

			updatemodworks('MOD', $validates);
			updatemodlog($tidstr, 'MOD');
			updatemoderate('tid', $tids, 2);

		}
	}

	if($pmlist) {
		foreach($pmlist as $pm) {
			notification_add($pm['authorid'], 'system', $pm['action'], $pm['notevar'], 1);
		}
	}
	if($_GET['fast']) {
		echo callback_js($_GET['tid']);
		exit;
	} else {
		cpmsg('moderate_threads_succeed', "action=moderate&operation=threads&page=$page&filter=$filter&modfid=$modfid&username={$_GET['username']}&title={$_GET['title']}&tpp={$_GET['tpp']}&showcensor=$showcensor&dateline={$_GET['dateline']}", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
	}

}

?>