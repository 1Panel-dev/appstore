<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_blog.php 31710 2012-09-24 07:24:52Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_GET['fast']) {

	require_once libfile('function/discuzcode');

	shownav('topic', $lang['moderate_blogs']);
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
	$blog_status = 1;
	if($_GET['filter'] == 'ignore') {
		$blog_status = 2;
	}
	showformheader("moderate&operation=blogs");
	showtableheader('search');

	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
		array(
			cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_GET[username]\" />",
			cplang('moderate_title_keyword'), "<input size=\"15\" name=\"title\" type=\"text\" value=\"$_GET[title]\" />",
		)
	);
	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
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
	$modcount = C::t('common_moderate')->count_by_search_for_blog($moderatestatus, $_GET['username'], (($dateline &&  $dateline != 'all') ? (TIMESTAMP - $dateline) : null), $_GET['title']);
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$blogarr = C::t('common_moderate')->fetch_all_by_search_for_blog($moderatestatus, $_GET['username'], (($dateline &&  $dateline != 'all') ? (TIMESTAMP - $dateline) : null), $_GET['title'], $start_limit, $tpp);
		$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && empty($blogarr));
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=blogs&filter=$filter&modfid=$modfid&ppp=$tpp&showcensor=$showcensor&dateline=$dateline");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	foreach($blogarr as $blog) {
		$blog['dateline'] = dgmdate($blog['dateline']);
		$blog['subject'] = $blog['subject'] ? '<b>'.$blog['subject'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
		if($showcensor) {
			$censor->check($blog['subject']);
			$censor->check($blog['message']);
		}
		$blog_censor_words = $censor->words_found;
		if(count($post_censor_words) > 3) {
			$blog_censor_words = array_slice($blog_censor_words, 0, 3);
		}
		$blog['censorwords'] = implode(', ', $blog_censor_words);
		$blog['modblogkey'] = modauthkey($blog['blogid']);
		$blog['postip'] = $blog['postip'] . '-' . convertip($blog['postip']);

		if(count($blog_censor_words)) {
			$blog_censor_text = "<span style=\"color: red;\">({$blog['censorwords']})</span>";
		} else {
			$blog_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$blog[blogid]_row1\"", array("id=\"mod_$blog[blogid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$blog[blogid]]\" id=\"mod_$blog[blogid]_1\" value=\"validate\" onclick=\"mod_setbg($blog[blogid], 'validate');\"><label for=\"mod_$blog[blogid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$blog[blogid]]\" id=\"mod_$blog[blogid]_2\" value=\"delete\" onclick=\"mod_setbg($blog[blogid], 'delete');\"><label for=\"mod_$blog[blogid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$blog[blogid]]\" id=\"mod_$blog[blogid]_3\" value=\"ignore\" onclick=\"mod_setbg($blog[blogid], 'ignore');\"><label for=\"mod_$blog[blogid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$blog[blogid]');\">$blog[subject]</a> $blog_censor_text</h3><p>$blog[postip]</p>",
			$blog[classname],
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$blog[uid]&submit=yes\">$blog[username]</a></p> <p>$blog[dateline]</p>",
			"<a href=\"home.php?mod=space&uid=$blog[uid]&do=blog&id=$blog[blogid]&modblogkey=$blog[modblogkey]\" target=\"_blank\">$lang[view]</a>&nbsp;<a href=\"home.php?mod=spacecp&ac=blog&blogid=$blog[blogid]&modblogkey=$blog[modblogkey]\" target=\"_blank\">$lang[edit]</a>",
		));
		showtablerow("id=\"mod_$blog[blogid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$blog['message'].'</div>');
		showtablerow("id=\"mod_$blog[blogid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=blogs&fast=1&blogid=$blog[blogid]&moderate[$blog[blogid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=blogs&fast=1&blogid=$blog[blogid]&moderate[$blog[blogid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=blogs&fast=1&blogid=$blog[blogid]&moderate[$blog[blogid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	if(is_array($moderate)) {
		foreach($moderate as $blogid => $act) {
			$moderate[$act][] = $blogid;
		}
	}

	if($moderate['validate']) {
		$validates = C::t('home_blog')->update($moderate['validate'], array('status' => '0'));
		$query_t = C::t('home_blog')->count_uid_by_blogid($moderate['validate']);
		foreach($query_t as $blog_user) {
			$credit_times = $blog_user['count'];
			updatecreditbyaction('publishblog', $blog_user['uid'], array('blogs' => 1), '', $credit_times);
		}
		updatemoderate('blogid', $moderate['validate'], 2);
	}

	if($moderate['delete']) {
		require_once libfile('function/delete');
		$delete_blogs = deleteblogs($moderate['delete']);
		$deletes = count($delete_blogs);
		updatemoderate('blogid', $moderate['delete'], 2);
	}

	if($moderate['ignore']) {
		$ignores = C::t('home_blog')->update($moderate['ignore'], array('status' => '2'));
		updatemoderate('blogid', $moderate['ignore'], 1);
	}

	if($_GET['fast']) {
		echo callback_js($_GET['blogid']);
		exit;
	} else {
		cpmsg('moderate_blogs_succeed', "action=moderate&operation=blogs&page=$page&filter=$filter&dateline={$_GET['dateline']}&username={$_GET['username']}&title={$_GET['title']}&tpp={$_GET['tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
	}

}

?>