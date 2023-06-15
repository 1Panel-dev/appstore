<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_blogcategory.php 26322 2011-12-09 02:20:12Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

cpheader();
$operation = $operation == 'delete' ? 'delete' : 'list';

loadcache('blogcategory');
$category = $_G['cache']['blogcategory'];

if($operation == 'list') {

	if(!submitcheck('editsubmit')) {

		shownav('portal', 'blogcategory');
		showsubmenu('blogcategory',  array(
			array('list', 'blogcategory', 1)
		));

		showformheader('blogcategory');
		showtableheader();
		showsetting('system_category_stat', 'settingnew[blogcategorystat]', $_G['setting']['blogcategorystat'], 'radio', '', 1);
		showsetting('system_category_required', 'settingnew[blogcategoryrequired]', $_G['setting']['blogcategoryrequired'], 'radio', '');
		echo '<tr><td colspan="2">';
		showtableheader();
		showsubtitle(array('order', 'blogcategory_name', 'blogcategory_num', 'operation'));
		foreach ($category as $key=>$value) {
			if($value['level'] == 0) {
				echo showcategoryrow($key, 0, '');
			}
		}
		echo '<tr><td class="td25">&nbsp;</td><td colspan="3"><div><a class="addtr" onclick="addrow(this, 0, 0)" href="###">'.cplang('blogcategory_addcategory').'</a></div></td></tr>';
		showtablefooter();
		echo '</td></tr>';

		showtableheader('', 'notop');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

		$langs = array();
		$keys = array('blogcategory_addcategory', 'blogcategory_addsubcategory', 'blogcategory_addthirdcategory');
		foreach ($keys as $key) {
			$langs[$key] = cplang($key);
		}
		echo <<<SCRIPT
<script type="text/JavaScript">
var rowtypedata = [
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="parentboard"><input type="text" class="txt" value="$lang[blogcategory_addcategory]" name="newname[{1}][]"/></div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="board"><input type="text" class="txt" value="$lang[blogcategory_addsubcategory]" name="newname[{1}][]"/></div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="childboard"><input type="text" class="txt" value="$lang[blogcategory_addthirdcategory]" name="newname[{1}][]"/></div>']],
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
					C::t('home_blog_category')->update($key, $sets);
				}
			}
		}
		if($_POST['newname']) {
			foreach ($_POST['newname'] as $upid=>$names) {
				foreach ($names as $nameid=>$name) {
					C::t('home_blog_category')->insert(array('upid' => $upid, 'catname' => trim($name), 'displayorder'=>intval($_POST['neworder'][$upid][$nameid])));
				}
			}
		}

		if($_POST['settingnew']) {
			$_POST['settingnew'] = array_map('intval', $_POST['settingnew']);
			C::t('common_setting')->update_batch($_POST['settingnew']);
			updatecache('setting');
		}

		include_once libfile('function/cache');
		updatecache('blogcategory');

		cpmsg('blogcategory_update_succeed', 'action=blogcategory', 'succeed');
	}

} elseif($operation == 'delete') {

	if(!$_GET['catid'] || !$category[$_GET['catid']]) {
		cpmsg('blogcategory_catgory_not_found', '', 'error');
	}
	if(!submitcheck('deletesubmit')) {
		$blog_count = C::t('home_blog')->count_by_catid($_GET['catid']);
		if(!$blog_count && empty($category[$_GET[catid]]['children'])) {
			C::t('home_blog_category')->delete($_GET['catid']);
			include_once libfile('function/cache');
			updatecache('blogcategory');
			cpmsg('blogcategory_delete_succeed', 'action=blogcategory', 'succeed');
		}

		shownav('portal', 'blogcategory');
		showsubmenu('blogcategory',  array(
			array('list', 'blogcategory', 0),
			array('delete', 'blogcategory&operation=delete&catid='.$_GET['catid'], 1)
		));

		showformheader('blogcategory&operation=delete&catid='.$_GET['catid']);
		showtableheader();
		if($category[$_GET[catid]]['children']) {
			showsetting('blogcategory_subcategory_moveto', '', '',
				'<input type="radio" name="subcat_op" value="trash" id="subcat_op_trash" checked="checked" />'.
				'<label for="subcat_op_trash" />'.cplang('blogcategory_subcategory_moveto_trash').'</label>'.
				'<input type="radio" name="subcat_op" value="parent" id="subcat_op_parent" checked="checked" />'.
				'<label for="subcat_op_parent" />'.cplang('blogcategory_subcategory_moveto_parent').'</label>'
			);
		}
		include_once libfile('function/portalcp');
		showsetting('blogcategory_blog_moveto', '', '', category_showselect('blog', 'tocatid', false, $category[$_GET['catid']]['upid']));
		showsubmit('deletesubmit');
		showtablefooter();
		showformfooter();

	} else {

		if($_POST['tocatid'] == $_GET['catid']) {
			cpmsg('blogcategory_move_category_failed', 'action=blogcategory', 'error');
		}
		$delids = array($_GET['catid']);
		if($category[$_GET['catid']]['children']) {
			if($_POST['subcat_op'] == 'parent') {
				$upid = intval($category[$_GET['catid']]['upid']);
				C::t('home_blog_category')->update($category[$_GET['catid']]['children'], array('upid' => $upid));
			} else {
				$delids = array_merge($delids, $category[$_GET['catid']]['children']);
				foreach ($category[$_GET['catid']]['children'] as $id) {
					$value = $category[$id];
					if($value['children']) {
						$delids = array_merge($delids, $value['children']);
					}
				}
				if(!$category[$_POST['tocatid']] || in_array($_POST['tocatid'], $delids)) {
					cpmsg('blogcategory_move_category_failed', 'action=blogcategory', 'error');
				}
			}
		}
		if($delids) {
			C::t('home_blog_category')->delete($delids);
			C::t('home_blog')->update_by_catid($delids, array('catid'=>$_POST['tocatid']));
			$num = C::t('home_blog')->count_by_catid($_POST['tocatid']);
			C::t('home_blog_category')->update_num_by_catid($num, $_POST['tocatid'], false);
		}

		include_once libfile('function/cache');
		updatecache('blogcategory');

		cpmsg('blogcategory_delete_succeed', 'action=blogcategory', 'succeed');
	}
}

function showcategoryrow($key, $level = 0, $last = '') {
	global $_G;

	loadcache('blogcategory');
	$value = $_G['cache']['blogcategory'][$key];
	$return = '';

	include_once libfile('function/portalcp');
	$value['num'] = category_get_num('blog', $key);
	if($level == 2) {
		$class = $last ? 'lastchildboard' : 'childboard';
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="'.$class.'">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.$value['catname'].'" class="txt" />'.
		'</div>'.
		'</td><td>'.$value[num].'</td><td><a href="'.ADMINSCRIPT.'?action=blogcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="board">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.$value['catname'].'" class="txt" />'.
		'<a class="addchildboard" onclick="addrowdirect = 1;addrow(this, 2, '.$value['catid'].')" href="###">'.cplang('blogcategory_addthirdcategory').'</a></div>'.
		'</td><td>'.$value[num].'</td><td><a href="'.ADMINSCRIPT.'?action=blogcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 2, $i==$L-1);
		}
	} else {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="parentboard">'.
		'<input type="text" name="name['.$value['catid'].']" value="'.$value['catname'].'" class="txt" />'.
		'</div>'.
		'</td><td>'.$value[num].'</td><td><a href="'.ADMINSCRIPT.'?action=blogcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a></td></tr>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 1, '');
		}
		$return .= '<tr><td class="td25"></td><td colspan="3"><div class="lastboard"><a class="addtr" onclick="addrow(this, 1, '.$value['catid'].')" href="###">'.cplang('blogcategory_addsubcategory').'</a></div>';
	}
	return $return;
}


?>