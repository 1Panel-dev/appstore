<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_adv.php 34093 2013-10-09 05:41:18Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$root = '<a href="'.ADMINSCRIPT.'?action=adv">'.cplang('adv_admin').'</a>';

$operation = $operation ? $operation : 'list';

$defaulttargets = array('portal', 'home', 'member', 'forum', 'group', 'plugin');

if(!empty($_GET['preview'])) {
	$_GET['advnew'][$_GET['advnew']['style']]['url'] = $_GET['TMPadvnew'.$_GET['advnew']['style']] ? $_GET['TMPadvnew'.$_GET['advnew']['style']] : $_GET['advnew'.$_GET['advnew']['style']];
	$data = encodeadvcode($_GET['advnew']);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET;?>" />
<head>
<script type="text/javascript">var IMGDIR = '<?php echo $_G['style']['imgdir']; ?>', cookiepre = '<?php echo $_G['config']['cookie']['cookiepre'];?>', cookiedomain = '<?php echo $_G['config']['cookie']['cookiedomain'];?>', cookiepath = '<?php echo $_G['config']['cookie']['cookiepath'];?>';</script>
<script type="text/javascript" src="static/js/common.js"></script>
<link rel="stylesheet" type="text/css" href="data/cache/style_<?php echo $_G['setting']['styleid'];?>_common.css" />
</head>
<body>
<div id="append_parent"></div><div id="ajaxwaitid"></div>
<div id="hd"><div class="wp">
<?php echo $data;?>
</div></div>
</body>
</html>
<?php

exit;
}

cpheader();

if($operation == 'ad') {

	if(!submitcheck('advsubmit')) {

		shownav('extended', 'adv_admin');
		$type = $_GET['type'];
		$target = $_GET['target'];
		$typeadd = $advfile = '';
		if($type) {
			$etype = explode(':', $type);
			if(count($etype) > 1 && preg_match('/^[\w\_:]+$/', $type)) {
				if(ispluginkey($etype[0]) && preg_match('/^\w$/', $etype[1])) {
					$advfile = DISCUZ_ROOT.'./source/plugin/'.$etype[0].'/adv/adv_'.$etype[1].'.php';
					$advclass = 'adv_'.$etype[1];
				}
			} else {
				$advfile = libfile('adv/'.$type, 'class');
				$advclass = 'adv_'.$type;
			}
			if($advfile && file_exists($advfile)) {
				require_once $advfile;
				$advclassv = new $advclass();
				if(class_exists($advclass)) {
					$advsetting = $advclassv->getsetting();
					$typeadd = ' - '.lang('adv/'.$type, $advclassv->name);
					if($type == 'custom') {
						$typeadd .= ' '.$advclassv->customname;
					}
					$typeadd .= ' <a href="'.ADMINSCRIPT.'?action=adv&operation=ad" style="font-weight:normal;font-size:12px">('.cplang('adv_admin_listall').')</a>';
				}
			}
		}
		showsubmenu($root.' &raquo; '.cplang('adv_list').$typeadd);

		showformheader('adv&operation=ad');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'display_order', 'available', 'subject', !$type ? 'type' : '', 'adv_style', 'start_time', 'end_time', 'adv_targets', ''));

		$advppp = $type != 'custom' ? 25 : 9999;
		$conditions = '';
		$order_by = 'displayorder, advid DESC, targets DESC';
		$start_limit = ($page - 1) * $advppp;

		$title = $_GET['title'];
		$starttime = $_GET['starttime'];
		$endtime = $_GET['endtime'];
		$orderby = $_GET['orderby'];

		$advnum = C::t('common_advertisement')->count_search($title, $starttime, $endtime, $type, $target);

		if(!$type) {
			$customadv = array();
			foreach(C::t('common_advertisement_custom')->fetch_all_data() as $custom) {
				$customadv[$custom['id']] = $custom['name'];
			}
		}

		$typenames = array();
		foreach(C::t('common_advertisement')->fetch_all_search($title, $starttime, $endtime, $type, $target, $orderby, $start_limit, $advppp) as $adv) {
			if(!$type) {
				$advfile = '';
				$etype = explode(':', $adv['type']);
				if(count($etype) > 1 && preg_match('/^[\w\_:]+$/', $adv['type'])) {
					$advfile = DISCUZ_ROOT.'./source/plugin/'.$etype[0].'/adv/adv_'.$etype[1].'.php';
					$advclass = 'adv_'.$etype[1];
				} else {
					$advfile = libfile('adv/'.$adv['type'], 'class');
					$advclass = 'adv_'.$adv['type'];
				}
				if(!$advfile || !file_exists($advfile)) {
					continue;
				}
				if(!isset($typenames[$adv['type']])) {
					require_once $advfile;
					if(class_exists($advclass)) {
						$advclassv = new $advclass();
						$typenames[$adv['type']] = lang('adv/'.$adv['type'], $advclassv->name);
					} else {
						$typenames[$adv['type']] = $adv['type'];
					}
				}
			}
			$adv['parameters'] = dunserialize($adv['parameters']);
			if($adv['type'] == 'custom' && $type && $_GET['customid'] != $adv['parameters']['extra']['customid']) {
				continue;
			}
			$targets = array();
			foreach(explode("\t", $adv['targets']) as $t) {
				if('adv_edit_targets_'.$t != 'adv_edit_targets_custom') {
					$targets[] = $lang['adv_edit_targets_'.$t] ? $lang['adv_edit_targets_'.$t] : $t;
				}
			}

			showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$adv[advid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$adv[advid]]\" value=\"$adv[displayorder]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$adv[advid]]\" value=\"1\" ".($adv['available'] ? 'checked' : '').">",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"titlenew[$adv[advid]]\" value=\"".dhtmlspecialchars($adv['title'])."\">",
				!$type ? '<a href="'.ADMINSCRIPT.'?action=adv&operation=ad&type='.$adv['type'].($adv['type'] != 'custom' ? '' : '&customid='.$adv['parameters']['extra']['customid']).'">'.$typenames[$adv['type']].($adv['type'] != 'custom' ? '' : ' '.$customadv[$adv['parameters']['extra']['customid']]).'</a>' : '',
				$lang['adv_style_'.$adv['parameters']['style']],
				$adv['starttime'] ? dgmdate($adv['starttime'], 'd') : $lang['unlimited'],
				$adv['endtime'] ? dgmdate($adv['endtime'], 'd') : $lang['unlimited'],
				$adv['type'] != 'custom' ? implode(', ', $targets) : $lang['custom'],
				"<a href=\"".ADMINSCRIPT."?action=adv&operation=edit&advid=$adv[advid]".($adv['type'] != 'custom' ? '' : '&customid='.$adv['parameters']['extra']['customid']).(!$type ? '&from=all' : '')."\" class=\"act\">$lang[edit]</a>"
			));
		}

		$multipage = multi($advnum, $advppp, $page, ADMINSCRIPT.'?action=adv&operation=ad'.($type ? '&type='.rawurlencode($type) : '').($target ? '&target='.rawurlencode($target) : '').($title ? '&title='.rawurlencode($title) : '').($starttime ? "&starttime=$starttime" : '').($endtime ? "&endtime=$endtime" : '').($orderby ? "&orderby=$orderby" : ''), 0, 3, TRUE, TRUE);

		$starttimecheck = array($starttime => 'selected="selected"');
		$endtimecheck = array($endtime => 'selected="selected"');
		$orderbycheck = array($orderby => 'selected="selected"');

		$targetselect = '<select name="target"><option value="">'.$lang['adv_targets'].'</option>';
		foreach($defaulttargets as $v) {
			$targetselect .= '<option value="'.$v.'"'.($v == $target ? ' selected="selected"' : '').'>'.$lang['adv_edit_targets_'.$v].'</option>';
		}
		$targetselect .= '</select>';

		showsubmit('advsubmit', 'submit', 'del', $type ? '<input type="button" class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=adv&operation=add&type='.$_GET['type'].($_GET['type'] != 'custom' ? '' : '&customid='.$_GET['customid']).'\'" value="'.cplang('add').'" />' : '', $multipage.'
<input type="text" class="txt" name="title" value="'.$title.'" size="15" onkeyup="if(event.keyCode == 13) this.form.searchsubmit.click()" onclick="this.value=\'\'"> &nbsp;&nbsp;
<select name="starttime">
<option value=""> '.cplang('start_time').'</option>
<option value="0" '.$starttimecheck['0'].'> '.cplang('all').'</option>
<option value="-1" '.$starttimecheck['-1'].'> '.cplang('nolimit').'</option>
<option value="86400" '.$starttimecheck['86400'].'> '.cplang('1_day').'</option>
<option value="604800" '.$starttimecheck['604800'].'> '.cplang('7_day').'</option>
<option value="2592000" '.$starttimecheck['2592000'].'> '.cplang('30_day').'</option>
<option value="7776000" '.$starttimecheck['7776000'].'> '.cplang('90_day').'</option>
<option value="15552000" '.$starttimecheck['15552000'].'> '.cplang('180_day').'</option>
<option value="31536000" '.$starttimecheck['31536000'].'> '.cplang('365_day').'</option>
</select> &nbsp;&nbsp;
<select name="endtime">
<option value=""> '.cplang('end_time').'</option>
<option value="0" '.$endtimecheck['0'].'> '.cplang('all').'</option>
<option value="-1" '.$endtimecheck['-1'].'> '.cplang('nolimit').'</option>
<option value="86400" '.$endtimecheck['86400'].'> '.cplang('1_day').'</option>
<option value="604800" '.$endtimecheck['604800'].'> '.cplang('7_day').'</option>
<option value="2592000" '.$endtimecheck['2592000'].'> '.cplang('30_day').'</option>
<option value="7776000" '.$endtimecheck['7776000'].'> '.cplang('90_day').'</option>
<option value="15552000" '.$endtimecheck['15552000'].'> '.cplang('180_day').'</option>
<option value="31536000" '.$endtimecheck['31536000'].'> '.cplang('365_day').'</option>
</select> &nbsp;&nbsp;
<select name="orderby">
<option value=""> '.cplang('adv_orderby').'</option>
<option value="starttime" '.$orderbycheck['starttime'].'> '.cplang('adv_addtime').'</option>
'.(!$type ? '<option value="type" '.$orderbycheck['type'].'> '.cplang('adv_type').'</option>' : '').'
<option value="displayorder" '.$orderbycheck['displayorder'].'> '.cplang('display_order').'</option>
</select> &nbsp;&nbsp;
'.$targetselect.' &nbsp;&nbsp;
<input type="button" class="btn" name="searchsubmit" value="'.cplang('search').'" onclick="if(this.form.title.value==\''.cplang('adv_inputtitle').'\'){this.form.title.value=\'\'}location.href=\''.ADMINSCRIPT.'?action=adv&operation=ad'.($type ? '&type='.rawurlencode($type) : '').'&title=\'+this.form.title.value+\'&starttime=\'+this.form.starttime.value+\'&endtime=\'+this.form.endtime.value+\'&target=\'+this.form.target.value+\'&orderby=\'+this.form.orderby.value;"> &nbsp;
		');
		showtablefooter();
		showformfooter();

	} else {

		if($_GET['delete']) {
			C::t('common_advertisement')->delete($_GET['delete']);
		}

		if(is_array($_GET['titlenew'])) {
			foreach($_GET['titlenew'] as $advid => $title) {
				C::t('common_advertisement')->update($advid, array(
					'available' => $_GET['availablenew'][$advid],
					'displayorder' => $_GET['displayordernew'][$advid],
					'title' => cutstr($_GET['titlenew'][$advid], 50)
				));
			}
		}

		updatecache('advs');
		updatecache('setting');

		cpmsg('adv_update_succeed', dreferer(), 'succeed');

	}

} elseif($operation == 'add' && !empty($_GET['type']) || $operation == 'edit' && !empty($_GET['advid'])) {

	if(!submitcheck('advsubmit')) {

		if($operation == 'edit') {
			$advid = $_GET['advid'];
			$adv = C::t('common_advertisement')->fetch($advid);
			if(!$adv) {
				cpmsg('advertisement_nonexistence', '', 'error');
			}
			$adv['parameters'] = dunserialize($adv['parameters']);
			$type = $adv['type'];
		} else {
			$adv['parameters']['style'] = 'code';
			$type = $_GET['type'];
		}

		$etype = explode(':', $type);
		if(count($etype) > 1 && preg_match('/^[\w\_:]+$/', $type)) {
			include_once DISCUZ_ROOT.'./source/plugin/'.$etype[0].'/adv/adv_'.$etype[1].'.php';
			$advclass = 'adv_'.$etype[1];
		} else {
			require_once libfile('adv/'.$type, 'class');
			$advclass = 'adv_'.$type;
		}
		$advclass = new $advclass;
		$advsetting = $advclass->getsetting();
		$advtitle = lang('adv/'.$type, $advclass->name).($type != 'custom' ? '' : ' '.$advclass->customname);
		$returnurl = 'action=adv&operation=ad'.(empty($_GET['from']) ? '&type='.$type.($type != 'custom' ? '' : '&customid='.$_GET['customid']) : '');

		$return = '<a href="'.ADMINSCRIPT.'?'.$returnurl.'">'.cplang('adv_list').(empty($_GET['from']) ? ' - '.$advtitle : '').'</a>';
		shownav('extended', 'adv_admin');
		showsubmenu($root.' &raquo; '.$return.' &raquo; '.($operation == 'edit' ? cplang('adv_edit') : cplang('adv_add')));
		echo '<br />';

		$targets = array();
		foreach($advclass->targets as $target) {
			if($target != 'custom') {
				$targets[] = array($target, $lang['adv_edit_targets_'.$target]);
			} else {
				$ets = explode("\t", $adv['targets']);
				$customv = array();
				foreach($ets as $et) {
					if(!in_array($et, $advclass->targets)) {
						$customv[] = $et;
					}
				}
				$targets[] = array($target, '<input title="'.cplang('adv_custom_target').'" name="advnew[targetcustom]" value="'.implode(',', $customv).'" />');
			}
		}
		$imagesizes = '';
		if(!empty($advclass->imagesizes)) {
			foreach($advclass->imagesizes as $size) {
				$imagesizes .= '<option value="'.$size.'">'.$size.'</option>';
			}
		}

		$adv['starttime'] = $adv['starttime'] ? dgmdate($adv['starttime'], 'Y-n-j') : '';
		$adv['endtime'] = $adv['endtime'] ? dgmdate($adv['endtime'], 'Y-n-j') : '';

		echo '<script type="text/javascript" src="static/js/calendar.js"></script>'.
			'<div class="colorbox"><h4>'.lang('adv/'.$type, $advclass->name).'</h4>'.
			'<table cellspacing="0" cellpadding="3"><tr><td>'.
			(count($etype) > 1 && preg_match('/^[\w\_:]+$/', $type) ? (file_exists(DISCUZ_ROOT.'./source/plugin/'.$etype[0].'/adv/adv_'.$etype[1].'.gif') ? '<img src="source/plugin/'.$etype[0].'/adv/adv_'.$etype[1].'.gif" />' : '')
			: (file_exists(DISCUZ_ROOT.'./static/image/admincp/'.$type.'.gif') ? '<img src="static/image/admincp/'.$type.'.gif" />' : '')).
			'</td><td valign="top">'.lang('adv/'.$type, $advclass->description).'</td></tr></table>'.
			'<div style="width:95%" align="right">'.lang('adv/'.$type, $advclass->copyright).'</div></div>';
		if($operation == 'edit') {
			echo '<input type="button" class="btn" onclick="$(\'previewbtn\').click()" name="jspreview" value="'.$lang['preview'].'">';
			echo '<div class="jswizard" id="advpreview" style="display:none"><iframe id="preview" name="preview" frameborder="0" allowtransparency="true" onload="this.style.height = (this.contentWindow.document.body.scrollHeight + 50) + \'px\'" width="95%" height="0"></iframe></div>';
		}

		showformheader("adv&operation=$operation".($operation == 'add' ? '&type='.$type : '&advid='.$advid), 'enctype');
		if($type == 'custom') {
			showhiddenfields(array('parameters[extra][customid]' => $_GET['customid']));
		}
		showhiddenfields(array('referer' => $returnurl));
		showtableheader();
		showtableheader(($operation == 'edit' ? cplang('adv_edit') : cplang('adv_add')).' - '.lang('adv/'.$type, $advclass->name), 'fixpadding');
		showsetting('adv_edit_title', 'advnew[title]', $adv['title'], 'text');
		if($type != 'custom') {
			showsetting('adv_edit_targets', array('advnew[targets]', $targets), explode("\t",$adv['targets']), 'mcheckbox');
		}

		if(is_array($advsetting)) {
			foreach($advsetting as $settingvar => $setting) {
				if(!empty($setting['value']) && is_array($setting['value'])) {
					foreach($setting['value'] as $k => $v) {
						$setting['value'][$k][1] = lang('adv/'.$type, $setting['value'][$k][1]);
					}
				}
				$varname = in_array($setting['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
					($setting['type'] == 'mselect' ? array('parameters[extra]['.$settingvar.'][]', $setting['value']) : array('parameters[extra]['.$settingvar.']', $setting['value']))
					: 'parameters['.$settingvar.']';
				$value = $adv['parameters']['extra'][$settingvar] != '' ? $adv['parameters']['extra'][$settingvar] : $setting['default'];
				$comment = lang('adv/'.$type, $setting['title'].'_comment');
				$comment = $comment != $setting['title'].'_comment' ? $comment : '';
				showsetting(lang('adv/'.$type, $setting['title']).':', $varname, $value, $setting['type'], '', 0, $comment);
			}
		}

		$adtypearray = array();
		$adtypes = array('code', 'text', 'image', 'flash');
		foreach($adtypes as $adtype) {
			$displayary = array();
			foreach($adtypes as $adtype1) {
				$displayary['style_'.$adtype1] = $adtype1 == $adtype ? '' : 'none';
			}
			$adtypearray[] = array($adtype, $lang['adv_style_'.$adtype], $displayary);
		}
		showsetting('adv_edit_starttime', 'advnew[starttime]', $adv['starttime'], 'calendar');
		showsetting('adv_edit_endtime', 'advnew[endtime]', $adv['endtime'], 'calendar');
		showsetting('adv_edit_style', array('advnew[style]', $adtypearray), $adv['parameters']['style'], 'mradio');

		showtagheader('tbody', 'style_code', $adv['parameters']['style'] == 'code');
		showtitle('adv_edit_style_code');
		showsetting('adv_edit_style_code_html', 'advnew[code][html]', $adv['parameters']['html'], 'textarea');
		showtagfooter('tbody');

		showtagheader('tbody', 'style_text', $adv['parameters']['style'] == 'text');
		showtitle('adv_edit_style_text');
		showsetting('adv_edit_style_text_title', 'advnew[text][title]', $adv['parameters']['title'], 'htmltext');
		showsetting('adv_edit_style_text_link', 'advnew[text][link]', $adv['parameters']['link'], 'text');
		showsetting('adv_edit_style_text_size', 'advnew[text][size]', $adv['parameters']['size'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'style_image', $adv['parameters']['style'] == 'image');
		showtitle('adv_edit_style_image');
		showsetting('adv_edit_style_image_url', 'advnewimage', $adv['parameters']['url'], 'filetext');
		showsetting('adv_edit_style_image_link', 'advnew[image][link]', $adv['parameters']['link'], 'text');
		showsetting('adv_edit_style_image_alt', 'advnew[image][alt]', $adv['parameters']['alt'], 'text');
		if($imagesizes) {
			$v = $adv['parameters']['width'].'x'.$adv['parameters']['height'];
			showsetting('adv_edit_style_image_size', '', '', '<select onchange="setsize(this.value, \'image\')"><option value="x">'.cplang('adv_edit_style_custom').'</option>'.str_replace('"'.$v.'"', '"'.$v.'" selected="selected"', $imagesizes).'</select>');
		}
		showsetting('adv_edit_style_image_width', 'advnew[image][width]', $adv['parameters']['width'], 'text', '', 0, '', 'id="imagewidth" onchange="setpreview(\'image\')"');
		showsetting('adv_edit_style_image_height', 'advnew[image][height]', $adv['parameters']['height'], 'text', '', 0, '', 'id="imageheight" onchange="setpreview(\'image\')"');
		showtagfooter('tbody');

		showtagheader('tbody', 'style_flash', $adv['parameters']['style'] == 'flash');
		showtitle('adv_edit_style_flash');
		showsetting('adv_edit_style_flash_url', 'advnewflash', $adv['parameters']['url'], 'filetext');
		if($imagesizes) {
			$v = $adv['parameters']['flash'].'x'.$adv['parameters']['flash'];
			showsetting('adv_edit_style_flash_size', '', '', '<select onchange="setsize(this.value, \'flash\')"><option>'.cplang('adv_edit_style_custom').'</option>'.str_replace('"'.$v.'"', '"'.$v.'" selected="selected"', $imagesizes).'</select>');
		}
		showsetting('adv_edit_style_flash_width', 'advnew[flash][width]', $adv['parameters']['width'], 'text', '', 0, '', 'id="flashwidth" onchange="setpreview(\'flash\')"');
		showsetting('adv_edit_style_flash_height', 'advnew[flash][height]', $adv['parameters']['height'], 'text', '', 0, '', 'id="flashheight" onchange="setpreview(\'flash\')"');
		showtagfooter('tbody');

		echo '<tr><td colspan="2">';
		if($operation == 'edit') {
			echo '<input id="previewbtn" type="button" class="btn" onclick="$(\'advpreview\').style.display=\'\';this.form.preview.value=1;this.form.target=\'preview\';this.form.submit();" name="jspreview" value="'.$lang['preview'].'">&nbsp; &nbsp;';
		}
		echo '<input type="submit" class="btn" name="advsubmit" onclick="this.form.preview.value=0;this.form.target=\'\'" value="'.$lang['submit'].'"><input name="preview" type="hidden" value="0"></td></tr>';
		showtablefooter();
		showtableheader();
		echo '<tr><td colspan="2" id="imagesizepreviewtd" style="border:0"><div id="imagesizepreview" style="display:none;border:1px dotted gray"></div></td></tr>';
		echo '<tr><td colspan="2" id="flashsizepreviewtd" style="border:0"><div id="flashsizepreview" style="display:none;border:1px dotted gray"></div></td></tr>';
		showtablefooter();
		showformfooter();
		echo '<script type="text/JavaScript">
		function setsize(v, o) {
			if(v) {
				var size = v.split(\'x\');
				$(o + \'width\').value = size[0];
				$(o + \'height\').value = size[1];
			}
			setpreview(o);
		}
		function setpreview(o) {
			var w = $(o + \'width\').value > 0 ? $(o + \'width\').value : 0;
			var h = $(o + \'height\').value > 0 ? $(o + \'height\').value : 0;
			var obj = $(o + \'sizepreview\');
			var tdobj = $(o + \'sizepreviewtd\');
			obj.style.display = \'\';
			obj.style.width = w + \'px\';
			obj.style.height = h + \'px\';
			tdobj.style.height = (parseInt(h) + 10) + \'px\';
		}';
		if($operation == 'edit' && ($adv['parameters']['style'] == 'image' || $adv['parameters']['style'] == 'flash')) {
			echo 'setpreview(\''.$adv['parameters']['style'].'\');';
		}
		echo '</script>';

	} else {

		if($operation == 'edit') {
			$advid = $_GET['advid'];
			$adv = C::t('common_advertisement')->fetch($advid);
			$type = $adv['type'];
			$adv['parameters'] = dunserialize($adv['parameters']);
		} else {
			$type = $_GET['type'];
		}

		$etype = explode(':', $type);
		if(count($etype) > 1 && preg_match('/^[\w\_:]+$/', $type)) {
			include_once DISCUZ_ROOT.'./source/plugin/'.$etype[0].'/adv/adv_'.$etype[1].'.php';
			$advclass = 'adv_'.$etype[1];
		} else {
			require_once libfile('adv/'.$type, 'class');
			$advclass = 'adv_'.$type;
		}
		$advclass = new $advclass;

		$advnew = $_GET['advnew'];

		$parameters = !empty($_GET['parameters']) ? $_GET['parameters'] : array();
		if(@in_array('custom', $advnew['targets'])) {
			$targetcustom = explode(',', $advnew['targetcustom']);
			$advnew['targets'] = array_merge($advnew['targets'], $targetcustom);
		}
		$advclass->setsetting($advnew, $parameters);

		$advnew['starttime'] = $advnew['starttime'] ? strtotime($advnew['starttime']) : 0;
		$advnew['endtime'] = $advnew['endtime'] ? strtotime($advnew['endtime']) : 0;

		if(!$advnew['title']) {
			cpmsg('adv_title_invalid', '', 'error');
		} elseif(strlen($advnew['title']) > 50) {
			cpmsg('adv_title_more', '', 'error');
		} elseif($advnew['endtime'] && ($advnew['endtime'] <= TIMESTAMP || $advnew['endtime'] <= $advnew['starttime'])) {
			cpmsg('adv_endtime_invalid', '', 'error');
		} elseif(($advnew['style'] == 'code' && !$advnew['code']['html'])
			|| ($advnew['style'] == 'text' && (!$advnew['text']['title'] || !$advnew['text']['link']))
			|| ($advnew['style'] == 'image' && (!$_FILES['advnewimage'] && !$_GET['advnewimage'] || !$advnew['image']['link']))
			|| ($advnew['style'] == 'flash' && (!$_FILES['advnewflash'] && !$_GET['advnewflash'] || !$advnew['flash']['width'] || !$advnew['flash']['height']))) {
			cpmsg('adv_parameter_invalid', '', 'error');
		}

		if($operation == 'add') {
			$advid = C::t('common_advertisement')->insert(array('available' => 1, 'type' => $type), true);
		}

		if($advnew['style'] == 'image' || $advnew['style'] == 'flash') {
			if($_FILES['advnew'.$advnew['style']]) {
				$upload = new discuz_upload();
				if($upload->init($_FILES['advnew'.$advnew['style']], 'common') && $upload->save(1)) {
					$advnew[$advnew['style']]['url'] = (!strstr($_G['setting']['attachurl'], '://') ? $_G['siteurl'] : '').$_G['setting']['attachurl'].'common/'.$upload->attach['attachment'];
				}
			} else {
				$advnew[$advnew['style']]['url'] = $_GET['advnew'.$advnew['style']];
			}
		}

		$advnew['displayorder'] = isset($advnew['displayorder']) ? implode("\t", $advnew['displayorder']) : '';
		$advnew['code'] = encodeadvcode($advnew);

		$extra = $type != 'custom' ? '' : '&customid='.$parameters['extra']['customid'];

		$advnew['parameters'] = serialize(array_merge(is_array($parameters) ? $parameters : array(), array('style' => $advnew['style']), $advnew['style'] == 'code' ? array() : $advnew[$advnew['style']], array('html' => $advnew['code']), array('displayorder' => $advnew['displayorder'])));

		C::t('common_advertisement')->update($advid, array(
			'title' => $advnew['title'],
			'targets' => $advnew['targets'],
			'parameters' => $advnew['parameters'],
			'code' => $advnew['code'],
			'starttime' => $advnew['starttime'],
			'endtime' => $advnew['endtime']
		));

		updatecache('advs');
		updatecache('setting');

		cpmsg('adv_succeed', 'action=adv&operation=edit&advid='.$advid.$extra, 'succeed');

	}

} elseif($operation == 'setting') {

	if(submitcheck('advsubmit')) {
		$_GET['advexpirationnew']['allow'] = $_GET['advexpirationnew']['allow'] && $_GET['advexpirationnew']['day'] > 0 && $_GET['advexpirationnew']['method'] && $_GET['advexpirationnew']['users'];
		C::t('common_setting')->update('advexpiration', $_GET['advexpirationnew']);
		updatecache('setting');
		cpmsg('setting_update_succeed', 'action=adv&operation=setting', 'succeed');
	} else {
		shownav('extended', 'adv_admin');
		showsubmenu('adv_admin', array(
			array('adv_admin_setting', 'adv&operation=setting', 1),
			array('adv_admin_list', 'adv&operation=list', 0),
			array('adv_admin_listall', 'adv&operation=ad', 0),
		));

		$advexpiration = C::t('common_setting')->fetch('advexpiration', true);
		showformheader('adv&operation=setting');
		showtableheader();
		showsetting('adv_setting_advexpiration', 'advexpirationnew[allow]', $advexpiration['allow'], 'radio', 0, 1);
		showsetting('adv_setting_advexpiration_day', 'advexpirationnew[day]', $advexpiration['day'], 'text');
		showsetting('adv_setting_advexpiration_method', array('advexpirationnew[method]', array(
			array('email', cplang('adv_setting_advexpiration_method_email')),
			array('notice', cplang('adv_setting_advexpiration_method_notice')),
		)), $advexpiration['method'], 'mcheckbox');
		showsetting('adv_setting_advexpiration_users', 'advexpirationnew[users]', $advexpiration['users'], 'textarea');
		showtagfooter('tbody');
		showsubmit('advsubmit');
		showtablefooter();
		showformfooter();
	}

} elseif($operation == 'list') {

	shownav('extended', 'adv_admin');
	showsubmenu('adv_admin', array(
		array('adv_admin_setting', 'adv&operation=setting', 0),
		array('adv_admin_list', 'adv&operation=list', 1),
		array('adv_admin_listall', 'adv&operation=ad', 0),
	));
	showtips('adv_list_tip');

	$advs = getadvs();
	showtableheader('', 'fixpadding');

	echo '<tr><td colspan="4">'.$lang['adv_targets'].': &nbsp;&nbsp; ';
	foreach($defaulttargets as $target) {
		echo '<a href="'.ADMINSCRIPT.'?action=adv&operation=ad&target='.$target.'">'.$lang['adv_edit_targets_'.$target].'</a> &nbsp;&nbsp; ';
	}

	$row = 4;
	$rowwidth = 1 / $row * 100;
	$customadv = $ads = array();
	$tmp = $advs['adv_custom.php'];
	unset($advs['adv_custom.php']);
	$advs['adv_custom.php'] = $tmp;
	foreach(C::t('common_advertisement')->fetch_all_type() as $ad) {
		$ads[$ad['type']] = $ad['count'];
	}
	foreach(C::t('common_advertisement')->fetch_all_by_type('custom') as $ad) {
		$parameters = dunserialize($ad['parameters']);
		$ads['custom_'.$parameters['extra']['customid']]++;
	}
	if($advs) {
		$i = $row;
		foreach($advs as $adv) {
			if($i == $row) {
				echo '<tr>';
			}
			if($adv['class'] == 'custom') {
				$customadv = $adv;
				$img = file_exists(DISCUZ_ROOT.'./static/image/admincp/'.$customadv['class'].'.gif') ? '<img src="static/image/admincp/'.$customadv['class'].'.gif" /><br />' : '';
				echo '<td width="'.$rowwidth.'%" class="hover" align="center">';
				echo $img.$lang['adv_custom_add'];
				showformheader("adv&operation=custom&do=add");
				echo '<input name="addcustom" class="txt" /><input name="submit" class="btn" type="submit" value="'.$lang['submit'].'" />';
				showformfooter();
				echo '</td>';
			} else {
				echo '<td width="'.$rowwidth.'%" class="hover" align="center"><a href="'.ADMINSCRIPT.'?action=adv&operation=ad&type='.$adv['class'].'">';
				$eclass = explode(':', $adv['class']);
				if(count($eclass) > 1) {
					echo file_exists(DISCUZ_ROOT.'./source/plugin/'.$eclass[0].'/adv/adv_'.$eclass[1].'.gif') ? '<img src="source/plugin/'.$eclass[0].'/adv/adv_'.$eclass[1].'.gif" /><br />' : '';
				} else {
					echo file_exists(DISCUZ_ROOT.'./static/image/admincp/'.$adv['class'].'.gif') ? '<img src="static/image/admincp/'.$adv['class'].'.gif" /><br />' : '';
				}
				echo $adv['name'].($ads[$adv['class']] ? '('.$ads[$adv['class']].')' : '').($adv['filemtime'] > TIMESTAMP - 86400 ? ' <font color="red">New!</font>' : '');
				echo '</a></td>';
			}
			$i--;
			if(!$i) {
				$i = $row;
			}
		}
		if($i != $row) {
			echo str_repeat('<td></td>', $i);
		}
	} else {
		showtablerow('', '', $lang['adv_nonexistence']);
	}
	if($customadv) {
		$img = file_exists(DISCUZ_ROOT.'./static/image/admincp/'.$customadv['class'].'.gif') ? '<img src="static/image/admincp/'.$customadv['class'].'.gif" /><br />' : '';
		$i = $row;
		foreach(C::t('common_advertisement_custom')->fetch_all_data() as $custom) {
			if($i == $row) {
				echo '<tr>';
			}
			echo '<td width="'.$rowwidth.'%" class="hover" align="center"><div id="op_'.$custom['id'].'"><a href="'.ADMINSCRIPT.'?action=adv&operation=ad&type='.$customadv['class'].'&customid='.$custom['id'].'">';
			echo $img.$lang['adv_custom'].' '.$custom['name'].($ads['custom_'.$custom['id']] ? '('.$ads['custom_'.$custom['id']].')' : '');
			echo '</a><br /><div class="right">';
			echo '<a onclick="ajaxget(this.href, \'op_'.$custom['id'].'\');return false;" href="'.ADMINSCRIPT.'?action=adv&operation=custom&do=edit&id='.$custom['id'].'">'.$lang['edit'].'</a>&nbsp;';
			echo '<a onclick="ajaxget(this.href, \'op_'.$custom['id'].'\');return false;" href="'.ADMINSCRIPT.'?action=adv&operation=custom&do=delete&id='.$custom['id'].'">'.$lang['delete'].'</a>';
			echo '</div></div></td>';
			$i--;
			if(!$i) {
				$i = $row;
			}
		}
		if($i != $row) {
			echo str_repeat('<td></td>', $i);
		}
	}
	echo '<tr>'.str_repeat('<td width="'.$rowwidth.'%"></td>', $row).'</tr>';
	showtablefooter();

} elseif($operation == 'custom') {

	if($do == 'add') {
		$addcustom = strip_tags($_GET['addcustom']);
		if($addcustom) {
			if(!($customid = C::t('common_advertisement_custom')->get_id_by_name($addcustom))) {
				$customid = C::t('common_advertisement_custom')->insert(array('name' => $addcustom), true);
			}
			dheader('location: '.ADMINSCRIPT.'?action=adv&operation=add&type=custom&customid='.$customid);
		}
	} elseif($do == 'edit') {
		$custom = C::t('common_advertisement_custom')->fetch($_GET['id']);
		$name = $custom['name'];
		if(!submitcheck('submit')) {
			ajaxshowheader();
			showformheader("adv&operation=custom&do=edit&id=$_GET[id]");
			echo $lang['adv_custom_edit'].'<br /><input name="customnew" class="txt" value="'.dhtmlspecialchars($name).'" />&nbsp;'.
				'<input name="submit" class="btn" type="submit" value="'.$lang['submit'].'" />&nbsp;'.
				'<input class="btn" type="button" onclick="location.href=\''.ADMINSCRIPT.'?action=adv&operation=list\'" value="'.$lang['cancel'].'" />';
			showformfooter();
			ajaxshowfooter();
		} else {
			$customnew = strip_tags($_GET['customnew']);
			if($_GET['customnew'] != $name) {
				C::t('common_advertisement_custom')->update($_GET['id'], array('name' => $customnew));
			}
		}
	} elseif($do == 'delete') {
		if(!submitcheck('submit')) {
			ajaxshowheader();
			showformheader("adv&operation=custom&do=delete&id=$_GET[id]");
			echo $lang['adv_custom_delete'].'<br /><input name="submit" class="btn" type="submit" value="'.$lang['delete'].'" />&nbsp;'.
				'<input class="btn" type="button" onclick="location.href=\''.ADMINSCRIPT.'?action=adv&operation=list\'" value="'.$lang['cancel'].'" />';
			showformfooter();
			ajaxshowfooter();
		} else {
			C::t('common_advertisement_custom')->delete($_GET['id']);
		}
	}
	dheader('location: '.ADMINSCRIPT.'?action=adv&operation=list');

}

function encodeadvcode($advnew) {
	switch($advnew['style']) {
		case 'code':
			$advnew['code'] = $advnew['code']['html'];
			break;
		case 'text':
			$advnew['code'] = '<a href="'.$advnew['text']['link'].'" target="_blank" '.($advnew['text']['size'] ? 'style="font-size: '.$advnew['text']['size'].'"' : '').'>'.$advnew['text']['title'].'</a>';
			break;
		case 'image':
			$advnew['code'] = '<a href="'.$advnew['image']['link'].'" target="_blank"><img src="'.$advnew['image']['url'].'"'.($advnew['image']['height'] ? ' height="'.$advnew['image']['height'].'"' : '').($advnew['image']['width'] ? ' width="'.$advnew['image']['width'].'"' : '').($advnew['image']['alt'] ? ' alt="'.$advnew['image']['alt'].'"' : '').' border="0"></a>';
			break;
		case 'flash':
			$advnew['code'] = '<embed width="'.$advnew['flash']['width'].'" height="'.$advnew['flash']['height'].'" src="'.$advnew['flash']['url'].'" type="application/x-shockwave-flash" wmode="transparent"></embed>';
			break;
	}
	return $advnew['code'];
}

function getadvs() {
	global $_G;
	$checkdirs = array_merge(array(''), $_G['setting']['plugins']['available']);
	$advs = array();
	foreach($checkdirs as $key) {
		if($key) {
			$dir = DISCUZ_ROOT.'./source/plugin/'.$key.'/adv';
		} else {
			$dir = DISCUZ_ROOT.'./source/class/adv';
		}
		if(!file_exists($dir)) {
			continue;
		}
		$advdir = dir($dir);
		while($entry = $advdir->read()) {
			if(!in_array($entry, array('.', '..')) && preg_match("/^adv\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($dir.'/'.$entry)) {
				@include_once $dir.'/'.$entry;
				$advclass = substr($entry, 0, -4);
				if(class_exists($advclass)) {
					$adv = new $advclass();
					$script = substr($advclass, 4);
					$script = ($key ? $key.':' : '').$script;
					$advs[$entry] = array(
						'class' => $script,
						'name' => lang('adv/'.$script, $adv->name),
						'version' => $adv->version,
						'copyright' => lang('adv/'.$script, $adv->copyright),
						'filemtime' => @filemtime($dir.'/'.$entry)
					);
				}
			}
		}
	}
	uasort($advs, 'filemtimesort');
	return $advs;
}

?>