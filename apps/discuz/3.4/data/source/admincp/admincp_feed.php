<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_feed.php 31634 2012-09-17 06:43:39Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$operation = $operation ? $operation : 'search';

shownav('topic', 'nav_feed');
$anchor = in_array($operation, array('search', 'global')) ? $operation : 'search';
$current = array($anchor => 1);
showsubmenu('nav_feed', array(
	array('nav_feed', 'feed', $current['search']),
	array('feed_global', 'feed&operation=global', $current['global']),
));

if($operation == 'global') {

	if(!submitcheck('globalsubmit')) {
		$feedid = intval($_GET['feedid']);
		$feed = array();
		if($feedid) {
			$feed = C::t('home_feed')->fetch('', '', '', $feedid);

			if($feed['uid']) {
				require_once libfile('function/feed');
				$feed = mkfeed($feed);
			}
			$feed['body_template'] = dhtmlspecialchars($feed['body_template']);
			$feed['body_general'] = dhtmlspecialchars($feed['body_general']);

			$feed['dateline'] = dgmdate($feed['dateline'], 'Y-m-d H:i');
		}
		if(empty($feed['dateline'])) {
			$feed['dateline'] = dgmdate($_G['timestamp'], 'Y-m-d H:i');
		}
		showformheader('feed&operation=global', $feed['uid'] ? '' : 'onsubmit="edit_save();"');
		echo '<script type="text/javascript" src="'.STATICURL.'image/editor/editor_function.js"></script>';
		echo "<input type=\"hidden\" name=\"feednew[feedid]\" value=\"$feed[feedid]\" /><input type=\"hidden\" name=\"feednew[feeduid]\" value=\"$feed[uid]\" />";
		showtableheader();
		if(empty($feed['uid'])) {
			showsetting('feed_global_title', 'feednew[title_template]', $feed['title_template'], 'text');
			$src = 'home.php?mod=editor&charset='.CHARSET.'&allowhtml=1&doodle=0';
			print <<<EOF
			<tr><td>{$lang['message']}</td><td></td></tr>
			<tr>
				<td colspan="2">
					<textarea class="userData" name="feednew[body_template]" id="uchome-ttHtmlEditor" style="height:100%;width:100%;display:none;border:0px" onkeydown="textareakey(this, event)">$feed[body_template]</textarea>
					<iframe src="$src" name="uchome-ifrHtmlEditor" id="uchome-ifrHtmlEditor" scrolling="no" border="0" frameborder="0" style="width:100%;border: 1px solid #C5C5C5;" height="400"></iframe>
				<td>
			</tr>
EOF;
			showsetting('feed_global_body_general', 'feednew[body_general]', $feed['body_general'], 'text');
		} else {
			print <<<EOF
			<tr><td class="td27">$lang[feed_global_title]</td><td></td></tr>
			<tr class="noborder"><td colspan="2">$feed[title_template]&nbsp;<td></tr>

			<tr><td class="td27">$lang[message]</td><td></td></tr>
			<tr class="noborder"><td colspan="2">$feed[body_template]&nbsp;<td></tr>

			<tr><td class="td27">$lang[feed_global_body_general]</td><td></td></tr>
			<tr class="noborder"><td colspan="2">$feed[body_general]&nbsp;<td></tr>
EOF;
		}

		showsetting('feed_global_image_1', 'feednew[image_1]', $feed['image_1'], 'text');
		showsetting('feed_global_image_1_link', 'feednew[image_1_link]', $feed['image_1_link'], 'text');
		showsetting('feed_global_image_2', 'feednew[image_2]', $feed['image_2'], 'text');
		showsetting('feed_global_image_2_link', 'feednew[image_2_link]', $feed['image_2_link'], 'text');
		showsetting('feed_global_image_3', 'feednew[image_3]', $feed['image_3'], 'text');
		showsetting('feed_global_image_3_link', 'feednew[image_3_link]', $feed['image_3_link'], 'text');
		showsetting('feed_global_image_4', 'feednew[image_4]', $feed['image_4'], 'text');
		showsetting('feed_global_image_4_link', 'feednew[image_4_link]', $feed['image_4_link'], 'text');

		showsetting('feed_global_dateline', 'feednew[dateline]', $feed['dateline'], 'text');
		if($feed['id']) {
			showsetting('feed_global_hot', 'feednew[hot]', $feed['hot'], 'text');
		}
		showsubmit('globalsubmit');
		showtablefooter();
		showformfooter();
	} else {
		$feednew = getgpc('feednew');
		$feedid = intval($feednew['feedid']);

		if(empty($feednew['feeduid']) || empty($feedid)) {
			$setarr = array(
				'title_template' => trim($feednew['title_template']),
				'body_template' => trim($feednew['body_template'])
			);
			if(empty($setarr['title_template']) && empty($setarr['body_template'])) {
				cpmsg('sitefeed_error', '', 'error');
			}

		} else {
			$setarr = array();
		}

		$feednew['dateline'] = trim($feednew['dateline']);
		if($feednew['dateline']) {
			require_once libfile('function/home');
			$newtimestamp = strtotime($feednew['dateline']);
			if($newtimestamp > $_G['timestamp']) {
				$_G['timestamp'] = $newtimestamp;
			}
		}

		if(empty($feedid)) {
			$_G['uid'] = 0;
			require_once libfile('function/feed');
			$feedid = feed_add('sitefeed',
				trim($feednew['title_template']),array(),
				trim($feednew['body_template']),array(),
				trim($feednew['body_general']),
				array(trim($feednew['image_1']),trim($feednew['image_2']),trim($feednew['image_3']),trim($feednew['image_4'])),
				array(trim($feednew['image_1_link']),trim($feednew['image_2_link']),trim($feednew['image_3_link']),trim($feednew['image_4_link'])),
				'','','',1
			);

		} else {
			if(empty($feednew['feeduid'])) {
				$setarr['body_general'] = trim($feednew['body_general']);
			}
			$setarr['image_1'] = trim($feednew['image_1']);
			$setarr['image_1_link'] = trim($feednew['image_1_link']);
			$setarr['image_2'] = trim($feednew['image_2']);
			$setarr['image_2_link'] = trim($feednew['image_2_link']);
			$setarr['image_3'] = trim($feednew['image_3']);
			$setarr['image_3_link'] = trim($feednew['image_3_link']);
			$setarr['image_4'] = trim($feednew['image_4']);
			$setarr['image_4_link'] = trim($feednew['image_4_link']);

			$setarr['dateline'] = $newtimestamp;
			$setarr['hot'] = intval($feednew['hot']);

			C::t('home_feed')->update('', $setarr, '', '', $feedid);
		}
		cpmsg('feed_global_add_success', '', 'succeed');
	}

} else {

	$detail = $_GET['detail'];
	$uid = $_GET['uid'];
	$users = $_GET['users'];
	$feedid = $_GET['feedid'];
	$icon = $_GET['icon'];
	$hot1 = $_GET['hot1'];
	$hot2 = $_GET['hot2'];
	$starttime = $_GET['starttime'];
	$endtime = $_GET['endtime'];
	$searchsubmit = $_GET['searchsubmit'];
	$feedids = $_GET['feedids'];

	$fromumanage = $_GET['fromumanage'] ? 1 : 0;

	showtips('feed_tips');
	if(!submitcheck('feedsubmit')) {

		if($fromumanage) {
			$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? '' : $starttime;
			$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? '' : $endtime;
		} else {
			$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
			$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;
		}

		echo <<<EOT
	<script type="text/javascript" src="static/js/calendar.js"></script>
	<script type="text/JavaScript">
	function page(number) {
		$('feedforum').page.value=number;
		$('feedforum').searchsubmit.click();
	}
	</script>
EOT;
		showtagheader('div', 'searchposts', !$searchsubmit);
		showformheader("feed", '', 'feedforum');
		showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
		showtableheader();
		showsetting('feed_search_detail', 'detail', $detail, 'radio');
		showsetting('feed_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
		$selected[$icon] = $icon ? 'selected="selected"' : '';
		showsetting('feed_search_icon', '', $icon, "<select name='icon'><option value=''>$lang[all]</option><option value='blog' $selected[blog]>$lang[feed_blog]</option>
			<option value='thread' $selected[thread]>$lang[feed_thread]</option><option value='album' $selected[album]>$lang[feed_album]</option><option value='doing' $selected[doing]>$lang[doing]</option>
			<option value='share' $selected[share]>$lang[shares]</option><option value='friend' $selected[friend]>$lang[feed_friend]</option><option value='poll' $selected[poll]>$lang[feed_poll]</option>
			<option value='comment' $selected[comment]>$lang[feed_comment]</option><option value='click' $selected[click]>$lang[feed_click]</option>
			<option value='show' $selected[show]>$lang[feed_show]</option><option value='profile' $selected[profile]>$lang[feed_profile]</option><option value='sitefeed' $selected[sitefeed]>$lang[feed_sitefeed]</option></select>");
		showsetting('feed_search_uid', 'uid', $uid, 'text');
		showsetting('feed_search_user', 'users', $users, 'text');
		showsetting('feed_search_feedid', 'feedid', $feedid, 'text');
		showsetting('feed_search_hot', array('hot1', 'hot2'), array('', ''), 'range');
		showsetting('feed_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
		echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {
		$feedids = authcode($feedids, 'DECODE');
		$feedidsadd = $feedids ? explode(',', $feedids) : $_GET['delete'];
		include_once libfile('function/delete');
		$deletecount = count(deletefeeds($feedidsadd));
		$cpmsg = cplang('feed_succeed', array('deletecount' => $deletecount));

	?>
	<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('feedforum').searchsubmit.click();</script>
	<?php

	}

	if(submitcheck('searchsubmit', 1)) {

		$feedids = $feedcount = '0';
		$sql = $error = '';
		$users = trim($users);

		if($users != '') {
			$uids = array(-1);
			$query = C::t('home_feed')->fetch_uid_by_username(explode(',', $users));
			$uids = array_keys($query) + $uids;
		}

		if($icon != '') {
			$feedarr = C::t('home_feed')->fetch_icon_by_icon($icon);
			$icon = $feedarr['icon'];
			if($icon == '') {
				$icon = '-1';
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

		if($feedid != '') {
			$feedids = array(-1);
			$query = C::t('home_feed')->fetch_feedid_by_feedid(explode(',', $feedid));
			$feedids = array_keys($query) + $feedids;
		}

		if($uid != '') {
			$query = C::t('home_feed')->fetch_uid_by_uid(explode(',', $uid));
			if(!$uids) {
				$uids = array_keys($query);
			} else {
				$uids = array_intersect(array_keys($query), $uids);
			}
			if(!$uids) {
				$uids = array(-1);
			}
		}


		if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
			$error = 'feed_mod_range_illegal';
		}

		if(!$error) {
			if($detail) {
				$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
				$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];
				$query = C::t('home_feed')->fetch_all_by_search(1, $uids, $icon, $starttime, $endtime, $feedids, $hot1, $hot2, (($page - 1) * $perpage), $perpage);
				$feeds = '';
				include_once libfile('function/feed');
				foreach ($query as $feed) {
					$feed['dateline'] = dgmdate($feed['dateline']);

					$feed = mkfeed($feed);

					$feeds .= showtablerow('', array('style="width:20px;"', 'style="width:260px;"', '', 'style="width:120px;"', 'style="width:60px;"'), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$feed[feedid]\" />",
						$feed['title_template'],
						$feed['body_template'],
						$feed['dateline'],
						'<a href="'.ADMINSCRIPT.'?action=feed&operation=global&feedid='.$feed['feedid'].'">'.$lang['edit'].'</a>'
					), TRUE);
				}
				$feedcount = C::t('home_feed')->fetch_all_by_search(3, $uids, $icon, $starttime, $endtime, $feedids, $hot1, $hot2);
				$multi = multi($feedcount, $perpage, $page, ADMINSCRIPT."?action=feed");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=feed&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=feed&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				$feedcount = 0;
				$query = C::t('home_feed')->fetch_all_by_search(2, $uids, $icon, $starttime, $endtime, $feedids, $hot1, $hot2);
				foreach ($query as $feed) {
					$feedids .= ','.$feed['feedid'];
					$feedcount++;
				}
				$multi = '';
			}

			if(!$feedcount) {
				$error = 'feed_post_nonexistence';
			}
		}

		showtagheader('div', 'postlist', $searchsubmit);
		showformheader('feed&frame=no', 'target="feedframe"');
		showhiddenfields(array('feedids' => authcode($feedids, 'ENCODE')));
		showtableheader(cplang('feed_result').' '.$feedcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'feedforum\').pp.value=\'\';$(\'feedforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

		if($error) {
			echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
		} else {
			if($detail) {
				showsubtitle(array('', 'feed_title', 'feed_body', 'time', ''));
				echo $feeds;
			}
		}

		showsubmit('feedsubmit', 'delete', $detail ? 'del' : '', '', $multi);
		showtablefooter();
		showformfooter();
		echo '<iframe name="feedframe" style="display:none"></iframe>';
		showtagfooter('div');

	}
}
?>