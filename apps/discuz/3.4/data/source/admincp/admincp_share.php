<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_share.php 27696 2012-02-10 03:39:50Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$detail = $_GET['detail'];
$uid = $_GET['uid'];
$users = $_GET['users'];
$sid = $_GET['sid'];
$type = $_GET['type'];
$hot1 = $_GET['hot1'];
$hot2 = $_GET['hot2'];
$starttime = $_GET['starttime'];
$endtime = $_GET['endtime'];
$searchsubmit = $_GET['searchsubmit'];
$sids = $_GET['sids'];

$fromumanage = $_GET['fromumanage'] ? 1 : 0;

cpheader();

if(!submitcheck('sharesubmit')) {
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

	shownav('topic', 'nav_share');
	showsubmenu('nav_share', array(
		array('newlist', 'share', !empty($newlist)),
		array('search', 'share&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('share_search', !$searchsubmit),
		array('nav_share', $searchsubmit)
	));
	showtips('share_tips');
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('shareforum').page.value=number;
	$('shareforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit && empty($newlist));
	showformheader("share".(!empty($_GET['search']) ? '&search=true' : ''), '', 'shareforum');
	showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
	showtableheader();
	showsetting('share_search_detail', 'detail', $detail, 'radio');
	showsetting('share_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	$selected[$type] = $type ? 'selected="selected"' : '';
	showsetting('share_search_icon', '', $type, "<select name='type'><option value=''>$lang[all]</option><option value='link' $selected[link]>$lang[link]</option>
			<option value='video' $selected[video]>$lang[video]</option><option value='music' $selected[music]>$lang[music]</option><option value='flash' $selected[flash]>Flash</option>
			<option value='blog' $selected[blog]>$lang[blogs]</option><option value='album' $selected[album]>$lang[albums]</option><option value='pic' $selected[pic]>$lang[pics]</option>
			<option value='space' $selected[space]>$lang[members]</option><option value='thread' $selected[thread]>$lang[thread]</option></select>");
	showsetting('share_search_uid', 'uid', $uid, 'text');
	showsetting('share_search_user', 'users', $users, 'text');
	showsetting('share_search_sid', 'sid', $sid, 'text');
	showsetting('share_search_hot', array('hot1', 'hot2'), array('', ''), 'range');
	showsetting('share_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
	$sids = authcode($sids, 'DECODE');
	$sidsadd = $sids ? explode(',', $sids) : $_GET['delete'];
	include_once libfile('function/delete');
	$deletecount = count(deleteshares($sidsadd));
	$cpmsg = cplang('share_succeed', array('deletecount' => $deletecount));

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('shareforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit', 1) || $newlist) {

	$uids = $sids = $sharecount = 0;
	$sql = $error = '';
	$users = trim($users);
	$uids = array();

	if($users != '') {
		foreach(C::t('home_share')->fetch_all_by_username(explode(',', str_replace(' ', '', $users))) as $arr) {
			$uids[$arr['uid']] = $arr['uid'];
		}
		if(!$uids) {
			$uids = array(-1);
		}
		$sql .= " AND s.uid IN ($uids)";
	}

	if($type != '') {
		$arr = C::t('home_share')->fetch_by_type($type);
		$type = $arr['type'];
	}

	if($starttime != '') {
		$starttime = strtotime($starttime);
		$sql .= " AND s.dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '') {
			$endtime = strtotime($endtime);
			$sql .= " AND s.dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}

	if($sid != '') {
		$sids = array();
		foreach(C::t('home_share')->fetch_all(explode(',', str_replace(' ', '', $sid))) as $fidarr) {
			$sids[] = $fidarr['sid'];
		}
		if(!$sids) {
			$sids = array(-1);
		}
		$sql .= " AND  s.sid IN ($sids)";
	}

	if($uid != '') {
		$uidtmp = array();
		foreach(C::t('home_share')->fetch_all_by_uid(explode(',', str_replace(' ', '', $uid))) as $uidarr) {
			$uidtmp[$uidarr['uid']] = $uidarr['uid'];
		}
		if($uids && $uids[0] != -1) {
			$uids = array_intersect($uids, $uidtmp);
		} else {
			$uids = $uidtmp;
		}
		if(!$uids) {
			$uids = array(-1);
		}
	}

	$sql .= $hot1 ? " AND s.hot >= '$hot1'" : '';
	$sql .= $hot2 ? " AND s.hot <= '$hot2'" : '';

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'share_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
			$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];
			$sharecount = C::t('home_share')->count_by_search($sids, $uids, $type, $starttime, $endtime, $hot1, $hot2);
			if($sharecount) {
				$shares = '';
				require_once libfile('function/share');

				$start = ($page - 1) * $perpage;
				foreach(C::t('home_share')->fetch_all_search($sids, $uids, $type, $starttime, $endtime, $hot1, $hot2, $start, $perpage) as $share) {
					$share['dateline'] = dgmdate($share['dateline']);
					$share = mkshare($share);
					$shares .= showtablerow('', array('', 'style="width:80px;"', 'style="width:150px;"', 'style="width:500px;"'), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$share[sid]\" />",
						"<a href=\"home.php?mod=space&uid=$share[uid]\" target=\"_blank\">".$share['username']."</a>",
						$share['title_template'],
						$share['body_template'],
						$share['dateline']
					), TRUE);
				}
				$multi = multi($sharecount, $perpage, $page, ADMINSCRIPT."?action=share");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=share&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=share&amp;page='+this.value", "page(this.value)", $multi);
			}
		} else {
			$sharecount = 0;
			foreach(C::t('home_share')->fetch_all_search($sids, $uids, $type, $starttime, $endtime, $hot1, $hot2) as $share) {
				$sids .= ','.$share['sid'];
				$sharecount++;
			}
			$multi = '';
		}

		if(!$sharecount) {
			$error = 'share_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit || $newlist);
	showformheader('share&frame=no', 'target="shareframe"');
	showhiddenfields(array('sids' => authcode($sids, 'ENCODE')));
	showtableheader(cplang('share_result').' '.$sharecount.(empty($newlist) ? ' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'shareforum\').pp.value=\'\';$(\'shareforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>' : ''), 'fixpadding');

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'author', 'share_title', 'share_body', 'time'));
			echo $shares;
		}
	}

	showsubmit('sharesubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="shareframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>