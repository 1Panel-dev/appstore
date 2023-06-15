<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_postcomment.php 25832 2011-11-24 01:11:51Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$detail = !empty($_GET['authorid']) ? true : $_GET['detail'];
$author = $_GET['author'];
$authorid = $_GET['authorid'];
$uid = $_GET['uid'];
$message = $_GET['message'];
$ip = $_GET['ip'];
$users = $_GET['users'];
$starttime = $_GET['starttime'];
$endtime = $_GET['endtime'];
$searchtid = $_GET['searchtid'];
$searchpid = $_GET['searchpid'];
$searchsubmit = $_GET['searchsubmit'];
$cids = $_GET['cids'];
$page = max(1, $_GET['page']);

cpheader();

$aid = $_GET['aid'];
$subject = $_GET['subject'];

if(!submitcheck('postcommentsubmit')) {
	if(empty($_GET['search'])) {
		$newlist = 1;
		$detail = 1;
		$starttime = dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j');
	}

	$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
	$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;

	shownav('topic', 'nav_postcomment');
	showsubmenu('nav_postcomment', array(
		array('newlist', 'postcomment', !empty($newlist)),
		array('search', 'postcomment&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('postcomment_search', !$searchsubmit),
		array('nav_postcomment', $searchsubmit)
	));
	if(empty($newlist)) {
		$search_tips = 1;
		showtips('postcomment_tips');
	}
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('postcommentforum').page.value=number;
	$('postcommentforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit && empty($newlist));
	showformheader("postcomment".(!empty($_GET['search']) ? '&search=true' : ''), '', 'postcommentforum');
	showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
	showtableheader();
	showsetting('postcomment_search_detail', 'detail', $detail, 'radio');
	showsetting('comment_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('postcomment_content', 'message', $message, 'text');
	showsetting('postcomment_search_tid', 'searchtid', $searchtid, 'text');
	showsetting('postcomment_search_pid', 'searchpid', $searchpid, 'text');
	showsetting('postcomment_search_author', 'author', $author, 'text');
	showsetting('postcomment_search_authorid', 'authorid', $authorid, 'text');
	showsetting('comment_search_ip', 'ip', $ip, 'text');
	showsetting('postcomment_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
	$cids = authcode($cids, 'DECODE');
	$cidsadd = $cids ? explode(',', $cids) : $_GET['delete'];
	$pids = array();
	foreach(C::t('forum_postcomment')->fetch_all($cidsadd) as $postcomment) {
		$pids[$postcomment['pid']] = $postcomment['pid'];
	}
	C::t('forum_postcache')->delete($pids);
	$cidsadd && C::t('forum_postcomment')->delete($cidsadd);
	$cpmsg = cplang('postcomment_delete');

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('postcommentforum').searchsubmit.click();</script>
<?php
}

if(submitcheck('searchsubmit') || $newlist) {

	$comments = $commentcount = '0';
	$sql = $error = '';
	$author = trim($author);

	if($author != '') {
		$authorids = C::t('common_member')->fetch_all_uid_by_username(array_map('trim', explode(',', $author)));
		$authorid = ($authorid ? $authorid.',' : '').implode(',',$authorids);
	}
	$authorid = trim($authorid,', ');

	if($starttime != '0') {
		$starttime = strtotime($starttime);
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '0') {
			$endtime = strtotime($endtime);
		}
	} else {
		$endtime = TIMESTAMP;
	}


	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'comment_mod_range_illegal';
	}


	if(!$error) {
		if($detail) {
			$commentcount = C::t('forum_postcomment')->count_by_search($searchtid, $searchpid, ($authorid ? explode(',', str_replace(' ', '', $authorid)) : null), $starttime, $endtime, $ip, $message);
			if($commentcount) {
				$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
				$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];

				$comments = '';

				foreach(C::t('forum_postcomment')->fetch_all_by_search($searchtid, $searchpid, ($authorid ? explode(',', str_replace(' ', '', $authorid)) : null), $starttime, $endtime, $ip, $message, (($page - 1) * $perpage), $perpage) as $comment) {
					$comment['dateline'] = dgmdate($comment['dateline']);
					$comments .= showtablerow('', '', array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$comment[id]\" />",
						str_replace(array('[b]', '[/b]', '[/color]'), array('<b>', '</b>', '</font>'), preg_replace("/\[color=([#\w]+?)\]/i", "<font color=\"\\1\">", $comment['comment'])),
						($comment['author'] ? "<a href=\"home.php?mod=space&uid=$comment[authorid]\" target=\"_blank\">".$comment['author']."</a>" : cplang('postcomment_guest')),
						$comment['dateline'],
						$comment['useip'],
						"<a href=\"forum.php?mod=redirect&goto=findpost&ptid=$comment[tid]&pid=$comment[pid]\" target=\"_blank\">".cplang('postcomment_pid')."</a>"
					), TRUE);
				}

				$multi = multi($commentcount, $perpage, $page, ADMINSCRIPT."?action=postcomment");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=postcomment&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=postcomment&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				$error = 'postcomment_nonexistence';
			}
		} else {
			$commentcount = 0;
			foreach(C::t('forum_postcomment')->fetch_all_by_search($searchtid, $searchpid, ($authorid ? explode(',', str_replace(' ', '', $authorid)) : null), $starttime, $endtime, $ip, $message) as $row) {
				$cids .= ','.$row['id'];
				$commentcount++;
			}
			$multi = '';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit || $newlist);
	showformheader('postcomment&frame=no', 'target="postcommentframe"');
	showhiddenfields(array('cids' => authcode($cids, 'ENCODE')));
	if(!$search_tips) {
		showtableheader(cplang('postcomment_new_result').' '.$commentcount, 'fixpadding');
	} else {
		showtableheader(cplang('postcomment_result').' '.$commentcount.(empty($newlist) ? ' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'postcommentforum\').pp.value=\'\';$(\'postcommentforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>' : ''), 'fixpadding');
	}

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} elseif($detail) {
		showsubtitle(array('', 'postcomment_content', 'author', 'time', 'ip' ,''));
		echo $comments;
	}

	showsubmit('postcommentsubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="postcommentframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>