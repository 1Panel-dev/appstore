<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_admingroup.php 31651 2012-09-18 10:23:26Z zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation) {

	if(submitcheck('groupsubmit') && $ids = dimplode($_GET['delete'])) {
		$gids = array();
		$query = C::t('common_usergroup')->fetch_all_by_groupid($_GET['delete']);
		foreach($query as $g) {
			$gids[] = $g['groupid'];
		}
		if($gids) {
			C::t('common_usergroup')->delete($gids);
			C::t('common_usergroup_field')->delete($gids);
			C::t('common_admingroup')->delete($gids);
			$newgroupid = C::t('common_usergroup')->fetch_new_groupid();
			C::t('common_member')->update_by_groupid($gids, array('groupid' => $newgroupid, 'adminid' => '0'), 'UNBUFFERED');
			deletegroupcache($gids);
		}
	}

	$grouplist = C::t('common_admingroup')->fetch_all_merge_usergroup();
	if(!submitcheck('groupsubmit')) {

		shownav('user', 'nav_admingroups');
		showsubmenu('nav_admingroups');
		showtips('admingroup_tips');

		showformheader('admingroup');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'usergroups_title', '', 'type', 'admingroup_level', 'usergroups_stars', 'usergroups_color',
		    '<input class="checkbox" type="checkbox" name="gbcmember" onclick="checkAll(\'value\', this.form, \'gbmember\', \'gbcmember\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>',
		    '<input class="checkbox" type="checkbox" name="gpcmember" onclick="checkAll(\'value\', this.form, \'gpmember\', \'gpcmember\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=admingroup&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>',
		));

		foreach($grouplist as $gid => $group) {
			$adminidselect = '<select name="newradminid['.$group['groupid'].']">';
			for($i = 1;$i <= 3;$i++) {
				$adminidselect .= '<option value="'.$i.'"'.($i == $group['radminid'] ? ' selected="selected"' : '').'>'.$lang['usergroups_system_'.$i].'</option>';
			}
			$adminidselect .= '</select>';

			showtablerow('', array('', '', 'class="td23 lightfont"', 'class="td25"', '', 'class="td25"'), array(
				$group['type'] == 'system' ? '<input type="checkbox" class="checkbox" disabled="disabled" />' : "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$group[groupid]\">",
				'<span style="color:'.$group[color].'">'.$group['grouptitle'].'</span>',
				"(groupid:$group[groupid])",
				$group['type'] == 'system' ? cplang('inbuilt') : cplang('custom'),
				$group['type'] == 'system' ? $lang['usergroups_system_'.$group['radminid']] : $adminidselect,
				"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
				"<input type=\"text\" id=\"group_color_$group[groupid]_v\" class=\"left txt\" size=\"6\" name=\"group_color[$group[groupid]]\" value=\"$group[color]\" onchange=\"updatecolorpreview('group_color_$group[groupid]')\"><input type=\"button\" id=\"group_color_$group[groupid]\"  class=\"colorwd\" onclick=\"group_color_$group[groupid]_frame.location='static/image/admincp/getcolor.htm?group_color_$group[groupid]|group_color_$group[groupid]_v';showMenu({'ctrlid':'group_color_$group[groupid]'})\" style=\"background: {$group['color']}\" /><span id=\"group_color_$group[groupid]_menu\" style=\"display: none\"><iframe name=\"group_color_$group[groupid]_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
				"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gbmember\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id={$group[admingid]}\" class=\"act\">$lang[admingroup_setting_user]</a>",
				"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gpmember\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=admingroup&operation=edit&id=$group[admingid]\" class=\"act\">$lang[admingroup_setting_admin]</a>"
			));
		}
		showtablerow('', array('class="td25"', '', '', '', 'colspan="6"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" size="12" name="grouptitlenew">',
			'',
			cplang('custom'),
			"<select name=\"radminidnew\"><option value=\"1\">$lang[usergroups_system_1]</option><option value=\"2\">$lang[usergroups_system_2]</option><option value=\"3\" selected=\"selected\">$lang[usergroups_system_3]</option>",
		));
		showsubmit('groupsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		foreach($grouplist as $gid => $group) {
			$stars = intval($_GET['group_stars'][$gid]);
			$color = dhtmlspecialchars($_GET['group_color'][$gid]);
			if($group['color'] != $color || $group['stars'] != $stars || $group['icon'] != $avatar) {
				C::t('common_usergroup')->update($gid, array('stars' => $stars, 'color' => $color));
			}
		}

		$grouptitlenew = dhtmlspecialchars(trim($_GET['grouptitlenew']));
		$radminidnew = intval($_GET['radminidnew']);

		foreach($_GET['newradminid'] as $groupid => $newradminid) {
			C::t('common_usergroup')->update($groupid, array('radminid' => $newradminid));
		}

		if($grouptitlenew && in_array($radminidnew, array(1, 2, 3))) {

			$data = array();
			$usergroup = C::t('common_usergroup')->fetch($radminidnew);
			foreach ($usergroup as $key => $val) {
				if(!in_array($key, array('groupid', 'radminid', 'type', 'system', 'grouptitle'))) {
					$val = addslashes($val);
					$data[$key] = $val;
				}
			}
			$fielddata = array();
			$usergroup = C::t('common_usergroup_field')->fetch($radminidnew);
			foreach ($usergroup as $key => $val) {
				if(!in_array($key, array('groupid'))) {
					$val = addslashes($val);
					$fielddata[$key] = $val;
				}
			}

			$adata = array();
			$admingroup = C::t('common_admingroup')->fetch($radminidnew);
			foreach ($admingroup as $key => $val) {
				if(!in_array($key, array('admingid'))) {
					$val = addslashes($val);
					$adata[$key] = $val;
				}
			}

			$data['radminid'] = $radminidnew;
			$data['type'] = 'special';
			$data['grouptitle'] = $grouptitlenew;
			$newgroupid = C::t('common_usergroup')->insert($data, true);
			if($newgroupid) {
				$adata['admingid'] = $newgroupid;
				$fielddata['groupid'] = $newgroupid;
				C::t('common_admingroup')->insert($adata);
				C::t('common_usergroup_field')->insert($fielddata);
			}
		}

		updatecache(array('usergroups', 'groupreadaccess', 'admingroups'));

		cpmsg('admingroups_edit_succeed', 'action=admingroup', 'succeed');

	}

} elseif($operation == 'edit') {

	$submitcheck = submitcheck('groupsubmit');

	$multiset = 0;
	if(empty($_GET['multi'])) {
		$gids = $_GET['id'];
	} else {
		$multiset = 1;
		if(is_array($_GET['multi'])) {
			$gids = $_GET['multi'];
		} else {
			$_GET['multi'] = explode(',', $_GET['multi']);
			array_walk($_GET['multi'], 'intval');
			$gids = $_GET['multi'];
		}
	}
	if(count($_GET['multi']) == 1) {
		$gids = $_GET['multi'][0];
		$multiset = 0;
	}

	if(!$submitcheck) {
		if(empty($gids)) {
			$grouplist = "<select name=\"id\" style=\"width: 150px\">\n";
			foreach(C::t('common_admingroup')->fetch_all_merge_usergroup() as $group) {
				$grouplist .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
			}
			$grouplist .= '</select>';
			$highlight = getgpc('highlight');
			$highlight = !empty($highlight) ? dhtmlspecialchars($highlight, ENT_QUOTES) : '';
			cpmsg('admingroups_edit_nonexistence', 'action=admingroup&operation=edit'.(!empty($highlight) ? "&highlight=$highlight" : ''), 'form', array(), $grouplist);
		}

		$mgroup = C::t('common_admingroup')->fetch_all_merge_usergroup($gids);
		if(!$mgroup) {
			cpmsg('usergroups_nonexistence', '', 'error');
		}/* else {
			while($group = DB::fetch($query)) {
				$mgroup[] = $group;
			}
		}*/

		$grouplist = $gutype = '';
		foreach(C::t('common_admingroup')->fetch_all_order() as $ggroup) {
			$checked = $_GET['id'] == $ggroup['groupid'] || in_array($ggroup['groupid'], $_GET['multi']);
			if($gutype != $ggroup['radminid']) {
				$grouplist .= '<em><span class="right"><input name="checkall_'.$ggroup['radminid'].'" onclick="checkAll(\'value\', this.form, \'g'.$ggroup['radminid'].'\', \'checkall_'.$ggroup['radminid'].'\')" type="checkbox" class="vmiddle checkbox" /></span>'.
					($ggroup['radminid'] == 1 ? $lang['usergroups_system_1'] : ($ggroup['radminid'] == 2 ? $lang['usergroups_system_2'] : $lang['usergroups_system_3'])).'</em>';
				$gutype = $ggroup['radminid'];
			}
			$grouplist .= '<input class="left checkbox ck" chkvalue="g'.$ggroup['radminid'].'" name="multi[]" value="'.$ggroup['groupid'].'" type="checkbox" '.($checked ? 'checked="checked" ' : '').'/>'.
				'<a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=admingroup&operation=edit&switch=yes&id='.$ggroup['groupid'].'&anchor=\'+currentAnchor+\'&scrolltop=\'+document.documentElement.scrollTop"'.($checked ? ' class="current"' : '').'>'.$ggroup['grouptitle'].'</a>';
		}
		$gselect = '<span id="ugselect" class="right popupmenu_dropmenu" onmouseover="showMenu({\'ctrlid\':this.id,\'pos\':\'34\'});$(\'ugselect_menu\').style.top=(parseInt($(\'ugselect_menu\').style.top)-scrollTopBody())+\'px\';$(\'ugselect_menu\').style.left=(parseInt($(\'ugselect_menu\').style.left)-document.documentElement.scrollLeft-20)+\'px\'">'.$lang['usergroups_switch'].'<em>&nbsp;&nbsp;</em></span>'.
			'<div id="ugselect_menu" class="popupmenu_popup" style="display:none">'.
			$grouplist.
			'<br style="clear:both" /><div class="cl"><input type="button" class="btn right" onclick="$(\'menuform\').submit()" value="'.cplang('admingroups_multiedit').'" /></div>'.
			'</div>';

		$_GET['anchor'] = in_array($_GET['anchor'], array('threadperm', 'postperm', 'modcpperm', 'portalperm', 'otherperm', 'spaceperm')) ? $_GET['anchor'] : 'threadperm';
		$anchorarray = array(
			array('admingroup_edit_threadperm', 'threadperm', $_GET['anchor'] == 'threadperm'),
			array('admingroup_edit_postperm', 'postperm', $_GET['anchor'] == 'postperm'),
			array('admingroup_edit_modcpperm', 'modcpperm', $_GET['anchor'] == 'modcpperm'),
			array('admingroup_edit_spaceperm', 'spaceperm', $_GET['anchor'] == 'spaceperm'),
			array('admingroup_edit_portalperm', 'portalperm', $_GET['anchor'] == 'portalperm'),
			array('admingroup_edit_otherperm', 'otherperm', $_GET['anchor'] == 'otherperm'),
		);

		showformheader('', '', 'menuform', 'get');
		showhiddenfields(array('action' => 'admingroup', 'operation' => 'edit'));
		showsubmenuanchors($lang['admingroup_edit'].(count($mgroup) == 1 ? ' - '.$mgroup[$_GET['id']]['grouptitle'].'(groupid:'.$mgroup[$_GET['id']]['groupid'].')' : ''), $anchorarray, $gselect);
		showformfooter();

		if($multiset) {
			showtips('setting_multi_tips');
		}

		showformheader("admingroup&operation=edit&id={$_GET['id']}");
		if($multiset) {
			$_G['showsetting_multi'] = 0;
			$_G['showsetting_multicount'] = count($mgroup);
			foreach($mgroup as $group) {
				$_G['showtableheader_multi'][] = '<a href="javascript:;" onclick="location.href=\''.ADMINSCRIPT.'?action=admingroup&operation=edit&id='.$group['groupid'].'&anchor=\'+$(\'cpform\').anchor.value;return false">'.$group['grouptitle'].'(groupid:'.$group['groupid'].')</a>';
			}
		}
		$mgids = array();
		foreach($mgroup as $group) {
			$_GET['id'] = $gid = $group['groupid'];
			$mgids[] = $gid;

			showmultititle();
			showtableheader();
			showtagheader('tbody', 'threadperm', $_GET['anchor'] == 'threadperm');
			showtitle('admingroup_edit_threadperm');
			showsetting('admingroup_edit_stick_thread', array('allowstickthreadnew', array(
				array(0, $lang['admingroup_edit_stick_thread_none']),
				array(1, $lang['admingroup_edit_stick_thread_1']),
				array(2, $lang['admingroup_edit_stick_thread_2']),
				array(3, $lang['admingroup_edit_stick_thread_3'])
			)), $group['allowstickthread'], 'mradio');
			showsetting('admingroup_edit_digest_thread', array('allowdigestthreadnew', array(
				array(0, $lang['admingroup_edit_digest_thread_none']),
				array(1, $lang['admingroup_edit_digest_thread_1']),
				array(2, $lang['admingroup_edit_digest_thread_2']),
				array(3, $lang['admingroup_edit_digest_thread_3'])
			)), $group['allowdigestthread'], 'mradio');
			showsetting('admingroup_edit_bump_thread', 'allowbumpthreadnew', $group['allowbumpthread'], 'radio');
			showsetting('admingroup_edit_highlight_thread', 'allowhighlightthreadnew', $group['allowhighlightthread'], 'radio');
			showsetting('admingroup_edit_live_thread', 'allowlivethreadnew', $group['allowlivethread'], 'radio');
			showsetting('admingroup_edit_recommend_thread', 'allowrecommendthreadnew', $group['allowrecommendthread'], 'radio');
			showsetting('admingroup_edit_stamp_thread', 'allowstampthreadnew', $group['allowstampthread'], 'radio');
			showsetting('admingroup_edit_stamp_list', 'allowstamplistnew', $group['allowstamplist'], 'radio');
			showsetting('admingroup_edit_close_thread', 'allowclosethreadnew', $group['allowclosethread'], 'radio');
			showsetting('admingroup_edit_move_thread', 'allowmovethreadnew', $group['allowmovethread'], 'radio');
			showsetting('admingroup_edit_edittype_thread', 'allowedittypethreadnew', $group['allowedittypethread'], 'radio');
			showsetting('admingroup_edit_copy_thread', 'allowcopythreadnew', $group['allowcopythread'], 'radio');
			showsetting('admingroup_edit_merge_thread', 'allowmergethreadnew', $group['allowmergethread'], 'radio');
			showsetting('admingroup_edit_split_thread', 'allowsplitthreadnew', $group['allowsplitthread'], 'radio');
			showsetting('admingroup_edit_repair_thread', 'allowrepairthreadnew', $group['allowrepairthread'], 'radio');
			showsetting('admingroup_edit_refund', 'allowrefundnew', $group['allowrefund'], 'radio');
			showsetting('admingroup_edit_edit_poll', 'alloweditpollnew', $group['alloweditpoll'], 'radio');
			showsetting('admingroup_edit_remove_reward', 'allowremoverewardnew', $group['allowremovereward'], 'radio');
			showsetting('admingroup_edit_edit_activity', 'alloweditactivitynew', $group['alloweditactivity'], 'radio');
			showsetting('admingroup_edit_edit_trade', 'allowedittradenew', $group['allowedittrade'], 'radio');
			showsetting('admingroup_edit_usertag', 'alloweditusertagnew', $group['alloweditusertag'], 'radio');
			showtagfooter('tbody');

			showtagheader('tbody', 'postperm', $_GET['anchor'] == 'postperm');
			showtitle('admingroup_edit_postperm');
			showsetting('admingroup_edit_edit_post', 'alloweditpostnew', $group['alloweditpost'], 'radio');
			showsetting('admingroup_edit_warn_post', 'allowwarnpostnew', $group['allowwarnpost'], 'radio');
			showsetting('admingroup_edit_ban_post', 'allowbanpostnew', $group['allowbanpost'], 'radio');
			showsetting('admingroup_edit_del_post', 'allowdelpostnew', $group['allowdelpost'], 'radio');
			showsetting('admingroup_edit_stick_post', 'allowstickreplynew', $group['allowstickreply'], 'radio');
			showsetting('admingroup_edit_manage_tag', 'allowmanagetagnew', $group['allowmanagetag'], 'radio');
			showtagfooter('tbody');

			showtagheader('tbody', 'modcpperm', $_GET['anchor'] == 'modcpperm');
			showtitle('admingroup_edit_modcpperm');
			showsetting('admingroup_edit_mod_post', 'allowmodpostnew', $group['allowmodpost'], 'radio');
			showsetting('admingroup_edit_mod_user', 'allowmodusernew', $group['allowmoduser'], 'radio');
			showsetting('admingroup_edit_ban_user', 'allowbanusernew', $group['allowbanuser'], 'radio');
			showsetting('admingroup_edit_ban_user_visit', 'allowbanvisitusernew', $group['allowbanvisituser'], 'radio');
			showsetting('admingroup_edit_ban_ip', 'allowbanipnew', $group['allowbanip'], 'radio');
			showsetting('admingroup_edit_edit_user', 'alloweditusernew', $group['allowedituser'], 'radio');
			showsetting('admingroup_edit_mass_prune', 'allowmassprunenew', $group['allowmassprune'], 'radio');
			showsetting('admingroup_edit_edit_forum', 'alloweditforumnew', $group['alloweditforum'], 'radio');
			showsetting('admingroup_edit_post_announce', 'allowpostannouncenew', $group['allowpostannounce'], 'radio');
			showsetting('admingroup_edit_clear_recycle', 'allowclearrecyclenew', $group['allowclearrecycle'], 'radio');
			showsetting('admingroup_edit_view_log', 'allowviewlognew', $group['allowviewlog'], 'radio');
			showtagfooter('tbody');

			showtagheader('tbody', 'spaceperm', $_GET['anchor'] == 'spaceperm');
			showtitle('admingroup_edit_spaceperm');
			showsetting('admingroup_edit_manage_feed', 'managefeednew', $group['managefeed'], 'radio');
			showsetting('admingroup_edit_manage_doing', 'managedoingnew', $group['managedoing'], 'radio');
			showsetting('admingroup_edit_manage_share', 'managesharenew', $group['manageshare'], 'radio');
			showsetting('admingroup_edit_manage_blog', 'manageblognew', $group['manageblog'], 'radio');
			showsetting('admingroup_edit_manage_album', 'managealbumnew', $group['managealbum'], 'radio');
			showsetting('admingroup_edit_manage_comment', 'managecommentnew', $group['managecomment'], 'radio');
			showsetting('admingroup_edit_manage_magiclog', 'managemagiclognew', $group['managemagiclog'], 'radio');
			showsetting('admingroup_edit_manage_report', 'managereportnew', $group['managereport'], 'radio');
			showsetting('admingroup_edit_manage_hotuser', 'managehotusernew', $group['managehotuser'], 'radio');
			showsetting('admingroup_edit_manage_defaultuser', 'managedefaultusernew', $group['managedefaultuser'], 'radio');
			showsetting('admingroup_edit_manage_videophoto', 'managevideophotonew', $group['managevideophoto'], 'radio');
			showsetting('admingroup_edit_manage_magic', 'managemagicnew', $group['managemagic'], 'radio');
			showsetting('admingroup_edit_manage_click', 'manageclicknew', $group['manageclick'], 'radio');
			showtagfooter('tbody');

			showtagheader('tbody', 'otherperm', $_GET['anchor'] == 'otherperm');
			showtitle('admingroup_edit_otherperm');
			showsetting('admingroup_edit_view_ip', 'allowviewipnew', $group['allowviewip'], 'radio');
			showsetting('admingroup_edit_manage_collection', 'allowmanagecollectionnew', $group['allowmanagecollection'], 'radio');
			showsetting('admingroup_edit_allow_make_html', 'allowmakehtmlnew', $group['allowmakehtml'], 'radio');
			showtagfooter('tbody');
			showtablefooter();

			showtagheader('div', 'portalperm', $_GET['anchor'] == 'portalperm');
			showtableheader();
			showtagheader('tbody', '', true);
			showtitle('admingroup_edit_portalperm');
			showsetting('admingroup_edit_manage_article', 'allowmanagearticlenew', $group['allowmanagearticle'], 'radio');
			showtagfooter('tbody');
			showtagheader('tbody', '', true);
			showsetting('admingroup_edit_add_topic', 'allowaddtopicnew', $group['allowaddtopic'], 'radio');
			showsetting('admingroup_edit_manage_topic', 'allowmanagetopicnew', $group['allowmanagetopic'], 'radio');
			showsetting('admingroup_edit_diy', 'allowdiynew', $group['allowdiy'], 'radio');
			showtagfooter('tbody');
			showtablefooter();
			showtagfooter('div');

			showsubmit('groupsubmit');

			$_G['showsetting_multi']++;
		}

		if($_G['showsetting_multicount'] > 1) {
			showhiddenfields(array('multi' => implode(',', $mgids)));
			showmulti();
		}
		showformfooter();

	} else {

		if(!$multiset) {
			$_GET['multinew'] = array(0 => array('single' => 1));
		}
		foreach($_GET['multinew'] as $k => $row) {
		if(empty($row['single'])) {
			foreach($row as $key => $value) {
				$_GET[''.$key] = $value;
			}
			$_GET['id'] = $_GET['multi'][$k];
		}
		$group = $mgroup[$k];

		$data = array(
			'alloweditpost' => $_GET['alloweditpostnew'],
			'alloweditpoll' => $_GET['alloweditpollnew'],
			'allowedittrade' => $_GET['allowedittradenew'],
			'alloweditusertag' => $_GET['alloweditusertagnew'],
			'allowremovereward' => $_GET['allowremoverewardnew'],
			'alloweditactivity' => $_GET['alloweditactivitynew'],
			'allowstickthread' => $_GET['allowstickthreadnew'],
			'allowmodpost' => $_GET['allowmodpostnew'],
			'allowbanpost' => $_GET['allowbanpostnew'],
			'allowdelpost' => $_GET['allowdelpostnew'],
			'allowmassprune' => $_GET['allowmassprunenew'],
			'allowrefund' => $_GET['allowrefundnew'],
			'allowcensorword' => $_GET['allowcensorwordnew'],
			'allowviewip' => $_GET['allowviewipnew'],
			'allowmanagecollection' => $_GET['allowmanagecollectionnew'],
			'allowbanip' => $_GET['allowbanipnew'],
			'allowedituser' => $_GET['alloweditusernew'],
			'allowbanuser' => $_GET['allowbanusernew'],
			'allowbanvisituser' => $_GET['allowbanvisitusernew'],
			'allowmoduser' => $_GET['allowmodusernew'],
			'allowpostannounce' => $_GET['allowpostannouncenew'],
			'allowclearrecycle' => $_GET['allowclearrecyclenew'],
			'allowhighlightthread' => $_GET['allowhighlightthreadnew'],
			'allowlivethread' => $_GET['allowlivethreadnew'],
			'allowdigestthread' => $_GET['allowdigestthreadnew'],
			'allowrecommendthread' => $_GET['allowrecommendthreadnew'],
			'allowbumpthread' => $_GET['allowbumpthreadnew'],
			'allowclosethread' => $_GET['allowclosethreadnew'],
			'allowmovethread' => $_GET['allowmovethreadnew'],
			'allowedittypethread' => $_GET['allowedittypethreadnew'],
			'allowstampthread' => $_GET['allowstampthreadnew'],
			'allowstamplist' => $_GET['allowstamplistnew'],
			'allowcopythread' => $_GET['allowcopythreadnew'],
			'allowmergethread' => $_GET['allowmergethreadnew'],
			'allowsplitthread' => $_GET['allowsplitthreadnew'],
			'allowrepairthread' => $_GET['allowrepairthreadnew'],
			'allowwarnpost' => $_GET['allowwarnpostnew'],
			'alloweditforum' => $_GET['alloweditforumnew'],
			'allowviewlog' => $_GET['allowviewlognew'],
			'allowmanagearticle' => $_GET['allowmanagearticlenew'],
			'allowaddtopic' => $_GET['allowaddtopicnew'],
			'allowmanagetopic' => $_GET['allowmanagetopicnew'],
			'allowdiy' => $_GET['allowdiynew'],
			'allowstickreply' => $_GET['allowstickreplynew'],
			'allowmanagetag' => $_GET['allowmanagetagnew'],
			'managefeed' => $_GET['managefeednew'],
			'managedoing' => $_GET['managedoingnew'],
			'manageshare' => $_GET['managesharenew'],
			'manageblog' => $_GET['manageblognew'],
			'managealbum' => $_GET['managealbumnew'],
			'managecomment' => $_GET['managecommentnew'],
			'managemagiclog' => $_GET['managemagiclognew'],
			'managereport' => $_GET['managereportnew'],
			'managehotuser' => $_GET['managehotusernew'],
			'managedefaultuser' => $_GET['managedefaultusernew'],
			'managevideophoto' => $_GET['managevideophotonew'],
			'managemagic' => $_GET['managemagicnew'],
			'manageclick' => $_GET['manageclicknew'],
			'allowmakehtml' => $_GET['allowmakehtmlnew'],
		);
		C::t('common_admingroup')->update($_GET[id], array_map('intval', $data));
		}

		updatecache(array('usergroups', 'groupreadaccess', 'admingroups'));

		cpmsg('admingroups_edit_succeed', 'action=admingroup&operation=edit&'.($multiset ? 'multi='.implode(',', $_GET['multi']) : 'id='.$_GET['id']).'&anchor='.$_GET['anchor'], 'succeed');
	}
}

function deletegroupcache($groupidarray) {
	if(!empty($groupidarray) && is_array($groupidarray)) {
		$cachenames = array();
		foreach ($groupidarray as $id) {
			if(($id = dintval($id))) {
				$cachenames['usergroup_'.$id] = 'usergroup_'.$id;
				$cachenames['admingroup_'.$id] = 'admingroup_'.$id;
			}
		}
		if(!empty($cachenames)) {
			C::t('common_syscache')->delete($cachenames);
		}
	}
}

?>