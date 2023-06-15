<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_district.php 26298 2011-12-08 03:58:22Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

shownav('global', 'district');
$values = array(intval($_GET['pid']), intval($_GET['cid']), intval($_GET['did']));
$elems = array($_GET['province'], $_GET['city'], $_GET['district']);
$level = 1;
$upids = array(0);
$theid = 0;
for($i=0;$i<3;$i++) {
	if(!empty($values[$i])) {
		$theid = intval($values[$i]);
		$upids[] = $theid;
		$level++;
	} else {
		for($j=$i; $j<3; $j++) {
			$values[$j] = '';
		}
		break;
	}
}

if(submitcheck('editsubmit')) {

	$delids = array();
	foreach(C::t('common_district')->fetch_all_by_upid($theid) as $value) {
		$usetype = 0;
		if($_POST['birthcity'][$value['id']] && $_POST['residecity'][$value['id']]) {
			$usetype = 3;
		} elseif($_POST['birthcity'][$value['id']]) {
			$usetype = 1;
		} elseif($_POST['residecity'][$value['id']]) {
			$usetype = 2;
		}
		if(!isset($_POST['district'][$value['id']])) {
			$delids[] = $value['id'];
		} elseif($_POST['district'][$value['id']] != $value['name'] || $_POST['displayorder'][$value['id']] != $value['displayorder'] || $usetype != $value['usetype']) {
			C::t('common_district')->update($value['id'], array('name'=>$_POST['district'][$value['id']], 'displayorder'=>$_POST['displayorder'][$value['id']], 'usetype'=>$usetype));
		}
	}
	if($delids) {
		$ids = $delids;
		for($i=$level; $i<4; $i++) {
			$ids = array();
			foreach(C::t('common_district')->fetch_all_by_upid($delids) as $value) {
				$value['id'] = intval($value['id']);
				$delids[] = $value['id'];
				$ids[] = $value['id'];
			}
			if(empty($ids)) {
				break;
			}
		}
		C::t('common_district')->delete($delids);
	}
	if(!empty($_POST['districtnew'])) {
		$inserts = array();
		$displayorder = '';
		foreach($_POST['districtnew'] as $key => $value) {
			$displayorder = trim($_POST['districtnew_order'][$key]);
			$value = trim($value);
			if(!empty($value)) {
				C::t('common_district')->insert(array('name' => $value, 'level' => $level, 'upid' => $theid, 'displayorder' => $displayorder));
			}
		}
	}
	cpmsg('setting_district_edit_success', 'action=district&pid='.$values[0].'&cid='.$values[1].'&did='.$values[2], 'succeed');

} else {
	showsubmenu('district');
	showtips('district_tips');

	showformheader('district&pid='.$values[0].'&cid='.$values[1].'&did='.$values[2]);
	showtableheader();

	$options = array(1=>array(), 2=>array(), 3=>array());
	$thevalues = array();
	foreach(C::t('common_district')->fetch_all_by_upid($upids) as $value) {
		$options[$value['level']][] = array($value['id'], $value['name']);
		if($value['upid'] == $theid) {
			$thevalues[] = array($value['id'], $value['name'], $value['displayorder'], $value['usetype']);
		}
	}

	$names = array('province', 'city', 'district');
	for($i=0; $i<3;$i++) {
		$elems[$i] = !empty($elems[$i]) ? $elems[$i] : $names[$i];
	}
	$html = '';
	for($i=0;$i<3;$i++) {
		$l = $i+1;
		$jscall = ($i == 0 ? 'this.form.city.value=\'\';this.form.district.value=\'\';' : '')."refreshdistrict('$elems[0]', '$elems[1]', '$elems[2]')";
		$html .= '<select name="'.$elems[$i].'" id="'.$elems[$i].'" onchange="'.$jscall.'">';
		$html .= '<option value="">'.lang('spacecp', 'district_level_'.$l).'</option>';
		foreach($options[$l] as $option) {
			$selected = $option[0] == $values[$i] ? ' selected="selected"' : '';
			$html .= '<option value="'.$option[0].'"'.$selected.'>'.$option[1].'</option>';
		}
		$html .= '</select>&nbsp;&nbsp;';
	}
	echo cplang('district_choose').' &nbsp; '.$html;
	showsubtitle($values[0] ? array('', 'display_order', 'name', 'operation') : array('', 'display_order', 'name', 'district_birthcity', 'district_residecity', 'operation'));
	foreach($thevalues as $value) {
		$valarr = array();
		$valarr[] = '';
		$valarr[] = '<input type="text" id="displayorder_'.$value[0].'" class="txt" name="displayorder['.$value[0].']" value="'.$value[2].'"/>';
		$valarr[] = '<p id="p_'.$value[0].'"><input type="text" id="input_'.$value[0].'" class="txt" name="district['.$value[0].']" value="'.$value[1].'"/></p>';
		if(!$values[0]) {
			$valarr[] = '<input type="checkbox" name="birthcity['.$value[0].']" value="1" class="checkbox"'.($value[3] && in_array($value[3], array(1,3)) ? ' checked="checked" ':'').' />';
			$valarr[] = '<input type="checkbox" name="residecity['.$value[0].']" value="1" class="checkbox"'.($value[3] && in_array($value[3], array(2,3)) ? ' checked="checked" ':'').' />';
		}
		$valarr[] = '<a href="javascript:;" onclick="deletedistrict('.$value[0].');return false;">'.cplang('delete').'</a>';
		showtablerow('id="td_'.$value[0].'"', array('', 'class="td25"','','','',''), $valarr);
	}
	showtablerow('', array('colspan=2'), array(
			'<div><a href="javascript:;" onclick="addrow(this, 0, 1);return false;" class="addtr">'.cplang('add').'</a></div>'
		));
	showsubmit('editsubmit', 'submit');
	$adminurl = ADMINSCRIPT.'?action=district';
echo <<<SCRIPT
<script type="text/javascript">
var rowtypedata = [
	[[1,'', ''],[1,'<input type="text" class="txt" name="districtnew_order[]" value="0" />', 'td25'],[2,'<input type="text" class="txt" name="districtnew[]" value="" />', '']],
];

function refreshdistrict(province, city, district) {
	location.href = "$adminurl"
		+ "&province="+province+"&city="+city+"&district="+district
		+"&pid="+$(province).value + "&cid="+$(city).value+"&did="+$(district).value;
}

function editdistrict(did) {
	$('input_' + did).style.display = "block";
	$('span_' + did).style.display = "none";
}

function deletedistrict(did) {
	var elem = $('p_' + did);
	elem.parentNode.removeChild(elem);
	var elem = $('td_' + did);
	elem.parentNode.removeChild(elem);
}
</script>
SCRIPT;
	showtablefooter();
	showformfooter();
}

?>