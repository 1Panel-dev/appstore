<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_smilies.php 29236 2012-03-30 05:34:47Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$imgextarray = array('jpg', 'gif', 'png');
$id = $_GET['id'];
if($operation == 'export' && $id) {
	$smileyarray = C::t('forum_imagetype')->fetch($id);
	if(!$smileyarray) {
		cpheader();
		cpmsg('smilies_type_nonexistence', '', 'error');
	}

	$smileyarray['smilies'] = array();
	foreach(C::t('common_smiley')->fetch_all_by_typeid_type($id, 'smiley') as $smiley) {
		$smileyarray['smilies'][] = $smiley;
	}

	$smileyarray['version'] = strip_tags($_G['setting']['version']);
	exportdata('Discuz! Smilies', $smileyarray['name'], $smileyarray);
}

cpheader();

if(!$operation) {

	if(!submitcheck('smiliessubmit')) {

		shownav('style', 'smilies_edit');
		showsubmenu('nav_smilies', array(
			array('smilies_type', 'smilies', 1),
			array('smilies_import', 'smilies&operation=import', 0),
		));
		showtips('smilies_tips_smileytypes');
		showformheader('smilies');
		showtableheader();
		showsubtitle(array('', 'display_order', 'enable', 'smilies_type', 'dir', 'smilies_nums', ''));

		$smtypes = 0;
		$dirfilter = array();
		foreach(C::t('forum_imagetype')->fetch_all_by_type('smiley') as $type) {
			$smiliesnum = C::t('common_smiley')->count_by_type_typeid('smiley', $type['typeid']);
			showtablerow('', array('class="td25"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[typeid]\" ".($smiliesnum ? 'disabled' : '').">",
				"<input type=\"text\" class=\"txt\" name=\"displayordernew[$type[typeid]]\" value=\"$type[displayorder]\" size=\"2\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$type[typeid]]\" value=\"1\" ".($type['available'] ? 'checked' : '').">",
				"<input type=\"text\" class=\"txt\" name=\"namenew[$type[typeid]]\" value=\"$type[name]\" size=\"15\">",
				"./static/image/smiley/$type[directory]",
				"$smiliesnum<input type=\"hidden\" name=\"smiliesnum[$type[typeid]]\" value=\"$smiliesnum\" />",
				"<a href=\"".ADMINSCRIPT."?action=smilies&operation=update&id=$type[typeid]\" class=\"act\" onclick=\"return confirm('$lang[smilies_update_confirm1]$type[directory]$lang[smilies_update_confirm2]$type[name]$lang[smilies_update_confirm3]')\">$lang[smilies_update]</a>&nbsp;".
				"<a href=\"".ADMINSCRIPT."?action=smilies&operation=export&id=$type[typeid]\" class=\"act\">$lang[export]</a>&nbsp;".
				"<a href=\"".ADMINSCRIPT."?action=smilies&operation=edit&id=$type[typeid]\" class=\"act\">$lang[detail]</a>"
			));
			$dirfilter[] = $type['directory'];
			$smtypes++;
		}

		$smdir = DISCUZ_ROOT.'./static/image/smiley';
		$smtypedir = dir($smdir);
		$dirnum = 0;
		while($entry = $smtypedir->read()) {
			if($entry != '.' && $entry != '..' && !in_array($entry, $dirfilter) && preg_match("/^\w+$/", $entry) && strlen($entry) < 30 && is_dir($smdir.'/'.$entry)){
				$smiliesdir = dir($smdir.'/'.$entry);
				$smnums = 0;
				$smilies = '';
				while($subentry = $smiliesdir->read()) {
					if(in_array(strtolower(fileext($subentry)), $imgextarray) && preg_match("/^[\w\-\.\[\]\(\)\<\> &]+$/", substr($subentry, 0, strrpos($subentry, '.'))) && strlen($subentry) < 30 && is_file($smdir.'/'.$entry.'/'.$subentry)) {
						$smilies .= '<input type="hidden" name="smilies['.$dirnum.']['.$smnums.'][available]" value="1"><input type="hidden" name="smilies['.$dirnum.']['.$smnums.'][displayorder]" value="0"><input type="hidden" name="smilies['.$dirnum.']['.$smnums.'][url]" value="'.$subentry.'">';
						$smnums++;
					}
				}
				showtablerow('', array('class="td25"', 'class="td28"'), array(
					($lang['add_new']),
					'<input type="text" class="txt" name="newdisplayorder['.$dirnum.']" value="'.($smtypes + $dirnum + 1).'" size="2" />',
					'<input class="checkbox" type="checkbox" name="newavailable['.$dirnum.']" value="1"'.($smnums ? ' checked="checked"' : ' disabled="disabled"').' />',
					'<input type="text" class="txt" name="newname['.$dirnum.']" value="" size="15" />',
					'./static/image/smiley/'.$entry.'<input type="hidden" name="newdirectory['.$dirnum.']" value="'.$entry.'">',
					"$smnums<input type=\"hidden\" name=\"smnums[$dirnum]\" value=\"$smnums\" />",
					$smilies,
					'',
					''

				));
				$dirnum++;
			}
		}

		if(!$dirnum) {
			showtablerow('', array('', 'colspan="8"'), array(
				cplang('add_new'),
				cplang('smiliesupload_tips')
			));
		}

		showsubmit('smiliessubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_GET['namenew'])) {
			foreach($_GET['namenew'] as $id => $val) {
				$_GET['availablenew'][$id] = $_GET['availablenew'][$id] && $_GET['smiliesnum'][$id] > 0 ? 1 : 0;
				C::t('forum_imagetype')->update($id, array(
				    'available' => $_GET['availablenew'][$id],
				    'name' => dhtmlspecialchars(trim($val)),
				    'displayorder' => $_GET['displayordernew'][$id]
				));
			}
		}

		if($_GET['delete']) {
			if(C::t('common_smiley')->count_by_type_typeid('smiley', $_GET['delete'])) {
				cpmsg('smilies_delete_invalid', '', 'error');
			}
			C::t('forum_imagetype')->delete($_GET['delete']);
		}

		if(is_array($_GET['newname'])) {
			foreach($_GET['newname'] as $key => $val) {
				$val = trim($val);
				if($val) {
					$smurl = './static/image/smiley/'.$_GET['newdirectory'][$key];
					$smdir = DISCUZ_ROOT.$smurl;
					if(!is_dir($smdir)) {
						cpmsg('smilies_directory_invalid', '', 'error', array('smurl' => $smurl));
					}
					$newavailable[$key] = $_GET['newavailable'][$key] && $smnums[$key] > 0 ? 1 : 0;
					$data = array(
						'available' => $_GET['newavailable'][$key],
						'name' => dhtmlspecialchars($val),
						'type' => 'smiley',
						'displayorder' => $_GET['newdisplayorder'][$key],
						'directory' => $_GET['newdirectory'][$key],
					);
					$newSmileId = C::t('forum_imagetype')->insert($data, true);

					$smilies = update_smiles($smdir, $newSmileId, $imgextarray);
					if($smilies['smilies']) {
						addsmilies($newSmileId, $smilies['smilies']);
						updatecache(array('smilies', 'smileycodes', 'smilies_js'));
					}
				}
			}
		}

		updatecache(array('smileytypes', 'smilies', 'smileycodes', 'smilies_js'));
		cpmsg('smilies_edit_succeed', 'action=smilies', 'succeed');

	}

} elseif($operation == 'edit' && $id) {

	$type = C::t('forum_imagetype')->fetch($id);
	$smurl = './static/image/smiley/'.$type['directory'];
	$smdir = DISCUZ_ROOT.$smurl;
	if(!is_dir($smdir)) {
		cpmsg('smilies_directory_invalid', '', 'error', array('smurl' => $smurl));
	}
	$fastsmiley = C::t('common_setting')->fetch('fastsmiley', true);

	if(!$do) {

		if(!submitcheck('editsubmit')) {

			$smiliesperpage = 100;
			$start_limit = ($page - 1) * $smiliesperpage;

			$num = C::t('common_smiley')->count_by_type_typeid('smiley', $id);
			$multipage = multi($num, $smiliesperpage, $page, ADMINSCRIPT.'?action=smilies&operation=edit&id='.$id);

			$smileynum = 1;
			$smilies = '';
			foreach(C::t('common_smiley')->fetch_all_by_typeid_type($id, 'smiley', $start_limit, $smiliesperpage) as $smiley) {
				$smilies .= showtablerow('', array('class="td25"', 'class="td28 td24"', 'class="td25"', 'class="td23"', 'class="td23"', 'class="td24"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\">",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\">",
					"<input class=\"checkbox\" type=\"checkbox\" name=\"fast[]\" ".(in_array($smiley['id'], $fastsmiley[$id]) ? 'checked="checked"' : '')." value=\"$smiley[id]\">",
					"<img src=\"$smurl/$smiley[url]\" border=\"0\" onload=\"if(this.height>30) {this.resized=true; this.height=30;}\" onmouseover=\"if(this.resized) this.style.cursor='pointer';\" onclick=\"if(!this.resized) {return false;} else {window.open(this.src);}\">",
					$smiley['id'],
					"<input type=\"text\" class=\"txt\" size=\"25\" name=\"code[$smiley[id]]\" value=\"".dhtmlspecialchars($smiley['code'])."\" id=\"code_$smileynum\" smileyid=\"$smiley[id]\" />",
					"<input type=\"hidden\" value=\"$smiley[url]\" id=\"url_$smileynum\">$smiley[url]"
				), TRUE);
				$imgfilter[] = $smiley[url];
				$smileynum ++;
			}

			echo <<<EOT
<script type="text/JavaScript">
	function addsmileycodes(smiliesnum, pre) {
		smiliesnum = parseInt(smiliesnum);
		if(smiliesnum > 1) {
			for(var i = 1; i < smiliesnum; i++) {
				var prefix = trim($(pre + 'prefix').value);
				var suffix = trim($(pre + 'suffix').value);
				var page = parseInt('$page');
				var middle = $(pre + 'middle').value == 1 ? $(pre + 'url_' + i).value.substr(0,$(pre + 'url_' + i).value.lastIndexOf('.')) : ($(pre + 'middle').value == 2 ? i + page * 10 : $(pre + 'code_'+ i).attributes['smileyid'].nodeValue);
				if(!prefix || prefix == '$lang[smilies_prefix]' || !suffix || suffix == '$lang[smilies_suffix]') {
					alert('$lang[smilies_prefix_tips]');
					return;
				}
				suffix = !suffix || suffix == '$lang[smilies_suffix]' ? '' : suffix;
				$(pre + 'code_' + i).value = prefix + middle + suffix;
			}
		}
	}
	function autoaddsmileycodes(smiliesnum) {
		smiliesnum = parseInt(smiliesnum);
		if(smiliesnum > 1) {
			for(var i = 1; i < smiliesnum; i++) {
				$('code_' + i).value = '{:' + '$id' + '_' + $('code_'+ i).attributes['smileyid'].nodeValue + ':}';
			}
		}

	}
	function clearinput(obj, defaultval) {
		if(obj.value == defaultval) {
			obj.value = '';
		}
	}
</script>
EOT;

			shownav('style', 'nav_smilies');
			showsubmenu(cplang('smilies_edit').' - '.$type['name'], array(
				array('smilies_type', 'smilies', 0),
				array('admin', "smilies&operation=edit&id=$id", !$do),
				array('add', "smilies&operation=edit&do=add&id=$id", $do == 'add')
			));
			showformheader("smilies&operation=edit&id=$id");
			showhiddenfields(array('page' => $_GET['page']));
			showtableheader('', 'nobottom');
			showsubtitle(array('', 'display_order', 'smilies_fast', 'smilies_edit_image', 'smilies_id', 'smilies_edit_code', 'smilies_edit_filename'));
			echo $smilies;
			showtablerow('', array('', 'colspan="5"'), array(
				'',
				$lang['smilies_edit_add_code'].' <input type="text" class="txt" style="margin-right:0;width:40px;" size="2" value="{:" title="'.$lang['smilies_prefix'].'" id="prefix" onclick="clearinput(this, \''.$lang['smilies_prefix'].'\')" /> + <select id="middle"><option value="1">'.$lang['smilies_edit_order_file'].'</option><option value="2">'.$lang['smilies_edit_order_radom'].'</option><option value="3">'.$lang['smilies_id'].'</option></select> + <input type="text" class="txt" style="margin-right:0;width:40px;" size="2" value=":}" title="'.$lang['smilies_suffix'].'" id="suffix" onclick="clearinput(this, \''.$lang['smilies_suffix'].'\')" /> <input type="button" class="btn" onclick="addsmileycodes(\''.$smileynum.'\', \'\');" value="'.$lang['apply'].'" /> &nbsp;&nbsp; <input type="button" class="btn" onclick="autoaddsmileycodes(\''.$smileynum.'\');" value="'.$lang['smilies_edit_addcode_auto'].'" />'
			));
			showsubmit('editsubmit', 'submit', 'del', '', $multipage);
			showtablefooter();
			showformfooter();

		} else {

			if($_GET['delete']) {
				C::t('common_smiley')->delete($_GET['delete']);
			}

			$unsfast = array();
			if(is_array($_GET['displayorder'])) {
				foreach($_GET['displayorder'] as $key => $val) {
					if(!in_array($key, $_GET['fast'])) {
						$unsfast[] = $key;
					}
					$_GET['displayorder'][$key] = intval($_GET['displayorder'][$key]);
					$_GET['code'][$key] = trim($_GET['code'][$key]);
					$data = array('displayorder' => $_GET['displayorder'][$key]);
					if(!empty($_GET['code'][$key])) {
						$data['code'] = $_GET['code'][$key];
					}
					C::t('common_smiley')->update($key, $data);
				}
			}

			$fastsmiley[$id] = array_diff(array_unique(array_merge((array)$fastsmiley[$id], (array)$_GET['fast'])), $unsfast);
			C::t('common_setting')->update('fastsmiley', $fastsmiley);
			updatecache(array('smilies', 'smileycodes', 'smilies_js'));
			cpmsg('smilies_edit_succeed', "action=smilies&operation=edit&id=$id&page=$_GET[page]", 'succeed');

		}

	} elseif($do == 'add') {

		if(!submitcheck('editsubmit')) {

			shownav('style', 'nav_smilies');
			showsubmenu(cplang('smilies_edit').' - '.$type[name], array(
				array('smilies_type', 'smilies', 0),
				array('admin', "smilies&operation=edit&id=$id", !$do),
				array('add', "smilies&operation=edit&do=add&id=$id", $do == 'add')
			));
			showtips('smilies_tips');
			showtagheader('div', 'addsmilies', TRUE);
			showtableheader('smilies_add', 'notop fixpadding');
			showtablerow('', '', "<span class=\"bold marginright\">$lang[smilies_type]:</span>$type[name]");
			showtablerow('', '', "<span class=\"bold marginright\">$lang[dir]:</span>$smurl $lang[smilies_add_search]");
			showtablerow('', '', '<input type="button" class="btn" value="'.$lang['search'].'" onclick="ajaxget(\''.ADMINSCRIPT.'?action=smilies&operation=edit&do=add&id='.$id.'&search=yes\', \'addsmilies\', \'addsmilies\', \'auto\');doane(event);">');
			showtablefooter();
			showtagfooter('div');
			if($_GET['search']) {

				$newid = 1;
				$newimages = '';
				$imgfilter =  array();
				foreach(C::t('common_smiley')->fetch_all_by_typeid_type($id, 'smiley') as $smiley) {
					$imgfilter[] = $img[url];
				}
				$smiliesdir = dir($smdir);
				while($entry = $smiliesdir->read()) {
					if(in_array(strtolower(fileext($entry)), $imgextarray) && !in_array($entry, $imgfilter) && preg_match("/^[\w\-\.\[\]\(\)\<\> &]+$/", substr($entry, 0, strrpos($entry, '.'))) && strlen($entry) < 30 && is_file($smdir.'/'.$entry)) {
						$newimages .= showtablerow('', array('class="td25"', 'class="td28 td24"', 'class="td23"'), array(
							"<input class=\"checkbox\" type=\"checkbox\" name=\"smilies[$newid][available]\" value=\"1\" checked=\"checked\">",
							"<input type=\"text\" class=\"txt\" size=\"2\" name=\"smilies[$newid][displayorder]\" value=\"0\">",
							"<img src=\"$smurl/$entry\" border=\"0\" onload=\"if(this.height>30) {this.resized=true; this.height=30;}\" onmouseover=\"if(this.resized) this.style.cursor='pointer';\" onclick=\"if(!this.resized) {return false;} else {window.open(this.src);}\">",
							"<input type=\"hidden\" size=\"25\" name=\"smilies[$newid][url]\" value=\"$entry\" id=\"addurl_$newid\">$entry"
						), TRUE);
						$newid ++;
					}
				}
				$smiliesdir->close();

				ajaxshowheader();

				if($newimages) {

					showformheader("smilies&operation=edit&do=add&id=$id");
					showtableheader('smilies_add', 'notop fixpadding');
					showsubtitle(array('', 'display_order', 'smilies_edit_image', 'smilies_edit_filename'));
					echo $newimages;
					showtablerow('', array('class="td25"', 'colspan="3"'), array(
						'<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'available\')" class="checkbox" checked="checked">'.$lang['enable'],
						'<input type="submit" class="btn" name="editsubmit" value="'.$lang['submit'].'"> &nbsp; <input type="button" class="btn" value="'.$lang['research'].'" onclick="ajaxget(\''.ADMINSCRIPT.'?action=smilies&operation=edit&do=add&id='.$id.'&search=yes\', \'addsmilies\', \'addsmilies\', \'auto\');doane(event);">'
					));
					showtablefooter();
					showformfooter();

				} else {

					showtableheader('smilies_add', 'notop');
					showtablerow('', 'class="lineheight"', cplang('smilies_edit_add_tips', array('smurl' => $smurl)));
					showtablerow('', '', '<input type="button" class="btn" value="'.$lang['research'].'" onclick="ajaxget(\''.ADMINSCRIPT.'?action=smilies&operation=edit&do=add&id='.$id.'&search=yes\', \'addsmilies\', \'addsmilies\', \'auto\');doane(event);">');
					showtablefooter();

				}

				ajaxshowfooter();
			}

		} else {

			if(is_array($_GET['smilies'])) {
				addsmilies($id, $_GET['smilies']);
			}

			updatecache(array('smilies', 'smileycodes', 'smilies_js'));
			cpmsg('smilies_edit_succeed', "action=smilies&operation=edit&id=$id", 'succeed');
		}
	}

} elseif($operation == 'update' && $id) {

	if(!($smtype = C::t('forum_imagetype')->fetch($id))) {
		cpmsg('smilies_type_nonexistence', '', 'error');
	} else {
		$smurl = './static/image/smiley/'.$smtype['directory'];
		$smdir = DISCUZ_ROOT.$smurl;
		if(!is_dir($smdir)) {
			cpmsg('smilies_directory_invalid', '', 'error', array('smurl' => $smurl));
		}
	}

	$smilies = update_smiles($smdir, $id, $imgextarray);

	if($smilies['smilies']) {
		addsmilies($id, $smilies['smilies']);
		updatecache(array('smilies', 'smileycodes', 'smilies_js'));
		cpmsg('smilies_update_succeed', "action=smilies", 'succeed', array('smurl' => $smurl, 'num' => $smilies['num'], 'typename' => $smtype['name']));
	} else {
		cpmsg('smilies_update_error', '', 'error', array('smurl' => $smurl));
	}

} elseif($operation == 'import') {

	if(!submitcheck('importsubmit')) {

		shownav('style', 'smilies_edit');
		showsubmenu('nav_smilies', array(
			array('smilies_type', 'smilies', 0),
			array('smilies_import', 'smilies&operation=import', 1),
		));
		showtips('smilies_tips');
		showformheader('smilies&operation=import', 'enctype');
		showtableheader('smilies_import');
		showimportdata();
		showsubmit('importsubmit');
		showtablefooter();
		showformfooter();

	} else {

		require_once libfile('function/importdata');
		$renamed = import_smilies();
		if($renamed) {
			cpmsg('smilies_import_succeed_renamed', 'action=smilies', 'succeed');
		} else {
			cpmsg('smilies_import_succeed', 'action=smilies', 'succeed');
		}

	}

}

function addsmilies($typeid, $smilies = array()) {
	if(is_array($smilies)) {
		$ids = array();
		foreach($smilies as $smiley) {
			if($smiley['available']) {
				$data = array(
					'type' => 'smiley',
					'displayorder' => $smiley['displayorder'],
					'typeid' => $typeid,
					'code' => '',
					'url' => $smiley['url'],
				);
				$ids[] = C::t('common_smiley')->insert($data, true);
			}
		}
		if($ids) {
			C::t('common_smiley')->update_code_by_id($ids);
		}
	}
}
function update_smiles($smdir, $id, &$imgextarray) {
	$num = 0;
	$smilies = $imgfilter =  array();
	foreach(C::t('common_smiley')->fetch_all_by_typeid_type($id, 'smiley') as $img) {
		$imgfilter[] = $img[url];
	}
	$smiliesdir = dir($smdir);
	while($entry = $smiliesdir->read()) {
		if(in_array(strtolower(fileext($entry)), $imgextarray) && !in_array($entry, $imgfilter) && preg_match("/^[\w\-\.\[\]\(\)\<\> &]+$/", substr($entry, 0, strrpos($entry, '.'))) && strlen($entry) < 30 && is_file($smdir.'/'.$entry)) {
			$smilies[] = array('available' => 1, 'displayorder' => 0, 'url' => $entry);
			$num++;
		}
	}
	$smiliesdir->close();

	return array('smilies'=>$smilies, 'num'=>$num);
}
?>