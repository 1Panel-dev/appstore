<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_postsplit.php 33060 2013-04-16 09:00:06Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
define('IN_DEBUG', false);

@set_time_limit(0);
define('MAX_POSTS_MOVE', 100000);
cpheader();
$topicperpage = 50;

if(empty($operation)) {
	$operation = 'manage';
}

$setting = C::t('common_setting')->fetch_all(array('posttable_info', 'posttableids', 'threadtableids'), true);
if($setting['posttable_info']) {
	$posttable_info = $setting['posttable_info'];
} else {
	$posttable_info = array();
	$posttable_info[0]['type'] = 'primary';
}
$posttableids = $setting['posttableids'] ? $setting['posttableids'] : array();
$threadtableids = $setting['threadtableids'];

if($operation == 'manage') {
	shownav('founder', 'nav_postsplit');
	if(!submitcheck('postsplit_manage')) {

		showsubmenu('nav_postsplit_manage');
		showtips('postsplit_manage_tips');
		showformheader('postsplit&operation=manage');
		showtableheader();

		showsubtitle(array('postsplit_manage_tablename', 'postsplit_manage_datalength', 'postsplit_manage_table_memo', ''));


		$tablename = C::t('forum_post')->getposttable(0, true);
		$tableid = 0;
		$tablestatus = helper_dbtool::gettablestatus($tablename);
		$postcount = $tablestatus['Rows'];
		$data_length = $tablestatus['Data_length'];
		$index_length = $tablestatus['Index_length'];



		$opstr = '<a href="'.ADMINSCRIPT.'?action=postsplit&operation=split&tableid=0">'.cplang('postsplit_name').'</a>';
		showtablerow('', array('', '', '', 'class="td25"'), array($tablename, $data_length, "<input type=\"text\" class=\"txt\" name=\"memo[0]\" value=\"{$posttable_info[0]['memo']}\" />", $opstr));

		foreach(C::t('forum_post')->show_table() as $table) {
			list($tempkey, $tablename) = each($table);
			$tableid = gettableid($tablename);
			if(!preg_match('/^\d+$/', $tableid)) {
				continue;
			}
			$tablestatus = helper_dbtool::gettablestatus($tablename);

			$opstr = '<a href="'.ADMINSCRIPT.'?action=postsplit&operation=split&tableid='.$tableid.'">'.cplang('postsplit_name').'</a>';
			showtablerow('', array('', '', '', 'class="td25"'), array($tablename, $tablestatus['Data_length'], "<input type=\"text\" class=\"txt\" name=\"memo[$tableid]\" value=\"{$posttable_info[$tableid]['memo']}\" />", $opstr));
		}
		showsubmit('postsplit_manage', 'postsplit_manage_update_memo_submit');
		showtablefooter();
		showformfooter();
	} else {
		$posttable_info = array();
		foreach($_GET['memo'] as $key => $value) {
			$key = intval($key);
			$posttable_info[$key]['memo'] = dhtmlspecialchars($value);
		}

		C::t('common_setting')->update('posttable_info', $posttable_info);
		savecache('posttable_info', $posttable_info);
		update_posttableids();
		updatecache('setting');

		cpmsg('postsplit_table_memo_update_succeed', 'action=postsplit&operation=manage', 'succeed');
	}
} elseif($operation == 'split') {

	if(!$_G['setting']['bbclosed']) {
		cpmsg('postsplit_forum_must_be_closed', 'action=postsplit&operation=manage', 'error');
	}

	$tableid = intval($_GET['tableid']);
	$tablename = getposttable($tableid);
	if($tableid && $tablename != 'forum_post' || !$tableid) {
		$status = helper_dbtool::gettablestatus(getposttable($tableid, true), false);
		$allowsplit = false;

		if($status && ((!$tableid && $status['Data_length'] > 400 * 1048576) || ($tableid && $status['Data_length']))) {

			if(!submitcheck('splitsubmit')) {
				showsubmenu('nav_postsplit_manage');
				showtips('postsplit_manage_tips');
				showformheader('postsplit&operation=split&tableid='.$tableid);
				showtableheader();
				showsetting('postsplit_from', '', '', getposttable($tableid, true).(!empty($posttable_info[$tableid]['memo']) ? '('.$posttable_info[$tableid]['memo'].')' : ''));
				$tablelist = '<option value="-1">'.cplang('postsplit_create').'</option>';
				foreach($posttable_info as $tid => $info) {
					if($tableid != $tid) {
						$tablestatus = helper_dbtool::gettablestatus(getposttable($tid, true));
						$tablelist .= '<option value="'.$tid.'">'.($info['memo'] ? $info['memo'] : 'forum_post'.($tid ? '_'.$tid : '')).'('.$tablestatus['Data_length'].')'.'</option>';
					}
				}
				showsetting('postsplit_to', '', '', '<select onchange="if(this.value >= 0) {$(\'tableinfo\').style.display = \'none\';} else {$(\'tableinfo\').style.display = \'\';}" name="targettable">'.$tablelist.'</select>');
				showtagheader('tbody', 'tableinfo', true, 'sub');
				showsetting('postsplit_manage_table_memo', "memo", '', 'text');
				showtagfooter('tbody');

				$datasize = round($status['Data_length'] / 1048576);
				$maxsize = round(($datasize - ($tableid ? 0 : 300)) / 100);
				$maxi = $maxsize > 10 ? 10 : ($maxsize < 1 ? 1 : $maxsize);
				for($i = 1; $i <= $maxi; $i++) {
					$movesize = $i == 10 ? 1024 : $i * 100;
					$maxsizestr .= '<option value="'.$movesize.'">'.($i == 10 ? sizecount($movesize * 1048576) : $movesize.'MB').'</option>';
				}
				showsetting('postsplit_move_size', '', '', '<select name="movesize">'.$maxsizestr.'</select>');

				showsubmit('splitsubmit', 'postsplit_manage_submit');
				showtablefooter();
				showformfooter();
			} else {

				$targettable = intval($_GET['targettable']);
				$createtable = false;
				if($targettable == -1) {
					$maxtableid = getmaxposttableid();
					DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
					$tableinfo = C::t('forum_post')->show_table_by_tableid(0);
					$createsql = $tableinfo['Create Table'];
					$targettable = $maxtableid + 1;
					$newtable = 'forum_post_'.$targettable;
					$createsql = str_replace(getposttable(), $newtable, $createsql);
					DB::query($createsql);

					$posttable_info[$targettable]['memo'] = $_GET['memo'];
					C::t('common_setting')->update('posttable_info', $posttable_info);
					savecache('posttable_info', $posttable_info);
					update_posttableids();
					$createtable = true;
				}
				$sourcetablearr = gettablefields(getposttable($tableid));
				$targettablearr = gettablefields(getposttable($targettable));
				$fields = array_diff(array_keys($sourcetablearr), array_keys($targettablearr));
				if(!empty($fields)) {
					cpmsg('postsplit_do_error', '', '', array('tableid' => getposttable($targettable, true), 'fields' => implode(',', $fields)));
				}

				$movesize = intval($_GET['movesize']);
				$movesize = $movesize >= 100 && $movesize <= 1024 ? $movesize : 100;
				$targetstatus = helper_dbtool::gettablestatus(getposttable($targettable, true), false);
				$hash = urlencode(authcode("$tableid\t$movesize\t$targettable\t$targetstatus[Data_length]", 'ENCODE'));
				if($createtable) {
					cpmsg('postsplit_table_create_succeed', 'action=postsplit&operation=movepost&fromtable='.$tableid.'&movesize='.$movesize.'&targettable='.$targettable.'&hash='.$hash, 'loadingform');
				} else {
					cpmsg('postsplit_finish', 'action=postsplit&operation=movepost&fromtable='.$tableid.'&movesize='.$movesize.'&targettable='.$targettable.'&hash='.$hash, 'loadingform');
				}

			}
		} else {
			cpmsg('postsplit_unallow', 'action=postsplit');
		}
	}

} elseif($operation == 'movepost') {

	if(!$_G['setting']['bbclosed']) {
		cpmsg('postsplit_forum_must_be_closed', 'action=postsplit&operation=manage', 'error');
	}
	list($tableid, $movesize, $targettableid, $sourcesize) = explode("\t", urldecode(authcode($_GET['hash'])));
	$hash = urlencode($_GET['hash']);

	if($tableid == $_GET['fromtable'] && $movesize == $_GET['movesize'] && $targettableid == $_GET['targettable']) {
		$fromtableid = intval($_GET['fromtable']);
		$movesize = intval($_GET['movesize']);
		$targettableid = intval($_GET['targettable']);

		$targettable = gettablefields(getposttable($targettableid));
		$fieldstr = '`'.implode('`, `', array_keys($targettable)).'`';

		loadcache('threadtableids');
		$threadtableids = array(0);
		if(!empty($_G['cache']['threadtableids'])) {
			$threadtableids = array_merge($threadtableids, $_G['cache']['threadtableids']);
		}
		$tableindex = intval(!empty($_GET['tindex']) ? $_GET['tindex'] : 0);
		if(isset($threadtableids[$tableindex])) {

			if(!$fromtableid) {
				$threadtableid = $threadtableids[$tableindex];

				$count = C::t('forum_thread')->count_by_posttableid_displayorder($threadtableid);
				if($count) {
					$tids = array();
					foreach(C::t('forum_thread')->fetch_all_by_posttableid_displayorder($threadtableid) as $tid => $thread) {
						$tids[$tid] = $tid;
					}
					movedate($tids);
				}
				if($tableindex+1 < count($threadtableids)) {
					$tableindex++;
					$status = helper_dbtool::gettablestatus(getposttable($targettableid, true), false);
					$targetsize = $sourcesize + $movesize * 1048576;
					$nowdatasize = $targetsize - $status['Data_length'];

					cpmsg('postsplit_doing', 'action=postsplit&operation=movepost&fromtable='.$tableid.'&movesize='.$movesize.'&targettable='.$targettableid.'&hash='.$hash.'&tindex='.$tableindex, 'loadingform', array('datalength' => sizecount($status['Data_length']), 'nowdatalength' => sizecount($nowdatasize)));
				}

			} else {
				$count = C::t('forum_post')->count_by_first($fromtableid, 1);
				if($count) {
					$threads = C::t('forum_post')->fetch_all_tid_by_first($fromtableid, 1, 0, 1000);
					$tids = array();
					foreach($threads as $thread) {
						$tids[$thread['tid']] = $thread['tid'];
					}
					movedate($tids);
				} else {
					cpmsg('postsplit_done', 'action=postsplit&operation=optimize&tableid='.$fromtableid, 'form');
				}

			}
		}


	} else {
		cpmsg('postsplit_abnormal', 'action=postsplit', 'succeed');
	}
} elseif($operation == 'optimize') {

	if(!$_G['setting']['bbclosed']) {
		cpmsg('postsplit_forum_must_be_closed', 'action=postsplit&operation=manage', 'error');
	}

	$fromtableid = intval($_GET['tableid']);
	$optimize = true;
	$tablename = getposttable($fromtableid);
	if($fromtableid && $tablename != 'forum_post') {
		$count = C::t('forum_post')->count_table($fromtableid);
		if(!$count) {
			C::t('forum_post')->drop_table($fromtableid);

			unset($posttable_info[$fromtableid]);
			C::t('common_setting')->update('posttable_info', $posttable_info);
			savecache('posttable_info', $posttable_info);
			update_posttableids();
			$optimize = false;
		}

	}
	if($optimize) {
		C::t('forum_post')->optimize_table($fromtableid);
	}
	cpmsg('postsplit_do_succeed', 'action=postsplit', 'succeed');

} elseif($operation == 'pidreset') {
	loadcache('posttableids');
	if(!empty($_G['cache']['posttableids'])) {
		$posttableids = $_G['cache']['posttableids'];
	} else {
		$posttableids = array('0');
	}
	$pidmax = 0;
	foreach($posttableids as $id) {
		if($id == 0) {
			$pidtmp = C::t('forum_post')->fetch_maxid(0);
		} else {
			$pidtmp = C::t('forum_post')->fetch_maxid($id);
		}
		if($pidtmp > $pidmax) {
			$pidmax = $pidtmp;
		}
	}
	$auto_increment = $pidmax + 1;
	C::t('forum_post_tableid')->alter_auto_increment($auto_increment);
	cpmsg('postsplit_resetpid_succeed', 'action=postsplit&operation=manage', 'succeed');
}

function gettableid($tablename) {
	$tableid = substr($tablename, strrpos($tablename, '_') + 1);
	return $tableid;
}

function getmaxposttableid() {
	$maxtableid = 0;
	foreach(C::t('forum_post')->show_table() as $table) {
		list($tempkey, $tablename) = each($table);
		$tableid = intval(gettableid($tablename));
		if($tableid > $maxtableid) {
			$maxtableid = $tableid;
		}
	}
	return $maxtableid;
}

function update_posttableids() {
	$tableids = get_posttableids();
	C::t('common_setting')->update('posttableids', $tableids);
	savecache('posttableids', $tableids);
}

function get_posttableids() {
	$tableids = array(0);
	foreach(C::t('forum_post')->show_table() as $table) {
		list($tempkey, $tablename) = each($table);
		$tableid = gettableid($tablename);
		if(!preg_match('/^\d+$/', $tableid)) {
			continue;
		}
		$tableid = intval($tableid);
		if(!$tableid) {
			continue;
		}
		$tableids[] = $tableid;
	}
	return $tableids;
}



function gettablefields($table) {
	static $tables = array();

	if(!isset($tables[$table])) {
		$tables[$table] = C::t('forum_post')->show_table_columns($table);
	}
	return $tables[$table];
}

function movedate($tids) {
	global $sourcesize, $tableid, $movesize, $targettableid, $hash, $tableindex, $threadtableids, $fieldstr, $fromtableid, $posttable_info;

	$fromtable = getposttable($fromtableid, true);
	C::t('forum_post')->move_table($targettableid, $fieldstr, $fromtable, $tids);
	if(DB::errno()) {
		C::t('forum_post')->delete_by_tid($targettableid, $tids);
	} else {
		foreach($threadtableids as $threadtableid) {
			$affected_rows = C::t('forum_thread')->update($tids, array('posttableid' => $targettableid), false, false, $threadtableid);
			if($affected_rows == count($tids)) {
				break;
			}
		}
		C::t('forum_post')->delete_by_tid($fromtableid, $tids);
	}
	$status = helper_dbtool::gettablestatus(getposttable($targettableid, true), false);
	$targetsize = $sourcesize + $movesize * 1048576;
	$nowdatasize = $targetsize - $status['Data_length'];

	if($status['Data_length'] >= $targetsize) {
		cpmsg('postsplit_done', 'action=postsplit&operation=optimize&tableid='.$fromtableid, 'form');
	}

	cpmsg('postsplit_doing', 'action=postsplit&operation=movepost&fromtable='.$tableid.'&movesize='.$movesize.'&targettable='.$targettableid.'&hash='.$hash.'&tindex='.$tableindex, 'loadingform', array('datalength' => sizecount($status['Data_length']), 'nowdatalength' => sizecount($nowdatasize)));
}

?>