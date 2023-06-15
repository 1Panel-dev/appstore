<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_blockstyle.php 32661 2013-02-28 06:29:46Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = in_array($operation, array('add', 'edit', 'delete')) ? $operation : 'list';

loadcache('blockclass');

shownav('portal', 'blockstyle');

if($operation=='add' || $operation=='edit') {

	if($operation=='edit') {
		showsubmenu('blockstyle',  array(
			array('list', 'blockstyle', 0),
			array('edit', 'blockstyle&operation=edit&blockclass='.$_GET['blockclass'].'&styleid='.$_GET['styleid'], 1)
		));
	} else {
		showsubmenu('blockstyle',  array(
			array('list', 'blockstyle', 0),
			array('add', 'blockstyle&operation=add', 1)
		));
	}

	include_once libfile('function/block');

	if(empty($_GET['blockclass'])) {

		$blockclass_sel = '<select name="blockclass">';
		$blockclass_sel .= '<option value="">'.cplang('blockstyle_blockclass_sel').'</option>';
		foreach($_G['cache']['blockclass'] as $key=>$value) {
			foreach($value['subs'] as $subkey=>$subvalue) {
				$blockclass_sel .= "<option value=\"$subkey\">$subvalue[name]</option>";
			}
		}
		$blockclass_sel .= '</select>';
		$adminscript = ADMINSCRIPT;
		$lang_blockclasssel = cplang('blockstyle_blockclass_sel');
		$lang_submit = cplang('submit');
		echo <<<BLOCKCLASSSEL
<form method="get" autocomplete="off" action="$adminscript">
	<div style="margin-top:8px;">
		<table cellspacing="3" cellpadding="3">
			<tr>
				<th>$lang_blockclasssel</th><td>$blockclass_sel</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>
					<input type="hidden" name="action" value="blockstyle" />
					<input type="hidden" name="operation" value="add" />
					<input type="submit" value="$lang_submit" class="btn" />
				</td>
			</tr>
		</table>
	</div>
</form>
BLOCKCLASSSEL;

	} else {

		showtips('blockstyle_add_tips');

		if(submitcheck('stylesubmit')) {
			$arr = array(
				'name' => $_POST['name'],
				'blockclass' => $_GET['blockclass'],
			);
			$_POST['template'] = $_POST['template'];

			include_once libfile('function/block');
			block_parse_template($_POST['template'], $arr);

			if($_GET['styleid']) {
				$styleid = intval($_GET['styleid']);
				C::t('common_block_style')->update($styleid, $arr);
				require_once libfile('function/cache');
				updatecache('blockclass');
				cpmsg('blockstyle_edit_succeed', 'action=blockstyle&operation=edit&blockclass='.$_GET['blockclass'].'&styleid='.$styleid.'&preview='.($_POST['preview']?'1':'0'), 'succeed');
			} else {
				$styleid = C::t('common_block_style')->insert($arr, true);
				$msg = 'blockstyle_create_succeed';
				require_once libfile('function/cache');
				updatecache('blockclass');
				cpmsg('blockstyle_create_succeed', 'action=blockstyle&operation=edit&blockclass='.$_GET['blockclass'].'&styleid='.$styleid.'&preview='.($_POST['preview']?'1':'0'), 'succeed');
			}
		}

		if($_GET['styleid']) {
			$_GET['styleid'] = intval($_GET['styleid']);
			include_once libfile('function/block');
			$thestyle = block_getstyle($_GET['styleid']);
			if(!$thestyle) {
				cpmsg('blockstyle_not_found!');
			}
			$thestyle['template'] = block_build_template($thestyle['template']);

			$_GET['blockclass'] = $thestyle['blockclass'];
		} else {
			$_GET['styleid'] = 0;
			$thestyle = array(
				'template' => "<div class=\"module cl\">\n<ul>\n[loop]\n\t<li><a href=\"{url}\"{target}>{title}</a></li>\n[/loop]\n</ul>\n</div>"
			);
		}

		$theclass = block_getclass($_GET['blockclass']);

		if($preview) {
			echo '<h4 style="margin-bottom:15px;">'.lang('preview').'</h4>'.$preview;
		}

		showformheader('blockstyle&operation='.$operation.'&blockclass='.$_GET['blockclass'].'&styleid='.$_GET['styleid']);
		showtableheader();
		if($_GET['styleid']) {
			showtitle('blockstyle_add_editstyle');
		} else {
			showtitle('blockstyle_add_addstyle');
		}
		showsetting('blockstyle_name', 'name', $thestyle['name'], 'text');
		showtablefooter();

		$template = '';
		foreach($theclass['fields'] as $key=>$value) {
			if($value['name']) {
				$template .= $value['name']. ': <a href="###" onclick="insertunit($(\'jstemplate\'), \'{'.$key.'}\')">{'.$key.'}</a>';
			}
		}
		$template .= '<br />';
		$template .= cplang('blockstyle_add_loop').': <a href="###" onclick="insertunit($(\'jstemplate\'), \'[loop]\n\n[/loop]\')">[loop]...[/loop]</a>';
		$template .= cplang('blockstyle_add_order').': <a href="###" onclick="insertunit($(\'jstemplate\'), \'[order=N]\n\n[/order]\')">[order=N]...[/order]</a>';
		$template .= cplang('blockstyle_add_index').': <a href="###" onclick="insertunit($(\'jstemplate\'), \'[index=N]\n\n[/index]\')">[index=N]...[/index]</a>';
		$template .= cplang('blockstyle_add_urltitle').': <a href="###" onclick=\'insertunit($("jstemplate"), "<a href=\"{url}\"{target}>{title}</a>")\'>&lt;a href=...</a>';
		$template .= cplang('blockstyle_add_picthumb').': <a href="###" onclick=\'insertunit($("jstemplate"), "<img src=\"{pic}\" width=\"{picwidth}\" height=\"{picheight}\" />")\'>&lt;img src=...&gt;</a>';
		if(in_array($_GET['blockclass'], array('forum_thread', 'portal_article', 'group_thread'), true)) {
			$template .= cplang('blockstyle_add_moreurl').': <a href="###" onclick="insertunit($(\'jstemplate\'), \'{moreurl}\')">{moreurl}</a>';
		}
		$template .= cplang('blockstyle_add_currentorder').': <a href="###" onclick="insertunit($(\'jstemplate\'), \'{currentorder}\')">{currentorder}</a>';
		$template .= cplang('blockstyle_add_parity').': <a href="###" onclick="insertunit($(\'jstemplate\'), \'{parity}\')">{parity}</a>';
		$template .= '</div><br />';
		$template .= '<textarea cols="100" rows="5" id="jstemplate" name="template" style="width: 95%;" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)">'.$thestyle['template'].'</textarea>';
		$template .= '<input type="hidden" name="preview" value="0" /><input type="hidden" name="stylesubmit" value="1" />';
		$template .= '<br /><!--input type="button" class="btn" onclick="this.form.preview=\'1\';this.form.submit()" value="'.$lang['preview'].'">&nbsp; &nbsp;--><input type="submit" class="btn" value="'.$lang['submit'].'"></div><br /><br />';
		echo '<div class="colorbox">';
		echo '<div class="extcredits">';
		echo $template;
		echo '</div>';

		showformfooter();
	}

} elseif($operation=='delete') {

	$_GET['styleid'] = intval($_GET['styleid']);
	$thestyle = C::t('common_block_style')->fetch($_GET['styleid']);
	if(empty($thestyle)) {
		cpmsg('blockstyle_not_found', 'action=blockstyle', 'error');
	}
	$styles = array();
	if(($styles = C::t('common_block_style')->fetch_all_by_blockclass($thestyle['blockclass']))) {
		unset($styles[$_GET['styleid']]);
	}
	if(empty($styles)) {
		cpmsg('blockstyle_should_be_kept', 'action=blockstyle', 'error');
	}

	if(submitcheck('deletesubmit')) {
		$_POST['moveto'] = intval($_POST['moveto']);
		$newstyle = C::t('common_block_style')->fetch($_POST['moveto']);
		if($newstyle['blockclass'] != $thestyle['blockclass']) {
			cpmsg('blockstyle_blockclass_not_match', 'action=blockstyle', 'error');
		}
		C::t('common_block')->update_by_styleid($styleid, array('styleid' => $_POST[moveto]));
		C::t('common_block_style')->delete($_GET['styleid']);
		updatecache('blockclass');
		cpmsg('blockstyle_delete_succeed', 'action=blockstyle', 'succeed');
	}

	if(C::t('common_block')->fetch_by_styleid($_GET['styleid'])) {
		showtips('blockstyle_delete_tips');
		showformheader('blockstyle&operation=delete&styleid='.$_GET['styleid']);
		showtableheader();
		$movetoselect = '<select name="moveto">';
		foreach($styles as $key=>$value) {
			$movetoselect .= "<option value=\"$key\">$value[name]</option>";
		}
		$movetoselect .= '</select>';
		showsetting('blockstyle_moveto', '', '', $movetoselect);
		showsubmit('deletesubmit');
		showtablefooter();
		showformfooter();

	} else {
		C::t('common_block_style')->delete($_GET['styleid']);
		updatecache('blockclass');
		cpmsg('blockstyle_delete_succeed', 'action=blockstyle', 'succeed');
	}

} else {

	$_GET = $_GET + $_POST;
	$searchctrl = '<span style="float: right; padding-right: 40px;">'
				.'<a href="javascript:;" onclick="$(\'tb_search\').style.display=\'\';$(\'a_search_show\').style.display=\'none\';$(\'a_search_hide\').style.display=\'\';" id="a_search_show" style="display:none">'.cplang('show_search').'</a>'
				.'<a href="javascript:;" onclick="$(\'tb_search\').style.display=\'none\';$(\'a_search_show\').style.display=\'\';$(\'a_search_hide\').style.display=\'none\';" id="a_search_hide">'.cplang('hide_search').'</a>'
				.'</span>';
	showsubmenu('blockstyle',  array(
		array('list', 'blockstyle', 1),
		array('add', 'blockstyle&operation=add', 0)
	), $searchctrl);

	$mpurl = ADMINSCRIPT.'?action=blockstyle';
	$intkeys = array('styleid');
	$strkeys = array('blockclass');
	$randkeys = array();
	$likekeys = array('name', 'template');
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
	foreach($likekeys as $k) {
		$_GET[$k] = dhtmlspecialchars($_GET[$k]);
	}
	$wherearr = $results['wherearr'];
	$mpurl .= '&'.implode('&', $results['urls']);

	$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);

	$orders = getorders(array('blockclass'), 'styleid');
	$ordersql = $orders['sql'];
	if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
	$orderby = array($_GET['orderby']=>' selected');
	$ordersc = array($_GET['ordersc']=>' selected');

	$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
	if(!in_array($perpage, array(10,20,50,100))) $perpage = 20;
	$perpages = array($perpage=>' selected');
	$mpurl .= '&perpage='.$perpage;

	$searchlang = array();
	$keys = array('search', 'likesupport', 'resultsort', 'defaultsort', 'orderdesc', 'orderasc', 'perpage_10', 'perpage_20', 'perpage_50', 'perpage_100',
	'blockstyle_id', 'blockstyle_name', 'blockstyle_blockclass', 'blockstyle_template');
	foreach ($keys as $key) {
		$searchlang[$key] = cplang($key);
	}
	$blockclass_sel = '<select name="blockclass">';
	$blockclass_sel .= '<option value="">'.cplang('blockstyle_blockclass_sel').'</option>';
	foreach($_G['cache']['blockclass'] as $key=>$value) {
		foreach($value['subs'] as $subkey=>$subvalue) {
			$selected = (!empty($_GET['blockclass']) && $subkey == $_GET['blockclass'] ? ' selected' : '');
			$blockclass_sel .= "<option value=\"$subkey\"$selected>$subvalue[name]</option>";
		}
	}
	$blockclass_sel .= '</select>';

	$adminscript = ADMINSCRIPT;
	echo <<<SEARCH
<form method="post" autocomplete="off" action="$adminscript" id="tb_search">
	<div style="margin-top:8px;">
		<table cellspacing="3" cellpadding="3">
			<tr>
				<th>$searchlang[blockstyle_id]</th><td><input type="text" class="txt" name="styleid" value="$_GET[styleid]"></td>
				<th>$searchlang[blockstyle_name]*</th><td><input type="text" class="txt" name="name" value="$_GET[name]">*$searchlang[likesupport]</td>
			</tr>
			<tr>
				<th>$searchlang[blockstyle_blockclass]</th><td>$blockclass_sel</td>
				<th>$searchlang[blockstyle_template]*</th><td><input type="text" name="template" value="$_GET[template]">*$searchlang[likesupport]</td>
			</tr>
			<tr>
				<th>$searchlang[resultsort]</th>
				<td colspan="3">
					<select name="orderby">
					<option value="styleid">$searchlang[defaultsort]</option>
					<option value="blockclass"$orderby[blockclass]>$searchlang[blockstyle_blockclass]</option>
					</select>
					<select name="ordersc">
					<option value="desc"$ordersc[desc]>$searchlang[orderdesc]</option>
					<option value="asc"$ordersc[asc]>$searchlang[orderasc]</option>
					</select>
					<select name="perpage">
					<option value="10"$perpages[10]>$searchlang[perpage_10]</option>
					<option value="20"$perpages[20]>$searchlang[perpage_20]</option>
					<option value="50"$perpages[50]>$searchlang[perpage_50]</option>
					<option value="100"$perpages[100]>$searchlang[perpage_100]</option>
					</select>
					<input type="hidden" name="action" value="blockstyle">
					<input type="submit" name="searchsubmit" value="$searchlang[search]" class="btn">
				</td>
			</tr>
		</table>
	</div>
</form>
SEARCH;

	$start = ($page-1)*$perpage;

	showformheader('blockstyle');
	showtableheader('blockstyle_list');
	showsubtitle(array('blockstyle_name', 'blockstyle_blockclass', 'operation'));

	$multipage = '';
	if(($count = C::t(common_block_style)->count_by_where($wheresql))) {
		include_once libfile('function/block');
		foreach(C::t('common_block_style')->fetch_all_by_where($wheresql, $ordersql, $start, $perpage) as $value) {
			$theclass = block_getclass($value['blockclass']);
			list($c1, $c2) = explode('_', $value['blockclass']);
			showtablerow('', array('class=""', 'class=""', 'class="td28"'), array(
				$value['name'],
				$theclass['name'],
				"<a href=\"".ADMINSCRIPT."?action=blockstyle&operation=edit&blockclass=$value[blockclass]&styleid=$value[styleid]\">".cplang('blockstyle_edit')."</a>&nbsp;&nbsp;".
				"<a href=\"".ADMINSCRIPT."?action=blockstyle&operation=delete&styleid=$value[styleid]\">".cplang('blockstyle_delete')."</a>"
			));
		}
		$multipage = multi($count, $perpage, $page, $mpurl);
	}

	showsubmit('', '', '', '', $multipage);
	showtablefooter();
	showformfooter();

}

?>