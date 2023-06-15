<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: resource_setting.inc.php 34815 2014-08-07 02:04:50Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

define('RSELF', 'action=plugins&operation=config&identifier=wechat&pmod=resource_setting&ac=');
$select = array(-1 => lang('plugin/wechat', 'resource_type_all'), 0 => lang('plugin/wechat', 'resource_type_s'), 1 => lang('plugin/wechat', 'resource_type_m'));

$ac = !empty($_GET['ac']) ? $_GET['ac'] : '';
$filter = isset($_GET['filter']) ? intval($_GET['filter']) : -1;

if(!$_POST) {

	if(!$ac) {

		$ppp = 9;
		$page = max(1, $_GET['page']);
		$start = ($page - 1) * $ppp;
		$count = C::t('#wechat#mobile_wechat_resource')->count_by_type($filter >= 0 ? $filter : null);
		$resource = C::t('#wechat#mobile_wechat_resource')->fetch_by_type($filter >= 0 ? $filter : null, $start, $ppp);
		$multi = multi($count, $ppp, $page, ADMINSCRIPT.'?'.RSELF.'&filter='.$filter);

		wxbox_style();
echo <<<EOF
<script>
var cookiepre = '{$_G[config][cookie][cookiepre]}', cookiedomain = '{$_G[config][cookie][cookiedomain]}', cookiepath = '{$_G[config][cookie][cookiepath]}';
function merge(id, type) {
	mids = getcookie('wechat_rids');
	var re = new RegExp('_' + id + '_', "g");
	mids = mids.replace(re, '');
	if(type) {
		mids += '_' + id + '_';
	}
	setcookie('wechat_rids', mids);
	showmerge();
}
function clearmerge() {
	setcookie('wechat_rids', '', -1);
	location.href = location.href;
}
function showmerge() {
	$('mergebtn').style.display = getcookie('wechat_rids') ? '' : 'none';
}
</script>
EOF;

		showtableheader();
		$wechat_rids = explode('_', $_G['cookie']['wechat_rids']);

		foreach($select as $k => &$row) {
			$row = '<option value="'.$k.'"'.($k == $filter ? ' selected' : '').'>'.$row.'</option>';
		}
		$select = '<select onchange="location.href=\''.ADMINSCRIPT.'?'.RSELF.'&filter=\' + this.value">'.implode('', $select).'</select> ';
		foreach($resource as $row) {
			$row = dhtmlspecialchars($row);
			echo '<table class="left tb2 wxbox">'
				. '<tr class="header"><th class="partition">'.$row['name'].'<br />'
				. '<div>'
				. '<span>'
				. (!$row['type']
				? '<label><input class="pc" type="checkbox" '.(in_array($row['id'], $wechat_rids)
					? 'checked '
					: '')
					. 'onclick="merge('.$row['id'].', this.checked)"><a>'.lang('plugin/wechat', 'resource_merge').'</a></label> '
					: '<a href="'.ADMINSCRIPT.'?'.RSELF.'addmerge&id='.$row['id'].'" class="addtr">'.lang('plugin/wechat', 'resource_append_merge').'</a> ')
				. '<a href="'.ADMINSCRIPT.'?'.RSELF.'edit&id='.$row['id'].'">'.lang('plugin/wechat', 'resource_edit').'</a> '
				. '<a href="'.ADMINSCRIPT.'?'.RSELF.'delete&id='.$row['id'].'">'.lang('plugin/wechat', 'resource_del').'</a>'
				. '</span>'
				. dgmdate($row['dateline']).'</div></th></tr>'
				. (!$row['type']
				? '<tr><td class="l1" title="'.$row['data']['title'].'">'.$row['data']['title'].'</td></tr>'
				. '<tr><td class="l2"><div>'.($row['data']['pic'] ? '<img src="'.$row['data']['pic'].'" />' : '').'</div></td></tr>'
				. '<tr><td class="l3">'.$row['data']['desc'].'</td></tr>'
				: '<tr><td class="l1">'.lang('plugin/wechat', 'resource_type_m').'</td></tr>'
				. '<tr><td class="l2">'.lang('plugin/wechat', 'resource_merge_count', array('count' => count($row['data']['mergeids']))).'<br /><a href="'.ADMINSCRIPT.'?'.RSELF.'edit&id='.$row['id'].'">'.lang('plugin/wechat', 'resource_view').'</a></td></tr>'
				. '<tr><td class="l3"></td></tr>')
				. '</table>';
		}
		showtablefooter();
		echo '<br style="clear:both"><div class="right pg">'.$multi.'</div>';
		echo $select.'<a href="'.ADMINSCRIPT.'?'.RSELF.'add" class="addtr">'.lang('plugin/wechat', 'resource_add').'</a> &nbsp;'
			. '<span id="mergebtn" '.($_G['cookie']['wechat_rids'] ? '' : ' style="display:none"').'>'
			. '<a href="'.ADMINSCRIPT.'?'.RSELF.'addmerge" class="addtr">'.lang('plugin/wechat', 'resource_type_m').'</a>'
			. '<a href="javascript:;" onclick="clearmerge()" class="lightfont">'.lang('plugin/wechat', 'resource_cancel_merge').'</a>'
			. '</span>';

	} elseif($ac == 'add') {

		showformheader('plugins&operation=config&identifier=wechat&pmod=resource_setting&ac=add', 'enctype');
		showtableheader();
		showsetting(lang('plugin/wechat', 'resource_name'), 'name', '', 'text');
		showsetting(lang('plugin/wechat', 'resource_title'), 'data[title]', '', 'text');
		showsetting(lang('plugin/wechat', 'resource_pic'), 'pic', '', 'filetext');
		showsetting(lang('plugin/wechat', 'resource_desc'), 'data[desc]', '', 'textarea');
		showsetting(lang('plugin/wechat', 'resource_content'), 'data[content]', '', 'textarea');
		showsetting(lang('plugin/wechat', 'resource_url'), 'data[url]', '', 'text');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();

	} elseif($ac == 'addmerge') {

		$wechat_rids = explode('_', $_G['cookie']['wechat_rids']);
		$resource = C::t('#wechat#mobile_wechat_resource')->fetch_all($wechat_rids);

		if($_GET['id']) {
			$aresource = C::t('#wechat#mobile_wechat_resource')->fetch($_GET['id']);
			foreach($resource as $row) {
				if(!$aresource['data']['mergeids'][$row['id']]) {
					$aresource['data']['mergeids'][$row['id']] = 0;
				}
			}
			asort($aresource['data']['mergeids']);
			$data = array(
				'data' => $aresource['data'],
			);
			C::t('#wechat#mobile_wechat_resource')->update($_GET['id'], $data);

			dsetcookie('wechat_rids', '', -1);
			dheader('location: '.ADMINSCRIPT.'?'.RSELF.'edit&id='.$_GET['id']);
		}

		showformheader('plugins&operation=config&identifier=wechat&pmod=resource_setting&ac=add');
		showtableheader();
		showsetting(lang('plugin/wechat', 'resource_name'), 'name', '', 'text');
		showtablefooter();
		showtableheader();
		echo '<tr class="header"><th>'.lang('plugin/wechat', 'resource_name').'</th><th>'.lang('plugin/wechat', 'resource_order').'</th></tr>';
		$i = 0;
		foreach($resource as $row) {
			echo '<tr><td><a href="'.ADMINSCRIPT.'?'.RSELF.'edit&id='.$row['id'].'">'.$row['name'].'</a></td><td><input name="data[mergeids]['.$row['id'].']" value="'.(++$i).'"></td></tr>';
		}
		showsubmit('addmergesubmit');
		showtablefooter();
		showformfooter();

	} elseif($ac == 'edit') {

		$resource = C::t('#wechat#mobile_wechat_resource')->fetch($_GET['id']);
		if(!$resource) {
			cpmsg(lang('plugin/wechat', 'resource_msg_nofound'), '', 'error');
		}

		if(!$resource['type']) {

			showformheader('plugins&operation=config&identifier=wechat&pmod=resource_setting&ac=edit&id='.$_GET['id'], 'enctype');
			showtableheader();
			showsetting(lang('plugin/wechat', 'resource_name'), 'name', $resource['name'], 'text');
			showsetting(lang('plugin/wechat', 'resource_title'), 'data[title]', $resource['data']['title'], 'text');
			showsetting(lang('plugin/wechat', 'resource_pic'), 'pic', $resource['data']['pic'], 'filetext');
			showsetting(lang('plugin/wechat', 'resource_desc'), 'data[desc]', $resource['data']['desc'], 'textarea');
			showsetting(lang('plugin/wechat', 'resource_content'), 'data[content]', $resource['data']['content'], 'textarea');
			showsetting(lang('plugin/wechat', 'resource_url'), 'data[url]', $resource['data']['url'], 'text');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$mergeids = array_keys($resource['data']['mergeids']);
			if(!$mergeids) {
				cpmsg(lang('plugin/wechat', 'resource_msg_nofound'), '', 'error');
			}
			$sresource = C::t('#wechat#mobile_wechat_resource')->fetch_all($mergeids);

			showformheader('plugins&operation=config&identifier=wechat&pmod=resource_setting&ac=edit&id='.$_GET['id']);
			showtableheader();
			showsetting(lang('plugin/wechat', 'resource_name'), 'name', $resource['name'], 'text');
			showtablefooter();
			showtableheader();

			$i = 0;
			wxbox_style();
			foreach($resource['data']['mergeids'] as $id => $order) {
				$row = dhtmlspecialchars($sresource[$id]);
				echo '<table class="left tb2 wxbox">'
				. '<tr class="header"><th class="partition">'.$row['name'].'<br />'
				. '<div>'
				. '<span>'
				. '<a href="'.ADMINSCRIPT.'?'.RSELF.'edit&id='.$row['id'].'" target="_blank">'.lang('plugin/wechat', 'resource_edit').'</a> '
				. '<a href="'.ADMINSCRIPT.'?'.RSELF.'removemerge&id='.$row['id'].'&fromid='.$_GET['id'].'">'.lang('plugin/wechat', 'resource_remove').'</a> '
				. lang('plugin/wechat', 'resource_order').'<input name="data[mergeids]['.$id.']" class="txt" value="'.$order.'">'
				. '</span>'
				. dgmdate($row['dateline']).'</div></th></tr>'
				. '<tr><td class="l1" title="'.$row['data']['title'].'">'.$row['data']['title'].'</td></tr>'
				. '<tr><td class="l2"><div>'.($row['data']['pic'] ? '<img src="'.$row['data']['pic'].'" />' : '').'</div></td></tr>'
				. '<tr><td class="l3">'.$row['data']['desc'].'</td></tr>'
				. '</table>';
			}
			showtablefooter();
			showtableheader();
			showsubmit('editmergesubmit');
			showtablefooter();
			showformfooter();

		}

	} elseif($ac == 'delete') {
		cpmsg(lang('plugin/wechat', 'resource_msg_del'), RSELF.'&id='.$_GET['id'].'&delsubmit=yes', 'form');
	} elseif($ac == 'removemerge') {
		cpmsg(lang('plugin/wechat', 'resource_msg_remove'), RSELF.'&id='.$_GET['id'].'&fromid='.$_GET['fromid'].'&removesubmit=yes', 'form');
	} elseif($ac == 'select') {
		include template('common/header_ajax');

		$ppp = 6;
		$page = max(1, $_GET['page']);
		$start = ($page - 1) * $ppp;
		$count = C::t('#wechat#mobile_wechat_resource')->count_by_type($filter >= 0 ? $filter : null);
		$resource = C::t('#wechat#mobile_wechat_resource')->fetch_by_type($filter >= 0 ? $filter : null, $start, $ppp);
		$multi = multi($count, $ppp, $page, ADMINSCRIPT.'?action=plugins&operation=config&identifier=wechat&pmod=resource_setting&ac=select&filter='.$filter);
		wxbox_style();

		foreach($select as $k => &$row) {
			$row = '<option value="'.$k.'"'.($k == $filter ? ' selected' : '').'>'.$row.'</option>';
		}
		$select = '<select onchange="ajaxget(\''.ADMINSCRIPT.'?action=plugins&operation=config&identifier=wechat&pmod=resource_setting&ac=select&filter=\' + this.value, \'rsel_content\')">'.implode('', $select).'</select> ';
		echo '<div class="pg"><div class="right">'.$multi.'</div>'.$select.'</div>';

		showtableheader();
		$wechat_rids = explode('_', $_G['cookie']['wechat_rids']);
		foreach($resource as $row) {
			$row = dhtmlspecialchars($row);
			echo '<table class="left tb2 wxbox hover" onclick="selResource('.$row['id'].', \''.$row['name'].'\')">'
				. '<tr class="header"><th class="partition">'.$row['name'].'</tr>'
				. (!$row['type']
				? '<tr><td class="l1" title="'.$row['data']['title'].'">'.$row['data']['title'].'</td></tr>'
				. '<tr><td class="l2"><div>'.($row['data']['pic'] ? '<img src="'.$row['data']['pic'].'" width="290" />' : '').'</div></td></tr>'
				: '<tr><td class="l1">'.lang('plugin/wechat', 'resource_type_m').'</td></tr>'
				. '<tr><td class="l2">'.lang('plugin/wechat', 'resource_merge_count', array('count' => count($row['data']['mergeids']))).'<br /><a href="javascript:;" onclick="window.open(\''.ADMINSCRIPT.'?'.RSELF.'edit&id='.$row['id'].'\');doane(event)" target="_blank">'.lang('plugin/wechat', 'resource_view').'</a></td></tr>')
				. '</table>';
		}
		showtablefooter();

		include template('common/footer_ajax');
	}

} elseif(submitcheck('addsubmit')) {

	if(dstrlen($_GET['data']['desc'], CHARSET) > 120) {
		cpmsg(lang('plugin/wechat', 'resource_msg_desc_toolong'), '', 'error');
	}
	if($_FILES['pic']['tmp_name']) {
		$upload = new discuz_upload();
		if(!getimagesize($_FILES['pic']['tmp_name']) || !$upload->init($_FILES['pic'], 'common', random(3, 1), random(8)) || !$upload->save()) {
			cpmsg($upload->errormessage(), '', 'error');
		}
		$_GET['data']['pic'] = (preg_match('/^https?:/', $_G['setting']['attachurl']) ? '' : $_G['siteurl']).$_G['setting']['attachurl'].'common/'.$upload->attach['attachment'];
		$_GET['data']['local'] = $upload->attach['attachment'];
	} else {
		$_GET['data']['pic'] = $_GET['pic'];
	}
	$data = array(
	    'name' => $_GET['name'],
	    'data' => $_GET['data'],
	);
	C::t('#wechat#mobile_wechat_resource')->insert($data);

	cpmsg('setting_update_succeed', RSELF, 'succeed');

} elseif(submitcheck('editsubmit')) {

	$resource = C::t('#wechat#mobile_wechat_resource')->fetch($_GET['id']);
	if(!$resource) {
		cpmsg(lang('plugin/wechat', 'resource_msg_nofound'), '', 'error');
	}

	if(dstrlen($_GET['data']['desc'], CHARSET) > 120) {
		cpmsg(lang('plugin/wechat', 'resource_msg_desc_toolong'), '', 'error');
	}
	if($_FILES['pic']['tmp_name']) {
		$upload = new discuz_upload();
		if(!getimagesize($_FILES['pic']['tmp_name']) || !$upload->init($_FILES['pic'], 'common', random(3, 1), random(8)) || !$upload->save()) {
			cpmsg($upload->errormessage(), '', 'error');
		}
		$_GET['data']['pic'] = (preg_match('/^https?:/', $_G['setting']['attachurl']) ? '' : $_G['siteurl']).$_G['setting']['attachurl'].'common/'.$upload->attach['attachment'];
		$_GET['data']['local'] = $upload->attach['attachment'];
		@unlink($_G['setting']['attachdir'].'common/'.$resource['data']['local']);
	} else {
		$_GET['data']['pic'] = $_GET['pic'];
	}
	$data = array(
	    'name' => $_GET['name'],
	    'data' => $_GET['data'],
	);
	C::t('#wechat#mobile_wechat_resource')->update($_GET['id'], $data);

	cpmsg('setting_update_succeed', RSELF, 'succeed');

} elseif(submitcheck('delsubmit')) {

	$resource = C::t('#wechat#mobile_wechat_resource')->fetch($_GET['id']);
	if(!$resource) {
		cpmsg(lang('plugin/wechat', 'resource_msg_nofound'), '', 'error');
	}

	if($resource['data']['local']) {
		@unlink($_G['setting']['attachdir'].'common/'.$resource['data']['local']);
	}
	C::t('#wechat#mobile_wechat_resource')->delete($_GET['id']);

	cpmsg('setting_update_succeed', RSELF, 'succeed');

} elseif(submitcheck('addmergesubmit')) {

	asort($_GET['data']['mergeids']);
	$data = array(
	    'name' => $_GET['name'],
	    'type' => 1,
	    'data' => $_GET['data'],
	);
	C::t('#wechat#mobile_wechat_resource')->insert($data);
	dsetcookie('wechat_rids', '', -1);

	cpmsg('setting_update_succeed', RSELF, 'succeed');

} elseif(submitcheck('editmergesubmit')) {

	asort($_GET['data']['mergeids']);
	$data = array(
	    'name' => $_GET['name'],
	    'data' => $_GET['data'],
	);
	C::t('#wechat#mobile_wechat_resource')->update($_GET['id'], $data);
	dsetcookie('wechat_rids', '', -1);

	cpmsg('setting_update_succeed', RSELF.'edit&id='.$_GET['id'], 'succeed');

} elseif(submitcheck('removesubmit')) {

	$resource = C::t('#wechat#mobile_wechat_resource')->fetch($_GET['fromid']);
	if(!$resource) {
		cpmsg(lang('plugin/wechat', 'resource_msg_nofound'), '', 'error');
	}

	unset($resource['data']['mergeids'][$_GET['id']]);
	$data = array(
	    'data' => $resource['data'],
	);
	C::t('#wechat#mobile_wechat_resource')->update($_GET['fromid'], $data);

	cpmsg('setting_update_succeed', RSELF.'edit&id='.$_GET['fromid'], 'succeed');

}

function wxbox_style() {
echo <<<EOF
<style>
.wxbox { table-layout: fixed; margin:0 5px 10px 0;width:300px;height:300px; }
.wxbox td { padding:0 2px;border: none; }
.wxbox th { height: 50px; white-space: nowrap; }
.wxbox th div { font-weight:normal; }
.wxbox th div span { float:right; }
.wxbox th div .txt { width: 40px; }
.wxbox .l1 { height: 30px; line-height: 30px;overflow:hidden;text-overflow:ellipsis;white-space: nowrap; }
.wxbox .l2 { text-align:center;	}
.wxbox .l2 img { width: 290px; }
.wxbox .l2 div { width: 290px;height: 160px; display:block; overflow:hidden; }
.wxbox .l3 { height: 80px; vertical-align:top;line-height:25px; }
.wxbox.hover { height:220px; }
.wxbox.hover th { height:20px; }
</style>
EOF;

}

?>