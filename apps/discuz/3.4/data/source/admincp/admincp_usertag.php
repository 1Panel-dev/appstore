<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_usertag.php 29214 2012-03-29 07:22:01Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
cpheader();
shownav('user', 'usertag');
$lpp = empty($_GET['lpp']) ? 20 : $_GET['lpp'];
$start = ($page - 1) * $lpp;
if($operation == '') {
	if($_GET['srchname']) {
		$addurl = '&srchname='.$_GET['srchname'];
	}
	if(submitcheck('submit') && $_GET['tagids']) {
		$class_tag = new tag();
		if($_GET['operate_type'] == 'delete') {
			$class_tag->delete_tag($_GET['tagids'], 'uid');
			cpmsg('usertag_delete_succeed', 'action=usertag'.$addurl, 'succeed');
		} elseif($_GET['operate_type'] == 'merge' && $_GET['newtag']) {
			$data = $class_tag->merge_tag($_GET['tagids'], $_GET['newtag'], 'uid');
			if($data != 'succeed') {
				cpmsg($data);
			}
			cpmsg('usertag_merge_succeed', 'action=usertag'.$addurl, 'succeed');
		}
	}
	showsubmenu('usertag', array(
		array('usertag_list', 'usertag', 1),
		array('usertag_add', 'usertag&operation=add', 0),
	));
	showtableheader();
	echo '<form method="post">'. $lang['keywords'].': <input type="text" name="srchname" value="'.$_GET['srchname'].'" /> &nbsp;<input type="submit" name="usertag_search" value="'.$lang[search].'" class="btn" /> </form>';
	showtablefooter();
	showformheader('usertag'.$addurl);
	$tagcount = C::t('common_tag')->fetch_all_by_status(3, $_GET['srchname'], 0, 0, 1);
	showtableheader(cplang('usertag_count', array('tagcount' => $tagcount)));
	if($tagcount) {
		showsubtitle(array('', 'tagname', 'usernum', 'operation'));
		$query = C::t('common_tag')->fetch_all_by_status(3, $_GET['srchname'], $start, $lpp);
		foreach($query as $row) {
			showtablerow('', array('class="td25"', 'width=100', ''), array(
					'<input type="checkbox" class="checkbox" name="tagids[]" value="'.$row['tagid'].'" />',
					$row['tagname'],
					'<span id="tag_'.$row['tagid'].'"><a href="javascript:;" onclick="ajaxget(\'misc.php?mod=tag&type=countitem&id='.$row['tagid'].'\', \'tag_'.$row['tagid'].'\');return false;">'.$lang['view'].'</a></span>',
					'<a href="'.ADMINSCRIPT.'?action=members&operation=search&submit=1&tagid='.$row['tagid'].'" target="_blank">'.cplang('view').$lang['usertag_user'].'</a>&nbsp;|&nbsp;<a href="'.ADMINSCRIPT.'?action=members&operation=newsletter&tagid='.$row['tagid'].'&submit=1" target="_blank">'.$lang['usertag_send_notice'].'</a>'
				));
		}
		$multipage = multi($tagcount, $lpp, $page, ADMINSCRIPT."?action=usertag$addurl&lpp=$lpp", 0, 3);
		showtablerow('', array('class="td25" colspan="3"'), array('<input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'tagids\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>'));
		showtablerow('', array('class="td25"', 'colspan="2"'), array(
		cplang('operation'),'<input class="radio" type="radio" name="operate_type" value="delete"> '.cplang('delete').' &nbsp; &nbsp;<input class="radio" type="radio" name="operate_type" value="merge"> '.cplang('mergeto').' <input name="newtag" value="" class="txt" type="text">'
				));
		showsubmit('submit', 'submit', '', '', $multipage);
	}

	showtablefooter();
	showformfooter();
} elseif($operation == 'add') {
	if(submitcheck('submit')) {
		$uids = $tagarray = array();
		if($_GET['usernames']) {
			$_GET['usernames'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $_GET['usernames']));
			$_GET['usernames'] = explode("\r\n", $_GET['usernames']);
			$uids = C::t('common_member')->fetch_all_uid_by_username($_GET['usernames']);
		}
		if(empty($_GET['usernames']) || $uids) {
			$class_tag = new tag();
			$tagarray = $class_tag->add_tag($_GET['tags'], 0, 'uid', 1);
		}

		if($uids && $tagarray) {
			foreach($uids as $uid) {
				if(empty($uid)) continue;
				foreach($tagarray as $tagid => $tagname) {
					C::t('common_tagitem')->insert(array('tagid' => $tagid, 'itemid' => $uid, 'idtype' => 'uid'), 0, 1);
				}
			}
			cpmsg('usertag_add_succeed', 'action=usertag&operation=add', 'succeed');
		} else {
			if($tagarray && empty($_GET['usernames'])) {
				cpmsg('usertag_add_tag_succeed', 'action=usertag&operation=add', 'succeed');
			} else {
				cpmsg('usertag_add_error', 'action=usertag&operation=add', 'error');
			}
		}

	}
	showsubmenu('usertag', array(
		array('usertag_list', 'usertag', 0),
		array('usertag_add', 'usertag&operation=add', 1),
	));
	showtips('usertag_add_tips');
	showformheader('usertag&operation=add');
	showtableheader();
	showsetting('usertag_add_tags', 'tags', '', 'text');
	showsetting('usertag_add_usernames', 'usernames', '', 'textarea');
	showtablefooter();
	showsubmit('submit', 'submit');
	showformfooter();
}
?>