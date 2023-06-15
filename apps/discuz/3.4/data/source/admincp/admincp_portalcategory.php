<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_portalcategory.php 32945 2013-03-26 05:01:12Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/portalcp');

cpheader();
$operation = in_array($operation, array('delete', 'move', 'perm', 'add', 'edit')) ? $operation : 'list';

loadcache('portalcategory');
$portalcategory = $_G['cache']['portalcategory'];

if($operation == 'list') {

	if(empty($portalcategory) && C::t('portal_category')->count()) {
		updatecache('portalcategory');
		loadcache('portalcategory', true);
		$portalcategory = $_G['cache']['portalcategory'];
	}
	if(!submitcheck('editsubmit')) {

		shownav('portal', 'portalcategory');
		showsubmenu('portalcategory',  array(
			array('list', 'portalcategory', 1)
		));

		$tdstyle = array('width="25"', 'width="60"', '', 'width="45"', 'width="55"', 'width="30"', 'width="30"', 'width="30"', 'width="185"', 'width="100"');
		showformheader('portalcategory');
		echo '<div style="height:30px;line-height:30px;"><a href="javascript:;" onclick="show_all()">'.cplang('show_all').'</a> | <a href="javascript:;" onclick="hide_all()">'.cplang('hide_all').'</a> <input type="text" id="srchforumipt" class="txt" /> <input type="submit" class="btn" value="'.cplang('search').'" onclick="return srchforum()" /></div>';
		showtableheader('', '', 'id="portalcategory_header" style="min-width:900px;*width:900px;"');
		showsubtitle(array('', '', 'portalcategory_name', 'portalcategory_articles', 'portalcategory_allowpublish', 'portalcategory_allowcomment', 'portalcategory_is_closed', 'setindex', 'operation', 'portalcategory_article_op'), 'header tbm', $tdstyle);
		showtablefooter();
		echo '<script type="text/javascript">floatbottom(\'portalcategory_header\');</script>';
		showtableheader('', '', 'style="min-width:900px;*width:900px;"');
		showsubtitle(array('', '', 'portalcategory_name', 'portalcategory_articles', 'portalcategory_allowpublish', 'portalcategory_allowcomment', 'portalcategory_is_closed', 'setindex', 'operation', 'portalcategory_article_op'), 'header', $tdstyle);
		foreach ($portalcategory as $key=>$value) {
			if($value['level'] == 0) {
				echo showcategoryrow($key, 0, '');
			}
		}
		echo '<tbody><tr><td>&nbsp;</td><td colspan="6"><div><a class="addtr" href="'.ADMINSCRIPT.'?action=portalcategory&operation=add&upid=0">'.cplang('portalcategory_addcategory').'</a></div></td><td colspan="3">&nbsp;</td></tr></tbody>';
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

		$langs = array();
		$keys = array('portalcategory_addcategory', 'portalcategory_addsubcategory', 'portalcategory_addthirdcategory');
		foreach ($keys as $key) {
			$langs[$key] = cplang($key);
		}
		echo <<<SCRIPT
<script type="text/Javascript">
var rowtypedata = [
	[[1,'', ''], [4, '<div class="parentboard"><input type="text" class="txt" value="$lang[portalcategory_addcategory]" name="newname[{1}][]"/></div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [4, '<div class="board"><input type="text" class="txt" value="$lang[portalcategory_addsubcategory]" name="newname[{1}][]"/>  <input type="checkbox" name="newinheritance[{1}][]" value="1" checked>$lang[portalcategory_inheritance]</div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [4, '<div class="childboard"><input type="text" class="txt" value="$lang[portalcategory_addthirdcategory]" name="newname[{1}][]"/> <input type="checkbox" name="newinheritance[{1}][]" value="1" checked>$lang[portalcategory_inheritance]</div>']],
];
</script>
SCRIPT;

	} else {
		$cachearr = array('portalcategory');
		if($_POST['name']) {
			$openarr = $closearr = array();
			foreach($_POST['name'] as $key=>$value) {
				$sets = array();
				$value = trim($value);
				if($portalcategory[$key] && $portalcategory[$key]['catname'] != $value) {
					$sets['catname'] = $value;
				}
				if($portalcategory[$key] && $portalcategory[$key]['displayorder'] != $_POST['neworder'][$key]) {
					$sets['displayorder'] = $_POST['neworder'][$key];
				}
				if($sets) {
					C::t('portal_category')->update($key, $sets);
					C::t('common_diy_data')->update('portal/list_'.$key, getdiydirectory($portalcategory[$key]['primaltplname']), array('name'=>$value));
					C::t('common_diy_data')->update('portal/view_'.$key, getdiydirectory($portalcategory[$key]['articleprimaltplname']), array('name'=>$value));
					$cachearr[] = 'diytemplatename';
				}
			}
		}

		if($_GET['newsetindex']) {
			C::t('common_setting')->update('defaultindex', $portalcategory[$_GET['newsetindex']]['caturl']);
			$cachearr[] = 'setting';
		}
		include_once libfile('function/cache');
		updatecache($cachearr);

		cpmsg('portalcategory_update_succeed', 'action=portalcategory', 'succeed');
	}

} elseif($operation == 'perm') {

	$catid = intval($_GET['catid']);
	if(!submitcheck('permsubmit')) {
		$category = C::t('portal_category')->fetch($catid);
		shownav('portal', 'portalcategory');
		$upcat = $category['upid'] ? ' - <a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$category['upid'].'">'.$portalcategory[$category['upid']]['catname'].'</a> ' : '';
		showsubmenu('<a href="'.ADMINSCRIPT.'?action=portalcategory">'.cplang('portalcategory_perm_edit').'</a>'.$upcat.' - '.$category['catname']);
		showtips('portalcategory_article_perm_tips');
		showformheader("portalcategory&operation=perm&catid=$catid");

		showtableheader('', 'fixpadding');

		$inherited_checked = !$category['notinheritedarticle'] ? 'checked' : '';
		if($portalcategory[$catid]['level'])showsubtitle(array('','<input class="checkbox" type="checkbox" name="inherited" value="1" '.$inherited_checked.'/>'.cplang('portalcategory_inheritance'),'',''));
		showsubtitle(array('', 'username',
		'<input class="checkbox" type="checkbox" name="chkallpublish" onclick="checkAll(\'prefix\', this.form, \'publish\', \'chkallpublish\')" id="chkallpublish" /><label for="chkallpublish">'.cplang('portalcategory_perm_publish').'</label>',
		'<input class="checkbox" type="checkbox" name="chkallmanage" onclick="checkAll(\'prefix\', this.form, \'manage\', \'chkallmanage\')" id="chkallmanage" /><label for="chkallmanage">'.cplang('portalcategory_perm_manage').'</label>',
		'block_perm_inherited'
		));

		$line = '&minus;';
		$permissions = C::t('portal_category_permission')->fetch_all_by_catid($catid);
		$members = C::t('common_member')->fetch_all(array_keys($permissions));
		foreach($permissions as $uid => $value) {
			$value = array_merge($value, $members[$uid]);
			if(!empty($value['inheritedcatid'])) {
				showtablerow('', array('class="td25"'), array(
					"",
					"$value[username]",
					$value['allowpublish'] ? '&radic;' : $line,
					$value['allowmanage'] ? '&radic;' : $line,
					'<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['inheritedcatid'].'">'.$portalcategory[$value['inheritedcatid']]['catname'].'</a>',
				));
			} else {
				showtablerow('', array('class="td25"'), array(
					"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[$value[uid]]\" value=\"$value[uid]\" /><input type=\"hidden\" name=\"perm[$value[uid]]\" value=\"$value[catid]\" />
					<input type=\"hidden\" name=\"perm[$value[uid]][allowpublish]\" value=\"$value[allowpublish]\" />
					<input type=\"hidden\" name=\"perm[$value[uid]][allowmanage]\" value=\"$value[allowmanage]\" />",
					"$value[username]",
					"<input type=\"checkbox\" class=\"checkbox\" name=\"allowpublish[$value[uid]]\" value=\"1\" ".($value['allowpublish'] ? 'checked' : '').' />',
					"<input type=\"checkbox\" class=\"checkbox\" name=\"allowmanage[$value[uid]]\" value=\"1\" ".($value['allowmanage'] ? 'checked' : '').' />',
					$line,
				));
			}
		}
		showtablerow('', array('class="td25"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" name="newuser" value="" size="20" />',
			'<input type="checkbox" class="checkbox" name="newpublish" value="1" />',
			'<input type="checkbox" class="checkbox" name="newmanage" value="1" />',
			'',
		));

		showsubmit('permsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
	} else {

		$users = array();
		if(is_array($_GET['perm'])) {
			foreach($_GET['perm'] as $uid => $value) {
				if(empty($_GET['delete']) || !in_array($uid, $_GET['delete'])) {
					$user = array();
					$user['allowpublish'] = $_GET['allowpublish'][$uid] ? 1 : 0;
					$user['allowmanage'] = $_GET['allowmanage'][$uid] ? 1 : 0;
					if($value['allowpublish'] != $user['allowpublish'] || $value['allowmanage'] != $user['allowmanage']) {
						$user['uid'] = intval($uid);
						$users[] = $user;
					}
				}
			}
		}
		if(!empty($_GET['newuser'])) {
			$newuid = C::t('common_member')->fetch_uid_by_username($_GET['newuser']);
			if($newuid) {
				$user['uid'] = $newuid;
				$user['allowpublish'] = $_GET['newpublish'] ? 1 : 0;
				$user['allowmanage'] = $_GET['newmanage'] ? 1 : 0;
				$users[$user['uid']] = $user;
			} else {
				cpmsg_error($_GET['newuser'].cplang('portalcategory_has_no_allowauthorizedarticle'));
			}
		}

		require_once libfile('class/portalcategory');
		$categorypermsission = & portal_category::instance();
		if(!empty($users)) {
			$categorypermsission->add_users_perm($catid, $users);
		}

		if(!empty($_GET['delete'])) {
			$categorypermsission->delete_users_perm($catid, $_GET['delete']);
		}

		$notinherited = !$_POST['inherited'] ? '1' : '0';
		if($notinherited != $portalcategory[$catid]['notinheritedarticle']) {
			if($notinherited) {
				$categorypermsission->delete_inherited_perm_by_catid($catid, $portalcategory[$catid]['upid']);
			} else {
				$categorypermsission->remake_inherited_perm($catid);
			}
			C::t('portal_category')->update($catid, array('notinheritedarticle'=>$notinherited));
		}

		include_once libfile('function/cache');
		updatecache('portalcategory');

		cpmsg('portalcategory_perm_update_succeed', "action=portalcategory&operation=perm&catid=$catid", 'succeed');
	}

} elseif($operation == 'delete') {

	$_GET['catid'] = max(0, intval($_GET['catid']));
	if(!$_GET['catid'] || !$portalcategory[$_GET['catid']]) {
		cpmsg('portalcategory_catgory_not_found', '', 'error');
	}
	$catechildren = $portalcategory[$_GET['catid']]['children'];
	include_once libfile('function/cache');
	if(!submitcheck('deletesubmit')) {
		$article_count = C::t('portal_article_title')->fetch_count_for_cat($_GET['catid']);
		if(!$article_count && empty($catechildren)) {

			if($portalcategory[$_GET['catid']]['foldername']) delportalcategoryfolder($_GET['catid']);

			deleteportalcategory($_GET['catid']);
			updatecache(array('portalcategory','diytemplatename'));
			cpmsg('portalcategory_delete_succeed', 'action=portalcategory', 'succeed');
		}

		shownav('portal', 'portalcategory');
		showsubmenu('portalcategory',  array(
			array('list', 'portalcategory', 0),
			array('delete', 'portalcategory&operation=delete&catid='.$_GET['catid'], 1)
		));

		showformheader('portalcategory&operation=delete&catid='.$_GET['catid']);
		showtableheader();
		if($portalcategory[$_GET[catid]]['children']) {
			showsetting('portalcategory_subcategory_moveto', '', '',
				'<input type="radio" name="subcat_op" value="trash" id="subcat_op_trash" checked="checked" />'.
				'<label for="subcat_op_trash" />'.cplang('portalcategory_subcategory_moveto_trash').'</label>'.
				'<input type="radio" name="subcat_op" value="parent" id="subcat_op_parent" checked="checked" />'.
				'<label for="subcat_op_parent" />'.cplang('portalcategory_subcategory_moveto_parent').'</label>'
			);
		}
		include_once libfile('function/portalcp');
		echo "<tr><td colspan=\"2\" class=\"td27\">".cplang('portalcategory_article').":</td></tr>
				<tr class=\"noborder\">
					<td class=\"vtop rowform\">
						<ul class=\"nofloat\" onmouseover=\"altStyle(this);\">
						<li class=\"checked\"><input class=\"radio\" type=\"radio\" name=\"article_op\" value=\"move\" checked />&nbsp;".cplang('portalcategory_article_moveto')."&nbsp;&nbsp;&nbsp;".category_showselect('portal', 'tocatid', false, $portalcategory[$_GET['catid']]['upid'])."</li>
						<li><input class=\"radio\" type=\"radio\" name=\"article_op\" value=\"delete\" />&nbsp;".cplang('portalcategory_article_delete')."</li>
						</ul></td>
					<td class=\"vtop tips2\"></td>
				</tr>";

		showsubmit('deletesubmit', 'portalcategory_delete');
		showtablefooter();
		showformfooter();

	} else {

		if($_POST['article_op'] == 'delete') {
			if(!$_GET['confirmed']) {
				cpmsg('portal_delete_confirm', "action=portalcategory&operation=delete&catid=$_GET[catid]", 'form', array(),
				'<input type="hidden" class="btn" id="deletesubmit" name="deletesubmit" value="1" /><input type="hidden" class="btn" id="subcat_op" name="subcat_op" value="'.$_POST[subcat_op].'" />
					<input type="hidden" class="btn" id="article_op" name="article_op" value="delete" /><input type="hidden" class="btn" id="tocatid" name="tocatid" value="'.$_POST[tocatid].'" />');
			}
		}

		if($_POST['article_op'] == 'move') {
			if($_POST['tocatid'] == $_GET['catid'] || empty($portalcategory[$_POST['tocatid']])) {
				cpmsg('portalcategory_move_category_failed', 'action=portalcategory', 'error');
			}
		}

		$delids = array($_GET['catid']);
		$updatecategoryfile = array();
		if($catechildren) {
			if($_POST['subcat_op'] == 'parent') {
				$upid = intval($portalcategory[$_GET['catid']]['upid']);
				if(!empty($portalcategory[$upid]['foldername']) || ($portalcategory[$_GET['catid']]['level'] == '0' && $portalcategory[$_GET['catid']]['foldername'])) {
					$parentdir = DISCUZ_ROOT.'/'.getportalcategoryfulldir($upid);
					foreach($catechildren as $subcatid) {
						if($portalcategory[$subcatid]['foldername']) {
							$olddir = DISCUZ_ROOT.'/'.getportalcategoryfulldir($subcatid);
							rename($olddir, $parentdir.$portalcategory[$subcatid]['foldername']);
							$updatecategoryfile[] = $subcatid;
						}
					}
				}
				C::t('portal_category')->update($catechildren, array('upid' => $upid));
				require_once libfile('class/blockpermission');
				require_once libfile('class/portalcategory');
				$tplpermission = & template_permission::instance();
				$tplpermission->delete_perm_by_inheritedtpl('portal/list_'.$_GET['catid']);
				$categorypermission = & portal_category::instance();
				$categorypermission->delete_perm_by_inheritedcatid($_GET['catid']);

			} else {
				$delids = array_merge($delids, $catechildren);
				foreach ($catechildren as $id) {
					$value = $portalcategory[$id];
					if($value['children']) {
						$delids = array_merge($delids, $value['children']);
					}
				}
				if($_POST['article_op'] == 'move') {
					if(!$portalcategory[$_POST['tocatid']] || in_array($_POST['tocatid'], $delids)) {
						cpmsg('portalcategory_move_category_failed', 'action=portalcategory', 'error');
					}
				}
			}
		}

		if($delids) {
			deleteportalcategory($delids);
			if($_POST['article_op'] == 'delete') {
				require_once libfile('function/delete');
				$aidarr = array();
				$query = C::t('portal_article_title')->fetch_all_for_cat($delids);
				foreach($query as $value) {
					$aidarr[] = $value['aid'];
				}
				if($aidarr) {
					deletearticle($aidarr, '0');
				}
			} else {
				C::t('portal_article_title')->update_for_cat($delids, array('catid'=>$_POST['tocatid']));
				$num = C::t('portal_article_title')->fetch_count_for_cat($_POST['tocatid']);
				C::t('portal_category')->update($_POST['tocatid'], array('articles'=>dintval($num)));
			}
		}

		if($portalcategory[$_GET['catid']]['foldername']) delportalcategoryfolder($_GET['catid']);
		updatecache(array('portalcategory','diytemplatename'));
		loadcache('portalcategory', true);
		remakecategoryfile($updatecategoryfile);
		cpmsg('portalcategory_delete_succeed', 'action=portalcategory', 'succeed');
	}

} elseif($operation == 'move') {
	$_GET['catid'] = intval($_GET['catid']);
	if(!$_GET['catid'] || !$portalcategory[$_GET['catid']]) {
		cpmsg('portalcategory_catgory_not_found', '', 'error');
	}
	if(!submitcheck('movesubmit')) {
		$article_count = C::t('portal_article_title')->fetch_count_for_cat($_GET['catid']);
		if(!$article_count) {
			cpmsg('portalcategory_move_empty_error', 'action=portalcategory', 'succeed');
		}

		shownav('portal', 'portalcategory');
		showsubmenu('portalcategory',  array(
			array('list', 'portalcategory', 0),
			array('portalcategory_move', 'portalcategory&operation=move&catid='.$_GET['catid'], 1)
		));

		showformheader('portalcategory&operation=move&catid='.$_GET['catid']);
		showtableheader();
		include_once libfile('function/portalcp');
		showsetting('portalcategory_article_moveto', '', '', category_showselect('portal', 'tocatid', false, $portalcategory[$_GET['catid']]['upid']));
		showsubmit('movesubmit', 'portalcategory_move');
		showtablefooter();
		showformfooter();

	} else {

		if($_POST['tocatid'] == $_GET['catid'] || empty($portalcategory[$_POST['tocatid']])) {
			cpmsg('portalcategory_move_category_failed', 'action=portalcategory', 'error');
		}

		C::t('portal_article_title')->update_for_cat($_GET['catid'], array('catid' => $_POST['tocatid']));
		C::t('portal_category')->update($_GET['catid'], array('articles'=>0));
		$num = C::t('portal_article_title')->fetch_count_for_cat($_POST['tocatid']);
		C::t('portal_category')->update($_POST['tocatid'], array('articles'=>$num));
		updatecache('portalcategory');

		cpmsg('portalcategory_move_succeed', 'action=portalcategory', 'succeed');
	}
} elseif($operation == 'edit' || $operation == 'add') {
	$_GET['catid'] = intval($_GET['catid']);
	$anchor = in_array($_GET['anchor'], array('basic', 'html')) ? $_GET['anchor'] : 'basic';

	if($_GET['catid'] && !$portalcategory[$_GET['catid']]) {
		cpmsg('portalcategory_catgory_not_found', '', 'error');
	}

	$cate = $_GET['catid'] ? $portalcategory[$_GET['catid']] : array();
	if($operation == 'add') {
		$_GET['upid'] = intval($_GET['upid']);
		if($_GET['upid']) {
			$cate['level'] = $portalcategory[$_GET['upid']] ? $portalcategory[$_GET['upid']]['level']+1 : 0;
			$cate['upid'] = intval($_GET['upid']);
		} else {
			$cate['level'] = 0;
			$cate['upid'] = 0;
		}
		$cate['displayorder'] = 0;
		$cate['closed'] = 1;
	}
	@include_once DISCUZ_ROOT.'./data/cache/cache_domain.php';
	$channeldomain = isset($rootdomain['channel']) && $rootdomain['channel'] ? $rootdomain['channel'] : array();

	if(!submitcheck('detailsubmit')) {
		shownav('portal', 'portalcategory');
		$url = 'portalcategory&operation='.$operation.($operation == 'add' ? '&upid='.$_GET['upid'] : '&catid='.$_GET['catid']);
		showsubmenuanchors(cplang('portalcategory_detail').($cate['catname'] ? ' - '.$cate['catname'] : ''), array(
			array('edit', 'basic', $anchor == 'basic'),
		));

		showtagheader('div', 'basic', $anchor == 'basic');
		showformheader($url);
		showtableheader();
		$catemsg = '';
		if($cate['username']) $catemsg .= $lang['portalcategory_username'].' '.$cate['username'];
		if($cate['dateline']) $catemsg .= ' '.$lang['portalcategory_dateline'].' '.dgmdate($cate['dateline'],'Y-m-d m:i:s');
		if($cate['upid']) $catemsg .= ' '.$lang['portalcategory_upname'].': <a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$cate['upid'].'">'.$portalcategory[$cate['upid']]['catname'].'</a>';
		if($catemsg) showtitle($catemsg);
		showsetting('portalcategory_catname', 'catname', html_entity_decode($cate['catname']), 'text');
		showsetting('display_order', 'displayorder', $cate['displayorder'], 'text');
		showsetting('portalcategory_foldername', 'foldername', $cate['foldername'], 'text');
		showsetting('portalcategory_url', 'url', $cate['url'], 'text');
		showsetting('portalcategory_perpage', 'perpage', $cate['perpage'] ? $cate['perpage'] : 15, 'text');
		showsetting('portalcategory_maxpages', 'maxpages', $cate['maxpages'] ? $cate['maxpages'] : 1000, 'text');

		showportalprimaltemplate($cate['primaltplname'], 'list');
		showportalprimaltemplate($cate['articleprimaltplname'], 'view');

		showsetting('portalcategory_allowpublish', 'allowpublish', $cate['disallowpublish'] ? 0 : 1, 'radio');
		showsetting('portalcategory_notshowarticlesummay', 'notshowarticlesummay', $cate['notshowarticlesummay'] ? 0 : 1, 'radio');
		showsetting('portalcategory_allowcomment', 'allowcomment', $cate['allowcomment'], 'radio');
		if($cate['level']) {
			showsetting('portalcategory_inheritancearticle', 'inheritancearticle', !$cate['notinheritedarticle'] ? '1' : '0', 'radio');
			showsetting('portalcategory_inheritanceblock', 'inheritanceblock', !$cate['notinheritedblock'] ? '1' : '0', 'radio');
		}
		showsetting('portalcategory_is_closed', 'closed', $cate['closed'] ? 0 : 1, 'radio');
		if($cate['level'] != 2) showsetting('portalcategory_shownav', 'shownav', $cate['shownav'], 'radio');
		$setindex = !empty($_G['setting']['defaultindex']) && $_G['setting']['defaultindex'] == $cate['caturl'] ? 1 : 0;
		showsetting('setindex', 'setindex', $setindex, 'radio');
		if($cate['level'] == 0) {
			if(!empty($_G['setting']['domain']['root']['channel'])) {
				showsetting('forums_edit_extend_domain', '', '', $_G['scheme'].'://<input type="text" class="txt" name="domain" class="txt" value="'.$cate['domain'].'" style="width:100px; margin-right:0px;" >.'.$_G['setting']['domain']['root']['channel']);
			} else {
				showsetting('forums_edit_extend_domain', 'domain', '', 'text', 'disabled');
			}
		}
		showsetting('portalcategory_noantitheft', 'noantitheft', $cate['noantitheft'], 'radio');
		showtablefooter();
		showtips('setting_seo_portal_tips', 'tips', true, 'setseotips');
		showtableheader();
		showsetting('portalcategory_seotitle', 'seotitle', $cate['seotitle'], 'text');
		showsetting('portalcategory_keyword', 'keyword', $cate['keyword'], 'text');
		showsetting('portalcategory_summary', 'description', $cate['description'], 'textarea');
		showtablefooter();

		showsubmit('detailsubmit');
		if($operation == 'add') showsetting('', '', '', '<input type="hidden" name="level" value="'.$cate['level'].'" />');
		showtablefooter();
		showformfooter();

	} else {
		require_once libfile('function/portalcp');
		$domain = $_GET['domain'] ? $_GET['domain'] : '';
		$_GET['closed'] = intval($_GET['closed']) ? 0 : 1;
		$_GET['catname'] = trim($_GET['catname']);
		$foldername = trim($_GET['foldername']);
		$oldsetindex = !empty($_G['setting']['defaultindex']) && $_G['setting']['defaultindex'] == $cate['caturl'] ? 1 : 0;
		$perpage = intval($_GET['perpage']);
		$maxpages = intval($_GET['maxpages']);
		$perpage = empty($perpage) ? 15 : $perpage;
		$maxpages = empty($maxpages) ? 1000 : $maxpages;

		if($_GET['catid'] && !empty($cate['domain'])) {
			require_once libfile('function/delete');
			deletedomain($_GET['catid'], 'channel');
		}
		if(!empty($domain)) {
			require_once libfile('function/domain');
			domaincheck($domain, $_G['setting']['domain']['root']['channel'], 1);
		}

		$updatecategoryfile = array();


		$editcat = array(
			'catname' => $_GET['catname'],
			'allowcomment'=>$_GET['allowcomment'],
			'url' => $_GET['url'],
			'closed' => $_GET['closed'],
			'seotitle' => $_GET['seotitle'],
			'keyword' => $_GET['keyword'],
			'description' => $_GET['description'],
			'displayorder' => intval($_GET['displayorder']),
			'notinheritedarticle' => $_GET['inheritancearticle'] ? '0' : '1',
			'notinheritedblock' => $_GET['inheritanceblock'] ? '0' : '1',
			'disallowpublish' => $_GET['allowpublish'] ? '0' : '1',
			'notshowarticlesummay' => $_GET['notshowarticlesummay'] ? '0' : '1',
			'perpage' => $perpage,
			'maxpages' => $maxpages,
			'noantitheft' => intval($_GET['noantitheft']),
		);

		$dir = '';
		if(!empty($foldername)) {
			$oldfoldername = empty($_GET['catid']) ? '' : $portalcategory[$_GET['catid']]['foldername'];
			preg_match_all('/[^\w\d\_]/',$foldername,$re);
			if(!empty($re[0])) {
				cpmsg(cplang('portalcategory_foldername_rename_error').','.cplang('return'), NULL, 'error');
			}
			$parentdir = getportalcategoryfulldir($cate['upid']);
			if($parentdir === false) cpmsg(cplang('portalcategory_parentfoldername_empty').','.cplang('return'), NULL, 'error');
			if($foldername == $oldfoldername) {
				$dir = $parentdir.$foldername;
			} elseif(is_dir(DISCUZ_ROOT.'./'.$parentdir.$foldername)) {
				cpmsg(cplang('portalcategory_foldername_duplicate').','.cplang('return'), NULL, 'error');
			} elseif ($portalcategory[$_GET['catid']]['foldername']) {
				$r = rename(DISCUZ_ROOT.'./'.$parentdir.$portalcategory[$_GET['catid']]['foldername'], DISCUZ_ROOT.'./'.$parentdir.$foldername);
				if($r) {
					$updatecategoryfile[] = $_GET['catid'];
					$editcat['foldername'] = $foldername;
				} else {
					cpmsg(cplang('portalcategory_foldername_rename_error').','.cplang('return'), NULL, 'error');
				}
			} elseif (empty($portalcategory[$_GET['catid']]['foldername'])) {
				$dir = $parentdir.$foldername;
				$editcat['foldername'] = $foldername;
			}
		} elseif(empty($foldername) && $portalcategory[$_GET['catid']]['foldername']) {
			delportalcategoryfolder($_GET['catid']);
			$editcat['foldername'] = '';
		}
		$primaltplname = $viewprimaltplname = '';
		if(!empty($_GET['listprimaltplname'])) {
			$primaltplname = $_GET['listprimaltplname'];
			if(!isset($_GET['signs']['list'][dsign($primaltplname)])) {
				cpmsg(cplang('diy_sign_invalid').','.cplang('return'), NULL, 'error');
			}
			$checktpl = checkprimaltpl($primaltplname);
			if($checktpl !== true) {
				cpmsg(cplang($checktpl).','.cplang('return'), NULL, 'error');
			}
		}

		if(empty($_GET['viewprimaltplname'])) {
			$_GET['viewprimaltplname'] = getparentviewprimaltplname($_GET['catid']);
		} else if(!isset($_GET['signs']['view'][dsign($_GET['viewprimaltplname'])])) {
				cpmsg(cplang('diy_sign_invalid').','.cplang('return'), NULL, 'error');
		}
		$viewprimaltplname = strpos($_GET['viewprimaltplname'], ':') === false ? $_G['cache']['style_default']['tpldir'].':portal/'.$_GET['viewprimaltplname'] : $_GET['viewprimaltplname'];
		$checktpl = checkprimaltpl($viewprimaltplname);
		if($checktpl !== true) {
			cpmsg(cplang($checktpl).','.cplang('return'), NULL, 'error');
		}

		$editcat['primaltplname'] = $primaltplname;
		$editcat['articleprimaltplname'] = $viewprimaltplname;

		if($_GET['catid']) {
			if($portalcategory[$_G['catid']]['level'] < 2) $editcat['shownav'] = intval($_GET['shownav']);
			if($domain && $portalcategory[$_G['catid']]['level'] == 0) {
				$editcat['domain'] = $domain;
			} else {
				$editcat['domain'] = '';
			}
		} else {
			if($portalcategory[$cate['upid']]) {
				if($portalcategory[$cate['upid']]['level'] == 0) $editcat['shownav'] = intval($_GET['shownav']);
			} else {
				$editcat['shownav'] = intval($_GET['shownav']);
				$editcat['domain'] = $domain;
			}
		}
		$cachearr = array('portalcategory');
		if($_GET['catid']) {
			C::t('portal_category')->update($cate['catid'], $editcat);
			if($cate['catname'] != $_GET['catname']) {
				C::t('common_diy_data')->update('portal/list_'.$cate['catid'], getdiydirectory($cate['primaltplname']), array('name'=>$_GET['catname']));
				C::t('common_diy_data')->update('portal/view_'.$cate['catid'], getdiydirectory($cate['articleprimaltplname']), array('name'=>$_GET['catname']));
				$cachearr[] = 'diytemplatename';
			}
		} else {
			$editcat['upid'] = $cate['upid'];
			$editcat['dateline'] = TIMESTAMP;
			$editcat['uid'] = $_G['uid'];
			$editcat['username'] = $_G['username'];
			$_GET['catid'] = C::t('portal_category')->insert($editcat, true);
			$cachearr[] = 'diytemplatename';
		}

		if(!empty($domain)) {
			C::t('common_domain')->insert(array('domain' => $domain, 'domainroot' => $_G['setting']['domain']['root']['channel'], 'id' => $_GET['catid'], 'idtype' => 'channel'));
			$cachearr[] = 'setting';
		}
		if($_GET['listprimaltplname'] && (empty($cate['primaltplname']) || $cate['primaltplname'] != $primaltplname)) {
			remakediytemplate($primaltplname, 'portal/list_'.$_GET['catid'], $_GET['catname'], getdiydirectory($cate['primaltplname']));
		}

		if($cate['articleprimaltplname'] != $viewprimaltplname) {
			remakediytemplate($viewprimaltplname, 'portal/view_'.$_GET['catid'], $_GET['catname'].'-'.cplang('portalcategory_viewpage'), getdiydirectory($cate['articleprimaltplname']));
		}

		include_once libfile('function/cache');
		updatecache('portalcategory');
		loadcache('portalcategory',true);
		$portalcategory = $_G['cache']['portalcategory'];

		require libfile('class/blockpermission');
		$tplpermsission = & template_permission::instance();
		$tplpre = 'portal/list_';

		require libfile('class/portalcategory');
		$categorypermsission = & portal_category::instance();

		if($operation == 'add') {
			if($cate['upid'] && $_GET['catid']) {
				if(!$editcat['notinheritedblock']) {
					$tplpermsission->remake_inherited_perm($tplpre.$_GET['catid'], $tplpre.$cate['upid']);
				}
				if(!$editcat['notinheritedarticle']) {
					$categorypermsission->remake_inherited_perm($_GET['catid']);
				}
			}
		} elseif($operation == 'edit') {
			if($editcat['notinheritedblock'] != $cate['notinheritedblock']) {
				$tplname = $tplpre.$cate['catid'];
				if($editcat['notinheritedblock']) {
					$tplpermsission->delete_inherited_perm_by_tplname($tplname, $tplpre.$cate['upid']);
				} else {
					if($portalcategory[$cate['catid']]['upid']) {
						$tplpermsission->remake_inherited_perm($tplname, $tplpre.$portalcategory[$cate['catid']]['upid']);
					}
				}
			}
			if($editcat['notinheritedarticle'] != $cate['notinheritedarticle']) {
				if($editcat['notinheritedarticle']) {
					$categorypermsission->delete_inherited_perm_by_catid($cate['catid'], $cate['upid']);
				} else {
					$categorypermsission->remake_inherited_perm($cate['catid']);
				}
			}
		}

		if(!empty($updatecategoryfile)) {
			remakecategoryfile($updatecategoryfile);
		}

		if($dir) {
			if(!makecategoryfile($dir, $_GET['catid'], $domain)) {
				cpmsg(cplang('portalcategory_filewrite_error').','.cplang('return'), NULL, 'error');
			}
			remakecategoryfile($portalcategory[$_GET['catid']]['children']);
		}

		if(($_GET['catid'] && $cate['level'] < 2) || empty($_GET['upid']) || ($_GET['upid'] && $portalcategory[$_GET['upid']]['level'] == 0)) {
			$nav = C::t('common_nav')->fetch_by_type_identifier(4, $_GET['catid']);
			if($editcat['shownav']) {
				if(empty($nav)) {
					$navparentid = 0;
					if($_GET['catid'] && $cate['level'] > 0 || !empty($_GET['upid'])) {
						$identifier = !empty($cate['upid']) ? $cate['upid'] : ($_GET['upid'] ? $_GET['upid'] : 0);
						$navparent = C::t('common_nav')->fetch_by_type_identifier(4, $identifier);
						$navparentid = $navparent['id'];
						if(empty($navparentid)) {
							cpmsg(cplang('portalcategory_parentcategory_no_shownav').','.cplang('return'), NULL, 'error');
						}
					}
					$setarr = array(
						'parentid' => $navparentid,
						'name' => $editcat['catname'],
						'url' => $portalcategory[$_GET['catid']]['caturl'],
						'type' => '4',
						'available' => '1',
						'identifier' => $_GET['catid'],
					);
					if($_GET['catid'] && $cate['level'] == 0 || empty($_GET['upid']) && empty($_GET['catid'])) {
						$setarr['subtype'] = '1';
					}
					$navid = C::t('common_nav')->insert($setarr, true);

					if($_GET['catid'] && $cate['level'] == 0) {
						if(!empty($cate['children'])) {
							foreach($cate['children'] as $subcatid) {
								if($portalcategory[$subcatid]['shownav']) {
									$setarr = array(
										'parentid' => $navid,
										'name' => $portalcategory[$subcatid]['catname'],
										'url' => $portalcategory[$subcatid]['caturl'],
										'type' => '4',
										'available' => '1',
										'identifier' => $subcatid,
									);
									C::t('common_nav')->insert($setarr);
								}
							}
						}
					}

				} else {
					$setarr = array('available'=>'1','url' => $portalcategory[$_GET['catid']]['caturl']);
					C::t('common_nav')->update_by_type_identifier(4, $_GET['catid'], $setarr);
					if($portalcategory[$_GET['catid']]['level'] == 0 && $portalcategory[$_GET['catid']]['children']) {
						foreach($portalcategory[$_GET['catid']]['children'] as $subcatid) {
							C::t('common_nav')->update_by_type_identifier(4, $subcatid, array('url' => $portalcategory[$subcatid]['caturl']));
						}
					}
				}
				$cachearr[] = 'setting';
			} else {
				if(!empty($nav)) {
					C::t('common_nav')->delete($nav['id']);
					if($portalcategory[$_GET['catid']]['level'] == 0 && !empty($portalcategory[$_GET['catid']]['children'])) {
						C::t('common_nav')->delete_by_parentid($nav['id']);
						C::t('portal_category')->update($portalcategory[$_GET['catid']]['children'], array('shownav'=>'0'));
					}
					$cachearr[] = 'setting';
				}
			}
		}

		if($_GET['setindex']) {
			C::t('common_setting')->update('defaultindex', $portalcategory[$_GET['catid']]['caturl']);
			$cachearr[] = 'setting';
		} elseif($oldsetindex) {
			C::t('common_setting')->update('defaultindex', '');
			$cachearr[] = 'setting';
		}

		updatecache(array_unique($cachearr));

		$url = $operation == 'add' ? 'action=portalcategory#cat'.$_GET['catid'] : 'action=portalcategory&operation=edit&catid='.$_GET['catid'];
		cpmsg('portalcategory_edit_succeed', $url, 'succeed');
	}
}

function showcategoryrow($key, $level = 0, $last = '') {
	global $_G;

	loadcache('portalcategory');
	$value = $_G['cache']['portalcategory'][$key];
	$return = '';

	include_once libfile('function/portalcp');
	$value['articles'] = category_get_num('portal', $key);
	$publish = '';
	if(empty($_G['cache']['portalcategory'][$key]['disallowpublish'])) {
		$publish = '&nbsp;<a href="portal.php?mod=portalcp&ac=article&catid='.$key.'" target="_blank">'.cplang('portalcategory_publish').'</a>';
	}
	if($level == 2) {
		$class = $last ? 'lastchildboard' : 'childboard';
		$return = '<tr class="hover" id="cat'.$value['catid'].'"><td>&nbsp;</td><td class="td25"><input type="text" class="txt" name="neworder['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="'.$class.'">'.
		'<input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" />'.
		'</div>'.
		'</td><td>'.$value['articles'].'</td>'.
		'<td>'.(empty($value['disallowpublish']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(!empty($value['allowcomment']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(empty($value['closed']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td><input class="radio" type="radio" name="newsetindex" value="'.$value['catid'].'" '.($value['caturl'] == $_G['setting']['defaultindex'] ? 'checked="checked"':'').' /></td>'.
		'<td><a href="'.$value['caturl'].'" target="_blank">'.cplang('view').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$value['catid'].'">'.cplang('edit').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname=portal/list_'.$value['catid'].'&tpldirectory='.getdiydirectory($value['primaltplname']).'">'.cplang('portalcategory_blockperm').'</a></td>
		<td><a href="'.ADMINSCRIPT.'?action=article&operation=list&&catid='.$value['catid'].'">'.cplang('portalcategory_articlemanagement').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_articleperm').'</a>'.$publish.'</td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover" id="cat'.$value['catid'].'"><td>&nbsp;</td><td class="td25"><input type="text" class="txt" name="neworder['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="board">'.
		'<input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" />'.
		'<a class="addchildboard" href="'.ADMINSCRIPT.'?action=portalcategory&operation=add&upid='.$value['catid'].'">'.cplang('portalcategory_addthirdcategory').'</a></div>'.
		'</td><td>'.$value['articles'].'</td>'.
		'<td>'.(empty($value['disallowpublish']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(!empty($value['allowcomment']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(empty($value['closed']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td><input class="radio" type="radio" name="newsetindex" value="'.$value['catid'].'" '.($value['caturl'] == $_G['setting']['defaultindex'] ? 'checked="checked"':'').' /></td>'.
		'<td><a href="'.$value['caturl'].'" target="_blank">'.cplang('view').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$value['catid'].'">'.cplang('edit').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname=portal/list_'.$value['catid'].'&tpldirectory='.getdiydirectory($value['primaltplname']).'">'.cplang('portalcategory_blockperm').'</a></td>
		<td><a href="'.ADMINSCRIPT.'?action=article&operation=list&&catid='.$value['catid'].'">'.cplang('portalcategory_articlemanagement').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_articleperm').'</a>'.$publish.'</td></tr>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 2, $i==$L-1);
		}
	} else {
		$childrennum = count($_G['cache']['portalcategory'][$key]['children']);
		$toggle = $childrennum > 25 ? ' style="display:none"' : '';
		$return = '<tbody><tr class="hover" id="cat'.$value['catid'].'"><td onclick="toggle_group(\'group_'.$value['catid'].'\')"><a id="a_group_'.$value['catid'].'" href="javascript:;">'.($toggle ? '[+]' : '[-]').'</a></td>'
		.'<td class="td25"><input type="text" class="txt" name="neworder['.$value['catid'].']" value="'.$value['displayorder'].'" /></td><td><div class="parentboard">'.
		'<input type="text" class="txt" name="name['.$value['catid'].']" value="'.$value['catname'].'" />'.
		'</div>'.
		'</td><td>'.$value['articles'].'</td>'.
		'<td>'.(empty($value['disallowpublish']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(!empty($value['allowcomment']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td>'.(empty($value['closed']) ? cplang('yes') : cplang('no')).'</td>'.
		'<td><input class="radio" type="radio" name="newsetindex" value="'.$value['catid'].'" '.($value['caturl'] == $_G['setting']['defaultindex'] ? 'checked="checked"':'').' /></td>'.
		'<td><a href="'.$value['caturl'].'" target="_blank">'.cplang('view').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=edit&catid='.$value['catid'].'">'.cplang('edit').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=move&catid='.$value['catid'].'">'.cplang('portalcategory_move').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=delete&catid='.$value['catid'].'">'.cplang('delete').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname=portal/list_'.$value['catid'].'&tpldirectory='.getdiydirectory($value['primaltplname']).'">'.cplang('portalcategory_blockperm').'</a></td>
		<td><a href="'.ADMINSCRIPT.'?action=article&operation=list&&catid='.$value['catid'].'">'.cplang('portalcategory_articlemanagement').'</a>&nbsp;
		<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.cplang('portalcategory_articleperm').'</a>'.$publish.'</td></tr></tbody>
		<tbody id="group_'.$value['catid'].'"'.$toggle.'>';
		for($i=0,$L=count($value['children']); $i<$L; $i++) {
			$return .= showcategoryrow($value['children'][$i], 1, '');
		}
		$return .= '</tdoby><tr><td>&nbsp;</td><td colspan="9"><div class="lastboard"><a class="addtr" href="'.ADMINSCRIPT.'?action=portalcategory&operation=add&upid='.$value['catid'].'">'.cplang('portalcategory_addsubcategory').'</a></td></div>';
	}
	return $return;
}

function deleteportalcategory($ids) {
	global $_G;

	if(empty($ids)) return false;
	if(!is_array($ids) && $_G['cache']['portalcategory'][$ids]['upid'] == 0) {
		@require_once libfile('function/delete');
		deletedomain(intval($ids), 'channel');
	}
	if(!is_array($ids)) $ids = array($ids);

	require_once libfile('class/blockpermission');
	require_once libfile('class/portalcategory');
	$tplpermission = & template_permission::instance();
	$templates = array();
	foreach($ids as $id) {
		$templates[] = 'portal/list_'.$id;
		$templates[] = 'portal/view_'.$id;
	}
	$tplpermission->delete_allperm_by_tplname($templates);
	$categorypermission = & portal_category::instance();
	$categorypermission->delete_allperm_by_catid($ids);

	C::t('portal_category')->delete($ids);
	C::t('common_nav')->delete_by_type_identifier(4, $ids);

	$tpls = $defaultindex = array();
	foreach($ids as $id) {
		$defaultindex[] = $_G['cache']['portalcategory'][$id]['caturl'];
		$tpls[] = 'portal/list_'.$id;
		$tpls[] = 'portal/view_'.$id;
	}
	if(in_array($_G['setting']['defaultindex'], $defaultindex)) {
		C::t('common_setting')->update('defaultindex', '');
	}
	C::t('common_diy_data')->delete($tpls, NULL);
	C::t('common_template_block')->delete_by_targettplname($tpls);

}


function makecategoryfile($dir, $catid, $domain) {
	dmkdir(DISCUZ_ROOT.'./'.$dir, 0777, FALSE);
	$portalcategory = getglobal('cache/portalcategory');
	$prepath = str_repeat('../', $portalcategory[$catid]['level']+1);
	if($portalcategory[$catid]['level']) {
		$upid = $portalcategory[$catid]['upid'];
		while($portalcategory[$upid]['upid']) {
			$upid = $portalcategory[$upid]['upid'];
		}
		$domain = $portalcategory[$upid]['domain'];
	}

	$sub_dir = $dir;
	if($sub_dir) {
		$sub_dir = substr($sub_dir, -1, 1) == '/' ? '/'.$sub_dir : '/'.$sub_dir.'/';
	}
	$code = "<?php
chdir('$prepath');
define('SUB_DIR', '$sub_dir');
\$_GET['mod'] = 'list';
\$_GET['catid'] = '$catid';
require_once './portal.php';
?>";
	$r = file_put_contents($dir.'/index.php', $code);
	return $r;
}
function getportalcategoryfulldir($catid) {
	if(empty($catid)) return '';
	$portalcategory = getglobal('cache/portalcategory');
	$curdir = $portalcategory[$catid]['foldername'];
	$curdir = $curdir ? $curdir : '';
	if($catid && empty($curdir)) return FALSE;
	$upid = $portalcategory[$catid]['upid'];
	while($upid) {
		$updir = $portalcategory[$upid]['foldername'];
		if(!empty($updir)) {
			$curdir = $updir.'/'.$curdir;
		} else {
			return FALSE;
		}
		$upid = $portalcategory[$upid]['upid'];
	}
	return $curdir ? $curdir.'/' : '';
}

function delportalcategoryfolder($catid) {
	if(empty($catid)) return FALSE;
	$updatearr = array();
	$portalcategory = getglobal('cache/portalcategory');
	$children = $portalcategory[$catid]['children'];
	if($children) {
		foreach($children as $subcatid) {
			if($portalcategory[$subcatid]['foldername']) {
				$arr = delportalcategorysubfolder($subcatid);
				$updatearr = array_merge($updatearr, $arr);
			}
		}
	}

	$dir = getportalcategoryfulldir($catid);
	if(!empty($dir)) {
		unlink(DISCUZ_ROOT.$dir.'index.html');
		unlink(DISCUZ_ROOT.$dir.'index.php');
		rmdir(DISCUZ_ROOT.$dir);
		$updatearr[] = $catid;
	}
	if(dimplode($updatearr)) {
		C::t('portal_category')->update($updatearr, array('foldername'=>''));
	}
}

function delportalcategorysubfolder($catid) {
	if(empty($catid)) return FALSE;
	$updatearr = array();
	$portalcategory = getglobal('cache/portalcategory');
	$children = $portalcategory[$catid]['children'];
	if($children) {
		foreach($children as $subcatid) {
			if($portalcategory[$subcatid]['foldername']) {
				$arr = delportalcategorysubfolder($subcatid);
				$updatearr = array_merge($updatearr, $arr);
			}
		}
	}

	$dir = getportalcategoryfulldir($catid);
	if(!empty($dir)) {
		unlink(DISCUZ_ROOT.$dir.'index.html');
		unlink(DISCUZ_ROOT.$dir.'index.php');
		rmdir(DISCUZ_ROOT.$dir);
		$updatearr[] = $catid;
	}
	return $updatearr;
}

function remakecategoryfile($categorys) {
	if(is_array($categorys)) {
		$portalcategory = getglobal('cache/portalcategory');
		foreach($categorys as $subcatid) {
			$dir = getportalcategoryfulldir($subcatid);
			makecategoryfile($dir, $subcatid, $portalcategory[$subcatid]['domain']);
			if($portalcategory[$subcatid]['children']) {
				remakecategoryfile($portalcategory[$subcatid]['children']);
			}
		}
	}
}

function showportalprimaltemplate($pritplname, $type) {
	include_once libfile('function/portalcp');
	$tpls = array('./template/default:portal/'.$type=>getprimaltplname('portal/'.$type.'.htm'));
	foreach($alltemplate = C::t('common_template')->range() as $template) {
		if(($dir = dir(DISCUZ_ROOT.$template['directory'].'/portal/'))) {
			while(false !== ($file = $dir->read())) {
				$file = strtolower($file);
				if (fileext($file) == 'htm' && substr($file, 0, strlen($type)+1) == $type.'_') {
					$key = $template['directory'].':portal/'.str_replace('.htm','',$file);
					$tpls[$key] = getprimaltplname($template['directory'].':portal/'.$file);
				}
			}
		}
	}

	foreach($tpls as $key => $value) {
		echo "<input name=signs[$type][".dsign($key)."] value='1' type='hidden' />";
	}

	$pritplvalue = '';
	if(empty($pritplname)) {
		$pritplhide = '';
		$pritplvalue = ' style="display:none;"';
	} else {
		$pritplhide = ' style="display:none;"';
	}
	$catetplselect = '<span'.$pritplhide.'><select id="'.$type.'select" name="'.$type.'primaltplname">';
	$selectedvalue = '';
	if($type == 'view') {
		$catetplselect .= '<option value="">'.cplang('portalcategory_inheritupsetting').'</option>';
	}
	foreach($tpls as $k => $v){
		if($pritplname === $k) {
			$selectedvalue = $k;
			$selected = ' selected';
		} else {
			$selected = '';
		}
		$catetplselect .= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
	}
	$pritplophide = !empty($pritplname) ? '' : ' style="display:none;"';
	$catetplselect .= '</select> <a href="javascript:;"'.$pritplophide.' onclick="$(\''.$type.'select\').value=\''.$selectedvalue.'\';$(\''.$type.'select\').parentNode.style.display=\'none\';$(\''.$type.'value\').style.display=\'\';">'.cplang('cancel').'</a></span>';

	if(empty($pritplname)) {
		showsetting('portalcategory_'.$type.'primaltplname', '', '', $catetplselect);
	} else {
		$tplname = getprimaltplname($pritplname.'.htm');
		$html = '<span id="'.$type.'value" '.$pritplvalue.'> '.$tplname.'<a href="javascript:;" onclick="$(\''.$type.'select\').parentNode.style.display=\'\';$(\''.$type.'value\').style.display=\'none\';"> '.cplang('modify').'</a></span>';
		showsetting('portalcategory_'.$type.'primaltplname', '', '', $catetplselect.$html);
	}
}

function remakediytemplate($primaltplname, $targettplname, $diytplname, $olddirectory){
	global $_G;
	if(empty($targettplname)) return false;
	$tpldirectory = '';
	if(strpos($primaltplname, ':') !== false) {
		list($tpldirectory, $primaltplname) = explode(':', $primaltplname);
	}
	$tpldirectory = ($tpldirectory ? $tpldirectory : $_G['cache']['style_default']['tpldir']);
	$newdiydata = C::t('common_diy_data')->fetch($targettplname, $tpldirectory);
	if($newdiydata) {
		return false;
	}
	$diydata = C::t('common_diy_data')->fetch($targettplname, $olddirectory);
	$diycontent = empty($diydata['diycontent']) ? '' : $diydata['diycontent'];
	if($diydata) {
		C::t('common_diy_data')->update($targettplname, $olddirectory, array('primaltplname'=>$primaltplname, 'tpldirectory'=>$tpldirectory));
	} else {
		$diycontent = '';
		if(in_array($primaltplname, array('portal/list', 'portal/view'))) {
			$diydata = C::t('common_diy_data')->fetch($targettplname, $olddirectory);
			$diycontent = empty($diydata['diycontent']) ? '' : $diydata['diycontent'];
		}
		$diyarr = array(
			'targettplname' => $targettplname,
			'tpldirectory' => $tpldirectory,
			'primaltplname' => $primaltplname,
			'diycontent' => $diycontent,
			'name' => $diytplname,
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => TIMESTAMP,
			);
		C::t('common_diy_data')->insert($diyarr);
	}
	if(empty($diycontent)) {
		$file = $tpldirectory.'/'.$primaltplname.'.htm';
		if (!file_exists($file)) {
			$file = './template/default/'.$primaltplname.'.htm';
		}
		$content = @file_get_contents(DISCUZ_ROOT.$file);
		if(!$content) $content = '';
		$content = preg_replace("/\<\!\-\-\[name\](.+?)\[\/name\]\-\-\>/i", '', $content);
		file_put_contents(DISCUZ_ROOT.'./data/diy/'.$tpldirectory.'/'.$targettplname.'.htm', $content);
	} else {
		updatediytemplate($targettplname, $tpldirectory);
	}
	return true;
}

function getparentviewprimaltplname($catid) {
	global $_G;
	$tpl = 'view';
	if(empty($catid)) {
		return $tpl;
	}
	$cat = $_G['cache']['portalcategroy'][$catid];
	if(!empty($cat['upid']['articleprimaltplname'])) {
		$tpl = $cat['upid']['articleprimaltplname'];
	} else {
		$cat = $_G['cache']['portalcategroy'][$cat['upid']];
		if($cat && $cat['articleprimaltplname']) {
			$tpl = $cat['articleprimaltplname'];
		}
	}
	return $tpl;
}
?>