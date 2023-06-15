<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_recyclebin.php 31045 2012-07-12 01:55:00Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/post');
require_once libfile('function/discuzcode');

cpheader();

if(!$operation) {

	shownav('topic', 'nav_recyclebin');

	if(!submitcheck('delsubmit') && !submitcheck('undelsubmit')) {

		showsubmenu('nav_recyclebin', array(
			array('recyclebin_list', 'recyclebin', 1),
			array('search', 'recyclebin&operation=search', 0),
			array('clean', 'recyclebin&operation=clean', 0)
		));
		$lpp = empty($_GET['lpp']) ? 20 : $_GET['lpp'];
		$start = ($page - 1) * $lpp;
		$start_limit = ($page - 1) * $lpp;
		$checklpp = array();
		$checklpp[$lpp] = 'selected="selected"';
		showformheader('recyclebin');
		showtableheader($lang['recyclebin_list'].
				'&nbsp<select onchange="if(this.options[this.selectedIndex].value != \'\') {window.location=\''.ADMINSCRIPT.'?action=recyclebin&lpp=\'+this.options[this.selectedIndex].value }">
				<option value="20" '.$checklpp[20].'> '.$lang[perpage_20].' </option><option value="50" '.$checklpp[50].'>'.$lang[perpage_50].'</option><option value="100" '.$checklpp[100].'>'.$lang[perpage_100].'</option></select>');
		showsubtitle(array('', 'thread', 'recyclebin_list_thread', 'recyclebin_list_author', 'recyclebin_list_status', 'recyclebin_list_lastpost', 'recyclebin_list_operation', 'reason'));
		$fids = $threadlist = array();
		$threads = C::t('forum_thread')->fetch_all_by_tid_fid_displayorder(0, 0, -1, 'dateline', $start_limit, $lpp, '=');
		foreach($threads as $tid => $value) {
			$fids[$value['fid']] = $value['fid'];
		}
		if($fids) {
			$forums = C::t('forum_forum')->fetch_all_name_by_fid($fids);
			foreach($threads as $tid => $thread) {
				$thread['forumname'] = $forums[$thread['fid']]['name'];
				$thread['modthreadkey'] = modauthkey($thread['tid']);
				$threadlist[$thread['tid']] = $thread;
			}
		}
		if($threadlist) {
			$tids = array_keys($threadlist);
			foreach(C::t('forum_threadmod')->fetch_all_by_tid($tids) as $row) {
				if(empty($threadlist[$row['tid']]['moduid'])) {
					$threadlist[$row['tid']]['moduid'] = $row['uid'];
					$threadlist[$row['tid']]['modusername'] = $row['username'];
					$threadlist[$row['tid']]['moddateline'] = $row['dateline'];
					$threadlist[$row['tid']]['modaction'] = $row['action'];
					$threadlist[$row['tid']]['reason'] = $row['reason'];
				}
			}
			foreach($threadlist as $tid => $thread) {
				showtablerow('', array('class="td25"', '', '', 'class="td28"', 'class="td28"'), array(
					"<input type=\"checkbox\" class=\"checkbox\" name=\"threadlist[]\" value=\"$thread[tid]\">",
					'<a href="forum.php?mod=viewthread&tid='.$thread['tid'].'&modthreadkey='.$thread['modthreadkey'].'" target="_blank">'.$thread['subject'].'</a>',
					'<a href="forum.php?mod=forumdisplay&fid='.$thread['fid'].'" target="_blank">'.$thread['forumname'].'</a>',
					'<a href="home.php?mod=space&uid='.$thread['authorid'].'" target="_blank">'.$thread['author'].'</a><br /><em style="font-size:9px;color:#999999;">'.dgmdate($thread['dateline'], 'd').'</em>',
					$thread['replies'].' / '.$thread['views'],
					$thread['lastposter'].'<br /><em style="font-size:9px;color:#999999;">'.dgmdate($thread['lastpost'], 'd').'</em>',
					$thread['modusername'] ? $thread['modusername'].'<br /><em style="font-size:9px;color:#999999;">'.dgmdate($thread['moddateline'], 'd').'</em>' : '',
					$thread['reason']
				));
			}
		}


		$threadcount = C::t('forum_thread')->count_by_displayorder(-1);
		$multipage = multi($threadcount, $lpp, $page, ADMINSCRIPT."?action=recyclebin&lpp=$lpp", 0, 6);

		showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'threadlist\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;<input type="submit" class="btn" name="delsubmit" value="'.cplang('recyclebin_delete').'" />&nbsp;<input type="submit" class="btn" name="undelsubmit" value="'.cplang('recyclebin_undelete').'" />', $multipage);
		showtablefooter();
		showformfooter();
	} else {
		$threadlist = $_GET['threadlist'];
		if(empty($threadlist)) {
			cpmsg('recyclebin_none_selected', 'action=recyclebin', 'error');
		}

		$threadsundel = $threadsdel = 0;
		if(submitcheck('undelsubmit')) {
			$threadsundel = undeletethreads($threadlist);
		} elseif(submitcheck('delsubmit')) {
			require_once libfile('function/delete');
			$threadsdel = deletethread($threadlist);
		}

		cpmsg('recyclebin_succeed', 'action=recyclebin', 'succeed', array('threadsdel' => $threadsdel, 'threadsundel' => $threadsundel));

	}

} elseif($operation == 'search') {

	if(!submitcheck('rbsubmit')) {

		$inforum = $_GET['inforum'];
		$authors = $_GET['authors'];
		$keywords = $_GET['keywords'];
		$admins = $_GET['admins'];
		$pstarttime = $_GET['pstarttime'];
		$pendtime = $_GET['pendtime'];
		$mstarttime = $_GET['mstarttime'];
		$mendtime = $_GET['mendtime'];

		$secStatus = false;

		$searchsubmit = $_GET['searchsubmit'];

		require_once libfile('function/forumlist');

		$forumselect = '<select name="inforum"><option value="">&nbsp;&nbsp;> '.$lang['select'].'</option>'.
			'<option value="">&nbsp;</option><option value="groupthread">'.$lang['group_thread'].'</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';

		if($inforum) {
			$forumselect = preg_replace("/(\<option value=\"$inforum\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
		}

		shownav('topic', 'nav_recyclebin');
		showsubmenu('nav_recyclebin', array(
			array('recyclebin_list', 'recyclebin', 0),
			array('search', 'recyclebin&operation=search', 1),
			array('clean', 'recyclebin&operation=clean', 0)
		));
		echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('rbsearchform').page.value=number;
	$('rbsearchform').searchsubmit.click();
}
</script>
EOT;
		showtagheader('div', 'threadsearch', !$searchsubmit);
		showformheader('recyclebin&operation=search', '', 'rbsearchform');
		showhiddenfields(array('page' => $page));
		showtableheader('recyclebin_search');
		showsetting('recyclebin_search_forum', '', '', $forumselect);
		showsetting('recyclebin_search_author', 'authors', $authors, 'text');
		showsetting('recyclebin_search_keyword', 'keywords', $keywords, 'text');
		showsetting('recyclebin_search_admin', 'admins', $admins, 'text');
		showsetting('recyclebin_search_post_time', array('pstarttime', 'pendtime'), array($pstarttime, $pendtime), 'daterange');
		showsetting('recyclebin_search_mod_time', array('mstarttime', 'mendtime'), array($mstarttime, $mendtime), 'daterange');

		if($secStatus){
			showsetting('recyclebin_search_security_thread', 'security', $security, 'radio');
		}

		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		if(submitcheck('searchsubmit')) {

			$sql = '';
			$isgroup = $fid = 0;
			if($inforum == 'groupthread') {
				$isgroup = 1;
			} else {
				$fid = $inforum ? $inforum : 0;
			}
			$author = $authors != ''		? explode(' ', $authors) : '';
			$admins = $admins != ''			? explode(' ', $admins) : '';
			$pstarttime = $pstarttime != ''	? strtotime($pstarttime) : '';
			$pendtime = $pendtime != ''		? strtotime($pendtime) : '';
			$mstarttime = $mstarttime != ''	? strtotime($mstarttime) : '';
			$mendtime = $mendtime != ''		? strtotime($mendtime) : '';



			$security = $secStatus && $security;
			if($security){
				$threadcount = C::t('#security#security_evilpost')->count_by_recyclebine($fid, $isgroup, $author, $admins, $pstarttime, $pendtime, $mstarttime, $mendtime, $keywords);
			}else{
				$threadcount = C::t('forum_thread')->count_by_recyclebine($fid, $isgroup, $author, $admins, $pstarttime, $pendtime, $mstarttime, $mendtime, $keywords);
			}

			$pagetmp = $page;

			$multi = multi($threadcount, $_G['ppp'], $page, ADMINSCRIPT."?action=recyclebin");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=recyclebin&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=recyclebin&amp;page='+this.value", "page(this.value)", $multi);

			echo '<script type="text/JavaScript">var replyreload;function attachimg() {}</script>';
			showtagheader('div', 'threadlist', $searchsubmit);
			showformheader('recyclebin&operation=search&frame=no', 'target="rbframe"', 'rbform');
			showtableheader(cplang('recyclebin_result').' '.$threadcount.' <a href="#" onclick="$(\'threadlist\').style.display=\'none\';$(\'threadsearch\').style.display=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

			if($threadcount) {

				if($security){
					$searchresult = C::t('#security#security_evilpost')->fetch_all_by_recyclebine($fid, $isgroup, $author, $admins, $pstarttime, $pendtime, $mstarttime, $mendtime, $keywords, ($pagetmp - 1) * $_G['ppp'], $_G['ppp']);
				}else{
					$searchresult = C::t('forum_thread')->fetch_all_by_recyclebine($fid, $isgroup, $author, $admins, $pstarttime, $pendtime, $mstarttime, $mendtime, $keywords, ($pagetmp - 1) * $_G['ppp'], $_G['ppp']);
				}
				$issettids = array();
				foreach($searchresult as $thread) {
					$disabledstr = '';
					if(isset($issettids[$thread['tid']])) {
						$disabledstr = 'disabled';
					} else {
						$issettids[$thread['tid']] = $thread['tid'];
					}
					$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($thread['tid']);
					$thread = array_merge($thread, $post);
					$thread['message'] = discuzcode($thread['message'], $thread['smileyoff'], $thread['bbcodeoff'], sprintf('%00b', $thread['htmlon']), $thread['allowsmilies'], $thread['allowbbcode'], $thread['allowimgcode'], $thread['allowhtml']);
					$thread['moddateline'] = dgmdate($thread['moddateline']);
					$thread['dateline'] = dgmdate($thread['dateline']);
					if($thread['attachment']) {
						require_once libfile('function/attachment');
						foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$thread['tid'], 'tid', $thread['tid']) as $attach) {
							$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
							$attach['url'] = $attach['isimage']
								? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 100) {this.resized=true; this.width=100;}\">"
								 : "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
							$thread['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])."\t").$attach['url'];
						}
					}

					showtablerow("id=\"mod_$thread[tid]_row1\"", array('rowspan="3" class="rowform threadopt" style="width:80px;"', 'class="threadtitle"'), array(
						"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_1\" value=\"delete\" ".(empty($disabledstr) ? "checked=\"checked\"" : '')." $disabledstr /><label for=\"mod_$thread[tid]_1\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_2\" value=\"undelete\" $disabledstr/><label for=\"mod_$thread[tid]_2\">$lang[undelete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_3\" value=\"ignore\" $disabledstr/><label for=\"mod_$thread[tid]_3\">$lang[ignore]</label></li></ul>",
						"<h3><a href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\" target=\"_blank\">$thread[forumname]</a> &raquo; $thread[subject]</h3><p><span class=\"bold\">$lang[author]:</span> <a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a> &nbsp;&nbsp; <span class=\"bold\">$lang[time]:</span> $thread[dateline] &nbsp;&nbsp; $lang[threads_replies]: $thread[replies] $lang[threads_views]: $thread[views]</p>"
					));
					showtablerow("id=\"mod_$thread[tid]_row2\"", 'colspan="2" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:120px; word-break: break-all;">'.$thread['message'].'</div>');
					showtablerow("id=\"mod_$thread[tid]_row3\"", 'class="threadopt threadtitle" colspan="2"', "$lang[operator]: <a href=\"home.php?mod=space&uid=$thread[moduid]\" target=\"_blank\">$thread[modusername]</a> &nbsp;&nbsp; $lang[recyclebin_delete_time]: $thread[moddateline]&nbsp;&nbsp; $lang[reason]: $thread[reason]");
				}
			}

			showsubmit('rbsubmit', 'submit', '', '<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'delete\')">'.cplang('recyclebin_all_delete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'undelete\')">'.cplang('recyclebin_all_undelete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'ignore\')">'.cplang('recyclebin_all_ignore').'</a> &nbsp;', $multi);
			showtablefooter();
			showformfooter();
			echo '<iframe name="rbframe" style="display:none"></iframe>';
			showtagfooter('div');

		}

	} else {
		$moderate = $_GET['moderate'];
		$moderation = array('delete' => array(), 'undelete' => array(), 'ignore' => array());
		if(is_array($moderate)) {
			foreach($moderate as $tid => $action) {
				$moderation[$action][] = intval($tid);
			}
		}

		require_once libfile('function/delete');
		$threadsdel = deletethread($moderation['delete']);
		$threadsundel = undeletethreads($moderation['undelete']);
		if($threadsdel || $threadsundel) {
			$cpmsg = cplang('recyclebin_succeed', array('threadsdel' => $threadsdel, 'threadsundel' => $threadsundel));
		} else {
			$cpmsg = cplang('recyclebin_nothread');
		}

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('rbsearchform').searchsubmit.click();</script>
<?php

	}

} elseif($operation == 'clean') {

	if(!submitcheck('rbsubmit', 1)) {

		shownav('topic', 'nav_recyclebin');
		showsubmenu('nav_recyclebin', array(
			array('recyclebin_list', 'recyclebin', 0),
			array('search', 'recyclebin&operation=search', 0),
			array('clean', 'recyclebin&operation=clean', 1)
		));
		showformheader('recyclebin&operation=clean');
		showtableheader('recyclebin_clean');
		showsetting('recyclebin_clean_days', 'days', '30', 'text');
		showsubmit('rbsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$deletetids = array();
		$timestamp = TIMESTAMP;
		$pernum = 20;
		$threadsdel = intval($_GET['threadsdel']);
		$days = intval($_GET['days']);
		foreach(C::t('forum_thread')->fetch_all_recyclebin_by_dateline($timestamp-($days * 86400), 0, $pernum) as $thread) {
			$deletetids[] = $thread['tid'];
		}
		if($deletetids) {
			require_once libfile('function/delete');
			$delcount = deletethread($deletetids);
			$threadsdel += $delcount;
			$startlimit += $pernum;
			cpmsg('recyclebin_clean_next', 'action=recyclebin&operation=clean&rbsubmit=1&threadsdel='.$threadsdel.'&days='.$days, 'loading', array('threadsdel' => $threadsdel));
		} else {
			cpmsg('recyclebin_succeed', 'action=recyclebin&operation=clean', 'succeed', array('threadsdel' => $threadsdel, 'threadsundel' => 0));
		}
	}
}

?>