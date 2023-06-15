<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_attach.php 31441 2012-08-28 07:46:04Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$searchsubmit = $_GET['searchsubmit'];

if(!submitcheck('deletesubmit')) {

	require_once libfile('function/forumlist');
	$anchor = isset($_GET['anchor']) ? $_GET['anchor'] : '';
	$anchor = in_array($anchor, array('search', 'admin')) ? $anchor : 'search';

	shownav('topic', 'nav_attaches'.($operation ? '_'.$operation : ''));
	showsubmenusteps('nav_attaches'.($operation ? '_'.$operation : ''), array(
		array('search', !$searchsubmit),
		array('admin', $searchsubmit),
	));
	showtips('attach_tips', 'attach_tips', $searchsubmit);
	showtagheader('div', 'search', !$searchsubmit);
	showformheader('attach'.($operation ? '&operation='.$operation : ''));
	showtableheader();
	showsetting('attach_nomatched', 'nomatched', 0, 'radio');
	if($operation != 'group') {
		showsetting('attach_forum', '', '', '<select name="inforum"><option value="all">&nbsp;&nbsp;>'.cplang('all').'</option><option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>');
	}
	showsetting('attach_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('attach_sizerange', array('sizeless', 'sizemore'), array('', ''), 'range');
	showsetting('attach_dlcountrange', array('dlcountless', 'dlcountmore'), array('', ''), 'range');
	showsetting('attach_daysold', 'daysold', '', 'text');
	showsetting('filename', 'filename', '', 'text');
	showsetting('attach_keyword', 'keywords', '', 'text');
	showsetting('attach_author', 'author', '', 'text');
	showsubmit('searchsubmit', 'search');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

	if(submitcheck('searchsubmit')) {

		require_once libfile('function/attachment');
		$operation == 'group' && $_GET['inforum'] = 'isgroup';
		$inforum = $_GET['inforum'] != 'all' && $_GET['inforum'] != 'isgroup' ? intval($_GET['inforum']) : $_GET['inforum'];
		$authorid = $_GET['author'] ? C::t('common_member')->fetch_uid_by_username($_GET['author']) : 0;
		$authorid = $_GET['author'] && !$authorid ? C::t('common_member_archive')->fetch_uid_by_username($_GET['author']) : $authorid;
		$attachments = '';
		$attachuids = $attachusers = array();
		$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
		$perpage = ($_GET['pp'] ? $_GET['pp'] : $_GET['perpage']) / 10;
		$attachmentcount = 0;
		for($attachi = 0;$attachi < 10;$attachi++) {
			$attachmentarray = array();
			$attachmentcount += C::t('forum_attachment')->fetch_all_for_manage($attachi, $inforum, $authorid, $_GET['filename'], $_GET['keywords'], $_GET['sizeless'], $_GET['sizemore'], $_GET['dlcountless'], $_GET['dlcountmore'], $_GET['daysold'], 1);
			$query = C::t('forum_attachment')->fetch_all_for_manage($attachi, $inforum, $authorid, $_GET['filename'], $_GET['keywords'], $_GET['sizeless'], $_GET['sizemore'], $_GET['dlcountless'], $_GET['dlcountmore'], $_GET['daysold'], 0, (($page - 1) * $perpage), $perpage);
			foreach($query as $attachment) {
				$attachuids[$attachment['uid']] = $attachment['uid'];
				$attachmentarray[] = $attachment;
			}
			$attachusers += C::t('common_member')->fetch_all($attachuids);

			foreach($attachmentarray as $attachment) {
				if(!$attachment['remote']) {
					$matched = file_exists($_G['setting']['attachdir'].'/forum/'.$attachment['attachment']) ? '' : cplang('attach_lost');
					$attachment['url'] = $_G['setting']['attachurl'].'forum/';
				} else {
					@set_time_limit(0);
					if(@fclose(@fopen($_G['setting']['ftp']['attachurl'].'forum/'.$attachment['attachment'], 'r'))) {
						$matched = '';
					} else {
						$matched = cplang('attach_lost');
					}
					$attachment['url'] = $_G['setting']['ftp']['attachurl'].'forum/';
				}
				$attachsize = sizecount($attachment['filesize']);
				if(!$_GET['nomatched'] || ($_GET['nomatched'] && $matched)) {
					$attachment['url'] = trim($attachment['url'], '/');
					$attachments .= showtablerow('', array('class="td25"', 'title="'.$attachment['description'].'" class="td21"'), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$attachment[aid]\" />",
						$attachment['remote'] ? "<span class=\"diffcolor3\">$attachment[filename]" : $attachment['filename'],
						$attachusers[$attachment['uid']]['username'],
						"<a href=\"forum.php?mod=viewthread&tid=$attachment[tid]\" target=\"_blank\">".cutstr($attachment['subject'], 20)."</a>",
						$attachsize,
						$attachment['downloads'],
						$matched ? "<em class=\"error\">$matched<em>" : "<a href=\"forum.php?mod=attachment&aid=".aidencode($attachment['aid'])."&noupdate=yes\" target=\"_blank\" class=\"act nomargin\">$lang[download]</a>"
					), TRUE);
				}
			}
		}

		$multipage = '<div class="cuspages right"><div class="pg">'.
				($page > 1 ? '<a href="javascript:page('.($page-1).')" class="nxt">&lsaquo;&lsaquo;</a>' : '').
				'<a href="javascript:page('.($page+1).')" class="nxt">&rsaquo;&rsaquo;</a>'.
				'</div></div>';

		echo <<<EOT
<script type="text/JavaScript">
	function page(number) {
		$('attachmentforum').page.value=number;
		$('attachmentforum').searchsubmit.click();
	}
</script>
EOT;
		showtagheader('div', 'admin', $searchsubmit);
		showformheader('attach'.($operation ? '&operation='.$operation : ''), '', 'attachmentforum');
		showhiddenfields(array(
			'page' => $page,
			'nomatched' => $_GET['nomatched'],
			'inforum' => $_GET['inforum'],
			'sizeless' => $_GET['sizeless'],
			'sizemore' => $_GET['sizemore'],
			'dlcountless' => $_GET['dlcountless'],
			'dlcountmore' => $_GET['dlcountmore'],
			'daysold' => $_GET['daysold'],
			'filename' => $_GET['filename'],
			'keywords' => $_GET['keywords'],
			'author' => $_GET['author'],
			'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']
		));
		echo '<input type="submit" name="searchsubmit" value="'.cplang('submit').'" class="btn" style="display: none" />';
		showformfooter();

		showformheader('attach&frame=no'.($operation ? '&operation='.$operation : ''), 'target="attachmentframe"');
		showtableheader();
		showsubtitle(array('', 'filename', 'author', 'attach_thread', 'size', 'attach_downloadnums', ''));
		echo $attachments;
		showsubmit('deletesubmit', 'submit', 'del', '<a href="###" onclick="$(\'admin\').style.display=\'none\';$(\'search\').style.display=\'\';$(\'attachmentforum\').pp.value=\'\';$(\'attachmentforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', $multipage);
		showtablefooter();
		showformfooter();
		echo '<iframe name="attachmentframe" style="display:none"></iframe>';
		showtagfooter('div');

	}

} else {

	if($_GET['delete']) {

		$tids = $pids = array();
		for($attachi = 0;$attachi < 10;$attachi++) {
			foreach(C::t('forum_attachment_n')->fetch_all($attachi, $_GET['delete']) as $attach) {
				dunlink($attach);
				$tids[$attach['tid']] = $attach['tid'];
				$pids[$attach['pid']] = $attach['pid'];
			}
			C::t('forum_attachment_n')->delete($attachi, $_GET['delete']);

			$attachtids = array();
			foreach(C::t('forum_attachment_n')->fetch_all_by_id($attachi, 'tid', $tids) as $attach) {
				unset($tids[$attach['tid']]);
			}
			if($tids) {
				C::t('forum_thread')->update($tids, array('attachment' => 0));
			}

			$attachpids = array();
			foreach(C::t('forum_attachment_n')->fetch_all_by_id($attachi, 'pid', $pids) as $attach) {
				$attachpids[$attach['pid']] = $attach['pid'];
			}
		}

		if($attachpids) {
			$pids = array_diff($pids, $attachpids);
		}
		loadcache('posttableids');
		$posttableids = $_G['cache']['posttableids'] ? $_G['cache']['posttableids'] : array('0');
		foreach($posttableids as $id) {
			C::t('forum_post')->update($id, $pids, array('attachment' => '0'));
		}

		$cpmsg = cplang('attach_edit_succeed');

	} else {

		$cpmsg = cplang('attach_edit_invalid');

	}

	echo "<script type=\"text/JavaScript\">alert('$cpmsg');parent.\$('attachmentforum').searchsubmit.click();</script>";
}

?>