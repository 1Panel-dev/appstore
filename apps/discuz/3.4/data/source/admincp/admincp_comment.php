<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_comment.php 28774 2012-03-12 10:09:50Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$detail = $_GET['detail'];
$idtype = $_GET['idtype'];
$id = $_GET['id'];
$author = $_GET['author'];
$authorid = $_GET['authorid'];
$uid = $_GET['uid'];
$message = $_GET['message'];
$ip = $_GET['ip'];
$users = $_GET['users'];
$starttime = $_GET['starttime'];
$endtime = $_GET['endtime'];
$searchsubmit = $_GET['searchsubmit'];
$cids = $_GET['cids'];
$page = max(1, $_GET['page']);

$fromumanage = $_GET['fromumanage'] ? 1 : 0;

cpheader();
if(empty($operation)) {
	if(!submitcheck('commentsubmit')) {

		if($fromumanage) {
			$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? '' : $starttime;
			$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? '' : $endtime;
		} else {
			$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
			$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;
		}

		shownav('topic', 'nav_comment');
		showsubmenu('nav_comment', array(
			array('comment_comment', 'comment', 1),
			array('comment_article_comment', 'comment&operation=article', 0),
			array('comment_topic_comment', 'comment&operation=topic', 0)
		));
		showtips('comment_tips');
		echo <<<EOT
	<script type="text/javascript" src="static/js/calendar.js"></script>
	<script type="text/JavaScript">
	function page(number) {
		$('commentforum').page.value=number;
		$('commentforum').searchsubmit.click();
	}
	</script>
EOT;
		showtagheader('div', 'searchposts', !$searchsubmit);
		showformheader("comment", '', 'commentforum');
		showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
		showtableheader();
		showsetting('comment_search_detail', 'detail', $detail, 'radio');
		showsetting('comment_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
		showsetting('comment_idtype', array('idtype', array(
			array('', $lang['all']),
			array('uid', $lang['comment_uid']),
			array('blogid', $lang['comment_blogid']),
			array('picid', $lang['comment_picid']),
			array('sid', $lang['comment_sid']),
		)), 'comment_idtype', 'select');
		showsetting('comment_search_id', 'id', $id, 'text');
		showsetting('comment_search_author', 'author', $author, 'text');
		showsetting('comment_search_authorid', 'authorid', $authorid, 'text');
		showsetting('comment_search_uid', 'uid', $uid, 'text');
		showsetting('comment_search_message', 'message', $message, 'text');
		showsetting('comment_search_ip', 'ip', $ip, 'text');
		showsetting('comment_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
		echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {
		$cids = authcode($cids, 'DECODE');
		$cidsadd = $cids ? explode(',', $cids) : $_GET['delete'];
		include_once libfile('function/delete');
		$deletecount = count(deletecomments($cidsadd));
		$cpmsg = cplang('comment_succeed', array('deletecount' => $deletecount));

	?>
	<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('commentforum').searchsubmit.click();</script>
	<?php

	}

	if(submitcheck('searchsubmit', 1)) {

		$comments = $commentcount = '0';
		$sql = $error = '';
		$authorids = array();
		$author = trim($author);

		if($id !='') {
			$id = explode(',', $id);
		}

		if($author != '') {
			$authorids = C::t('common_member')->fetch_all_uid_by_username(array_map('trim', explode(',', $author)));
			if (!$authorids) {
				$authorids = array(-1);
			}
		}

		$authorid = trim($authorid, ', ');
		if($authorid != '') {
			if (!$authorids) {
				$authorids = explode(',', $authorid);
			} else {
				$authorids = array_intersect($authorids, explode(',', $authorid));
			}
			if (!$authorids) {
				$authorids = array(-1);
			}
		}

		if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
			if($endtime != '') {
				$endtime = strtotime($endtime);
			}
		} else {
			$endtime = TIMESTAMP;
		}

		if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
			$error = 'comment_mod_range_illegal';
		}

		$uid = trim($uid, ', ');
		if($uid !='') {
			$uid = explode(',', $uid);
		}

		if(!$error) {
			if($commentcount = C::t('home_comment')->fetch_all_search(3, $id, $authorids, $uid, $ip, $message, $idtype, $starttime, $endtime)) {
				if($detail) {
					$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
					$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];
					$query = C::t('home_comment')->fetch_all_search(1, $id, $authorids, $uid, $ip, $message, $idtype, $starttime, $endtime, (($page - 1) * $perpage), $perpage);
					$comments = '';

					foreach ($query as $comment) {
						$comment['dateline'] = dgmdate($comment['dateline']);
						switch($comment['idtype']) {
							case 'picid':
								$address = "<a href=\"home.php?mod=space&uid=$comment[uid]&do=album&picid=$comment[id]\" target=\"_blank\">$comment[message]</a>";
								break;
							case 'uid':
								$address = "<a href=\"home.php?mod=space&uid=$comment[uid]&do=wall\" target=\"_blank\">$comment[message]</a>";
								break;
							case 'sid':
								$address = "<a href=\"home.php?mod=space&uid=1&do=share&id=$comment[id]\" target=\"_blank\">$comment[message]</a>";
								break;
							case 'blogid':
								$address = "<a href=\"home.php?mod=space&uid=$comment[uid]&do=blog&id=$comment[id]\" target=\"_blank\">$comment[message]</a>";
								break;
						}
						$comments .= showtablerow('', '', array(
							"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$comment[cid]\" />",
							$address,
							"<a href=\"home.php?mod=space&uid=$comment[uid]\" target=\"_blank\">$comment[author]</a>",
							$comment['ip'],
							$comment['idtype'],
							$comment['dateline']
						), TRUE);
					}
					$multi = multi($commentcount, $perpage, $page, ADMINSCRIPT."?action=comment");
					$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=comment&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
					$multi = str_replace("window.location='".ADMINSCRIPT."?action=comment&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				$query = C::t('home_comment')->fetch_all_search(2, $id, $authorids, $uid, $ip, $message, $idtype, $starttime, $endtime);
				foreach ($query as $comment) {
					$cids .= ','.$comment['cid'];
				}
			}
		} else
			$error = 'comment_post_nonexistence';
		}

		showtagheader('div', 'postlist', $searchsubmit);
		showformheader('comment&frame=no', 'target="commentframe"');
		showhiddenfields(array('cids' => authcode($cids, 'ENCODE')));
		showtableheader(cplang('comment_result').' '.$commentcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'commentforum\').pp.value=\'\';$(\'commentforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

		if($error) {
			echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
		} else {
			if($detail) {
				showsubtitle(array('', 'message', 'author', 'ip', 'comment_idtype', 'time'));
				echo $comments;
			}
		}

		showsubmit('commentsubmit', 'delete', $detail ? 'del' : '', '', $multi);
		showtablefooter();
		showformfooter();
		echo '<iframe name="commentframe" style="display:none"></iframe>';
		showtagfooter('div');

	}
}

if($operation == 'article' || $operation == 'topic') {

	$aid = $_GET['aid'];
	$subject = $_GET['subject'];
	$idtype = $operation == 'article' ? 'aid' : 'topicid';
	$tablename = $idtype == 'aid' ? 'portal_article_title' : 'portal_topic';

	if(!submitcheck('articlesubmit')) {

		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
		$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;

		shownav('topic', 'nav_comment');
		showsubmenu('nav_comment', array(
			array('comment_comment', 'comment', 0),
			array('comment_article_comment', 'comment&operation=article', $operation == 'article' ? 1 : 0),
			array('comment_topic_comment', 'comment&operation=topic',  $operation == 'topic' ? 1 : 0)
		));
		showtips('comment_'.$operation.'_tips');
		echo <<<EOT
	<script type="text/javascript" src="static/js/calendar.js"></script>
	<script type="text/JavaScript">
	function page(number) {
		$('articleforum').page.value=number;
		$('articleforum').searchsubmit.click();
	}
	</script>
EOT;
		showtagheader('div', 'searchposts', !$searchsubmit);
		showformheader("comment&operation=$operation", '', 'articleforum');
		showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
		showtableheader();
		showsetting('comment_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
		showsetting("comment_{$operation}_subject", 'subject', $subject, 'text');
		showsetting("comment_{$operation}_id", 'aid', $aid, 'text');
		showsetting('comment_search_message', 'message', $message, 'text');
		showsetting('comment_search_author', 'author', $author, 'text');
		showsetting('comment_search_authorid', 'authorid', $authorid, 'text');
		showsetting('comment_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {


		$commentnum = array();
		foreach(C::t('portal_comment')->fetch_all($_GET['delete']) as $value) {
			$commentnum[$value['idtype']][$value['id']] = $value['id'];
		}
		if($commentnum['aid']) {
			C::t('portal_article_count')->increase($commentnum['aid'], array('commentnum' => -1));
		} elseif($commentnum['topicid']) {
			C::t('portal_topic')->increase($commentnum['topicid'], array('commentnum' => -1));
		}
		C::t('portal_comment')->delete($_GET['delete']);
		$cpmsg = cplang('comment_article_delete');

	?>
	<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('articleforum').searchsubmit.click();</script>
	<?php
	}

	if(submitcheck('searchsubmit')) {

		$comments = $commentcount = '0';
		$sql = $error = '';
		$author = trim($author);

		$queryAId = $aid ? array($aid) : array();

		if($subject != '') {

				$ids = array();
				$query = C::t($tablename)->fetch_all_by_title($idtype, $subject);
				foreach($query as $value) {
					$ids[] = intval($value[$idtype]);
				}
				$queryAId = array_merge($queryAId, $ids);
		}



		$queryAuthorIDs = $authorid ? array($authorid) : array();

		if($author != '') {
			$authorids = C::t('common_member')->fetch_all_uid_by_username(array_map('trim', explode(',', $author)));
			$queryAuthorIDs = array_merge($queryAuthorIDs, $authorids);
		}


		if($starttime != '0') {
			$starttime = strtotime($starttime);
		}

		$sqlendtime = '';

		if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
			if($endtime != '0') {
				$sqlendtime = $endtime = strtotime($endtime);
			}
		} else {
			$endtime = TIMESTAMP;
		}

		if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
			$error = 'comment_mod_range_illegal';
		}



		if(!$error) {

			$commentcount = C::t('portal_comment')->count_all_by_search($queryAId, $queryAuthorIDs, $starttime, $sqlendtime, $idtype, $message);
			if($commentcount) {
				$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
				$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];
				$query = C::t('portal_comment')->fetch_all_by_search($queryAId, $queryAuthorIDs, $starttime, $sqlendtime, $idtype, $message, (($page - 1) * $perpage), $perpage);

				$comments = '';

				$mod = $idtype == 'aid' ? 'view' : 'topic';
				foreach($query as $comment) {
					$comment['dateline'] = dgmdate($comment['dateline']);
					$comments .= showtablerow('', '', array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$comment[cid]\" />",
						"<a href=\"portal.php?mod=$mod&$idtype=$comment[id]\" target=\"_blank\">$comment[title]</a>",
						$comment[message],
						"<a href=\"home.php?mod=space&uid=$comment[uid]\" target=\"_blank\">$comment[username]</a>",
						$comment['dateline']
					), TRUE);
				}

				$multi = multi($commentcount, $perpage, $page, ADMINSCRIPT."?action=comment&operation=$operation");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=comment&operation=$operation&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=comment&amp;operation=$operation&amp;page='+this.value", "page(this.value)", $multi);

			} else {
				$error = 'comment_post_nonexistence';
			}
		}

		showtagheader('div', 'postlist', $searchsubmit);
		showformheader('comment&operation='.$operation.'&frame=no', 'target="articleframe"');
		showtableheader(cplang('comment_result').' '.$commentcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'articleforum\').pp.value=\'\';$(\'articleforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

		if($error) {
			echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
		} else {
			showsubtitle(array('', 'article_title', 'message', 'author', 'time'));
			echo $comments;
		}

		showsubmit('articlesubmit', 'delete', 'del', '', $multi);
		showtablefooter();
		showformfooter();
		echo '<iframe name="articleframe" style="display:none"></iframe>';
		showtagfooter('div');

	}
}
?>