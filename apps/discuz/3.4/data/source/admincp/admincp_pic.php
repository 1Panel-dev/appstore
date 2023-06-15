<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_pic.php 28299 2012-02-27 08:48:36Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$detail = $_GET['detail'];
$albumid = $_GET['albumid'];
$users = $_GET['users'];
$picid = $_GET['picid'];
$postip = $_GET['postip'];
$hot1 = $_GET['hot1'];
$hot2 = $_GET['hot2'];
$starttime = $_GET['starttime'];
$endtime = $_GET['endtime'];
$searchsubmit = $_GET['searchsubmit'];
$picids = $_GET['picids'];
$title = $_GET['title'];
$orderby = $_GET['orderby'];
$ordersc = $_GET['ordersc'];

$fromumanage = $_GET['fromumanage'] ? 1 : 0;

$muticondition = '';
$muticondition .= $albumid ? '&albumid='.$albumid : '';
$muticondition .= $users ? '&users='.$users : '';
$muticondition .= $picid ? '&picid='.$picid : '';
$muticondition .= $postip ? '&postip='.$postip : '';
$muticondition .= $hot1 ? '&hot1='.$hot1 : '';
$muticondition .= $hot2 ? '&hot2='.$hot2 : '';
$muticondition .= $starttime ? '&starttime='.$starttime : '';
$muticondition .= $endtime ? '&endtime='.$endtime : '';
$muticondition .= $title ? '&title='.$title : '';
$muticondition .= $orderby ? '&orderby='.$orderby : '';
$muticondition .= $ordersc ? '&ordersc='.$ordersc : '';
$muticondition .= $fromumanage ? '&fromumanage='.$fromumanage : '';
$muticondition .= $searchsubmit ? '&searchsubmit='.$searchsubmit : '';
$muticondition .= $_GET['search'] ? '&search='.$_GET['search'] : '';
$muticondition .= $detail ? '&detail='.$detail : '';

cpheader();

if(!submitcheck('picsubmit')) {
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

	shownav('topic', 'nav_pic');
	showsubmenu('nav_pic', array(
		array('newlist', 'pic', !empty($newlist)),
		array('search', 'pic&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('pic_search', !$searchsubmit),
		array('nav_pic', $searchsubmit)
	));
	if($muticondition) {
		showtips('pic_tips');
	}
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('picforum').page.value=number;
	$('picforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit && empty($newlist));
	showformheader("pic".(!empty($_GET['search']) ? '&search=true' : ''), '', 'picforum');
	showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
	showtableheader();
	showsetting('pic_search_detail', 'detail', $detail, 'radio');
	showsetting('pic_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('resultsort', '', $orderby, "<select name='orderby'><option value=''>$lang[defaultsort]</option><option value='dateline'>$lang[pic_search_createtime]</option><option value='size'>$lang[pic_size]</option><option value='hot'>$lang[pic_search_hot]</option></select> ");
	showsetting('', '', $ordersc, "<select name='ordersc'><option value='desc'>$lang[orderdesc]</option><option value='asc'>$lang[orderasc]</option></select>", '', 0, '', '', '', true);
	showsetting('pic_search_albumid', 'albumid', $albumid, 'text');
	showsetting('pic_search_user', 'users', $users, 'text');
	showsetting('pic_search_picid', 'picid', $picid, 'text');
	showsetting('pic_search_title', 'title', $title, 'text');
	showsetting('pic_search_ip', 'postip', $postip, 'text');
	showsetting('pic_search_hot', array('hot1', 'hot2'), array('', ''), 'range');
	showsetting('pic_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
	$picids = authcode($picids, 'DECODE');
	$picidsadd = $picids ? explode(',', $picids) : $_GET['delete'];
	include_once libfile('function/delete');
	$deletecount = count(deletepics($picidsadd));
	$cpmsg = cplang('pic_succeed', array('deletecount' => $deletecount));

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('picforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit', 1) || $newlist) {

	$picids = $piccount = '0';
	$sql = $error = '';
	$users = trim($users);

	if($starttime != '') {
		$starttime = strtotime($starttime);
		$sql .= ' AND p.'.DB::field('dateline', $starttime, '>');
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '') {
			$endtime = strtotime($endtime);
			$sql .= ' AND p.'.DB::field('dateline', $endtime, '<');
		}
	} else {
		$endtime = TIMESTAMP;
	}

	if($picid !='') {
		$picids = '-1';
		$picidsarr = array('-1');
		$query = C::t('home_pic')->fetch_all(explode(',', str_replace(' ', '', $picid)));
		foreach($query as $arr) {
			$picids .=",$arr[picid]";
			$picidsarr[] = $arr['picid'];
		}
		$sql .= ' AND p.'.DB::field('picid', $picidsarr);
	}

	if($albumid !='') {
		$albumids = '-1';
		$albumidsarr = array('-1');
		$query = C::t('home_album')->fetch_all(explode(',', $albumid));
		foreach($query as $arr) {
			$albumids .=",$arr[albumid]";
			$albumidsarr[] = $arr['albumid'];
		}
		$sql .= ' AND p.'.DB::field('albumid', $albumidsarr);
	}

	if($users != '') {
		$uids = '-1';
		$uidsarr = array('-1');
		$query = C::t('home_album')->fetch_uid_by_username(explode(',', $users));
		foreach($query as $arr) {
			$uids .= ",$arr[uid]";
			$uidsarr[] = $arr['uid'];
		}
		$sql .= ' AND p.'.DB::field('uid', $uidsarr);
	}

	if($postip != '') {
		$sql .= ' AND p.'.DB::field('postip', str_replace('*', '%', $postip), 'like');
	}

	$sql .= $hot1 ? ' AND p.'.DB::field('hot', $hot1, '>=') : '';
	$sql .= $hot2 ? ' AND p.'.DB::field('hot', $hot2, '<=') : '';
	$sql .= $title ? ' AND p.'.DB::field('title', '%'.$title.'%', 'like') : '';
	$orderby = $orderby ? $orderby : 'dateline';
	$ordersc = $ordersc ? "$ordersc" : 'DESC';

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'pic_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
			$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];
			$query = C::t('home_pic')->fetch_all_by_sql('1 '.$sql, 'p.'.DB::order($orderby, $ordersc), (($page - 1) * $perpage), $perpage);
			$pics = '';

			include_once libfile('function/home');
			foreach($query as $pic) {
				$pic['dateline'] = dgmdate($pic['dateline']);
				$pic['pic'] = pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);
				$pic['albumname'] = empty($pic['albumname']) && empty($pic['albumid']) ? $lang['album_default'] : $pic['albumname'];
				$pic['albumid'] = empty($pic['albumid']) ? -1 : $pic['albumid'];
				$pics .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$pic[picid]\" />",
					"<a href='home.php?mod=space&uid=$pic[uid]&do=album&picid=$pic[picid]'  target='_blank'><img src='$pic[pic]'/></a>",
					$pic['size'],
					"<a href='home.php?mod=space&uid=$pic[uid]&do=album&id=$pic[albumid]'  target='_blank'>$pic[albumname]</a>",
					"<a href=\"home.php?mod=space&uid=$pic[uid]\" target=\"_blank\">".$pic['username']."</a>",
					$pic['dateline'], "<a href=\"".ADMINSCRIPT."?action=comment&detail=1&searchsubmit=1&idtype=picid&id=$pic[picid]\">".$lang['pic_comment']."</a>"
				), TRUE);
			}
			$piccount = C::t('home_pic')->fetch_all_by_sql('1 '.$sql, '', 0, 0, 1);
			$multi = multi($piccount, $perpage, $page, ADMINSCRIPT."?action=pic$muticondition");
		} else {
			$piccount = 0;
			$query = C::t('home_pic')->fetch_all_by_sql('1 '.$sql, '', 0, 0, 0, 0);
			foreach($query as $pic) {
				$picids .= ','.$pic['picid'];
				$piccount++;
			}
			$multi = '';
		}

		if(!$piccount) {
			$error = 'pic_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit || $newlist);
	showformheader('pic&frame=no', 'target="picframe"');
	showhiddenfields(array('picids' => authcode($picids, 'ENCODE')));
	if(!$muticondition) {
		showtableheader(cplang('pic_new_result').' '.$piccount, 'fixpadding');
	} else {
		showtableheader(cplang('pic_result').' '.$piccount.(empty($newlist) ? ' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'picforum\').pp.value=\'\';$(\'picforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>' : ''), 'fixpadding');
	}

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'albumpic', 'pic_size', 'albumname', 'author', 'time', 'pic_comment'));
			echo $pics;
		}
	}

	showsubmit('picsubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="picframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>