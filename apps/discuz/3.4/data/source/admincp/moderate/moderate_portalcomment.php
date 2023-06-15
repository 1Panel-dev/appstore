<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_portalcomment.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$idtype = $tablename = $mod = '';
if($operation == 'articlecomments') {
	$idtype = 'aid';
	$tablename = 'portal_article_title';
	$mod = 'view';
} else {
	$idtype = 'topicid';
	$tablename = 'portal_topic';
	$mod = 'topic';
}
if(!submitcheck('modsubmit') && !$_GET['fast']) {

	shownav('topic', $lang['moderate_articlecomments']);
	showsubmenu('nav_moderate_articlecomments', $submenu);

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
	$cat_select = '';
	if($operation == 'articlecomments') {
		$cat_select = '<option value="">'.$lang['all'].'</option>';
		loadcache('portalcategory');
		foreach($_G['cache']['portalcategory'] as $cat) {
			$selected = '';
			if($cat['catid'] == $_GET['catid']) {
				$selected = 'selected="selected"';
			}
			$cat_select .= "<option value=\"$cat[catid]\" $selected>$cat[catname]</option>";
		}
		$cat_select = "<select name=\"catid\">$cat_select</select>";
	}

	$articlecomment_status = 1;
	if($_GET['filter'] == 'ignore') {
		$articlecomment_status = 2;
	}
	showformheader("moderate&operation=$operation");
	showtableheader('search');

	if($operation == 'articlecomments') {
		showtablerow('', array('width="60"', 'width="160"', 'width="60"', 'width="200"', 'width="60"'),
			array(
				cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_GET[username]\" />",
				cplang('moderate_article_category'), $cat_select,
				cplang('moderate_content_keyword'), "<input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_GET[keyword]\" />",
			)
		);
	} else {
		showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
			array(
				cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_GET[username]\" />",
				cplang('moderate_content_keyword'), "<input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_GET[keyword]\" />",
			)
		);
	}
	showtablerow('', $operation == 'articlecomments' ?
		array('width="60"', 'width="160"', 'width="60"', 'colspan="3"') :
		array('width="60"', 'width="160"', 'width="60"'),
                array(
                        "$lang[perpage]",
                        "<select name=\"tpp\">$tpp_options</select><label><input name=\"showcensor\" type=\"checkbox\" class=\"checkbox\" value=\"yes\" ".($showcensor ? ' checked="checked"' : '')."/> $lang[moderate_showcensor]</label>",
                        "$lang[moderate_bound]",
                        "<select name=\"filter\">$filteroptions</select>
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );

	showtablefooter();

	$pagetmp = $page;
	$modcount = C::t('common_moderate')->fetch_all_for_portalcomment($idtype, $tablename, $moderatestatus, $_GET['catid'], $_GET['username'], $dateline, 1, $_GET['keyword']);
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$query  = C::t('common_moderate')->fetch_all_for_portalcomment($idtype, $tablename, $moderatestatus, $_GET['catid'], $_GET['username'], $dateline, 0, $_GET['keyword'], $start_limit, $tpp);
		$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && count($query) == 0);
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=$operation&filter=$filter&modfid=$modfid&ppp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	foreach($query as $articlecomment) {
		$articlecomment['dateline'] = dgmdate($articlecomment['dateline']);
		if($showcensor) {
			$censor->check($articlecomment['title']);
			$censor->check($articlecomment['message']);
		}
		$articlecomment_censor_words = $censor->words_found;
		if(count($articlecomment_censor_words) > 3) {
			$articlecomment_censor_words = array_slice($articlecomment_censor_words, 0, 3);
		}
		$articlecomment['censorwords'] = implode(', ', $articlecomment_censor_words);
		$articlecomment['modarticlekey'] = modauthkey($articlecomment['aid']);
		$articlecomment['modarticlecommentkey'] = modauthkey($articlecomment['cid']);

		if(count($articlecomment_censor_words)) {
			$articlecomment_censor_text = "<span style=\"color: red;\">({$articlecomment['censorwords']})</span>";
		} else {
			$articlecomment_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$articlecomment[cid]_row1\"", array("id=\"mod_$articlecomment[cid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$articlecomment[cid]]\" id=\"mod_$articlecomment[cid]_1\" value=\"validate\" onclick=\"mod_setbg($articlecomment[cid], 'validate');\"><label for=\"mod_$articlecomment[cid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$articlecomment[cid]]\" id=\"mod_$articlecomment[cid]_2\" value=\"delete\" onclick=\"mod_setbg($articlecomment[cid], 'delete');\"><label for=\"mod_$articlecomment[cid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$articlecomment[cid]]\" id=\"mod_$articlecomment[cid]_3\" value=\"ignore\" onclick=\"mod_setbg($articlecomment[cid], 'ignore');\"><label for=\"mod_$articlecomment[cid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$articlecomment[cid]});\">$articlecomment[title] $articlecomment_censor_text</a></h3>",
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$articlecomment[uid]&submit=yes\">$articlecomment[username]</a></p> <p>$articlecomment[dateline]</p>",
			"<a target=\"_blank\" href=\"portal.php?mod=$mod&$idtype=$articlecomment[id]&modarticlekey=$articlecomment[modarticlekey]#comment_anchor_{$articlecomment[cid]}\">$lang[view]</a>&nbsp;<a href=\"portal.php?mod=portalcp&ac=comment&op=edit&cid=$articlecomment[cid]&modarticlecommentkey=$articlecomment[modarticlecommentkey]\" target=\"_blank\">$lang[edit]</a>",
		));

		showtablerow("id=\"mod_$articlecomment[cid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$articlecomment['message'].'</div>');

		showtablerow("id=\"mod_$articlecomment[cid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=$operation&fast=1&cid=$articlecomment[cid]&moderate[$articlecomment[cid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=$operation&fast=1&cid=$articlecomment[cid]&moderate[$articlecomment[cid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=$operation&fast=1&cid=$articlecomment[cid]&moderate[$articlecomment[cid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	if(is_array($moderate)) {
		foreach($moderate as $cid => $act) {
			$moderation[$act][] = $cid;
		}
	}

	if($moderation['validate']) {
		$validates = C::t('portal_comment')->update($moderation['validate'], array('status' => '0'));
		updatemoderate($idtype.'_cid', $moderation['validate'], 2);
	}
	if($moderation['delete']) {
		$validates = C::t('portal_comment')->delete($moderation['delete']);
		updatemoderate($idtype.'_cid', $moderation['delete'], 2);
	}
	if($moderation['ignore']) {
		$validates = C::t('portal_comment')->update($ignore_cids, array('status' => '2'));
		updatemoderate($idtype.'_cid', $moderation['ignore'], 1);
	}

	if($_GET['fast']) {
		echo callback_js($_GET['cid']);
		exit;
	} else {
		cpmsg('moderate_'.$operation.'_succeed', "action=moderate&operation=$operation&page=$page&filter=$filter&dateline={$_GET['dateline']}&username={$_GET['username']}&keyword={$_GET['keyword']}&catid={$_GET['catid']}&tpp={$_GET['tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
	}

}

?>