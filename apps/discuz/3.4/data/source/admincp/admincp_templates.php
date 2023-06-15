<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_templates.php 29301 2012-04-01 02:55:08Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
if(!isfounder()) cpmsg('noaccess_isfounder', '', 'error');

$isplugindeveloper = isset($_G['config']['plugindeveloper']) && $_G['config']['plugindeveloper'] > 0;
if(!$isplugindeveloper) {
	cpmsg('undefined_action', '', 'error');
}

$operation = empty($operation) ? 'admin' : $operation;

if($operation == 'admin') {

	if(!submitcheck('tplsubmit')) {

		$templates = '';
		foreach(C::t('common_template')->fetch_all_data() as $tpl) {
			$basedir = basename($tpl['directory']);
			$templates .= showtablerow('', array('class="td25"', '', 'class="td29"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" ".($tpl['templateid'] == 1 ? 'disabled ' : '')."value=\"$tpl[templateid]\">",
				"<input type=\"text\" class=\"txt\" size=\"8\" name=\"namenew[$tpl[templateid]]\" value=\"$tpl[name]\">".
				($basedir != 'default' ? '<a href="'.ADMINSCRIPT.'?action=cloudaddons&frame=no&id='.urlencode($basedir).'.template" target="_blank" title="'.$lang['cloudaddons_linkto'].'">'.$lang['view'].'</a>' : ''),
				"<input type=\"text\" class=\"txt\" size=\"20\" name=\"directorynew[$tpl[templateid]]\" value=\"$tpl[directory]\">",
				!empty($tpl['copyright']) ?
					$tpl['copyright'] :
					"<input type=\"text\" class=\"txt\" size=\"8\" name=\"copyrightnew[$tpl[templateid]]\" value=>"
			), TRUE);
		}

		shownav('template', 'templates_admin');
		showsubmenu('styles_admin', array(
			array('templates_add', 'templates&operation=add', 0),
			array('nav_templates', 'templates&operation=admin', 1),
			array('cloudaddons_style_link', 'cloudaddons&frame=no&operation=templates&from=more', 0, 1),
		));
		showformheader('templates');
		showtableheader();
		showsubtitle(array('', 'templates_admin_name', 'dir', 'copyright'));
		echo $templates;
		echo '<tr><td>'.$lang['add_new'].'</td><td><input type="text" class="txt" size="8" name="newname"></td><td class="td29"><input type="text" class="txt" size="20" name="newdirectory"></td><td><input type="text" class="txt" size="25" name="newcopyright"></td><td>&nbsp;</td></tr>';
		showsubmit('tplsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($_GET['newname']) {
			if(!$_GET['newdirectory']) {
				cpmsg('tpl_new_directory_invalid', '', 'error');
			} elseif(!istpldir($_GET['newdirectory'])) {
				$directory = $_GET['newdirectory'];
				cpmsg('tpl_directory_invalid', '', 'error', array('directory' => $directory));
			}
			C::t('common_template')->insert(array('name' => $_GET['newname'], 'directory' => $_GET['newdirectory'], 'copyright' => $_GET['newcopyright']));
		}

		foreach($_GET['directorynew'] as $id => $directory) {
			if(!$_GET['delete'] || ($_GET['delete'] && !in_array($id, $_GET['delete']))) {
				if(!istpldir($directory)) {
					cpmsg('tpl_directory_invalid', '', 'error', array('directory' => $directory));
				} elseif($id == 1 && $directory != './template/default') {
					cpmsg('tpl_default_directory_invalid', '', 'error');
				}
				C::t('common_template')->update($id, array('name' => $_GET['namenew'][$id], 'directory' => $_GET['directorynew'][$id]));
				if(!empty($_GET['copyrightnew'][$id])) {
					$template = C::t('common_template')->fetch($id);
					if(!$template['copyright']) {
						C::t('common_template')->update($id, array('copyright' => $_GET['copyrightnew'][$id]));
					}
				}
			}
		}

		if(is_array($_GET['delete'])) {
			if(in_array('1', $_GET['delete'])) {
				cpmsg('tpl_delete_invalid', '', 'error');
			}
			if($_GET['delete']) {
				C::t('common_template')->delete($_GET['delete']);
				C::t('common_style')->update($_GET['delete'], array('templateid' => 1));
			}
		}

		updatecache('styles');
		cpmsg('tpl_update_succeed', 'action=templates', 'succeed');

	}

} elseif($operation == 'add') {
	$predefinedvars = array('available' => array(), 'boardimg' => array(), 'imgdir' => array(), 'styleimgdir' => array(), 'stypeid' => array(),
		'headerbgcolor' => array(0, $lang['styles_edit_type_bg']),
		'bgcolor' => array(0),
		'sidebgcolor' => array(0, '', '#FFF sidebg.gif repeat-y 100% 0'),
		'titlebgcolor' => array(0),

		'headerborder' => array(1, $lang['styles_edit_type_header'], '1px'),
		'headertext' => array(0),
		'footertext' => array(0),

		'font' => array(1, $lang['styles_edit_type_font']),
		'fontsize' => array(1),
		'threadtitlefont' => array(1, $lang['styles_edit_type_thread_title']),
		'threadtitlefontsize' => array(1),
		'smfont' => array(1),
		'smfontsize' => array(1),
		'tabletext' => array(0),
		'midtext' => array(0),
		'lighttext' => array(0),

		'link' => array(0, $lang['styles_edit_type_url']),
		'highlightlink' => array(0),
		'lightlink' => array(0),

		'wrapbg' => array(0),
		'wrapbordercolor' => array(0),

		'msgfontsize' => array(1, $lang['styles_edit_type_post'], '14px'),
		'contentwidth' => array(1),
		'contentseparate' => array(0),

		'menubgcolor' => array(0, $lang['styles_edit_type_menu']),
		'menutext' => array(0),
		'menuhoverbgcolor' => array(0),
		'menuhovertext' => array(0),

		'inputborder' => array(0, $lang['styles_edit_type_input']),
		'inputborderdarkcolor' => array(0),
		'inputbg' => array(0, '', '#FFF'),

		'dropmenuborder' => array(0, $lang['styles_edit_type_dropmenu']),
		'dropmenubgcolor' => array(0),

		'floatbgcolor' => array(0, $lang['styles_edit_type_float']),
		'floatmaskbgcolor' => array(0),

		'commonborder' => array(0, $lang['styles_edit_type_other']),
		'commonbg' => array(0),
		'specialborder' => array(0),
		'specialbg' => array(0),
		'noticetext' => array(0),
	);
	if(!submitcheck('addsubmit')) {
		shownav('template', 'templates_add');
		showsubmenu('styles_admin', array(
			array('templates_add', 'templates&operation=add', 1),
			array('nav_templates', 'templates&operation=admin', 0),
			array('cloudaddons_style_link', 'cloudaddons&frame=no&operation=templates&from=more', 0, 1),
		));
		showtips('templates_add_tips');

		showformheader("templates&operation=add", '', 'configform');
		showtableheader();
		showsetting('templates_edit_name', 'namenew', '', 'text');
		showsetting('templates_edit_copyright', 'copyrightnew', '', 'text');
		showsetting('templates_edit_identifier', 'identifiernew', '', 'text');

		$styleselect = array();
		$styleselect[] = array(0, $lang['templates_empty']);
		foreach(C::t('common_style')->fetch_all_data(true) as $value) {
			$styleselect[] = array($value['styleid'], $value['name']);
		}
		showsetting('templates_edit_style', array('styleidnew', $styleselect), '', 'select');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();
	} else {
		$namenew	= dhtmlspecialchars(trim($_GET['namenew']));
		$identifiernew	= trim($_GET['identifiernew']);
		$copyrightnew	= dhtmlspecialchars($_GET['copyrightnew']);
		$styleidnew	= dintval($_GET['styleidnew']);

		if(!$namenew) {
			cpmsg('templates_edit_name_invalid', '', 'error');
		}

		if(!ispluginkey($identifiernew)) {
			cpmsg('templates_edit_identifier_invalid', '', 'error');
		}

		$templateid = C::t('common_template')->insert(array('name' => $namenew, 'directory' => './template/'.$identifiernew, 'copyright' => $copyrightnew), true);
		$styleid = C::t('common_style')->insert(array('name' => $namenew, 'templateid' => $templateid), true);
		if($styleidnew) {
			foreach(C::t('common_stylevar')->fetch_all_by_styleid($styleidnew) as $stylevar) {
				C::t('common_stylevar')->insert(array('styleid' => $styleid, 'variable' => $stylevar['variable'], 'substitute' => $stylevar['substitute']));
			}
		}else{
			foreach(array_keys($predefinedvars) as $variable) {
				$substitute = isset($predefinedvars[$variable][2]) ? $predefinedvars[$variable][2] : '';
				C::t('common_stylevar')->insert(array('styleid' => $styleid, 'variable' => $variable, 'substitute' => $substitute));
			}
		}
		dmkdir(DISCUZ_ROOT.'./template/'.$identifiernew.'/');
		updatecache(array('setting', 'styles'));
		loadcache('style_default', true);
		updatecache('updatediytemplate');
		cpmsg('templates_add_succeed', "action=styles", 'succeed');
	}

}
?>