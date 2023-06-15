<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_magics.php 34093 2013-10-09 05:41:18Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = $operation ? $operation : 'admin';

if($operation == 'admin') {

	if(!submitcheck('magicsubmit')) {

		shownav('extended', 'magics', 'admin');
		showsubmenu('nav_magics', array(
			array('admin', 'magics&operation=admin', 1),
			array('nav_magics_confer', 'members&operation=confermagic', 0)
		));
		showtips('magics_tips');

		$settings = C::t('common_setting')->fetch_all(array('magicstatus', 'magicdiscount'));
		showformheader('magics&operation=admin');
		showtableheader();
		showsetting('magics_config_open', 'settingsnew[magicstatus]', $settings['magicstatus'], 'radio');
		showsetting('magics_config_discount', 'settingsnew[magicdiscount]', $settings['magicdiscount'], 'text');
		showtablefooter();

		showtableheader('magics_list', 'fixpadding');
		$newmagics = getmagics();
		showsubtitle(array('', 'display_order', '<input type="checkbox" onclick="checkAll(\'prefix\', this.form, \'available\', \'availablechk\')" class="checkbox" id="availablechk" name="availablechk">'.cplang('available'), 'name', $lang['price'], $lang['magics_num'], 'weight'));

		foreach(C::t('common_magic')->fetch_all_data() as $magic) {
			$magic['credit'] = $magic['credit'] ? $magic['credit'] : $_G['setting']['creditstransextra'][3];
			$credits = '<select name="credit['.$magic['magicid'].']">';
			foreach($_G['setting']['extcredits'] as $i => $extcredit) {
				$credits .= '<option value="'.$i.'" '.($i == $magic['credit'] ? 'selected' : '').'>'.$extcredit['title'].'</option>';
			}
			$credits .= '</select>';
			$magictype = $lang['magics_type_'.$magic['type']];
			$eidentifier = explode(':', $magic['identifier']);

			showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', '', ''), array(
				"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$magic[magicid]\">",
				"<input type=\"text\" class=\"txt\" name=\"displayorder[$magic[magicid]]\" value=\"$magic[displayorder]\">",
				"<input type=\"checkbox\" class=\"checkbox\" name=\"available[$magic[magicid]]\" value=\"1\" ".($magic['available'] ? 'checked' : '').">",
				"<input type=\"text\" class=\"txt\" style=\"width:80px\" name=\"name[$magic[magicid]]\" value=\"$magic[name]\">".
				(count($eidentifier) > 1 ? (file_exists(DISCUZ_ROOT.'./source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.small.gif') ? '<img class="vmiddle" src="source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.small.gif" />' : '')
					: (file_exists(DISCUZ_ROOT.'./static/image/magic/'.$magic['identifier'].'.small.gif') ? '<img class="vmiddle" src="static/image/magic/'.$magic['identifier'].'.small.gif" />' : '')),
				"<input type=\"text\" class=\"txt\" name=\"price[$magic[magicid]]\" value=\"$magic[price]\">".$credits,
				"<input type=\"text\" class=\"txt\" name=\"num[$magic[magicid]]\" value=\"$magic[num]\">".
					($magic['supplytype'] ? '/ '.$magic['supplynum'].' / '.$lang['magic_suppytype_'.$magic['supplytype']] : ''),
				"<input type=\"text\" class=\"txt\" name=\"weight[$magic[magicid]]\" value=\"$magic[weight]\"><input type=\"hidden\" name=\"identifier[$magic[magicid]]\" value=\"$magic[identifier]\">",
				"<a href=\"".ADMINSCRIPT."?action=magics&operation=edit&magicid=$magic[magicid]\" class=\"act\">$lang[detail]</a>"
			));
			unset($newmagics[$magic[identifier]]);
		}
		foreach($newmagics as $newmagic) {
			$credits = '<select name="newcredit['.$newmagic['class'].']">';
			foreach($_G['setting']['extcredits'] as $i => $extcredit) {
				$credits .= '<option value="'.$i.'">'.$extcredit['title'].'</option>';
			}
			$credits .= '</select>';
			$eclass = explode(':', $newmagic['class']);
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', '', ''), array(
				'',
				"<input type=\"text\" class=\"txt\" name=\"newdisplayorder[$newmagic[class]]\" value=\"0\">",
				"<input type=\"checkbox\" class=\"checkbox\" name=\"newavailable[$newmagic[class]]\" value=\"1\">",
				"<input type=\"text\" class=\"txt\" style=\"width:80px\" name=\"newname[$newmagic[class]]\" value=\"$newmagic[name]\">".
					(count($eclass) > 1 ? (file_exists(DISCUZ_ROOT.'./source/plugin/'.$eclass[0].'/magic/magic_'.$eclass[1].'.small.gif') ? '<img class="vmiddle" src="source/plugin/'.$eclass[0].'/magic/magic_'.$eclass[1].'.small.gif" />' : '')
						: (file_exists(DISCUZ_ROOT.'./static/image/magic/'.$newmagic['class'].'.small.gif') ? '<img class="vmiddle" src="static/image/magic/'.$newmagic['class'].'.small.gif" />' : '')).
					"<input type=\"hidden\" name=\"newdesc[$newmagic[class]]\" value=\"$newmagic[desc]\" />".
					"<input type=\"hidden\" name=\"newuseevent[$newmagic[class]]\" value=\"$newmagic[useevent]\" />",
				"<input type=\"text\" class=\"txt\" name=\"newprice[$newmagic[class]]\" value=\"$newmagic[price]\">".$credits,
				"<input type=\"text\" class=\"txt\" name=\"newnum[$newmagic[class]]\" value=\"0\">",
				"<input type=\"text\" class=\"txt\" name=\"newweight[$newmagic[class]]\" value=\"$newmagic[weight]\">",
				'<font color="#F00">New!</font>'
			));
		}
		showsubmit('magicsubmit', 'submit', 'del', '&nbsp;&nbsp;<input type="checkbox" onclick="checkAll(\'prefix\', this.form, \'available\', \'availablechk1\')" class="checkbox" id="availablechk1" name="availablechk1">'.cplang('available'));
		showtablefooter();
		showformfooter();

	} else {
		if(is_array($_GET['settingsnew'])) {
			C::t('common_setting')->update_batch(array('magicstatus'=> $_GET['settingsnew']['magicstatus'], 'magicdiscount' => $_GET['settingsnew']['magicdiscount']));
		}

		if($ids = dimplode($_GET['delete'])) {
			C::t('common_magic')->delete($_GET['delete']);
			C::t('common_member_magic')->delete('', $_GET['delete']);
			C::t('common_magiclog')->delete_by_magicid($_GET['delete']);

		}

		if(is_array($_GET['name'])) {
			foreach($_GET['name'] as $id => $val) {
				if(!is_array($_GET['identifier']) ||
					!is_array($_GET['displayorder']) || !is_array($_GET['credit']) ||
					!is_array($_GET['price']) || !is_array($_GET['num']) ||
					!is_array($_GET['weight']) || !preg_match('/^[\w:]+$/', $_GET['identifier'][$id])) {
					continue;
				}
				C::t('common_magic')->update($id, array(
					'available' => $_GET['available'][$id],
					'name' => $val,
					'identifier' => $_GET['identifier'][$id],
					'displayorder' => $_GET['displayorder'][$id],
					'credit' => $_GET['credit'][$id],
					'price' => $_GET['price'][$id],
					'num' => $_GET['num'][$id],
					'weight' => $_GET['weight'][$id]
				));
			}
		}

		if(is_array($_GET['newname'])) {

			foreach($_GET['newname'] as $identifier => $name) {
				$data = array(
					'name' => $name,
					'useevent' => $_GET['newuseevent'][$identifier],
					'identifier' => $identifier,
					'available' => $_GET['newavailable'][$identifier],
					'description' => $_GET['newdesc'][$identifier],
					'displayorder' => $_GET['newdisplayorder'][$identifier],
					'credit' => $_GET['newcredit'][$identifier],
					'price' => $_GET['newprice'][$identifier],
					'num' => $_GET['newnum'][$identifier],
					'weight' => $_GET['newweight'][$identifier],
				);
				C::t('common_magic')->insert($data);
			}
		}

		updatecache(array('setting', 'magics'));
		cpmsg('magics_data_succeed', 'action=magics&operation=admin', 'succeed');

	}

} elseif($operation == 'edit') {

	$magicid = intval($_GET['magicid']);
	$magic = C::t('common_magic')->fetch($magicid);

	if(!submitcheck('magiceditsubmit')) {

		$magicperm = dunserialize($magic['magicperm']);

		$groups = $forums = array();
		foreach(C::t('common_usergroup')->range() as $group) {
			$groups[$group['groupid']] = $group['grouptitle'];
		}

		$typeselect = array($magic['type'] => 'selected');

		shownav('extended', 'magics', 'admin');
		showsubmenu('nav_magics', array(
			array('admin', 'magics&operation=admin', 0),
			array('nav_magics_confer', 'members&operation=confermagic', 0)
		));
		echo '<br />';

		$eidentifier = explode(':', $magic['identifier']);
		if(count($eidentifier) > 1 && preg_match('/^[\w\_:]+$/', $magic['identifier'])) {
			include_once DISCUZ_ROOT.'./source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.php';
			$magicclass = 'magic_'.$eidentifier[1];
		} else {
			require_once libfile('magic/'.$magic['identifier'], 'class');
			$magicclass = 'magic_'.$magic['identifier'];
		}

		$magicclass = new $magicclass;
		$magicsetting = $magicclass->getsetting($magicperm);
		echo '<div class="colorbox"><h4>'.lang('magic/'.$magic['identifier'], $magicclass->name).'</h4>'.
			'<table cellspacing="0" cellpadding="3"><tr><td>'.
			(count($eidentifier) > 1 ? (file_exists(DISCUZ_ROOT.'./source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.gif') ? '<img src="source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.gif" />' : '')
			: (file_exists(DISCUZ_ROOT.'./static/image/magic/'.$magic['identifier'].'.gif') ? '<img src="static/image/magic/'.$magic['identifier'].'.gif" />' : '')).
			'</td><td valign="top">'.lang('magic/'.$magic['identifier'], $magicclass->description).'</td></tr></table>'.
			'<div style="width:95%" align="right">'.lang('magic/'.$magic['identifier'], $magicclass->copyright).'</div></div>';
		$credits = array();
		foreach($_G['setting']['extcredits'] as $i => $extcredit) {
			$credits[] = array($i, $extcredit['title']);
		}

		showformheader('magics&operation=edit&magicid='.$magicid);
		showtableheader();
		showtitle($lang['magics_edit'].' - '.$magic['name'].'('.$magic['identifier'].')');
		showsetting('magics_edit_name', 'namenew', $magic['name'], 'text');
		showsetting('magics_edit_credit', array('creditnew', $credits), $magic['credit'], 'select');
		showsetting('magics_edit_price', 'pricenew', $magic['price'], 'text');
		showsetting('magics_edit_num', 'numnew', $magic['num'], 'text');
		showsetting('magics_edit_supplynum', 'supplynumnew', $magic['supplynum'], 'text');
		showsetting('magics_edit_weight', 'weightnew', $magic['weight'], 'text');
		showsetting('magics_edit_supplytype', array('supplytypenew', array(
			array(0, $lang['magics_goods_stack_none']),
			array(1, $lang['magics_goods_stack_day']),
			array(2, $lang['magics_goods_stack_week']),
			array(3, $lang['magics_goods_stack_month']),
		)), $magic['supplytype'], 'mradio');
		showsetting('magics_edit_useperoid', array('useperoidnew', array(
			array(0, $lang['magics_edit_useperoid_none']),
			array(1, $lang['magics_edit_useperoid_day']),
			array(4, $lang['magics_edit_useperoid_24hr']),
			array(2, $lang['magics_edit_useperoid_week']),
			array(3, $lang['magics_edit_useperoid_month']),
		)), $magic['useperoid'], 'mradio');
		showsetting('magics_edit_usenum', 'usenumnew', $magic['usenum'], 'text');
		showsetting('magics_edit_description', 'descriptionnew', $magic['description'], 'textarea');

		if(is_array($magicsetting)) {
			foreach($magicsetting as $settingvar => $setting) {
				if(!empty($setting['value']) && is_array($setting['value'])) {
					foreach($setting['value'] as $k => $v) {
						$setting['value'][$k][1] = lang('magic/'.$magic['identifier'], $setting['value'][$k][1]);
					}
				}
				$varname = in_array($setting['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
					($setting['type'] == 'mselect' ? array('perm['.$settingvar.'][]', $setting['value']) : array('perm['.$settingvar.']', $setting['value']))
					: 'perm['.$settingvar.']';
				$value = $magicperm[$settingvar] != '' ? $magicperm[$settingvar] : $setting['default'];
				$comment = lang('magic/'.$magic['identifier'], $setting['title'].'_comment');
				$comment = $comment != $setting['title'].'_comment' ? $comment : '';
				showsetting(lang('magic/'.$magic['identifier'], $setting['title']).':', $varname, $value, $setting['type'], '', 0, $comment);
			}
		}

		showtitle('magics_edit_perm');
		showtablerow('', 'colspan="2" class="td27"', $lang['magics_edit_usergroupperm'].':<input class="checkbox" type="checkbox" name="chkall1" onclick="checkAll(\'prefix\', this.form, \'usergroupsperm\', \'chkall1\', true)" id="chkall1" /><label for="chkall1"> '.cplang('select_all').'</label>');
		showtablerow('', 'colspan="2"', mcheckbox('usergroupsperm', $groups, explode("\t", $magicperm['usergroups'])));

		if(!empty($magicclass->targetgroupperm)) {
			showtablerow('', 'colspan="2" class="td27"', $lang['magics_edit_targetgroupperm'].':<input class="checkbox" type="checkbox" name="chkall2" onclick="checkAll(\'prefix\', this.form, \'targetgroupsperm\', \'chkall2\', true)" id="chkall2" /><label for="chkall2"> '.cplang('select_all').'</label>');
			showtablerow('', 'colspan="2"', mcheckbox('targetgroupsperm', $groups, explode("\t", $magicperm['targetgroups'])));
		}
		showsubmit('magiceditsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$namenew	= dhtmlspecialchars(trim($_GET['namenew']));
		$identifiernew	= dhtmlspecialchars(trim(strtoupper($_GET['identifiernew'])));
		$descriptionnew	= dhtmlspecialchars($_GET['descriptionnew']);
		$availablenew   = !$identifiernew ? 0 : 1;

		$magicperm['usergroups'] = is_array($_GET['usergroupsperm']) && !empty($_GET['usergroupsperm']) ? "\t".implode("\t",$_GET['usergroupsperm'])."\t" : '';
		$magicperm['targetgroups'] = is_array($_GET['targetgroupsperm']) && !empty($_GET['targetgroupsperm']) ? "\t".implode("\t",$_GET['targetgroupsperm'])."\t" : '';

		$eidentifier = explode(':', $magic['identifier']);
		if(count($eidentifier) > 1 && preg_match('/^[\w\_:]+$/', $magic['identifier'])) {
			include_once DISCUZ_ROOT.'./source/plugin/'.$eidentifier[0].'/magic/magic_'.$eidentifier[1].'.php';
			$magicclass = 'magic_'.$eidentifier[1];
		} else {
			require_once libfile('magic/'.$magic['identifier'], 'class');
			$magicclass = 'magic_'.$magic['identifier'];
		}

		$magicclass = new $magicclass;
		$magicclass->setsetting($magicperm, $_GET['perm']);
		$magicpermnew = addslashes(serialize($magicperm));

		$supplytypenew = intval($_GET['supplytypenew']);
		$supplynumnew = $_GET['supplytypenew'] ? intval($_GET['supplynumnew']) : 0;
		$usenumnew = intval($_GET['usenumnew']);
		$useperoidnew = $_GET['useperoidnew'] ? intval($_GET['useperoidnew']) : 0;
		$creditnew = intval($_GET['creditnew']);

		if(!$namenew) {
			cpmsg('magics_parameter_invalid', '', 'error');
		}

		if(C::t('common_magic')->check_identifier($identifiernew, $magicid)) {
			cpmsg('magics_identifier_invalid', '', 'error');
		}

		C::t('common_magic')->update($magicid, array(
			'name' => $namenew,
			'description' => $descriptionnew,
			'price' => $_GET['pricenew'],
			'num' => $_GET['numnew'],
			'supplytype' => $supplytypenew,
			'supplynum' => $supplynumnew,
			'useperoid' => $useperoidnew,
			'usenum' => $usenumnew,
			'weight' => $_GET['weightnew'],
			'magicperm' => $magicpermnew,
			'credit' => $creditnew
		));

		updatecache(array('setting', 'magics'));
		cpmsg('magics_data_succeed', 'action=magics&operation=admin', 'succeed');

	}

}

function getmagics() {
	global $_G;
	$checkdirs = array_merge(array(''), $_G['setting']['plugins']['available']);
	$magics = array();
	foreach($checkdirs as $key) {
		if($key) {
			$dir = DISCUZ_ROOT.'./source/plugin/'.$key.'/magic';
		} else {
			$dir = DISCUZ_ROOT.'./source/class/magic';
		}
		if(!file_exists($dir)) {
			continue;
		}
		$magicdir = dir($dir);
		while($entry = $magicdir->read()) {
			if(!in_array($entry, array('.', '..')) && preg_match("/^magic\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($dir.'/'.$entry)) {
				@include_once $dir.'/'.$entry;
				$magicclass = substr($entry, 0, -4);
				if(class_exists($magicclass)) {
					$magic = new $magicclass();
					$script = substr($magicclass, 6);
					$script = ($key ? $key.':' : '').$script;
					$magics[$script] = array(
						'class' => $script,
						'name' => lang('magic/'.$script, $magic->name),
						'desc' => lang('magic/'.$script, $magic->description),
						'price' => $magic->price,
						'weight' => $magic->weight,
						'useevent' => !empty($magic->useevent) ? $magic->useevent : 0,
						'version' => $magic->version,
						'copyright' => lang('magic/'.$script, $magic->copyright),
						'filemtime' => @filemtime($dir.'/'.$entry)
					);
				}
			}
		}
	}
	uasort($magics, 'filemtimesort');
	return $magics;
}

?>