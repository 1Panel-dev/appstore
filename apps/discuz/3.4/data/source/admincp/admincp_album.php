<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_album.php 27892 2012-02-16 07:24:19Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
include_once libfile('function/portalcp');

cpheader();

$detail = $_GET['detail'];
$albumname = $_GET['albumname'];
$albumid = $_GET['albumid'];
$uid = $_GET['uid'];
$users = $_GET['users'];
$starttime = $_GET['starttime'];
$endtime = $_GET['endtime'];
$searchsubmit = $_GET['searchsubmit'];
$albumids = $_GET['albumids'];
$friend = $_GET['friend'];
$orderby = $_GET['orderby'];
$ordersc = $_GET['ordersc'];

$fromumanage = $_GET['fromumanage'] ? 1 : 0;

$muticondition = '';
$muticondition .= $albumname ? '&albumname='.$albumname : '';
$muticondition .= $albumid ? '&albumid='.$albumid : '';
$muticondition .= $uid ? '&uid='.$uid : '';
$muticondition .= $users ? '&users='.$users : '';
$muticondition .= $starttime ? '&starttime='.$starttime : '';
$muticondition .= $endtime ? '&endtime='.$endtime : '';
$muticondition .= $friend ? '&friend='.$friend : '';
$muticondition .= $orderby ? '&orderby='.$orderby : '';
$muticondition .= $ordersc ? '&ordersc='.$ordersc : '';
$muticondition .= $fromumanage ? '&fromumanage='.$fromumanage : '';
$muticondition .= $searchsubmit ? '&searchsubmit='.$searchsubmit : '';
$muticondition .= $_GET['search'] ? '&search='.$_GET['search'] : '';
$muticondition .= $detail ? '&detail='.$detail : '';

if(!submitcheck('albumsubmit')) {
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

	shownav('topic', 'nav_album');
	showsubmenu('nav_album', array(
		array('newlist', 'album', !empty($newlist)),
		array('search', 'album&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('album_search', !$searchsubmit),
		array('nav_album', $searchsubmit)
	));
	if($muticondition) {
		showtips('album_tips');
	}
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('albumforum').page.value=number;
	$('albumforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit && empty($newlist));
	showformheader("album".(!empty($_GET['search']) ? '&search=true' : ''), '', 'albumforum');
	showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
	showtableheader();
	showsetting('album_search_detail', 'detail', $detail, 'radio');
	showsetting('album_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('resultsort', '', $orderby, "<select name='orderby'><option value=''>$lang[defaultsort]</option><option value='dateline'>$lang[topic_dateline]</option><option value='updatetime'>$lang[updatetime]</option><option value='picnum'>$lang[pic_num]</option></select> ");
	showsetting('', '', $ordersc, "<select name='ordersc'><option value='desc'>$lang[orderdesc]</option><option value='asc'>$lang[orderasc]</option></select>", '', 0, '', '', '', true);
	showsetting('album_search_albumname', 'albumname', $albumname, 'text');
	showsetting('album_search_albumid', 'albumid', $albumid, 'text');
	showsetting('album_search_uid', 'uid', $uid, 'text');
	showsetting('album_search_user', 'users', $users, 'text');
	showsetting('blog_search_friend', '', $friend, "<select name='friend'><option value='0'>$lang[setting_home_privacy_alluser]</option><option value='1'>$lang[setting_home_privacy_friend]</option><option value='2'>$lang[setting_home_privacy_specified_friend]</option><option value='3'>$lang[setting_home_privacy_self]</option><option value='4'>$lang[setting_home_privacy_password]</option></select>");
	showsetting('album_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
	if($_GET['albumids']) {
		$albumids = authcode($_GET['albumids'], 'DECODE');
		$albumidsadd = $albumids ? explode(',', $albumids) : $_GET['delete'];
		include_once libfile('function/delete');
		$deletecount = count(deletealbums($albumidsadd));
		$cpmsg = cplang('album_succeed', array('deletecount' => $deletecount));
	} else {
		$albums = $catids = array();
		$selectalbumids = !empty($_GET['ids']) && is_array($_GET['ids']) ? $_GET['ids'] : array();
		if($selectalbumids) {
			$query = C::t('home_album')->fetch_all($selectalbumids);
			foreach($query as $value) {
				$albums[$value['albumid']] = $value;
				$catids[] = intval($value['catid']);
			}
		}
		if($albums) {
			$selectalbumids = array_keys($albums);
			if($_POST['optype'] == 'delete') {
				include_once libfile('function/delete');
				$deletecount = count(deletealbums($selectalbumids));
				$cpmsg = cplang('album_succeed', array('deletecount' => $deletecount));
			} elseif($_POST['optype'] == 'move') {
				$tocatid = intval($_POST['tocatid']);
				$catids[] = $tocatid;
				$catids = array_merge($catids);
				C::t('home_album')->update($selectalbumids, array('catid'=>$tocatid));
				foreach($catids as $catid) {
					$catid = intval($catid);
					$cnt = C::t('home_album')->count_by_catid($catid);
					C::t('home_album_category')->update($catid, array('num'=>intval($cnt)));
				}
				$cpmsg = cplang('album_move_succeed');
			} else {
				$cpmsg = cplang('album_choose_at_least_one_operation');
			}
		} else {
			$cpmsg = cplang('album_choose_at_least_one_album');
		}
	}

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('albumforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit', 1) || $newlist) {

	$albumids = $albumcount = '0';
	$sql = $error = '';
	$users = trim($users);

	if($users != '') {
		$uids = array(-1);
		$query = C::t('home_album')->fetch_uid_by_username(explode(',', $users));
		$uids = array_keys($query) + $uids;
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

	if($albumid != '') {
		$albumids = explode(',', $albumid);
	}

	if($uid != '') {
		$query = C::t('home_album')->fetch_uid_by_uid($uid);
		if(!$uids) {
			$uids = array_keys($query);
		} else {
			$uids = array_intersect(array_keys($query), $uids);
		}
		if(!$uids) {
			$uids = array(-1);
		}
	}

	$orderby = $orderby ? $orderby : 'updatetime';
	$ordersc = $ordersc ? $ordersc : 'DESC';

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'album_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
			$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];
			$query = C::t('home_album')->fetch_all_by_search(1, $uids, $albumname, false, '', $starttime, $endtime, $albumids, $friend, $orderby, $ordersc, (($page - 1) * $perpage), $perpage);
			$albums = '';

			include_once libfile('function/home');
			foreach($query as $album) {
				if($album['friend'] != 4 && ckfriend($album['uid'], $album['friend'], $album['target_ids'])) {
					$album['pic'] = pic_cover_get($album['pic'], $album['picflag']);
				} else {
					$album['pic'] = STATICURL.'image/common/nopublish.gif';
				}
				$album['updatetime'] = dgmdate($album['updatetime']);
				switch ($album['friend']) {
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
				$album['friend'] = $album['friend'] ? " <a href=\"".ADMINSCRIPT."?action=album&friend=$album[friend]\">$privacy_name</a>" : $privacy_name;
				$albums .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"ids[]\" value=\"$album[albumid]\" />",
					"<a href=\"home.php?mod=space&uid=$album[uid]&do=album&id=$album[albumid]\" target=\"_blank\"><img src='$album[pic]' /></a>",
					"<a href=\"home.php?mod=space&uid=$album[uid]&do=album&id=$album[albumid]\" target=\"_blank\">$album[albumname]</a>",
					"<a href=\"home.php?mod=space&uid=$album[uid]\" target=\"_blank\">".$album['username']."</a>",
					$album['updatetime'],"<a href=\"".ADMINSCRIPT."?action=pic&albumid=$album[albumid]\">".$album['picnum']."</a>",
					$album['friend']
				), TRUE);
			}
			$albumcount = C::t('home_album')->fetch_all_by_search(3, $uids, $albumname, false, '', $starttime, $endtime, $albumids, $friend);
			$multi = multi($albumcount, $perpage, $page, ADMINSCRIPT."?action=album$muticondition");
		} else {
			$albumcount = 0;
			$query = C::t('home_album')->fetch_all_by_search(2, $uids, $albumname, false, '', $starttime, $endtime, $albumids, $friend);
			foreach($query as $album) {
				$albumids .= ','.$album['albumid'];
				$albumcount++;
			}
			$multi = '';
		}

		if(!$albumcount) {
			$error = 'album_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit || $newlist);
	showformheader('album&frame=no', 'target="albumframe"');
	if(!$muticondition) {
		showtableheader(cplang('album_new_result').' '.$albumcount, 'fixpadding');
	} else {
		showtableheader(cplang('album_result').' '.$albumcount.(empty($newlist) ? ' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'albumforum\').pp.value=\'\';$(\'albumforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>' : ''), 'fixpadding');
	}

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'albumpic', 'albumname', 'author', 'updatetime', 'pic_num', 'privacy'));
			echo $albums;
			$optypehtml = ''
			.'<input type="radio" name="optype" id="optype_delete" value="delete" class="radio" /><label for="optype_delete">'.cplang('delete').'</label>&nbsp;&nbsp;'
			;
			$optypehtml .= '<input type="radio" name="optype" id="optype_move" value="move" class="radio" /><label for="optype_move">'.cplang('article_opmove').'</label> '
					.category_showselect('album', 'tocatid', false)
					.'&nbsp;&nbsp;';
			showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ids\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;'.$optypehtml.'<input type="submit" class="btn" name="albumsubmit" value="'.cplang('submit').'" />', $multi);
		} else {
			showhiddenfields(array('albumids' => authcode($albumids, 'ENCODE')));
			showsubmit('albumsubmit', 'delete', $detail ? 'del' : '', '', $multi);
		}
	}

	showtablefooter();
	showformfooter();
	echo '<iframe name="albumframe" style="display:none;"></iframe>';
	showtagfooter('div');

}
?>