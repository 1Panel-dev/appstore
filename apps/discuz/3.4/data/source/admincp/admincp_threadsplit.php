<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_threadsplit.php 29236 2012-03-30 05:34:47Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@set_time_limit(0);
define('IN_DEBUG', false);

define('MAX_THREADS_MOVE', 100);

cpheader();
$topicperpage = 50;
if(empty($operation)) {
	$operation = 'manage';
}
$settings = C::t('common_setting')->fetch_all(array('threadtableids', 'threadtable_info'), true);
$threadtableids = $settings['threadtableids'] ? $settings['threadtableids'] : array();
$threadtable_info = $settings['threadtable_info'] ? $settings['threadtable_info'] : array();
if($operation == 'manage') {
	shownav('founder', 'nav_threadsplit');
	if(!submitcheck('threadsplit_update_submit')) {
		showsubmenu('nav_threadsplit', array(
			array('nav_threadsplit_manage', 'threadsplit&operation=manage', 1),
			array('nav_threadsplit_move', 'threadsplit&operation=move', 0),
		));
		showtips('threadsplit_manage_tips');
		showformheader('threadsplit&operation=manage');
		showtableheader('threadsplit_manage_table_orig');

		$thread_table_orig = C::t('forum_thread')->gettablestatus();
		showsubtitle(array('threadsplit_manage_tablename', 'threadsplit_manage_threadcount', 'threadsplit_manage_datalength', 'threadsplit_manage_indexlength', 'threadsplit_manage_table_createtime', 'threadsplit_manage_table_memo', ''));
		showtablerow('', array(), array($thread_table_orig['Name'], $thread_table_orig['Rows'], $thread_table_orig['Data_length'], $thread_table_orig['Index_length'], $thread_table_orig['Create_time'], "<input type=\"text\" class=\"txt\" name=\"memo[0]\" value=\"{$threadtable_info[0]['memo']}\" />", ''));

		showtableheader('threadsplit_manage_table_archive');
		showsubtitle(array('threadsplit_manage_tablename', 'threadsplit_manage_dislayname', 'threadsplit_manage_threadcount', 'threadsplit_manage_datalength', 'threadsplit_manage_indexlength', 'threadsplit_manage_table_createtime', 'threadsplit_manage_table_memo', ''));
		foreach($threadtableids as $tableid) {
			if(!$tableid) {
				continue;
			}
			$tablename = "forum_thread_$tableid";
			$table_info = C::t('forum_thread')->gettablestatus($tableid);
			showtablerow('', array(), array($table_info['Name'], "<input type=\"text\" class=\"txt\" name=\"displayname[$tableid]\" value=\"{$threadtable_info[$tableid]['displayname']}\" />", $table_info['Rows'], $table_info['Data_length'], $table_info['Index_length'], $table_info['Create_time'], "<input type=\"text\" class=\"txt\" name=\"memo[$tableid]\" value=\"{$threadtable_info[$tableid]['memo']}\" />", "<a href=\"?action=threadsplit&operation=droptable&tableid=$tableid\">{$lang['delete']}</a>"));
		}
		showtablefooter();
		showsubmit('threadsplit_update_submit', 'threadsplit_manage_update', '', '<a href="?action=threadsplit&operation=addnewtable" style="border-style: solid; border-width: 1px;" class="btn">'.$lang['threadsplit_manage_table_add'].'</a>&nbsp;<a href="?action=threadsplit&operation=forumarchive" style="border-style: solid; border-width: 1px;" class="btn">'.$lang['threadsplit_manage_forum_update'].'</a>');
		showformfooter();
	} else {
		$threadtable_info = array();
		$_GET['memo'] = !empty($_GET['memo']) ? $_GET['memo'] : array();
		$_GET['displayname'] = !empty($_GET['displayname']) ? $_GET['displayname'] : array();
		foreach(array_keys($_GET['memo']) as $tableid) {
			$threadtable_info[$tableid]['memo'] = $_GET['memo'][$tableid];
		}
		foreach(array_keys($_GET['displayname']) as $tableid) {
			$threadtable_info[$tableid]['displayname'] = $_GET['displayname'][$tableid];
		}
		C::t('common_setting')->update('threadtable_info', $threadtable_info);
		savecache('threadtable_info', $threadtable_info);
		update_threadtableids();
		updatecache('setting');
		cpmsg('threadsplit_manage_update_succeed', 'action=threadsplit&operation=manage', 'succeed');
	}
} elseif($operation == 'addnewtable') {
	if(empty($threadtableids)) {
		$maxtableid = 0;
	} else {
		$maxtableid = max($threadtableids);
	}

	C::t('forum_thread')->create_table($maxtableid + 1);

	update_threadtableids();
	updatecache('setting');
	cpmsg('threadsplit_table_create_succeed', 'action=threadsplit&operation=manage', 'succeed');
} elseif($operation == 'droptable') {
	$tableid = intval($_GET['tableid']);
	$tablename = "forum_thread_$tableid";
	$table_info = C::t('forum_thread')->gettablestatus($tableid);
	if(!$tableid || !$table_info) {
		cpmsg('threadsplit_table_no_exists', 'action=threadsplit&operation=manage', 'error');
	}
	if($table_info['Rows'] > 0) {
		cpmsg('threadsplit_drop_table_no_empty_error', 'action=threadsplit&operation=manage', 'error');
	}

	C::t('forum_thread')->drop_table($tableid);
	unset($threadtable_info[$tableid]);

	update_threadtableids();

	C::t('common_setting')->update('threadtable_info', $threadtable_info);
	savecache('threadtable_info', $threadtable_info);
	updatecache('setting');
	cpmsg('threadsplit_drop_table_succeed', 'action=threadsplit&operation=manage', 'succeed');
} elseif($operation == 'move') {
	if(!$_G['setting']['bbclosed'] && !IN_DEBUG) {
		cpmsg('threadsplit_forum_must_be_closed', 'action=threadsplit&operation=manage', 'error');
	}

	require_once libfile('function/forumlist');
	$tableselect = '<select name="sourcetableid">';
	foreach($threadtableids as $tableid) {
		$selected = $_GET['sourcetableid'] == $tableid ? 'selected="selected"' : '';
		$tableselect .= "<option value=\"$tableid\" $selected>".C::t('forum_thread')->get_table_name($tableid)."</option>";
	}
	$tableselect .= '</select>';

	$forumselect = '<select name="inforum"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>'.
		'<option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
	if(isset($_GET['inforum'])) {
		$forumselect = preg_replace("/(\<option value=\"{$_GET['inforum']}\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
	}

	$typeselect = $sortselect = '';
	$query = C::t('forum_threadtype')->fetch_all_for_order();
	foreach($query as $type) {
		if($type['special']) {
			$sortselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].'</option>';
		} else {
			$typeselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].'</option>';
		}
	}

	if(isset($_GET['insort'])) {
		$sortselect = preg_replace("/(\<option value=\"{$_GET['insort']}\")(\>)/", "\\1 selected=\"selected\" \\2", $sortselect);
	}

	if(isset($_GET['intype'])) {
		$typeselect = preg_replace("/(\<option value=\"{$_GET['intype']}\")(\>)/", "\\1 selected=\"selected\" \\2", $typeselect);
	}
echo <<<EOT
<script src="static/js/calendar.js"></script>
<script type="text/JavaScript">
	function page(number) {
		$('threadform').page.value=number;
		$('threadform').threadsplit_move_search.click();
	}
</script>
EOT;
	shownav('founder', 'nav_threadsplit');
	if(!submitcheck('threadsplit_move_submit') && !$_GET['moving']) {
		showsubmenu('nav_threadsplit', array(
			array('nav_threadsplit_manage', 'threadsplit&operation=manage', 0),
			array('nav_threadsplit_move', 'threadsplit&operation=move', 1),
		));
		showtips('threadsplit_move_tips');
		showtagheader('div', 'threadsearch', !submitcheck('threadsplit_move_search'));
		showformheader('threadsplit&operation=move', '', 'threadform');
		showhiddenfields(array('page' => $_GET['page']));
		showtableheader();
		showsetting('threads_search_detail', 'detail', $_GET['detail'], 'radio');
		showsetting('threads_search_sourcetable', '', '', $tableselect);
		showsetting('threads_search_forum', '', '', $forumselect);
		showsetting('threadsplit_move_tidrange', array('tidmin', 'tidmax'), array($_GET['tidmin'], $_GET['tidmax']), 'range');
		showsetting('threads_search_noreplyday', 'noreplydays', isset($_GET['noreplydays']) ? $_GET['noreplydays'] : 365, 'text');

		showtagheader('tbody', 'advanceoption');
		showsetting('threads_search_time', array('starttime', 'endtime'), array($_GET['starttime'], $_GET['endtime']), 'daterange');
		showsetting('threads_search_type', '', '', '<select name="intype"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>'.$typeselect.'</select>');
		showsetting('threads_search_sort', '', '', '<select name="insort"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>'.$sortselect.'</select>');
		showsetting('threads_search_viewrange', array('viewsmore', 'viewsless'), array($_GET['viewsmore'], $_GET['viewsless']), 'range');
		showsetting('threads_search_replyrange', array('repliesmore', 'repliesless'), array($_GET['repliesmore'], $_GET['repliesless']), 'range');
		showsetting('threads_search_readpermmore', 'readpermmore', $_GET['readpermmore'], 'text');
		showsetting('threads_search_pricemore', 'pricemore', $_GET['pricemore'], 'text');
		showsetting('threads_search_keyword', 'keywords', $_GET['keywords'], 'text');
		showsetting('threads_search_user', 'users', $_GET['users'], 'text');

		showsetting('threads_search_type', array('specialthread', array(
			array(0, cplang('unlimited'), array('showspecial' => 'none')),
			array(1, cplang('threads_search_include_yes'), array('showspecial' => '')),
			array(2, cplang('threads_search_include_no'), array('showspecial' => '')),
		), TRUE), isset($_GET['specialthread']) ? $_GET['specialthread'] : 2, 'mradio');
		showtablerow('id="showspecial" style="display:'.($_GET['specialthread'] || !isset($_GET['specialthread']) ? '' : 'none').'"', 'class="sub" colspan="2"', mcheckbox('special', array(
			1 => cplang('thread_poll'),
			2 => cplang('thread_trade'),
			3 => cplang('thread_reward'),
			4 => cplang('thread_activity'),
			5 => cplang('thread_debate')
		), $_GET['special'] ? $_GET['special'] : array(1,2,3,4,5)));
		showsetting('threads_search_sticky', array('sticky', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), isset($_GET['sticky']) ? $_GET['sticky'] : 2, 'mradio');
		showsetting('threads_search_digest', array('digest', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), isset($_GET['digest']) ? $_GET['digest'] : 2, 'mradio');
		showsetting('threads_search_attach', array('attach', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), isset($_GET['attach']) ? $_GET['attach'] : 0, 'mradio');
		showsetting('threads_rate', array('rate', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), isset($_GET['rate']) ? $_GET['rate'] : 2, 'mradio');
		showsetting('threads_highlight', array('highlight', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), isset($_GET['highlight']) ? $_GET['highlight'] : 2, 'mradio');
		showtagfooter('tbody');

		showsubmit('threadsplit_move_search', 'submit', '', 'more_options');
		showtablefooter();
		showformfooter();
		showtagfooter('div');
		if(submitcheck('threadsplit_move_search')) {
			$searchurladd = array();
			$conditions = array(
				'sourcetableid' => $_GET['sourcetableid'],
				'inforum' => $_GET['inforum'],
				'tidmin' => $_GET['tidmin'],
				'tidmax' => $_GET['tidmax'],
				'starttime' => $_GET['starttime'],
				'endtime' => $_GET['endtime'],
				'keywords' => $_GET['keywords'],
				'users' => $_GET['users'],
				'intype' => $_GET['intype'],
				'insort' => $_GET['insort'],
				'viewsmore' => $_GET['viewsmore'],
				'viewsless' => $_GET['viewsless'],
				'repliesmore' => $_GET['repliesmore'],
				'repliesless' => $_GET['repliesless'],
				'readpermmore' => $_GET['readpermmore'],
				'pricemore' => $_GET['pricemore'],
				'noreplydays' => $_GET['noreplydays'],
				'specialthread' => $_GET['specialthread'],
				'special' => $_GET['special'],
				'sticky' => $_GET['sticky'],
				'digest' => $_GET['digest'],
				'attach' => $_GET['attach'],
				'rate' => $_GET['rate'],
				'highlight' => $_GET['highlight'],
			);
			if($_GET['detail']) {
				$pagetmp = $page;
				$threadlist = threadsplit_search_threads($conditions, ($pagetmp - 1) * $topicperpage, $topicperpage);
			} else {
				$threadtomove = threadsplit_search_threads($conditions, null, null, TRUE);
			}

			$fids = array();
			$tids = '0';
			if($_GET['detail']) {
				$threads = '';
				foreach($threadlist as $thread) {
					$fids[] = $thread['fid'];
					$thread['lastpost'] = dgmdate($thread['lastpost']);
					$threads .= showtablerow('', array('class="td25"', '', '', '', '', ''), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"tidarray[]\" value=\"$thread[tid]\" checked=\"checked\" />",
						"<a href=\"forum.php?mod=viewthread&tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a>",
						"<a href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\" target=\"_blank\">{$_G['cache'][forums][$thread[fid]][name]}</a>",
						"<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>",
						$thread['replies'],
						$thread['views']
					), TRUE);
				}
				$multi = multi($threadcount, $topicperpage, $page, ADMINSCRIPT."?action=threadsplit&amp;operation=move");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=threadsplit&amp;operation=move&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=threadsplit&amp;operation=move&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				foreach($threadlist as $thread) {
					$fids[] = $thread['fid'];
					$tids .= ','.$thread['tid'];
				}
				$multi = '';
			}
			$fids = implode(',', array_unique($fids));

			showtagheader('div', 'threadlist', TRUE);
			showformheader("threadsplit&operation=move&sourcetableid={$_GET['sourcetableid']}&threadtomove=".$threadtomove);
			showhiddenfields($_GET['detail'] ? array('fids' => $fids) : array('conditions' => serialize($conditions)));
			showtableheader(cplang('threads_result').' '.$threadcount.' <a href="###" onclick="$(\'threadlist\').style.display=\'none\';$(\'threadsearch\').style.display=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'nobottom');
			showsubtitle(array('', 'threadsplit_move_to', 'threadsplit_manage_threadcount', 'threadsplit_manage_datalength', 'threadsplit_manage_indexlength', 'threadsplit_manage_table_createtime', 'threadsplit_manage_table_memo'));

			if(!$threadcount) {

				showtablerow('', 'colspan="3"', cplang('threads_thread_nonexistence'));

			} else {
				$threadtable_orig = C::t('forum_thread')->gettablestatus();
				$tableid = 0;

				showtablerow('', array('class="td25"'), array("<input class=\"radio\" ".($_GET['sourcetableid'] == '0' ? 'disabled="disabled"' : '')." type=\"radio\" name=\"tableid\" value=\"0\" />", $threadtable_orig['Name'], $threadtable_orig['Rows'], $threadtable_orig['Data_length'], $threadtable_orig['Index_length'], $threadtable_orig['Create_time'], $threadtable_info[0]['memo']));
				foreach($threadtableids as $tableid) {
					if($tableid) {
						$tablename = "forum_thread_$tableid";
						$tablestatus = C::t('forum_thread')->gettablestatus($tableid);

						showtablerow('', array(), array("<input class=\"radio\" ".($_GET['sourcetableid'] == $tableid ? 'disabled="disabled"' : '')." type=\"radio\" name=\"tableid\" value=\"$tableid\" />", $tablestatus['Name'].($threadtable_info[$tableid]['displayname'] ? " (".dhtmlspecialchars($threadtable_info[$tableid]['displayname']).")" : ''), $tablestatus['Rows'], $tablestatus['Data_length'], $tablestatus['Index_length'], $tablestatus['Create_time'], $threadtable_info[$tableid]['memo']));
					}
				}

				if($_GET['detail']) {

					showtablefooter();
					showtableheader('threads_list', 'notop');
					showsubtitle(array('', 'subject', 'forum', 'author', 'threads_replies', 'threads_views'));
					echo $threads;

				}

			}
			showtablefooter();
			if($threadcount) {
				showtableheader('');
				showsetting('threadsplit_move_threads_per_time', 'threads_per_time', 200, 'text');
				showtablefooter();
				showsubmit('threadsplit_move_submit', 'submit', $_GET['detail'] ? '<input name="chkall" id="chkall" type="checkbox" class="checkbox" checked="checked" onclick="checkAll(\'prefix\', this.form, \'tidarray\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>' : '', '', $multi);

			}
			showformfooter();
			showtagfooter('div');

		}
	} else {
		if(!isset($_GET['tableid'])) {
			cpmsg('threadsplit_no_target_table', '', 'error');
		}
		$continue = false;

		$tidsarray = !empty($_GET['tidarray']) ? $_GET['tidarray'] : array();
		if(empty($tidsarray) && !empty($_GET['conditions'])) {
			$conditions = dunserialize($_GET['conditions']);
			$max_threads_move = intval($_GET['threads_per_time']) ? intval($_GET['threads_per_time']) : MAX_THREADS_MOVE;
			$threadlist = threadsplit_search_threads($conditions, 0, $max_threads_move);
			foreach($threadlist as $thread) {
				$tidsarray[] = $thread['tid'];
				$continue = TRUE;
			}
		}
		if(empty($tidsarray[0])) {
			array_shift($tidsarray);
		}

		if(!empty($tidsarray)) {
			$continue = true;
		}
		if($_GET['tableid'] == $_GET['sourcetableid']) {
			cpmsg('threadsplit_move_source_target_no_same', 'action=threadsplit&operation=move', 'error');
		}
		if($continue) {
			$threadtable_target = $_GET['tableid'] ? $_GET['tableid'] : 0;
			$threadtable_source = $_GET['sourcetableid'] ? $_GET['sourcetableid'] : 0;
			C::t('forum_thread')->move_thread_by_tid($tidsarray, $threadtable_source, $threadtable_target);

			C::t('forum_forumrecommend')->delete($tidsarray);

			$completed = intval($_GET['completed']) + count($tidsarray);

			$nextstep = $step + 1;
			cpmsg('threadsplit_moving', "action=threadsplit&operation=move&{$_GET['urladd']}&tableid={$_GET['tableid']}&completed=$completed&sourcetableid={$_GET['sourcetableid']}&threadtomove={$_GET['threadtomove']}&step=$nextstep&moving=1", 'loadingform', array('count' => $completed, 'total' => intval($_GET['threadtomove']), 'threads_per_time' => $_GET['threads_per_time'], 'conditions' => dhtmlspecialchars($_GET['conditions'])));
		}

		cpmsg('threadsplit_move_succeed', "action=threadsplit&operation=forumarchive", 'succeed');
	}
} elseif($operation == 'forumarchive') {
	$step = intval($_GET['step']);
	$continue = false;
	if(isset($threadtableids[$step])) {
		$continue = true;
	}
	if($continue) {
		$threadtableid = $threadtableids[$step];
		C::t('forum_forum_threadtable')->update_by_threadtableid($threadtableid, array('threads' => '0', 'posts' => '0'));
		$threadtable = $threadtableid ? $threadtableid : 0;
		foreach(C::t('forum_thread')->count_group_by_fid($threadtable) as $row) {
			C::t('forum_forum_threadtable')->insert(array(
				'fid' => $row['fid'],
				'threadtableid' => $threadtableid,
				'threads' => $row['threads'],
				'posts' => $row['posts'],
			), false, true);
			if($row['threads'] > 0) {
				C::t('forum_forum')->update($row['fid'], array('archive' => '1'));
			}
		}
		$nextstep = $step + 1;
		cpmsg('threadsplit_manage_forum_processing', "action=threadsplit&operation=forumarchive&step=$nextstep", 'loading', array('table' => DB::table($threadtable)));
	} else {
		C::t('forum_forum_threadtable')->delete_none_threads();
		$fids = array('0');
		foreach(C::t('forum_forum_threadtable')->range() as $row) {
			$fids[] = $row['fid'];
		}
		C::t('forum_forum')->update_archive($fids);
		cpmsg('threadsplit_manage_forum_complete', 'action=threadsplit&operation=manage', 'succeed');
	}
}



function threadsplit_search_threads($conditions, $offset = null, $length = null, $onlycount = FALSE) {
	global $_G, $searchurladd, $page, $threadcount;
	if($conditions) {
		$conditions = daddslashes($conditions);
	}
	$sql = '';










	$threadlist = array();

	$sql = C::t('forum_thread')->search_condition($conditions, 't');
	$searchurladd = C::t('forum_thread')->get_url_param();
	if($sql || $conditions['sourcetableid']) {
		$conditions['isgroup'] = 0;
		$tableid = $conditions['sourcetableid'] ? $conditions['sourcetableid'] : 0;
		$threadcount = C::t('forum_thread')->count_search($conditions, $tableid, 't');
		if(isset($offset) && isset($length)) {
			$sql .= " LIMIT $offset, $length";
		}
		if($onlycount) {
			return $threadcount;
		}
		if($threadcount) {

			foreach(C::t('forum_thread')->fetch_all_search($conditions, $tableid, $offset, $length) as $thread) {
				$thread['lastpost'] = dgmdate($thread['lastpost']);
				$threadlist[] = $thread;
			}
		}
	}
	return $threadlist;
}

function update_threadtableids() {
	$threadtableids = C::t('forum_thread')->fetch_thread_table_ids();
	C::t('common_setting')->update('threadtableids', $threadtableids);
	savecache('threadtableids', $threadtableids);
}
?>