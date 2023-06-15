<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_blog.php 32130 2012-11-14 09:20:40Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
include_once libfile('function/portalcp');

cpheader();

$detail = $_GET['detail'];
$uid = $_GET['uid'];
$blogid = $_GET['blogid'];
$users = $_GET['users'];
$keywords = $_GET['keywords'];
$lengthlimit = $_GET['lengthlimit'];
$viewnum1 = $_GET['viewnum1'];
$viewnum2 = $_GET['viewnum2'];
$replynum1 = $_GET['replynum1'];
$replynum2 = $_GET['replynum2'];
$hot1 = $_GET['hot1'];
$hot2 = $_GET['hot2'];
$starttime = $_GET['starttime'];
$endtime = $_GET['endtime'];
$searchsubmit = $_GET['searchsubmit'];
$blogids = $_GET['blogids'];
$friend = $_GET['friend'];
$ip = $_GET['ip'];
$orderby = $_GET['orderby'];
$ordersc = $_GET['ordersc'];

$fromumanage = $_GET['fromumanage'] ? 1 : 0;

$muticondition = '';
$muticondition .= $uid ? '&uid='.$uid : '';
$muticondition .= $blogid ? '&blogid='.$blogid : '';
$muticondition .= $users ? '&users='.$users : '';
$muticondition .= $keywords ? '&keywords='.$keywords : '';
$muticondition .= $lengthlimit ? '&lengthlimit='.$lengthlimit : '';
$muticondition .= $viewnum1 ? '&viewnum1='.$viewnum1 : '';
$muticondition .= $viewnum2 ? '&viewnum2='.$viewnum2 : '';
$muticondition .= $replynum1 ? '&replynum1='.$replynum1 : '';
$muticondition .= $replynum2 ? '&replynum2='.$replynum2 : '';
$muticondition .= $hot1 ? '&hot1='.$hot1 : '';
$muticondition .= $hot2 ? '&hot2='.$hot2 : '';
$muticondition .= $starttime ? '&starttime='.$starttime : '';
$muticondition .= $endtime ? '&endtime='.$endtime : '';
$muticondition .= $friend ? '&friend='.$friend : '';
$muticondition .= $ip ? '&ip='.$ip : '';
$muticondition .= $orderby ? '&orderby='.$orderby : '';
$muticondition .= $ordersc ? '&ordersc='.$ordersc : '';
$muticondition .= $fromumanage ? '&fromumanage='.$fromumanage : '';
$muticondition .= $searchsubmit ? '&searchsubmit='.$searchsubmit : '';
$muticondition .= $_GET['search'] ? '&search='.$_GET['search'] : '';
$muticondition .= $detail ? '&detail='.$detail : '';

if(!submitcheck('blogsubmit')) {
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

	shownav('topic', 'nav_blog');
	showsubmenu('nav_blog', array(
		array('newlist', 'blog', !empty($newlist)),
		array('search', 'blog&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('blog_search', !$searchsubmit),
		array('nav_blog', $searchsubmit)
	));
	if($muticondition) {
		showtips('blog_tips');
	}
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('blogforum').page.value=number;
	$('blogforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit && empty($newlist));
	showformheader("blog".(!empty($_GET['search']) ? '&search=true' : ''), '', 'blogforum');
	showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
	showtableheader();
	showsetting('blog_search_detail', 'detail', $detail, 'radio');
	showsetting('blog_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('resultsort', '', $orderby, "<select name='orderby'><option value=''>$lang[defaultsort]</option><option value='dateline'>$lang[forums_edit_extend_order_starttime]</option><option value='viewnum'>$lang[blog_search_view]</option><option value='replynum'>$lang[blog_search_reply]</option><option value='hot'>$lang[blog_search_hot]</option></select> ");
	showsetting('', '', $ordersc, "<select name='ordersc'><option value='desc'>$lang[orderdesc]</option><option value='asc'>$lang[orderasc]</option></select>", '', 0, '', '', '', true);
	showsetting('blog_search_uid', 'uid', $uid, 'text');
	showsetting('blog_search_blogid', 'blogid', $blogid, 'text');
	showsetting('blog_search_user', 'users', $users, 'text');
	showsetting('blog_search_keyword', 'keywords', $keywords, 'text');
	showsetting('blog_search_friend', '', $friend, "<select name='friend'><option value='0'>$lang[setting_home_privacy_alluser]</option><option value='1'>$lang[setting_home_privacy_friend]</option><option value='2'>$lang[setting_home_privacy_specified_friend]</option><option value='3'>$lang[setting_home_privacy_self]</option><option value='4'>$lang[setting_home_privacy_password]</option></select>");
	showsetting('blog_search_ip', 'ip', $ip, 'text');
	showsetting('blog_search_lengthlimit', 'lengthlimit', $lengthlimit, 'text');
	showsetting('blog_search_view', array('viewnum1', 'viewnum2'), array('', ''), 'range');
	showsetting('blog_search_reply', array('replynum1', 'replynum2'), array('', ''), 'range');
	showsetting('blog_search_hot', array('hot1', 'hot2'), array('', ''), 'range');
	showsetting('blog_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
    if($_GET['blogids']) {
		$blogids = authcode($_GET['blogids'], 'DECODE');
		$blogidsadd = $blogids ? explode(',', $blogids) : $_GET['delete'];
		include_once libfile('function/delete');
		$deletecount = count(deleteblogs($blogidsadd));
		$cpmsg = cplang('blog_succeed', array('deletecount' => $deletecount));
	} else {
		$blogs = $catids = array();
		$selectblogids = !empty($_GET['ids']) && is_array($_GET['ids']) ? $_GET['ids'] : array();
		if($selectblogids) {
			$query = C::t('home_blog')->fetch_all($selectblogids);
			foreach($query as $value) {
				$blogs[$value['blogid']] = $value;
				$catids[] = intval($value['catid']);
			}
		}
		if($blogs) {
			$selectblogids = array_keys($blogs);
			if($_POST['optype'] == 'delete') {
				include_once libfile('function/delete');
				$deletecount = count(deleteblogs($selectblogids));
				$cpmsg = cplang('blog_succeed', array('deletecount' => $deletecount));
			} elseif($_POST['optype'] == 'move') {
				$tocatid = intval($_POST['tocatid']);
				$catids[] = $tocatid;
				$catids = array_merge($catids);

				C::t('home_blog')->update($selectblogids, array('catid'=>$tocatid));
				foreach($catids as $catid) {
					$catid = intval($catid);
					$cnt = C::t('home_blog')->count_by_catid($catid);
					C::t('home_blog_category')->update($catid, array('num'=>$cnt));
				}
				$cpmsg = cplang('blog_move_succeed');
			} else {
				$cpmsg = cplang('blog_choose_at_least_one_operation');
			}
		} else {
			$cpmsg = cplang('blog_choose_at_least_one_blog');
		}
	}
?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('blogforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit', 1) || $newlist) {

	$blogids = $blogcount = '0';
	$sql = $error = '';
	$keywords = trim($keywords);
	$users = trim($users);
	$uids = array();

	if($blogid != '') {
		$blogid = explode(',', $blogid);
	}

	if($users != '') {
		$uids = C::t('common_member')->fetch_all_uid_by_username(array_map('trim', explode(',', $users)));
		if(!$uids) {
			$uids = array(-1);
		}
	}

	$uid = trim($uid, ', ');
	if($uid != '') {
		$uid = explode(',', $uid);
		if($uids && $uids[0] != -1) {
			$uids = array_intersect($uids, $uid);
		} else {
			$uids = $uid;
		}
		if(!$uids) {
			$uids = array(-1);
		}
	}

	if($starttime != '') {
		$starttime = strtotime($starttime);
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '') {
			$endtime = strtotime($endtime);
		}
	} else {
		$endtime = TIMESTAMP;
	}


	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'blog_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$pagetmp = $page;
			$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
			$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];
			do{
				$query = C::t('home_blog')->fetch_all_by_search(1, $blogid, $uids, $starttime, $endtime, $hot1, $hot2, $viewnum1, $viewnum2, $replynum1, $replynum2, $friend, $ip, $keywords, $lengthlimit, $orderby, $ordersc, (($pagetmp - 1) * $perpage), $perpage, null, null, null, null, false, array(0, 1));
				$pagetmp--;
			} while(!count($query) && $pagetmp);
			$blogs = '';
			foreach($query as $blog) {
				$blog['dateline'] = dgmdate($blog['dateline']);
				$blog['subject'] = cutstr($blog['subject'], 30);
				switch ($blog['friend']) {
					case '0':
						$privacy_name = $lang[setting_home_privacy_alluser];
						break;
					case '1':
						$privacy_name = $lang[setting_home_privacy_friend];
						break;
					case '2':
						$privacy_name = $lang[setting_home_privacy_specified_friend];
						break;
					case '3':
						$privacy_name = $lang[setting_home_privacy_self];
						break;
					case '4':
						$privacy_name = $lang[setting_home_privacy_password];
						break;
					default:
						$privacy_name = $lang[setting_home_privacy_alluser];
				}
				$blog['friend'] = $blog['friend'] ? " <a href=\"".ADMINSCRIPT."?action=blog&friend=$blog[friend]\">$privacy_name</a>" : $privacy_name;
				$blogs .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"ids[]\" value=\"$blog[blogid]\" />",
					$blog['blogid'],
					"<a href=\"home.php?mod=space&uid=$blog[uid]\" target=\"_blank\">$blog[username]</a>",
					"<a href=\"home.php?mod=space&uid=$blog[uid]&do=blog&id=$blog[blogid]\" target=\"_blank\">$blog[subject]</a>",
					$blog['viewnum'],
					$blog['replynum'],
					$blog['hot'],
					$blog['dateline'],
					$blog['friend']
				), TRUE);
			}
			$blogcount = C::t('home_blog')->count_all_by_search($blogid, $uids, $starttime, $endtime, $hot1, $hot2, $viewnum1, $viewnum2, $replynum1, $replynum2, $friend, $ip, $keywords, $lengthlimit, null, null, null, false, array(0, 1));
			$multi = multi($blogcount, $perpage, $page, ADMINSCRIPT."?action=blog".($perpage ? '&perpage='.$perpage : '').$muticondition);
		} else {
			$blogcount = 0;
			$query = C::t('home_blog')->fetch_all_by_search(2, $blogid, $uids, $starttime, $endtime, $hot1, $hot2, $viewnum1, $viewnum2, $replynum1, $replynum2, $friend, $ip, $keywords, $lengthlimit, null, null, 0, 0, null, null, null, null, false, array(0, 1));
			foreach($query as $blog) {
				$blogids .= ','.$blog['blogid'];
				$blogcount++;
			}
			$multi = '';
		}

		if(!$blogcount) {
			$error = 'blog_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit || $newlist);
	showformheader('blog&frame=no', 'target="blogframe"');
	if(!$muticondition) {
		showtableheader(cplang('blog_new_result').' '.$blogcount, 'fixpadding');
	} else {
		showtableheader(cplang('blog_result').' '.$blogcount.(empty($newlist) ? ' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'blogforum\').pp.value=\'\';$(\'blogforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>' : ''), 'fixpadding');
	}

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'blogid', 'author', 'subject', 'view', 'reply', 'hot', 'time', 'privacy'));
			echo $blogs;
			$optypehtml = ''
			.'<input type="radio" name="optype" id="optype_delete" value="delete" class="radio" /><label for="optype_delete">'.cplang('delete').'</label>&nbsp;&nbsp;'
			;
			$optypehtml .= '<input type="radio" name="optype" id="optype_move" value="move" class="radio" /><label for="optype_move">'.cplang('article_opmove').'</label> '
					.category_showselect('blog', 'tocatid', false)
					.'&nbsp;&nbsp;';
			showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ids\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;'.$optypehtml.'<input type="submit" class="btn" name="blogsubmit" value="'.cplang('submit').'" />', $multi);
		} else {
			showhiddenfields(array('blogids' => authcode($blogids, 'ENCODE')));
			showsubmit('blogsubmit', 'delete', $detail ? 'del' : '', '', $multi);
		}
	}

	showtablefooter();
	showformfooter();
	echo '<iframe name="blogframe" style="display:none;"></iframe>';
	showtagfooter('div');

}

?>