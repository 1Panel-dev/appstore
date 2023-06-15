<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_doing.php 27696 2012-02-10 03:39:50Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$detail = $_GET['detail'];
$users = $_GET['users'];
$userip = $_GET['userip'];
$keywords = $_GET['keywords'];
$lengthlimit = $_GET['lengthlimit'];
$starttime = $_GET['starttime'];
$endtime = $_GET['endtime'];
$searchsubmit = $_GET['searchsubmit'];
$doids = $_GET['doids'];

$fromumanage = $_GET['fromumanage'] ? 1 : 0;

cpheader();

if(!submitcheck('doingsubmit')) {
	if(empty($_GET['search'])) {
		$newlist = 1;
		$detail = 1;
	}

	if($fromumanage) {
		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? '' : $starttime;
		$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? '' : $endtime;
	} else {
		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
		$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;
	}

	shownav('topic', 'nav_doing');
	showsubmenu('nav_doing', array(
		array('newlist', 'doing', !empty($newlist)),
		array('search', 'doing&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('doing_search', !$searchsubmit),
		array('nav_doing', $searchsubmit)
	));
	if(empty($newlist)) {
		$search_tips = 1;
		showtips('doing_tips');
	}
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('doingforum').page.value=number;
	$('doingforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit && empty($newlist));
	showformheader("doing".(!empty($_GET['search']) ? '&search=true' : ''), '', 'doingforum');
	showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
	showtableheader();
	showsetting('doing_search_detail', 'detail', $detail, 'radio');
	showsetting('doing_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('doing_search_user', 'users', $users, 'text');
	showsetting('doing_search_ip', 'userip', $userip, 'text');
	showsetting('doing_search_keyword', 'keywords', $keywords, 'text');
	showsetting('doing_search_lengthlimit', 'lengthlimit', $lengthlimit, 'text');
	showsetting('doing_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {

	$doids = authcode($doids, 'DECODE');
	$doidsadd = $doids ? explode(',', $doids) : $_GET['delete'];
	include_once libfile('function/delete');
	$deletecount = count(deletedoings($doidsadd));
	$cpmsg = cplang('doing_succeed', array('deletecount' => $deletecount));

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('doingforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit', 1) || $newlist) {

	$doids = $doingcount = '0';
	$sql = $error = '';
	$keywords = trim($keywords);
	$users = trim($users);

	if($users != '') {
		$uids = C::t('common_member')->fetch_all_uid_by_username(array_map('trim', explode(',', $users)));
		if(!$uids) {
			$uids = array(-1);
		}
	}

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'prune_mod_range_illegal';
	}

	if(!($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j'))) {
		$endtime = TIMESTAMP;
	}

	if(!$error) {
		if($detail) {
			$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
			$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];
			$query = C::t('home_doing')->fetch_all_search((($page - 1) * $perpage), $perpage, 1, $uids, $userip, $keywords, $lengthlimit, $starttime, $endtime);
			$doings = '';

			foreach ($query as $doing) {
				$doing['dateline'] = dgmdate($doing['dateline']);
				$doings .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$doing[doid]\"  />",
					"<a href=\"home.php?mod=space&uid=$doing[uid]\" target=\"_blank\">$doing[username]</a>",
					$doing['message'],
					$doing['ip'],
					$doing['dateline']
				), TRUE);
			}
			$doingcount = C::t('home_doing')->fetch_all_search((($page - 1) * $perpage), $perpage, 3, $uids, $userip, $keywords, $lengthlimit, $starttime, $endtime);
			$multi = multi($doingcount, $perpage, $page, ADMINSCRIPT."?action=doing");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=doing&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=doing&amp;page='+this.value", "page(this.value)", $multi);

		} else {
			$doingcount = 0;
			$query = C::t('home_doing')->fetch_all_search((($page - 1) * $perpage), $perpage, 2, $uids, $userip, $keywords, $lengthlimit, $starttime, $endtime);
			foreach ($query as $doing) {
				$doids .= ','.$doing['doid'];
				$doingcount++;
			}
			$multi = '';
		}

		if(!$doingcount) {
			$error = 'doing_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit || $newlist);
	showformheader('doing&frame=no', 'target="doingframe"');
	showhiddenfields(array('doids' => authcode($doids, 'ENCODE')));
	if(!$search_tips) {
		showtableheader(cplang('doing_new_result').' '.$doingcount, 'fixpadding');
	} else {
		showtableheader(cplang('doing_result').' '.$doingcount.(empty($newlist) ? ' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'doingforum\').pp.value=\'\';$(\'doingforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>' : ''), 'fixpadding');
	}

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'author', 'message', 'ip', 'time'));
			echo $doings;
		}
	}

	showsubmit('doingsubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="doingframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>