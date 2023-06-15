<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_grid.php 31813 2012-10-11 08:13:23Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
cpheader();

if(!submitcheck('gridssubmit')) {
	$grid = C::t('common_setting')->fetch('grid', true);
	shownav('forum', 'forums_grid');
	showsubmenu('forums_grid');
	showtips('forums_grid_tips');
	showformheader('grid');
	showtableheader('');
	showsetting('forums_grid_show_grid', 'grid[showgrid]', $grid['showgrid'], 'radio', '', 1);
	showsetting('forums_grid_style_type', array(0 => 'grid[gridtype]', array(array('0', $lang['forums_grid_style_image']), array(1, $lang['forums_grid_style_text']))), $grid['gridtype'], 'select');
	showsetting('forums_grid_text_length', 'grid[textleng]', $grid['textleng'], 'text');
	include_once libfile('function/forumlist');
	$forumselect = '<select name="grid[fids][]" multiple="multiple" size="10"><option value="0"'.(in_array(0, $grid['fids']) ? ' selected' : '').'>'.$lang['all'].'</option>'.forumselect(FALSE, 0, $grid['fids'], TRUE).'</select>';
	showsetting('forums_grid_data_source', '', '', $forumselect);
	showsetting('forums_grid_high_light', 'grid[highlight]', $grid['highlight'], 'radio');
	showsetting('forums_grid_target_blank', 'grid[targetblank]', $grid['targetblank'], 'radio');
	showsetting('forums_grid_show_tips', 'grid[showtips]', $grid['showtips'], 'radio');
	showsetting('forums_grid_cache_life', 'grid[cachelife]', $grid['cachelife'], 'text');
	showtagfooter('tbody');
	showsubmit('gridssubmit');
	showtablefooter();
	showformfooter();
} else {
	$_POST['grid']['fids'] = in_array(0, $_POST['grid']['fids']) ? array(0) : $_POST['grid']['fids'];
	C::t('common_setting')->update('grid', $_POST['grid']);
	updatecache('setting');
	C::t('common_syscache')->delete('grids');
	cpmsg('setting_update_succeed', 'action=grid', 'succeed');
}
?>