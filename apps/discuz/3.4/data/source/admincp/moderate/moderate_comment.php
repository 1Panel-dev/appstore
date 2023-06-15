<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_comment.php 31996 2012-10-30 06:15:14Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_GET['fast']) {

	shownav('topic', $lang['moderate_comments']);
	showsubmenu('nav_moderate_posts', $submenu);

	$select[$_GET['tpp']] = $_GET['tpp'] ? "selected='selected'" : '';
	$tpp_options = "<option value='20' $select[20]>20</option><option value='50' $select[50]>50</option><option value='100' $select[100]>100</option>";
	$tpp = !empty($_GET['tpp']) ? $_GET['tpp'] : '20';
	$start_limit = ($page - 1) * $ppp;
	$dateline = $_GET['dateline'] ? $_GET['dateline'] : '604800';
	$dateline_options = '';
	foreach(array('all', '604800', '2592000', '7776000') as $v) {
		$selected = '';
		if($dateline == $v) {
			$selected = "selected='selected'";
		}
		$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
	}
	$idtype_select = '<option value="">'.$lang['all'].'</option>';
	foreach(array('uid', 'blogid', 'picid', 'sid') as $v) {
		$selected = '';
		if($_GET['idtype'] == $v) {
			$selected = 'selected="selected"';
		}
		$idtype_select .= "<option value=\"$v\" $selected>".$lang["comment_$v"]."</option>";
	}
	$comment_status = 1;
	if($_GET['filter'] == 'ignore') {
		$comment_status = 2;
	}
	showformheader("moderate&operation=comments");
	showtableheader('search');


	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
		array(
			cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_GET[username]\" />",
			cplang('moderate_content_keyword'), "<input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_GET[keyword]\" />",
		)
	);
	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
                array(
                        "$lang[perpage]",
                        "<select name=\"tpp\">$tpp_options</select><label><input name=\"showcensor\" type=\"checkbox\" class=\"checkbox\" value=\"yes\" ".($showcensor ? ' checked="checked"' : '')."/> $lang[moderate_showcensor]</label>",
                        "$lang[moderate_bound]",
                        "<select name=\"filter\">$filteroptions</select>
			<select name=\"idtype\">$idtype_select</select>
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );

	showtablefooter();

	$pagetmp = $page;
	$modcount = C::t('common_moderate')->count_by_search_for_commnet($_GET['idtype'], $moderatestatus, $_GET['username'], (($dateline &&  $dateline != 'all') ? (TIMESTAMP - $dateline) : null), $_GET['keyword']);
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$commentarr = C::t('common_moderate')->fetch_all_by_search_for_comment($_GET['idtype'], $moderatestatus, $_GET['username'], (($dateline &&  $dateline != 'all') ? (TIMESTAMP - $dateline) : null), $_GET['keyword'], $start_limit, $tpp);
		$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && empty($commentarr));
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=comments&filter=$filter&dateline={$_GET['dateline']}&username={$_GET['username']}&keyword={$_GET['keyword']}&idtype={$_GET['idtype']}&ppp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	foreach($commentarr as $comment) {
		$comment['dateline'] = dgmdate($comment['dateline']);
		$short_desc = cutstr($comment['message'], 75);
		if($showcensor) {
			$censor->check($short_desc);
			$censor->check($comment['message']);
		}
		$comment_censor_words = $censor->words_found;
		if(count($comment_censor_words) > 3) {
			$comment_censor_words = array_slice($comment_censor_words, 0, 3);
		}
		$comment['censorwords'] = implode(', ', $comment_censor_words);
		$comment['ip'] = $comment['ip'] . ' - ' . convertip($comment['ip']);
		$comment['modkey'] = modauthkey($comment['id']);
		$comment['modcommentkey'] = modauthkey($comment['cid']);

		if($showcensor) {
			if(count($comment_censor_words)) {
				$comment_censor_text = "<span style=\"color: red;\">({$comment['censorwords']})</span>";
			} else {
				$comment_censor_text = lang('admincp', 'no_censor_word');
			}
		}
		$viewurl = '';
		$commenttype = '';
		$editurl = "home.php?mod=spacecp&ac=comment&op=edit&cid=$comment[cid]&modcommentkey=$comment[modcommentkey]";
		switch($comment['idtype']) {
			case 'uid':
				$commenttype = lang('admincp', 'comment_uid');
				$viewurl = "home.php?mod=space&uid=$comment[uid]&do=wall#comment_anchor_$comment[cid]";
				break;
			case 'blogid':
				$commenttype = lang('admincp', 'comment_blogid');
				$viewurl = "home.php?mod=space&uid=$comment[uid]&do=blog&id=$comment[id]&modblogkey=$comment[modkey]#comment_anchor_$comment[cid]";
				break;
			case 'picid':
				$commenttype = lang('admincp', 'comment_picid');
				$viewurl = "home.php?mod=space&uid=$comment[uid]&do=album&picid=$comment[id]&modpickey=$comment[modkey]#comment_anchor_$comment[cid]";
				break;
			case 'sid':
				$commenttype = lang('admincp', 'comment_sid');
				$viewurl = "home.php?mod=space&uid=$comment[uid]&do=share&id=$comment[id]#comment_anchor_$comment[cid]";
				break;
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$comment[cid]_row1\"", array("id=\"mod_$comment[cid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$comment[cid]]\" id=\"mod_$comment[cid]_1\" value=\"validate\" onclick=\"mod_setbg($comment[cid], 'validate');\"><label for=\"mod_$comment[cid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$comment[cid]]\" id=\"mod_$comment[cid]_2\" value=\"delete\" onclick=\"mod_setbg($comment[cid], 'delete');\"><label for=\"mod_$comment[cid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$comment[cid]]\" id=\"mod_$comment[cid]_3\" value=\"ignore\" onclick=\"mod_setbg($comment[cid], 'ignore');\"><label for=\"mod_$comment[cid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$comment[cid]});\"> $short_desc $comment_censor_text</a></h3><p>$comment[ip]</p>",
			$commenttype.'<input name="idtypes['.$comment['cid'].']" type="hidden" value="'.$comment['idtype'].'">',
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$comment[authorid]&submit=yes\">$comment[author]</a></p> <p>$comment[dateline]</p>",
			"<a target=\"_blank\" href=\"$viewurl\">$lang[view]</a>&nbsp;<a href=\"$editurl\" target=\"_blank\">$lang[edit]</a>",
		));

		showtablerow("id=\"mod_$comment[cid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$comment['message'].'</div>');
		showtablerow("id=\"mod_$comment[cid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=comments&fast=1&cid=$comment[cid]&moderate[$comment[cid]]=validate&idtypes[$comment[cid]]=$comment[idtype]&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=comments&fast=1&cid=$comment[cid]&moderate[$comment[cid]]=delete&idtypes[$comment[cid]]=$comment[idtype]&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=comments&fast=1&cid=$comment[cid]&moderate[$comment[cid]]=ignore&idtypes[$comment[cid]]=$comment[idtype]&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");

		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	$moderatedata = array();
	if(is_array($moderate)) {
		foreach($moderate as $cid => $act) {
			$moderation[$act][] = $cid;
			$moderatedata[$act][$_GET['idtypes'][$cid]][] = $cid;
		}
	}

	foreach($moderatedata as $act => $typeids) {
		foreach($typeids as $idtype => $ids) {
			$op = $act == 'ignore' ? 1 : 2;
			updatemoderate($idtype.'_cid', $ids, $op);
		}
	}

	if($moderation['validate']) {
		$validates = C::t('home_comment')->update($moderation['validate'], array('status' => '0'));
	}
	if(!empty($moderation['delete'])) {
		require_once libfile('function/delete');
		$comments = deletecomments($moderation['delete']);
		$deletes = count($comments);
	}
	if($moderation['ignore']) {
		$ignores = C::t('home_comment')->update($moderation['ignore'], array('status' => '2'));
	}

	if($_GET['fast']) {
		echo callback_js($_GET['cid']);
		exit;
	} else {
		cpmsg('moderate_comments_succeed', "action=moderate&operation=comments&page=$page&filter=$filter&dateline={$_GET['dateline']}&username={$_GET['username']}&keyword={$_GET['keyword']}&idtype={$_GET['idtype']}&tpp={$_GET['tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
	}

}

?>