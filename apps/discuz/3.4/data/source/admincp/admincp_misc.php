<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_misc.php 34303 2014-01-15 04:32:19Z hypowang $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if($operation == 'onlinelist') {

	if(!submitcheck('onlinesubmit')) {

		shownav('style', 'misc_onlinelist');
		showsubmenu('nav_misc_onlinelist');
		showtips('misc_onlinelist_tips');
		showformheader('misc&operation=onlinelist&');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'display_order', 'usergroup', 'usergroups_title', 'misc_onlinelist_image'));

		$listarray = array();
		foreach(C::t('forum_onlinelist')->range() as $list) {
			$list['title'] = dhtmlspecialchars($list['title']);
			$listarray[$list['groupid']] = $list;
		}

		$onlinelist = '';
		$query = array_merge(array(0 => array('groupid' => 0, 'grouptitle' => 'Member')), C::t('common_usergroup')->range());
		foreach($query as $group) {
			$id = $group['groupid'];
			showtablerow('', array('class="td25"', 'class="td23 td28"', 'class="td24"', 'class="td24"', 'class="td21 td26"'), array(
				$listarray[$id]['url'] ? " <img src=\"static/image/common/{$listarray[$id]['url']}\">" : '',
				'<input type="text" class="txt" name="displayordernew['.$id.']" value="'.$listarray[$id]['displayorder'].'" size="3" />',
				$group['groupid'] <= 8 ? cplang('usergroups_system_'.$id) : $group['grouptitle'],
				'<input type="text" class="txt" name="titlenew['.$id.']" value="'.($listarray[$id]['title'] ? $listarray[$id]['title'] : $group['grouptitle']).'" size="15" />',
				'<input type="text" class="txt" name="urlnew['.$id.']" value="'.$listarray[$id]['url'].'" size="20" />'
			));

		}

		showsubmit('onlinesubmit', 'submit', 'td');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_GET['urlnew'])) {
			C::t('forum_onlinelist')->delete_all();
			foreach($_GET['urlnew'] as $id => $url) {
				$url = trim($url);
				if($id == 0 || $url) {
					$data = array(
						'groupid' => $id,
						'displayorder' => $_GET['displayordernew'][$id],
						'title' => $_GET['titlenew'][$id],
						'url' => $url,
					);
					C::t('forum_onlinelist')->insert($data);
				}
			}
		}

		updatecache(array('onlinelist', 'groupicon'));
		cpmsg('onlinelist_succeed', 'action=misc&operation=onlinelist', 'succeed');

	}

} elseif($operation == 'link') {

	if(!submitcheck('linksubmit')) {

?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
		[1,'<input type="text" class="txt" name="newname[]" size="15">'],
		[1,'<input type="text" class="txt" name="newurl[]" size="20">'],
		[1,'<input type="text" class="txt" name="newdescription[]" size="30">', 'td26'],
		[1,'<input type="text" class="txt" name="newlogo[]" size="20">'],
		[1,'<input type="checkbox" name="newportal[{n}]" value="1" class="checkbox">'],
		[1,'<input type="checkbox" name="newforum[{n}]" value="1" class="checkbox">'],
		[1,'<input type="checkbox" name="newgroup[{n}]" value="1" class="checkbox">'],
		[1,'<input type="checkbox" name="newhome[{n}]" value="1" class="checkbox">']
	]
]
</script>
<?php

		shownav('extended', 'misc_link');
		showsubmenu('nav_misc_links');
		showtips('misc_link_tips');
		showformheader('misc&operation=link');
		showtableheader();
		showsubtitle(array('', 'display_order', 'misc_link_edit_name', 'misc_link_edit_url', 'misc_link_edit_description', 'misc_link_edit_logo', 'misc_link_group1', 'misc_link_group2', 'misc_link_group3','misc_link_group4'));
		showsubtitle(array('', '', '', '', '', '', '<input class="checkbox" type="checkbox" name="portalall" onclick="checkAll(\'prefix\', this.form, \'portal\', \'portalall\')">',
			'<input class="checkbox" type="checkbox" name="forumall" onclick="checkAll(\'prefix\', this.form, \'forum\', \'forumall\')">',
			'<input class="checkbox" type="checkbox" name="groupall" onclick="checkAll(\'prefix\', this.form, \'group\', \'groupall\')">',
			'<input class="checkbox" type="checkbox" name="homeall" onclick="checkAll(\'prefix\', this.form, \'home\', \'homeall\')">'));

		$query = C::t('common_friendlink')->fetch_all_by_displayorder();
		foreach ($query as $forumlink) {
			$type = sprintf('%04b', $forumlink['type']);
			showtablerow('', array('class="td25"', 'class="td28"', '', '', 'class="td26"'), array(
				'<input type="checkbox" class="checkbox" name="delete[]" value="'.$forumlink['id'].'" />',
				'<input type="text" class="txt" name="displayorder['.$forumlink[id].']" value="'.$forumlink['displayorder'].'" size="3" />',
				'<input type="text" class="txt" name="name['.$forumlink[id].']" value="'.$forumlink['name'].'" size="15" />',
				'<input type="text" class="txt" name="url['.$forumlink[id].']" value="'.$forumlink['url'].'" size="20" />',
				'<input type="text" class="txt" name="description['.$forumlink[id].']" value="'.$forumlink['description'].'" size="30" />',
				'<input type="text" class="txt" name="logo['.$forumlink[id].']" value="'.$forumlink['logo'].'" size="20" />',
				'<input class="checkbox" type="checkbox" value="1" name="portal['.$forumlink[id].']" '.($type[0] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="forum['.$forumlink[id].']" '.($type[1] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="group['.$forumlink[id].']" '.($type[2] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="home['.$forumlink[id].']" '.($type[3] ? "checked" : '').'>',
			));
		}

		echo '<tr><td></td><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['misc_link_add'].'</a></div></td></tr>';
		showsubmit('linksubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($_GET['delete']) {
			C::t('common_friendlink')->delete($_GET['delete']);
		}

		if(is_array($_GET['name'])) {
			foreach($_GET['name'] as $id => $val) {
				$type_str = intval($_GET['portal'][$id]).intval($_GET['forum'][$id]).intval($_GET['group'][$id]).intval($_GET['home'][$id]);
				$type_str = intval($type_str, '2');
				$query = C::t('common_friendlink')->update($id, array(
					'displayorder' => $_GET['displayorder'][$id],
					'name' => $_GET['name'][$id],
					'url' => $_GET['url'][$id],
					'description' => $_GET['description'][$id],
					'logo' => $_GET['logo'][$id],
					'type' => $type_str,
				));
			}
		}

		if(is_array($_GET['newname'])) {
			foreach($_GET['newname'] as $key => $value) {
				if($value) {
					$type_str = intval($_GET['newportal'][$key]).intval($_GET['newforum'][$key]).intval($_GET['newgroup'][$key]).intval($_GET['newhome'][$key]);
					$type_str = intval($type_str, '2');
					C::t('common_friendlink')->insert(array(
						'displayorder' => $_GET['newdisplayorder'][$key],
						'name' => $value,
						'url' => $_GET['newurl'][$key],
						'description' => $_GET['newdescription'][$key],
						'logo' => $_GET['newlogo'][$key],
						'type' => $type_str,
					));
				}
			}
		}

		updatecache('forumlinks');
		cpmsg('forumlinks_succeed', 'action=misc&operation=link', 'succeed');

	}

} elseif($operation == 'relatedlink') {

	if(!submitcheck('linksubmit')) {

?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" name="newname[]" size="15">'],
		[1,'<input type="text" name="newurl[]" size="50">'],
		[1,'<input class="checkbox" type="checkbox" value="1" name="newarticle[{n}]">'],
		[1,'<input class="checkbox" type="checkbox" value="1" name="newforum[{n}]">'],
		[1,'<input class="checkbox" type="checkbox" value="1" name="newgroup[{n}]">'],
		[1,'<input class="checkbox" type="checkbox" value="1" name="newblog[{n}]">']
	]
]
</script>
<?php

		shownav('extended', 'misc_relatedlink');
		showsubmenu('nav_misc_relatedlink');
		showtips('misc_relatedlink_tips');
		$tdstyle = array('width="50"', 'width="120"', 'width="330"', 'width="50"', 'width="80"', 'width="80"', '');
		showformheader('misc&operation=relatedlink');
		showtableheader();
		showsetting('misc_relatedlink_status', 'relatedlinkstatus', $_G['setting']['relatedlinkstatus'], 'radio');
		showtablefooter();
		showtableheader('', '', 'id="relatedlink_header"');
		showsubtitle(array('', 'misc_relatedlink_edit_name', 'misc_relatedlink_edit_url', 'misc_relatedlink_extent_article', 'misc_relatedlink_extent_forum', 'misc_relatedlink_extent_group', 'misc_relatedlink_extent_blog'), 'header tbm', $tdstyle);
		showtablefooter();
		echo '<script type="text/javascript">floatbottom(\'relatedlink_header\');</script>';
		showtableheader();
		showsubtitle(array('', 'misc_relatedlink_edit_name', 'misc_relatedlink_edit_url', '<label><input class="checkbox" type="checkbox" name="articleall" onclick="checkAll(\'prefix\', this.form, \'article\', \'articleall\')">'.cplang('misc_relatedlink_extent_article').'</label>', '<label><input class="checkbox" type="checkbox" name="forumall" onclick="checkAll(\'prefix\', this.form, \'forum\', \'forumall\')">'.cplang('misc_relatedlink_extent_forum').'</label>', '<label><input class="checkbox" type="checkbox" name="groupall" onclick="checkAll(\'prefix\', this.form, \'group\', \'groupall\')">'.cplang('misc_relatedlink_extent_group').'</label>', '<label><input class="checkbox" type="checkbox" name="blogall" onclick="checkAll(\'prefix\', this.form, \'blog\', \'blogall\')">'.cplang('misc_relatedlink_extent_blog').'</label>'), 'header', $tdstyle);

		$query = C::t('common_relatedlink')->range(0, 0, 'DESC');
		foreach($query as $link) {
			$extent = sprintf('%04b', $link['extent']);
			showtablerow('', array('class="td25"', '', '', 'class="td26"', 'class="td26"', 'class="td26"', ''), array(
				'<input type="checkbox" class="checkbox" name="delete[]" value="'.$link['id'].'" />',
				'<input type="text" class="txt" name="name['.$link[id].']" value="'.$link['name'].'" size="15" />',
				'<input type="text" name="url['.$link[id].']" value="'.$link['url'].'" size="50" />',
				'<input class="checkbox" type="checkbox" value="1" name="article['.$link[id].']" '.($extent[0] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="forum['.$link[id].']" '.($extent[1] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="group['.$link[id].']" '.($extent[2] ? "checked" : '').'>',
				'<input class="checkbox" type="checkbox" value="1" name="blog['.$link[id].']" '.($extent[3] ? "checked" : '').'>',
			));
		}

		echo '<tr><td></td><td colspan="6"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['misc_relatedlink_add'].'</a></div></td></tr>';
		showsubmit('linksubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($_GET['delete']) {
			C::t('common_relatedlink')->delete($_GET['delete']);
		}

		if(is_array($_GET['name'])) {
			foreach($_GET['name'] as $id => $val) {
				$extent_str = intval($_GET['article'][$id]).intval($_GET['forum'][$id]).intval($_GET['group'][$id]).intval($_GET['blog'][$id]);
				$extent_str = intval($extent_str, '2');
				C::t('common_relatedlink')->update($id, array(
					'name' => $_GET['name'][$id],
					'url' => $_GET['url'][$id],
					'extent' => $extent_str,
				));
			}
		}

		if(is_array($_GET['newname'])) {
			foreach($_GET['newname'] as $key => $value) {
				if($value) {
					$extent_str = intval($_GET['newarticle'][$key]).intval($_GET['newforum'][$key]).intval($_GET['newgroup'][$key]).intval($_GET['newblog'][$key]);
					$extent_str = intval($extent_str, '2');
					C::t('common_relatedlink')->insert(array(
						'name' => $value,
						'url' => $_GET['newurl'][$key],
						'extent' => $extent_str,
					));
				}
			}
		}
		C::t('common_setting')->update('relatedlinkstatus', $_GET['relatedlinkstatus']);
		updatecache(array('relatedlink','setting'));
		cpmsg('relatedlink_succeed', 'action=misc&operation=relatedlink', 'succeed');

	}

} elseif($operation == 'bbcode') {

	$edit = $_GET['edit'];
	if(!submitcheck('bbcodessubmit') && !$edit) {
		shownav('style', 'setting_editor');

		showsubmenu('setting_editor', array(
			array('setting_editor_global', 'setting&operation=editor', 0),
			array('setting_editor_code', 'misc&operation=bbcode', 1),
		));

		showtips('misc_bbcode_edit_tips');
		showformheader('misc&operation=bbcode');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'misc_bbcode_tag', 'available', 'display', 'display_order', 'misc_bbcode_icon', 'misc_bbcode_icon_file', ''));
		foreach(C::t('forum_bbcode')->fetch_all_by_available_icon() as $bbcode) {
			showtablerow('', array('class="td25"', 'class="td21"', 'class="td25"', 'class="td25"', 'class="td28 td24"', 'class="td25"', 'class="td21"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$bbcode[id]\">",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"tagnew[$bbcode[id]]\" value=\"$bbcode[tag]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$bbcode[id]]\" value=\"1\" ".($bbcode['available'] ? 'checked="checked"' : NULL).">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"displaynew[$bbcode[id]]\" value=\"1\" ".($bbcode['available'] == '2' ? 'checked="checked"' : NULL).">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$bbcode[id]]\" value=\"$bbcode[displayorder]\">",
				$bbcode['icon'] ? "<em class=\"editor\"><a class=\"customedit\"><img src=\"static/image/common/$bbcode[icon]\" border=\"0\"></a></em>" : ' ',
				"<input type=\"text\" class=\"txt\" size=\"25\" name=\"iconnew[$bbcode[id]]\" value=\"$bbcode[icon]\">",
				"<a href=\"".ADMINSCRIPT."?action=misc&operation=bbcode&edit=$bbcode[id]\" class=\"act\">$lang[detail]</a>"
			));
		}
		showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"', 'class="td25"', 'class="td28 td24"', 'class="td25"', 'class="td21"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" size="15" name="newtag">',
			'',
			'',
			'<input type="text" class="txt" size="2" name="newdisplayorder">',
			'',
			'<input type="text" class="txt" size="25" name="newicon">',
			''
		));
		showsubmit('bbcodessubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} elseif(submitcheck('bbcodessubmit')) {

		$delete = $_GET['delete'];
		if(is_array($delete)) {
			C::t('forum_bbcode')->delete($delete);
		}

		$tagnew = $_GET['tagnew'];
		$displaynew = $_GET['displaynew'];
		$displayordernew = $_GET['displayordernew'];
		$iconnew = $_GET['iconnew'];
		if(is_array($tagnew)) {
			$custom_ids = array();
			foreach(C::t('forum_bbcode')->fetch_all_by_available_icon() as $bbcode) {
				$custom_ids[] = $bbcode['id'];
			}
			$availablenew = $_GET['availablenew'];
			foreach($tagnew as $id => $val) {
				if(in_array($id, $custom_ids) && !preg_match("/^[0-9a-z]+$/i", $tagnew[$id]) && strlen($tagnew[$id]) < 20) {
					cpmsg('dzcode_edit_tag_invalid', '', 'error');
				}
				$availablenew[$id] = in_array($id, $custom_ids) ? $availablenew[$id] : 1;
				$availablenew[$id] = $availablenew[$id] && $displaynew[$id] ? 2 : $availablenew[$id];
				$data = array(
						'available' => $availablenew[$id],
						'displayorder' => $displayordernew[$id]
					);
				if(in_array($id, $custom_ids)) {
					$data['tag'] = $tagnew[$id];
					$data['icon'] = $iconnew[$id];
				}
				C::t('forum_bbcode')->update($id, $data);
			}
		}

		$newtag = $_GET['newtag'];
		if($newtag != '') {
			if(!preg_match("/^[0-9a-z]+$/i", $newtag && strlen($newtag) < 20)) {
				cpmsg('dzcode_edit_tag_invalid', '', 'error');
			}
			$data = array(
				'tag' => $newtag,
				'icon' => $_GET['newicon'],
				'available' => 0,
				'displayorder' => $_GET['newdisplayorder'],
				'params' => 1,
				'nest' => 1,
			);
			C::t('forum_bbcode')->insert($data);
		}

		updatecache(array('bbcodes', 'bbcodes_display'));
		cpmsg('dzcode_edit_succeed', 'action=misc&operation=bbcode', 'succeed');

	} elseif($edit) {

		$bbcode = C::t('forum_bbcode')->fetch($edit);
		if(!$bbcode) {
			cpmsg('bbcode_not_found', '', 'error');
		}

		if(!submitcheck('editsubmit')) {

			$bbcode['perm'] = explode("\t", $bbcode['perm']);
			$query = C::t('common_usergroup')->range_orderby_credit();
			$groupselect = array();
			foreach($query as $group) {
				$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
				$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.(@in_array($group['groupid'], $bbcode['perm']) ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
			}
			$select = '<select name="permnew[]" size="10" multiple="multiple"><option value=""'.(@in_array('', $var['value']) ? ' selected' : '').'>'.cplang('plugins_empty').'</option>'.
				'<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
				($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
				($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
				'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup></select>';

			$bbcode['prompt'] = str_replace("\t", "\n", $bbcode['prompt']);

			shownav('style', 'nav_posting_bbcode');
			showsubmenu($lang['misc_bbcode_edit'].' - '.$bbcode['tag']);
			showformheader("misc&operation=bbcode&edit=$edit");
			showtableheader();
			showsetting('misc_bbcode_edit_tag', 'tagnew', $bbcode['tag'], 'text');
			showsetting('misc_bbcode_edit_replacement', 'replacementnew', $bbcode['replacement'], 'textarea');
			showsetting('misc_bbcode_edit_example', 'examplenew', $bbcode['example'], 'text');
			showsetting('misc_bbcode_edit_explanation', 'explanationnew', $bbcode['explanation'], 'text');
			showsetting('misc_bbcode_edit_params', 'paramsnew', $bbcode['params'], 'text');
			showsetting('misc_bbcode_edit_prompt', 'promptnew', $bbcode['prompt'], 'textarea');
			showsetting('misc_bbcode_edit_nest', 'nestnew', $bbcode['nest'], 'text');
			showsetting('misc_bbcode_edit_usergroup', '', '', $select);
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$tagnew = trim($_GET['tagnew']);
			$paramsnew = $_GET['paramsnew'];
			$nestnew = $_GET['nestnew'];
			$replacementnew = $_GET['replacementnew'];
			$examplenew = $_GET['examplenew'];
			$explanationnew = $_GET['explanationnew'];
			$promptnew = $_GET['promptnew'];
			$permnew = implode("\t", $_GET['permnew']);

			if(!preg_match("/^[0-9a-z]+$/i", $tagnew)) {
				cpmsg('dzcode_edit_tag_invalid', '', 'error');
			} elseif($paramsnew < 1 || $paramsnew > 3 || $nestnew < 1 || $nestnew > 3) {
				cpmsg('dzcode_edit_range_invalid', '', 'error');
			}
			$promptnew = trim(str_replace(array("\t", "\r", "\n"), array('', '', "\t"), $promptnew));

			C::t('forum_bbcode')->update($edit, array('tag'=>$tagnew, 'replacement'=>$replacementnew, 'example'=>$examplenew, 'explanation'=>$explanationnew, 'params'=>$paramsnew, 'prompt'=>$promptnew, 'nest'=>$nestnew, 'perm'=>$permnew));
			updatecache(array('bbcodes', 'bbcodes_display'));
			cpmsg('dzcode_edit_succeed', 'action=misc&operation=bbcode', 'succeed');

		}
	}

} elseif($operation == 'censor') {

	$ppp = 30;

	$addcensors = isset($_GET['addcensors']) ? trim($_GET['addcensors']) : '';

	if($do == 'export') {

		ob_end_clean();
		dheader('Cache-control: max-age=0');
		dheader('Expires: '.gmdate('D, d M Y H:i:s', TIMESTAMP - 31536000).' GMT');
		dheader('Content-Encoding: none');
		dheader('Content-Disposition: attachment; filename=CensorWords.txt');
		dheader('Content-Type: text/plain');
		foreach(C::t('common_word_type')->fetch_all() as $result) {
			$result['used'] = 0;
			$word_type[$result['id']] = $result;
		}
		foreach(C::t('common_word')->fetch_all_order_type_find() as $censor) {
			$censor['replacement'] = str_replace('*', '', $censor['replacement']) <> '' ? $censor['replacement'] : '';
			if($word_type[$censor['type']]['used'] == 0 && $word_type[$censor['type']]) {
				if($temp_type == 1) {
					echo "[/type]\n";
				}
				echo "\n[type:".$word_type[$censor['type']]['typename']."]\n";
				$temp_type = 1;
				$word_type[$censor['type']]['used'] = 1;
			}
			echo $censor['find'].($censor['replacement'] != '' ? '='.$censor['replacement'] : '')."\n";
		}
		if($temp_type == 1) {
			echo "[/type]\n";
			unset($temp_type);
		}
		define('FOOTERDISABLED' , 1);
		exit();

	} elseif(submitcheck('addcensorsubmit') && $addcensors != '') {
		$oldwords = array();
		if($_G['adminid'] == 1 && $_GET['overwrite'] == 2) {
			C::t('common_word')->truncate();
		} else {
			foreach(C::t('common_word')->fetch_all() as $censor) {
				$oldwords[md5($censor['find'])] = $censor['admin'];
			}
		}
		$typesearch = "\[type\:(.+?)\](.+?)\[\/type\]";
		preg_match_all("/($typesearch)/is", $addcensors, $wordmatch);
		$wordmatch[3][] = preg_replace("/($typesearch)/is", '', $addcensors);
		$updatecount = $newcount = $ignorecount = 0;
		foreach($wordmatch[3] AS $key => $val) {
			$word_type = 0;
			if($wordmatch[2][$key] && !$wordtype_used[$key]) {
				$row = C::t('common_word_type')->fetch_by_typename($wordmatch[2][$key]);
				if(empty($row)) {
					$word_type = C::t('common_word_type')->insert(array('typename' => $wordmatch[2][$key]), true);
				} else {
					$word_type = $row['id'];
				}
				$wordtype_used[$key] = 1;
			}
			$word_type = $word_type ? $word_type : 0 ;

			$censorarray = explode("\n", $val);
			foreach($censorarray as $censor) {
				list($newfind, $newreplace) = array_map('trim', explode('=', $censor));
				$newreplace = $newreplace <> '' ? daddslashes(str_replace("\\\'", '\'', $newreplace), 1) : '**';
				if(strlen($newfind) < 3) {
					if($newfind != '') {
						$ignorecount ++;
					}
					continue;
				} elseif(isset($oldwords[md5($newfind)])) {
					if($_GET['overwrite'] && ($_G['adminid'] == 1 || $oldwords[md5($newfind)] == $_G['member']['username'])) {
						$updatecount ++;
						C::t('common_word')->update_by_find($newfind, array(
							'replacement' => $newreplace,
							'type' => ($word_type ? $word_type : (intval($_GET['wordtype_select']) ? intval($_GET['wordtype_select']) : 0))
						));
					} else {
						$ignorecount ++;
					}
				} else {
					$newcount ++;
					C::t('common_word')->insert(array(
						'admin' => $_G['username'],
						'find' => $newfind,
						'replacement' => $newreplace,
						'type' => ($word_type ? $word_type : (intval($_GET['wordtype_select']) ? intval($_GET['wordtype_select']) : 0))
					));
					$oldwords[md5($newfind)] = $_G['member']['username'];
				}
			}

		}


		updatecache('censor');
		cpmsg('censor_batch_add_succeed', "action=misc&operation=censor&anchor=import", 'succeed', array('newcount' => $newcount, 'updatecount' => $updatecount, 'ignorecount' => $ignorecount));

	} elseif(submitcheck('wordtypesubmit')) {
		if(is_array($_GET['delete'])) {
			$_GET['delete'] = array_map('intval', (array)$_GET['delete']);
			C::t('common_word_type')->delete($_GET['delete']);
			C::t('common_word')->update_by_type($_GET['delete'], array('type'=>0));
		}
		if(is_array($_GET['typename'])) {
			foreach($_GET['typename'] AS $key => $val) {
				if(!$_GET['delete'][$key] && !empty($val)) {
					DB::update("common_word_type", array('typename' => $val), DB::field("id", $key));
				}
			}
		}
		if($_GET['newtypename']) {
			foreach($_GET['newtypename'] AS $key => $val) {
				$val = trim($val);
				if(!empty($val)) {
					C::t('common_word_type')->insert(array('typename' => $val));
				}
			}
		}
		cpmsg('censor_wordtype_edit', 'action=misc&operation=censor&anchor=wordtype', 'succeed');
	} elseif(!submitcheck('censorsubmit')) {
		$ftype = $ffind = null;
		if(!empty($_GET['censor_search_type'])) {
			$ftype = $_GET['censor_search_type'];
		}

		$ffind = !empty($_GET['censorkeyword']) ? $_GET['censorkeyword'] : null;
		if($_POST['censorkeyword']) {
			$page = 1;
		}

		$ppp = 50;
		$startlimit = ($page - 1) * $ppp;

		foreach(C::t('common_word_type')->fetch_all() as $result) {
			$result['typename'] = dhtmlspecialchars($result['typename']);
			$word_type[$result['id']] = $result;
			$word_type_option .= "<option value=\"{$result['id']}\">{$result['typename']}</option>";
			if(!empty($_GET['censor_search_type'])) {
				$word_type_option_search .= "<option value=\"{$result['id']}\"".($_GET['censor_search_type'] == $result['id'] ? 'selected' : '' ).">{$result['typename']}</option>";
			}
		}

		shownav('topic', 'nav_posting_censor');
		$anchor = in_array($_GET['anchor'], array('list', 'import', 'wordtype', 'showanchor')) ? $_GET['anchor'] : 'list';
		showsubmenuanchors('nav_posting_censor', array(
			array('admin', 'list', $anchor == 'list'),
			array('misc_censor_batch_add', 'import', $anchor == 'import'),
			array('misc_censor_wordtype_edit', 'wordtype', $anchor == 'wordtype'),
		));
		showtips('misc_censor_tips', 'list_tips', $anchor == 'list');
		showtips('misc_censor_batch_add_tips', 'import_tips', $anchor == 'import');
		showtips('misc_censor_wordtype_tips', 'wordtype_tips', $anchor == 'wordtype');

		showtagheader('div', 'list', $anchor == 'list');
		showformheader("misc&operation=censor&page=$page", '', 'keywordsearch');
		showtableheader();
		echo '<br /><br /><form method="post">'. $lang['keywords'].': <input type="text" name="censorkeyword" value="'.$_GET['censorkeyword'].'" /> &nbsp; <select name="censor_search_type"><option value = "">'.cplang("misc_censor_wordtype_search").'</option><option value="0">'.cplang('misc_censor_word_default_typename').'</option>'.($word_type_option_search ? $word_type_option_search : $word_type_option).'</select> &nbsp;<input type="submit" name="censor_search" value="'.$lang[search].'" class="btn" /> </form>';
		showtablefooter();

		showformheader("misc&operation=censor&page=$page", '', 'listform');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'misc_censor_word', 'misc_censor_replacement', 'misc_censor_type', 'operator'));

		$multipage = '';
		$totalcount = C::t('common_word')->count_by_type_find($ftype, $ffind);
		if($totalcount) {
			$multipage = multi($totalcount, $ppp, $page, ADMINSCRIPT."?action=misc&operation=censor".($ffind ? "&censorkeyword=".$ffind : '' ).($_GET['censor_search_type'] ? "&censor_search_type=".$_GET['censor_search_type'] : '' ));
			foreach(C::t('common_word')->fetch_all_by_type_find($ftype, $ffind, $startlimit, $ppp) as $censor) {
				$censor['replacement'] = $censor['replacement'];
				$censor['replacement'] = dhtmlspecialchars($censor['replacement']);
				$censor['find'] = dhtmlspecialchars($censor['find']);
				$disabled = $_G['adminid'] != 1 && $censor['admin'] != $_G['member']['username'] ? 'disabled' : NULL;
				if(in_array($censor['replacement'], array('{BANNED}', '{MOD}'))) {
					$replacedisplay = 'style="display:none"';
					$optionselected = array();
					foreach(array('{BANNED}', '{MOD}') as $option) {
						$optionselected[$option] = $censor['replacement'] == $option ? 'selected' : '';
					}
				} else {
					$optionselected['{REPLACE}'] = 'selected';
					$replacedisplay = '';
				}
				$word_type_tmp = "<select name='wordtype_select[{$censor['id']}]' id='wordtype_select'><option value='0'>".cplang('misc_censor_word_default_typename')."</option>";
				foreach($word_type AS $key => $val) {
					if($censor['type'] == $val['id']) {
						$word_type_tmp .= "<option value='{$val['id']}' selected>{$val['typename']}</option>";
					} else {
						$word_type_tmp .= "<option value='{$val['id']}'>{$val['typename']}</option>";
					}
				}
				$word_type_tmp .= "</select>";
				showtablerow('', array('class="td25"', '', '', 'class="td26"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$censor[id]\" $disabled>",
					"<input type=\"text\" class=\"txt\" size=\"30\" name=\"find[$censor[id]]\" value=\"$censor[find]\" $disabled>",
					'<select name="replace['.$censor['id'].']" onchange="if(this.options[this.options.selectedIndex].value==\'{REPLACE}\'){$(\'divbanned'.$censor['id'].'\').style.display=\'\';$(\'divbanned'.$censor['id'].'\').value=\'\';}else{$(\'divbanned'.$censor['id'].'\').style.display=\'none\';}" '.$disabled.'>
					<option value="{BANNED}" '.$optionselected['{BANNED}'].'>'.cplang('misc_censor_word_banned').'</option><option value="{MOD}" '.$optionselected['{MOD}'].'>'.cplang('misc_censor_word_moderated').'</option><option value="{REPLACE}" '.$optionselected['{REPLACE}'].'>'.cplang('misc_censor_word_replaced').'</option></select>
					<input class="txt" type="text" size="10" name="replacecontent['.$censor['id'].']" value="'.$censor['replacement'].'" id="divbanned'.$censor['id'].'" '.$replacedisplay.' '.$disabled.'>',
					$word_type_tmp,
					$censor['admin']
				));
			}
		}
		$misc_censor_word_banned = cplang('misc_censor_word_banned');
		$misc_censor_word_moderated = cplang('misc_censor_word_moderated');
		$misc_censor_word_replaced = cplang('misc_censor_word_replaced');
		$misc_censor_word_newtypename = cplang('misc_censor_word_newtypename');
		$misc_censor_word_default_typename = cplang('misc_censor_word_default_typename');
		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,''],
			[1,'<input type="text" class="txt" size="30" name="newfind[]">'], [1, ' <select onchange="if(this.options[this.options.selectedIndex].value==\'{REPLACE}\'){this.nextSibling.style.display=\'\';}else{this.nextSibling.style.display=\'none\';}" name="newreplace[]" ><option value="{BANNED}">$misc_censor_word_banned</option><option value="{MOD}">$misc_censor_word_moderated</option><option value="{REPLACE}">$misc_censor_word_replaced</option></select><input class="txt" type="text" size="15" name="newreplacecontent[]" style="display:none;">']
EOT;
		if($word_type_option) {
			echo ", [1,' <select onchange=\"if(this.options[this.options.selectedIndex].value==\'0\'){this.nextSibling.style.display=\'\';}else{this.nextSibling.style.display=\'none\';}\" name=\"newwordtype[]\" id=\"newwordtype[]\"><option value=\"0\" selected>{$misc_censor_word_default_typename}</option>{$word_type_option}</select><input class=\"txt\" type=\"text\" size=\"10\" name=\"newtypename[]\" >']";
		}
echo <<<EOT
			, [1,'']
		],
		[
			[1,''],
			[1,'<input type="text" class="txt" size="30" name="newtypename[]">']
		]
	];
	</script>
EOT;
		echo '<tr><td></td><td colspan="4"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['add_new'].'</a></div></td></tr>';

		showsubmit('censorsubmit', 'submit', 'del', '', $multipage, false);
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		showtagheader('div', 'import', $anchor == 'import');
		showformheader("misc&operation=censor&page=$page", 'fixpadding');
		showtableheader('', 'fixpadding', 'importform');
		showtablerow('', 'class="vtop rowform"', "<select name=\"wordtype_select\"><option value='0'>".cplang('misc_censor_word_default_typename')."</option>$word_type_option</select>");
		showtablerow('', 'class="vtop rowform"', '<br /><textarea name="addcensors" class="tarea" rows="10" cols="80" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)"></textarea><br /><br />'.mradio('overwrite', array(
				0 => cplang('misc_censor_batch_add_no_overwrite'),
				1 => cplang('misc_censor_batch_add_overwrite'),
				2 => cplang('misc_censor_batch_add_clear')
		), '', FALSE));

		showsubmit('addcensorsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');


		showtagheader('div', 'wordtype', $anchor == 'wordtype');
		showformheader("misc&operation=censor", 'fixpadding');
		showtableheader('', 'fixpadding', 'wordtypeform');
		showsubtitle(array('', 'misc_censor_wordtype_name'));
		if($wordtypecount = C::t('common_word_type')->count()) {
			foreach(C::t('common_word_type')->fetch_all() as $result) {
				showtablerow('', array('class="td25"', ''), array("<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"{$result['id']}\" >", "<input type=\"text\" class=\"txt\" size=\"10\" name=\"typename[{$result['id']}]\" value=\"{$result['typename']}\">"));
			}
		}

		echo '<tr><td></td><td colspan="2"><div><a href="###" onclick="addrow(this, 1)" class="addtr">'.$lang['add_new'].'</a></div></td></tr>';
		showsubmit('wordtypesubmit', 'submit', 'del', '', '', false);
		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {

		if($ids = dimplode($_GET['delete'])) {
			DB::delete('common_word', "id IN ($ids) AND ('{$_G['adminid']}'='1' OR `admin`='{$_G['username']}')");
		}

		if(is_array($_GET['find'])) {
			foreach($_GET['find'] as $id => $val) {
				$_GET['find'][$id]  = $val = trim(str_replace('=', '', $_GET['find'][$id]));
				if(strlen($val) < 3) {
					cpmsg('censor_keywords_tooshort', '', 'error');
				}
				$_GET['replace'][$id] = $_GET['replace'][$id] == '{REPLACE}' ? $_GET['replacecontent'][$id] : $_GET['replace'][$id];
				$_GET['replace'][$id] = daddslashes(str_replace("\\\'", '\'', $_GET['replace'][$id]), 1);
				DB::update('common_word', array(
					'find' => $_GET['find'][$id],
					'replacement' => $_GET['replace'][$id],
					'type' => $_GET['wordtype_select'][$id],
				), DB::field("id", $id)." AND ('{$_G['adminid']}'='1' OR `admin`='{$_G['username']}')");
			}
		}

		$newfind_array = !empty($_GET['newfind']) ? $_GET['newfind'] : array();
		$newreplace_array = !empty($_GET['newreplace']) ? $_GET['newreplace'] : array();
		$newreplacecontent_array = !empty($_GET['newreplacecontent']) ? $_GET['newreplacecontent'] : array();
		$newwordtype = !empty($_GET['newwordtype']) ? $_GET['newwordtype'] : array();
		$newtypename = !empty($_GET['newtypename']) ? $_GET['newtypename'] : array();

		foreach($newfind_array as $key => $value) {
			$newfind = trim(str_replace('=', '', $newfind_array[$key]));
			$newreplace  = trim($newreplace_array[$key]);

			if($newfind != '') {
				if(strlen($newfind) < 3) {
					cpmsg('censor_keywords_tooshort', '', 'error');
				}
				if($newreplace == '{REPLACE}') {
					$newreplace = daddslashes(str_replace("\\\'", '\'', $newreplacecontent_array[$key]), 1);
				}

				if($newtypename) {
					$newtypename = daddslashes($newtypename);
				}

				if($newwordtype) {
					$newwordtype[$key] = intval($newwordtype[$key]);
				}

				if($newwordtype[$key] == 0) {
					if(!empty($newtypename[$key])) {
						$newwordtype[$key] = C::t('common_word_type')->insert(array('typename' => $newtypename[$key]), true);
					}
				}
				if($oldcenser = C::t('common_word')->fetch_by_find($newfind)) {
					cpmsg('censor_keywords_existence', '', 'error');
				} else {
					C::t('common_word')->insert(array(
						'admin' => $_G['username'],
						'find' => $newfind,
						'replacement' => $newreplace,
						'type' => $newwordtype[$key],
					));
				}
			}
		}

		updatecache('censor');
		cpmsg('censor_succeed', "action=misc&operation=censor&page=$page", 'succeed');

	}

} elseif($operation == 'stamp') {

	if(!submitcheck('stampsubmit')) {

		$anchor = in_array($_GET['anchor'], array('list', 'llist', 'add')) ? $_GET['anchor'] : 'list';
		shownav('style', 'nav_thread_stamp');
		showsubmenuanchors('nav_thread_stamp', array(
			array('misc_stamp_thread', 'list', $anchor == 'list'),
			array('misc_stamp_list', 'llist', $anchor == 'llist'),
			array('add', 'add', $anchor == 'add')
		));

		showtagheader('div', 'list', $anchor == 'list');
		showtips('misc_stamp_listtips');
		showformheader('misc&operation=stamp');
		showhiddenfields(array('anchor' => 'list'));
		showtableheader();
		showsubtitle(array('', 'misc_stamp_id', 'misc_stamp_name', 'smilies_edit_image', 'smilies_edit_filename', 'misc_stamp_icon', 'misc_stamp_option'));

		$imgfilter = $stamplist = $stamplistfiles = $stampicons = array();
		foreach(C::t('common_smiley')->fetch_all_by_type('stamplist') as $smiley) {
			$stamplistfiles[$smiley['url']] = $smiley['id'];
			$stampicons[$smiley['url']] = $smiley['typeid'];
			$stamplist[] = $smiley;
		}
		$tselect = '<select><option value="0">'.cplang('none').'</option><option value="1">'.cplang('misc_stamp_option_stick').'</option><option value="2">'.cplang('misc_stamp_option_digest').'</option><option value="3">'.cplang('misc_stamp_option_recommend').'</option><option value="4">'.cplang('misc_stamp_option_recommendto').'</option></select>';
		foreach(C::t('common_smiley')->fetch_all_by_type('stamp') as $smiley) {
			$s = $r = array();
			$s[] = '<select>';
			$r[] = '<select name="typeidnew['.$smiley['id'].']">';
			if($smiley['typeid']) {
				$s[] = '<option value="'.$smiley['typeid'].'">';
				$r[] = '<option value="'.$smiley['typeid'].'" selected="selected">';
				$s[] = '<option value="0">';
				$r[] = '<option value="-1">';
			}
			$tselectrow = str_replace($s, $r, $tselect);
			$dot = strrpos($smiley['url'], '.');
			$fn = substr($smiley['url'], 0, $dot);
			$ext = substr($smiley['url'], $dot + 1);
			$stampicon = $fn.'.small.'.$ext;
			$small = array_key_exists($stampicon, $stamplistfiles);
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td23"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"code[$smiley[id]]\" value=\"$smiley[code]\">",
				"<img src=\"static/image/stamp/$smiley[url]\">",
				$smiley['url'],
				($small ? '<input class="checkbox" type="checkbox" name="stampicon['.$smiley['id'].']"'.($smiley['id'] == $stampicons[$stampicon] ? ' checked="checked"' : '').' value="'.$stamplistfiles[$stampicon].'" /><img class="vmiddle" src="static/image/stamp/'.$stampicon.'">': ''),
				$tselectrow,
			));
			$imgfilter[] = $smiley['url'];
		}

		showsubmit('stampsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		showtagheader('div', 'llist', $anchor == 'llist');
		showtips('misc_stamp_listtips');
		showformheader('misc&operation=stamp&type=list');
		showhiddenfields(array('anchor' => 'llist'));
		showtableheader();
		showsubtitle(array('', 'misc_stamp_id', 'misc_stamp_listname', 'smilies_edit_image', 'smilies_edit_filename'));

		foreach($stamplist as $smiley) {
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td23"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"code[$smiley[id]]\" value=\"$smiley[code]\">",
				"<img src=\"static/image/stamp/$smiley[url]\">",
				$smiley['url']
			));
			$imgfilter[] = $smiley['url'];
		}

		showsubmit('stampsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		showtagheader('div', 'add', $anchor == 'add');
		showformheader('misc&operation=stamp');
		showtips('misc_stamp_addtips');
		showtableheader();
		showsubtitle(array('add', 'misc_stamp_type', 'misc_stamp_id', 'misc_stamp_imagename', 'smilies_edit_image', 'smilies_edit_filename'));

		$newid = 0;
		$imgextarray = array('png', 'gif');
		$stampsdir = dir(DISCUZ_ROOT.'./static/image/stamp');
		while($entry = $stampsdir->read()) {
			if(in_array(strtolower(fileext($entry)), $imgextarray) && !in_array($entry, $imgfilter) && is_file(DISCUZ_ROOT.'./static/image/stamp/'.$entry)) {
				showtablerow('', array('class="td25"', 'class="td28 td24 rowform"', 'class="td23"'), array(
					"<input type=\"checkbox\" name=\"addcheck[$newid]\" id=\"addcheck_$newid\" class=\"checkbox\">",
					"<ul onmouseover=\"altStyle(this);\">".
					"<li class=\"checked\"><input type=\"radio\" name=\"addtype[$newid]\" value=\"0\" checked=\"checked\" class=\"radio\">".cplang('misc_stamp_thread')."</li>".
					"<li><input type=\"radio\" name=\"addtype[$newid]\" value=\"1\" class=\"radio\" onclick=\"$('addcheck_$newid').checked='true'\">".cplang('misc_stamp_list')."</li>".
					"</ul>",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"adddisplayorder[$newid]\" value=\"0\">",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"addcode[$newid]\" value=\"\">",
					"<img src=\"static/image/stamp/$entry\" />",
					"<input type=\"hidden\" class=\"txt\" size=\"35\" name=\"addurl[$newid]\" value=\"$entry\">$entry"
				));
				$newid ++;
			}
		}
		$stampsdir->close();
		if(!$newid) {
			showtablerow('', array('class="td25"', 'colspan="3"'), array('', cplang('misc_stamp_tips')));
		} else {
			showsubmit('stampsubmit', 'submit', '<input type="checkbox" class="checkbox" name="chkall2" id="chkall2" onclick="checkAll(\'prefix\', this.form, \'addcheck\', \'chkall2\')"><label for="chkall2">'.cplang('select_all').'</label>');
		}

		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {

		if($_GET['delete']) {
			C::t('common_smiley')->delete($_GET['delete']);
		}

		if(is_array($_GET['displayorder'])) {
			$typeidset = array();
			foreach($_GET['displayorder'] as $id => $val) {
				$_GET['displayorder'][$id] = intval($_GET['displayorder'][$id]);
				if($_GET['displayorder'][$id] >= 0 && $_GET['displayorder'][$id] < 100) {
					$typeidadd = '';
					if($_GET['typeidnew'][$id]) {
						if(!isset($typeidset[$_GET['typeidnew'][$id]])) {
							$_GET['typeidnew'][$id] = $_GET['typeidnew'][$id] > 0 ? $_GET['typeidnew'][$id] : 0;
							$typeidadd = ",typeid='{$_GET['typeidnew'][$id]}'";
							$typeidset[$_GET['typeidnew'][$id]] = TRUE;
						} else {
							$_GET['typeidnew'][$id] = 0;
						}
					}
					C::t('common_smiley')->update($id, array(
						'displayorder' => $_GET['displayorder'][$id],
						'code' => $_GET['code'][$id],
						'typeid' => $_GET['typeidnew'][$id],
					));
				}
			}
		}

		if(is_array($_GET['addurl'])) {
			$count = C::t('common_smiley')->count_by_type(array('stamp','stamplist'));
			if($count < 100) {
				foreach($_GET['addurl'] as $k => $v) {
					if($_GET['addcheck'][$k] && $_GET['addcode'][$k]) {
						$count++;

						C::t('common_smiley')->insert(array(
							'displayorder' => '0',
							'type' => (!$_GET['addtype'][$k] ? 'stamp' : 'stamplist'),
							'url' => $_GET['addurl'][$k],
							'code' => $_GET['addcode'][$k],
						));
					}
				}
			}
		}

		C::t('common_smiley')->update_by_type('stamplist', array('typeid' => 0));
		if(is_array($_GET['stampicon'])) {
			foreach($_GET['stampicon'] as $k => $v) {
				if($_GET['typeidnew'][$k]) {
					$k = 0;
				}
				C::t('common_smiley')->update_by_id_type($v, 'stamplist', array('typeid' => $k));
			}
		}

		updatecache('stamps');
		updatecache('stamptypeid');

		cpmsg('thread_stamp_succeed', "action=misc&operation=stamp&anchor=$_GET[anchor]", 'succeed');
	}

} elseif($operation == 'attachtype') {

	if(!submitcheck('typesubmit')) {

		$attachtypes = '';
		$query = DB::query("SELECT * FROM ".DB::table('forum_attachtype')." WHERE fid='0'");
		while($type = DB::fetch($query)) {
			$type['maxsize'] = round($type['maxsize'] / 1024);
			$attachtypes .= showtablerow('', array('class="td25"', 'class="td24"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[id]\" />",
				"<input type=\"text\" class=\"txt\" size=\"10\" name=\"extension[$type[id]]\" value=\"$type[extension]\" />",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"maxsize[$type[id]]\" value=\"$type[maxsize]\" />"
			), TRUE);
		}

?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input name="newextension[]" type="text" class="txt" size="10">', 'td24'],
		[1,'<input name="newmaxsize[]" type="text" class="txt" size="15">']
	]
];
</script>
<?php

		shownav('global', 'nav_posting_attachtype');
		showsubmenu('nav_posting_attachtype');
		showtips('misc_attachtype_tips');
		showformheader('misc&operation=attachtype');
		showtableheader();
		showtablerow('class="partition"', array('class="td25"', 'class="td24"'), array('', cplang('misc_attachtype_ext'), cplang('misc_attachtype_maxsize')));
		echo $attachtypes;
		echo '<tr><td></td><td colspan="2"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['misc_attachtype_add'].'</a></div></tr>';
		showsubmit('typesubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($ids = dimplode($_GET['delete'])) {
			DB::delete('forum_attachtype', "id IN ($ids) AND fid='0'");
		}

		if(is_array($_GET['extension'])) {
			foreach($_GET['extension'] as $id => $val) {
				DB::update('forum_attachtype', array(
					'extension' => $_GET['extension'][$id],
					'maxsize' => $_GET['maxsize'][$id] * 1024,
				), DB::field("id", $id));
			}
		}

		if(is_array($_GET['newextension'])) {
			foreach($_GET['newextension'] as $key => $value) {
				if($newextension1 = trim($value)) {
					if(C::t('forum_attachtype')->count_by_extension_fid($newextension1, 0)) {
						cpmsg('attachtypes_duplicate', '', 'error');
					}
					C::t('forum_attachtype')->insert(array(
						'extension' => $newextension1,
						'maxsize' => $_GET['newmaxsize'][$key] * 1024,
						'fid' => 0
					));
				}
			}
		}

		updatecache('attachtype');
		cpmsg('attachtypes_succeed', 'action=misc&operation=attachtype', 'succeed');

	}

} elseif($operation == 'cron') {

	if(empty($_GET['edit']) && empty($_GET['run'])) {

		if(!submitcheck('cronssubmit')) {

			shownav('tools', 'misc_cron');
			showsubmenu('nav_misc_cron');
			showtips('misc_cron_tips');
			showformheader('misc&operation=cron');
			showtableheader('', 'fixpadding');
			showsubtitle(array('', 'name', 'available', 'type', 'time', 'misc_cron_last_run', 'misc_cron_next_run', ''));

			$query = DB::query("SELECT * FROM ".DB::table('common_cron')." ORDER BY type DESC");
			while($cron = DB::fetch($query)) {
				$disabled = $cron['weekday'] == -1 && $cron['day'] == -1 && $cron['hour'] == -1 && $cron['minute'] == '' ? 'disabled' : '';

				if($cron['day'] > 0 && $cron['day'] < 32) {
					$cron['time'] = cplang('misc_cron_permonth').$cron['day'].cplang('misc_cron_day');
				} elseif($cron['weekday'] >= 0 && $cron['weekday'] < 7) {
					$cron['time'] = cplang('misc_cron_perweek').cplang('misc_cron_week_day_'.$cron['weekday']);
				} elseif($cron['hour'] >= 0 && $cron['hour'] < 24) {
					$cron['time'] = cplang('misc_cron_perday');
				} else {
					$cron['time'] = cplang('misc_cron_perhour');
				}

				$cron['time'] .= $cron['hour'] >= 0 && $cron['hour'] < 24 ? sprintf('%02d', $cron[hour]).cplang('misc_cron_hour') : '';

				if(!in_array($cron['minute'], array(-1, ''))) {
					foreach($cron['minute'] = explode("\t", $cron['minute']) as $k => $v) {
						$cron['minute'][$k] = sprintf('%02d', $v);
					}
					$cron['minute'] = implode(',', $cron['minute']);
					$cron['time'] .= $cron['minute'].cplang('misc_cron_minute');
				} else {
					$cron['time'] .= '00'.cplang('misc_cron_minute');
				}

				$cron['lastrun'] = $cron['lastrun'] ? dgmdate($cron['lastrun'], $_G['setting']['dateformat']."<\b\\r />".$_G['setting']['timeformat']) : '<b>N/A</b>';
				$cron['nextcolor'] = $cron['nextrun'] && $cron['nextrun'] + $_G['setting']['timeoffset'] * 3600 < TIMESTAMP ? 'style="color: #ff0000"' : '';
				$cron['nextrun'] = $cron['nextrun'] ? dgmdate($cron['nextrun'], $_G['setting']['dateformat']."<\b\\r />".$_G['setting']['timeformat']) : '<b>N/A</b>';
				$cron['run'] = $cron['available'];
				$efile = explode(':', $cron['filename']);
				if(count($efile) > 1 && !in_array($efile[0], $_G['setting']['plugins']['available'])) {
					$cron['run'] = 0;
				}

				showtablerow('', array('class="td25"', 'class="crons"', 'class="td25"', 'class="td25"', 'class="td23"', 'class="td23"', 'class="td23"'.$cron['nextcolor'], 'class="td25"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$cron[cronid]\" ".($cron['type'] == 'system' ? 'disabled' : '').">",
					"<input type=\"text\" class=\"txt\" name=\"namenew[$cron[cronid]]\" size=\"20\" value=\"$cron[name]\"><br /><b>$cron[filename]</b>",
					"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$cron[cronid]]\" value=\"1\" ".($cron['available'] ? 'checked' : '')." $disabled>",
					cplang($cron['type'] == 'system' ? 'inbuilt' : ($cron['type'] == 'plugin' ? 'plugin' : 'custom')),
					$cron[time],
					$cron[lastrun],
					$cron[nextrun],
					"<a href=\"".ADMINSCRIPT."?action=misc&operation=cron&edit=$cron[cronid]\" class=\"act\">$lang[edit]</a><br />".
					($cron['run'] ? " <a href=\"".ADMINSCRIPT."?action=misc&operation=cron&run=$cron[cronid]\" class=\"act\">$lang[misc_cron_run]</a>" : " <a href=\"###\" class=\"act\" disabled>$lang[misc_cron_run]</a>")
				));
			}

			showtablerow('', array('','colspan="10"'), array(
				cplang('add_new'),
				'<input type="text" class="txt" name="newname" value="" size="20" />'
			));
			showsubmit('cronssubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

		} else {

			if($ids = dimplode($_GET['delete'])) {
				DB::delete('common_cron', "cronid IN ($ids) AND type='user'");
			}

			if(is_array($_GET['namenew'])) {
				foreach($_GET['namenew'] as $id => $name) {
					$newcron = array(
						'name' => dhtmlspecialchars($_GET['namenew'][$id]),
						'available' => $_GET['availablenew'][$id]
					);
					if(empty($_GET['availablenew'][$id])) {
						$newcron['nextrun'] = '0';
					}
					DB::update('common_cron', $newcron, DB::field("cronid", $id));
				}
			}

			if($newname = trim($_GET['newname'])) {
				DB::insert('common_cron', array(
					'name' => dhtmlspecialchars($newname),
					'type' => 'user',
					'available' => '0',
					'weekday' => '-1',
					'day' => '-1',
					'hour' => '-1',
					'minute' => '',
					'nextrun' => $_G['timestamp'],
				));
			}

			$query = DB::query("SELECT cronid, filename FROM ".DB::table('common_cron'));
			while($cron = DB::fetch($query)) {
				$efile = explode(':', $cron['filename']);
				$pluginid = '';
				if(count($efile) > 1 && ispluginkey($efile[0])) {
					$pluginid = $efile[0];
					$cron['filename'] = $efile[1];
				}
				if(!$pluginid) {
					if(!file_exists(DISCUZ_ROOT.'./source/include/cron/'.$cron['filename'])) {
						DB::update('common_cron', array(
							'available' => '0',
							'nextrun' => '0',
						), "cronid='$cron[cronid]'");
					}
				} else {
					if(!file_exists(DISCUZ_ROOT.'./source/plugin/'.$pluginid.'/cron/'.$cron['filename'])) {
						DB::delete('common_cron', "cronid='$cron[cronid]'");
					}
				}
			}

			updatecache('setting');
			cpmsg('crons_succeed', 'action=misc&operation=cron', 'succeed');

		}

	} else {

		$cronid = empty($_GET['run']) ? $_GET['edit'] : $_GET['run'];
		$cron = DB::fetch_first("SELECT * FROM ".DB::table('common_cron')." WHERE ".DB::field("cronid", $cronid));
		if(!$cron) {
			cpmsg('cron_not_found', '', 'error');
		}
		$cron['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $cron['filename']);
		$cronminute = str_replace("\t", ',', $cron['minute']);
		$cron['minute'] = explode("\t", $cron['minute']);

		if(!empty($_GET['edit'])) {

			if(!submitcheck('editsubmit')) {

				shownav('tools', 'misc_cron');
				showsubmenu($lang['misc_cron_edit'].' - '.$cron['name']);
				showtips('misc_cron_edit_tips');

				$weekdayselect = $dayselect = $hourselect = '';

				for($i = 0; $i <= 6; $i++) {
					$weekdayselect .= "<option value=\"$i\" ".($cron['weekday'] == $i ? 'selected' : '').">".$lang['misc_cron_week_day_'.$i]."</option>";
				}

				for($i = 1; $i <= 31; $i++) {
					$dayselect .= "<option value=\"$i\" ".($cron['day'] == $i ? 'selected' : '').">$i $lang[misc_cron_day]</option>";
				}

				for($i = 0; $i <= 23; $i++) {
					$hourselect .= "<option value=\"$i\" ".($cron['hour'] == $i ? 'selected' : '').">$i $lang[misc_cron_hour]</option>";
				}

				shownav('tools', 'misc_cron');
				showformheader("misc&operation=cron&edit=$cronid");
				showtableheader();
				showsetting('misc_cron_edit_weekday', '', '', "<select name=\"weekdaynew\"><option value=\"-1\">*</option>$weekdayselect</select>");
				showsetting('misc_cron_edit_day', '', '', "<select name=\"daynew\"><option value=\"-1\">*</option>$dayselect</select>");
				showsetting('misc_cron_edit_hour', '', '', "<select name=\"hournew\"><option value=\"-1\">*</option>$hourselect</select>");
				showsetting('misc_cron_edit_minute', 'minutenew', $cronminute, 'text');
				showsetting('misc_cron_edit_filename', 'filenamenew', $cron['filename'], 'text');
				showsubmit('editsubmit');
				showtablefooter();
				showformfooter();

			} else {

				$daynew = $_GET['weekdaynew'] != -1 ? -1 : $_GET['daynew'];
				if(strpos($_GET['minutenew'], ',') !== FALSE) {
					$minutenew = explode(',', $_GET['minutenew']);
					foreach($minutenew as $key => $val) {
						$minutenew[$key] = $val = intval($val);
						if($val < 0 || $var > 59) {
							unset($minutenew[$key]);
						}
					}
					$minutenew = array_slice(array_unique($minutenew), 0, 12);
					$minutenew = implode("\t", $minutenew);
				} else {
					$minutenew = intval($_GET['minutenew']);
					$minutenew = $minutenew >= 0 && $minutenew < 60 ? $minutenew : '';
				}

				$efile = explode(':', $_GET['filenamenew']);
				if(substr($_GET['filenamenew'], -4) !== '.php') {
					cpmsg('crons_filename_illegal', '', 'error');
				}

				$pluginid = '';
				if(count($efile) > 1 && ispluginkey($efile[0])) {
					$pluginid = $efile[0];
					$_GET['filenamenew'] = $efile[1];
				}

				if(!$pluginid) {
					if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $_GET['filenamenew'])) {
						cpmsg('crons_filename_illegal', '', 'error');
					} elseif(!is_readable(DISCUZ_ROOT.($cronfile = "./source/include/cron/{$_GET['filenamenew']}"))) {
						cpmsg('crons_filename_invalid', '', 'error', array('cronfile' => $cronfile));
					} elseif($_GET['weekdaynew'] == -1 && $daynew == -1 && $_GET['hournew'] == -1 && $minutenew === '') {
						cpmsg('crons_time_invalid', '', 'error');
					}
				} else {
					if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $_GET['filenamenew'])) {
						cpmsg('crons_filename_illegal', '', 'error');
					} elseif(!is_readable(DISCUZ_ROOT.($cronfile = "./source/plugin/$pluginid/cron/{$_GET['filenamenew']}"))) {
						cpmsg('crons_filename_invalid', '', 'error', array('cronfile' => $cronfile));
					} elseif($_GET['weekdaynew'] == -1 && $daynew == -1 && $_GET['hournew'] == -1 && $minutenew === '') {
						cpmsg('crons_time_invalid', '', 'error');
					}
					$_GET['filenamenew'] = $pluginid.':'.$_GET['filenamenew'];
				}

				DB::update('common_cron', array(
					'weekday' => $_GET['weekdaynew'],
					'day' => $daynew,
					'hour' => $_GET['hournew'],
					'minute' => $minutenew,
					'filename' => trim($_GET['filenamenew']),
				), DB::field("cronid", $cronid));

				discuz_cron::run($cronid);

				cpmsg('crons_succeed', 'action=misc&operation=cron', 'succeed');

			}

		} else {

			$efile = explode(':', $cron['filename']);
			if(count($efile) > 1 && ispluginkey($efile[0])) {
				$cronfile = DISCUZ_ROOT.'./source/plugin/'.$efile[0].'/cron/'.$efile[1];
			} else {
				$cronfile = DISCUZ_ROOT."./source/include/cron/$cron[filename]";
			}

			if(substr($cronfile, -4) !== '.php' || !file_exists($cronfile)) {
				cpmsg('crons_run_invalid', '', 'error', array('cronfile' => $cronfile));
			} else {
				discuz_cron::run($cron['cronid']);
				cpmsg('crons_run_succeed', 'action=misc&operation=cron', 'succeed');
			}

		}

	}

} elseif($operation == 'focus') {

	require_once libfile('function/post');

	$focus = C::t('common_setting')->fetch('focus', true);
	$focus_position_array = array(
		array('portal', cplang('misc_focus_position_portal')),
		array('home', cplang('misc_focus_position_home')),
		array('member', cplang('misc_focus_position_member')),
		array('forum', cplang('misc_focus_position_forum')),
		array('group', cplang('misc_focus_position_group')),
		array('search', cplang('misc_focus_position_search')),
	);

	if(!$do) {

		if(!submitcheck('focussubmit')) {

			shownav('extended', 'misc_focus');
			showsubmenu('misc_focus', array(
				array('config', 'misc&operation=focus&do=config', 0),
				array('admin', 'misc&operation=focus', 1),
				array('add', 'misc&operation=focus&do=add')
			));
			showtips('misc_focus_tips');
			showformheader('misc&operation=focus');
			showtableheader('admin', 'fixpadding');
			showsubtitle(array('', 'subject', 'available', ''));
			if(is_array($focus['data'])) {
				foreach($focus['data'] as $k => $v) {
					showtablerow('', array('class="td25"','', 'class="td25"', 'class="td25"'), array(
						"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$k\">",
						'<a href="'.$v['url'].'" target="_blank">'.$v[subject].'</a>',
						"<input type=\"checkbox\" class=\"checkbox\" name=\"available[$k]\" value=\"1\" ".($v['available'] ? 'checked' : '').">",
						"<a href=\"".ADMINSCRIPT."?action=misc&operation=focus&do=edit&id=$k\" class=\"act\">$lang[edit]</a>",
					));
				}
			}

			showsubmit('focussubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

		} else {

			$newfocus = array();
			$newfocus['title'] = $focus['title'];
			$newfocus['data'] = array();
			if(isset($focus['data']) && is_array($focus['data'])) foreach($focus['data'] as $k => $v) {
				if(is_array($_GET['delete']) && in_array($k, $_GET['delete'])) {
					unset($focus['data'][$k]);
				} else {
					$v['available'] = $_GET['available'][$k] ? 1 : 0;
					$newfocus['data'][$k] = $v;
				}
			}
			$newfocus['cookie'] = $focus['cookie'];
			C::t('common_setting')->update('focus', $newfocus);
			updatecache(array('setting', 'focus'));

			cpmsg('focus_update_succeed', 'action=misc&operation=focus', 'succeed');

		}

	} elseif($do == 'add') {

		if(count($focus['data']) >= 10) {
			cpmsg('focus_add_num_limit', 'action=misc&operation=focus', 'error');
		}

		if(!submitcheck('addsubmit')) {

			shownav('extended', 'misc_focus');
			showsubmenu('misc_focus', array(
				array('config', 'misc&operation=focus&do=config', 0),
				array('admin', 'misc&operation=focus', 0),
				array('add', 'misc&operation=focus&do=add', 1)
			));
			showformheader('misc&operation=focus&do=add');
			showtableheader('misc_focus_handadd', 'fixpadding');
			showsetting('misc_focus_handurl', 'focus_url', '', 'text');
			showsetting('misc_focus_handsubject' , 'focus_subject', '', 'text');
			showsetting('misc_focus_handsummary', 'focus_summary', '', 'textarea');
			showsetting('misc_focus_handimg', 'focus_image', '', 'text');

			showsetting('misc_focus_position', array('focus_position', $focus_position_array), '', 'mcheckbox');
			showsubmit('addsubmit', 'submit', '', '');
			showtablefooter();
			showformfooter();

		} else {

			if($_GET['focus_url'] && $_GET['focus_subject'] && $_GET['focus_summary']) {

				if(is_array($focus['data'])) {
					foreach($focus['data'] as $item) {
						if($item['url'] == $_GET['focus_url']) {
							cpmsg('focus_topic_exists', 'action=misc&operation=focus', 'error');
						}
					}
				}
				$focus['data'][] = array(
					'url' => $_GET['focus_url'],
					'available' => '1',
					'subject' => cutstr($_GET['focus_subject'], 80),
					'summary' => $_GET['focus_summary'],
					'image' => $_GET['focus_image'],
					'aid' => 0,
					'filename' => basename($_GET['focus_image']),
					'position' => $_GET['focus_position'],
				);
				C::t('common_setting')->update('focus', $focus);
				updatecache(array('setting', 'focus'));
			} else {
				cpmsg('focus_topic_addrequired', '', 'error');
			}

			cpmsg('focus_add_succeed', 'action=misc&operation=focus', 'succeed');

		}

	} elseif($do == 'edit') {
		$id = intval($_GET['id']);
		if(!$item = $focus['data'][$id]) {
			cpmsg('focus_topic_noexists', 'action=misc&operation=focus', 'error');
		}
		if(!submitcheck('editsubmit')) {

			shownav('extended', 'misc_focus');
			showsubmenu('misc_focus', array(
				array('config', 'misc&operation=focus&do=config', 0),
				array('admin', 'misc&operation=focus', 0),
				array('add', 'misc&operation=focus&do=add', 0)
			));

			showformheader('misc&operation=focus&do=edit&id='.$id);
			showtableheader('misc_focus_edit', 'fixpadding');
			showsetting('misc_focus_handurl', 'focus_url', $item['url'], 'text');
			showsetting('misc_focus_handsubject' , 'focus_subject', $item['subject'], 'text');
			showsetting('misc_focus_handsummary', 'focus_summary', $item['summary'], 'textarea');
			showsetting('misc_focus_handimg', 'focus_image', $item['image'], 'text');
			showsetting('misc_focus_position', array('focus_position', $focus_position_array), $item['position'], 'mcheckbox');

			showsubmit('editsubmit', 'submit');
			showtablefooter();
			showformfooter();

		} else {

			if($_GET['focus_url'] && $_GET['focus_subject'] && $_GET['focus_summary']) {
				if($item['type'] == 'thread') {
					$_GET['focus_url'] = $item['url'];
				} else {
					$focus_filename = basename($_GET['focus_image']);
				}
				$item = array(
					'url' => $_GET['focus_url'],
					'tid' => $item['tid'],
					'available' => '1',
					'subject' => cutstr($_GET['focus_subject'], 80),
					'summary' => $_GET['focus_summary'],
					'image' => $_GET['focus_image'],
					'aid' => 0,
					'filename' => $focus_filename,
					'position' => $_GET['focus_position'],
				);
				$focus['data'][$id] = $item;
				C::t('common_setting')->update('focus', $focus);
				updatecache(array('setting', 'focus'));
			}

			cpmsg('focus_edit_succeed', 'action=misc&operation=focus', 'succeed');

		}

	} elseif($do == 'config') {

		if(!submitcheck('confsubmit')) {

			shownav('extended', 'misc_focus');
			showsubmenu('misc_focus', array(
				array('config', 'misc&operation=focus&do=config', 1),
				array('admin', 'misc&operation=focus', 0),
				array('add', 'misc&operation=focus&do=add', 0)
			));
			showformheader('misc&operation=focus&do=config');
			showtableheader('config', 'fixpadding');
			showsetting('misc_focus_area_title', 'focus_title', empty($focus['title']) ? cplang('misc_focus') : $focus['title'], 'text');
			showsetting('misc_focus_area_cookie', 'focus_cookie', empty($focus['cookie']) ? 0 : $focus['cookie'], 'text');
			showsubmit('confsubmit', 'submit');
			showtablefooter();
			showformfooter();

		} else {

			$focus['title'] = trim($_GET['focus_title']);
			$focus['title'] = empty($focus['title']) ? cplang('misc_focus') : $focus['title'];
			$focus['cookie'] = trim(intval($_GET['focus_cookie']));
			$focus['cookie'] = empty($focus['cookie']) ? 0 : $focus['cookie'];
			C::t('common_setting')->update('focus', $focus);
			updatecache(array('setting', 'focus'));

			cpmsg('focus_conf_succeed', 'action=misc&operation=focus&do=config', 'succeed');

		}

	}

} elseif($operation == 'checkstat') {
	if($statid && $statkey) {
		$q = "statid=$statid&statkey=$statkey";
		$q=rawurlencode(base64_encode($q));
		$url = 'https://stat.discuz.vip/stat_ins.php?action=checkstat&q='.$q;
		$key = dfsockopen($url);
		$newstatdisable = $key == $statkey ? 0 : 1;
		if($newstatdisable != $statdisable) {
			C::t('common_setting')->update('statdisable', $newstatdisable);
			require_once libfile('function/cache');
			updatecache('setting');
		}
	}
} elseif($operation == 'custommenu') {

	if(!$do) {

		if(!submitcheck('optionsubmit')) {
			$mpp = 10;
			$startlimit = ($page - 1) * $mpp;
			$num = C::t('common_admincp_cmenu')->count_by_uid($_G['uid']);
			$multipage = multi($num, $mpp, $page, ADMINSCRIPT.'?action=misc&operation=custommenu');
			$optionlist = $ajaxoptionlist = '';
			foreach(C::t('common_admincp_cmenu')->fetch_all_by_uid($_G['uid'], $startlimit, $mpp) as $custom) {
				$custom['url'] = rawurldecode($custom['url']);
				$optionlist .= showtablerow('', array('class="td25"', 'class="td28"', '', 'class="td26"'), array(
					"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$custom[id]\">",
					"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayordernew[$custom[id]]\" value=\"$custom[displayorder]\">",
					"<input type=\"text\" class=\"txt\" size=\"25\" name=\"titlenew[$custom[id]]\" value=\"".cplang($custom['title'])."\"><input type=\"hidden\" name=\"langnew[$custom[id]]\" value=\"$custom[title]\">",
					"<input type=\"text\" class=\"txt\" size=\"40\" name=\"urlnew[$custom[id]]\" value=\"$custom[url]\">"
				), TRUE);
				$ajaxoptionlist .= '<li><a href="'.$custom['url'].'" target="'.(substr(rawurldecode($custom['url']), 0, 17) == ADMINSCRIPT.'?action=' ? 'main' : '_blank').'">'.cplang($custom['title']).'</a></li>';
			}

			echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
			[1,'<input type="text" class="txt" name="newtitle[]" size="25">'],
			[1,'<input type="text" class="txt" name="newurl[]" size="40">', 'td26']
		]
	];
</script>
EOT;
			shownav('tools', 'nav_custommenu');
			showsubmenu('nav_custommenu');
			showformheader('misc&operation=custommenu');
			showtableheader();
			showsubtitle(array('', 'display_order', 'name', 'URL'));
			echo $optionlist;
			echo '<tr><td></td><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['custommenu_add'].'</a></div></td></tr>';
			showsubmit('optionsubmit', 'submit', 'del', '', $multipage);
			showtablefooter();
			showformfooter();

		} else {

			if($ids = dimplode($_GET['delete'])) {
				C::t('common_admincp_cmenu')->delete($_GET['delete'], $_G['uid']);
			}

			if(is_array($_GET['titlenew'])) {
				foreach($_GET['titlenew'] as $id => $title) {
					$_GET['urlnew'][$id] = rawurlencode($_GET['urlnew'][$id]);
					$title = dhtmlspecialchars($_GET['langnew'][$id] && cplang($_GET['langnew'][$id], false) ? $_GET['langnew'][$id] : $title);
					$ordernew = intval($_GET['displayordernew'][$id]);
					C::t('common_admincp_cmenu')->update($id, array('title' => $title, 'displayorder' => $ordernew, 'url' => dhtmlspecialchars($_GET['urlnew'][$id])));
				}
			}

			if(is_array($_GET['newtitle'])) {
				foreach($_GET['newtitle'] as $k => $v) {
					$_GET['urlnew'][$k] = rawurlencode($_GET['urlnew'][$k]);
					C::t('common_admincp_cmenu')->insert(array(
						'title' => dhtmlspecialchars($v),
						'displayorder' => intval($_GET['newdisplayorder'][$k]),
						'url' => dhtmlspecialchars($_GET['newurl'][$k]),
						'sort' => 1,
						'uid' => $_G['uid'],
					));
				}
			}

			updatemenu('index');
			cpmsg('custommenu_edit_succeed', 'action=misc&operation=custommenu', 'succeed');

		}

	} elseif($do == 'add') {

		if($_GET['title'] && $_GET['url']) {
			admincustom($_GET['title'], dhtmlspecialchars($_GET['url']), 1);
			updatemenu('index');
			cpmsg('custommenu_add_succeed', rawurldecode($_GET['url']), 'succeed', array('title' => cplang($_GET['title'])));
		} else {
			cpmsg('parameters_error', '', 'error');
		}
	}

}

?>