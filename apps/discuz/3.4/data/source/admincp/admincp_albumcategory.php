<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_albumcategory.php 24658 2011-09-29 09:39:40Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

cpheader();
$operation = $operation == 'delete' ? 'delete' : 'list';

loadcache('albumcategory');
$category = $_G['cache']['albumcategory'];

if($operation == 'list') {

	if(!submitcheck('editsubmit')) {

		shownav('portal', 'albumcategory');
		showsubmenu('albumcategory',  array(
			array('list', 'albumcategory', 1)
		));

		showformheader('albumcategory');
		showtableheader();
		showsetting('system_category_stat', 'settingnew[albumcategorystat]', $_G['setting']['albumcategorystat'], 'radio', '', 1);
		showsetting('system_category_required', 'settingnew[albumcategoryrequired]', $_G['setting']['albumcategoryrequired'], 'radio', '');
		echo '<tr><td colspan="2">';
		showtableheader();
		showsubtitle(array('order', 'albumcategory_name', 'albumcategory_num', 'operation'));
		foreach ($category as $key=>$value) {
			if($value['level'] == 0) {
				echo showcategoryrow($key, 0, '');
			}
		}
		echo '<tr><td class="td25">&nbsp;</td><td colspan="3"><div><a class="addtr" onclick="addrow(this, 0, 0)" href="###">'.cplang('albumcategory_addcategory').'</a></div></td></tr>';
		showtablefooter();
		echo '</td></tr>';

		showtableheader('', 'notop');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

		$langs = array();
		$keys = array('albumcategory_addcategory', 'albumcategory_addsubcategory', 'albumcategory_addthirdcategory');
		foreach ($keys as $key) {
			$langs[$key] = cplang($key);
		}
		echo <<<SCRIPT
<script type="text/Javascript">
var rowtypedata = [
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="parentboard"><input type="text" class="txt" value="$lang[albumcategory_addcategory]" name="newname[{1}][]"/></div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="board"><input type="text" class="txt" value="$lang[albumcategory_addsubcategory]" name="newname[{1}][]"/></div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="childboard"><input type="text" class="txt" value="$lang[albumcategory_addthirdcategory]" name="newname[{1}][]"/></div>']],
];
</script>
SCRIPT;

	} else {

		if($_POST['name']) {
			foreach($_POST['name'] as $key=>$value) {
				$sets = array();
				$value = trim($value);
				if($category[$key] && $category[$key]['catname'] != $value) {
					$sets['catname'] = $value;
				}
				if($category[$key] && $category[$key]['displayorder'] != $_POST['order'][$key]) {
					$sets['displayorder'] = $_POST['order'][$key] ? $_POST['order'][$key] : '0';
				}
				if($sets) {
					C::t('home_album_category')->update($key, $sets);
				}
			}
		}
		if($_POST['newname']) {
			foreach ($_POST['newname'] as $upid=>$names) {
				foreach ($names as $nameid=>$name) {
					C::t('home_album_category')->insert(array('upid' => $upid, 'catname' => trim($name), 'displayorder'=>intval($_POST['neworder'][$upid][$nameid])));
				}
			}
		}

		if($_POST['settingnew']) {
			$_POST['settingnew'] = array_map('intval', $_POST['settingnew']);
			C::t('common_setting')->update_batch($_POST['settingnew']);
			updatecache('setting');
		}

		include_once libfile('function/cache');
		updatecache('albumcategory');

		cpmsg('albumcategory_update_succeed', 'action=albumcategory', 'succeed');
	}

} elseif($operation == 'delete') {

	if(!$_GET['catid'] || !$category[$_GET['catid']]) {
		cpmsg('albumcategory_catgory_not_found', '', 'error');
	}
	if(!submitcheck('deletesubmit')) {
		$a_count = C::t('home_album')->count_by_catid($_GET['catid']);
		if(!$a_count && empty($category[$_GET['catid']]['children'])) {
			C::t('home_album_category')->delete($_GET['catid']);
			include_once libfile('function/cache');
			updatecache('albumcategory');
			cpmsg('albumcategory_delete_succeed', 'action=albumcategory', 'succeed');
		}

		shownav('portal', 'albumcategory');
		showsubmenu('albumcategory',  array(
			array('list', 'albumcategory', 0),
			array('delete', 'albumcategory&operation=delete&catid='.$_GET['catid'], 1)
		));

		showformheader('albumcategory&operation=delete&catid='.$_GET['catid']);
		showtableheader();
		if($category[$_GET[catid]]['children']) {
			showsetting('albumcategory_subcategory_moveto', '', '',
				'<input type="radio" name="subcat_op" value="trash" id="subcat_op_trash" checked="checked" />'.
				'<label for="subcat_op_trash" />'.cplang('albumcategory_subcategory_moveto_trash').'</label>'.
				'<input type="radio" name="subcat_op" value="parent" id="subcat_op_parent" checked="checked" />'.
				'<label for="subcat_op_parent" />'.cplang('albumcategory_subcategory_moveto_parent').'</label>'
			);
		}
		include_once libfile('function/portalcp');
		showsetting('albumcategory_article_moveto', '', '', category_showselect('album', 'tocatid', false, $category[$_GET['catid']]['upid']));
		showsubmit('deletesubmit');
		showtablefooter();
		showformfooter();

	} else {

		if($_POST['tocatid'] == $_GET['catid']) {
			cpmsg('albumcategory_move_category_failed', 'action=albumcategory', 'error');
		}
		$delids = array($_GET['catid']);
		if($category[$_GET['catid']]['children']) {
			if($_POST['subcat_op'] == 'parent') {
				$upid = intval($category[$_GET['catid']]['upid']);
				C::t('home_album_category')->update($category[$_GET['catid']]['children'], array('upid'=>$upid));
			} else {
				$delids = array_merge($delids, $category[$_GET['catid']]['children']);
				foreach ($category[$_GET['catid']]['children'] as $id) {
					$value = $category[$id];
					if($value['children']) {
						$delids = array_merge($delids, $value['children']);
					}
				}
				if(!$category[$_POST['tocatid']] || in_array($_POST['tocatid'], $delids)) {
					cpmsg('albumcategory_move_category_failed', 'action=albumcategory', 'error');
				}
			}
		}
		if($delids) {
			C::t('home_album')->update_by_catid($delids, array('catid' => $_POST['tocatid']));
			C::t('home_album_category')->delete($delids);
			$num =C::t('home_album')->count_by_catid($_GET['tocatid']);
			C::t('home_album_category')->update($_POST['tocatid'], array('num'=>$num));
		}

		include_once libfile('function/cache');
		updatecache('albumcategory');

		cpmsg('albumcategory_delete_succeed', 'action=albumcategory', 'succeed');
	}
}

function showcategoryrow($key, $level = 0, $last = '') {
	global $_G;

	loadcache('albumcategory');
	$value = $_G['cache']['albumcategory'][$key];
	$return = '';

	include_once libfile('function/portalcp');
	$value['num'] = category_get_num('album', $key);
	if($level == 2) {
		$class = $last ? 'lastchildboard' : 'childboard';
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="'.$class.'">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.$value['catname'].'" class="txt" />'.
		'</div>'.
		'</td><td>'.$value[num].'</td><td><a href="'.ADMINSCRIPT.'?action=albumcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="board">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.$value['catname'].'" class="txt" />'.
		'<a class="addchildboard" onclick="addrowdirect = 1;addrow(this, 2, '.$value['catid'].')" href="###">'.cplang('albumcategory_addthirdcategory').'</a></div>'.
		'</td><td>'.$value[num].'</td><td><a href="'.ADMINSCRIPT.'?action=albumcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 2, $i==$L-1);
		}
	} else {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="parentboard">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.$value['catname'].'" class="txt" />'.
		'</div>'.
		'</td><td>'.$value[num].'</td><td><a href="'.ADMINSCRIPT.'?action=albumcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 1, '');
		}
		$return .= '<tr><td class="td25"></td><td colspan="3"><div class="lastboard"><a class="addtr" onclick="addrow(this, 1, '.$value['catid'].')" href="###">'.cplang('albumcategory_addsubcategory').'</a></div>';
	}
	return $return;
}

?>