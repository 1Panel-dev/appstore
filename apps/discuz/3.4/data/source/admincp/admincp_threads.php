<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_threads.php 33828 2013-08-20 02:29:32Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/post');

cpheader();

$optype = $_GET['optype'];
$fromumanage = $_GET['fromumanage'] ? 1 : 0;

if((!$operation && !$optype) || ($operation == 'group' && empty($optype))) {
	if(!submitcheck('searchsubmit', 1) && empty($_GET['search'])) {
		$newlist = 1;
		$_GET['intype'] = '';
		$_GET['detail'] = 1;
		$_GET['inforum'] = 'all';
		$_GET['starttime'] = dgmdate(TIMESTAMP - 86400 * 30, 'Y-n-j');
	}
	$intypes = '';
	if($_GET['inforum'] && $_GET['inforum'] != 'all' && $_GET['intype']) {
		$foruminfo = C::t('forum_forumfield')->fetch($_GET['inforum']);
		$forumthreadtype = $foruminfo['threadtypes'];
		if($forumthreadtype) {
			$forumthreadtype = dunserialize($forumthreadtype);
			foreach($forumthreadtype['types'] as $typeid => $typename) {
				$intypes .= '<option value="'.$typeid.'"'.($typeid == $_GET['intype'] ? ' selected' : '').'>'.$typename.'</option>';
			}
		}
	}
	require_once libfile('function/forumlist');
	$forumselect = '<b>'.$lang['threads_search_forum'].':</b><br><br><select name="inforum" onchange="ajaxget(\'forum.php?mod=ajax&action=getthreadtypes&selectname=intype&fid=\' + this.value, \'forumthreadtype\')"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
	$typeselect = $lang['threads_move_type'].' <span id="forumthreadtype"><select name="intype"><option value=""></option>'.$intypes.'</select></span>';
	if(isset($_GET['inforum'])) {
		$forumselect = preg_replace("/(\<option value=\"$_GET[inforum]\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
	}

	$sortselect = '';
	$query = C::t('forum_threadtype')->fetch_all_for_order();
	foreach($query as $type) {
		if($type['special']) {
			$sortselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].'</option>';
		}
	}

	if(isset($_GET['insort'])) {
		$sortselect = preg_replace("/(\<option value=\"{$_GET['insort']}\")(\>)/", "\\1 selected=\"selected\" \\2", $sortselect);
	}

	echo <<<EOT
<script src="static/js/calendar.js"></script>
<script type="text/JavaScript">
	function page(number) {
		$('threadforum').page.value=number;
		$('threadforum').searchsubmit.click();
	}
</script>
EOT;
	shownav('topic', 'nav_maint_threads'.($operation ? '_'.$operation : ''));
	showsubmenu('nav_maint_threads'.($operation ? '_'.$operation : ''), array(
		array('newlist', 'threads'.($operation ? '&operation='.$operation : ''), !empty($newlist)),
		array('search', 'threads'.($operation ? '&operation='.$operation : '').'&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('threads_search', !$_GET['searchsubmit']),
		array('nav_maint_threads', $_GET['searchsubmit'])
	));
	if(empty($newlist)) {
		$search_tips = 1;
		showtips('threads_tips');
	}
	showtagheader('div', 'threadsearch', !submitcheck('searchsubmit', 1) && empty($newlist));
	showformheader('threads'.($operation ? '&operation='.$operation : ''), '', 'threadforum');
	showhiddenfields(array('page' => $page, 'pp' => $_GET['pp'] ? $_GET['pp'] : $_GET['perpage']));
	showtableheader();
	showsetting('threads_search_detail', 'detail', $_GET['detail'], 'radio');
	if($operation != 'group') {
		showtablerow('', array('class="rowform" colspan="2" style="width:auto;"'), array($forumselect.$typeselect));
	}
	showsetting('threads_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	if(!$fromumanage && !submitcheck('searchsubmit', 1)) {
		empty($_GET['starttime']) && $_GET['starttime'] = date('Y-m-d', time() - 86400 * 30);
	}
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsetting('threads_search_time', array('starttime', 'endtime'), array($_GET['starttime'], $_GET['endtime']), 'daterange');
	showsetting('threads_search_user', 'users', $_GET['users'], 'text');
	showsetting('threads_search_keyword', 'keywords', $_GET['keywords'], 'text');

	showtagheader('tbody', 'advanceoption');
	showsetting('threads_search_sort', '', '', '<select name="insort"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>'.$sortselect.'</select>');
	showsetting('threads_search_viewrange', array('viewsmore', 'viewsless'), array($_GET['viewsmore'], $_GET['viewsless']), 'range');
	showsetting('threads_search_replyrange', array('repliesmore', 'repliesless'), array($_GET['repliesmore'], $_GET['repliesless']), 'range');
	showsetting('threads_search_readpermmore', 'readpermmore', $_GET['readpermmore'], 'text');
	showsetting('threads_search_pricemore', 'pricemore', $_GET['pricemore'], 'text');
	showsetting('threads_search_noreplyday', 'noreplydays', $_GET['noreplydays'], 'text');
	showsetting('threads_search_type', array('specialthread', array(
		array(0, cplang('unlimited'), array('showspecial' => 'none')),
		array(1, cplang('threads_search_include_yes'), array('showspecial' => '')),
		array(2, cplang('threads_search_include_no'), array('showspecial' => '')),
	), TRUE), $_GET['specialthread'], 'mradio');
	showtablerow('id="showspecial" style="display:'.($_GET['specialthread'] ? '' : 'none').'"', 'class="sub" colspan="2"', mcheckbox('special', array(
		1 => cplang('thread_poll'),
		2 => cplang('thread_trade'),
		3 => cplang('thread_reward'),
		4 => cplang('thread_activity'),
		5 => cplang('thread_debate')
	), $_GET['special'] ? $_GET['special'] : array(0)));
	showsetting('threads_search_sticky', array('sticky', array(
		array(0, cplang('unlimited')),
		array(1, cplang('threads_search_include_yes')),
		array(2, cplang('threads_search_include_no')),
	), TRUE), $_GET['sticky'], 'mradio');
	showsetting('threads_search_digest', array('digest', array(
		array(0, cplang('unlimited')),
		array(1, cplang('threads_search_include_yes')),
		array(2, cplang('threads_search_include_no')),
	), TRUE), $_GET['digest'], 'mradio');
	showsetting('threads_search_attach', array('attach', array(
		array(0, cplang('unlimited')),
		array(1, cplang('threads_search_include_yes')),
		array(2, cplang('threads_search_include_no')),
	), TRUE), $_GET['attach'], 'mradio');
	showsetting('threads_rate', array('rate', array(
		array(0, cplang('unlimited')),
		array(1, cplang('threads_search_include_yes')),
		array(2, cplang('threads_search_include_no')),
	), TRUE), $_GET['rate'], 'mradio');
	showsetting('threads_highlight', array('highlight', array(
		array(0, cplang('unlimited')),
		array(1, cplang('threads_search_include_yes')),
		array(2, cplang('threads_search_include_no')),
	), TRUE), $_GET['highlight'], 'mradio');
	showsetting('threads_save', 'savethread', $_GET['savethread'], 'radio');
	if($operation != 'group') {
		showsetting('threads_hide', 'hidethread', $_GET['hidethread'], 'radio');
	}
	showtagfooter('tbody');

	showsubmit('searchsubmit', 'submit', '', 'more_options');
	showtablefooter();
	showformfooter();
	showtagfooter('div');
	if(submitcheck('searchsubmit', 1) || $newlist) {
		$operation == 'group' && $_GET['inforum'] = 'isgroup';

		$conditions['inforum'] = $_GET['inforum'] != '' && $_GET['inforum'] != 'all' && $_GET['inforum'] != 'isgroup' ? $_GET['inforum'] : '';
		$conditions['isgroup'] = $_GET['inforum'] != '' && $_GET['inforum'] == 'isgroup' ? 1 : 0;
		$conditions['intype'] = $_GET['intype'] !== '' ? $_GET['intype'] : '';
		$conditions['insort'] = $_GET['insort'] != '' && $_GET['insort'] != 'all' ? $_GET['insort'] : '';
		$conditions['viewsless'] = $_GET['viewsless'] != '' ? $_GET['viewsless'] : '';
		$conditions['viewsmore'] = $_GET['viewsmore'] != '' ? $_GET['viewsmore'] : '';
		$conditions['repliesless'] = $_GET['repliesless'] != '' ? $_GET['repliesless'] : '';
		$conditions['repliesmore'] = $_GET['repliesmore'] != '' ? $_GET['repliesmore'] : '';
		$conditions['readpermmore'] = $_GET['readpermmore'] != '' ? $_GET['readpermmore'] : '';
		$conditions['pricemore'] = $_GET['pricemore'] != '' ? $_GET['pricemore'] : '';
		$conditions['beforedays'] = $_GET['beforedays'] != '' ? $_GET['beforedays'] : '';
		$conditions['noreplydays'] = $_GET['noreplydays'] != '' ? $_GET['noreplydays'] : '';
		$conditions['starttime'] = !empty($_GET['starttime']) ? $_GET['starttime'] : '';
		$conditions['endtime'] = !empty($_GET['endtime']) ? $_GET['endtime'] : '';
		if(!empty($_GET['savethread'])) {
			$conditions['sticky'] = 4;
			$conditions['displayorder'] = -4;
		}
		if(!empty($_GET['hidethread'])) {
			$conditions['hidden'] = 1;
		}

		if(trim($_GET['keywords'])) {
			$conditions['keywords'] = $_GET['keywords'];
		}

		$conditions['users'] = trim($_GET['users']) ? $_GET['users'] : '';
		if($_GET['sticky'] == 1) {
			$conditions['sticky'] = 1;
		} elseif($_GET['sticky'] == 2) {
			$conditions['sticky'] = 2;
		}
		if($_GET['digest'] == 1) {
			$conditions['digest'] = 1;
		} elseif($_GET['digest'] == 2) {
			$conditions['digest'] = 2;
		}
		if($_GET['attach'] == 1) {
			$conditions['attach'] = 1;
		} elseif($_GET['attach'] == 2) {
			$conditions['attach'] = 2;
		}
		if($_GET['rate'] == 1) {
			$conditions['rate'] = 1;
		} elseif($_GET['rate'] == 2) {
			$conditions['rate'] = 2;
		}
		if($_GET['highlight'] == 1) {
			$conditions['highlight'] = 1;
		} elseif($_GET['highlight'] == 2) {
			$conditions['highlight'] = 2;
		}
		if(!empty($_GET['special'])) {
			$specials = $comma = '';
			foreach($_GET['special'] as $val) {
				$specials .= $comma.'\''.$val.'\'';
				$comma = ',';
			}
			$conditions['special'] = $_GET['special'];
			if($_GET['specialthread'] == 1) {
				$conditions['specialthread'] = 1;
			} elseif($_GET['specialthread'] == 2) {
				$conditions['specialthread'] = 2;
			}
		}

		$fids = array();
		$tids = $threadcount = '0';
		if($conditions) {
			if(empty($_GET['savethread']) && !isset($conditions['displayorder']) && !isset($conditions['sticky'])) {
				$conditions['sticky'] = 5;
			}
			if($_GET['detail']) {
				$_GET['perpage'] = intval($_GET['perpage']) < 1 ? 20 : intval($_GET['perpage']);
				$perpage = $_GET['pp'] ? $_GET['pp'] : $_GET['perpage'];
				$start = ($page - 1) * $perpage;
				$threads = '';
				$groupsname = $groupsfid = $threadlist = array();
				$threadcount = C::t('forum_thread')->count_search($conditions);
				if($threadcount) {
					foreach(C::t('forum_thread')->fetch_all_search($conditions, 0, $start, $perpage, 'tid', 'DESC', ' FORCE INDEX(PRIMARY) ') as $thread) {
						$fids[] = $thread['fid'];
						if($thread['isgroup']) {
							$groupsfid[$thread[fid]] = $thread['fid'];
						}
						$thread['lastpost'] = dgmdate($thread['lastpost']);
						$threadlist[] = $thread;
					}
					if($groupsfid) {
						$query = C::t('forum_forum')->fetch_all_by_fid($groupsfid);
						foreach($query as $row) {
							$groupsname[$row[fid]] = $row['name'];
						}
					}
					if($threadlist) {
						foreach($threadlist as $thread) {
							$threads .= showtablerow('', array('class="td25"', '', '', '', 'class="td25"', 'class="td25"'), array(
								"<input class=\"checkbox\" type=\"checkbox\" name=\"tidarray[]\" value=\"$thread[tid]\" />",
								"<a href=\"forum.php?mod=viewthread&tid=$thread[tid]".($thread['displayorder'] != -4 ? '' : '&modthreadkey='.modauthkey($thread['tid']))."\" target=\"_blank\">$thread[subject]</a>".($thread['readperm'] ? " - [$lang[threads_readperm] $thread[readperm]]" : '').($thread['price'] ? " - [$lang[threads_price] $thread[price]]" : ''),
							"<a href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\" target=\"_blank\">".(empty($thread['isgroup']) ? $_G['cache']['forums'][$thread[fid]]['name'] : $groupsname[$thread[fid]])."</a>",
								"<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>",
								$thread['replies'],
								$thread['views'],
								$thread['lastpost']
							), TRUE);
						}
					}

					$multi = multi($threadcount, $perpage, $page, ADMINSCRIPT."?action=threads");
					$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=threads&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
					$multi = str_replace("window.location='".ADMINSCRIPT."?action=threads&amp;page='+this.value", "page(this.value)", $multi);
				}
			} else {
				$threadcount = C::t('forum_thread')->count_search($conditions);
				if($threadcount) {
					foreach(C::t('forum_thread')->fetch_all_search($conditions, 0, $start, $perpage, 'tid', 'DESC', ' FORCE INDEX(PRIMARY) ') as $thread) {
						$fids[] = $thread['fid'];
						$tids .= ','.$thread['tid'];
					}
				}

				$multi = '';
			}
		}
		$fids = implode(',', array_unique($fids));

		showtagheader('div', 'threadlist', TRUE);
		showformheader('threads&frame=no'.($operation ? '&operation='.$operation : ''), 'target="threadframe"');
		showhiddenfields($_GET['detail'] ? array('fids' => $fids) : array('fids' => $fids, 'tids' => $tids));
		if(!$search_tips) {
			showtableheader(cplang('threads_new_result').' '.$threadcount, 'nobottom');
		} else {
			showtableheader(cplang('threads_result').' '.$threadcount.' <a href="###" onclick="$(\'threadlist\').style.display=\'none\';$(\'threadsearch\').style.display=\'\';$(\'threadforum\').pp.value=\'\';$(\'threadforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'nobottom');
		}
		if(!$threadcount) {

			showtablerow('', 'colspan="3"', cplang('threads_thread_nonexistence'));

		} else {

			if($_GET['detail']) {
				showsubtitle(array('', 'subject', 'forum', 'author', 'threads_replies', 'threads_views', 'threads_lastpost'));
				echo $threads;
				showtablerow('', array('class="td25" colspan="7"'), array('<input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'tidarray\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>'));
				showtablefooter();
				showtableheader('operation', 'notop');

			}
			showsubtitle(array('', 'operation', 'option'));
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" id="optype_moveforum" name="optype" value="moveforum" onclick="this.form.modsubmit.disabled=false;">',
				$lang['threads_move_forum'],
				'<select name="toforum"  id="toforum" onchange="$(\'optype_moveforum\').checked=\'checked\';ajaxget(\'forum.php?mod=ajax&action=getthreadtypes&fid=\' + this.value, \'threadtypes\')">'.forumselect(FALSE, 0, 0, TRUE).'</select>'.
				$lang['threads_move_type'].' <span id="threadtypes"><select name="threadtypeid" onchange="$(\'optype_moveforum\').checked=\'checked\'"><option value="0"></option></select></span>'
			));
			if($operation != 'group') {
				showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
					'<input class="radio" type="radio" id="optype_movesort" name="optype" value="movesort" onclick="this.form.modsubmit.disabled=false;">',
					$lang['threads_move_sort'],
					'<select name="tosort" onchange="$(\'optype_movesort\').checked=\'checked\';"><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>'.$sortselect.'</select>'
				));
				showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
					'<input class="radio" type="radio" id="optype_stick" name="optype" value="stick" onclick="this.form.modsubmit.disabled=false;">',
					$lang['threads_stick'],
					'<input class="radio" type="radio" name="stick_level" value="0" onclick="$(\'optype_stick\').checked=\'checked\'"> '.$lang['threads_remove'].' &nbsp; &nbsp;<input class="radio" type="radio" name="stick_level" value="1" onclick="$(\'optype_stick\').checked=\'checked\'"> '.$lang['threads_stick_one'].' &nbsp; &nbsp;<input class="radio" type="radio" name="stick_level" value="2" onclick="$(\'optype_stick\').checked=\'checked\'"> '.$lang['threads_stick_two'].' &nbsp; &nbsp;<input class="radio" type="radio" name="stick_level" value="3" onclick="$(\'optype_stick\').checked=\'checked\'"> '.$lang['threads_stick_three']
				));
				showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
					'<input class="radio" type="radio" id="optype_addstatus" name="optype" value="addstatus" onclick="this.form.modsubmit.disabled=false;">',
					$lang['threads_open_close'],
					'<input class="radio" type="radio" name="status" value="0" onclick="$(\'optype_addstatus\').checked=\'checked\'"> '.$lang['open'].' &nbsp; &nbsp;<input class="radio" type="radio" name="status" value="1"  onclick="$(\'optype_addstatus\').checked=\'checked\'"> '.$lang['closed']
				));
			}
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" id="optype_delete" name="optype" value="delete" onclick="this.form.modsubmit.disabled=false;">',
				$lang['threads_delete'],
				'<input class="checkbox" type="checkbox" name="donotupdatemember" id="donotupdatemember" value="1" /><label for="donotupdatemember"> '.$lang['threads_delete_no_update_member'].'</label>'
			));
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" name="optype" id="optype_adddigest" value="adddigest" onclick="this.form.modsubmit.disabled=false;">',
				$lang['threads_add_digest'],
				'<input class="radio" type="radio" name="digest_level" value="0" onclick="$(\'optype_adddigest\').checked=\'checked\'"> '.$lang['threads_remove'].' &nbsp; &nbsp;<input class="radio" type="radio" name="digest_level" value="1" onclick="$(\'optype_adddigest\').checked=\'checked\'"> '.$lang['threads_digest_one'].' &nbsp; &nbsp;<input class="radio" type="radio" name="digest_level" value="2" onclick="$(\'optype_adddigest\').checked=\'checked\'"> '.$lang['threads_digest_two'].' &nbsp; &nbsp;<input class="radio" type="radio" name="digest_level" value="3" onclick="$(\'optype_adddigest\').checked=\'checked\'"> '.$lang['threads_digest_three']
			));
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" name="optype" value="deleteattach" onclick="this.form.modsubmit.disabled=false;">',
				$lang['threads_delete_attach'],
				''
			));

		}

		showsubmit('modsubmit', 'submit', '', '', $multi);
		showtablefooter();
		showformfooter();
		echo '<script type="text/JavaScript">ajaxget(\'forum.php?mod=ajax&action=getthreadtypes&fid=\' + $("toforum").value, \'threadtypes\')</script>';
		echo '<iframe name="threadframe" style="display:none"></iframe>';
		showtagfooter('div');

	}

} else {

	$tidsarray = isset($_GET['tids']) ? explode(',', $_GET['tids']) : $_GET['tidarray'];
	$tidsadd = 'tid IN ('.dimplode($tidsarray).')';
	if($optype == 'moveforum') {
		if(!C::t('forum_forum')->check_forum_exists($_GET['toforum'])) {
			cpmsg('threads_move_invalid', '', 'error');
		}
		C::t('forum_thread')->update($tidsarray, array('fid'=>$_GET['toforum'], 'typeid'=>$_GET['threadtypeid'], 'isgroup'=>0));
		loadcache('posttableids');
		$posttableids = $_G['cache']['posttableids'] ? $_G['cache']['posttableids'] : array('0');
		foreach($posttableids as $id) {
			C::t('forum_post')->update_by_tid($id, $tidsarray, array('fid' => $_GET['toforum']));
		}

		foreach(explode(',', $_GET['fids'].','.$_GET['toforum']) as $fid) {
			updateforumcount(intval($fid));
		}

		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'movesort') {

		if($_GET['tosort'] != 0) {
			if(!C::t('forum_threadtype')->fetch($_GET['tosort'])) {
				cpmsg('threads_move_invalid', '', 'error');
			}
		}

		C::t('forum_thread')->update($tidsarray, array('sortid'=>$_GET['tosort']));
		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'delete') {

		require_once libfile('function/delete');
		deletethread($tidsarray, !$_GET['donotupdatemember'], !$_GET['donotupdatemember']);

		if($_G['setting']['globalstick']) {
			updatecache('globalstick');
		}

		foreach(explode(',', $_GET['fids']) as $fid) {
			updateforumcount(intval($fid));
		}

		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'deleteattach') {

		require_once libfile('function/delete');
		deleteattach($tidsarray, 'tid');
		C::t('forum_thread')->update($tidsarray, array('attachment'=>0));
		loadcache('posttableids');
		$posttableids = $_G['cache']['posttableids'] ? $_G['cache']['posttableids'] : array('0');
		foreach($posttableids as $id) {
			C::t('forum_post')->update_by_tid($id, $tidsarray, array('attachment' => '0'));
		}

		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'stick') {

		C::t('forum_thread')->update($tidsarray, array('displayorder'=>$_GET['stick_level']));
		$my_act = $_GET['stick_level'] ? 'sticky' : 'update';

		if($_G['setting']['globalstick']) {
			updatecache('globalstick');
		}

		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'adddigest') {

		foreach(C::t('forum_thread')->fetch_all_by_tid($tidsarray) as $thread) {
			if($_GET['digest_level'] == $thread['digest']) continue;
			$extsql = array();
			if($_GET['digest_level'] > 0 && $thread['digest'] == 0) {
				$extsql = array('digestposts' => 1);
			}
			if($_GET['digest_level'] == 0 && $thread['digest'] > 0) {
				$extsql = array('digestposts' => -1);
			}
			updatecreditbyaction('digest', $thread['authorid'], $extsql, '', $_GET['digest_level'] - $thread['digest'], 1, $thread['fid']);
		}
		C::t('forum_thread')->update($tidsarray, array('digest'=>$_GET['digest_level']));
		$my_act = $_GET['digest_level'] ? 'digest' : 'update';

		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'addstatus') {

		C::t('forum_thread')->update($tidsarray, array('closed'=>$_GET['status']));
		$my_opt = $_GET['status'] ? 'close' : 'open';

		$cpmsg = cplang('threads_succeed');

	} elseif($operation == 'forumstick') {
		shownav('topic', 'threads_forumstick');
		loadcache(array('forums', 'grouptype'));
		$forumstickthreads = C::t('common_setting')->fetch('forumstickthreads', true);
		if(!submitcheck('forumsticksubmit')) {
			showsubmenu('threads_forumstick', array(
				array('admin', 'threads&operation=forumstick', !$do),
				array('add', 'threads&operation=forumstick&do=add', $do == 'add'),
			));
			showtips('threads_forumstick_tips');
			if(!$do) {
				showformheader('threads&operation=forumstick');
				showtableheader('admin', 'fixpadding');
				showsubtitle(array('', 'subject', 'threads_forumstick_forum', 'threads_forumstick_group', 'edit'));
				if(is_array($forumstickthreads)) {
					foreach($forumstickthreads as $k => $v) {
						$forumnames = array();
						foreach($v['forums'] as $forum_id){
							if($_G['cache']['forums'][$forum_id]['name']) {
								$forumnames[] = $name = $_G['cache']['forums'][$forum_id]['name'];
							} elseif($_G['cache']['grouptype']['first'][$forum_id]['name']) {
								$grouptypes[] = $name = $_G['cache']['grouptype']['first'][$forum_id]['name'];
							} elseif($_G['cache']['grouptype']['second'][$forum_id]['name']) {
								$grouptypes[] = $name = $_G['cache']['grouptype']['second'][$forum_id]['name'];
							}
						}
						showtablerow('', array('class="td25"'), array(
							"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$k\">",
							"<a href=\"forum.php?mod=viewthread&tid=$v[tid]\" target=\"_blank\">$v[subject]</a>",
							implode(', ', $forumnames),
							implode(', ', $grouptypes),
							"<a href=\"".ADMINSCRIPT."?action=threads&operation=forumstick&do=edit&id=$k\">$lang[threads_forumstick_targets_change]</a>",
						));
					}
				}
				showsubmit('forumsticksubmit', 'submit', 'del');
				showtablefooter();
				showformfooter();
			} elseif($do == 'add') {
				require_once libfile('function/forumlist');
				showformheader('threads&operation=forumstick&do=add');
				showtableheader('add', 'fixpadding');
				showsetting('threads_forumstick_threadurl', 'forumstick_url', '', 'text');
				$targetsselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
				require_once libfile('function/group');
				$groupselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.get_groupselect(0, 0, 0).'</select>';
				showsetting('threads_forumstick_targets', '', '', $targetsselect);
				showsetting('threads_forumstick_targetgroups', '', '', $groupselect);
				echo '<input type="hidden" value="add" name="do" />';
				showsubmit('forumsticksubmit', 'submit');
				showtablefooter();
				showformfooter();
			} elseif($do == 'edit') {
				require_once libfile('function/forumlist');
				showformheader("threads&operation=forumstick&do=edit&id={$_GET['id']}");
				showtableheader('edit', 'fixpadding');
				$targetsselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
				require_once libfile('function/group');
				$groupselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.get_groupselect(0, 0, 0).'</select>';
				foreach($forumstickthreads[$_GET['id']]['forums'] as $target) {
					$targetsselect = preg_replace("/(\<option value=\"$target\")([^\>]*)(\>)/", "\\1 \\2 selected=\"selected\" \\3", $targetsselect);
					$groupselect = preg_replace("/(\<option value=\"$target\")([^\>]*)(\>)/", "\\1 \\2 selected=\"selected\" \\3", $groupselect);
				}
				showsetting('threads_forumstick_targets', '', '', $targetsselect);
				showsetting('threads_forumstick_targetgroups', '', '', $groupselect);
				echo '<input type="hidden" value="edit" name="do" />';
				echo "<input type=\"hidden\" value=\"{$_GET['id']}\" name=\"id\" />";
				showsubmit('forumsticksubmit', 'submit');
				showtablefooter();
				showformfooter();
			}
		} else {
			if(!$do) {
				$do = 'del';
			}
			if($do == 'del') {
				if(!empty($_GET['delete']) && is_array($_GET['delete'])) {
					$del_tids = array();
					foreach($_GET['delete'] as $del_tid){
						unset($forumstickthreads[$del_tid]);
						$del_tids[] = $del_tid;
					}
					if($del_tids) {
						C::t('forum_thread')->update($del_tids, array('displayorder'=>0));
					}
				} else {
					cpmsg('threads_forumstick_del_nochoice', '', 'error');
				}
			} elseif($do == 'add') {
				$_GET['forumstick_url'] = rawurldecode($_GET['forumstick_url']);
				if(preg_match('/tid=(\d+)/i', $_GET['forumstick_url'], $matches)) {
					$forumstick_tid = $matches[1];
				} elseif(in_array('forum_viewthread', $_G['setting']['rewritestatus']) && $_G['setting']['rewriterule']['forum_viewthread']) {
					preg_match_all('/(\{tid\})|(\{page\})|(\{prevpage\})/', $_G['setting']['rewriterule']['forum_viewthread'], $matches);
					$matches = $matches[0];

					$tidpos = array_search('{tid}', $matches);
					if($tidpos === false) {
						cpmsg('threads_forumstick_url_invalid', "action=threads&operation=forumstick&do=add", 'error');
					}
					$tidpos = $tidpos + 1;
					$rewriterule = str_replace(
						array('\\', '(', ')', '[', ']', '.', '*', '?', '+'),
						array('\\\\', '\(', '\)', '\[', '\]', '\.', '\*', '\?', '\+'),
						$_G['setting']['rewriterule']['forum_viewthread']
					);

					$rewriterule = str_replace(array('{tid}', '{page}', '{prevpage}'), '(\d+?)', $rewriterule);
					$rewriterule = str_replace(array('{', '}'), array('\{', '\}'), $rewriterule);
					preg_match("/$rewriterule/i", $_GET['forumstick_url'], $match_result);
					$forumstick_tid = $match_result[$tidpos];
				} elseif(in_array('all_script', $_G['setting']['rewritestatus']) && $_G['setting']['rewriterule']['all_script']) {
					preg_match_all('/(\{script\})|(\{param\})/', $_G['setting']['rewriterule']['all_script'], $matches);
					$matches = $matches[0];
					$parampos = array_search('{param}', $matches);
					if($parampos === false) {
						cpmsg('threads_forumstick_url_invalid', "action=threads&operation=forumstick&do=add", 'error');
					}
					$parampos = $parampos + 1;
					$rewriterule = str_replace(
						array('\\', '(', ')', '[', ']', '.', '*', '?', '+'),
						array('\\\\', '\(', '\)', '\[', '\]', '\.', '\*', '\?', '\+'),
						$_G['setting']['rewriterule']['all_script']
					);
					$rewriterule = str_replace(array('{script}', '{param}'), '([\w\d\-=]+?)', $rewriterule);
					$rewriterule = str_replace(array('{', '}'), array('\{', '\}'), $rewriterule);
					$rewriterule = "/\\/$rewriterule/i";
					preg_match($rewriterule, $_GET['forumstick_url'], $match_result);
					$param = $match_result[$parampos];

					if(preg_match('/viewthread-tid-(\d+)/i', $param, $tidmatch)) {
						$forumstick_tid = $tidmatch[1];
					} else {
						cpmsg('threads_forumstick_url_invalid', "action=threads&operation=forumstick&do=add", 'error');
					}
				} else {
					cpmsg('threads_forumstick_url_invalid', "action=threads&operation=forumstick&do=add", 'error');
				}
				if(empty($_GET['forumsticktargets'])) {
					cpmsg('threads_forumstick_targets_empty', "action=threads&operation=forumstick&do=add", 'error');
				}
				$stickthread = C::t('forum_thread')->fetch($forumstick_tid);
				$stickthread_tmp = array(
					'subject' => $stickthread['subject'],
					'tid' => $forumstick_tid,
					'forums' => $_GET['forumsticktargets'],
				);
				$forumstickthreads[$forumstick_tid] = $stickthread_tmp;
				C::t('forum_thread')->update($forumstick_tid, array('displayorder'=>4));
			} elseif($do == 'edit') {
				if(empty($_GET['forumsticktargets'])) {
					cpmsg('threads_forumstick_targets_empty', "action=threads&operation=forumstick&do=edit&id={$_GET['id']}", 'error');
				}
				$forumstickthreads[$_GET['id']]['forums'] = $_GET['forumsticktargets'];
				C::t('forum_thread')->update($forumstick_tid, array('displayorder'=>4));
			}

			C::t('common_setting')->update('forumstickthreads', $forumstickthreads);
			updatecache(array('forumstick', 'setting'));
			cpmsg('threads_forumstick_'.$do.'_succeed', "action=threads&operation=forumstick", 'succeed');
		}
	}

	$_GET['tids'] && deletethreadcaches($_GET['tids']);
	$cpmsg = $cpmsg ? "alert('$cpmsg');" : '';
	echo '<script type="text/JavaScript">'.$cpmsg.'if(parent.$(\'threadforum\')) parent.$(\'threadforum\').searchsubmit.click();</script>';
}

function delete_position($select) {
	if(empty($select) || !is_array($select)) {
		cpmsg('select_thread_empty', '', 'error');
	}
	$tids = dimplode($select);
	C::t('forum_postposition')->delete_by_tid($select);
	C::t('forum_thread')->update_status_by_tid($tids, '1111111111111110', '&');
}

?>