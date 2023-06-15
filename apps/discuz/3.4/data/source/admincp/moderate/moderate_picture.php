<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_picture.php 25728 2011-11-21 03:52:01Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_GET['fast']) {

	shownav('topic', $lang['moderate_pictures']);
	showsubmenu('nav_moderate_posts', $submenu);

	$select[$_GET['tpp']] = $_GET['tpp'] ? "selected='selected'" : '';
	$tpp_options = "<option value='20' $select[20]>20</option><option value='50' $select[50]>50</option><option value='100' $select[100]>100</option>";
	$tpp = !empty($_GET['tpp']) ? $_GET['tpp'] : '20';
	$start_limit = ($page - 1) * $tpp;
	$dateline = $_GET['dateline'] ? $_GET['dateline'] : '604800';
	$dateline_options = '';
	foreach(array('all', '604800', '2592000', '7776000') as $v) {
		$selected = '';
		if($dateline == $v) {
			$selected = "selected='selected'";
		}
		$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
	}
	$pic_status = 1;
	if($_GET['filter'] == 'ignore') {
		$pic_status = 2;
	}
	showformheader("moderate&operation=pictures");
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
	$modcount = C::t('common_moderate')->count_by_search_for_pic($moderatestatus, $_GET['username'], (($dateline &&  $dateline != 'all') ? (TIMESTAMP - $dateline) : null), $_GET['title']);
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$picarr = C::t('common_moderate')->fetch_all_by_search_for_pic($moderatestatus, $_GET['username'], (($dateline &&  $dateline != 'all') ? (TIMESTAMP - $dateline) : null), $_GET['title'], $start_limit, $tpp);
		$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && empty($picarr));
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=pictures&filter=$filter&dateline={$_GET['dateline']}&username={$_GET['username']}&title={$_GET['title']}&tpp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	require_once libfile('function/home');
	foreach($picarr as $pic) {
		$pic['dateline'] = dgmdate($pic['dateline']);
		$pic['title'] = $pic['title'] ? '<b>'.$pic['title'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
		if($showcensor) {
			$censor->check($pic['title']);
		}
		$pic_censor_words = $censor->words_found;
		if(count($pic_censor_words) > 3) {
			$pic_censor_words = array_slice($pic_censor_words, 0, 3);
		}
		$pic['censorwords'] = implode(', ', $pic_censor_words);
		$pic['modpickey'] = modauthkey($pic['picid']);
		$pic['postip'] = $pic['postip'] . '-' . convertip($pic['postip']);
		$pic['url'] = pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);

		if(count($pic_censor_words)) {
			$pic_censor_text = "<span style=\"color: red;\">({$pic['censorwords']})</span>";
		} else {
			$pic_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$pic[picid]_row1\"", array("id=\"mod_$pic[picid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$pic[picid]]\" id=\"mod_$pic[picid]_1\" value=\"validate\" onclick=\"mod_setbg($pic[picid], 'validate');\"><label for=\"mod_$pic[picid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$pic[picid]]\" id=\"mod_$pic[picid]_2\" value=\"delete\" onclick=\"mod_setbg($pic[picid], 'delete');\"><label for=\"mod_$pic[picid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$pic[picid]]\" id=\"mod_$pic[picid]_3\" value=\"ignore\" onclick=\"mod_setbg($pic[picid], 'ignore');\"><label for=\"mod_$pic[picid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$pic[picid]');\">$pic[title]</a> $pic_censor_text</h3><p>$pic[postip]</p>",
			"<a target=\"_blank\" href=\"home.php?mod=space&uid=$pic[uid]&do=album&id=$pic[albumid]\">$pic[albumname]</a>",
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$pic[uid]&submit=yes\">$pic[username]</a></p> <p>$pic[dateline]</p>",
			"<a target=\"_blank\" href=\"home.php?mod=space&uid=$pic[uid]&do=album&picid=$pic[picid]&modpickey=$pic[modpickey]\">$lang[view]</a>",
		));
		showtablerow("id=\"mod_$pic[picid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;"><img src="'.$pic['url'].'" /></div>');
		showtablerow("id=\"mod_$pic[picid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=pictures&fast=1&picid=$pic[picid]&moderate[$pic[picid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=pictures&fast=1&picid=$pic[picid]&moderate[$pic[picid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=pictures&fast=1&picid=$pic[picid]&moderate[$pic[picid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	if(is_array($moderate)) {
		foreach($moderate as $picid => $act) {
			$moderation[$act][] = $picid;
		}
	}
	if($moderation['validate']) {
		$validates = C::t('home_pic')->update($moderation['validate'], array('status' => '0'));
		$albumincrease = array();
		foreach(C::t('home_pic')->fetch_all($moderation['validate']) as $pics) {
			$albumincrease[$pics['albumid']]++;
		}
		foreach($albumincrease as $albumid=>$albuminc) {
			C::t('home_album')->update_num_by_albumid($albumid, $albuminc);
		}
		updatemoderate('picid', $moderation['validate'], 2);
	}

	if(!empty($moderation['delete'])) {
		require_once libfile('function/delete');
		$pics = deletepics($moderation['delete']);
		$deletes = count($pics);
		updatemoderate('picid', $moderation['delete'], 2);
	}

	if($moderation['ignore']) {
		$ignores = C::t('home_pic')->update($moderation['ignore'], array('status' => '2'));
		updatemoderate('picid', $moderation['ignore'], 1);
	}

	if($_GET['fast']) {
		echo callback_js($_GET['picid']);
		exit;
	} else {
		cpmsg('moderate_pictures_succeed', "action=moderate&operation=pictures&page=$page&filter=$filter&dateline={$_GET['dateline']}&username={$_GET['username']}&title={$_GET['title']}&tpp={$_GET['tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
	}

}

?>