<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_tools.php 33301 2013-05-23 03:10:20Z andyzheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if($operation == 'updatecache') {

	$step = max(1, intval($_GET['step']));
	shownav('tools', 'nav_updatecache');
	showsubmenusteps('nav_updatecache', array(
		array('nav_updatecache_confirm', $step == 1),
		array('nav_updatecache_verify', $step == 2),
		array('nav_updatecache_completed', $step == 3)
	));

	showtips('tools_updatecache_tips');

	if($step == 1) {
		cpmsg("<input type=\"checkbox\" name=\"type[]\" value=\"data\" id=\"datacache\" class=\"checkbox\" checked /><label for=\"datacache\">".$lang['tools_updatecache_data']."</label><input type=\"checkbox\" name=\"type[]\" value=\"tpl\" id=\"tplcache\" class=\"checkbox\" checked /><label for=\"tplcache\">".$lang['tools_updatecache_tpl']."</label><input type=\"checkbox\" name=\"type[]\" value=\"blockclass\" id=\"blockclasscache\" class=\"checkbox\" /><label for=\"blockclasscache\">".$lang['tools_updatecache_blockclass'].'</label><input type="checkbox" name="type[]" value="csscache" id="csscache" class="checkbox" /><label for="csscache">'.$lang['styles_csscache_update'].'</label>', 'action=tools&operation=updatecache&step=2', 'form', '', FALSE);
	} elseif($step == 2) {
		$type = implode('_', (array)$_GET['type']);
		cpmsg(cplang('tools_updatecache_waiting'), "action=tools&operation=updatecache&step=3&type=$type", 'loading', '', FALSE);
	} elseif($step == 3) {
		$type = explode('_', $_GET['type']);
		if(in_array('data', $type)) {
			updatecache();
			require_once libfile('function/group');
			$groupindex['randgroupdata'] = $randgroupdata = grouplist('lastupdate', array('ff.membernum', 'ff.icon'), 80);
			$groupindex['topgrouplist'] = $topgrouplist = grouplist('activity', array('f.commoncredits', 'ff.membernum', 'ff.icon'), 10);
			$groupindex['updateline'] = TIMESTAMP;
			$groupdata = C::t('forum_forum')->fetch_group_counter();
			$groupindex['todayposts'] = $groupdata['todayposts'];
			$groupindex['groupnum'] = $groupdata['groupnum'];
			savecache('groupindex', $groupindex);
			C::t('forum_groupfield')->truncate();
			savecache('forum_guide', '');
			if($_G['setting']['grid']['showgrid']) {
				savecache('grids', array());
			}
		}
		if((in_array('tpl', $type) && $_G['config']['output']['tplrefresh']) || in_array('csscache', $type)) {
			cleartemplatecache();
		}
		if(in_array('blockclass', $type)) {
			include_once libfile('function/block');
			blockclass_cache();
		}
		if(in_array('csscache', $type)) {
			updatecache(array('setting', 'styles'));
			loadcache('style_default', true);
			updatecache('updatediytemplate');
		}
		cpmsg('update_cache_succeed', '', 'succeed', '', FALSE);
	}

} elseif($operation == 'fileperms') {

	$step = max(1, intval($_GET['step']));

	shownav('tools', 'nav_fileperms');
	showsubmenusteps('nav_fileperms', array(
		array('nav_fileperms_confirm', $step == 1),
		array('nav_fileperms_verify', $step == 2),
		array('nav_fileperms_completed', $step == 3)
	));

	if($step == 1) {
		cpmsg(cplang('fileperms_check_note'), 'action=tools&operation=fileperms&step=2', 'button', '', FALSE);
	} elseif($step == 2) {
		cpmsg(cplang('fileperms_check_waiting'), 'action=tools&operation=fileperms&step=3', 'loading', '', FALSE);
	} elseif($step == 3) {

		showtips('fileperms_tips');

		$entryarray = array(
			'data',
			'data/attachment',
			'data/attachment/album',
			'data/attachment/category',
			'data/attachment/common',
			'data/attachment/forum',
			'data/attachment/group',
			'data/attachment/portal',
			'data/attachment/profile',
			'data/attachment/swfupload',
			'data/attachment/temp',
			'data/cache',
			'data/log',
			'data/template',
			'data/threadcache',
			'data/diy'
		);

		$result = '';
		foreach($entryarray as $entry) {
			$fullentry = DISCUZ_ROOT.'./'.$entry;
			if(!is_dir($fullentry) && !file_exists($fullentry)) {
				continue;
			} else {
				if(!dir_writeable($fullentry)) {
					$result .= '<li class="error">'.(is_dir($fullentry) ? $lang['dir'] : $lang['file'])." ./$entry $lang[fileperms_unwritable]</li>";
				}
			}
		}
		$result = $result ? $result : '<li>'.$lang['fileperms_check_ok'].'</li>';
		echo '<div class="colorbox"><ul class="fileperms">'.$result.'</ul></div>';
	}
}

function jsinsertunit() {

?>
<script type="text/JavaScript">
function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function insertunit(text, obj) {
	if(!obj) {
		obj = 'jstemplate';
	}
	$(obj).focus();
	if(!isUndefined($(obj).selectionStart)) {
		var opn = $(obj).selectionStart + 0;
		$(obj).value = $(obj).value.substr(0, $(obj).selectionStart) + text + $(obj).value.substr($(obj).selectionEnd);
	} else if(document.selection && document.selection.createRange) {
		var sel = document.selection.createRange();
		sel.text = text.replace(/\r?\n/g, '\r\n');
		sel.moveStart('character', -strlen(text));
	} else {
		$(obj).value += text;
	}
}
</script>
<?php

}

?>