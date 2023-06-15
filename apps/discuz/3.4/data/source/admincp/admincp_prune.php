<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_prune.php 29900 2012-05-02 08:17:44Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$searchsubmit = $_GET['searchsubmit'];
$fromumanage = $_GET['fromumanage'] ? 1 : 0;

require_once libfile('function/misc');
loadcache('forums');

if(!submitcheck('prunesubmit')) {

	require_once libfile('function/forumlist');

	if($_G['adminid'] == 1 || $_G['adminid'] == 2) {
		$forumselect = '<select name="forums"><option value="">&nbsp;&nbsp;> '.$lang['select'].'</option>'.
			'<option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';

		if($_GET['forums']) {
			$forumselect = preg_replace("/(\<option value=\"$_GET[forums]\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
		}
	} else {
		$forumselect = $comma = '';
		$mfids = array();
		foreach(C::t('forum_moderator')->fetch_all_by_uid($_G['uid']) as $row) {
			$mfids[] = $row['fid'];
		}
		$query = C::t('forum_forum')->fetch_all_by_fid($mfids);
		foreach($query as $forum) {
			$forumselect .= $comma.$forum['name'];
			$comma = ', ';
		}
		$forumselect = $forumselect ? $forumselect : $lang['none'];
	}

	if($fromumanage) {
		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $_GET['starttime']) ? '' : $_GET['starttime'];
		$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $_GET['endtime']) ? '' : $_GET['endtime'];
	} else {
		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $_GET['starttime']) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $_GET['starttime'];
		$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $_GET['endtime']) ? dgmdate(TIMESTAMP, 'Y-n-j') : $_GET['endtime'];
	}

	shownav('topic', 'nav_prune'.($operation ? '_'.$operation : ''));
	showsubmenusteps('nav_prune'.($operation ? '_'.$operation : ''), array(
		array('prune_search', !$searchsubmit),
		array('nav_prune', $searchsubmit)
	));
	showtips('prune_tips');
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('pruneforum').page.value=number;
	$('pruneforum').searchsubmit.click();
}
</script>
EOT;

	$posttableselect = getposttableselect();
	showtagheader('div', 'searchposts', !$searchsubmit);
	showformheader("prune".($operation ? '&operation='.$operation : ''), '', 'pruneforum');
	showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
	showtableheader();
	showsetting('prune_search_detail', 'detail', $_GET['detail'], 'radio');
	if($posttableselect) {
		showsetting('prune_search_select_postsplit', '', '', $posttableselect);
	}
	if($operation != 'group') {
		showsetting('prune_search_forum', '', '', $forumselect);
	}
	showsetting('prune_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	if(!$fromumanage) {
		empty($_GET['starttime']) && $_GET['starttime'] = dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j');
	}
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsetting('prune_search_time', array('starttime', 'endtime'), array($_GET['starttime'], $_GET['endtime']), 'daterange');
	showsetting('prune_search_user', 'users', $_GET['users'], 'text');
	showsetting('prune_search_ip', 'useip', $_GET['useip'], 'text');
	showsetting('prune_search_keyword', 'keywords', $_GET['keywords'], 'text');
	showsetting('prune_search_lengthlimit', 'lengthlimit', $_GET['lengthlimit'], 'text');
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {

	$pidsdelete = $tidsdelete = array();
	$pids = authcode($_GET['pids'], 'DECODE');

	loadcache('posttableids');
	$posttable = in_array($_GET['posttableid'], $_G['cache']['posttableids']) ? $_GET['posttableid'] : 0;
	foreach(C::t('forum_post')->fetch_all($posttable, ($pids ? explode(',', $pids) : $_GET['pidarray']), false) as $post) {
		$prune['forums'][] = $post['fid'];
		$prune['thread'][$post['tid']]++;

		$pidsdelete[] = $post['pid'];
		if($post['first']) {
			$tidsdelete[] = $post['tid'];
		}
	}

	if($pidsdelete) {
		require_once libfile('function/post');
		require_once libfile('function/delete');
		$deletedposts = deletepost($pidsdelete, 'pid', !$_GET['donotupdatemember'], $posttable);
		$deletedthreads = deletethread($tidsdelete, !$_GET['donotupdatemember'], !$_GET['donotupdatemember']);

		if(count($prune['thread']) < 50) {
			foreach($prune['thread'] as $tid => $decrease) {
				updatethreadcount($tid);
			}
		} else {
			$repliesarray = array();
			foreach($prune['thread'] as $tid => $decrease) {
				$repliesarray[$decrease][] = $tid;
			}
			foreach($repliesarray as $decrease => $tidarray) {
				C::t('forum_thread')->increase($tidarray, array('replies'=>-$decrease));
			}
		}

		if($_G['setting']['globalstick']) {
			updatecache('globalstick');
		}

		foreach(array_unique($prune['forums']) as $fid) {
			updateforumcount($fid);
		}

	}

	$deletedthreads = intval($deletedthreads);
	$deletedposts = intval($deletedposts);
	updatemodworks('DLP', $deletedposts);
	$cpmsg = cplang('prune_succeed', array('deletedthreads' => $deletedthreads, 'deletedposts' => $deletedposts));

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('pruneforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit', 1)) {

	loadcache('posttableids');
	$posttable = in_array($_GET['posttableid'], $_G['cache']['posttableids']) ? $_GET['posttableid'] : 0;

	$pids = array();
	$postcount = '0';
	$sql = $error = '';
	$operation == 'group' && $_GET['forums'] = 'isgroup';
	$_GET['keywords'] = trim($_GET['keywords']);
	$_GET['users'] = trim($_GET['users']);
	if(($_GET['starttime'] == '' && $_GET['endtime'] == '' && !$fromumanage) || ($_GET['keywords'] == '' && $_GET['useip'] == '' && $_GET['users'] == '')) {
		$error = 'prune_condition_invalid';
	}

	if($_G['adminid'] == 1 || $_G['adminid'] == 2) {
		if($_GET['forums'] && $_GET['forums'] != 'isgroup') {
			$fid = $_GET['forums'];
		}
		if($_GET['forums'] == 'isgroup') {
			$isgroup = 1;
		} else {
			$isgroup = 0;
		}
	} else {
		$forums = array();
		foreach(C::t('forum_moderator')->fetch_all_by_uid($_G['uid']) as $forum) {
			$forums[] = $forum['fid'];
		}
		$fid = $forums;
	}

	if($_GET['users'] != '') {
		$uids = C::t('common_member')->fetch_all_uid_by_username(array_map('trim', explode(',', $_GET['users'])));
		$authorid = $uids;
	}
	if($_GET['useip'] != '') {
		$useip = str_replace('*', '%', $_GET['useip']);
	}
	if($_GET['keywords'] != '') {
		$keywords = $_GET['keywords'];
	}

	if($_GET['lengthlimit'] != '') {
		$lengthlimit = intval($_GET['lengthlimit']);
		$len_message = $lengthlimit;
	}

	if(!empty($_GET['starttime'])) {
		$starttime = strtotime($_GET['starttime']);
	}

	if($_G['adminid'] == 1 && !empty($_GET['endtime']) && $_GET['endtime'] != dgmdate(TIMESTAMP, 'Y-n-j')) {
		$endtime = strtotime($_GET['endtime']);
	} else {
		$endtime = TIMESTAMP;
	}
	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'prune_mod_range_illegal';
	}

	if(!$error) {
		if($_GET['detail']) {
			$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
			$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];
			$posts = '';
			$groupsname = $groupsfid = $postlist = array();
			$postlist = C::t('forum_post')->fetch_all_prune_by_search($posttable, $isgroup, $keywords, $len_message, $fid, $authorid, $starttime, $endtime, $useip, true, ($page - 1) * $perpage, $perpage);
			foreach($postlist as $key => $post) {
					$postfids[$post[fid]] = $post['fid'];
				$post['dateline'] = dgmdate($post['dateline']);
				$post['subject'] = cutstr($post['subject'], 30);
				$post['message'] = dhtmlspecialchars(cutstr($post['message'], 50));
				$postlist[$key] = $post;
			}
			if($postfids) {
				$query = C::t('forum_forum')->fetch_all_by_fid($postfids);
				foreach($query as $row) {
					$forumnames[$row[fid]] = $row['name'];
				}
			}
			if($postlist) {
				foreach($postlist as $post) {
					$posts .= showtablerow('', '', array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"pidarray[]\" value=\"$post[pid]\" checked />",
						"<a href=\"forum.php?mod=redirect&goto=findpost&pid=$post[pid]&ptid=$post[tid]\" target=\"_blank\">$post[subject]</a>",
						$post['message'],
					"<a href=\"forum.php?mod=forumdisplay&fid=$post[fid]\" target=\"_blank\">".$forumnames[$post[fid]]."</a>",
						"<a href=\"home.php?mod=space&uid=$post[authorid]\" target=\"_blank\">$post[author]</a>",
						$post['dateline']
					), TRUE);
				}
			}
			$postcount = C::t('forum_post')->count_prune_by_search($posttable, $isgroup, $keywords, $len_message, $fid, $authorid, $starttime, $endtime, $useip);
			$multi = multi($postcount, $perpage, $page, ADMINSCRIPT."?action=prune");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=prune&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=prune&amp;page='+this.value", "page(this.value)", $multi);
		} else {
			$postcount = 0;
			foreach(C::t('forum_post')->fetch_all_prune_by_search($posttable, $isgroup, $keywords, $len_message, $fid, $authorid, $starttime, $endtime, $useip, false) as $post) {
				$pids[] = $post['pid'];
				$postcount++;
			}
			$multi = '';
		}

		if(!$postcount) {
			$error = 'prune_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit);
	showformheader('prune&frame=no'.($operation ? '&operation='.$operation : ''), 'target="pruneframe"');
	showhiddenfields(array('pids' => authcode(implode(',', $pids), 'ENCODE'), 'posttableid' => $posttable));
	showtableheader(cplang('prune_result').' '.$postcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'pruneforum\').pp.value=\'\';$(\'pruneforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

	if($error) {
		cpmsg($error);
	} else {
		if($_GET['detail']) {
			showsubtitle(array('', 'subject', 'message', 'forum', 'author', 'time'));
			echo $posts;
		}
	}

	showsubmit('prunesubmit', 'submit', $_GET['detail'] ? '<input type="checkbox" name="chkall" id="chkall" class="checkbox" checked onclick="checkAll(\'prefix\', this.form, \'pidarray\')" /><label for="chkall">'.cplang('del').'</label>' : '',
		'<input class="checkbox" type="checkbox" name="donotupdatemember" id="donotupdatemember" value="1" checked="checked" /><label for="donotupdatemember"> '.cplang('prune_no_update_member').'</label>', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="pruneframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>