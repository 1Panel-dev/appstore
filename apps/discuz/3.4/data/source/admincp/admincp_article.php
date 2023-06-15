<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_article.php 33047 2013-04-12 08:46:56Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$operation = in_array($operation, array('trash', 'tag')) ? $operation : 'list';
loadcache('portalcategory');
$category = $_G['cache']['portalcategory'];

cpheader();
shownav('portal', 'article');

$searchctrl = '';
if($operation == 'list') {

	$searchctrl = '<span style="float: right; padding-right: 40px;">'
			.'<a href="javascript:;" onclick="$(\'tb_search\').style.display=\'\';$(\'a_search_show\').style.display=\'none\';$(\'a_search_hide\').style.display=\'\';" id="a_search_show" style="display:none">'.cplang('show_search').'</a>'
			.'<a href="javascript:;" onclick="$(\'tb_search\').style.display=\'none\';$(\'a_search_show\').style.display=\'\';$(\'a_search_hide\').style.display=\'none\';" id="a_search_hide">'.cplang('hide_search').'</a>'
			.'</span>';
}
$catid = $_GET['catid'] = intval($_GET['catid']);
showsubmenu('article',  array(
	array('list', 'article&catid='.$catid, $operation == 'list'),
	array('article_trash', 'article&operation=trash&catid='.$catid, $operation == 'trash'),
	array('article_tag', 'article&operation=tag', $operation == 'tag'),
	array('article_add', 'portal.php?mod=portalcp&ac=article', false, 1, 1)
), $searchctrl);

if($operation == 'tag') {

	showtips('article_tag_tip');

	if(submitcheck('articletagsubmit')) {
		C::t('common_setting')->update('article_tags', $_POST['tag']);
		updatecache('setting');
		cpmsg('update_articletag_succeed', 'action=article&operation=tag', 'succeed');
	}

	require_once libfile('function/portalcp');
	$tag_names = article_tagnames();
	showformheader('article&operation=tag');
	showtableheader('article_tag_setting');
	for($i=1; $i<=8; $i++) {
		showtablerow('', array('width=80', ''),
			array(lang('portalcp', 'article_tag').$i, "<input type=\"text\" class=\"txt\" name=\"tag[$i]\" value=\"$tag_names[$i]\" />"));
	}
	showsubmit('articletagsubmit', 'submit');
	showtablefooter();
	showformfooter();

} elseif($operation == 'trash') {

	if(submitcheck('batchsubmit', true)) {
		$_POST['optype'] = empty($_POST['optype']) ? $_GET['optype'] : $_POST['optype'];
		if(empty($_POST['ids']) && $_POST['optype'] != 'clear') {
			cpmsg('article_choose_at_least_one_article', 'action=article&operation=trash', 'error');
		}

		if($_POST['optype'] == 'recover') {

			$inserts = $ids = $catids = array();
			foreach(C::t('portal_article_trash')->fetch_all($_POST['ids']) as $value) {
				$ids[] = intval($value['aid']);
				$article = dunserialize($value['content']);
				$catids[] = intval($article['catid']);
				$inserts[] = array (
					'aid' => $article['aid'],
					'catid' => $article['catid'],
					'uid' => $article['uid'],
					'username' => $article['username'],
					'title' => $article['title'],
					'url' => $article['url'],
					'summary' => $article['summary'],
					'pic' => $article['pic'],
					'id' => $article['id'],
					'idtype' => $article['idtype'],
					'contents' => $article['contents'],
					'dateline' => $article['dateline'],
					'thumb' => $article['thumb'],
					'remote' => $article['remote'],
					'click1' => $article['click1'],
					'click2' => $article['click2'],
					'click3' => $article['click3'],
					'click4' => $article['click4'],
					'click5' => $article['click5'],
					'click6' => $article['click6'],
					'click7' => $article['click7'],
					'click8' => $article['click8'],
					'author' => $article['author'],
					'from' => $article['from'],
					'fromurl' => $article['fromurl'],
					'bid' => $article['bid'],
					'allowcomment' => $article['allowcomment'],
					'tag' => $article['tag'],
					'owncomment' => $article['owncomment'],
					'status' => $article['status'],
					'highlight' => $article['highlight'],
					'showinnernav' => $article['showinnernav'],
					'preaid' => $article['preaid'],
					'nextaid' => $article['nextaid'],
					'htmlmade' => $article['htmlmade'],
					'htmlname' => $article['htmlname'],
					'htmldir' => $article['htmldir'],
				);
			}

			if($inserts) {
				foreach($inserts as $data) {
					C::t('portal_article_title')->insert($data, 0, 1);
				}
				C::t('portal_article_trash')->delete($ids);
			}

			$catids = array_unique($catids);
			if($catids) {
				foreach($catids as $catid) {
					$cnt = C::t('portal_article_title')->fetch_count_for_cat($catid);
					C::t('portal_category')->update($catid, array('articles'=>dintval($cnt)));
				}
			}
			cpmsg('article_trash_recover_succeed', 'action=article&operation=trash', 'succeed');

		} elseif($_POST['optype'] == 'delete') {

			require_once libfile('function/delete');
			deletetrasharticle($_POST['ids']);
			cpmsg('article_trash_delete_succeed', 'action=article&operation=trash', 'succeed');

		} elseif($_POST['optype'] == 'clear') {
			$aids = array();
			foreach(C::t('portal_article_trash')->range(50) as $value) {
				$aids[$value['aid']] = $value['aid'];
			}
			if(!empty($aids)) {
				require_once libfile('function/delete');
				deletetrasharticle($aids);
				cpmsg('article_trash_is_clearing', 'action=article&operation=trash&optype=clear&batchsubmit=yes&formhash='.FORMHASH);
			} else {
				cpmsg('article_trash_is_empty', 'action=article');
			}
		} else {
			cpmsg('article_choose_at_least_one_operation', 'action=article&operation=trash', 'error');
		}

	} else {

		$perpage = 50;

		$start = ($page-1)*$perpage;

		$mpurl .= '&perpage='.$perpage;
		$perpages = array($perpage => ' selected');

		$mpurl = ADMINSCRIPT.'?mod=portal&action=article&operation='.$operation;

		showformheader('article&operation=trash');
		showtableheader('article_trash_list');
		showsubtitle(array('', 'article_title', 'article_category', 'article_username', 'article_dateline'));

		$multipage = '';
		$count = C::t('portal_article_trash')->count();
		if($count) {
			foreach(C::t('portal_article_trash')->range($start, $perpage) as $value) {
				$value = dunserialize($value['content']);
				showtablerow('', array('class="td25"', 'class=""', 'class="td28"'), array(
						"<input type=\"checkbox\" class=\"checkbox\" name=\"ids[]\" value=\"$value[aid]\">",
						$value[title],
						$category[$value['catid']]['catname'],
						"<a href=\"home.php?mod=space&uid=$value[uid]&do=profile\" target=\"_blank\">$value[username]</a>",
						dgmdate($value[dateline])
					));
			}
			$multipage = multi($count, $perpage, $page, $mpurl);
		}

		$batchradio = '<input type="radio" name="optype" value="recover" id="op_recover" class="radio" /><label for="op_recover">'.cplang('article_trash_recover').'</label>&nbsp;&nbsp;';
		$batchradio .= '<input type="radio" name="optype" value="delete" id="op_delete" class="radio" /><label for="op_delete">'.cplang('article_trash_delete').'</label>&nbsp;&nbsp;';
		$batchradio .= '<input type="radio" name="optype" value="clear" id="op_clear" class="radio" style="display:none;"/><input type="hidden" name="batchsubmit" value="yes" />';
		showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ids\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;'
					.$batchradio.'<input type="submit" class="btn" name="batchbutton" value="'.cplang('submit').'" />
					<input type="button" class="btn" name="clearbutton" value="'.cplang('article_clear_trash').'" onclick="if(confirm(\''.cplang('article_clear_trash_confirm').'?\')){this.form.optype[2].checked=\'checked\';this.form.submit();}"/>', $multipage);
		showtablefooter();
		showformfooter();
	}
} else {

	if(submitcheck('articlesubmit')) {

		$perpage = intval($_GET['hiddenperpage']);
		$page = intval($_GET['hiddenpage']);
		$catid = intval($_GET['hiddencatid']);

		$articles = $catids = array();
		$aids = !empty($_GET['ids']) && is_array($_GET['ids']) ? $_GET['ids'] : array();
		if($aids) {
			$query = C::t('portal_article_title')->fetch_all($aids);
			foreach($query as $value) {
				$articles[$value['aid']] = array('aid' => $value['aid'], 'catid' => $value['catid']);
				$catids[] = intval($value['catid']);
			}
		}
		if(empty($articles)) {
			cpmsg('article_choose_at_least_one_article', 'action=article&catid='.$catid.'&perpage='.$perpage.'&page='.$page, 'error');
		}
		$aids = array_keys($articles);

		if($_POST['optype'] == 'trash') {
			require_once libfile('function/delete');
			deletearticle($aids, true);

			cpmsg('article_trash_succeed', 'action=article&catid='.$catid.'&perpage='.$perpage.'&page='.$page, 'succeed');

		} elseif($_POST['optype'] == 'move') {

			$tocatid = intval($_POST['tocatid']);
			$catids[] = $tocatid;
			$catids = array_merge($catids);
			C::t('portal_article_title')->update($aids, array('catid'=>$tocatid));
			foreach($catids as $catid) {
				$catid = intval($catid);
				$cnt = C::t('portal_article_title')->fetch_count_for_cat($catid);
				C::t('portal_category')->update($catid, array('articles'=>dintval($cnt)));
			}
			cpmsg('article_move_succeed', 'action=article&catid='.$catid.'&perpage='.$perpage.'&page='.$page, 'succeed');

		} else {
			cpmsg('article_choose_at_least_one_operation', 'action=article&catid='.$catid.'&perpage='.$perpage.'&page='.$page, 'error');
		}

	} else {

		include_once libfile('function/portalcp');

		$mpurl = ADMINSCRIPT.'?action=article&operation='.$operation;

		$intkeys = array('aid', 'uid');
		$strkeys = array();
		$randkeys = array();
		$likekeys = array('title', 'username');
		$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
		foreach($likekeys as $k) {
			$_GET[$k] = dhtmlspecialchars($_GET[$k]);
		}
		$wherearr = $results['wherearr'];
		$mpurl .= '&'.implode('&', $results['urls']);
		if(!empty($_GET['catid'])) {
			$catid = intval($_GET['catid']);
			$mpurl .= '&catid='.$catid;
			$catids = category_get_childids('portal', $_GET['catid']);
			$catids[] = $_GET['catid'];
			$wherearr[] = 'catid IN ('.dimplode($catids).')';
		}
		if(!empty($_GET['tag'])) {
			$tag = article_make_tag($_GET['tag']);
			$wherearr[] = "(tag & '$tag' = '$tag')";
			foreach($_GET['tag'] as $k=>$v) {
				$mpurl .= "&tag[$k]=$v";
			}
		}
		$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);

		$orders = getorders(array('dateline'), 'aid');
		$ordersql = $orders['sql'];
		if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
		$orderby = array($_GET['orderby']=>' selected');
		$ordersc = array($_GET['ordersc']=>' selected');

		$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
		if(!in_array($perpage, array(10,20,50,100))) $perpage = 10;

		$categoryselect = category_showselect('portal', 'catid', true, $_GET['catid']);
		$searchlang = array();
		$keys = array('search', 'likesupport', 'resultsort', 'defaultsort', 'orderdesc', 'orderasc', 'perpage_10', 'perpage_20', 'perpage_50', 'perpage_100',
		'article_dateline', 'article_id', 'article_title', 'article_uid', 'article_username', 'article_category', 'article_tag');
		foreach ($keys as $key) {
			$searchlang[$key] = cplang($key);
		}
		$articletagcheckbox = '';
		$article_tags = article_tagnames();
		foreach($article_tags as $k=>$v) {
			$checked = !empty($_GET['tag']) && !empty($_GET['tag'][$k]) ? 'checked="checked"' : '';
			$articletagcheckbox .= "<input type=\"checkbox\" class=\"checkbox\" id=\"tag_$k\" name=\"tag[$k]\" value=\"1\"$checked />";
			$articletagcheckbox .= "<label for=\"tag_$k\">$v</label>";
		}

		$start = ($page-1)*$perpage;

		$mpurl .= '&perpage='.$perpage;
		$perpages = array($perpage => ' selected');

		$adminscript = ADMINSCRIPT;
		echo <<<SEARCH
		<form method="get" autocomplete="off" action="$adminscript" id="tb_search">
			<div style="margin-top:8px;">
				<table cellspacing="3" cellpadding="3">
					<tr>
						<th>$searchlang[article_id]</th><td><input type="text" class="txt" name="aid" value="$_GET[aid]"></td>
						<th>$searchlang[article_title]*</th><td><input type="text" class="txt" name="title" value="$_GET[title]">*$searchlang[likesupport]</td>
					</tr>
					<tr>
						<th>$searchlang[article_uid]</th><td><input type="text" class="txt" name="uid" value="$_GET[uid]"></td>
						<th>$searchlang[article_username]*</th><td><input type="text" class="txt" name="username" value="$_GET[username]"></td>
					</tr>
					<tr>
						<th>$searchlang[article_category]</th><td>$categoryselect</td>
						<th>&nbsp;</th><td>&nbsp;</td>
					</tr>
					<tr>
						<th>$searchlang[article_tag]</th><td colspan="3">$articletagcheckbox</td>
					</tr>
					<tr>
						<th>$searchlang[resultsort]</th>
						<td colspan="3">
							<select name="orderby">
							<option value="">$searchlang[defaultsort]</option>
							<option value="dateline"$orderby[dateline]>$searchlang[article_dateline]</option>
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
							<input type="hidden" name="action" value="article">
							<input type="submit" name="searchsubmit" value="$searchlang[search]" class="btn">
						</td>
					</tr>
				</table>
			</div>
		</form>
		<script src="static/js/makehtml.js?1" type="text/javascript"></script>

SEARCH;

		$makehtmlflag = !empty($_G['setting']['makehtml']['flag']);
		showformheader('article&operation=list');
		showtableheader('article_list');
		$subtitle = array('', 'article_title', 'article_category', 'article_username', 'article_dateline');
		if($makehtmlflag) {
			$subtitle[] = 'HTML';
		}
		$subtitle[] = 'operation';
		showsubtitle($subtitle);

		$multipage = '';
		$count = C::t('portal_article_title')->fetch_all_by_sql($wheresql, '', 0, 0, 1);
		if($count) {
			$repairs = array();
			$query = C::t('portal_article_title')->fetch_all_by_sql($wheresql, $ordersql, $start, $perpage);
			foreach($query as $value) {

				$htmlname = $value['htmldir'].$value['htmlname'].'.'.$_G['setting']['makehtml']['extendname'];
				if($makehtmlflag && $value['htmlmade'] && !file_exists(DISCUZ_ROOT.'./'.$htmlname)) {
					$value['htmlmade'] = 0;
					$repairs[$value['aid']] = $value['aid'];
				}

				$tags = article_parse_tags($value['tag']);
				$taghtml = '';
				foreach($tags as $k=>$v) {
					if($v) {
						$taghtml .= ' [<a href="'.ADMINSCRIPT.'?action=article&operation=list&tag['.$k.']=1" style="color: #666">'.$article_tags[$k].'</a>] ';
					}
				}
				$tablerow = array(
						"<input type=\"checkbox\" class=\"checkbox\" name=\"ids[]\" value=\"$value[aid]\">",
						"<a href=\"portal.php?mod=view&aid=$value[aid]\" target=\"_blank\">$value[title]</a>".($taghtml ? $taghtml : ''),
						'<a href="'.ADMINSCRIPT.'?action=article&operation=list&catid='.$value['catid'].'">'.$category[$value['catid']]['catname'].'</a>',
						"<a href=\"".ADMINSCRIPT."?action=article&uid=$value[uid]\">$value[username]</a>",
						dgmdate($value[dateline]),
					);
				if($makehtmlflag) {
					$tablerow[] = "<span id='mkhtml_$value[aid]' style='color:".($value['htmlmade'] ? "blue;'>".cplang('setting_functions_makehtml_made') : "red;'>".cplang('setting_functions_makehtml_dismake'))."</span>";
				}
				$tablerow[] = ($makehtmlflag ? ($category[$value['catid']]['fullfoldername'] ? "<a href='javascript:void(0);' onclick=\"make_html('portal.php?mod=view&aid=$value[aid]', $('mkhtml_$value[aid]'))\">".cplang('setting_functions_makehtml_make')."</a>" : cplang('setting_functions_makehtml_make_has_no_foldername')) : '')
						." <a href=\"portal.php?mod=portalcp&ac=article&aid=$value[aid]\" target=\"_blank\">".cplang('edit')."</a>";
				showtablerow('', array('class="td25"', 'width="480"', 'class="td28"'), $tablerow);
			}
			$multipage = multi($count, $perpage, $page, $mpurl);
			if($repairs) {
				C::t('portal_article_title')->repair_htmlmade($repairs);
			}
		}

		$optypehtml = ''
			.'<input type="hidden" name="hiddenpage" id="hiddenpage" value="'.$page.'"/><input type="hidden" name="hiddencatid" id="hiddencatid" value="'.$catid.'"/><input type="hidden" name="hiddenperpage" id="hiddenperpage" value="'.$perpage.'"/><input type="radio" name="optype" id="optype_trash" value="trash" class="radio" /><label for="optype_trash">'.cplang('article_optrash').'</label>&nbsp;&nbsp;'
			.'<input type="radio" name="optype" id="optype_move" value="move" class="radio" /><label for="optype_move">'.cplang('article_opmove').'</label> '
			.category_showselect('portal', 'tocatid', false)
			.'&nbsp;&nbsp;';
		showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ids\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;'.$optypehtml.'<input type="submit" class="btn" name="articlesubmit" value="'.cplang('submit').'" />', $multipage);
		showtablefooter();
		showformfooter();
	}
}

function showcategoryrow($key, $type = '', $last = '') {
	global $category, $lang;

	$forum = $forums[$key];
	$showedforums[] = $key;

	if($last == '') {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$forum['fid'].']" value="'.$forum['displayorder'].'" /></td><td>';
		if($type == 'group') {
			$return .= '<div class="parentboard">';
		} elseif($type == '') {
			$return .= '<div class="board">';
		} elseif($type == 'sub') {
			$return .= '<div id="cb_'.$forum['fid'].'" class="childboard">';
		}

		$boardattr = '';
		if(!$forum['status']  || $forum['password'] || $forum['redirect']) {
			$boardattr = '<div class="boardattr">';
			$boardattr .= $forum['status'] ? '' : $lang['forums_admin_hidden'];
			$boardattr .= !$forum['password'] ? '' : ' '.$lang['forums_admin_password'];
			$boardattr .= !$forum['redirect'] ? '' : ' '.$lang['forums_admin_url'];
			$boardattr .= '</div>';
		}

		$return .= '<input type="text" class="txt" name="name['.$forum['fid'].']" value="'.dhtmlspecialchars($forum['name']).'" class="txt" />'.
			($type == '' ? '<a href="###" onclick="addrowdirect = 1;addrow(this, 2, '.$forum['fid'].')" class="addchildboard">'.$lang['forums_admin_add_sub'].'</a>' : '').
			'</div>'.$boardattr.
			'</td><td>'.showforum_moderators($forum).'</td>
			<td><a href="'.ADMINSCRIPT.'?action=forums&operation=edit&fid='.$forum['fid'].'" title="'.$lang['forums_edit_comment'].'" class="act">'.$lang['edit'].'</a>'.
			($type != 'group' ? '<a href="'.ADMINSCRIPT.'?action=forums&operation=copy&source='.$forum['fid'].'" title="'.$lang['forums_copy_comment'].'" class="act">'.$lang['forums_copy'].'</a>' : '').
			'<a href="'.ADMINSCRIPT.'?action=forums&operation=delete&fid='.$forum['fid'].'" title="'.$lang['forums_delete_comment'].'" class="act">'.$lang['delete'].'</a></td></tr>';
	} else {
		if($last == 'lastboard') {
			$return = '<tr><td></td><td colspan="3"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$forum['fid'].')" class="addtr">'.$lang['forums_admin_add_forum'].'</a></div></td></tr>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '<tr><td></td><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['forums_admin_add_category'].'</a></div></td></tr>';
		}
	}

	return $return;
}

?>