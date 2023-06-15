<?php

/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: admincp_recyclebinpost.php 28728 2012-03-09 03:15:48Z songlixin $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

require_once libfile('function/post');
require_once libfile('function/discuzcode');

$posttableid = intval($_GET['posttableid']);

cpheader();

if(submitcheck('rbsubmit')) {
	$moderate = $_GET['moderate'];
	$moderation = array('delete' => array(), 'undelete' => array(), 'ignore' => array());
	if(is_array($moderate)) {
		foreach($moderate as $pid => $action) {
			$moderation[$action][] = intval($pid);
		}
	}

	$postsdel = $postsundel = 0;
	if($moderation['delete']) {
		$postsdel = recyclebinpostdelete($moderation['delete'], $posttableid);
	}
	if($moderation['undelete']) {
		$postsundel = recyclebinpostundelete($moderation['undelete'], $posttableid);
	}

	if($operation == 'search') {
		$cpmsg = cplang('recyclebinpost_succeed', array('postsdel' => $postsdel, 'postsundel' => $postsundel));
?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('rbsearchform').searchsubmit.click();</script>
<?php
	} else {
		cpmsg('recyclebinpost_succeed', 'action=recyclebinpost&operation='.$operation, 'succeed', array('postsdel' => $postsdel, 'postsundel' => $postsundel));
	}
}

$lpp = empty($_GET['lpp']) ? 20 : $_GET['lpp'];
$start = ($page - 1) * $lpp;
$start_limit = ($page - 1) * $lpp;
$multi = '';

if(!$operation) {
	shownav('topic', 'nav_recyclebinpost');
	showsubmenu('nav_recyclebinpost', array(
		array('recyclebinpost_list', 'recyclebinpost', 1),
		array('search', 'recyclebinpost&operation=search', 0),
		array('clean', 'recyclebinpost&operation=clean', 0)
	));
	showtagheader('div', 'postlist', 1);
	showformheader('recyclebinpost', '', 'rbform');
	showhiddenfields(array('posttableid' => $posttableid));
	$checklpp = array();
	$checklpp[$lpp] = 'selected="selected"';
	showtableheader($lang['recyclebinpost_list'].
				'&nbsp<select onchange="if(this.options[this.selectedIndex].value != \'\') {window.location=\''.ADMINSCRIPT.'?action=recyclebinpost&lpp=\'+this.options[this.selectedIndex].value }">
				<option value="20" '.$checklpp[20].'> '.$lang[perpage_20].' </option><option value="50" '.$checklpp[50].'>'.$lang[perpage_50].'</option><option value="100" '.$checklpp[100].'>'.$lang[perpage_100].'</option></select>');

	$postlistcount = C::t('forum_post')->count_by_invisible($posttableid, '-5');

	if($postlistcount && recyclebinpostshowpostlist(null, null, null, null, null, $start_limit, $lpp)) {
		$multi = multi($postlistcount, $lpp, $page, ADMINSCRIPT."?action=recyclebinpost&lpp=$lpp");
	}
	showsubmit('rbsubmit', 'submit', '', '<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'delete\')">'.cplang('recyclebin_all_delete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'undelete\')">'.cplang('recyclebin_all_undelete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'ignore\')">'.cplang('recyclebin_all_ignore').'</a> &nbsp;', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="rbframe" style="display:none"></iframe>';
	showtagfooter('div');

} elseif($operation == 'search') {

	$inforum = $_GET['inforum'];
	$authors = $_GET['authors'];
	$keywords = $_GET['keywords'];
	$pstarttime = $_GET['pstarttime'];
	$pendtime = $_GET['pendtime'];

	$secStatus = false;

	$searchsubmit = $_GET['searchsubmit'];

	require_once libfile('function/forumlist');

	$forumselect = '<select name="inforum"><option value="">&nbsp;&nbsp;> '.$lang['allthread'].'</option>'.
		'<option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';

	if($inforum) {
		$forumselect = preg_replace("/(\<option value=\"$inforum\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
	}

	shownav('topic', 'nav_recyclebinpost');
	showsubmenu('nav_recyclebinpost', array(
		array('recyclebinpost_list', 'recyclebinpost', 0),
		array('search', 'recyclebinpost&operation=search', 1),
		array('clean', 'recyclebinpost&operation=clean', 0)
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
	showtagheader('div', 'postsearch', !$searchsubmit);
	showformheader('recyclebinpost&operation=search', '', 'rbsearchform');
	showhiddenfields(array('page' => $page));
	showtableheader('recyclebinpost_search');
	showsetting('recyclebinpost_search_forum', '', '', $forumselect);
	showsetting('recyclebinpost_search_author', 'authors', $authors, 'text');
	showsetting('recyclebinpost_search_keyword', 'keywords', $keywords, 'text');
	showsetting('recyclebin_search_post_time', array('pstarttime', 'pendtime'), array($pstarttime, $pendtime), 'daterange');
	showsetting('postsplit', '', '', getposttableselect());
	if($secStatus){
        showsetting('recyclebin_search_security_thread', 'security', $security, 'radio');
	}
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

	if(submitcheck('searchsubmit')) {

		$security = $secStatus && $security;
		if($security){
			$postlistcount = C::t('#security#security_evilpost')->count_by_search($posttableid, null, $keywords, -5, $inforum, null, ($authors ? explode(',', str_replace(' ', '', $authors)) : null), strtotime($pstarttime), strtotime($pendtime));
		}else{
			$postlistcount = C::t('forum_post')->count_by_search($posttableid, null, $keywords, -5, $inforum, null, ($authors ? explode(',', str_replace(' ', '', $authors)) : null), strtotime($pstarttime), strtotime($pendtime));
		}

		showtagheader('div', 'postlist', $searchsubmit);
		showformheader('recyclebinpost&operation=search&frame=no', 'target="rbframe"', 'rbform');
		showtableheader(cplang('recyclebinpost_result').' '.$postlistcount.' <a href="#" onclick="$(\'postlist\').style.display=\'none\';$(\'postsearch\').style.display=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

		if($postlistcount && recyclebinpostshowpostlist($inforum, $authors, $pstarttime, $pendtime, $keywords, $start_limit, $lpp)) {
			$multi = multi($postlistcount, $lpp, $page, ADMINSCRIPT."?action=recyclebinpost");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=recyclebinpost&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=recyclebinpost&amp;page='+this.value", "page(this.value)", $multi);
		}

		showsubmit('rbsubmit', 'submit', '', '<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'delete\')">'.cplang('recyclebin_all_delete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'undelete\')">'.cplang('recyclebin_all_undelete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'ignore\')">'.cplang('recyclebin_all_ignore').'</a> &nbsp;', $multi);
		showtablefooter();
		showformfooter();
		echo '<iframe name="rbframe" style="display:none"></iframe>';
		showtagfooter('div');
	}

} elseif($operation == 'clean') {

	if(!submitcheck('cleanrbsubmit', 1)) {

		shownav('topic', 'nav_recyclebinpost');
		showsubmenu('nav_recyclebinpost', array(
			array('recyclebinpost_list', 'recyclebinpost', 0),
			array('search', 'recyclebinpost&operation=search', 0),
			array('clean', 'recyclebinpost&operation=clean', 1)
		));
		showformheader('recyclebinpost&operation=clean');
		showtableheader('recyclebinpost_clean');
		showsetting('recyclebinpost_clean_days', 'days', '30', 'text');
		showsubmit('cleanrbsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$deletetids = array();
		$pernum = 200;
		$postsdel = intval($_GET['postsdel']);
		$days = intval($_GET['days']);
		$timestamp = TIMESTAMP - max(0, $days * 86400);

		$postlist = array();
		loadcache('posttableids');
		$posttables = !empty($_G['cache']['posttableids']) ? $_G['cache']['posttableids'] : array(0);
		foreach($posttables as $ptid) {
			foreach(C::t('forum_post')->fetch_all_pid_by_invisible_dateline($ptid, -5, $timestamp, 0, $pernum) as $post) {
				$postlist[$ptid][] = $post['pid'];
			}
		}
		$postsundel = 0;
		if($postlist) {
			foreach($postlist as $ptid => $deletepids) {
				$postsdel += recyclebinpostdelete($deletepids, $ptid);
			}
			$startlimit += $pernum;
			cpmsg('recyclebinpost_clean_next', 'action=recyclebinpost&operation=clean&cleanrbsubmit=1&days='.$days.'&postsdel='.$postsdel, 'succeed', array('postsdel' => $postsdel));
		} else {
			cpmsg('recyclebinpost_succeed', 'action=recyclebinpost&operation=clean', 'succeed', array('postsdel' => $postsdel, 'postsundel' => $postsundel));
		}
	}
}

function recyclebinpostshowpostlist($fid, $authors, $starttime, $endtime, $keywords, $start_limit, $lpp) {
	global $_G, $lang, $posttableid, $security;

	$tids = $fids = array();

	if($security){
		$postlist = C::t('#security#security_evilpost')->fetch_all_by_search($posttableid, null, $keywords, -5, $fid, null, ($authors ? explode(',', str_replace(' ', '', $authors)): null), strtotime($starttime), strtotime($endtime), null, null, $start_limit, $lpp);
	}else{
		$postlist = C::t('forum_post')->fetch_all_by_search($posttableid, null, $keywords, -5, $fid, null, ($authors ? explode(',', str_replace(' ', '', $authors)): null), strtotime($starttime), strtotime($endtime), null, null, $start_limit, $lpp);
	}

	if(empty($postlist)) return false;

	foreach($postlist as $key => $post) {
		$tids[$post['tid']] = $post['tid'];
		$fids[$post['fid']] = $post['fid'];
	}
	foreach(C::t('forum_thread')->fetch_all_by_tid($tids) as $thread) {
		$thread['tsubject'] = $thread['subject'];
		$threadlist[$thread['tid']] = $thread;
	}
	$query = C::t('forum_forum')->fetch_all_by_fid($fids);
	foreach($query as $val) {
		$forum = array('fid' => $val['fid'],
			'forumname' => $val['name'],
			'allowsmilies' => $val['allowsmilies'],
			'allowhtml' => $val['allowhtml'],
			'allowbbcode' => $val['allowbbcode'],
			'allowimgcode' => $val['allowimgcode']
			);
		$forumlist[$forum['fid']] = $forum;
	}

	foreach($postlist as $key => $post) {
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $forumlist[$post['fid']]['allowsmilies'], $forumlist[$post['fid']]['allowbbcode'], $forumlist[$post['fid']]['allowimgcode'], $forumlist[$post['fid']]['allowhtml']);
		$post['dateline'] = dgmdate($post['dateline']);
		if($post['attachment']) {
			require_once libfile('function/attachment');
			foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$post['tid'], 'pid', $post['pid']) as $attach) {
				$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['url'] = $attach['isimage']
					? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 100) {this.resized=true; this.width=100;}\">"
					 : "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
				$post['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])."\t").$attach['url'];
			}
		}

		showtablerow("id=\"mod_$post[pid]_row1\"", array('rowspan="3" class="rowform threadopt" style="width:80px;"', 'class="threadtitle"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_1\" value=\"delete\" checked=\"checked\" /><label for=\"mod_$post[pid]_1\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_2\" value=\"undelete\" /><label for=\"mod_$post[pid]_2\">$lang[undelete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_3\" value=\"ignore\" /><label for=\"mod_$post[pid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"forum.php?mod=forumdisplay&fid=$post[fid]\" target=\"_blank\">".$forumlist[$post['fid']]['forumname']."</a> &raquo; <a href=\"forum.php?mod=viewthread&tid=$post[tid]\" target=\"_blank\">".$threadlist[$post['tid']]['tsubject']."</a>".($post['subject'] ? ' &raquo; '.$post['subject'] : '')."</h3><p><span class=\"bold\">$lang[author]:</span> <a href=\"home.php?mod=space&uid=$post[authorid]\" target=\"_blank\">$post[author]</a> &nbsp;&nbsp; <span class=\"bold\">$lang[time]:</span> $post[dateline] &nbsp;&nbsp; IP: $post[useip]</p>"
		));
		showtablerow("id=\"mod_$post[pid]_row2\"", 'colspan="2" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:120px; word-break: break-all;">'.$post['message'].'</div>');
		showtablerow("id=\"mod_$post[pid]_row3\"", 'class="threadopt threadtitle" colspan="2"', "$lang[isanonymous]: ".($post['anonymous'] ? $lang['yes'] : $lang['no'])." &nbsp;&nbsp; $lang[ishtmlon]: ".($post['htmlon'] ? $lang['yes'] : $lang['no']));
	}
	return true;
}
?>