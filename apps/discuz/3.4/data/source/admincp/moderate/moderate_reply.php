<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_reply.php 32501 2013-01-29 09:51:00Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

loadcache('posttableids');
$posttable = in_array($_GET['posttableid'], $_G['cache']['posttableids']) ? $_GET['posttableid'] : 0;

if(!submitcheck('modsubmit') && !$_GET['fast']) {

	require_once libfile('function/discuzcode');

	$select[$_GET['ppp']] = $_GET['ppp'] ? "selected='selected'" : '';
	$ppp_options = "<option value='20' $select[20]>20</option><option value='50' $select[50]>50</option><option value='100' $select[100]>100</option>";
	$ppp = !empty($_GET['ppp']) ? $_GET['ppp'] : '20';
	$start_limit = ($page - 1) * $ppp;
	$dateline = $_GET['dateline'] ? $_GET['dateline'] : '604800';
	$dateline_options = '';
	foreach(array('all', '604800', '2592000', '7776000') as $v) {
		$selected = '';
		if($dateline == $v) {
			$selected = "selected='selected'";
		}
		$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
	}

	$posttableselect = getposttableselect();

	shownav('topic', $lang['moderate_replies']);
	showsubmenu('nav_moderate_posts', $submenu);

	showformheader("moderate&operation=replies");
	showtableheader('search');

	showtablerow('', array('width="60"', 'width="160"', 'width="60"', $posttableselect ? 'width="160"' : '', $posttableselect ? 'width="60"' : ''),
		array(
			cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_GET[username]\" />",
			cplang('moderate_content_keyword'), "<input size=\"15\" name=\"title\" type=\"text\" value=\"$_GET[title]\" />",
			$posttableselect ? cplang('postsplit_select') : '',
			$posttableselect
		)
	);
	showtablerow('', array('width="60"', 'width="160"', 'width="60"', 'colspan="3"'),
                array(
                        "$lang[perpage]",
                        "<select name=\"ppp\">$ppp_options</select><label><input name=\"showcensor\" type=\"checkbox\" class=\"checkbox\" value=\"yes\" ".($showcensor ? ' checked="checked"' : '')."/> $lang[moderate_showcensor]</label>",
                        "$lang[moderate_bound]",
                        "<select name=\"filter\">$filteroptions</select>
                        <select name=\"modfid\">$forumoptions</select>
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );

	showtablefooter();
	showtableheader();
	$fidadd = array();
	$sqlwhere = '';
	if(!empty($_GET['username'])) {
		$sqlwhere .= " AND p.author='{$_GET['username']}'";
	}
	if(!empty($dateline) && $dateline != 'all') {
		$sqlwhere .= " AND p.dateline>'".(TIMESTAMP - $dateline)."'";
	}
	if(!empty($_GET['title'])) {
		$sqlwhere .= " AND t.subject LIKE '%{$_GET['title']}%'";
	}
	if($modfid > 0) {
		$fidadd['fids'] = $modfid;
	}

	$modcount = C::t('common_moderate')->count_by_search_for_post(getposttable($posttable), $moderatestatus, 0, ($modfid > 0 ? $modfid : 0), $_GET['username'], (($dateline &&  $dateline != 'all') ? (TIMESTAMP - $dateline) : null), $_GET['title']);
	$start_limit = ($page - 1) * $ppp;
	$postarr = C::t('common_moderate')->fetch_all_by_search_for_post(getposttable($posttable), $moderatestatus, 0, ($modfid > 0 ? $modfid : 0), $_GET['username'], (($dateline &&  $dateline != 'all') ? (TIMESTAMP - $dateline) : null), $_GET['title'], $start_limit, $ppp);
	if($postarr) {
		$_tids = $_fids = array();
		foreach($postarr as $_post) {
			$_fids[$_post['fid']] = $_post['fid'];
			$_tids[$_post['tid']] = $_post['tid'];
		}
		$_forums = C::t('forum_forum')->fetch_all($_fids);
		$_threads = C::t('forum_thread')->fetch_all($_tids);
	}
	$checklength = C::t('common_moderate')->fetch_all_by_idtype('pid', $moderatestatus, null);
	if($modcount != $checklength && !$srcdate && !$modfid && !$_GET['username'] && !$_GET['title'] && !$posttable) {
		moderateswipe('pid', array_keys($checklength));
	}
	$multipage = multi($modcount, $ppp, $page, ADMINSCRIPT."?action=moderate&operation=replies&filter=$filter&modfid=$modfid&dateline={$_GET['dateline']}&username={$_GET['username']}&title={$_GET['title']}&ppp=$ppp&showcensor=$showcensor&posttableid=$posttable");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a><p>';

	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	foreach($postarr as &$post) {
		$_forum = $_forums[$post['fid']];
		$_arr = array(
			'forumname' => $_forum['name'],
			'allowsmilies' => $_forum['allowsmilies'],
			'allowhtml' => $_forum['allowhtml'],
			'allowbbcode' => $_forum['allowbbcode'],
			'allowimgcode' => $_forum['allowimgcode'],
		);
		$post = array_merge($post, $_arr);
		if(getstatus($post['status'], 5)) {
			$post['authorid'] = 0;
			$post['author'] = cplang('moderate_t_comment');
		}
		$post['dateline'] = dgmdate($post['dateline']);
		$post['tsubject'] = $_threads[$post['tid']]['subject'];
		$post['subject'] = $post['subject'] ? '<b>'.$post['subject'].'</b>' : '';
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $post['allowsmilies'], $post['allowbbcode'], $post['allowimgcode'], $post['allowhtml']);
		if($showcensor) {
			$censor->check($post['subject']);
			$censor->check($post['message']);
		}
		$post_censor_words = $censor->words_found;
		if(count($post_censor_words) > 3) {
			$post_censor_words = array_slice($post_censor_words, 0, 3);
		}
		$post['censorwords'] = implode(', ', $post_censor_words);
		$post['modthreadkey'] = modauthkey($post['tid']);
		$post['useip'] = $post['useip'] . '-' . convertip($post['useip']);

		if($post['attachment']) {
			require_once libfile('function/attachment');

			foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$post['tid'], 'pid', $post['pid']) as $attach) {
				$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['url'] = $attach['isimage']
				 		? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
					 	 : "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
				$post['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])."\t").$attach['url'];
			}
		}

		if(count($post_censor_words)) {
			$post_censor_text = "<span style=\"color: red;\">({$post['censorwords']})</span>";
		} else {
			$post_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$post[pid]_row1\"", array("id=\"mod_$post[pid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_1\" value=\"validate\" onclick=\"mod_setbg($post[pid], 'validate');\"><label for=\"mod_$post[pid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_2\" value=\"delete\" onclick=\"mod_setbg($post[pid], 'delete');\"><label for=\"mod_$post[pid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_3\" value=\"ignore\" onclick=\"mod_setbg($post[pid], 'ignore');\"><label for=\"mod_$post[pid]_3\">$lang[ignore]</label></li></ul>",
			$post['subject'] ? "<h3>$post[tsubject] &rsaquo; <a href=\"javascript:;\" onclick=\"display_toggle('$post[pid]');\">$post[subject]</a> $post_censor_text</h3><p>$post[useip]</p>" : "<h3> <a href=\"javascript:;\" onclick=\"display_toggle('$post[pid]');\">$post[tsubject] &rsaquo;</a> $post_censor_text</h3><p>$post[useip]</p>",
			"<a href=\"forum.php?mod=forumdisplay&fid=$post[fid]\">$post[forumname]</a>",
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$post[authorid]&submit=yes\">$post[author]</a></p> <p>$post[dateline]</p>",
			"<a target=\"_blank\" href=\"forum.php?mod=redirect&goto=findpost&ptid=$post[tid]&pid=$post[pid]\">$lang[view]</a>&nbsp;<a href=\"forum.php?mod=viewthread&tid=$post[tid]&modthreadkey=$post[modthreadkey]\" target=\"_blank\">$lang[edit]</a>",
		));
		showtablerow("id=\"mod_$post[pid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$post['message'].'</div>');
		showtablerow("id=\"mod_$post[pid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=replies&fast=1&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]&moderate[$post[pid]]=validate&page=$page&posttableid=$posttable&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=replies&fast=1&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]&moderate[$post[pid]]=delete&page=$page&posttableid=$posttable&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=replies&fast=1&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]&moderate[$post[pid]]=ignore&page=$page&posttableid=$posttable&frame=no\" target=\"fasthandle\">$lang[ignore]</a>&nbsp;&nbsp;|&nbsp;&nbsp; ".$lang['moderate_reasonpm']."&nbsp; <input type=\"text\" class=\"txt\" name=\"pm_$post[pid]\" id=\"pm_$post[pid]\" style=\"margin: 0px;\"> &nbsp; <select style=\"margin: 0px;\" onchange=\"$('pm_$post[pid]').value=this.value\">$modreasonoptions</select>");
		showtagfooter('tbody');

	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a> &nbsp;<label><input class="checkbox" type="checkbox" name="apply_all" id="chk_apply_all"  value="1" disabled="disabled" />'.cplang('moderate_apply_all').'</label>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$pmlist = array();
	$validates = $ignores = $deletes = 0;

	if(is_array($moderate)) {
		foreach($moderate as $pid => $act) {
			$moderation[$act][] = intval($pid);
		}
	}

	if($_GET['apply_all']) {
		$apply_all_action = $_GET['apply_all'];
		$first = '0';
		if($filter == 'ignore') {
			$invisible = '-3';
		} else {
			$invisible = '-2';
		}
		if($modfid > 0) {
			$modfid = $modfid;
		}
		if(!empty($_GET['dateline']) && $_GET['dateline'] != 'all') {
			$starttime = $_GET['dateline'];
		}
		if(!empty($_GET['username'])) {
			$author = $_GET['username'];
		}
		if(!empty($_GET['title'])) {
			$title = str_replace(array('_', '%'), array('\_', '\%'), $_GET['title']);
			$keywords = $title;
		}
		foreach(C::t('forum_post')->fetch_all_by_search($posttable, null, $keywords, $invisible, $modfid, null, $author, $starttime, null, null, $first) as $post) {
			switch($apply_all_action) {
				case 'validate':
					$moderation['validate'][] = $post['pid'];
					break;
				case 'delete':
					$moderation['delete'][] = $post['pid'];
					break;
				case 'ignore':
					$moderation['ignore'][] = $post['pid'];
					break;
			}
		}
	}
	if($ignorepids = dimplode($moderation['ignore'])) {
		$ignores = C::t('forum_post')->update($posttable, $moderation['ignore'], array('invisible' => -3), false, false, 0, -2, $fidadd[fids]);
		updatemoderate('pid', $moderation['ignore'], 1);
	}

	if($deletepids = dimplode($moderation['delete'])) {
		$pids = $recyclebinpids = array();
		foreach(C::t('forum_post')->fetch_all($posttable, $moderation['delete']) as $post) {
			if($post['invisible'] != $displayorder || $post['first'] != 0 || ($fidadd['fids'] && $post['fid'] != $fidadd['fids'])) {
				continue;
			}
			if($recyclebins[$post['fid']]) {
				$recyclebinpids[] = $post['pid'];
			} else {
				$pids[] = $post['pid'];
			}
			$pm = 'pm_'.$post['pid'];
			if($post['authorid'] && $post['authorid'] != $_G['uid']) {
				$pmlist[] = array(
					'action' => $_GET[''.$pm] ? 'modreplies_delete_reason' : 'modreplies_delete',
					'notevar' => array('pid' => $post['pid'], 'post' => dhtmlspecialchars(cutstr($post['message'], 30)), 'reason' => dhtmlspecialchars($_GET[''.$pm])),
					'authorid' => $post['authorid'],
				);
			}
		}
		require_once libfile('function/delete');
		if($recyclebinpids) {
			deletepost($recyclebinpids, 'pid', false, $posttable, true);
		}
		if($pids) {
			$deletes = deletepost($pids, 'pid', false, $posttable);
		}
		$deletes += count($recyclebinpids);
		updatemodworks('DLP', count($moderation['delete']));
		updatemoderate('pid', $moderation['delete'], 2);
	}

	if($validatepids = dimplode($moderation['validate'])) {
		$forums = $threads = $attachments = $pidarray = $authoridarray = array();
		$tids = $postlist = array();
		foreach(C::t('forum_post')->fetch_all($posttable, $moderation['validate']) as $post) {
			if($post['first'] != 0) {
				continue;
			}
			$tids[$post['tid']] = $post['tid'];
			$postlist[] = $post;
		}
		$threadlist = C::t('forum_thread')->fetch_all($tids);
		$firsttime_validatepost = array();
		$uids = array();
		foreach($postlist as $post) {
			$post['lastpost'] = $threadlist[$post['tid']]['lastpost'];

			$pidarray[] = $post['pid'];
			if(getstatus($post['status'], 3) == 0) {
				$post['subject'] = $threadlist[$post['tid']]['subject'];
				$firsttime_validatepost[] = $post;
				$uids[] = $post['authorid'];
				updatepostcredits('+', $post['authorid'], 'reply', $post['fid']);
				$attachcount = C::t('forum_attachment_n')->count_by_id('tid:'.$post['tid'], 'pid', $post['pid']);
				updatecreditbyaction('postattach', $post['authorid'], array(), '', $attachcount, 1, $post['fid']);
			}

			$forums[] = $post['fid'];


			$threads[$post['tid']]['replies']++;
			if($post['dateline'] > $post['lastpost']) {
				$threads[$post['tid']]['lastpost'] = array($post['dateline']);
				$threads[$post['tid']]['lastposter'] = array($post['anonymous'] && $post['dateline'] != $post['lastpost'] ? '' : $post['author']);
			}
			if($threads[$post['tid']]['attachadd'] || $post['attachment']) {
				$threads[$post['tid']]['attachment'] = array(1);
			}

			$pm = 'pm_'.$post['pid'];
			if($post['authorid'] && $post['authorid'] != $_G['uid']) {
				$pmlist[] = array(
					'action' => 'modreplies_validate',
					'notevar' => array('pid' => $post['pid'], 'tid' => $post['tid'], 'post' => dhtmlspecialchars(cutstr($post['message'], 30)), 'reason' => dhtmlspecialchars($_GET[''.$pm]), 'from_id' => 0, 'from_idtype' => 'modreplies'),
					'authorid' => $post['authorid'],
				);
			}
		}
		unset($postlist, $tids, $threadlist);
		if($firsttime_validatepost) {
			require_once libfile('function/post');
			require_once libfile('function/feed');
			$forumsinfo = C::t('forum_forum')->fetch_all_info_by_fids($forums);
			$users = array();
			foreach ($uids as $uid) {
				$space = array('uid'=>$uid);
				space_merge($space, 'field_home');
				$users[$uid] = $space;
			}
			foreach ($firsttime_validatepost as $post) {
				if($forumsinfo[$post['fid']] && $forumsinfo[$post['fid']]['allowfeed'] && $users[$post['authorid']]['privacy']['feed']['newreply'] && !$post['anonymous']) {
					$feed = array(
						'icon' => 'post',
						'title_template' => 'feed_reply_title',
						'title_data' => array(),
						'images' => array()
					);
					$post_url = "forum.php?mod=redirect&goto=findpost&pid=".$post['pid']."&ptid=".$post['tid'];
					$feed['title_data'] = array(
						'subject' => "<a href=\"$post_url\">".$post['subject']."</a>",
						'author' => "<a href=\"home.php?mod=space&uid=".$post['authorid']."\">".$post['author']."</a>"
					);
					$feed['title_data']['hash_data'] = 'tid'.$post['tid'];
					$feed['id'] = $post['pid'];
					$feed['idtype'] = 'pid';
					feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], '', $feed['images'], $feed['image_links'], '', '', '', 0, $feed['id'], $feed['idtype'],$post['authorid'], $post['author']);
				}
			}
		}

		foreach($threads as $tid => $thread) {
			C::t('forum_thread')->increase($tid, $thread);
		}

		foreach(array_unique($forums) as $fid) {
			updateforumcount($fid);
		}

		if(!empty($pidarray)) {
			C::t('forum_post')->update($posttable, $pidarray, array('status' => 4), false, false, null, -2, null, 0);
			$validates = C::t('forum_post')->update($posttable, $pidarray, array('invisible' => 0));
			updatemodworks('MOD', $validates);
			updatemoderate('pid', $pidarray, 2);
		} else {
		    require_once libfile('function/forum');
			updatemodworks('MOD', 1);
		}
	}

	if($pmlist) {
		foreach($pmlist as $pm) {
			notification_add($pm['authorid'], 'system', $pm['action'], $pm['notevar'], 1);
		}
	}
	if($_GET['fast']) {
		echo callback_js($_GET['pid']);
		exit;
	} else {
		cpmsg('moderate_replies_succeed', "action=moderate&operation=replies&page=$page&filter=$filter&modfid=$modfid&posttableid=$posttable&dateline={$_GET['dateline']}&username={$_GET['username']}&title={$_GET['title']}&ppp={$_GET['ppp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
	}

}

?>