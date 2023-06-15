<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_nav.php 31560 2012-09-10 03:47:45Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation) {
	$operation = 'headernav';
}

$navs = array('headernav', 'topnav', 'footernav', 'mynav', 'spacenav');
$navdata = array();
foreach($navs as $nav) {
	$navdata[] = array('nav_nav_'.$nav, 'nav&operation='.$nav, $nav == $operation);
}

if($operation == 'headernav') {

	if(!$do) {

		if(!submitcheck('submit')) {

			shownav('style', 'nav_setting_customnav');
			showsubmenu('nav_setting_customnav', $navdata);

			showformheader('nav&operation=headernav');
			showtableheader();
			showsubtitle(array('', 'display_order', 'name', 'misc_customnav_subtype', 'url', 'type', 'setindex', 'available', ''));
			showtagheader('tbody', '', true);

			$navlist = $subnavlist = $pluginsubnav = array();
			foreach(C::t('common_nav')->fetch_all_by_navtype(0) as $nav) {
				if($nav['parentid']) {
					$subnavlist[$nav['parentid']][] = $nav;
				} else {
					$navlist[$nav['id']] = $nav;
				}
			}
			foreach(C::t('common_plugin')->fetch_all_data() as $plugin) {
				if($plugin['available']) {
					$plugin['modules'] = dunserialize($plugin['modules']);
					if(is_array($plugin['modules'])) {
						unset($plugin['modules']['extra']);
						foreach($plugin['modules'] as $k => $module) {
							if(isset($module['name'])) {
								switch($module['type']) {
									case 5:
										$module['url'] = $module['url'] ? $module['url'] : 'plugin.php?id='.$plugin['identifier'].':'.$module['name'];
										list($module['menu'], $module['title']) = explode('/', $module['menu']);
										$pluginsubnav[] = array('key' => $k, 'id' => $plugin['pluginid'], 'displayorder' => $module['displayorder'], 'menu' => $module['menu'], 'title' => $module['title'], 'url' => $module['url']);
										break;
								}
							}
						}
					}
				}
			}
			foreach($navlist as $nav) {
				if($nav['available'] < 0) {
					continue;
				}
				$navsubtype = array();
				$navsubtype[$nav['subtype']] = 'selected="selected"';
				$readonly = $nav['type'] == '4' ? ' readonly="readonly"' : '';
				showtablerow('', array('class="td25"', 'class="td25"', '', '', '',''), array(
					($subnavlist[$nav['id']] || $nav['identifier'] == 6 && $nav['type'] == 0 && count($pluginsubnav) ? '<a href="javascript:;" class="right" onclick="toggle_group(\'subnav_'.$nav['id'].'\', this)">[+]</a>' : '').(in_array($nav['type'], array('2', '1')) ? "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$nav[id]\">" : '<input type="checkbox" class="checkbox" value="" disabled="disabled" />'),
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$nav[id]]\" value=\"$nav[displayorder]\">",
					"<div><input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$nav[id]]\" value=\"".dhtmlspecialchars($nav['name'])."\"$readonly>".
						($nav['identifier'] == 6 && $nav['type'] == 0 ? '' : "<a href=\"###\" onclick=\"addrowdirect=1;addrow(this, 1, $nav[id])\" class=\"addchildboard\">$lang[misc_customnav_add_submenu]</a></div>"),
					$nav['identifier'] == 6 && $nav['nav'] == 0 ? $lang['misc_customnav_subtype_menu'] : "<select name=\"subtypenew[$nav[id]]\"><option value=\"0\" $navsubtype[0]>$lang[misc_customnav_subtype_menu]</option><option value=\"1\" $navsubtype[1]>$lang[misc_customnav_subtype_sub]</option></select>",
					$nav['type'] == '0' || $nav['type'] == '4' || $nav['type'] == '5' ? "<span title='{$nav['url']}'>".$nav['url'].'<span>' : "<input type=\"text\" class=\"txt\" size=\"15\" name=\"urlnew[$nav[id]]\" value=\"".dhtmlspecialchars($nav['url'])."\">",
					cplang($nav['type'] == '0' ? 'inbuilt' : ($nav['type'] == '3' ? 'nav_plugin' : ($nav['type'] == '4' ? 'channel' : ($nav['type'] == '5' ? 'forum' : 'custom')))),
					$nav['url'] != '#' ? "<input name=\"defaultindex\" class=\"radio\" type=\"radio\" value=\"$nav[url]\"".($_G['setting']['defaultindex'] == $nav['url'] ? ' checked="checked"' : '')." />" : '',
					"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$nav[id]]\" value=\"1\" ".($nav['available'] > 0 ? 'checked' : '').">",
					"<a href=\"".ADMINSCRIPT."?action=nav&operation=headernav&do=edit&id=$nav[id]\" class=\"act\">$lang[edit]</a>"
				));
				if($nav['identifier'] == 6 && $nav['type'] == 0) {
					showtagheader('tbody', 'subnav_'.$nav['id'], false);
					$subnavnum = count($pluginsubnav);
					foreach($pluginsubnav as $row) {
						$subnavnum--;
						showtablerow('', array('class="td25"', 'class="td25"', '', ''), array(
							'',
							'<input type="text" class="txt" size="2" name="plugindisplayordernew['.$row['id'].']['.$row['key'].']" value="'.intval($row['displayorder']).'" />',
							'<div class="'.($subnavnum ? 'board' : 'lastboard').'"><input type="text" class="txt" size="15" name="pluginnamenew['.$row['id'].']['.$row['key'].']" value="'.dhtmlspecialchars($row['menu']).'" /></div>',
							'<input type="hidden" size="15" name="plugintitlenew['.$row['id'].']['.$row['key'].']" value="'.dhtmlspecialchars($row['title']).'" />',
							$row['url'],
							cplang('nav_plugin'),
							'',
							'<input class="checkbox" type="checkbox" checked disabled />',
							'<a href="'.ADMINSCRIPT.'?action=plugins&operation=edit&pluginid='.$row['id'].'&anchor=modules" class="act" target="_blank">'.$lang['edit'].'</a>',
						));
					}
					showtagfooter('tbody');
				}
				if(!empty($subnavlist[$nav['id']])) {
					showtagheader('tbody', 'subnav_'.$nav['id'], false);
					$subnavnum = count($subnavlist[$nav['id']]);
					foreach($subnavlist[$nav['id']] as $sub) {
						$readonly = $sub['type'] == '4' ? ' readonly="readonly"' : '';
						$subnavnum--;
						showtablerow('', array('class="td25"', 'class="td25"', '', ''), array(
							$sub['type'] == '0' || $sub['type'] == '4' ? '' : "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$sub[id]\">",
							"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$sub[id]]\" value=\"$sub[displayorder]\">",
							"<div class=\"".($subnavnum ? 'board' : 'lastboard')."\"><input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$sub[id]]\" value=\"".dhtmlspecialchars($sub['name'])."\"$readonly></div>",
							'',
							$sub['type'] == '0' || $sub['type'] == '4' ? "<span title='{$sub['url']}'>".$sub['url'].'</span>' : "<input type=\"text\" class=\"txt\" size=\"15\" name=\"urlnew[$sub[id]]\" value=\"".dhtmlspecialchars($sub['url'])."\">",
							cplang($sub['type'] == '0' ? 'inbuilt' : ($sub['type'] == '3' ? 'nav_plugin' : ($sub['type'] == '4' ? 'channel' : 'custom'))),
							$sub['url'] != '#' ? "<input name=\"defaultindex\" class=\"radio\" type=\"radio\" value=\"$sub[url]\"".($_G['setting']['defaultindex'] == $sub['url'] ? ' checked="checked"' : '')." />" : '',
							"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$sub[id]]\" value=\"1\" ".($sub['available'] ? 'checked' : '').">",
							"<a href=\"".ADMINSCRIPT."?action=nav&operation=headernav&do=edit&id=$sub[id]\" class=\"act\">$lang[edit]</a>"
						));
					}
					showtagfooter('tbody');
				}
			}
			showtagfooter('tbody');
			echo '<tr><td colspan="1"></td><td colspan="8"><div><a href="###" onclick="addrow(this, 0, 0)" class="addtr">'.$lang['misc_customnav_add_menu'].'</a></div></td></tr>';
			showsubmit('submit', 'submit', 'del');
			showtablefooter();
			showformfooter();

			loaducenter();
			$ucapparray = uc_app_ls();

			$applist = '';
			if(count($ucapparray) > 1) {
				$applist = $lang['misc_customnav_add_ucenter'].'<select name="applist" onchange="app(this)"><option value=""></option>';
				foreach($ucapparray as $app) {
					if($app['appid'] != UC_APPID) {
						$applist .= "<option value=\"$app[url]\">$app[name]</option>";
					}
				}
				$applist .= '</select>';
			}
			$applist = str_replace("'", "\'", $applist);

			echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1, '', 'td25'], [1,'<input name="newdisplayorder[]" value="" size="3" type="text" class="txt">', 'td25'], [1, '<input name="newname[]" value="" size="15" type="text" class="txt">'],[1,'<select name="newsubtype[]"><option value="0">$lang[misc_customnav_subtype_menu]</option><option value="1">$lang[misc_customnav_subtype_sub]</option></select>'],[5, '<input name="newurl[]" value="" size="15" type="text" class="txt"> $applist <input type="hidden" name="newparentid[]" value="0" />']],
		[[1, '', 'td25'], [1,'<input name="newdisplayorder[]" value="" size="3" type="text" class="txt">', 'td25'], [1, '<div class=\"board\"><input name="newname[]" value="" size="15" type="text" class="txt"></div>'], [1,'',''], [5, '<input name="newurl[]" value="" size="15" type="text" class="txt"> $applist <input type="hidden" name="newparentid[]" value="{1}" />']]
	];
	function app(obj) {
		var inputs = obj.parentNode.parentNode.getElementsByTagName('input');
		for(var i = 0; i < inputs.length; i++) {
			if(inputs[i].name == 'newname[]') {
				inputs[i].value = obj.options[obj.options.selectedIndex].innerHTML;
			} else if(inputs[i].name == 'newurl[]') {
				inputs[i].value = obj.value;
			}
		}
	}
</script>
EOT;

		} else {

			if($ids = dimplode($_GET['delete'])) {
				C::t('common_nav')->delete_by_navtype_id(0, $_GET['delete']);
				C::t('common_nav')->delete_by_navtype_parentid(0, $_GET['delete']);
			}

			if(is_array($_GET['namenew'])) {
				foreach($_GET['namenew'] as $id => $name) {


					$name = trim(dhtmlspecialchars($name));
					$urlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['urlnew'][$id]));
					$urladd = !empty($_GET['urlnew'][$id]) ? ", url='$urlnew'" : '';
					$availablenew[$id] = $name && (!isset($_GET['urlnew'][$id]) || $_GET['urlnew'][$id]) && $_GET['availablenew'][$id];
					$displayordernew[$id] = intval($_GET['displayordernew'][$id]);
					$data = array(
							'displayorder' => $displayordernew[$id],
							'available' => $availablenew[$id],
						);
					if(!empty($_GET['urlnew'][$id])) {
						$data['url'] = $urlnew;
					}
					if(!empty($name)) {
						$data['name'] = $name;
					}
					if(isset($_GET['subtypenew'][$id])) {
						$data['subtype'] = intval($_GET['subtypenew'][$id]);
					}
					C::t('common_nav')->update($id, $data);
				}
			}

			if(is_array($_GET['pluginnamenew']))  {
				foreach($_GET['pluginnamenew'] as $id => $rows) {
					$plugin = C::t('common_plugin')->fetch($id);
					$module = dunserialize($plugin['modules']);
					foreach($rows as $key => $menunew) {
						$module[$key]['menu'] = $menunew.($_GET['plugintitlenew'][$id][$key] ? '/'.$_GET['plugintitlenew'][$id][$key] : '');
						$module[$key]['displayorder'] = $_GET['plugindisplayordernew'][$id][$key];
					}
					C::t('common_plugin')->update($id, array('modules' => serialize($module)));
				}
			}

			if(is_array($_GET['newname'])) {
				foreach($_GET['newname'] as $k => $v) {
					$v = dhtmlspecialchars(trim($v));
					if(!empty($v)) {
						$newavailable = $v && $_GET['newurl'][$k];
						$newparentid[$k] = intval($_GET['newparentid'][$k]);
						$newdisplayorder[$k] = intval($_GET['newdisplayorder'][$k]);
						$subtype = isset($_GET['newsubtype'][$k]) ? intval($_GET['newsubtype'][$k]) : 0;
						$newurl[$k] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['newurl'][$k]));
						$data = array(
							'parentid' => $newparentid[$k],
							'name' => $v,
							'displayorder' => $newdisplayorder[$k],
							'subtype' => $subtype,
							'url' => $newurl[$k],
							'type' => 1,
							'available' => $newavailable,
							'navtype' => 0
						);
						C::t('common_nav')->insert($data);
					}
				}
			}

			if($_GET['defaultindex'] && $_GET['defaultindex'] != '#') {
				C::t('common_setting')->update('defaultindex', $_GET['defaultindex']);
			}

			updatecache('setting');
			cpmsg('nav_add_succeed', 'action=nav&operation=headernav', 'succeed');

		}

	} elseif($do == 'edit' && ($id = $_GET['id'])) {

		$nav = C::t('common_nav')->fetch_by_id_navtype($id, 0);
		if(!$nav) {
			cpmsg('nav_not_found', '', 'error');
		}

		if(!submitcheck('editsubmit')) {

			$string = sprintf('%02d', $nav['highlight']);

			shownav('style', 'nav_setting_customnav');
			showsubmenu('nav_setting_customnav', $navdata);
			$parentselect = array(array('0', cplang('misc_customnav_parent_top')));
			$parentname = '';
			foreach(C::t('common_nav')->fetch_all_by_navtype_parentid(0, 0) as $pnavs) {
				if($pnavs['id'] != $id && !($pnavs['identifier'] == 6 && $pnavs['type'] == 0)) {
					$parentselect[] = array($pnavs['id'], '&nbsp;&nbsp;'.$pnavs['name']);
					if($nav['parentid'] == $pnavs['id']) {
						$parentname = ' - '.$pnavs['name'];
					}
				}
			}

			if($nav['logo']) {
				$navlogo = str_replace('{STATICURL}', STATICURL, $nav['logo']);
				if(!preg_match("/^".preg_quote(STATICURL, '/')."/i", $navlogo) && !(($valueparse = parse_url($navlogo)) && isset($valueparse['host']))) {
					$navlogo = $_G['setting']['attachurl'].'common/'.$nav['logo'].'?'.random(6);
				}
				$logohtml = '<br /><label><input type="checkbox" class="checkbox" name="deletelogo" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$navlogo.'" />';
			}

			showformheader("nav&operation=headernav&do=edit&id=$id", 'enctype');
			showtableheader();
			showtitle(cplang('nav_nav_headernav').$parentname.' - '.$nav['name']);
			showsetting('misc_customnav_name', 'namenew', $nav['name'], 'text', $nav['type'] == '4');
			showsetting('misc_customnav_parent', array('parentidnew', $parentselect), $nav['parentid'], 'select');
			showsetting('misc_customnav_title', 'titlenew', $nav['title'], 'text');
			showsetting('misc_customnav_url', 'urlnew', $nav['url'], 'text', ($nav['type'] == '0' || $nav['type'] == '4'));
			showsetting('misc_customnav_style', array('stylenew', array(cplang('misc_customnav_style_underline'), cplang('misc_customnav_style_italic'), cplang('misc_customnav_style_bold'))), $string[0], 'binmcheckbox');
			showsetting('misc_customnav_style_color', array('colornew', array(
				array(0, '<span style="color: '.LINK.';">Default</span>'),
				array(1, '<span style="color: Red;">Red</span>'),
				array(2, '<span style="color: Orange;">Orange</span>'),
				array(3, '<span style="color: Yellow;">Yellow</span>'),
				array(4, '<span style="color: Green;">Green</span>'),
				array(5, '<span style="color: Cyan;">Cyan</span>'),
				array(6, '<span style="color: Blue;">Blue</span>'),
				array(7, '<span style="color: Purple;">Purple</span>'),
				array(8, '<span style="color: Gray;">Gray</span>'),
			)), $string[1], 'mradio2');
			showsetting('misc_customnav_url_open', array('targetnew', array(
				array(0, cplang('misc_customnav_url_open_default')),
				array(1, cplang('misc_customnav_url_open_blank'))
			), TRUE), $nav['target'], 'mradio');
			if(!$nav['parentid']) {
				showsetting('misc_customnav_logo', 'logonew', $nav['logo'], 'filetext', '', 0, cplang('misc_customnav_logo_comment').$logohtml);
				showsetting('misc_customnav_level', array('levelnew', array(
					array(0, cplang('nolimit')),
					array(1, cplang('member')),
					array(2, cplang('usergroups_system_3')),
					array(3, cplang('usergroups_system_1')),
				)), $nav['level'], 'select');
				showsetting('misc_customnav_subtype', array('subtypenew', array(
					array(0, cplang('misc_customnav_subtype_menu'), array('subcols' => 'none')),
					array(1, cplang('misc_customnav_subtype_sub'), array('subcols' => '')),
				)), $nav['subtype'], 'mradio');
				showtagheader('tbody', 'subcols', $nav['subtype'], 'sub');
				showsetting('misc_customnav_subcols', 'subcolsnew', $nav['subcols'], 'text');
				showtagfooter('tbody');
			}
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$namenew = trim(dhtmlspecialchars($_GET['namenew']));
			$titlenew = trim(dhtmlspecialchars($_GET['titlenew']));
			$urlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['urlnew']));
			$colornew = $_GET['colornew'];
			$parentidnew = $_GET['parentidnew'];
			$subtypenew = $_GET['subtypenew'];
			$stylebin = '';
			for($i = 3; $i >= 1; $i--) {
				$stylebin .= empty($_GET['stylenew'][$i]) ? '0' : '1';
			}
			$stylenew = bindec($stylebin);
			$targetnew = intval($_GET['targetnew']) ? 1 : 0;
			$levelnew = intval($_GET['levelnew']) && $_GET['levelnew'] > 0 && $_GET['levelnew'] < 4 ? intval($_GET['levelnew']) : 0 ;

			$urladd = $nav['type'] != '0' && $urlnew ? ", url='".$urlnew."'" : '';
			$subcols = ", subcols='".intval($_GET['subcolsnew'])."'";

			$logonew = addslashes($nav['logo']);
			if($_FILES['logonew']) {
				$upload = new discuz_upload();
				if($upload->init($_FILES['logonew'], 'common') && $upload->save()) {
					$logonew = $upload->attach['attachment'];
				}
			} else {
				$logonew = $_GET['logonew'];
			}
			if($_GET['deletelogo'] && $nav['logo']) {
				$valueparse = parse_url($nav['logo']);
				if(!isset($valueparse['host']) && !strexists($nav['logo'], '{STATICURL}')) {
					@unlink($_G['setting']['attachurl'].'common/'.$nav['logo']);
				}
				$logonew = '';
			}
			$logoadd = ", logo='$logonew'";

			$data = array(
					'name' => $namenew,
					'parentid' => $parentidnew,
					'title' => $titlenew,
					'highlight' => "$stylenew$colornew",
					'target' => $targetnew,
					'level' => $levelnew,
					'subtype' => $subtypenew,
					'subcols' => intval($_GET['subcolsnew']),
					'logo' => $logonew
				);
			if($nav['type'] != '0' && $urlnew) {
				$data['url'] = $urlnew;
			}
			C::t('common_nav')->update($id, $data);

			updatecache('setting');
			cpmsg('nav_add_succeed', 'action=nav&operation=headernav', 'succeed');

		}

	}

} elseif($operation == 'footernav') {

	if(!$do) {

		if(!submitcheck('submit')) {

			shownav('style', 'nav_setting_customnav');
			showsubmenu('nav_setting_customnav', $navdata);

			showformheader('nav&operation=footernav');
			showtableheader();
			showsubtitle(array('', 'display_order', 'name', 'url', 'type', 'available', ''));

			$navlist = array();
			foreach(C::t('common_nav')->fetch_all_by_navtype(1) as $nav) {
				$navlist[$nav['id']] = $nav;
			}

			foreach($navlist as $nav) {
				showtablerow('', array('class="td25"', 'class="td25"', '', ''), array(
					in_array($nav['type'], array('2', '1')) ? "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$nav[id]\">" : '<input type="checkbox" class="checkbox" value="" disabled="disabled" />',
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$nav[id]]\" value=\"$nav[displayorder]\">",
					"<div><input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$nav[id]]\" value=\"".dhtmlspecialchars($nav['name'])."\">",
					$nav['type'] == '0' ? $nav['url'] : "<input type=\"text\" class=\"txt\" size=\"15\" name=\"urlnew[$nav[id]]\" value=\"".dhtmlspecialchars($nav['url'])."\">",
					cplang($nav['type'] == '0' ? 'inbuilt' : ($nav['type'] == '3' ? 'nav_plugin' : ($nav['type'] == '4' ? 'channel' : 'custom'))),
					"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$nav[id]]\" value=\"1\" ".($nav['available'] ? 'checked' : '').">",
					"<a href=\"".ADMINSCRIPT."?action=nav&operation=footernav&do=edit&id=$nav[id]\" class=\"act\">$lang[edit]</a>"
				));
			}
			echo '<tr><td colspan="1"></td><td colspan="7"><div><a href="###" onclick="addrow(this, 0, 0)" class="addtr">'.$lang['nav_footernav_add'].'</a></div></td></tr>';
			showsubmit('submit', 'submit', 'del');
			showtablefooter();
			showformfooter();

			echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1, '', 'td25'], [1,'<input name="newdisplayorder[]" value="" size="3" type="text" class="txt">', 'td25'], [1, '<input name="newname[]" value="" size="15" type="text" class="txt">'], [4, '<input name="newurl[]" value="" size="15" type="text" class="txt">']],
	];
</script>
EOT;

		} else {

			if($ids = dimplode($_GET['delete'])) {
				C::t('common_nav')->delete_by_navtype_id(1, $_GET['delete']);
			}

			if(is_array($_GET['namenew'])) {
				foreach($_GET['namenew'] as $id => $name) {
					$name = trim(dhtmlspecialchars($name));
					$urlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['urlnew'][$id]));
					$availablenew[$id] = $name && (!isset($_GET['urlnew'][$id]) || $_GET['urlnew'][$id]) && $_GET['availablenew'][$id];
					$displayordernew[$id] = intval($_GET['displayordernew'][$id]);
					$data = array(
							'displayorder' => $displayordernew[$id],
							'available' => $availablenew[$id],
						);
					if(!empty($_GET['urlnew'][$id])) {
						$data['url'] = $urlnew;
					}
					if(!empty($name)) {
						$data['name'] = $name;
					}
					C::t('common_nav')->update($id, $data);
				}
			}

			if(is_array($_GET['newname'])) {
				foreach($_GET['newname'] as $k => $v) {
					$v = dhtmlspecialchars(trim($v));
					if(!empty($v)) {
						$newavailable = $v && $_GET['newurl'][$k];
						$newdisplayorder[$k] = intval($_GET['newdisplayorder'][$k]);
						$newurl[$k] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['newurl'][$k]));
						$data = array(
							'name' => $v,
							'displayorder' => $newdisplayorder[$k],
							'url' => $newurl[$k],
							'type' => 1,
							'available' => $newavailable,
							'navtype' => 1
						);
						C::t('common_nav')->insert($data);
					}
				}
			}

			updatecache('setting');
			cpmsg('nav_add_succeed', 'action=nav&operation=footernav', 'succeed');

		}

	} elseif($do == 'edit' && ($id = $_GET['id'])) {

		$nav = C::t('common_nav')->fetch_by_id_navtype($id, 1);
		if(!$nav) {
			cpmsg('nav_not_found', '', 'error');
		}

		if(!submitcheck('editsubmit')) {

			$string = sprintf('%02d', $nav['highlight']);

			shownav('style', 'nav_setting_customnav');
			showsubmenu('nav_setting_customnav', $navdata);

			showformheader("nav&operation=footernav&do=edit&id=$id");
			showtableheader();
			showtitle(cplang('nav_nav_footernav').' - '.$nav['name']);
			showsetting('misc_customnav_name', 'namenew', $nav['name'], 'text');
			showsetting('misc_customnav_title', 'titlenew', $nav['title'], 'text');
			showsetting('misc_customnav_url', 'urlnew', $nav['url'], 'text', $nav['type'] == '0');
			showsetting('misc_customnav_style', array('stylenew', array(cplang('misc_customnav_style_underline'), cplang('misc_customnav_style_italic'), cplang('misc_customnav_style_bold'))), $string[0], 'binmcheckbox');
			showsetting('misc_customnav_style_color', array('colornew', array(
				array(0, '<span style="color: '.LINK.';">Default</span>'),
				array(1, '<span style="color: Red;">Red</span>'),
				array(2, '<span style="color: Orange;">Orange</span>'),
				array(3, '<span style="color: Yellow;">Yellow</span>'),
				array(4, '<span style="color: Green;">Green</span>'),
				array(5, '<span style="color: Cyan;">Cyan</span>'),
				array(6, '<span style="color: Blue;">Blue</span>'),
				array(7, '<span style="color: Purple;">Purple</span>'),
				array(8, '<span style="color: Gray;">Gray</span>'),
			)), $string[1], 'mradio2');
			showsetting('misc_customnav_url_open', array('targetnew', array(
				array(0, cplang('misc_customnav_url_open_default')),
				array(1, cplang('misc_customnav_url_open_blank'))
			), TRUE), $nav['target'], 'mradio');
			if($nav['type']) {
				showsetting('misc_customnav_level', array('levelnew', array(
					array(0, cplang('nolimit')),
					array(1, cplang('member')),
					array(2, cplang('usergroups_system_3')),
					array(3, cplang('usergroups_system_1')),
				)), $nav['level'], 'select');
			}
			showtagfooter('tbody');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$namenew = trim(dhtmlspecialchars($_GET['namenew']));
			$titlenew = trim(dhtmlspecialchars($_GET['titlenew']));
			$urlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['urlnew']));
			$colornew = $_GET['colornew'];
			$stylebin = '';
			for($i = 3; $i >= 1; $i--) {
				$stylebin .= empty($_GET['stylenew'][$i]) ? '0' : '1';
			}
			$stylenew = bindec($stylebin);
			$targetnew = intval($_GET['targetnew']) ? 1 : 0;
			$levelnew = $nav['type'] ? (intval($_GET['levelnew']) && $_GET['levelnew'] > 0 && $_GET['levelnew'] < 4 ? intval($_GET['levelnew']) : 0) : 0;
			$urladd = $nav['type'] != '0' && $urlnew ? ", url='".$urlnew."'" : '';

			$data = array(
					'name' => $namenew,
					'title' => $titlenew,
					'highlight' => "$stylenew$colornew",
					'target' => $targetnew,
					'level' => $levelnew
				);
			if($nav['type'] != '0' && $urlnew) {
				$data['url'] = $urlnew;
			}
			C::t('common_nav')->update($id, $data);

			updatecache('setting');
			cpmsg('nav_add_succeed', 'action=nav&operation=footernav', 'succeed');

		}

	}

} elseif($operation == 'spacenav') {

	if(!$do) {

		if(!submitcheck('submit')) {

			shownav('style', 'nav_setting_customnav');

			showsubmenu('nav_setting_customnav', $navdata);
			showtips('nav_spacenav_tips');
			showformheader('nav&operation=spacenav');
			showtableheader();
			showsubtitle(array('', 'display_order', 'name', 'url', 'type', 'available', ''));

			$navlist = array();
			foreach(C::t('common_nav')->fetch_all_by_navtype(2) as $nav) {
				if($nav['available'] < 0) {
					continue;
				}
				$navlist[$nav['id']] = $nav;
			}

			foreach($navlist as $nav) {
				$navicon = str_replace('{STATICURL}', STATICURL, $nav['icon']);
				if(!preg_match("/^".preg_quote(STATICURL, '/')."/i", $navicon) && !(($valueparse = parse_url($navicon)) && isset($valueparse['host']))) {
					$navicon = $_G['setting']['attachurl'].'common/'.$nav['icon'].'?'.random(6);
				}
				showtablerow('', array('class="td25"', 'class="td25"', '', ''), array(
					in_array($nav['type'], array('2', '1')) ? "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$nav[id]\">" : '<input type="checkbox" class="checkbox" value="" disabled="disabled" />',
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$nav[id]]\" value=\"$nav[displayorder]\">",
					!in_array($nav['name'], array('{hr}')) ? ("<input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$nav[id]]\" value=\"".dhtmlspecialchars($nav['name'])."\">".
					($nav['icon'] ? '<img src="'.$navicon.'" width="16" height="16" class="vmiddle" />' : '')) : "<input type=\"hidden\" name=\"namenew[$nav[id]]\" value=\"$nav[name]\">".cplang('nav_spacenav_'.str_replace(array('{', '}'), '', $nav['name']), array('navname' => $_G['setting']['navs'][5]['navname'])),
					$nav['type'] == '0' || $nav['name'] == '{hr}' ? $nav['url'] : "<input type=\"text\" class=\"txt\" size=\"15\" name=\"urlnew[$nav[id]]\" value=\"".dhtmlspecialchars($nav['url'])."\">",
					cplang($nav['type'] == '0' ? 'inbuilt' : ($nav['type'] == '3' ? 'nav_plugin' : ($nav['type'] == '4' ? 'channel' : 'custom'))),
					"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$nav[id]]\" value=\"1\" ".($nav['available'] ? 'checked' : '').">",
					!in_array($nav['name'], array('{hr}')) ? "<a href=\"".ADMINSCRIPT."?action=nav&operation=spacenav&do=edit&id=$nav[id]\" class=\"act\">$lang[edit]</a>" : ''
				));
			}
			echo '<tr><td colspan="1"></td><td colspan="7"><div><a href="###" onclick="addrow(this, 0, 0)" class="addtr">'.$lang['nav_spacenav_add'].'</a> &nbsp; <a href="###" onclick="addrow(this, 1, 0)" class="addtr">'.$lang['nav_spacenav_add_hr'].'</a></div></td></tr>';
			showsubmit('submit', 'submit', 'del');
			showtablefooter();
			showformfooter();

			echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1, '', 'td25'], [1,'<input name="newdisplayorder[]" value="" size="3" type="text" class="txt">', 'td25'], [1, '<input name="newname[]" value="" size="15" type="text" class="txt">'], [4, '<input name="newurl[]" value="" size="15" type="text" class="txt">']],
		[[1, '', 'td25'], [1,'<input name="newdisplayorder[]" value="" size="3" type="text" class="txt">', 'td25'], [1, '<input name="newname[]" value="{hr}" type="hidden">$lang[nav_spacenav_hr]'], [4, '<input name="newurl[]" value="" type="hidden">']],
	];
</script>
EOT;

		} else {

			if($ids = dimplode($_GET['delete'])) {
				C::t('common_nav')->delete_by_navtype_id(2, $_GET['delete']);
			}

			if(is_array($_GET['namenew'])) {
				foreach($_GET['namenew'] as $id => $name) {
					$name = trim(dhtmlspecialchars($name));
					$urlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['urlnew'][$id]));
					$urladd = !empty($_GET['urlnew'][$id]) ? ", url='$urlnew'" : '';
					$availablenew[$id] = $name && (!isset($_GET['urlnew'][$id]) || $_GET['urlnew'][$id]) && $_GET['availablenew'][$id];
					$displayordernew[$id] = intval($_GET['displayordernew'][$id]);
					$nameadd = !empty($name) ? ", name='$name'" : '';
					$data = array(
							'displayorder' => $displayordernew[$id],
							'available' => $availablenew[$id]
						);
					if(!empty($_GET['urlnew'][$id])) {
						$data['url'] = $urlnew;
					}
					if(!empty($name)) {
						$data['name'] = $name;
					}
					C::t('common_nav')->update($id, $data);
				}
			}

			if(is_array($_GET['newname'])) {
				foreach($_GET['newname'] as $k => $v) {
					$v = dhtmlspecialchars(trim($v));
					if(!empty($v)) {
						$newavailable = $v && $_GET['newurl'][$k];
						$newdisplayorder[$k] = intval($_GET['newdisplayorder'][$k]);
						$newurl[$k] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['newurl'][$k]));
						$data = array(
							'name' => $v,
							'displayorder' => $newdisplayorder[$k],
							'url' => $newurl[$k],
							'type' => 1,
							'available' => $newavailable,
							'navtype' => 2
						);
						C::t('common_nav')->insert($data);
					}
				}
			}

			updatecache('setting');
			cpmsg('nav_add_succeed', 'action=nav&operation=spacenav', 'succeed');

		}

	} elseif($do == 'edit' && ($id = $_GET['id'])) {

		$nav = C::t('common_nav')->fetch_by_id_navtype($id, 2);
		if(!$nav) {
			cpmsg('nav_not_found', '', 'error');
		}

		if(!submitcheck('editsubmit')) {

			$nav['allowsubnew'] = 1;
			if(substr($nav['subname'], 0, 1) == "\t") {
				$nav['allowsubnew'] = 0;
				$nav['subname'] = substr($nav['subname'], 1);
			}
			if($nav['icon']) {
				$navicon = str_replace('{STATICURL}', STATICURL, $nav['icon']);
				if(!preg_match("/^".preg_quote(STATICURL, '/')."/i", $navicon) && !(($valueparse = parse_url($navicon)) && isset($valueparse['host']))) {
					$navicon = $_G['setting']['attachurl'].'common/'.$nav['icon'].'?'.random(6);
				}
				$naviconhtml = '<br /><label><input type="checkbox" class="checkbox" name="deleteicon" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$navicon.'" width="16" height="16" />';
			}
			shownav('style', 'nav_setting_customnav');
			showsubmenu('nav_setting_customnav', $navdata);

			showformheader("nav&operation=spacenav&do=edit&id=$id", 'enctype');
			showtableheader();
			showtitle(cplang('nav_nav_spacenav').' - '.$nav['name']);
			showsetting('misc_customnav_name', 'namenew', $nav['name'], 'text');
			showsetting('misc_customnav_title', 'titlenew', $nav['title'], 'text');
			showsetting('misc_customnav_url', 'urlnew', $nav['url'], 'text', $nav['type'] == '0');
			showsetting('misc_customnav_icon', 'iconnew', $nav['icon'], 'filetext', '', 0, cplang('misc_customnav_icon_comment').$naviconhtml);
			showsetting('misc_customnav_allowsub', 'allowsubnew', $nav['allowsubnew'], 'radio', '', 1);
			showsetting('misc_customnav_subname', 'subnamenew', $nav['subname'], 'text');
			showsetting('misc_customnav_suburl', 'suburlnew', $nav['suburl'], 'text', $nav['type'] == '0');
			showtagfooter('tbody');
			showsetting('misc_customnav_url_open', array('targetnew', array(
				array(0, cplang('misc_customnav_url_open_default')),
				array(1, cplang('misc_customnav_url_open_blank'))
			), TRUE), $nav['target'], 'mradio');
			showsetting('misc_customnav_level', array('levelnew', array(
				array(0, cplang('nolimit')),
				array(1, cplang('member')),
				array(2, cplang('usergroups_system_3')),
				array(3, cplang('usergroups_system_1')),
			)), $nav['level'], 'select');
			showtagfooter('tbody');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$namenew = trim(dhtmlspecialchars($_GET['namenew']));
			$titlenew = trim(dhtmlspecialchars($_GET['titlenew']));
			$subnamenew = trim(dhtmlspecialchars($_GET['subnamenew']));
			$urlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['urlnew']));
			$suburlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['suburlnew']));
			$targetnew = intval($_GET['targetnew']) ? 1 : 0;
			$levelnew = intval($_GET['levelnew']) && $_GET['levelnew'] > 0 && $_GET['levelnew'] < 4 ? intval($_GET['levelnew']) : 0 ;
			$urladd = $nav['type'] != '0' && $urlnew ? ", url='$urlnew'" : '';
			$urladd .= $nav['type'] != '0' && $suburlnew ? ", suburl='$suburlnew'" : '';

			if(empty($_GET['allowsubnew'])) {
				$subnamenew = "\t".$subnamenew;
			}
			$iconnew = addslashes($nav['icon']);
			if($_FILES['iconnew']) {
				$upload = new discuz_upload();
				if($upload->init($_FILES['iconnew'], 'common') && $upload->save()) {
					$iconnew = $upload->attach['attachment'];
				}
			} else {
				$iconnew = $_GET['iconnew'];
			}
			if($_GET['deleteicon'] && $nav['icon']) {
				$valueparse = parse_url($nav['icon']);
				if(!isset($valueparse['host']) && !strexists($nav['icon'], '{STATICURL}')) {
					@unlink($_G['setting']['attachurl'].'common/'.$nav['icon']);
				}
				$iconnew = '';
			}
			$iconadd = ", icon='$iconnew'";

			$data = array(
					'name' => $namenew,
					'subname' => $subnamenew,
					'title' => $titlenew,
					'target' => $targetnew,
					'level' => $levelnew,
					'icon' => $iconnew
				);
			if($nav['type'] != '0' && $urlnew) {
				$data['url'] = $urlnew;
			}
			if($nav['type'] != '0' && $suburlnew) {
				$data['suburl'] = $suburlnew;
			}
			C::t('common_nav')->update($id, $data);
			updatecache('setting');
			cpmsg('nav_add_succeed', 'action=nav&operation=spacenav', 'succeed');

		}

	}

} elseif($operation == 'mynav') {

	if(!$do) {

		if(!submitcheck('submit')) {

			shownav('style', 'nav_setting_customnav');
			showsubmenu('nav_setting_customnav', $navdata);

			showformheader('nav&operation=mynav');
			showtableheader();
			showsubtitle(array('', 'display_order', 'name', 'url', 'type', 'available', ''));

			$navlist = array();
			foreach(C::t('common_nav')->fetch_all_by_navtype(3) as $nav) {
				if($nav['available'] < 0) {
					continue;
				}
				$navlist[$nav['id']] = $nav;
			}

			foreach($navlist as $nav) {
				$navicon = str_replace('{STATICURL}', STATICURL, $nav['icon']);
				if(!preg_match("/^".preg_quote(STATICURL, '/')."/i", $navicon) && !(($valueparse = parse_url($navicon)) && isset($valueparse['host']))) {
					$navicon = $_G['setting']['attachurl'].'common/'.$nav['icon'].'?'.random(6);
				}
				showtablerow('', array('class="td25"', 'class="td25"', '', ''), array(
					in_array($nav['type'], array('2', '1')) ? "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$nav[id]\">" : '<input type="checkbox" class="checkbox" value="" disabled="disabled" />',
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$nav[id]]\" value=\"$nav[displayorder]\">",
					"<input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$nav[id]]\" value=\"".dhtmlspecialchars($nav['name'])."\">".
					($nav['icon'] ? '<img src="'.$navicon.'" width="40" height="40" class="vmiddle" />' : ''),
					$nav['type'] == '0' ? $nav['url'] : "<input type=\"text\" class=\"txt\" size=\"15\" name=\"urlnew[$nav[id]]\" value=\"".dhtmlspecialchars($nav['url'])."\">",
					cplang($nav['type'] == '0' ? 'inbuilt' : ($nav['type'] == '3' ? 'nav_plugin' : ($nav['type'] == '4' ? 'channel' : 'custom'))),
					"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$nav[id]]\" value=\"1\" ".($nav['available'] ? 'checked' : '').">",
					"<a href=\"".ADMINSCRIPT."?action=nav&operation=mynav&do=edit&id=$nav[id]\" class=\"act\">$lang[edit]</a>"
				));
			}
			echo '<tr><td colspan="1"></td><td colspan="7"><div><a href="###" onclick="addrow(this, 0, 0)" class="addtr">'.$lang['nav_mynav_add'].'</a></div></td></tr>';
			showsubmit('submit', 'submit', 'del');
			showtablefooter();
			showformfooter();

			echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1, '', 'td25'], [1,'<input name="newdisplayorder[]" value="" size="3" type="text" class="txt">', 'td25'], [1, '<input name="newname[]" value="" size="15" type="text" class="txt">'], [4, '<input name="newurl[]" value="" size="15" type="text" class="txt">']],
	];
</script>
EOT;

		} else {

			if($_GET['delete']) {
				C::t('common_nav')->delete_by_navtype_id(3, $_GET['delete']);
			}

			if(is_array($_GET['namenew'])) {
				foreach($_GET['namenew'] as $id => $name) {
					$name = trim(dhtmlspecialchars($name));
					$urlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['urlnew'][$id]));
					$urladd = !empty($_GET['urlnew'][$id]) ? ", url='$urlnew'" : '';
					$availablenew[$id] = $name && (!isset($_GET['urlnew'][$id]) || $_GET['urlnew'][$id]) && $_GET['availablenew'][$id];
					$displayordernew[$id] = intval($_GET['displayordernew'][$id]);
					$nameadd = !empty($name) ? ", name='$name'" : '';
					$data = array(
							'displayorder' => $displayordernew[$id],
							'available' => $availablenew[$id]
						);
					if(!empty($_GET['urlnew'][$id])) {
						$data['url'] = $urlnew;
					}
					if(!empty($name)) {
						$data['name'] = $name;
					}
					C::t('common_nav')->update($id, $data);
				}
			}

			if(is_array($_GET['newname'])) {
				foreach($_GET['newname'] as $k => $v) {
					$v = dhtmlspecialchars(trim($v));
					if(!empty($v)) {
						$newavailable = $v && $_GET['newurl'][$k];
						$newdisplayorder[$k] = intval($_GET['newdisplayorder'][$k]);
						$newurl[$k] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['newurl'][$k]));
						$data = array(
							'name' => $v,
							'displayorder' => $newdisplayorder[$k],
							'url' => $newurl[$k],
							'type' => 1,
							'available' => $newavailable,
							'navtype' => 3
						);
						C::t('common_nav')->insert($data);
					}
				}
			}

			updatecache('setting');
			cpmsg('nav_add_succeed', 'action=nav&operation=mynav', 'succeed');

		}

	} elseif($do == 'edit' && ($id = $_GET['id'])) {

		$nav = C::t('common_nav')->fetch_by_id_navtype($id, 3);
		if(!$nav) {
			cpmsg('nav_not_found', '', 'error');
		}

		if(!submitcheck('editsubmit')) {

			$nav['allowsubnew'] = 1;
			if(substr($nav['subname'], 0, 1) == "\t") {
				$nav['allowsubnew'] = 0;
				$nav['subname'] = substr($nav['subname'], 1);
			}
			if($nav['icon']) {
				$navicon = str_replace('{STATICURL}', STATICURL, $nav['icon']);
				if(!preg_match("/^".preg_quote(STATICURL, '/')."/i", $navicon) && !(($valueparse = parse_url($navicon)) && isset($valueparse['host']))) {
					$navicon = $_G['setting']['attachurl'].'common/'.$nav['icon'].'?'.random(6);
				}
				$naviconhtml = '<br /><label><input type="checkbox" class="checkbox" name="deleteicon" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$navicon.'" width="40" height="40" />';
			}
			shownav('style', 'nav_setting_customnav');
			showsubmenu('nav_setting_customnav', $navdata);

			showformheader("nav&operation=mynav&do=edit&id=$id", 'enctype');
			showtableheader();
			showtitle(cplang('nav_nav_mynav').' - '.$nav['name']);
			showsetting('misc_customnav_name', 'namenew', $nav['name'], 'text');
			showsetting('misc_customnav_title', 'titlenew', $nav['title'], 'text');
			showsetting('misc_customnav_url', 'urlnew', $nav['url'], 'text', $nav['type'] == '0');
			showsetting('misc_customnav_icon', 'iconnew', $nav['icon'], 'filetext', '', 0, cplang('misc_mynav_icon_comment').$naviconhtml);
			showsetting('misc_customnav_url_open', array('targetnew', array(
				array(0, cplang('misc_customnav_url_open_default')),
				array(1, cplang('misc_customnav_url_open_blank'))
			), TRUE), $nav['target'], 'mradio');
			showsetting('misc_customnav_level', array('levelnew', array(
				array(0, cplang('nolimit')),
				array(1, cplang('member')),
				array(2, cplang('usergroups_system_3')),
				array(3, cplang('usergroups_system_1')),
			)), $nav['level'], 'select');
			showtagfooter('tbody');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$namenew = trim(dhtmlspecialchars($_GET['namenew']));
			$titlenew = trim(dhtmlspecialchars($_GET['titlenew']));
			$urlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['urlnew']));
			$targetnew = intval($_GET['targetnew']) ? 1 : 0;
			$levelnew = intval($_GET['levelnew']) && $_GET['levelnew'] > 0 && $_GET['levelnew'] < 4 ? intval($_GET['levelnew']) : 0 ;
			$urladd = $nav['type'] != '0' && $urlnew ? ", url='$urlnew'" : '';

			$iconnew = addslashes($nav['icon']);
			if($_FILES['iconnew']) {
				$upload = new discuz_upload();
				if($upload->init($_FILES['iconnew'], 'common') && $upload->save()) {
					$iconnew = $upload->attach['attachment'];
				}
			} else {
				$iconnew = $_GET['iconnew'];
			}
			if($_GET['deleteicon'] && $nav['icon']) {
				$valueparse = parse_url($nav['icon']);
				if(!isset($valueparse['host']) && !strexists($nav['icon'], '{STATICURL}')) {
					@unlink($_G['setting']['attachurl'].'common/'.$nav['icon']);
				}
				$iconnew = '';
			}
			$iconadd = ", icon='$iconnew'";

			$data = array(
					'name' => $namenew,
					'title' => $titlenew,
					'target' => $targetnew,
					'level' => $levelnew,
					'icon' => $iconnew
				);
			if($nav['type'] != '0' && $urlnew) {
				$data['url'] = $urlnew;
			}
			C::t('common_nav')->update($id, $data);

			updatecache('setting');
			cpmsg('nav_add_succeed', 'action=nav&operation=mynav', 'succeed');

		}

	}

} elseif($operation == 'topnav') {

	if(!$do) {

		if(!submitcheck('submit')) {

			shownav('style', 'nav_setting_customnav');
			showsubmenu('nav_setting_customnav', $navdata);

			showformheader('nav&operation=topnav');
			showtableheader();
			showsubtitle(array('', 'display_order', 'name', 'setting_styles_global_topnavtype', 'url', 'type', 'available', ''));

			$navlist = array();
			foreach(C::t('common_nav')->fetch_all_by_navtype(4) as $nav) {
				$navlist[$nav['id']] = $nav;
			}

			foreach($navlist as $nav) {
				$navtype = array();
				$navtype[$nav['subtype']] = 'selected="selected"';
				showtablerow('', array('class="td25"', 'class="td25"', '', ''), array(
					in_array($nav['type'], array('2', '1')) ? "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$nav[id]\">" : '<input type="checkbox" class="checkbox" value="" disabled="disabled" />',
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$nav[id]]\" value=\"$nav[displayorder]\">",
					"<div><input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$nav[id]]\" value=\"".dhtmlspecialchars($nav['name'])."\">",
					"<select name=\"subtypenew[$nav[id]]\"><option value=\"0\" $navtype[0]>$lang[setting_styles_global_topnavtype_0]</option><option value=\"1\" $navtype[1]>$lang[setting_styles_global_topnavtype_1]</option></select>",
					$nav['type'] == '0' ? $nav['url'] : "<input type=\"text\" class=\"txt\" size=\"15\" name=\"urlnew[$nav[id]]\" value=\"".dhtmlspecialchars($nav['url'])."\">",
					cplang($nav['type'] == '0' ? 'inbuilt' : ($nav['type'] == '3' ? 'nav_plugin' : ($nav['type'] == '4' ? 'channel' : 'custom'))),
					"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$nav[id]]\" value=\"1\" ".($nav['available'] ? 'checked' : '').">",
					"<a href=\"".ADMINSCRIPT."?action=nav&operation=topnav&do=edit&id=$nav[id]\" class=\"act\">$lang[edit]</a>"
				));
			}
			echo '<tr><td colspan="1"></td><td colspan="7"><div><a href="###" onclick="addrow(this, 0, 0)" class="addtr">'.$lang['nav_topnav_add'].'</a></div></td></tr>';
			showsubmit('submit', 'submit', 'del');
			showtablefooter();
			showformfooter();

			echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1, '', 'td25'], [1,'<input name="newdisplayorder[]" value="" size="3" type="text" class="txt">', 'td25'], [1, '<input name="newname[]" value="" size="15" type="text" class="txt">'], [1, '<select name="newsubtype[]"><option value="0">$lang[setting_styles_global_topnavtype_0]</option><option value="1">$lang[setting_styles_global_topnavtype_1]</option></select>'], [4, '<input name="newurl[]" value="" size="15" type="text" class="txt">']],
	];
</script>
EOT;

		} else {

			if($_GET['delete']) {
				C::t('common_nav')->delete_by_navtype_id(4, $_GET['delete']);
			}

			if(is_array($_GET['namenew'])) {
				foreach($_GET['namenew'] as $id => $name) {
					$name = trim(dhtmlspecialchars($name));
					$urlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['urlnew'][$id]));
					$availablenew[$id] = $name && (!isset($_GET['urlnew'][$id]) || $_GET['urlnew'][$id]) && $_GET['availablenew'][$id];
					$displayordernew[$id] = intval($_GET['displayordernew'][$id]);
					$data = array(
							'displayorder' => $displayordernew[$id],
							'available' => $availablenew[$id]
						);
					if(!empty($_GET['urlnew'][$id])) {
						$data['url'] = $urlnew;
					}
					if(!empty($name)) {
						$data['name'] = $name;
					}
					if(isset($_GET['subtypenew'][$id])) {
						$data['subtype'] = intval($_GET['subtypenew'][$id]);
					}
					C::t('common_nav')->update($id, $data);
				}
			}

			if(is_array($_GET['newname'])) {
				foreach($_GET['newname'] as $k => $v) {
					$v = dhtmlspecialchars(trim($v));
					if(!empty($v)) {
						$newavailable = $v && $_GET['newurl'][$k];
						$newdisplayorder[$k] = intval($_GET['newdisplayorder'][$k]);
						$subtype = isset($_GET['newsubtype'][$k]) ? intval($_GET['newsubtype'][$k]) : 0;
						$newurl[$k] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['newurl'][$k]));
						$data = array(
							'name' => $v,
							'displayorder' => $newdisplayorder[$k],
							'subtype' => $subtype,
							'url' => $newurl[$k],
							'type' => 1,
							'available' => $newavailable,
							'navtype' => 4
						);
						C::t('common_nav')->insert($data);
					}
				}
			}

			updatecache('setting');
			cpmsg('nav_add_succeed', 'action=nav&operation=topnav', 'succeed');

		}

	} elseif($do == 'edit' && ($id = $_GET['id'])) {

		$nav = C::t('common_nav')->fetch_by_id_navtype($id, 4);
		if(!$nav) {
			cpmsg('nav_not_found', '', 'error');
		}

		if(!submitcheck('editsubmit')) {

			$string = sprintf('%02d', $nav['highlight']);

			shownav('style', 'nav_setting_customnav');
			showsubmenu('nav_setting_customnav', $navdata);

			showformheader("nav&operation=topnav&do=edit&id=$id");
			showtableheader();
			showtitle(cplang('nav_nav_topnav').' - '.$nav['name']);
			showsetting('misc_customnav_name', 'namenew', $nav['name'], 'text');
			showsetting('setting_styles_global_topnavtype', array('subtypenew', array(
				array(0, cplang('setting_styles_global_topnavtype_0')),
				array(1, cplang('setting_styles_global_topnavtype_1')),
			)), $nav['subtype'], 'select');
			showsetting('misc_customnav_title', 'titlenew', $nav['title'], 'text');
			showsetting('misc_customnav_url', 'urlnew', $nav['url'], 'text', $nav['type'] == '0');
			showsetting('misc_customnav_style', array('stylenew', array(cplang('misc_customnav_style_underline'), cplang('misc_customnav_style_italic'), cplang('misc_customnav_style_bold'))), $string[0], 'binmcheckbox');
			showsetting('misc_customnav_style_color', array('colornew', array(
				array(0, '<span style="color: '.LINK.';">Default</span>'),
				array(1, '<span style="color: Red;">Red</span>'),
				array(2, '<span style="color: Orange;">Orange</span>'),
				array(3, '<span style="color: Yellow;">Yellow</span>'),
				array(4, '<span style="color: Green;">Green</span>'),
				array(5, '<span style="color: Cyan;">Cyan</span>'),
				array(6, '<span style="color: Blue;">Blue</span>'),
				array(7, '<span style="color: Purple;">Purple</span>'),
				array(8, '<span style="color: Gray;">Gray</span>'),
			)), $string[1], 'mradio2');
			showsetting('misc_customnav_url_open', array('targetnew', array(
				array(0, cplang('misc_customnav_url_open_default')),
				array(1, cplang('misc_customnav_url_open_blank'))
			), TRUE), $nav['target'], 'mradio');
			if($nav['type']) {
				showsetting('misc_customnav_level', array('levelnew', array(
					array(0, cplang('nolimit')),
					array(1, cplang('member')),
					array(2, cplang('usergroups_system_3')),
					array(3, cplang('usergroups_system_1')),
				)), $nav['level'], 'select');
			}
			showtagfooter('tbody');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$namenew = trim(dhtmlspecialchars($_GET['namenew']));
			$titlenew = trim(dhtmlspecialchars($_GET['titlenew']));
			$urlnew = str_replace(array('&amp;'), array('&'), dhtmlspecialchars($_GET['urlnew']));
			$colornew = $_GET['colornew'];
			$subtypenew = $_GET['subtypenew'];
			$stylebin = '';
			for($i = 3; $i >= 1; $i--) {
				$stylebin .= empty($_GET['stylenew'][$i]) ? '0' : '1';
			}
			$stylenew = bindec($stylebin);
			$targetnew = intval($_GET['targetnew']) ? 1 : 0;
			$levelnew = $nav['type'] ? (intval($_GET['levelnew']) && $_GET['levelnew'] > 0 && $_GET['levelnew'] < 4 ? intval($_GET['levelnew']) : 0) : 0;
			$urladd = $nav['type'] != '0' && $urlnew ? ", url='".$urlnew."'" : '';

			$data = array(
					'name' => $namenew,
					'title' => $titlenew,
					'highlight' => "$stylenew$colornew",
					'target' => $targetnew,
					'level' => $levelnew,
					'subtype' => $subtypenew
				);
			if($nav['type'] != '0' && $urlnew) {
				$data['url'] = $urlnew;
			}
			C::t('common_nav')->update($id, $data);
			updatecache('setting');
			cpmsg('nav_add_succeed', 'action=nav&operation=topnav', 'succeed');

		}

	}

}

?>